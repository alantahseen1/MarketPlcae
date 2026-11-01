<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Customer;
use App\Models\Product;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    /**
     * Generate dynamic sitemap.xml.
     */
    public function index(): Response
    {
        $categories = Category::query()
            ->where('is_active', true)
            ->get(['id', 'name']);

        $products = Product::query()
            ->publiclyVisible()
            ->latest('id')
            ->get(['id', 'updated_at']);

        $shops = Customer::query()
            ->where('type', 'shop')
            ->where('is_banned', false)
            ->get(['id', 'created_at']);

        $content = view('public.sitemap', compact('categories', 'products', 'shops'))->render();

        return response($content, 200, [
            'Content-Type' => 'text/xml; charset=UTF-8',
        ]);
    }
}
