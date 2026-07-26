<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\Product;
use App\Models\User;

class UnilevelService
{
    /**
     * Distribute product repurchase bonus up the ref_by chain based on the
     * generations allocated to the purchasing user's project.
     *
     * Generation numbering:
     *   Gen 1 → the user who made the purchase ($user)
     *   Gen 2 → $user->ref_by (direct sponsor)
     *   Gen N → N-1 levels above the purchaser
     */ 

    public function process(Invoice $invoice, User $user, Product $product, int $quantity, string $trx): void
    {
        $project = $user->project;
        //dd($project);
        if (!$project) {
            return;
        }

        // Load only active generations allocated to this project, keyed by generation number 
        $generations = $project->unilevelGenerations()
            ->where('unilevel_generations.status', true)
            ->get()
            ->keyBy('number');

        if ($generations->isEmpty()) {
            return;
        }
       
        $prb = round((float) ($product->prb ?? 0), 2);
        if ($prb <= 0) {
            return;
        }
        
        $pv = (float)$product->pv;

        $maxGenNumber = $generations->keys()->max();

        // Walk the upline chain. Gen 1 is the distributor homself
        $current   = $user;
        $genNumber = 1;

        while ($current != null && $genNumber <= $maxGenNumber) {
            if ($generations->has($genNumber)) {
                $gen         = $generations->get($genNumber);
                $bonusAmount = round(($prb *((float)$gen->percentage / 100) * $pv) * $quantity, 2);
                //dd($bonusAmount);

                if ($bonusAmount > 0) {
                    // Re-fetch with lock to prevent concurrent writes on the same user
                    $recipient = User::lockForUpdate()->find($current->id);
                    if ($recipient) {
                        //$recipient->unilevel_bonus = (float) $recipient->unilevel_bonus + $bonusAmount;
                        $recipient->unilevel_bonus += $bonusAmount;
                        $recipient->save();

                        $details = sprintf(
                            'Unilevel Gen-%d bonus — %s × %d (from %s, %s)',
                            $genNumber,
                            $product->name,
                            $quantity,
                            $user->username,
                            $project->title
                        );
                         
                        unilevelBonusTransaction(
                            $recipient->id,
                            $recipient->unilevel_bonus,
                            $bonusAmount,
                            $details,
                            $trx
                        );
                    }
                }
            }

            // Move one level up the sponsorship chain
            $nextId  = $current->ref_by;
            $current = $nextId ? User::find($nextId) : null;
            $genNumber++;
        }
    }
}
