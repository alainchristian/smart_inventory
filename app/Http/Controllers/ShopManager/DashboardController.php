<?php

namespace App\Http\Controllers\ShopManager;

use App\Http\Controllers\Controller;
use App\Models\Shop;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        if ($user->isShopManager()) {
            $shopId = $user->location_id;
        } elseif ($user->isOwner()) {
            $shopId = request()->get('shop_id')
                ?? session('selected_shop_id')
                ?? Shop::first()?->id;

            if (!$shopId) {
                return redirect()->route('owner.dashboard')
                    ->with('error', 'No shop found. Please create a shop first.');
            }

            session(['selected_shop_id' => $shopId]);
        }

        return view('shop.dashboard', compact('shopId'));
    }
}
