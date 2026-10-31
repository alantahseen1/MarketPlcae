<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProductRejectRequest;
use App\Models\Product;
use App\Services\ProductReviewService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProductReviewController extends Controller
{
    public function __construct(
        protected ProductReviewService $reviewService
    ) {}

    /**
     * Display a listing of products to review with status filter tabs.
     */
    public function index(Request $request): View
    {
        $activeTab = $request->input('status', 'pending');

        $query = Product::with(['category', 'customer', 'images']);

        if ($activeTab === 'pending') {
            $query->where('status', 'pending')->orderBy('created_at', 'asc');
        } elseif ($activeTab === 'approved') {
            $query->where('status', 'approved')->latest('id');
        } elseif ($activeTab === 'rejected') {
            $query->where('status', 'rejected')->latest('id');
        } elseif ($activeTab === 'sold') {
            $query->where('status', 'sold')->latest('id');
        } else {
            // 'all'
            $query->where('status', '!=', 'removed')->latest('id');
            $activeTab = 'all';
        }

        $products = $query->paginate(15)->withQueryString();

        return view('staff.products.index', compact('products', 'activeTab'));
    }

    /**
     * Display the full details of a product for review.
     */
    public function show(int $id): View
    {
        $product = Product::with(['images', 'category', 'customer', 'reviewer'])->findOrFail($id);

        return view('staff.products.show', compact('product'));
    }

    /**
     * Approve a pending product.
     */
    public function approve(int $id): RedirectResponse
    {
        $product = Product::findOrFail($id);

        if ($product->status !== 'pending') {
            return redirect()->back()->with('error', __('staff.already_reviewed'));
        }

        $this->reviewService->approve($product, auth('staff')->user());

        return redirect()->route('staff.products.index')
            ->with('success', __('staff.approved_success'));
    }

    /**
     * Reject a pending product.
     */
    public function reject(ProductRejectRequest $request, int $id): RedirectResponse
    {
        $product = Product::findOrFail($id);

        if ($product->status !== 'pending') {
            return redirect()->back()->with('error', __('staff.already_reviewed'));
        }

        $this->reviewService->reject(
            $product,
            auth('staff')->user(),
            $request->input('rejection_reason')
        );

        return redirect()->route('staff.products.index')
            ->with('success', __('staff.rejected_success'));
    }
}
