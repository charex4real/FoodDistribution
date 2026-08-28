<?php

namespace App\Services;

use App\Mail\AffiliateInvoiceMail;
use App\Models\AffiliateOrder;
use App\Models\AffiliateSetting;
use App\Models\Product;
use App\Models\Stockist;
use App\Models\StockistRedemption;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * Single source of truth for creating and settling /shop orders: stock
 * reservation, Paystack confirmation, cash-on-pickup redemption, and the
 * affiliate-bonus crediting rules that go with each. Mirrors the shape of
 * WelcomePackageService for the internal repurchase flow.
 */
class AffiliateOrderService
{
    const AFFILIATE_BONUS_TYPE = 18; // Transaction::bonus_type code reserved for affiliate-shop bonuses

    /**
     * Creates the order + items, reserving stock immediately. Cash-on-pickup
     * orders are considered "placed" right away (awaiting_pickup); Paystack
     * orders stay pending until the gateway confirms payment.
     */
    public function createOrder(array $buyer, array $resolvedCart, string $paymentMethod, ?array $attribution, string $ip, string $userAgent): AffiliateOrder
    {
        if (empty($resolvedCart['lines'])) {
            throw new \RuntimeException('Your cart is empty.');
        }

        return DB::transaction(function () use ($buyer, $resolvedCart, $paymentMethod, $attribution, $ip, $userAgent) {
            $order = AffiliateOrder::create([
                'affiliate_user_id'  => $attribution['affiliate_user_id'] ?? null,
                'affiliate_click_id' => $attribution['click_id'] ?? null,
                'buyer_name'         => $buyer['name'],
                'buyer_email'        => $buyer['email'],
                'buyer_phone'        => $buyer['phone'] ?? null,
                'state_id'           => $buyer['state_id'],
                'payment_method'     => $paymentMethod,
                'status'             => $paymentMethod === AffiliateOrder::PAYMENT_CASH_ON_PICKUP
                                            ? AffiliateOrder::STATUS_AWAITING_PICKUP
                                            : AffiliateOrder::STATUS_PENDING,
                'subtotal'           => $resolvedCart['subtotal'],
                'total_amount'       => $resolvedCart['subtotal'],
                'ip_address'         => $ip,
                'user_agent'         => $userAgent,
            ]);

            foreach ($resolvedCart['lines'] as $line) {
                /** @var Product $product */
                $product = $line['product'];

                $locked = Product::whereKey($product->id)->lockForUpdate()->first();
                if (!$locked || $locked->quantity < $line['quantity']) {
                    throw new \RuntimeException("\"{$product->name}\" no longer has enough stock available.");
                }

                $locked->decrement('quantity', $line['quantity']);

                $bonus = $order->affiliate_user_id
                    ? $product->calculateAffiliateBonus($line['unit_price'], $line['quantity'])
                    : 0.0;

                $order->items()->create([
                    'product_id'   => $product->id,
                    'product_name' => $product->name,
                    'quantity'     => $line['quantity'],
                    'unit_price'   => $line['unit_price'],
                    'line_total'   => $line['line_total'],
                    'bonus_amount' => $bonus,
                ]);
            }

            if ($order->status === AffiliateOrder::STATUS_AWAITING_PICKUP) {
                $this->sendInvoiceEmail($order);
            }

            return $order->fresh('items');
        });
    }

    /**
     * Called once Paystack verification succeeds (callback or webhook — both
     * paths funnel here). Idempotent: a second confirmation is a no-op.
     */
    public function markPaystackPaid(AffiliateOrder $order, string $reference): void
    {
        if ($order->status !== AffiliateOrder::STATUS_PENDING) {
            return;
        }

        DB::transaction(function () use ($order, $reference) {
            $order->update([
                'status'             => AffiliateOrder::STATUS_PAID,
                'paystack_reference' => $reference,
            ]);

            $this->creditAffiliateBonus($order->fresh('items'));
        });

        $this->sendInvoiceEmail($order->fresh());
    }

