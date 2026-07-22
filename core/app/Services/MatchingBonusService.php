<?php

namespace App\Services;

use App\Constants\Status;
use App\Contracts\MatchingBonusServiceInterface;
use App\Models\Matrix;
use App\Models\MatchingBonusLog;
use App\Models\Order;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * MatchingBonusService
 *
 * Core matching-bonus engine. One "match" requires MATCHING_NUMBER PV
 * accumulated on BOTH the left and right pairing legs of a binary matrix.
 * Each match earns the user an amount equal to their project's pairing_per_day.
 *
 * Autoship split rule:
 *   If the user has NOT purchased a product in the current calendar month:
 *     80% → matching_bonus wallet
 *     20% → autoship wallet (locked until a monthly purchase is made)
 *   If the user HAS purchased at least once this month:
 *     100% → matching_bonus wallet (no autoship deduction)
 */
class MatchingBonusService implements MatchingBonusServiceInterface
{
    /**
     * PV required on EACH leg to earn one match.
     */
    public const MATCHING_NUMBER = 50;

    /**
     * Fraction paid to matching_bonus when autoship requirement is unmet.
     * The remainder (1 - MATCHING_RATIO) goes to autoship.
     */
    private const MATCHING_RATIO = 0.80;

    // -----------------------------------------------------------------------

    public function calculateMatches(float $pvLeft, float $pvRight): int
    {
        if ($pvLeft < self::MATCHING_NUMBER || $pvRight < self::MATCHING_NUMBER) {
            return 0;
        }
        // this picks the minimum noumber so as to calculate the total match you can get.

        return (int) floor(min($pvLeft, $pvRight) / self::MATCHING_NUMBER);
    }

    /**
     * Returns true if the user has at least one non-cancelled product order
     * in the current calendar month — i.e. the autoship requirement is met.
     */
    public function hasAutoshipFulfilled(int $userId): bool
    {
        return Order::where('user_id', $userId)
            ->where('status', '!=', Status::ORDER_CANCELED)
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->exists();
    }

