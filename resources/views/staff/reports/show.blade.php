@extends('layouts.staff')

@section('title', __('staff.report_details') . ' #' . $report->id . ' - ' . __('staff.panel_title'))

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-3">
                <a href="{{ route('staff.reports.index') }}" class="text-sm font-semibold text-indigo-600 hover:underline">
                    &larr; {{ __('reports.back') }}
                </a>
                <span class="text-slate-300">/</span>
                <h1 class="text-2xl sm:text-3xl font-bold text-slate-900">
                    {{ __('staff.report_details') }} #{{ $report->id }}
                </h1>
            </div>
            <p class="mt-1 text-sm text-slate-500">
                بەرواری تۆمارکردن: {{ $report->created_at->format('Y-m-d H:i') }} ({{ $report->created_at->diffForHumans() }})
            </p>
        </div>

        {{-- Status Badge --}}
        <div>
            @if($report->status === 'open')
                <span class="inline-flex items-center px-4 py-1.5 rounded-full text-sm font-bold bg-amber-100 text-amber-800 border border-amber-300">
                    {{ __('reports.status_open') }}
                </span>
            @elseif($report->status === 'resolved')
                <span class="inline-flex items-center px-4 py-1.5 rounded-full text-sm font-bold bg-emerald-100 text-emerald-800 border border-emerald-300">
                    {{ __('reports.status_resolved') }}
                </span>
            @else
                <span class="inline-flex items-center px-4 py-1.5 rounded-full text-sm font-bold bg-slate-100 text-slate-700 border border-slate-300">
                    {{ __('reports.status_dismissed') }}
                </span>
            @endif
        </div>
    </div>

    {{-- Main 2 Column Grid --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Left: Report & Target Info (2 cols) --}}
        <div class="lg:col-span-2 space-y-6">
            {{-- Report Reason & Description Card --}}
            <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-xs space-y-4">
                <h2 class="text-base font-bold text-slate-900 border-b border-slate-100 pb-3">
                    زانیاریی سکاڵاکە
                </h2>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                    <div>
                        <span class="text-xs text-slate-400 block mb-1">{{ __('reports.reason') }}</span>
                        <span class="font-bold text-slate-900 text-base">
                            {{ __('reports.reason_' . $report->reason) }}
                        </span>
                    </div>

                    <div>
                        <span class="text-xs text-slate-400 block mb-1">{{ __('staff.reporter') }}</span>
                        <div class="font-medium text-slate-800">
                            {{ $report->reporter->full_name }} ({{ $report->reporter->phone }})
                        </div>
                    </div>
                </div>

                @if($report->details)
                    <div class="pt-3 border-t border-slate-100">
                        <span class="text-xs text-slate-400 block mb-1.5">{{ __('reports.details') }}</span>
                        <div class="p-3.5 bg-slate-50 rounded-xl text-sm text-slate-800 leading-relaxed whitespace-pre-line border border-slate-100">
                            {{ $report->details }}
                        </div>
                    </div>
                @endif

                @if($report->handled_by)
                    <div class="pt-3 border-t border-slate-100 text-xs text-slate-500 flex items-center justify-between">
                        <span>{{ __('staff.handled_by') }} <strong class="text-slate-800">{{ $report->handler?->full_name ?? '-' }}</strong></span>
                        <span>{{ __('staff.handled_at') }} {{ $report->handled_at?->format('Y-m-d H:i') }}</span>
                    </div>
                @endif
            </div>

            {{-- Target Info Card --}}
            @if($report->reported_product_id && $report->product)
                @php $product = $report->product; @endphp
                <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-xs space-y-5">
                    <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                        <h2 class="text-base font-bold text-slate-900 flex items-center gap-2">
                            <span>{{ __('reports.target_product') }}</span>
                            <span class="text-xs px-2 py-0.5 rounded-full font-semibold {{ $product->status === 'approved' ? 'bg-emerald-100 text-emerald-800' : ($product->status === 'removed' ? 'bg-red-100 text-red-800' : 'bg-slate-100 text-slate-700') }}">
                                {{ $product->status }}
                            </span>
                        </h2>
                        <a href="{{ route('products.show', $product->id) }}" target="_blank" class="text-xs font-semibold text-indigo-600 hover:underline">
                            بینینی لە ماڵپەڕ &rarr;
                        </a>
                    </div>

                    <div class="flex flex-col sm:flex-row gap-5">
                        {{-- Photo gallery preview --}}
                        <div class="sm:w-48 shrink-0">
                            @php
                                $mainImg = $product->images->firstWhere('is_main', true) ?? $product->images->first();
                            @endphp
                            @if($mainImg)
                                <img src="{{ asset('storage/' . $mainImg->image_path) }}" 
                                     alt="{{ $product->title }}" 
                                     class="w-full aspect-[4/3] rounded-xl object-cover border border-slate-200">
                            @endif
                            @if($product->images->count() > 1)
                                <div class="grid grid-cols-4 gap-1.5 mt-2">
                                    @foreach($product->images->take(4) as $img)
                                        <img src="{{ asset('storage/' . $img->image_path) }}" 
                                             class="w-full aspect-square rounded-md object-cover border border-slate-100">
                                    @endforeach
                                </div>
                            @endif
                        </div>

                        {{-- Details --}}
                        <div class="flex-1 space-y-2">
                            <h3 class="font-bold text-lg text-slate-900 leading-snug">{{ $product->title }}</h3>
                            <div class="text-xl font-extrabold text-indigo-600">
                                {{ \App\Helpers\MarketplaceHelper::formatPrice($product->price) }}
                            </div>
                            <div class="text-xs text-slate-500">
                                هاوپۆل: <span class="font-semibold text-slate-800">{{ $product->category->name }}</span> • 
                                بارودۆخ: <span class="font-semibold text-slate-800">{{ __('products.condition_' . $product->condition) }}</span>
                            </div>
                            <p class="text-xs text-slate-600 whitespace-pre-line line-clamp-3 mt-2">{{ $product->description }}</p>

                            <div class="pt-3 border-t border-slate-100 text-xs text-slate-600">
                                فرۆشیار: 
                                <strong class="text-slate-900">{{ $product->customer->shop_name ?? $product->customer->full_name }}</strong> 
                                ({{ $product->customer->phone }})
                                @if($product->customer->is_banned)
                                    <span class="mr-2 text-red-600 font-bold">[بلۆککراوە]</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @elseif($report->reported_customer_id && $report->reportedCustomer)
                @php $targetCustomer = $report->reportedCustomer; @endphp
                <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-xs space-y-4">
                    <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                        <h2 class="text-base font-bold text-slate-900">
                            {{ __('reports.target_customer') }}
                        </h2>
                        <span class="text-xs px-2.5 py-0.5 rounded-full font-semibold {{ $targetCustomer->is_banned ? 'bg-red-100 text-red-800' : 'bg-emerald-100 text-emerald-800' }}">
                            {{ $targetCustomer->is_banned ? __('staff.banned') : __('staff.active') }}
                        </span>
                    </div>

                    <div class="flex items-center gap-4">
                        @if($targetCustomer->type === 'shop' && $targetCustomer->logo)
                            <img src="{{ asset('storage/' . $targetCustomer->logo) }}" 
                                 class="w-16 h-16 rounded-xl object-cover border border-slate-200 shrink-0">
                        @else
                            <div class="w-16 h-16 rounded-xl bg-indigo-50 text-indigo-600 font-black text-2xl flex items-center justify-center shrink-0">
                                {{ mb_substr($targetCustomer->shop_name ?? $targetCustomer->full_name, 0, 1) }}
                            </div>
                        @endif

                        <div class="space-y-1 min-w-0">
                            <div class="flex items-center gap-2">
                                <h3 class="font-bold text-lg text-slate-900 truncate">
                                    {{ $targetCustomer->shop_name ?? $targetCustomer->full_name }}
                                </h3>
                                @if($targetCustomer->type === 'shop' && $targetCustomer->is_verified)
                                    <span class="text-xs bg-blue-50 text-blue-700 px-2 py-0.5 rounded-full font-semibold border border-blue-200">
                                        {{ __('public.verified_shop_badge') }}
                                    </span>
                                @endif
                            </div>
                            <div class="text-xs text-slate-500">
                                {{ $targetCustomer->phone }} • {{ $targetCustomer->email }}
                            </div>
                            <div class="text-xs text-slate-600">
                                {{ $targetCustomer->address }}
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            {{-- Other Reports on Same Target --}}
            @if($otherReports->isNotEmpty())
                <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-xs space-y-4">
                    <h2 class="text-base font-bold text-slate-900 border-b border-slate-100 pb-3 flex items-center justify-between">
                        <span>{{ __('staff.other_reports_on_target') }}</span>
                        <span class="text-xs bg-slate-100 px-2.5 py-0.5 rounded-full font-bold text-slate-700">
                            {{ $otherReports->count() }}
                        </span>
                    </h2>

                    <div class="divide-y divide-slate-100">
                        @foreach($otherReports as $other)
                            <div class="py-3 flex items-center justify-between gap-4 text-xs">
                                <div>
                                    <span class="font-bold text-slate-900">{{ __('reports.reason_' . $other->reason) }}</span>
                                    <span class="text-slate-400 mx-1.5">•</span>
                                    <span class="text-slate-600">{{ $other->reporter->full_name ?? '-' }}</span>
                                    <span class="text-slate-400 mx-1.5">•</span>
                                    <span class="text-slate-400">{{ $other->created_at->format('Y-m-d') }}</span>
                                </div>
                                <div>
                                    <a href="{{ route('staff.reports.show', $other->id) }}" class="text-indigo-600 font-semibold hover:underline">
                                        بینینی سکاڵا &rarr;
                                    </a>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>

        {{-- Right: Actions Box (1 col) --}}
        <div class="space-y-6">
            <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-xs space-y-5">
                <h2 class="text-base font-bold text-slate-900 border-b border-slate-100 pb-3">
                    {{ __('staff.actions') }}
                </h2>

                {{-- Resolve / Dismiss Actions (Open reports only) --}}
                @if($report->status === 'open')
                    <div class="space-y-3">
                        <form action="{{ route('staff.reports.resolve', $report->id) }}" method="POST" onsubmit="return confirm('{{ __('staff.resolve_confirm') }}')">
                            @csrf
                            <button type="submit" 
                                    class="w-full py-2.5 px-4 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl text-sm shadow-xs transition">
                                {{ __('staff.resolve_btn') }}
                            </button>
                        </form>

                        <form action="{{ route('staff.reports.dismiss', $report->id) }}" method="POST" onsubmit="return confirm('{{ __('staff.dismiss_confirm') }}')">
                            @csrf
                            <button type="submit" 
                                    class="w-full py-2.5 px-4 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-xl text-sm transition">
                                {{ __('staff.dismiss_btn') }}
                            </button>
                        </form>
                    </div>
                @else
                    <div class="p-4 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-500 text-center">
                        {{ __('staff.report_already_closed') }}
                    </div>
                @endif

                {{-- Moderation Actions: Remove Product & Ban Customer --}}
                <div class="pt-4 border-t border-slate-100 space-y-3">
                    <span class="text-xs font-bold text-slate-400 uppercase tracking-wider block">سزا و دەستکاری</span>

                    {{-- If reported target is product and not removed --}}
                    @if($report->reported_product_id && $report->product && $report->product->status !== 'removed')
                        <form action="{{ route('staff.reports.remove-product', $report->id) }}" method="POST" onsubmit="return confirm('ئایا دڵنیایت لە سڕینەوەی ئەم بەرهەمە لە ماڵپەڕ؟')">
                            @csrf
                            <button type="submit" 
                                    class="w-full py-2 px-3 bg-red-50 hover:bg-red-100 text-red-700 font-semibold rounded-lg text-xs transition border border-red-200">
                                {{ __('staff.remove_product_btn') }}
                            </button>
                        </form>
                    @endif

                    {{-- If seller / customer is not banned --}}
                    @php
                        $targetCust = $report->reportedCustomer ?? $report->product?->customer;
                    @endphp
                    @if($targetCust && ! $targetCust->is_banned)
                        <form action="{{ route('staff.reports.ban-customer', $report->id) }}" method="POST" onsubmit="return confirm('{{ __('staff.ban_confirm_text') }}')">
                            @csrf
                            <button type="submit" 
                                    class="w-full py-2 px-3 bg-rose-600 hover:bg-rose-700 text-white font-semibold rounded-lg text-xs transition">
                                {{ __('staff.ban_btn') }} ({{ $targetCust->full_name }})
                            </button>
                        </form>
                    @elseif($targetCust && $targetCust->is_banned)
                        <div class="text-center py-2 px-3 bg-rose-50 text-rose-700 rounded-lg text-xs font-semibold border border-rose-200">
                            بەکارهێنەر بلۆککراوە
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

