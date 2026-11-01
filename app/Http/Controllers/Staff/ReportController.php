<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Report;
use App\Services\CustomerManagementService;
use App\Services\ReportService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class ReportController extends Controller
{
    public function __construct(
        protected ReportService $reportService,
        protected CustomerManagementService $customerService
    ) {}

    /**
     * Display a paginated listing of reports with status tabs.
     */
    public function index(Request $request): View
    {
        $tab = $request->query('tab', 'open');
        if (! in_array($tab, ['open', 'resolved', 'dismissed', 'all'], true)) {
            $tab = 'open';
        }

        $query = Report::query()
            ->with(['product.customer', 'reportedCustomer', 'reporter'])
            ->latest('id');

        if ($tab !== 'all') {
            $query->where('status', $tab);
        }

        $reports = $query->paginate(15)->withQueryString();

        // Calculate open reports count for each report's target
        $targetOpenCounts = [];
        foreach ($reports as $report) {
            if ($report->reported_product_id) {
                $key = 'product_'.$report->reported_product_id;
                if (! isset($targetOpenCounts[$key])) {
                    $targetOpenCounts[$key] = Report::query()
                        ->where('reported_product_id', $report->reported_product_id)
                        ->where('status', 'open')
                        ->count();
                }
            } elseif ($report->reported_customer_id) {
                $key = 'customer_'.$report->reported_customer_id;
                if (! isset($targetOpenCounts[$key])) {
                    $targetOpenCounts[$key] = Report::query()
                        ->where('reported_customer_id', $report->reported_customer_id)
                        ->where('status', 'open')
                        ->count();
                }
            }
        }

        $counts = [
            'open' => Report::where('status', 'open')->count(),
            'resolved' => Report::where('status', 'resolved')->count(),
            'dismissed' => Report::where('status', 'dismissed')->count(),
            'all' => Report::count(),
        ];

        return view('staff.reports.index', compact('reports', 'tab', 'counts', 'targetOpenCounts'));
    }

    /**
     * Display report details and other reports on the same target.
     */
    public function show(int $id): View
    {
        $report = Report::query()
            ->with([
                'product.customer',
                'product.images',
                'product.category',
                'reportedCustomer',
                'reporter',
                'handler',
            ])
            ->findOrFail($id);

        if ($report->reported_product_id) {
            $otherReports = Report::query()
                ->where('reported_product_id', $report->reported_product_id)
                ->where('id', '!=', $report->id)
                ->with('reporter')
                ->latest('id')
                ->get();
        } else {
            $otherReports = Report::query()
                ->where('reported_customer_id', $report->reported_customer_id)
                ->where('id', '!=', $report->id)
                ->with('reporter')
                ->latest('id')
                ->get();
        }

        return view('staff.reports.show', compact('report', 'otherReports'));
    }

    /**
     * Resolve an open report.
     */
    public function resolve(int $id): RedirectResponse
    {
        $report = Report::query()->findOrFail($id);

        try {
            $this->reportService->resolve($report, auth('staff')->user());
        } catch (ValidationException $e) {
            return redirect()
                ->back()
                ->with('error', $e->getMessage());
        }

        return redirect()
            ->back()
            ->with('success', __('staff.report_resolved_success'));
    }

    /**
     * Dismiss an open report.
     */
    public function dismiss(int $id): RedirectResponse
    {
        $report = Report::query()->findOrFail($id);

        try {
            $this->reportService->dismiss($report, auth('staff')->user());
        } catch (ValidationException $e) {
            return redirect()
                ->back()
                ->with('error', $e->getMessage());
        }

        return redirect()
            ->back()
            ->with('success', __('staff.report_dismissed_success'));
    }

    /**
     * Remove a reported product from the report view (sets status = 'removed').
     */
    public function removeProduct(int $id): RedirectResponse
    {
        $report = Report::query()->with('product')->findOrFail($id);

        if (! $report->product) {
            abort(404);
        }

        try {
            $this->reportService->removeProduct($report->product);
        } catch (ValidationException $e) {
            return redirect()
                ->back()
                ->with('error', $e->getMessage());
        }

        return redirect()
            ->back()
            ->with('success', __('staff.product_removed_success'));
    }

    /**
     * Ban a reported customer (or the seller of the reported product) from the report view.
     */
    public function banCustomer(int $id): RedirectResponse
    {
        $report = Report::query()->with(['product.customer', 'reportedCustomer'])->findOrFail($id);

        $customer = $report->reportedCustomer ?? $report->product?->customer;

        if (! $customer) {
            abort(404);
        }

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
}
