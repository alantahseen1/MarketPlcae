<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Report;
use App\Services\VisitStatsService;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Display the staff dashboard with overview metrics.
     */
    public function index(VisitStatsService $visitStatsService): View
    {
        $pendingCount = Product::where('status', 'pending')->count();
        $approvedCount = Product::where('status', 'approved')->count();
        $soldCount = Product::where('status', 'sold')->count();
        $customersCount = Customer::count();
        $openReportsCount = Report::where('status', 'open')->count();

        $user = auth('staff')->user();
        $visitStats = null;

        // Visitor statistics are strictly visible to admin users only
        if ($user && $user->role === 'admin') {
            $visitStats = $visitStatsService->getDashboardStats();
        }

        return view('staff.dashboard', compact(
            'pendingCount',
            'approvedCount',
            'soldCount',
            'customersCount',
            'openReportsCount',
            'visitStats'
        ));
    }
}
