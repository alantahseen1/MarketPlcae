<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use Illuminate\Contracts\View\View;

class ShopController extends Controller
{
    /**
     * Display a public shop page with its approved products.
     */
    public function show(int $customerId): View
    {
        $shop = Customer::query()
            ->where('id', $customerId)
            ->where('type', 'shop')
            ->where('is_banned', false)
            ->firstOrFail();

        $products = $shop->products()
            ->where('status', 'approved')
            ->with(['images', 'category', 'customer'])
            ->latest('id')
            ->paginate(12)
            ->withQueryString();

        return view('public.shops.show', compact('shop', 'products'));
    }
}
