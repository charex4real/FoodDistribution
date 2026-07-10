<?php

namespace App\Http\Controllers\User;

use App\Constants\Status;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Transaction;
use App\Models\Transfer;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class BonusTransferController extends Controller
{
    private array $bonusFields = [
        'direct_bonus'   => ['label' => 'Direct Bonus',   'icon' => 'las la-user-check',     'color' => '#3B82F6', 'bg' => 'linear-gradient(135deg,#3B82F6,#1D4ED8)'],
        'indirect_bonus' => ['label' => 'Indirect Bonus', 'icon' => 'las la-users',           'color' => '#8B5CF6', 'bg' => 'linear-gradient(135deg,#8B5CF6,#6D28D9)'],
        'upgrade_bonus'  => ['label' => 'Upgrade Bonus',  'icon' => 'las la-arrow-circle-up', 'color' => '#F59E0B', 'bg' => 'linear-gradient(135deg,#F59E0B,#D97706)'],
        'unilevel_bonus' => ['label' => 'Unilevel Bonus', 'icon' => 'las la-layer-group',     'color' => '#10B981', 'bg' => 'linear-gradient(135deg,#10B981,#059669)'],
        'awards'         => ['label' => 'Awards',          'icon' => 'las la-trophy',          'color' => '#F97316', 'bg' => 'linear-gradient(135deg,#F97316,#EA580C)'],
        'pairing_bonus'  => ['label' => 'Pairing Bonus',  'icon' => 'las la-code-branch',     'color' => '#EC4899', 'bg' => 'linear-gradient(135deg,#EC4899,#BE185D)', 'requires_purchase' => true],
    ];

    public function index()
    {
        $pageTitle             = 'Bonus Transfer';
        $user                  = $this->authUser();
        $hasPurchasedThisMonth = $this->hasMonthlyPurchase($user->id);
        $transfers             = Transfer::where('user_id', $user->id)->latest()->paginate(15);
        $bonusFields           = $this->bonusFields;
        $totalTransferable     = $this->computeTotalTransferable($user, $hasPurchasedThisMonth);

        return view('Template::user.bonus_transfer', compact(
            'pageTitle', 'user', 'bonusFields', 'hasPurchasedThisMonth', 'transfers', 'totalTransferable'
        ));
    }

    public function transfer(Request $request)
    {
        $request->validate([
            'bonus_field' => 'required|in:' . implode(',', array_keys($this->bonusFields)),
            'amount'      => 'required|numeric|min:0.01',
            'password'    => 'required|string',
        ]);

        $user   = $this->authUser();
        $field  = $request->bonus_field;
        $amount = (float) $request->amount;
        $config = $this->bonusFields[$field];

        if (!Hash::check($request->password, $user->password)) {
            return back()
                ->withInput($request->only('bonus_field', 'amount'))
                ->withErrors(['password' => 'Incorrect password. Please try again.']);
        }

        if (!empty($config['requires_purchase']) && !$this->hasMonthlyPurchase($user->id)) {
            $notify[] = ['error', 'You need at least one purchase this month to transfer your Pairing Bonus.'];
            return back()->withNotify($notify);
        }

        if ((float) ($user->$field ?? 0) < $amount) {
            $notify[] = ['error', 'Insufficient ' . $config['label'] . ' balance.'];
            return back()->withNotify($notify);
        }

        DB::transaction(function () use ($user, $field, $amount, $config) {
            $user->decrement($field, $amount);
            $user->increment('balance', $amount);

            // Log to transactions — balance is now updated in-memory by increment()
            $trx               = new Transaction();
            $trx->user_id      = $user->id;
            $trx->amount       = $amount;
            $trx->charge       = 0;
            $trx->trx_type     = '+';
            $trx->details      = 'Bonus Transfer from ' . $config['label'];
            $trx->remark       = 'bonus_transfer';
            $trx->trx          = getTrx();
            $trx->bonus_type   = 6;
            $trx->post_balance = $user->balance;
            $trx->save();

            Transfer::create([
                'user_id'      => $user->id,
                'source_field' => $field,
                'source_label' => $config['label'],
                'amount'       => $amount,
                'status'       => Transfer::STATUS_COMPLETED,
            ]);
        });

        $notify[] = ['success', getAmount($amount) . ' transferred from ' . $config['label'] . ' to your Money Box.'];
        return back()->withNotify($notify);
    }

    public function transferAll(Request $request)
    {
        $request->validate(['password' => 'required|string']);

        $user = $this->authUser();

        if (!Hash::check($request->password, $user->password)) {
            return back()->withErrors(['password' => 'Incorrect password. Please try again.'])
                         ->with('reopen_all_modal', true);
        }

        $hasPurchase = $this->hasMonthlyPurchase($user->id);

        // Collect eligible transfers before opening the DB transaction
        $eligible = [];
        foreach ($this->bonusFields as $field => $config) {
            $amount = (float) ($user->$field ?? 0);
            if ($amount <= 0) {
                continue;
            }
            if (!empty($config['requires_purchase']) && !$hasPurchase) {
                continue;
            }
            $eligible[] = ['field' => $field, 'config' => $config, 'amount' => $amount];
        }

        if (empty($eligible)) {
            $notify[] = ['warning', 'No eligible bonus balance to transfer.'];
            return back()->withNotify($notify);
        }

        $total = (float) array_sum(array_column($eligible, 'amount'));

        DB::transaction(function () use ($user, $eligible, $total) {
            $runningBalance = (float) $user->balance;
            $now            = now();
            $txRecords      = [];
            $trfRecords     = [];

            foreach ($eligible as $item) {
                $user->decrement($item['field'], $item['amount']);
                $runningBalance += $item['amount'];

                // One transaction entry per source so the audit trail is unambiguous
                $txRecords[] = [
                    'user_id'      => $user->id,
                    'amount'       => $item['amount'],
                    'charge'       => 0,
                    'trx_type'     => '+',
                    'details'      => 'Bonus Transfer from ' . $item['config']['label'],
                    'remark'       => 'bonus_transfer',
                    'trx'          => getTrx(),
                    'bonus_type'   => 6,
                    'post_balance' => $runningBalance,
                    'created_at'   => $now,
                    'updated_at'   => $now,
                ];

                $trfRecords[] = [
                    'user_id'      => $user->id,
                    'source_field' => $item['field'],
                    'source_label' => $item['config']['label'],
                    'amount'       => $item['amount'],
                    'status'       => Transfer::STATUS_COMPLETED,
                    'created_at'   => $now,
                    'updated_at'   => $now,
                ];
            }

            $user->increment('balance', $total);
            Transaction::insert($txRecords);
            Transfer::insert($trfRecords);
        });

        $notify[] = ['success', getAmount($total) . ' total transferred to your Money Box.'];
        return back()->withNotify($notify);
    }

    public function checkPurchase()
    {
        $user  = $this->authUser();
        $has   = $this->hasMonthlyPurchase($user->id);
        $month = now()->format('F Y');

        $order = null;
        if ($has) {
            $order = Order::where('user_id', $user->id)
                ->where('status', '!=', Status::ORDER_CANCELED)
                ->whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->latest()
                ->select(['invoice_code', 'total_amount', 'created_at'])
                ->first();
        }

        return response()->json([
            'eligible' => $has,
            'month'    => $month,
            'message'  => $has
                ? "You have a qualifying purchase in {$month}. You can transfer your Pairing Bonus."
                : "No qualifying purchase found for {$month}. Make at least one purchase this month to unlock Pairing Bonus transfer.",
            'order'    => $order ? [
                'invoice' => $order->invoice_code,
                'amount'  => getAmount($order->total_amount),
                'date'    => $order->created_at->format('M d, Y'),
            ] : null,
        ]);
    }

    /** @return User */
    private function authUser(): User
    {
        return auth()->user();
    }

    private function hasMonthlyPurchase(int $userId): bool
    {
        return Order::where('user_id', $userId)
            ->where('status', '!=', Status::ORDER_CANCELED)
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->exists();
    }

    private function computeTotalTransferable(User $user, bool $hasPurchase): float
    {
        $total = 0.0;
        foreach ($this->bonusFields as $field => $config) {
            if (!empty($config['requires_purchase']) && !$hasPurchase) {
                continue;
            }
            $total += (float) ($user->$field ?? 0);
        }
        return $total;
    }
}
