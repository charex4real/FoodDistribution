<?php

namespace App\Http\Controllers\User;

use App\Constants\Status;
use App\Http\Controllers\Controller;
use App\Models\AdminNotification;
use App\Models\Project;
use App\Models\Matrix;
use App\Models\Transaction;
use App\Models\User;
use App\Models\UserLogin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use App\Services\MatrixPlacementService2;

class DistributorController extends Controller
{

    public function __construct(MatrixPlacementService2 $matrixService2)
    {
        
        $this->matrixService2 = $matrixService2;

    }

    public function showForm(Request $request)
    {
        $pageTitle = 'Add Distributor';
        $sponsor   = auth()->user();
        $projects  = Project::active()->get();

        $info       = json_decode(json_encode(getIpInfo()), true);
        $mobileCode = @implode(',', $info['code']);
        $countries  = json_decode(file_get_contents(resource_path('views/partials/country.json')));

        // Pre-fill support: binary-list passes ?parent=USERNAME&position=left|right
        // so the sponsor can register directly into a specific open slot.
        $preParent   = $request->query('parent',   '');
        $prePosition = in_array($request->query('position'), ['left', 'right'], true)
                       ? $request->query('position')
                       : '';

        return view('Template::user.distributor', compact(
            'pageTitle', 'sponsor', 'projects', 'mobileCode', 'countries',
            'preParent', 'prePosition'
        ));
    }

    public function checkVisaBalance(Request $request)
    {
        $request->validate(['project_id' => 'required|integer|exists:projects,id']);

        $project = Project::findOrFail($request->project_id);
        $user    = auth()->user();
        $balance = (float) ($user->visa ?? 0);
        $cost    = (float) $project->amount;

        return response()->json([
            'success'    => $balance >= $cost,
            'balance'    => $balance,
            'cost'       => $cost,
            'balance_fmt'=> gs('cur_sym') . showAmount($balance, currencyFormat: false),
            'cost_fmt'   => gs('cur_sym') . showAmount($cost, currencyFormat: false),
            'project'    => $project->title,
        ]);
    }

