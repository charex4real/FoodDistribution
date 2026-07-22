<?php

namespace App\Console\Commands;

use App\Jobs\ProcessMatchingBonusJob;
use App\Models\Matrix;
use App\Services\MatchingBonusService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

/**
 * DispatchMatchingBonusCommand
 *
 * Artisan command: matching:dispatch
 *
 * Queries every active matrix row where BOTH pv_left_pairing and
 * pv_right_pairing are >= MATCHING_NUMBER, then dispatches one
 * ProcessMatchingBonusJob per matrix onto the [matching] queue.
 *
 * The query is chunked to avoid loading millions of rows into memory at once.
 * The actual PV deduction, crediting, and logging happen inside the job —
 * this command is a pure dispatcher.
 *
 * Scheduled : daily at 01:00 via routes/console.php
 * cPanel     : curl -s "https://yourdomain.com/matchingDispatch?token=CRON_SECRET"
 */
class DispatchMatchingBonusCommand extends Command
{
    protected $signature   = 'matching:dispatch';
    protected $description = 'Dispatch matching-bonus jobs for all eligible user matrices.';

    public function handle(): int
    {
        $threshold = MatchingBonusService::MATCHING_NUMBER;
        $dispatched = 0;

        $this->info("Querying matrices with pv_left_pairing >= {$threshold} AND pv_right_pairing >= {$threshold}...");

        try {
            Matrix::where('is_active', true)
                ->where('pv_left_pairing',  '>=', $threshold)
                ->where('pv_right_pairing', '>=', $threshold)
                ->chunkById(200, function ($matrices) use (&$dispatched) {
                    foreach ($matrices as $matrix) {
                        ProcessMatchingBonusJob::dispatch($matrix);
                        $dispatched++;
                    }
                });
        } catch (\Throwable $e) {
            Log::error('[MatchingBonus] DispatchMatchingBonusCommand failed.', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            $this->error('Dispatch failed: ' . $e->getMessage());
            return self::FAILURE;
        }

        $this->info("Dispatched {$dispatched} matching-bonus job(s) to the [matching] queue.");

        Log::info('[MatchingBonus] Dispatch complete.', ['dispatched' => $dispatched]);

        return self::SUCCESS;
    }
}
