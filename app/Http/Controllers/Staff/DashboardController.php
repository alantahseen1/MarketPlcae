<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Product;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Display the staff dashboard with overview metrics.
     */
    public function index(): View
    {
        $pendingCount = Product::where('status', 'pending')->count();
        $approvedCount = Product::where('status', 'approved')->count();
        $soldCount = Product::where('status', 'sold')->count();
        $customersCount = Customer::count();

        return view('staff.dashboard', compact(
            'pendingCount',
            'approvedCount',
            'soldCount',
            'customersCount'
        ));
    }
}
