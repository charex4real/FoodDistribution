<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\AffiliateOrder;
use Illuminate\Http\Request;

class AffiliateController extends Controller
{
    public function dashboard()
    {
        $pageTitle = 'Affiliate Dashboard';
        $user      = auth()->user();
        $code      = $user->getOrCreateAffiliateCode();
        $shopLink  = route('shop.index', ['ref' => $code]);

        $clicksCount = $user->affiliateClicks()->count();
        $orders      = $user->affiliateOrders();
        $ordersCount = (clone $orders)->count();
        $paidCount   = (clone $orders)->whereIn('status', [AffiliateOrder::STATUS_PAID, AffiliateOrder::STATUS_FULFILLED])->count();
        $fulfilledCount = (clone $orders)->where('status', AffiliateOrder::STATUS_FULFILLED)->count();

        $recentOrders = (clone $orders)->latest()->limit(8)->get();

        return view('Template::user.affiliate.dashboard', compact(
            'pageTitle', 'user', 'code', 'shopLink', 'clicksCount',
            'ordersCount', 'paidCount', 'fulfilledCount', 'recentOrders'
        ));
    }

    public function orders()
    {
        $pageTitle = 'My Affiliate Orders';
        $orders    = auth()->user()->affiliateOrders()->with('items')->latest()->paginate(15);

        return view('Template::user.affiliate.orders', compact('pageTitle', 'orders'));
    }
}