    /**
     * A Stockist confirms the buyer picked up the order (and, for
     * cash-on-pickup, paid in person). Credits the affiliate bonus at this
     * point for cash orders (money wasn't real until now) and an optional
     * stockist handling fee.
     */
    public function confirmPickup(AffiliateOrder $order, Stockist $stockist): void
    {
        if (!$order->isReadyForPickup()) {
            throw new \RuntimeException('This order is not awaiting pickup.');
        }

        DB::transaction(function () use ($order, $stockist) {
            $order->update([
                'status'                  => AffiliateOrder::STATUS_FULFILLED,
                'redeemed_by_stockist_id' => $stockist->id,
                'redeemed_at'             => now(),
            ]);

            $order->loadMissing('items');

            StockistRedemption::create([
                'stockist_id'        => $stockist->id,
                'type'               => StockistRedemption::TYPE_AFFILIATE_INVOICE,
                'reference_code'     => $order->order_code,
                'affiliate_order_id' => $order->id,
                'buyer_name'         => $order->buyer_name,
                'items'              => $order->items->map(fn ($item) => [
                    'product_id'   => $item->product_id,
                    'product_name' => $item->product_name,
                    'quantity'     => $item->quantity,
                    'unit_price'   => $item->unit_price,
                    'subtotal'     => $item->line_total,
                ])->all(),
                'quantity'    => $order->items->sum('quantity'),
                'amount'      => $order->total_amount,
                'redeemed_at' => now(),
            ]);

            if ($order->payment_method === AffiliateOrder::PAYMENT_CASH_ON_PICKUP) {
                $this->creditAffiliateBonus($order->fresh('items'));

                $fee = AffiliateSetting::current()->stockistFeeFor((float) $order->total_amount);
                if ($fee > 0) {
                    $stockist->wallet += $fee;
                    $stockist->save();
                    stockistTransaction($stockist, $order->affiliate ?: $stockist->user, getTrx(10), $fee);
                }
            }
        });
    }

    public function creditAffiliateBonus(AffiliateOrder $order): void
    {
        if ($order->bonus_credited || !$order->affiliate_user_id) {
            return;
        }

        $affiliate = $order->affiliate;
        if (!$affiliate) {
            return;
        }

        $total = (float) $order->items->sum('bonus_amount');

        if ($total > 0) {
            $affiliate->addAffiliateBonus($total);

            newTransaction(
                $affiliate,
                'Affiliate bonus for order ' . $order->order_code,
                'affiliate_bonus',
                $total,
                '+',
                getTrx(10),
                self::AFFILIATE_BONUS_TYPE,
                0,
                'affiliate_bonus'
            );
        }

        $order->update(['bonus_credited' => true]);
    }

    private function sendInvoiceEmail(AffiliateOrder $order): void
    {
        try {
            Mail::to($order->buyer_email)->send(new AffiliateInvoiceMail($order));
        } catch (\Throwable $e) {
            Log::error('Affiliate invoice email failed for order ' . $order->order_code . ': ' . $e->getMessage());
        }
    }

    /**
     * Cancels stale unpaid/unpicked orders and releases their reserved stock.
     * Invoked from the cron.secret-guarded endpoint, matching this app's
     * existing cron convention (see routes/web.php).
     */
    public function expireStaleOrders(): int
    {
        $days = AffiliateSetting::current()->pending_order_expiry_days ?: 7;
        $cutoff = now()->subDays($days);

        $stale = AffiliateOrder::whereIn('status', [AffiliateOrder::STATUS_PENDING, AffiliateOrder::STATUS_AWAITING_PICKUP])
            ->where('created_at', '<', $cutoff)
            ->with('items')
            ->get();

        foreach ($stale as $order) {
            DB::transaction(function () use ($order) {
                foreach ($order->items as $item) {
                    Product::whereKey($item->product_id)->increment('quantity', $item->quantity);
                }
                $order->update(['status' => AffiliateOrder::STATUS_EXPIRED]);
            });
        }

        return $stale->count();
    }
}
