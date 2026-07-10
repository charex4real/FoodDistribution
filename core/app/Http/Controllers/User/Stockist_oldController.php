<?php
 
namespace App\Http\Controllers\User;
use App\Models\User;
use App\Models\PvLog;
use App\Models\Order;
use App\Models\Sorder;
use App\Models\Matrix;
use App\Models\UserExtra;
use App\Models\Stockist;
use App\Models\Invoice;
use App\Models\Product;
use App\Constants\Status;
use App\Models\Transaction;
use App\Models\Stransaction;
use Illuminate\Http\Request;
use App\Models\Stockist_store;
use App\Models\StockistService;
use App\Models\GeneralSetting;
use App\Models\StockistLocation;
use App\Models\InvoiceRedemption;
use App\Models\Stockist_store_record;  
use Illuminate\Validation\Rule;
use App\Lib\GoogleAuthenticator;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class StockistController extends Controller
{

   
    public function redeemForm(){
        return view('Template::user.stockist.redeem');
    }


    // User-facing methods (no auth required)
    public function findStockist()
    {  $pageTitle = "Search Stockist";
        return view('Template::user.stockist.find', compact('pageTitle'));
    }
    public function searchStockists(Request $request)
    {
        $request->validate([
            'city' => 'required|string|max:255',
            'state' => 'required|string|max:255'
        ]);

        try {
            $stockists = StockistLocation::with(['stockist', 'stockist.services'])
                ->where(function($query) use ($request) {
                    $query->where('city', 'like', '%' . $request->city . '%')
                          ->orWhere('state', 'like', '%' . $request->state . '%');
                })
                ->where('is_active', true)
                ->get()
                ->map(function ($location) {
                    $stockist = $location->stockist;
                    
                    return [
                        'id' => $location->id,
                        'stockist_id' => $stockist->id,
                        'name' => $stockist->user->name ?? $stockist->business_name,
                        'business_name' => $stockist->business_name,
                        'business_email' => $stockist->business_email,
                        'business_phone' => $stockist->business_phone,
                        'address_line_1' => $location->address_line_1,
                        'address_line_2' => $location->address_line_2,
                        'city' => $location->city,
                        'state' => $location->state,
                        'country' => $location->country,
                        'postal_code' => $location->postal_code,
                        'latitude' => $location->latitude,
                        'longitude' => $location->longitude,
                        'phone' => $location->phone,
                        'email' => $location->email,
                        'opening_hours' => $location->opening_hours,
                        'is_verified' => $stockist->is_verified,
                        'services' => $stockist->services->pluck('service_name')->toArray(),
                        'full_address' => $location->full_address,
                        'formatted_opening_hours' => $location->formatted_opening_hours
                    ];
                });

            return response()->json($stockists);

        } catch (\Exception $e) {
            \Log::error('Stockist search error: ' . $e->getMessage());
            return response()->json([
                'error' => 'Failed to search for stockists. Please try again.'
            ], 500);
        }
    }
    
   
    // Stockist dashboard methods (protected)
    public function dashboard()
    {   $pageTitle = "Stockist Dashboard";
        // Get the authenticated user's stockist
        $stockist = auth()->user()->stockist;

        // If user doesn't have a stockist record, redirect to setup
        if (!$stockist) {
            return to_route('user.stockist.setup')->with('error', 'Please complete your stockist profile first.');
        }

        $recentRedemptions = $stockist->redemptions()
            ->with(['invoice.order', 'user'])
            ->latest()
            ->take(5)
            ->get();

        $stats = [
            'total_redemptions' => $stockist->redemptions()->count(),
            'today_redemptions' => $stockist->redemptions()->whereDate('created_at', today())->count(),
            'total_amount' => $stockist->redemptions()->sum('total_amount'),
        ];

        return view('Template::user.stockist.dashboard', compact('stockist', 'recentRedemptions', 'stats','pageTitle'));
    }

    public function setup()
    {   $pageTitle = "STOCKIST";
        // If user already has a stockist, redirect to dashboard
        if (auth()->user()->stockist) {
            return redirect()->route('user.stockist.dashboard');
        }

        return view('Template::user.stockist.setup', compact('pageTitle'));
    }

    public function storeSetup(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'company_name' => 'required|string|max:255',
            'email' => 'required|email|unique:stockists,email',
            'phone' => 'required|string|max:20',
            'address' => 'required|string|max:255',
            'city' => 'required|string|max:255',
            'state' => 'required|string|max:255',
            'country' => 'required|string|max:255',
        ]);

        DB::beginTransaction();
        try {
            // Create stockist
            $stockist = Stockist::create([
                'name' => $request->name,
                'company_name' => $request->company_name,
                'email' => $request->email,
                'phone' => $request->phone,
                'is_active' => true,
            ]);

            // Create primary location
            $stockist->locations()->create([
                'address' => $request->address,
                'city' => $request->city,
                'state' => $request->state,
                'country' => $request->country,
                'postal_code' => $request->postal_code,
                'is_primary' => true,
                'is_active' => true,
            ]);

            // Associate stockist with user
            auth()->user()->stockist()->save($stockist);

            DB::commit();

            return redirect()->route('stockist.dashboard')->with('success', 'Stockist profile created successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to create stockist profile: ' . $e->getMessage());
        }
    }
    
    public function verifyInvoice(Request $request)
    {   
        $stockist = auth()->user()->stockist;
        
        if (!$stockist) {
            return response()->json([
                'success' => false,
                'message' => 'Stockist profile not found'
            ], 403);
        } 

        $request->validate([
            'invoice_code' => 'required|string|exists:invoices,invoice_code'
        ]);

 
        $invoice = Invoice::with(['order.items.product', 'redemptions'])
            ->where('invoice_code', $request->invoice_code)
            ->first();

        //dd($invoice);

        if (!$invoice) {
            return response()->json([
                'success' => false,
                'message' => 'Invoice not found'
            ]);
        }


        if ($invoice->is_fully_redeemed) {
            return response()->json([
                'success' => false,
                'message' => 'This invoice has been fully redeemed'
            ]);
        }

        if ($invoice->state_id != $stockist->state_id) {
            return response()->json([
                'success' => false,
                'message' => 'Invoice does not fall within your state <br>Its for Stockist in '.$invoice->state->name
            ]);
        }
 
        return response()->json([
            'success' => true,
            'invoice' => $invoice,
            'remaining_items' => $invoice->remaining_items,
            'original_items' => json_decode($invoice->items, true)
        ]);
    }
    

    public function verifyInvoice1(Request $request)
    {
            $stockist = auth()->user()->stockist;
            
            if (!$stockist) {
                return response()->json([
                    'success' => false,
                    'message' => 'Stockist profile not found'
                ], 403);
            }

            $request->validate([
                'invoice_code' => 'required|string|exists:invoices,invoice_code'
            ]);

            $invoice = Invoice::with(['order.items.product', 'redemptions'])
                ->where('invoice_code', $request->invoice_code)
                ->first();

            if (!$invoice) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invoice not found'
                ]);
            }

            if ($invoice->is_fully_redeemed) {
                return response()->json([
                    'success' => false,
                    'message' => 'This invoice has been fully redeemed'
                ]);
            }

            return response()->json([
                'success' => true,
                'invoice' => $invoice,
                'remaining_items' => $invoice->remaining_items,
                'original_items' => json_decode($invoice->items, true)
            ]);
    }

    private function calculateRemainingItems($invoice){
        
        //dd($invoice->redemptions);
        try {
            // Get all redeemed quantities by product
            $redeemedQuantities = [];
            //dd($invoice);
            foreach ($invoice->redemptions as $redemption) {
                $items = $redemption->redeemed_items;
                if (is_string($items)) {
                    $items = json_decode($items, true);
                
                
                    foreach ($items as $item) {
                        $productId = $item['product_id'];
                        $quantity = $item['quantity'];
                        
                        if (!isset($redeemedQuantities[$productId])) {
                            $redeemedQuantities[$productId] = 0;
                        }
                        $redeemedQuantities[$productId] += $quantity;
                    }
                }
            }

            // Get original quantities
            $originalQuantities = [];
            $itemsData = $invoice->items;
            if (is_string($itemsData)) {
                $itemsData = json_decode($itemsData, true);
            }
            //dd($itemsData);
            
            foreach ($itemsData as $item) {
                $productId = $item['product_id'];
                $quantity = $item['quantity'];
                //dd($productId);
                if (!isset($originalQuantities[$productId])) {
                    $originalQuantities[$productId] = 0;
                }
                $originalQuantities[$productId] += $quantity;
            }


            // Calculate remaining
            $remainingItems = [];
            foreach ($originalQuantities as $productId => $originalQty) {
                $redeemedQty = $redeemedQuantities[$productId] ?? 0;
                $remainingQty = $originalQty - $redeemedQty;
                
                if ($remainingQty > 0) {
                    $remainingItems[$productId] = $remainingQty;
                }
            }

            return $remainingItems;

        } catch (\Exception $e) {
            \Log::error("Error calculating remaining items: " . $e->getMessage());
            return [];
        }


    }
 
    public function processRedemption(Request $request)
    {
        $stockist = auth()->user()->stockist;
        $user = auth()->user();
        
        if (!$stockist) {
            return response()->json([
                'success' => false,
                'message' => 'Stockist profile not found'
            ], 403);
        } 

        $request->validate([
            'invoice_code' => 'required|string|exists:invoices,invoice_code',
            'redeemed_items' => 'required|array',
            'notes' => 'nullable|string'
        ]);

        //get the Invoice
        $invoice = Invoice::where('invoice_code', $request->invoice_code)->first();

        if ($invoice->state_id != $stockist->state_id) {
            return response()->json([
                'success' => false,
                'message' => 'Invoice does not fall within your state <br>Its for Stockist in '.$invoice->state->name
            ]);
        }

        DB::beginTransaction();
        try {
            

            //Get the transaction code from the order.
            //to be use later in the code
            
            $trx = $invoice->order->trx;

            // Validate redemption quantities
            $remainingItems = $invoice->remaining_items;
            $totalAmount = 0;
            $redeemedItems = [];
            foreach ($request->redeemed_items as $productId => $quantity) {
                $quantity = intval($quantity);
                if(isset($remainingItems[$productId])){
                    if ($quantity > $remainingItems[$productId]) {

                        $pName = getProctuctName($productId);
                        throw new \Exception("Product quantity for {$pName } cannot exceed the: {$remainingItems[$productId]}");
                    }


                    if ($quantity > 0) {
                        $product = Product::find($productId);
                        if (!$product) {
                            throw new \Exception("Product not found: {$productId}");
                        }
                        
                        $totalAmount += $product->price * $quantity;
                        $redeemedItems[] = [
                            'product_id' => $productId,
                            'product_name' => $product->name,
                            'quantity' => $quantity,
                            'price' => $product->price
                        ];

                        // 1.) deduct the product from stockist store. 
                        $stockist_store = Stockist_store::where('user_id', auth()->id())->where('product_id', $productId)->first();
                        $stockist_store->quantity -= $quantity;
                        $stockist_store->save();

                        
                        // 2.) record this transaction in Stockist_store_record
                        $this->stockistStore($productId, $invoice->id, $product->price, $quantity);

                        /* 3.) this is where stockist PV allocation
                        and user bonuses will refelect.
                        this bonuses differs for product so we have to drop the code here.

                        */
                       $this->stockist_allocation($stockist, $product, $invoice, $quantity, $trx);

                        

                        $user_dist  = $invoice->order->user;

                        //$user_dist->total_order_amount
                        
                        
                        $ords = (int)$user_dist->total_invoice_redemption;
                       
                        /* 5.) calculate the user Unilel bonus
                         First we get the user that made the purchase
                         next will automate the bonus depend on the plan the user subscribe to
                        */
                          
                         //$this->unilevelBonus($user_dist, $product, $quantity, $trx);
                       
                    }
                }
            }
            // run throught this logic again.
            if (empty($redeemedItems)) {
                throw new \Exception("No items to redeem");
            }

            // Create redemption record
            $redemption = InvoiceRedemption::create([
                'invoice_id' => $invoice->id,
                'stockist_id' => $stockist->id,
                'user_id' => $invoice->order->user_id,
                'redeemed_items' => $redeemedItems,
                'total_amount' => $totalAmount,
                'notes' => $request->notes,
                'redeemed_at' => now()
            ]);

            //credit the stockist wallet.
            $stockist->wallet += $totalAmount;
            $stockist->save();

            //

            //dd($user);
            //record in stockist transaction
            stockistTransaction($stockist, $user, $trx, $totalAmount);
            

            // Check if invoice is fully redeemed
            if ($invoice->fresh()->is_fully_redeemed) { 
                $invoice->is_fully_redeemed = true;
                $invoice->fully_redeemed_at = now();
                $invoice->redeemed_at = now();
                $invoice->save();

                // update the order status
                $order = Order::find($invoice->order_id);
                $order->status = 1; // 1 indicate Redeemed
                $order->save();
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Redemption processed successfully',
                'redemption_id' => $redemption->id,
                'remaining_items' => $invoice->fresh()->remaining_items
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
             //DD($e->getMessage());
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }
    protected function unilevelBonus(User $user, Product $product, $quantity, $trx){
        $plan = $user->plan;
        switch ($plan->id) {
            case 1:
                //elite plan
                $this->eliteUnilevelBonus($user, $product->name, $quantity, $trx);
                break;
        
            default:
                // code...
                break;
        }

        //dd($plan);

    }
    
   
    //Elite Plan
    protected function eliteUnilevelBonus(User $user, $product_name, $quantity, $trx)
    {

        //First set the generation bonus
        $prb  = (((10 * 10) / 100 ) * 500) * $quantity;
        $gen = [];
        $gen[1]  = (((5 * 10) / 100 ) * 500) * $quantity;
        $gen[2]  = (((4 * 10) / 100 ) * 500) * $quantity;
        $gen[3]  = (((3 * 10) / 100 ) * 500) * $quantity;
        $gen[4]  = (((3 * 10) / 100 ) * 500) * $quantity;
        $gen[5]  = (((2 * 10) / 100 ) * 500) * $quantity;
        $gen[6]  = (((2 * 10) / 100 ) * 500) * $quantity;
        $gen[7]  = (((2 * 10) / 100 ) * 500) * $quantity;
        $gen[8]  = (((1 * 10) / 100 ) * 500) * $quantity;
        $gen[9]  = (((2 * 10) / 100 ) * 500) * $quantity;
        $gen[10]  = (((2 * 10) / 100 ) * 500) * $quantity;
        $gen[11]  = (((2 * 10) / 100 ) * 500) * $quantity;
        $gen[12]  = (((3 * 10) / 100 ) * 500) * $quantity;
        $gen[13]  = (((3 * 10) / 100 ) * 500) * $quantity;
        $gen[14]  = (((3 * 10) / 100 ) * 500) * $quantity;
        $gen[15]  = (((3 * 10) / 100 ) * 500) * $quantity;

        //allocate the bonus to the user who purchase the goods.
        $user->unilevel_bonus  +=  $prb;
        $user->save();

        $details = 'Unilevel bonus gotten for re-purchasing '.$quantity.' of '.$product_name;
        // record the transaction
        unilevelBonusTransaction($user->id, $user->unilevel_bonus, $prb , $details, $trx);
        // begin calculation for 15 generation
        $user_id = $user->pos_id;
        if ($user_id != 0) {
        
            while($user_id != "" || $user_id != "0"){
                // the the deliiter
                $i = 1;
                
                $user_matrix = Matrix::where('user_id', $user_id)->first();
                if($user_matrix){
                    $userGen = $user_matrix->user;
                    if ($userGen) {
                        $userGen->unilevel_bonus += $gen[$i];
                        $userGen->save();
                        $des = 'unilevel Bonus bonus gotten from '.$user->username.' re-purchasing '.$quantity.' of '.$product_name;
                        unilevelBonusTransaction($userGen->id, $userGen->unilevel_bonus, $gen[$i] , $des, $trx);
                        $user_id = $user_matrix->parent_id;
                    }
                }else{
                    break;
                }

                
                $i++;  // increase the delimiter
                // test if true then terinate the while loop
                if ($i > 15) break;
            }//end while loop
        } //end if
    }
 
    protected function stockist_allocation(Stockist $stockist, Product $product, Invoice $invoice, $quantity, $trxx){
        $gs = GeneralSetting::first();
 
        $stockist_store_type = $stockist->store_type;

        if ($stockist_store_type == 1) {

            //$percentage = $gs->mini_store;
        }elseif($stockist_store_type == 2){
            $stockist_parent = $stockist->parent;
            //$percentage = $gs->service_center;
        }

        // calculate unileve bonus
        $stockist_rebate = (($product->cost_price * $percentage ) / 100) *  $quantity;
        $user = auth()->user();
        $user->stockist_rebate += $stockist_rebate;
        $user->save();
        // add it in transaction
        stockistRebateTransaction($stockist_rebate, $trxx);


    }

    protected function stockistStore($productId, $invoiceId, $productPrice, $quantity){

        $stockist_store_record = new Stockist_store_record();
        $stockist_store_record->user_id    =  auth()->id();
        $stockist_store_record->product_id =  $productId;
        $stockist_store_record->invoice_id =  $invoiceId;
        $stockist_store_record->price      =  $productPrice;
        $stockist_store_record->quantity   =  $quantity;
        $stockist_store_record->save();
    }


    public function redemptionHistory()
    {
        $stockist = auth()->user()->stockist;
        $pageTitle        = 'Stockists History Dashboard';
        
        if (!$stockist) {
            return redirect()->route('stockist.setup')->with('error', 'Please complete your stockist profile first.');
        }

        $redemptions = $stockist->redemptions()
            ->with(['invoice.order', 'user'])
            ->latest()
            ->paginate(10);

        return view('Template::user.stockist.history', compact('redemptions','pageTitle'));
    }

    public function profile()
    {
        $user = auth()->user();
        $pageTitle        = 'Stockists Profile';
        
        if (!$user->isStockist()) {
            return redirect()->route('home')->with('error', 'You are not registered as a stockist.');
        }

        $stockist = $user->stockist->load(['locations', 'services', 'primaryLocation']);



        return view('Template::user.stockist.profile', compact('stockist','pageTitle'));
    }

    public function updateBasicInfo(Request $request)
    {
        $user = auth()->user();
        
        if (!$user->isStockist()) {
            return response()->json([
                'success' => false,
                'message' => 'You are not registered as a stockist.'
            ], 403);
        }

        $request->validate([
            'business_name' => 'required|string|max:255',
            'business_email' => 'required|email|max:255',
            'business_phone' => 'required|string|max:20',
            'business_description' => 'nullable|string|max:1000',
            'website' => 'nullable|url|max:255',
            'business_registration_number' => 'nullable|string|max:100',
        ]);

        try {
            $user->stockist->update($request->only([
                'business_name', 'business_email', 'business_phone',
                'business_description', 'website', 'business_registration_number'
            ]));

            return response()->json([
                'success' => true,
                'message' => 'Basic information updated successfully!'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update basic information: ' . $e->getMessage()
            ]);
        }
    }

    public function updateLocation(Request $request)
    {
        $user = auth()->user();
        
        if (!$user->isStockist()) {
            return response()->json([
                'success' => false,
                'message' => 'You are not registered as a stockist.'
            ], 403);
        }

        $request->validate([
            'address_line_1' => 'required|string|max:255',
            'address_line_2' => 'nullable|string|max:255',
            'city' => 'required|string|max:255',
            'state' => 'required|string|max:255',
            'country' => 'required|string|max:255',
            'postal_code' => 'nullable|string|max:20',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
        ]);

        DB::beginTransaction();
        try {
            $stockist = $user->stockist;
            
            // Check if primary location exists
            $primaryLocation = $stockist->primaryLocation;
            
            if ($primaryLocation) {
                // Update existing primary location
                $primaryLocation->update($request->only([
                    'address_line_1', 'address_line_2', 'city', 'state', 'country',
                    'postal_code', 'phone', 'email'
                ]));
            } else {
                // Create new primary location
                $stockist->locations()->create(array_merge(
                    $request->only([
                        'address_line_1', 'address_line_2', 'city', 'state', 'country',
                        'postal_code', 'phone', 'email'
                    ]),
                    ['is_primary' => true, 'is_active' => true]
                ));
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Location information updated successfully!'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to update location: ' . $e->getMessage()
            ]);
        }
    }

    public function updateOpeningHours(Request $request)
    {
        $user = auth()->user();
        
        if (!$user->isStockist()) {
            return response()->json([
                'success' => false,
                'message' => 'You are not registered as a stockist.'
            ], 403);
        }

        $request->validate([
            'opening_hours' => 'required|array',
            'opening_hours.*.open' => 'required|string|max:10',
            'opening_hours.*.close' => 'required|string|max:10',
        ]);

        try {
            $stockist = $user->stockist;
            $primaryLocation = $stockist->primaryLocation;
            
            if ($primaryLocation) {
                $primaryLocation->update([
                    'opening_hours' => $request->opening_hours
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Opening hours updated successfully!'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update opening hours: ' . $e->getMessage()
            ]);
        }
    }

    public function updateServices(Request $request)
    {
        $user = auth()->user();
        //dd($request);
        if (!$user->isStockist()) {
            return response()->json([
                'success' => false,
                'message' => 'You are not registered as a stockist.'
            ], 403);
        }

        /*
        $request->validate([
            'serviceName' => 'required|array',
            'serviceName.*' => 'required|string|max:255',
            'serviceName.*' => 'nullable|string|max:500',
            'description' => 'required|array',
            'description.*' => 'required|string|max:255',
            'description.*' => 'nullable|string|max:500',
        ]);
        */

        // Perform validation
        $validatedData = $request->validate([
            'serviceName' => 'required|array',
            'serviceName.*' => 'required|string|max:255',
            'description' => 'required|array',
            'description.*' => 'required|string|max:500',
        ]);

        

        DB::beginTransaction();
        try {
            $stockist = $user->stockist;
            
            // Delete existing services
            $stockist->services()->delete();
            
            // Create new services
           
            $count = count($validatedData['serviceName']);
            // Loop through the arrays and create records
            for ($i=0; $i < $count ; $i++) { 
                $stockist->services()->create([
                        'service_name' => $validatedData['serviceName'][$i],
                        'description' => $validatedData['description'][$i] ?? null,
                        'is_active' => true
                ]);
                
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Services updated successfully!'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to update services: ' . $e->getMessage()
            ]);
        }
    }

    public function getProfileData()
    {
        $user = auth()->user();
        
        if (!$user->isStockist()) {
            return response()->json([
                'success' => false,
                'message' => 'You are not registered as a stockist.'
            ], 403);
        }

        $stockist = $user->stockist->load(['primaryLocation', 'services']);
        
        return response()->json([
            'success' => true,
            'stockist' => $stockist,
            'primary_location' => $stockist->primaryLocation,
            'services' => $stockist->services
        ]);
    }


    public function index()
    {
        $user_id = auth()->id();
        $pageTitle        = 'Stockists Dashboard';
        $stock     = Stockist::where('user_id', $user_id)->first();
        if(!$stock){
            $notify[] = ['error', 'Your are not permitted to visit Stockist page'];
            return to_route('user.home')->withNotify($notify);
        }
       
        $wallet     = Stockist::where('user_id', $user_id)->first()->wallet;

        //$stockist_store = Stockist_store::where('user_id', auth()->id())->with('product')->orderBy('id', 'desc')->get();
        $products    = Product::get();
        return view('Template::user.stock', compact('pageTitle', 'wallet', 'stock', 'products'));

    }

    

    public function redeemProduct(Request $request){
        $request->validate([
            'invoice_code' => 'required|string'
        ]);

        $invoice = Invoice::where('invoice_code', $request->invoice_code)->first();

        if (!$invoice) {
            return back()->with('error', 'Invalid invoice code');
        }

        if ($invoice->redeemed_at) {
            return back()->with('error', 'This invoice has already been redeemed');
        }

        if ($invoice->order->status !== 'paid') {
            return back()->with('error', 'This order is not paid');
        }

        $invoice->update([
            'redeemed_at' => now(),
            'redeemed_by' => auth()->id() // if stockist is authenticated
        ]);

        $invoice->order->update(['status' => 'redeemed']);

        return view('Template::user.stockist.redeem-success', compact('invoice'));
    }
    public function checkCode(Request $request)
    {   
        $request->validate([
            'code'   => 'required|string|min:7'
            
        ]);

        $data = [];
        $dat= Order::where('order_code', $request->code)->where('status', 0)->first();

        if($dat){
            $product = Product::hasCategory()->active()->find($dat->product_id);
            $data['productId'] = $product->id;
            $data['price'] = showAmount($product->price);
            $data['name'] = $product->name;
            $data['description'] = $product->description;
            $data['qty'] = $dat->quantity;
            $data['userId'] = $dat->user_id;
            $data['status'] = $dat->status;
            $data['total'] = showAmount($product->price * $dat->quantity);
            $data['order_code'] = $dat->order_code;
        }

        return response()->json(['data' => $data]);
    } 


    public function purchaseDone(Request $request)
    {
        //dd($request);
        $request->validate([
            'userId'   => 'required|integer',
            'order_code'   => 'required|string',
            'productId'   => 'required|integer',
            
        ]); 
        //dd($request->order_code);

        $order= Order::where('order_code', $request->order_code)->where('product_id', $request->productId)->where('user_id', $request->userId)->where('status', 0)->first();

        //dd($order);

        $product = Product::hasCategory()->active()->find($request->productId);
        $user_distributor = User::find($request->userId);
        //dd($product);

        $stockist_store = Stockist_store::where('product_id', $product->id)->where('user_id', auth()->id())->first();
        if(!$stockist_store){
            
             $notify[] = ['error', 'Store Quantity is not enough to process this transaction'];
                return back()->withNotify($notify);
        }

        if($stockist_store && $order && $user_distributor && $product){

            if($stockist_store->quantity < $order->quantity){
                 $notify[] = ['error', 'Store Quantity is not enough to process this transaction'];
                return back()->withNotify($notify);
            }
            
            DB::beginTransaction();

            try {

            
                $user_id = auth()->id();

                $stockist= Stockist::where('user_id', $user_id)->where('status', 1)->first();

                $total = $order->price * $order->quantity;
                // Update stockist status
                //dd($stockist); 
                $stockist->wallet += $total;
                $stockist->save();

                // Record the transaction
                $post_balance1 = $stockist->wallet;
                $details = 'Credit on stockist wallet';
                $remark = 'stockist_sales';
                $trx = $order->trx;

                stockistPurchase($user_id, $total, $post_balance1, $details, $remark, $trx);
                
                // reduce stockist store quantity on this particular product
                $stockist_store->quantity -= $order->quantity;
                $stockist_store->save();

                //$stockist->update(['wallet' => newTotal]);

                $order->status = Status::ORDER_DELIVERED;
                $order->stockist_user_id = $user_id;
                $order->save(); 

                // Process the commission for the distributor upliner
                $this->processPurchaseCommision($product, $user_distributor, 
                    $order);

                $notify[] = ['success', 'Transaction successfully'];

            DB::commit();

            } catch (\Throwable $e) {
                DB::rollBack();
                throw $e;
                $notify[] = ['error', 'Failed'];
            }

            
            return back()->withNotify($notify);         
        }
 
    }
    // Process the purchase commission
    protected function processPurchaseCommision(Product $product, User $user_distributor, Order $order){
        // first we allocate the 40 of the interest dirstribute to the stockist.

        // Amount to distributor 
        $amount = $product->distributor_purchase * $order->quantity;

        $trx = $order->trx;
        $product_name = $product->name;


        //Credit the stockist user

        $stockist_user= User::find(auth()->id());

        if($stockist_user){
            
            $details1 = 'Stockist Product purchase commission recieved from product ( '.$product_name.' ) purchase by '.$user_distributor->username;

            purchaseCommision_distributor_stockist($stockist_user, $details1, 
                $amount, $trx, 40);
        }


        $user_1st_id = $user_distributor->ref_by;
        if($user_1st_id){
            $user= User::find($user_1st_id);
            $details1 = 'Product purchase commission (1st Gen ) recieved from product ( '.$product_name.' ) purchase by '.$user_distributor->username;

            purchaseCommision_distributor($user, $details1, 
                $amount, $trx, 24);
           
        

        // Second Generation/ parent referral
        $user_2nd = returnReferrerUser($user_1st_id);
        if($user_2nd){

          
            $details2 = 'Product purchase commission (2nd Gen ) recieved from product('.$product_name.') purchase by '.$user_distributor->username;
            purchaseCommision_distributor($user_2nd, $details2, 
                $amount, $trx, 20);
        // Third Generation/ parent referral
        $user_3rd = returnReferrerUser($user_2nd->id);
        if($user_3rd){
            $details3 = 'Product purchase commission (3rd Gen ) recieved from product('.$product_name.') purchase by '.$user_distributor->username;
            purchaseCommision_distributor($user_3rd, $details3, 
                $amount, $trx, 12);
       

        // Fourth Generation/ parent referral
        $user_4th = returnReferrerUser($user_3rd->id);
        if($user_4th){
            $details4 = 'Product purchase commission (4th Gen ) recieved from product('.$product_name.') purchase by '.$user_distributor->username;
            purchaseCommision_distributor($user_4th, $details4, 
                $amount, $trx, 4);

        } // Fourth Generation ends here

        }// Third Generation ends here

        }// Second Generation ends here

        }// first generation ends here

      
    }
    
    public function restockGoods(Request $request)
    {
        
        $request->validate([
            'prod_id'   => 'required|integer',
            'qty'   => 'required|string',
            'stock_id'   => 'required|string',
            
        ]);

        $product = Product::where('status', 1)->find($request->prod_id);
        

        if(!$product){
            $notify[] = ['error', 'Invalid request, Product not available'];
                return back()->withNotify($notify);
        }

        $stockist_store = Stockist_store::where('user_id', auth()->id())->where('product_id', $product->id)->first();
        if ($stockist_store) {
           if($stockist_store->quantity > 100){
                $notify[] = ['error', 'Order Decline. Your Store Quantity is still above 50 bags'];
                return back()->withNotify($notify);
            }

        }

        
        //Ensure no previous order exist.
        $s_order = Sorder::where('product_id', $product->id)->where('user_id', auth()->id())->where('status', 0)->first();

        if($s_order){
            $notify[] = ['error', 'You have an existing order'];
            return back()->withNotify($notify);
        }

        if ($stockist_store ) {
            DB::transaction(function () use ($stockist_store, $product, $request) {

                $sorder                     = new Sorder();
                $sorder->user_id            = auth()->id();
                $sorder->quantity           = $request->qty;
                $sorder->product_id         = $product->id;
                $sorder->stockist_store_id  = $stockist_store->id;
                $sorder->save();
 
            });
        }else{
            //create Stockist_store firstbefore making the order
            DB::transaction(function () use ($stockist_store, $product, $request) {
                $user_id = auth()->id();
                //$stockist= Stockist::where('user_id', $user_id)->where('status', 1)->first();
                
                $stockist_store  = new Stockist_store();
                $stockist_store->user_id    = $user_id;
                $stockist_store->product_id = $product->id;
                $stockist_store->status     = 1;
                $stockist_store->save();

                $sorder                     = new Sorder();
                $sorder->user_id            = $user_id;
                $sorder->quantity           = $request->qty;
                $sorder->product_id         = $product->id;
                $sorder->stockist_store_id  = $stockist_store->id;
                $sorder->save();

            }); 
        }

        $notify[] = ['success', 'Order Request successfully'];
            return back()->withNotify($notify);
    }


    public function transactions()
    {
        $pageTitle    = 'Transactions';
        $remarks      = Transaction::where('user_id', auth()->id())->distinct('remark')->orderBy('remark')->whereNotNull('remark')->get('remark');
        $transactions = Transaction::where('user_id', auth()->id())->searchable(['trx'])->filter(['trx_type', 'remark'])->orderBy('id', 'desc')->paginate(getPaginate());
        return view('Template::user.transactions', compact('pageTitle', 'transactions', 'remarks'));
    }

    
   
    public function downloadAttachment($fileHash)
    {
        $filePath  = decrypt($fileHash);
        $extension = pathinfo($filePath, PATHINFO_EXTENSION);
        $title     = slug(gs('site_name')) . '- attachments.' . $extension;
        try {
            $mimetype = mime_content_type($filePath);
        } catch (\Exception $e) {
            $notify[] = ['error', 'File does not exists'];
            return back()->withNotify($notify);
        }
        header('Content-Disposition: attachment; filename="' . $title);
        header("Content-Type: " . $mimetype);
        return readfile($filePath);
    }

    public function purchase(Request $request)
    {
        $request->validate([
            'quantity'   => 'required|integer|gt:0',
            'product_id' => 'required|integer|gt:0'
        ]);

        $product = Product::hasCategory()->active()->find($request->product_id);

        if (!$product) {
            $notify[] = ['error', 'Product not found'];
            return back()->withNotify($notify);
        }
        
        if ($request->quantity > $product->quantity) {
            $notify[] = ['error', 'Requested quantity is not available in stock'];
            return back()->withNotify($notify);
        }
        $user       = auth()->user();
        $totalPrice = $product->price * $request->quantity;
        if ($user->balance < $totalPrice) {
            $notify[] = ['error', 'Balance is not sufficient'];
            return back()->withNotify($notify);
        }
        $user->balance -= $totalPrice;
        $user->save();

        $product->quantity -= $request->quantity;
        $product->save();

        $transaction               = new Transaction();
        $transaction->user_id      = $user->id;
        $transaction->amount       = $totalPrice;
        $transaction->post_balance = $user->balance;
        $transaction->charge       = 0;
        $transaction->trx_type     = '-';
        $transaction->details      = $product->name . ' item purchase';
        $transaction->trx          = getTrx();
        $transaction->save();

        $order              = new Order();
        $order->user_id     = $user->id;
        $order->product_id  = $product->id;
        $order->quantity    = $request->quantity;
        $order->price       = $product->price;
        $order->total_price = $totalPrice;
        $order->trx         = $transaction->trx;
        $order->status      = 0;
        $order->save();

        notify($user, 'ORDER_PLACED', [
            'product_name' => $product->name,
            'quantity'     => $request->quantity,
            'price'        => showAmount($product->price, currencyFormat: false),
            'total_price'  => showAmount($totalPrice, currencyFormat: false),
            'trx'          => $transaction->trx,
        ]);

        $notify[] = ['success', 'Order placed successfully'];
        return back()->withNotify($notify);
    }


    

    
    public function orders()
    {
        $pageTitle = 'Orders';
        $orders    = Order::where('user_id', auth()->user()->id)->with('product')->orderBy('id', 'desc')->paginate(getPaginate());
        return view('Template::user.orders', compact('pageTitle', 'orders'));
    }
}
