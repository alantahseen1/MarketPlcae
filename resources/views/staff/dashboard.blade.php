@extends('layouts.staff')

@section('title', __('staff.dashboard') . ' - ' . __('staff.panel_title'))

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="mb-8 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl sm:text-3xl font-bold text-slate-900">{{ __('staff.dashboard') }}</h1>
            <p class="mt-1 text-sm text-slate-500">
                بەخێربێیتەوە بەڕێز {{ auth('staff')->user()->full_name }} بۆ دەستەی بەڕێوەبردن
            </p>
        </div>

        <div>
            <a href="{{ route('staff.products.index') }}"
                class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl text-sm font-semibold text-white bg-indigo-600 hover:bg-indigo-700 shadow-xs transition">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                </svg>
                {{ __('staff.view_review_queue') }} ({{ $pendingCount }})
            </a>
        </div>
    </div>

    <!-- Stat Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-5">
        <!-- Pending Products -->
        <a href="{{ route('staff.products.index', ['status' => 'pending']) }}"
            class="bg-white rounded-2xl border border-amber-200 p-6 shadow-xs hover:border-amber-400 hover:shadow-md transition flex flex-col justify-between">
            <div class="flex items-center justify-between mb-4">
                <span class="text-sm font-semibold text-slate-600">{{ __('staff.pending_products') }}</span>
                <span class="w-10 h-10 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center font-bold">
                    ⏳
                </span>
            </div>
            <div class="text-3xl font-black text-amber-700">{{ $pendingCount }}</div>
        </a>

        <!-- Open Reports (Admin Only) -->
        @if(auth('staff')->user()->role === 'admin')
            <a href="{{ route('staff.reports.index') }}"
                class="bg-white rounded-2xl border border-red-200 p-6 shadow-xs hover:border-red-400 hover:shadow-md transition flex flex-col justify-between">
                <div class="flex items-center justify-between mb-4">
                    <span class="text-sm font-semibold text-slate-600">{{ __('staff.open_reports') }}</span>
                    <span class="w-10 h-10 rounded-xl bg-red-50 text-red-600 flex items-center justify-center font-bold">
                        ⚠️
                    </span>
                </div>
                <div class="text-3xl font-black text-red-700">{{ $openReportsCount }}</div>
            </a>
        @endif

        <!-- Approved Products -->
        <a href="{{ route('staff.products.index', ['status' => 'approved']) }}"
            class="bg-white rounded-2xl border border-emerald-200 p-6 shadow-xs hover:border-emerald-400 hover:shadow-md transition flex flex-col justify-between">
            <div class="flex items-center justify-between mb-4">
                <span class="text-sm font-semibold text-slate-600">{{ __('staff.approved_products') }}</span>
                <span class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center font-bold">
                    ✓
                </span>
            </div>
            <div class="text-3xl font-black text-emerald-700">{{ $approvedCount }}</div>
        </a>

        <!-- Sold Products -->
        <a href="{{ route('staff.products.index', ['status' => 'sold']) }}"
            class="bg-white rounded-2xl border border-slate-200 p-6 shadow-xs hover:border-slate-400 hover:shadow-md transition flex flex-col justify-between">
            <div class="flex items-center justify-between mb-4">
                <span class="text-sm font-semibold text-slate-600">{{ __('staff.sold_products') }}</span>
                <span class="w-10 h-10 rounded-xl bg-slate-100 text-slate-700 flex items-center justify-center font-bold">
                    🏷
                </span>
            </div>
            <div class="text-3xl font-black text-slate-800">{{ $soldCount }}</div>
        </a>

        <!-- Total Customers -->
        <div class="bg-white rounded-2xl border border-indigo-200 p-6 shadow-xs flex flex-col justify-between">
            <div class="flex items-center justify-between mb-4">
                <span class="text-sm font-semibold text-slate-600">{{ __('staff.total_customers') }}</span>
                <span class="w-10 h-10 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center font-bold">
                    👥
                </span>
            </div>
            <div class="text-3xl font-black text-indigo-700">{{ $customersCount }}</div>
        </div>
    </div>
</div>
@endsection

