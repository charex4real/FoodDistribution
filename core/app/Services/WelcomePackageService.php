<?php

namespace App\Services;

use App\Models\Stockist;
use App\Models\User;
use App\Models\WelcomePackage;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * WelcomePackageService
 *
 * Single source of truth for creating and redeeming Welcome Back Packages.
 * A package is created in place of an instant cash-back credit at
 * registration/upgrade time (see helpers.php: processCashBack(),
 * processUpgradeCashBack()), and stays locked until a stockist redeems
 * its code in person.
 */
class WelcomePackageService
{ 
    /**
     * Create a pending package for the given user, or null if there is
     * no positive amount to hold (mirrors the original helpers' early-return
     * behaviour on a zero/negative cash-back amount).
     */
    public function createForUser(User $user, float $amount, string $source, string $trx, string $details): ?WelcomePackage
    {
        if ($amount <= 0) {
            return null;
        }

        // $details is accepted for parity with the cash-back helpers' existing
        // signatures and is available here for a future notify() call (e.g.
        // texting/emailing the user their new welcome package code).
        return WelcomePackage::create([
            'user_id' => $user->id,
            'amount'  => round($amount, 2),
            'code'    => $this->generateUniqueCode(),
            'source'  => $source,
            'status'  => WelcomePackage::STATUS_PENDING,
            'trx'     => $trx,
        ]);
    }

    /**
     * Resolve a redemption code to a still-redeemable package.
     *
     * @throws RuntimeException if the code is unknown or already redeemed.
     */
    public function findByCode(string $code): WelcomePackage
    {
        $package = WelcomePackage::where('code', strtoupper(trim($code)))->first();

        if (!$package) {
            throw new RuntimeException('Welcome package code not found.');
        }

        if ($package->isRedeemed()) {
            throw new RuntimeException('This welcome package has already been redeemed.');
        }

        return $package;
    }

    /**
     * Redeem a package on behalf of a stockist: the stockist is reimbursed
     * the package amount for handing the welcome value to the customer in
     * person, exactly like an invoice redemption.
     *
     * @throws RuntimeException if the package was redeemed concurrently.
     */
    public function redeem(WelcomePackage $package, Stockist $stockist, $pvs = 0): WelcomePackage
    {
        return DB::transaction(function () use ($package, $stockist, $pvs) {
            $locked = WelcomePackage::lockForUpdate()->findOrFail($package->id);

            if ($locked->isRedeemed()) {
                throw new RuntimeException('This welcome package has already been redeemed.');
            }

            $locked->status                 = WelcomePackage::STATUS_REDEEMED;
            $locked->redeemed_by_stockist_id = $stockist->id;
            $locked->redeemed_at             = now();
            $locked->save();
            // calculate the stockist rebate from the product pv

            // get stockist type and percentage.
            // $stockist->getStockistPercentage($pvs);
            
            $stockist_rebate_amount =  $stockist->getStockistPercentage($pvs);
            // add money to $stockist->wallet so stockist can use it to order again.
            $amount = (float)$package->amount;
            $stockist->addToWallet($amount);
            // $stockist->save();
            // $stockist->wallet += $stockist_rebate_amount;

            $user = auth()->user();
            $user->addToStockistRebate($stockist_rebate_amount);
 
            stockistTransaction($stockist, $locked->user, $locked->trx, $stockist_rebate_amount);
            return $locked;
        });
    }

    /**
     * Generate a short, human-presentable, guaranteed-unique redemption code.
     */
    private function generateUniqueCode(): string
    {
        do {
            $code = getTrx(10);
        } while (WelcomePackage::where('code', $code)->exists());

        return $code;
    }
}
