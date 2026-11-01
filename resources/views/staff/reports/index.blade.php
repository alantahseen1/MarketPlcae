@extends('layouts.staff')

@section('title', __('staff.reports') . ' - ' . __('staff.panel_title'))

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl sm:text-3xl font-bold text-slate-900">{{ __('staff.reports') }}</h1>
            <p class="mt-1 text-sm text-slate-500">
                بەڕێوەبردن و پێداچوونەوەی سکاڵاکانی کڕیاران لەسەر بەرهەمەکان و بەکارهێنەران
            </p>
        </div>
    </div>

    {{-- Tabs --}}
    <div class="border-b border-slate-200">
        <nav class="flex space-x-2 space-x-reverse" aria-label="Tabs">
            @php
                $tabLinks = [
                    'open' => [__('reports.status_open'), $counts['open']],
                    'resolved' => [__('reports.status_resolved'), $counts['resolved']],
                    'dismissed' => [__('reports.status_dismissed'), $counts['dismissed']],
                    'all' => [__('staff.tab_all'), $counts['all']],
                ];
            @endphp

            @foreach($tabLinks as $tKey => [$tLabel, $tCount])
                <a href="{{ route('staff.reports.index', ['tab' => $tKey]) }}"
                   class="px-4 py-2.5 text-sm font-semibold border-b-2 rounded-t-lg transition flex items-center gap-2 {{ $tab === $tKey ? 'border-indigo-600 text-indigo-600 bg-white shadow-xs' : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300' }}">
                    <span>{{ $tLabel }}</span>
                    <span class="px-2 py-0.5 text-xs rounded-full {{ $tab === $tKey ? 'bg-indigo-100 text-indigo-700' : 'bg-slate-100 text-slate-600' }}">
                        {{ $tCount }}
                    </span>
                </a>
            @endforeach
        </nav>
    </div>

    {{-- Table / List --}}
    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
        @if($reports->isEmpty())
            <div class="p-12 text-center text-slate-500">
                <svg class="w-12 h-12 mx-auto text-slate-300 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <p class="font-medium text-base">هیچ سکاڵایەک لەم بەشەدا نییە.</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-right">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-6 py-3.5 text-xs font-bold text-slate-600 uppercase">{{ __('staff.target_type') }}</th>
                            <th class="px-6 py-3.5 text-xs font-bold text-slate-600 uppercase">{{ __('staff.target_title') }}</th>
                            <th class="px-6 py-3.5 text-xs font-bold text-slate-600 uppercase">{{ __('reports.reason') }}</th>
                            <th class="px-6 py-3.5 text-xs font-bold text-slate-600 uppercase">{{ __('staff.reporter') }}</th>
                            <th class="px-6 py-3.5 text-xs font-bold text-slate-600 uppercase">{{ __('staff.open_reports_count') }}</th>
                            <th class="px-6 py-3.5 text-xs font-bold text-slate-600 uppercase">{{ __('staff.report_status') }}</th>
                            <th class="px-6 py-3.5 text-xs font-bold text-slate-600 uppercase">{{ __('products.date') }}</th>
                            <th class="px-6 py-3.5 text-xs font-bold text-slate-600 uppercase">{{ __('staff.actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200">
                        @foreach($reports as $report)
                            @php
                                $isProduct = (bool) $report->reported_product_id;
                                $targetKey = $isProduct ? 'product_'.$report->reported_product_id : 'customer_'.$report->reported_customer_id;
                                $openSameCount = $targetOpenCounts[$targetKey] ?? 1;
                            @endphp
                            <tr class="hover:bg-slate-50 transition">
                                <td class="px-6 py-4 whitespace-nowrap text-xs font-semibold">
                                    @if($isProduct)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-blue-50 text-blue-700 border border-blue-200">
                                            {{ __('reports.target_product') }}
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-purple-50 text-purple-700 border border-purple-200">
                                            {{ __('reports.target_customer') }}
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-sm font-bold text-slate-900 max-w-xs truncate">
                                    @if($isProduct && $report->product)
                                        <a href="{{ route('staff.reports.show', $report->id) }}" class="hover:text-indigo-600 truncate block">
                                            {{ $report->product->title }}
                                        </a>
                                    @elseif(!$isProduct && $report->reportedCustomer)
                                        <a href="{{ route('staff.reports.show', $report->id) }}" class="hover:text-indigo-600 truncate block">
                                            {{ $report->reportedCustomer->shop_name ?? $report->reportedCustomer->full_name }}
                                        </a>
                                    @else
                                        <span class="text-slate-400 italic">سڕاوەتەوە</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-700">
                                    {{ __('reports.reason_' . $report->reason) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-600">
                                    {{ $report->reporter->full_name ?? '-' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-center">
                                    @if($openSameCount > 1)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold bg-red-100 text-red-800 border border-red-200">
                                            {{ $openSameCount }} کراوە
                                        </span>
                                    @else
                                        <span class="text-xs text-slate-500 font-medium">١</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-xs">
                                    @if($report->status === 'open')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-amber-100 text-amber-800 border border-amber-200">
                                            {{ __('reports.status_open') }}
                                        </span>
                                    @elseif($report->status === 'resolved')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800 border border-emerald-200">
                                            {{ __('reports.status_resolved') }}
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-slate-100 text-slate-700 border border-slate-200">
                                            {{ __('reports.status_dismissed') }}
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-xs text-slate-500">
                                    {{ $report->created_at->format('Y-m-d H:i') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <a href="{{ route('staff.reports.show', $report->id) }}" 
                                       class="inline-flex items-center gap-1 px-3 py-1.5 bg-slate-100 hover:bg-indigo-50 hover:text-indigo-600 text-slate-700 rounded-lg text-xs font-semibold transition">
                                        {{ __('staff.report_details') }}
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="p-4 border-t border-slate-200">
                {{ $reports->links() }}
            </div>
        @endif
    </div>
</div>
@endsection

