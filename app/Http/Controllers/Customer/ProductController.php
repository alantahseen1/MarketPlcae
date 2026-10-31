<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProductStoreRequest;
use App\Http\Requests\ProductUpdateRequest;
use App\Models\Category;
use App\Models\Product;
use App\Services\ProductService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function __construct(
        protected ProductService $productService
    ) {}

    /**
     * Display a listing of the customer's products.
     * Excludes removed products.
     */
    public function index(Request $request): View
    {
        $products = $request->user('customer')
            ->products()
            ->where('status', '!=', 'removed')
            ->with(['images', 'category'])
            ->latest('id')
            ->paginate(12);

        return view('customer.products.index', compact('products'));
    }

    /**
     * Show the form for creating a new product.
     */
    public function create(): View
    {
        $categories = Category::where('is_active', true)->orderBy('name')->get();

        return view('customer.products.form', [
            'product' => null,
            'categories' => $categories,
        ]);
    }

    /**
     * Store a newly created product in storage.
     */
    public function store(ProductStoreRequest $request): RedirectResponse
    {
        DB::transaction(function () use ($request): void {
            $this->productService->create(
                $request->user('customer'),
                $request->validated(),
                $request->file('photos', [])
            );
        });

        return redirect()->route('customer.products.index')
            ->with('success', __('products.created_success'));
    }

    /**
     * Show the form for editing the specified product.
     */
    public function edit(int $id): View
    {
        $product = Product::with('images')->findOrFail($id);

        $this->authorize('update', $product);

        $categories = Category::where('is_active', true)->orderBy('name')->get();

        return view('customer.products.form', [
            'product' => $product,
            'categories' => $categories,
        ]);
    }

    /**
     * Update the specified product in storage.
     */
    public function update(ProductUpdateRequest $request, int $id): RedirectResponse
    {
        $product = $request->getTargetProduct();

        $this->authorize('update', $product);

        DB::transaction(function () use ($request, $product): void {
            $this->productService->update(
                $product,
                $request->validated(),
                $request->file('photos', []),
                (array) $request->input('delete_photos', []),
                $request->input('main_photo_id') !== null ? (int) $request->input('main_photo_id') : null
            );
        });

        return redirect()->route('customer.products.index')
            ->with('success', __('products.updated_success'));
    }

    /**
     * Soft-remove the specified product.
     */
    public function destroy(int $id): RedirectResponse
    {
        $product = Product::findOrFail($id);

        $this->authorize('delete', $product);

        DB::transaction(function () use ($product): void {
            $this->productService->remove($product);
        });

        return redirect()->route('customer.products.index')
            ->with('success', __('products.deleted_success'));
    }

    /**
     * Mark an approved product as sold.
     */
    public function markSold(int $id): RedirectResponse
    {
        $product = Product::findOrFail($id);

        $this->authorize('markSold', $product);

        DB::transaction(function () use ($product): void {
            $this->productService->markAsSold($product);
        });

        return redirect()->route('customer.products.index')
            ->with('success', __('products.sold_success'));
    }
}

