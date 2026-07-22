<?php

namespace App\Contracts;

use App\Models\Matrix;
use App\Models\MatchingBonusLog;

interface MatchingBonusServiceInterface
{
    /**  
     * Process all available matches for a single matrix row.
     *
     * Acquires a row-level lock, calculates how many matches are available,
     * deducts PV from both legs, credits matching_bonus, writes the log,
     * and records a transaction — all inside a single DB transaction.
     *
     * Returns the log record on success, or null when no matches were available.
     */
    public function processMatrix(Matrix $matrix): ?MatchingBonusLog;

    /**
     * Calculate how many complete matches are available given the two PV leg values.
     * A match requires MATCHING_NUMBER PV on EACH leg.
     */
    public function calculateMatches(float $pvLeft, float $pvRight): int;

    /**
     * Returns true if the user has fulfilled the autoship requirement for the
     * current calendar month (at least one non-cancelled product order).
     * When true, 100% of the match bonus goes to matching_bonus.
     * When false, 80% goes to matching_bonus and 20% to the autoship wallet.
     */
    public function hasAutoshipFulfilled(int $userId): bool;
}
