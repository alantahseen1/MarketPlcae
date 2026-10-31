<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Customer;
use App\Models\Product;
use Illuminate\Contracts\View\View;

class HomeController extends Controller
{
    /**
     * Show the public home page.
     */
    public function index(): View
    {
        $categories = Category::query()
            ->where('is_active', true)
            ->withCount(['products' => function ($query): void {
                $query->publiclyVisible();
            }])
            ->orderBy('id')
            ->get();

        $latestProducts = Product::query()
            ->publiclyVisible()
            ->with(['images', 'category', 'customer'])
            ->latest('id')
            ->take(12)
            ->get();

        $featuredShops = Customer::query()
            ->where('type', 'shop')
            ->where('is_banned', false)
            ->withCount(['products' => function ($query): void {
                $query->publiclyVisible();
            }])
            ->orderByDesc('is_verified')
            ->latest('id')
            ->take(6)
            ->get();

        return view('public.home', compact('categories', 'latestProducts', 'featuredShops'));
    }
}