    public function processMatrix(Matrix $matrix): ?MatchingBonusLog
    {
        $matrixId = $matrix->id;
        //dd($matrix);

        try {
            return DB::transaction(function () use ($matrixId) {
                $matrix = Matrix::lockForUpdate()->find($matrixId);

                if (!$matrix) {
                    Log::warning('[MatchingBonus] Matrix not found inside lock.', ['matrix_id' => $matrixId]);
                    return null;
                }

                $pvLeft  = (float) $matrix->pv_left_pairing;
                $pvRight = (float) $matrix->pv_right_pairing; 
                $matches = $this->calculateMatches($pvLeft, $pvRight);
                

                if ($matches == 0) {
                    return null;
                }

                $user = User::with('project')->lockForUpdate()->find($matrix->user_id);

                if (!$user) {
                    Log::error('[MatchingBonus] User not found.', ['matrix_id' => $matrixId, 'user_id' => $matrix->user_id]);
                    return null;
                }

                if (!$user->project) {
                    Log::warning('[MatchingBonus] User has no project — skipping.', ['user_id' => $user->id, 'matrix_id' => $matrixId]);
                    return null;
                }

                $bonusPerMatch = (float) $user->project->pairing_per_day;
                //dd($bonusPerMatch);
                if ($bonusPerMatch <= 0) {
                    Log::info('[MatchingBonus] Project pairing_per_day is 0 — no payment.', ['user_id' => $user->id, 'matrix_id' => $matrixId]);
                    return null;
                }

                $pvDeducted = $matches * self::MATCHING_NUMBER;
                $totalBonus = round($matches * $bonusPerMatch, 2);
                $trxRef     = getTrx();

                // ── Autoship split ─────────────────────────────────────────
                $fulfilled = $this->hasAutoshipFulfilled($user->id);

                if ($fulfilled) {
                    $matchingAmount = $totalBonus;
                    $autoshipAmount = 0.0;
                } else {
                    $matchingAmount = round($totalBonus * self::MATCHING_RATIO, 2);
                    // derive remainder to avoid floating-point drift (80+20 must = 100)
                    $autoshipAmount = (float) bcsub((string) $totalBonus, (string) $matchingAmount, 2);
                }
                // ──────────────────────────────────────────────────────────

                // 1. Deduct PV from both legs.
                $matrix->pv_left_pairing  = $pvLeft  - $pvDeducted;
                $matrix->pv_right_pairing = $pvRight - $pvDeducted;
                $matrix->save();

                // 2. Credit wallets.
                $user->matching_bonus = bcadd((string) $user->matching_bonus, (string) $matchingAmount, 2);
                if ($autoshipAmount > 0) {
                    $user->autoship = bcadd((string) ($user->autoship ?? '0'), (string) $autoshipAmount, 2);
                }
                $user->save();

                // 3. Persist the match log.
                $log = MatchingBonusLog::create([
                    'user_id'            => $user->id,
                    'matrix_id'          => $matrix->id,
                    'project_id'         => $user->project_id,
                    'matches'            => $matches,
                    'pv_per_match'       => self::MATCHING_NUMBER,
                    'bonus_per_match'    => $bonusPerMatch,
                    'total_bonus'        => $totalBonus,
                    'autoship_fulfilled' => $fulfilled,
                    'autoship_amount'    => $autoshipAmount,
                    'pv_left_before'     => $pvLeft,
                    'pv_right_before'    => $pvRight,
                    'pv_left_after'      => $matrix->pv_left_pairing,
                    'pv_right_after'     => $matrix->pv_right_pairing,
                    'trx'                => $trxRef,
                    'processed_at'       => now(),
                ]);

                // 4a. Matching bonus ledger entry.
                $splitNote = $autoshipAmount > 0 ? ' (80% — autoship active)' : '';

                $txn               = new Transaction();
                $txn->user_id      = $user->id;
                $txn->amount       = $matchingAmount;
                $txn->charge       = 0;
                $txn->trx_type     = '+';
                $txn->remark       = 'matching_bonus';
                $txn->details      = "{$matches} match(es) × " . showAmount($bonusPerMatch, currencyFormat: false)
                                   . ' = ' . showAmount($totalBonus, currencyFormat: false) . $splitNote;
                $txn->trx          = $trxRef;
                $txn->post_balance = $user->matching_bonus;
                $txn->save();

                // 4b. Autoship ledger entry (only when split).
                if ($autoshipAmount > 0) {
                    $txnA               = new Transaction();
                    $txnA->user_id      = $user->id;
                    $txnA->amount       = $autoshipAmount;
                    $txnA->charge       = 0;
                    $txnA->trx_type     = '+';
                    $txnA->remark       = 'autoship';
                    $txnA->details      = 'Autoship hold (20% of matching bonus) — purchase a product this month to unlock';
                    $txnA->trx          = getTrx();
                    $txnA->post_balance = $user->autoship;
                    $txnA->save();
                }

                // 5. Notify user.
                notify($user, 'MATCHING_BONUS', [
                    'amount'       => showAmount($matchingAmount, currencyFormat: false),
                    'matches'      => $matches,
                    'paid_bv'      => $pvDeducted,
                    'post_balance' => showAmount($user->matching_bonus, currencyFormat: false),
                    'trx'          => $trxRef,
                ]);

                Log::info('[MatchingBonus] Processed successfully.', [
                    'user_id'            => $user->id,
                    'matrix_id'          => $matrix->id,
                    'matches'            => $matches,
                    'total_bonus'        => $totalBonus,
                    'matching_amount'    => $matchingAmount,
                    'autoship_amount'    => $autoshipAmount,
                    'autoship_fulfilled' => $fulfilled,
                    'trx'                => $trxRef,
                ]);

                return $log;
            });

        } catch (\Throwable $e) {
            Log::error('[MatchingBonus] processMatrix failed.', [
                'matrix_id' => $matrixId,
                'error'     => $e->getMessage(),
                'trace'     => $e->getTraceAsString(),
            ]);

            throw $e;
        }
    }
}
