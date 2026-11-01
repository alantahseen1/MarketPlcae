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

    <!-- Marketplace Stat Cards -->
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

    <!-- Visitor Statistics Section (Strictly Admin Only) -->
    @if(auth('staff')->user()->role === 'admin' && $visitStats)
        <div class="mt-12 bg-white rounded-2xl border border-slate-200 p-6 sm:p-8 shadow-xs">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 pb-6 border-b border-slate-100">
                <div>
                    <h2 class="text-xl font-bold text-slate-900 flex items-center gap-2">
                        <span>📊</span>
                        <span>{{ __('staff.visitor_stats_title') }}</span>
                    </h2>
                    <p class="mt-1 text-xs sm:text-sm text-slate-500">
                        {{ __('staff.visitor_stats_note') }}
                    </p>
                </div>
                <span class="self-start sm:self-auto text-xs px-2.5 py-1 bg-slate-100 text-slate-600 rounded-full font-medium">
                    نوێبوونەوە لە ٥ خولەک جارێک
                </span>
            </div>

            <!-- Stats Overview Cards -->
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-4 mt-6">
                <div class="bg-indigo-50/50 border border-indigo-100 rounded-xl p-4">
                    <div class="text-xs font-semibold text-indigo-900">{{ __('staff.today_visitors') }}</div>
                    <div class="text-2xl font-black text-indigo-700 mt-2">{{ number_format($visitStats['todayVisitors']) }}</div>
                </div>

                <div class="bg-sky-50/50 border border-sky-100 rounded-xl p-4">
                    <div class="text-xs font-semibold text-sky-900">{{ __('staff.today_page_views') }}</div>
                    <div class="text-2xl font-black text-sky-700 mt-2">{{ number_format($visitStats['todayPageViews']) }}</div>
                </div>

                <div class="bg-emerald-50/50 border border-emerald-100 rounded-xl p-4">
                    <div class="text-xs font-semibold text-emerald-900">{{ __('staff.last_7_days_visitors') }}</div>
                    <div class="text-2xl font-black text-emerald-700 mt-2">{{ number_format($visitStats['last7DaysVisitors']) }}</div>
                </div>

                <div class="bg-purple-50/50 border border-purple-100 rounded-xl p-4">
                    <div class="text-xs font-semibold text-purple-900">{{ __('staff.last_30_days_visitors') }}</div>
                    <div class="text-2xl font-black text-purple-700 mt-2">{{ number_format($visitStats['last30DaysVisitors']) }}</div>
                </div>

                <div class="bg-slate-50 border border-slate-200 rounded-xl p-4 col-span-2 sm:col-span-1">
                    <div class="text-xs font-semibold text-slate-700">{{ __('staff.total_visitor_days') }}</div>
                    <div class="text-2xl font-black text-slate-800 mt-2">{{ number_format($visitStats['totalUniqueVisitorDays']) }}</div>
                </div>
            </div>

            <!-- 14-Day HTML/CSS Bar Chart -->
            <div class="mt-8 pt-6 border-t border-slate-100">
                <h3 class="text-sm font-bold text-slate-800 mb-6 flex items-center justify-between">
                    <span>{{ __('staff.chart_14_days') }}</span>
                    <span class="text-xs font-normal text-slate-400">ڕێژە بەپێی سەردانکەر لە ڕۆژێکدا</span>
                </h3>

                <div class="h-56 flex items-end gap-2 sm:gap-4 pt-6 px-2 bg-slate-50/60 rounded-xl border border-slate-100">
                    @foreach($visitStats['chartData'] as $day)
                        <div class="flex-1 flex flex-col items-center h-full justify-end group relative">
                            <!-- Tooltip -->
                            <div class="opacity-0 group-hover:opacity-100 transition pointer-events-none absolute -top-10 bg-slate-900 text-white text-xs px-2.5 py-1 rounded-md shadow whitespace-nowrap z-10">
                                {{ $day['date'] }}: {{ number_format($day['visitors']) }} {{ __('staff.visitors_unit') }}
                            </div>

                            <!-- Value above bar for desktop -->
                            <span class="text-[10px] text-slate-500 mb-1 font-semibold hidden sm:block">
                                {{ $day['visitors'] > 0 ? $day['visitors'] : '' }}
                            </span>

                            <!-- Bar -->
                            <div class="w-full max-w-[32px] rounded-t-md transition-all duration-300 {{ $day['visitors'] > 0 ? 'bg-indigo-600 group-hover:bg-indigo-500' : 'bg-slate-200' }}"
                                 style="height: {{ max(4, $day['height_percent']) }}%;">
                            </div>

                            <!-- X-axis Date label -->
                            <span class="text-[10px] text-slate-500 mt-2 font-medium tracking-tighter truncate w-full text-center">
                                {{ $day['label'] }}
                            </span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endif
</div>
@endsection
