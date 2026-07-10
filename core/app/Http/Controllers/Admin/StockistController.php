<?php

namespace App\Http\Controllers\Admin;
use App\Models\User;  
use App\Constants\Status;
use App\Models\Stockist;
use App\Models\State;
use App\Models\StockistOrder;
use App\Models\Stockist_store;
use App\Models\StockistInventory;
use App\Models\Stockist_store_history;
use App\Models\Sktransaction;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;



class StockistController extends Controller
{
    public function allStockists()
    {
        $pageTitle = "All Stockists";
        $stockists = Stockist::with('user')->orderBy('id', 'desc')->paginate(getPaginate());
         
        return view('admin.stockist.index', compact('pageTitle', 'stockists'));
    }

    public function store(Request $request, $id = 0)
    { 
        $notify = array();
        $request->validate([
            'user_id'     => 'required|numeric|min:0',
            'activation_fee' => 'required|numeric|min:0',
        ]);
        

        $stock = Stockist::where('user_id', $request->user_id)->first();

        
        if ($stock) {

            $notification = 'User is already a Stockist';
            $notify[] = ['error', $notification];

        }else {
           // dd($request);
           $activation_fee  = $request->activation_fee;
           $user = User::where('id', $request->user_id)->where('status', Status::ACTIVE)->first();

           if ($user) {
                if ($user->balance >=  $request->activation_fee) {
                    $trx          = getTrx();
                    $amount       = $request->activation_fee;

                    DB::beginTransaction();
                    try {
                        
                       
                        $user->deductWallet($amount);
                        // 
                        $post_balance = $user->balance;
                        $user_id  = $user->id;

                        $stockist          = new Stockist();
                        $stockist->user_id = $user_id;
                        $stockist->wallet = $amount;
                        $stockist->status  = Status::ACTIVE;
                        $stockist->save();



                        // Trans for activation
                        $details = "Stockist activation";
                        $remark  = "Stockist_activation_fee";

                        stockistActivation($user_id, $amount, $post_balance, $details, $remark, $trx);

                        DB::commit();
                        $notification = 'User Promoted to Stockist successfully';
                        $notify[] = ['success', $notification];
                    } catch (\Throwable $e) {
                        DB::rollBack();
                        //throw $e;
                        $notification =$e;
                        $notify[] = ['error', $notification];
                    }
                }else{
                    $notification = 'User has Insufficient balance';
                        $notify[] = ['error', $notification];
                }
              
           }

        }
         return back()->withNotify($notify);
       
    }

 
    public function detail($id)
    {
        $stockist = Stockist::with('user')->findOrFail($id);
        //dd($stockist);
        $pageTitle = 'Stockist Details  - '. $stockist->user->username . ' ( '.$stockist->user->firstname. ' '.$stockist->user->lastname. ' )';
        return view('admin.stockist.detail', compact('pageTitle', 'stockist'));
    }

    public function activeStockist()
    {
    	$pageTitle = "All Used Pins";
    	$stockists = Stockist::where('status', Status::ACTIVE)->with('user')->orderBy('id', 'desc')->paginate(getPaginate());
    	return view('admin.stockist.index', compact('pageTitle', 'stockists'));
    }

    public function inactiveStockist()
    {
    	$pageTitle = "All Inactive Stockist";
    	$stockists = Stockist::where('status', Status::INACTIVE)->with('user')->orderBy('id', 'desc')->paginate(getPaginate());
    	return view('admin.stockist.index', compact('pageTitle', 'stockists'));
    }

    public function changeUserStockistStatusddd()
    {
        $pageTitle = 'Banned Users';
        $users = $this->userData('banned');
        return view('admin.users.list', compact('pageTitle', 'users'));
    }


    public function changeUserStockistStatus(Request $request,$id)
    {
        $stockist = Stockist::findOrFail($id);
        if ($stockist->status == Status::ACTIVE) {
            $request->validate([
                'status_reason'=>'required|string|max:255'
            ]);
            $stockist->status = Status::INACTIVE;
            $stockist->status_reason = $request->status_reason;
            $notify[] = ['success','Stockist is now In Active'];
        }else{
            $stockist->status = Status::ACTIVE;
            $stockist->status_reason  = null;
            $notify[] = ['success','Stockist is now Active'];
        }
        $stockist->save();
        return back()->withNotify($notify);

    }


    // the new stockist starts here

    public function dashboard()
    {
        $pageTitle = 'Stockist ';
        $totalStockists = Stockist::count();
        $verifiedStockists = Stockist::where('is_verified', true)->count();
        $activeStockists = Stockist::where('is_active', true)->count();
        
        // Calculate total products quantity across all stockists
        $totalProducts = DB::table('stockist_stores')
            ->join('stockists', 'stockist_stores.stockist_id', '=', 'stockists.id')
            ->sum('stockist_stores.quantity');

        $stockists = Stockist::with(['user', 'stockiststore'])
            ->withCount(['stockiststore as total_products' => function($query) {
                $query->select(DB::raw('COALESCE(SUM(quantity), 0)'));
            }])
            ->latest()
            ->paginate(10);

        return view('admin.stockist.stockist-dashboard', compact(
            'totalStockists',
            'verifiedStockists',
            'activeStockists',
            'totalProducts',
            'stockists',
            'pageTitle'
        ));
    }

    public function activationPage()
    {   
        $pageTitle = 'Stockist ';
        $states = State::orderBy('name')->get();
    
        return view('admin.stockist.stockist-activation', compact('pageTitle', 'states'));
    }

    public function walletTopUpPage(Stockist $stockist)
    {
        $pageTitle = 'Stockist ';
        return view('admin.stockist.stockist-wallet-topup', compact('stockist', 'pageTitle'));
    }

