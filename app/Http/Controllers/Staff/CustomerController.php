<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Services\CustomerManagementService;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class CustomerController extends Controller
{
    public function __construct(
        protected CustomerManagementService $customerService
    ) {}

    /**
     * Display a paginated listing of customers with search and filters.
     */
    public function index(Request $request): View
    {
        $query = Customer::query()
            ->withCount([
                'products',
                'reportsAgainst as open_reports_count' => function (Builder $q): void {
                    $q->where('status', 'open');
                },
            ]);

        // Search term
        if ($search = trim((string) $request->input('search'))) {
            $term = '%'.$search.'%';
            $query->where(function (Builder $sub) use ($term): void {
                $sub->where('full_name', 'like', $term)
                    ->orWhere('shop_name', 'like', $term)
                    ->orWhere('email', 'like', $term)
                    ->orWhere('phone', 'like', $term);
            });
        }

        // Filter type
        if ($type = $request->input('type')) {
            if (in_array($type, ['regular', 'shop'], true)) {
                $query->where('type', $type);
            }
        }

        // Filter banned
        if ($request->has('banned') && $request->input('banned') !== '') {
            $query->where('is_banned', (bool) $request->input('banned'));
        }

        // Filter verified
        if ($request->has('verified') && $request->input('verified') !== '') {
            $query->where('is_verified', (bool) $request->input('verified'));
        }

        $customers = $query->latest('id')->paginate(15)->withQueryString();

        return view('staff.customers.index', compact('customers'));
    }

    /**
     * Display a customer's full profile, products, and reports.
     */
    public function show(int $id): View
    {
        $customer = Customer::query()
            ->withCount([
                'products',
                'reportsAgainst as open_reports_count' => function (Builder $q): void {
                    $q->where('status', 'open');
                },
            ])
            ->findOrFail($id);

        $products = $customer->products()
            ->with(['category', 'images'])
            ->latest('id')
            ->get();

        $reportsFiled = $customer->reports()
            ->with(['product', 'reportedCustomer'])
            ->latest('id')
            ->get();

        $reportsAgainst = $customer->reportsAgainst()
            ->with(['reporter', 'product'])
            ->latest('id')
            ->get();

        return view('staff.customers.show', compact('customer', 'products', 'reportsFiled', 'reportsAgainst'));
    }

    /**
     * Ban a customer.
     */
    public function ban(int $id): RedirectResponse
    {
        $customer = Customer::query()->findOrFail($id);

        try {
            $this->customerService->ban($customer);
        } catch (ValidationException $e) {
            return redirect()
                ->back()
                ->with('error', $e->getMessage());
        }

        return redirect()
            ->back()
            ->with('success', __('staff.customer_banned_success'));
    }

    /**
     * Unban a customer.
     */
    public function unban(int $id): RedirectResponse
    {
        $customer = Customer::query()->findOrFail($id);

        try {
            $this->customerService->unban($customer);
        } catch (ValidationException $e) {
            return redirect()
                ->back()
                ->with('error', $e->getMessage());
        }

        return redirect()
            ->back()
            ->with('success', __('staff.customer_unbanned_success'));
    }

    /**
     * Verify a shop.
     */
    public function verify(int $id): RedirectResponse
    {
        $customer = Customer::query()->findOrFail($id);

        try {
            $this->customerService->verify($customer);
        } catch (ValidationException $e) {
            return redirect()
                ->back()
                ->with('error', $e->getMessage());
        }

        return redirect()
            ->back()
            ->with('success', __('staff.customer_verified_success'));
    }

    /**
     * Unverify a shop.
     */
    public function unverify(int $id): RedirectResponse
    {
        $customer = Customer::query()->findOrFail($id);

        try {
            $this->customerService->unverify($customer);
        } catch (ValidationException $e) {
            return redirect()
                ->back()
                ->with('error', $e->getMessage());
        }

        return redirect()
            ->back()
            ->with('success', __('staff.customer_unverified_success'));
    }
}
