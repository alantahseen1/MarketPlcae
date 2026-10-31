<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProductBrowseRequest;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Contracts\View\View;

class ProductBrowseController extends Controller
{
    /**
     * Display a listing of publicly visible products with search and filters.
     */
    public function index(ProductBrowseRequest $request): View
    {
        $filters = $request->filters();

        $categories = Category::query()
            ->where('is_active', true)
            ->withCount(['products' => function ($query): void {
                $query->publiclyVisible();
            }])
            ->orderBy('id')
            ->get();

        $selectedCategory = ! empty($filters['category'])
            ? $categories->firstWhere('id', $filters['category'])
            : null;

        $products = Product::query()
            ->publiclyVisible()
            ->with(['images', 'category', 'customer'])
            ->filter($filters)
            ->paginate(12)
            ->withQueryString();

        return view('public.products.index', compact('products', 'categories', 'filters', 'selectedCategory'));
    }

    /**
     * Display the specified publicly visible product.
     */
    public function show(int $id): View
    {
        $product = Product::query()
            ->publiclyVisible()
            ->with(['images', 'category', 'customer'])
            ->findOrFail($id);

        $relatedProducts = Product::query()
            ->publiclyVisible()
            ->where('category_id', $product->category_id)
            ->where('id', '!=', $product->id)
            ->with(['images', 'category', 'customer'])
            ->latest('id')
            ->take(4)
            ->get();

        return view('public.products.show', compact('product', 'relatedProducts'));
    }
}