    public function store(Request $request)
    { 
        $passwordValidation = Password::min(6);
        if (gs('secure_password')) {
            $passwordValidation = $passwordValidation->mixedCase()->numbers()->symbols()->uncompromised();
        }

        $countryData  = (array) json_decode(file_get_contents(resource_path('views/partials/country.json')));
        $countryCodes = implode(',', array_keys($countryData));
        $mobileCodes  = implode(',', array_column($countryData, 'dial_code'));
        $countries    = implode(',', array_column($countryData, 'country'));

        $request->validate([
            'username'    => 'required|unique:users|min:6|regex:/^[A-Za-z0-9_]+$/',
            'email'       => 'required|email',
            'password'    => ['required', 'confirmed', $passwordValidation],
            'firstname'   => 'required|string|max:100',
            'lastname'    => 'required|string|max:100',
            'country_code'=> 'required|in:' . $countryCodes,
            'country'     => 'required|in:' . $countries,
            'mobile_code' => 'required|in:' . $mobileCodes,
            'mobile'      => 'required',
            'address'     => 'required|string|max:255',
            'state'       => 'required|string|max:100',
            'city'        => 'required|string|max:100',
            'project_id'  => 'required|integer|exists:projects,id',
            'referBy'     => 'nullable|string|max:100',
            'parent'      => 'nullable|string|max:100',
            'position'    => 'nullable|in:left,right',
        ], [
            'username.regex'  => 'Username may only contain letters, numbers and underscores.',
            'project_id.required' => 'Please select a project.',
        ]);

        $sponsor = auth()->user();
        $project = Project::findOrFail($request->project_id);

        // Visa balance check
        $visaBalance = (float) ($sponsor->visa ?? 0);
        $cost        = (float) $project->amount;

        if ($visaBalance < $cost) {
            $notify[] = ['error', 'Insufficient VISA wallet balance. You need ' . gs('cur_sym') . showAmount($cost, currencyFormat: false) . ' but have ' . gs('cur_sym') . showAmount($visaBalance, currencyFormat: false) . '.'];
            return back()->withNotify($notify)->withInput($request->except('password', 'password_confirmation'));
        }

        // Resolve referrer
        $referrerId = $sponsor->id;
        if ($request->filled('referBy')) {
            $refUser = User::where('username', $request->referBy)->first();
            if (!$refUser) {
                $notify[] = ['error', 'Referral username not found in the system.'];
                return back()->withNotify($notify)->withInput($request->except('password', 'password_confirmation'));
            }
            $referrerId = $refUser->id;
        }

        DB::beginTransaction();
        try {
            $parentId = 0;
            $position = 0;

            // Validate parent placement if provided
            if ($request->filled('parent')) {
                $parentUser = User::where('username', $request->parent)->first();
                if (!$parentUser) {
                    $notify[] = ['error', 'Parent username not found in the network.'];
                    return back()->withNotify($notify)->withInput($request->except('password', 'password_confirmation'));
                }

                $parentMatrix = Matrix::where('user_id', $parentUser->id)
                    ->where($request->position, 0)
                    ->where('stage_id', 1)
                    ->where('is_active', 1)
                    ->first();
                // when you go life remmember to check if the parent has an active matrix and the position is available
                if (!$parentMatrix) {
                    $notify[] = ['error', 'Parent\'s ' . $request->position . ' position is already taken.'];
                    return back()->withNotify($notify)->withInput($request->except('password', 'password_confirmation'));
                }

                $parentId = $parentUser->id;
                $position = $request->position;
            } else {
                // No parent specified — BFS through sponsor's downline to find first open slot
                $sponsorMatrix = Matrix::where('user_id', $sponsor->id)
                    ->where('stage_id', 1)
                    ->first();
                    //->where('is_active', 1)

                if (!$sponsorMatrix) {
                    $notify[] = ['error', 'Your matrix account is not active. Please contact support.'];
                    return back()->withNotify($notify)->withInput($request->except('password', 'password_confirmation'));
                }

                $placement = $this->matrixService2->findDownline_reg($sponsorMatrix, 1);

                if (!$placement) {
                    $notify[] = ['error', 'No open slot found in your downline. Please specify a parent and position manually.'];
                    return back()->withNotify($notify)->withInput($request->except('password', 'password_confirmation'));
                }

                $parentId = $placement->user_id;
                $position = ((int) $placement->left === 0) ? 'left' : 'right';
            }

            // Create the new user
            $trx  = getTrx(20);
            $newUser = new User();
            $newUser->username     = $request->username;
            $newUser->email        = $request->email;
            $newUser->password     = Hash::make($request->password);
            $newUser->firstname    = $request->firstname;
            $newUser->lastname     = $request->lastname;
            $newUser->country_code = $request->country_code;
            $newUser->country_name = $request->country;
            //$newUser->mobile_code  = $request->mobile_code ?? null;
            $newUser->dial_code    = $request->mobile_code ?? 234; 
            $newUser->mobile       = $request->mobile;
            $newUser->address      = $request->address;
            $newUser->state        = $request->state;
            $newUser->city         = $request->city;
            $newUser->ref_by       = $referrerId;
            $newUser->pos_id       = 1;
            $newUser->trx          = $trx;
            $newUser->section      = $parentId ? 1 : 1;
            $newUser->project_id   = $project->id;
            //gs('kv') ? Status::NO : Status::YES;
            $newUser->kv = Status::NO;
            $newUser->ev = Status::NO;
            $newUser->sv = Status::NO;
            $newUser->ts = Status::DISABLE;
            $newUser->tv = Status::ENABLE;
            $newUser->status = Status::USER_ACTIVE;
            $newUser->profile_complete = Status::YES;
            $newUser->save();

            // Place in matrix
            $matrix            = new Matrix();
            $matrix->user_id   = $newUser->id;
            $matrix->parent_id = $parentId;
            $matrix->position  = $position;
            $matrix->stage_id  = 1;
            $matrix->save();

            // Link new user into the parent's left/right slot so the tree is traversable
            if ($parentId && in_array($position, ['left', 'right'])) {
                Matrix::where('user_id', $parentId)
                    ->where('stage_id', 1)
                    ->update([$position => $newUser->id]);
            }

            // Debit sponsor's visa wallet FIRST — cash back is only allocated after this deduction
            $sponsor->visa -= $cost;
            $sponsor->save();

            // Record debit transaction for sponsor's visa wallet
            $txn              = new Transaction();
            $txn->user_id     = $sponsor->id;
            $txn->amount      = $cost;
            $txn->post_balance = $sponsor->visa;
            $txn->charge      = 0;
            $txn->trx_type    = '-';
            $txn->remark      = 'distributor_registration';
            $txn->details     = 'Registered distributor: ' . $newUser->username . ' on ' . $project->title;
            $txn->trx         = $trx;
            $txn->save();

            $newUser->keyed_in_by = $sponsor->id;
            $newUser->save();

            $details = 'direct bonus gotten from username: '.$newUser->username;
            directBonus($newUser, $details);

            $dets = $newUser->username . ' Subscribed to ' . $newUser->project->title . ' Project.';
            updatePV($newUser, $dets);

            // Cash back credited to the new user's product_wallet after visa deduction is settled
            processCashBack($newUser, (float) $project->amount, $project->title . ' subscription');

            // Key-In Bonus: 2% of registration fee to the sponsor who keyed in the registration
            $keyInBonus = round($cost * 0.02, 2);
            if ($keyInBonus > 0) {
                $sponsor->increment('key_in_bonus', $keyInBonus);
                $sponsor->refresh();
                balance_TransactionReturn(
                    $sponsor->id,
                    'Key-in bonus: registered ' . $newUser->username,
                    $keyInBonus,
                    'key_in_bonus',
                    $trx,
                    $sponsor->key_in_bonus
                );
            }



            // Admin notification
            $adminNotification            = new AdminNotification();
            $adminNotification->user_id   = $newUser->id;
            $adminNotification->title     = 'New distributor registered by ' . $sponsor->username;
            $adminNotification->click_url = urlPath('admin.users.detail', $newUser->id);
            $adminNotification->save();

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            //$notify[] = ['error', 'Something went wrong. Please try again.'];
            $notify[] = ['error', 'Error: ' . $e];
            return back()->withNotify($notify)->withInput($request->except('password', 'password_confirmation'));
            //return back()->withNotify($notify)->withInput($request->except('password', 'password_confirmation'));
        }

        $notify[] = ['success', 'Distributor ' . $newUser->username . ' registered successfully! ' . gs('cur_sym') . showAmount($cost, currencyFormat: false) . ' debited from your VISA wallet.'];
        return back()->withNotify($notify);
    }
}
