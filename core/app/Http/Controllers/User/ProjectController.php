<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Matrix;
use App\Models\Project;
use App\Models\PvLog;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProjectController extends Controller
{
    // This is Charles. just folow my comments
    public function index()
    {
        $pageTitle      = 'My Project';
        $user           = auth()->user();
        $currentProject = $user->project_id ? Project::find($user->project_id) : null;

        /* Only show projects at or above the user's current tier (hide downgradable options)
        */
        $query = Project::where('status', true)->orderBy('sort_order')->orderBy('amount');
        if ($currentProject) {
            $query->where('sort_order', '>=', $currentProject->sort_order);
        }
        $projects = $query->get(); 

        return view('Template::user.my_project', compact('pageTitle', 'user', 'currentProject', 'projects'));
    }

    public function upgrade(Request $request)
    {
        $request->validate([
            'project_id' => 'required|integer|exists:projects,id',
        ]);

        $userId          = auth()->id();
        $targetProjectId = (int) $request->project_id;

        try {
            DB::transaction(function () use ($userId, $targetProjectId) {
                // Lock the user row to prevent concurrent double-upgrades
                $user = User::lockForUpdate()->findOrFail($userId);

                $currentProject = $user->project_id ? Project::find($user->project_id) : null;

                $targetProject = Project::where('id', $targetProjectId)
                    ->where('status', true)
                    ->where('upgrade_allowed', true)
                    ->lockForUpdate()
                    ->first();

                if (!$targetProject) {
                    throw new \RuntimeException('This project is not available for upgrade.');
                }

                // Idempotency: already on this project
                if ((int) $user->project_id == $targetProject->id) {
                    throw new \RuntimeException('You are already subscribed to this project.');
                }

                // Only allow moving to a higher-tier project
                if ($currentProject && $targetProject->sort_order <= $currentProject->sort_order) {
                    throw new \RuntimeException('You can only upgrade to a higher-tier project.');
                }

                // Cost is the price difference (user already paid for lower tier)
                $currentAmount = $currentProject ? (float) $currentProject->amount : 0.0;
                $upgradeCost = round((float) $targetProject->amount - $currentAmount, 2);

                if ($upgradeCost <= 0) {
                    throw new \RuntimeException('Upgrade cost is invalid. Please contact support on whatsapp.');
                }
                // 
                $visaBalance = (float) ($user->visa ?? 0);
                if ($visaBalance < $upgradeCost) {
                    throw new \RuntimeException(
                        'Insufficient VISA wallet balance. You need '
                        . gs('cur_sym') . showAmount($upgradeCost, currencyFormat: false)
                        . ' but have '
                        . gs('cur_sym') . showAmount($visaBalance, currencyFormat: false) . '.'
                    );
                }

                $trx = getTrx(12);

                // 1. Debit visa wallet and switch project
                $user->visa      -= $upgradeCost;
                $user->project_id = $targetProject->id;
                $user->save();

                // 2. Debit transaction log (visa wallet)
                $txn               = new Transaction();
                $txn->user_id      = $user->id;
                $txn->amount       = $upgradeCost;
                $txn->charge       = 0;
                $txn->trx_type     = '-';
                $txn->bonus_type     = 10;
                $txn->details      = 'Project upgrade to ' . $targetProject->title;
                $txn->remark       = 'project_upgrade';
                $txn->trx          = $trx;
                $txn->post_balance = $user->visa;
                $txn->save();



                // 3.) Credit the upliner the difference in direct bonus commission 
                $currentDirecCommisison = $currentProject ? (float) $currentProject->direct_commisison : 0.0;

                $direct_commisison = round((float) $targetProject->direct_commisison - $currentDirecCommisison, 2);
                // the section is under reviews.
                // Some user have no Project so this takes care of the errors 
                $current_Project_title = $currentProject ? $currentProject->title : null;

                $details ='Direct bonus gotten from '.$user->username.' upgrading to  '.$targetProject->title.' from '.$current_Project_title;

                directBonus($user, $details, $trx, $direct_commisison);

                // 4.) Upgrade bonus to sponsor (percentage difference in PVs)
                // $project->upgrade_bonus is in % percentage.
                // so i will get the difference in PV.

                $currentPV = $currentProject ? $currentProject->pv : 0;
                // also we have to be sure admin did not post the wrong pv or nagetive pv
                $targetPV = $targetProject->pv ?? 0;

                $diffPV = $targetPV - $currentPV;
                // now get the pv to the percentage as set by the admin to the 
                $uplinePVDiff = round($diffPV * ((int)$targetProject->upgrad_bonus / 100));

                $details2 =$user->username.' upgradedto  '.$targetProject->title.' from '.$current_Project_title;

                
                upLinePvOnUpgrade($user, $uplinePVDiff, $details2);
               

                // 5.) Propagate the PV *difference* up the matrix tree

                /*
                // will resume this in the future
                $pvDiff = round(
                    (int) $targetProject->pv - ($currentProject ? (int) $currentProject->pv : 0),
                    2
                );
                if ($pvDiff > 0) {
                    $this->propagatePv(
                        $user,
                        $pvDiff,
                        $user->username . ' upgraded to ' . $targetProject->title
                    );
                }
                */

                // 5. Cash back difference credited to product_wallet
                //    New cash back - old cash back (what user already received at registration)
                $newCashBack = round((float) $targetProject->cash_back / 100 * (float) $targetProject->amount, 2);
                $oldCashBack = $currentProject
                    ? round((float) $currentProject->cash_back / 100 * (float) $currentProject->amount, 2)
                    : 0.0;
                $cashBackDiff = round($newCashBack - $oldCashBack, 2);

                if ($cashBackDiff > 0) {
                    processCashBack($user, $upgradeCost, 'upgrade to ' . $targetProject->title);
                }
            });
        } catch (\RuntimeException $e) {
            $notify[] = ['error', $e->getMessage()];
            return back()->withNotify($notify);
        } 
        /*catch (\Throwable $e) {
            $notify[] = ['error', 'Something went wrong. Please try again.'];
            return back()->withNotify($notify);
        }
        */

        $notify[] = ['success', 'Your project has been upgraded successfully.'];
        return back()->withNotify($notify);
    }

    


    /**
     * Walk up the binary tree from $user and credit each ancestor with the PV difference.
     * Mirrors the logic in updatePV() but accepts a custom amount instead of $user->project->pv.
     */
    private function propagatePv(User $user, int $pvAmount, string $details): void
    {
        $userMatrix = Matrix::where('stage_id', 1)->where('user_id', $user->id)->first();
        if (!$userMatrix) {
            return;
        }

        $childId  = $user->id;
        $parentId = (int) $userMatrix->parent_id;

        while ($parentId > 0) {
            $parentMatrix = Matrix::where('stage_id', 1)->where('user_id', $parentId)->first();
            if (!$parentMatrix) {
                break;
            }
            if ((int) $parentMatrix->left == $childId) {
                $parentMatrix->pv_left         += $pvAmount;
                $parentMatrix->pv_left_pairing += $pvAmount;
                $position                = 1;
            } else {
                $parentMatrix->pv_right         += $pvAmount;
                $parentMatrix->pv_right_pairing += $pvAmount;
                $position                 = 2;
            }

            $parentMatrix->save();
            
            pvLog($parentMatrix->user_id, $pvAmount, $position, '+', $details);

            $childId  = $parentMatrix->user_id;
            $parentId = (int) $parentMatrix->parent_id;
        }
    }
}
