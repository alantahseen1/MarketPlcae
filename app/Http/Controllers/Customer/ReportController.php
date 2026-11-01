<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Http\Requests\ReportStoreRequest;
use App\Models\Customer;
use App\Models\Product;
use App\Services\ReportService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\ValidationException;

class ReportController extends Controller
{
    public function __construct(
        protected ReportService $reportService
    ) {}

    /**
     * Show report form for a product.
     */
    public function createForProduct(int $id): View|RedirectResponse
    {
        $product = Product::query()
            ->publiclyVisible()
            ->with(['customer', 'category', 'images'])
            ->findOrFail($id);

        if ($product->customer_id === auth('customer')->id()) {
            return redirect()
                ->route('products.show', $product->id)
                ->with('error', __('reports.cannot_report_own_product'));
        }

        return view('customer.reports.form', [
            'targetType' => 'product',
            'product' => $product,
            'targetCustomer' => null,
        ]);
    }

    /**
     * Store report for a product.
     */
    public function storeForProduct(ReportStoreRequest $request, int $id): RedirectResponse
    {
        $product = Product::query()
            ->publiclyVisible()
            ->findOrFail($id);

        try {
            $this->reportService->createProductReport(
                auth('customer')->user(),
                $product,
                $request->validated()
            );
        } catch (ValidationException $e) {
            return redirect()
                ->back()
                ->withInput()
                ->withErrors($e->errors());
        }

        return redirect()
            ->route('products.show', $product->id)
            ->with('success', __('reports.success_message'));
    }

    /**
     * Show report form for a customer / shop.
     */
    public function createForCustomer(int $id): View|RedirectResponse
    {
        $targetCustomer = Customer::query()
            ->where('is_banned', false)
            ->findOrFail($id);

        if ($targetCustomer->id === auth('customer')->id()) {
            $redirectUrl = $targetCustomer->type === 'shop'
                ? route('shops.show', $targetCustomer->id)
                : route('home');

            return redirect($redirectUrl)->with('error', __('reports.cannot_report_self'));
        }

        return view('customer.reports.form', [
            'targetType' => 'customer',
            'product' => null,
            'targetCustomer' => $targetCustomer,
        ]);
    }

    /**
     * Store report for a customer / shop.
     */
    public function storeForCustomer(ReportStoreRequest $request, int $id): RedirectResponse
    {
        $targetCustomer = Customer::query()->findOrFail($id);

        try {
            $this->reportService->createCustomerReport(
                auth('customer')->user(),
                $targetCustomer,
                $request->validated()
            );
        } catch (ValidationException $e) {
            return redirect()
                ->back()
                ->withInput()
                ->withErrors($e->errors());
        }

        $redirectUrl = $targetCustomer->type === 'shop'
            ? route('shops.show', $targetCustomer->id)
            : route('home');

        return redirect($redirectUrl)->with('success', __('reports.success_message'));
    }
}