    public function topUpWallet(Request $request, Stockist $stockist)
    {
        $request->validate([
            'amount' => 'required|numeric|min:0',
            'notes' => 'nullable|string|max:500'
        ]);

        try {
            DB::beginTransaction();
            $amount = $request->amount;
            $notes = $request->notes;
            // Update stockist wallet
            $balanceBefore = $stockist->wallet;

            $stockist->wallet += $amount;
            $stockist->save();


            // Create sktransaction record
            Sktransaction::create([
                'stockist_id' => $stockist->id,
                'type' => 'credit',
                'amount' => $amount,
                'balance_before' => $balanceBefore,
                'balance_after' => $stockist->wallet,
                'description' => 'Wallet Top-up by Admin',
                'notes' => $notes,
                'reference' => Sktransaction::generateReference(),
                'metadata' => [
                    'topped_up_by' => auth()->id(),
                    'topped_up_at' => now()->toDateTimeString(),
                    'method' => 'manual'
                ]
            ]);


            
                $notify[] = ["success","Successfully topped up ₦" . number_format($request->amount) . " to {$stockist->business_name}'s wallet."];
            DB::commit();
            return back()->withNotify($notify);
            //return redirect()->route('admin.stockist.dashboard')->with('success', "Successfully topped up ₦" . number_format($request->amount) . " to {$stockist->business_name}'s wallet.");

        } catch (\Exception $e) {
            DB::rollBack();
            $notify[] = ['error', 'Failed to top up wallet: ' . $e->getMessage()];
            return back()->withNotify($notify);
            //return back()->with('error', 'Failed to top up wallet: ' . $e->getMessage());
        }
    }

    public function searchUser(Request $request)
    {
        $request->validate([
            'username' => 'required|string'
        ]);

        $user = User::where('username', $request->username)->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found'
            ]);
        }

        // Check if user is already a stockist
        $existingStockist = Stockist::where('user_id', $user->id)->first();
        
        if ($existingStockist) {
            return response()->json([
                'success' => false,
                'message' => 'User is already a stockist',
                'stockist' => $existingStockist
            ]);
        }

        return response()->json([
            'success' => true,
            'user' => [
                'id' => $user->id,
                'name' => $user->fullname,
                'username' => $user->username,
                'email' => $user->email,
                'balance' => $user->balance,
                'created_at' => $user->created_at->format('M d, Y')
            ]
        ]);
    }

    public function activateStockist(Request $request)
    { 
       // dd($request);

        $request->validate([
            'user_id' => 'required|exists:users,id',
            'state_id' => 'required|exists:states,id', 
            'store_type' => 'required|in:1,2',
            'activation_cost' => 'required|numeric|min:0',
            'business_name' => 'required|string|max:255',
            'business_email' => 'required|email',
            'business_phone' => 'required|string|max:20',
        ]);
        //transaction code
        $trx = getTrx();

        try {
            DB::beginTransaction();

            $user = User::findOrFail($request->user_id);
    
            // Check if user has sufficient balance
            if ($user->balance < $request->activation_cost) {
                $notify[] = ['error', 'User has insufficient balance for activation.'];
                return back()->withNotify($notify);
            }
            $amount =$request->activation_cost;
            // Deduct activation cost from user's balance
            $user->balance -=  $amount;
            $user->save();
            $post_balance = $user->balance;
        

            // Create stockist record

            $stockist = new Stockist();
            $stockist->user_id = $user->id;
            $stockist->store_type = $request->store_type;
            $stockist->wallet = $amount;
            $stockist->state_id = $request->state_id;
            $stockist->business_name = $request->business_name;
            $stockist->business_email = $request->business_email;
            $stockist->business_phone = $request->business_phone;
            $stockist->is_verified = true;
            $stockist->status = Status::ACTIVE;
            $stockist->is_active = true;
            $stockist->verified_at = now();
            $stockist->save();

            // Trans for activation
            $details = "Stockist activation";
            $remark  = "Stockist_activation_fee";

            stockistActivation($user->id, $amount, $post_balance, $details, $remark, $trx);

            DB::commit();
            $notify[] = ['success','Stockist activated successfully!'];
            return redirect()->route('admin.stockist.dashboard')->withNotify($notify);

        } catch (\Exception $e) {
            DB::rollBack();
            $notify[] = ['error','Failed to activate stockist: ' . $e->getMessage()];
            return back()->withNotify($notify);
        
        }
    }

    // In StockistController - update the showDetails method
    public function showDetails(Stockist $stockist)
    {   
        $pageTitle = "Stockist Details";

        // Load relationships with additional data
        $stockist->load([
            'user',
            'stockistStores' => function($query) {
                $query->with('product')->latest();
            },
            'sktransactions' => function($query) {
                $query->latest()->take(10);
            }
        ]);

        // Calculate statistics
        $totalProductsValue = $stockist->stockistStores->sum(function($store) {
            return $store->quantity * ($store->price ?? 0);
        });

        $totalProductsCount = $stockist->stockistStores->sum('quantity');
        $uniqueProducts = $stockist->stockistStores->count();

        // Recent activity (last 7 days) - using sktransactions
        $recentActivity = $stockist->sktransactions()
            ->where('created_at', '>=', now()->subDays(7))
            ->count();

        // If no sktransactions, use stockist stores activity
        if ($recentActivity === 0) {
            $recentActivity = $stockist->stockistStores()
                ->where('created_at', '>=', now()->subDays(7))
                ->count();
        }

        return view('admin.stockist.stockist-details', compact(
            'stockist',
            'totalProductsValue',
            'totalProductsCount',
            'uniqueProducts',
            'recentActivity',
            'pageTitle'
        ));
    }

    

}
