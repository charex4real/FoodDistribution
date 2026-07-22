<?php

namespace App\Console\Commands;

use App\Models\AdminAutoship;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * SweepAutoshipCommand
 *
 * Artisan command: autoship:sweep
 *
 * Runs daily at 23:00; acts only when it is BOTH the last calendar
 * day of the month AND within the final hour (23:xx).
 * Any user with a non-zero autoship balance that was not transferred
 * to their Money Box loses that balance — it is recorded in
 * admin_autoship for admin visibility and the user's wallet is zeroed.
 *
 * Scheduled: daily at 23:00 via routes/console.php
 * Manual   : php artisan autoship:sweep [--force]
 */
class SweepAutoshipCommand extends Command
{
    protected $signature   = 'autoship:sweep {--force : Skip date/hour guards and run immediately}';
    protected $description = 'Sweep unclaimed autoship balances to admin on the last hour of the last day of the month.';

    public function handle(): int
    {
        $now           = now();
        $isLastDay     = $now->isLastOfMonth();
        $isLastHour    = $now->hour === 23;   // 23:00 – 23:59

        if (!$this->option('force') && (!$isLastDay || !$isLastHour)) {
            $this->info(sprintf(
                'Guard: last_day=%s last_hour=%s — nothing to do. Use --force to override.',
                $isLastDay  ? 'YES' : 'NO',
                $isLastHour ? 'YES' : 'NO'
            ));
            return self::SUCCESS;
        }

        $month   = now()->format('Y-m');
        $swept   = 0;
        $total   = 0.0;
        $errors  = 0;

        $this->info("Running autoship sweep for {$month}…");

        User::where('autoship', '>', 0)
            ->chunkById(200, function ($users) use ($month, &$swept, &$total, &$errors) {
                foreach ($users as $user) {
                    $amount = round((float) $user->autoship, 2);
                    if ($amount <= 0) {
                        continue;
                    }

                    try {
                        DB::transaction(function () use ($user, $amount, $month) {
                            $trxRef = getTrx();

                            AdminAutoship::create([
                                'user_id'  => $user->id,
                                'amount'   => $amount,
                                'month'    => $month,
                                'swept_at' => now(),
                                'trx'      => $trxRef,
                            ]);

                            $user->autoship = 0;
                            $user->save();

                            $txn               = new Transaction();
                            $txn->user_id      = $user->id;
                            $txn->amount       = $amount;
                            $txn->charge       = 0;
                            $txn->trx_type     = '-';
                            $txn->remark       = 'autoship_sweep';
                            $txn->details      = 'Autoship balance expired — unclaimed at end of ' . $month;
                            $txn->trx          = $trxRef;
                            $txn->post_balance = 0;
                            $txn->save();
                        });

                        $swept++;
                        $total += $amount;
                    } catch (\Throwable $e) {
                        $errors++;
                        Log::error('[AutoshipSweep] Failed for user.', [
                            'user_id' => $user->id,
                            'amount'  => $amount,
                            'error'   => $e->getMessage(),
                        ]);
                    }
                }
            });

        $this->info("Swept {$swept} user(s) · Total: {$total} · Errors: {$errors}");

        Log::info('[AutoshipSweep] Complete.', [
            'month'  => $month,
            'swept'  => $swept,
            'total'  => $total,
            'errors' => $errors,
        ]);

        return $errors > 0 ? self::FAILURE : self::SUCCESS;
    }
}
