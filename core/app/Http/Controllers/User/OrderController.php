<?php
// app/Http/Controllers/OrderController.php
namespace App\Http\Controllers\User;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Invoice;
use App\Models\Cart;
use App\Models\CartItem;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
 
class OrderController extends Controller
{
    public function index()
    {   
        $pageTitle = "Order Details";
        //$invoice = 
        $orders = auth()->user()
        ->orders()
        ->with(['items.product', 'invoice'])
        ->latest()
        ->paginate(10);
        //dd($orders);
        
       // $orders = auth()->user()->orders()->with('items.product')->latest()->get();
        return view('Template::user.orders.index', compact('orders', 'pageTitle'));
    }
    
    public function checkout(){
        $pageTitle = "Checkout";
        $user = auth()->user();
        $cart = $user->cart;
        
        if (!$cart || $cart->items->isEmpty()) {
            return redirect()->route('user.cart.index')->with('error', 'Your cart is empty');
        }
        
        // Load relationships for the view
        $cart->load('items.product');
        
        return view('Template::user.orders.checkout', compact('cart', 'pageTitle'));
    }

    public function processPayment(Request $request)
    { 
        $user = auth()->user();
        $cart = $user->cart;

        if (!$cart || $cart->items->isEmpty()) {
            return response()->json(['success' => false, 'message' => 'Cart is empty']);
        }

        if ($user->product_wallet < $cart->total_amount) {
            return response()->json(['success' => false, 'message' => 'Insufficient Product Wallet balance']);
        }
        $trx = getTrx(10);

        DB::beginTransaction();
        try {

            // Create order
            //get the state ID
            $stateId = $cart->state_the_id;

            $order =  new Order();
            $order->user_id = $user->id;
            $order->state_id = $stateId;
            $order->status = 0; // 0 indicate pending order
            $order->save();

            // Create order items
            foreach ($cart->items as $cartItem) {
                $orderItem = new OrderItem();
                $orderItem->order_id = $order->id;
                $orderItem->product_id = $cartItem->product_id;
                $orderItem->quantity = $cartItem->quantity;
                $orderItem->price = $cartItem->price;
                $orderItem->save();
               
            }
 
            // Create invoice
            $invoice = new Invoice();
            $invoice->invoice_code  = $order->invoice_code;
            $invoice->order_id = $order->id;
            $invoice->state_id = $stateId;
            $invoice->items = json_encode($cart->items->map(function ($item) {
                return [
                    'product_id' => $item->product->id,
                    'product_name' => $item->product->name,
                    'quantity' => $item->quantity,
                    'product_state_id' => $item->product_state_id,
                    'price' => $item->price
                ];
                }));
            
            $invoice->total_amount = $cart->total_amount;
            $invoice->save();
            

            // Deduct from wallet
            //get final total amount
            $grand_total = $cart->total_amount;
            //deduct
            $user->productDeductWallet($grand_total);
            

            // record transaction
            productPurchase($user, $trx, $grand_total);

            // add the transaction code to order. for proper tracking
            $order->trx = $trx;
            $order->save();

            // Clear cart
            $cart->items()->delete();
            $cart->updateTotal();

            DB::commit();

            return response()->json([
                'success' => true,
                'invoice_code' => $invoice->invoice_code,
                'redirect_url' => route('user.orders.show', $order->id)
            ]);
 
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Payment failed: ' . $e->getMessage()]);
        }
    }

    public function show(Order $order)
    {   $pageTitle = "Order Details";

        if ($order->user_id != auth()->id()) {
            abort(403, 'Unauthorized action.');
        }

        // Load relationships for the view
        $order->load([
            'items.product', 
            'invoice',
            'user'
        ]);
 


        return view('Template::user.orders.show', compact('order', 'pageTitle'));
    }


    
}