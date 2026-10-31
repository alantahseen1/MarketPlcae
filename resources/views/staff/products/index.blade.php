@extends('layouts.staff')

@section('title', __('staff.review_queue_title') . ' - ' . __('staff.panel_title'))

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl sm:text-3xl font-bold text-slate-900">{{ __('staff.review_queue_title') }}</h1>
            <p class="mt-1 text-sm text-slate-500">
                پێداچوونەوە و هەڵسەنگاندنی بەرهەمە زیادکراوەکانی بەکارهێنەران
            </p>
        </div>
    </div>

    <!-- Filter Tabs -->
    <div class="flex items-center gap-2 border-b border-slate-200 pb-3 mb-6 overflow-x-auto">
        <a href="{{ route('staff.products.index', ['status' => 'pending']) }}"
            class="px-4 py-2 text-sm font-semibold rounded-xl transition whitespace-nowrap {{ $activeTab === 'pending' ? 'bg-indigo-600 text-white shadow-xs' : 'bg-white text-slate-600 hover:bg-slate-200' }}">
            {{ __('staff.tab_pending') }}
        </a>
        <a href="{{ route('staff.products.index', ['status' => 'approved']) }}"
            class="px-4 py-2 text-sm font-semibold rounded-xl transition whitespace-nowrap {{ $activeTab === 'approved' ? 'bg-indigo-600 text-white shadow-xs' : 'bg-white text-slate-600 hover:bg-slate-200' }}">
            {{ __('staff.tab_approved') }}
        </a>
        <a href="{{ route('staff.products.index', ['status' => 'rejected']) }}"
            class="px-4 py-2 text-sm font-semibold rounded-xl transition whitespace-nowrap {{ $activeTab === 'rejected' ? 'bg-indigo-600 text-white shadow-xs' : 'bg-white text-slate-600 hover:bg-slate-200' }}">
            {{ __('staff.tab_rejected') }}
        </a>
        <a href="{{ route('staff.products.index', ['status' => 'all']) }}"
            class="px-4 py-2 text-sm font-semibold rounded-xl transition whitespace-nowrap {{ $activeTab === 'all' ? 'bg-indigo-600 text-white shadow-xs' : 'bg-white text-slate-600 hover:bg-slate-200' }}">
            {{ __('staff.tab_all') }}
        </a>
    </div>

    @if($products->isEmpty())
        <div class="bg-white rounded-2xl border border-slate-200 p-12 text-center shadow-xs">
            <p class="text-base font-semibold text-slate-700">
                {{ $activeTab === 'pending' ? __('staff.no_pending_reviews') : __('staff.no_products_found') }}
            </p>
        </div>
    @else
        <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-right text-sm">
                    <thead class="bg-slate-50 text-slate-600 font-semibold border-b border-slate-200">
                        <tr>
                            <th class="py-3 px-4">وێنە</th>
                            <th class="py-3 px-4">بەرهەم</th>
                            <th class="py-3 px-4">هاوپۆل</th>
                            <th class="py-3 px-4">نرخ</th>
                            <th class="py-3 px-4">فرۆشیار</th>
                            <th class="py-3 px-4">دۆخ</th>
                            <th class="py-3 px-4">بەرواری ناردن</th>
                            <th class="py-3 px-4 text-left">کردار</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($products as $product)
                            @php
                                $firstImage = $product->images->firstWhere('is_main', true) ?? $product->images->first();
                                $sellerDisplayName = $product->customer->type === 'shop' && $product->customer->shop_name
                                    ? $product->customer->shop_name
                                    : $product->customer->full_name;
                            @endphp
                            <tr class="hover:bg-slate-50 transition">
                                <td class="py-3 px-4">
                                    <div class="w-14 h-14 rounded-lg bg-slate-100 overflow-hidden flex-shrink-0 border border-slate-200">
                                        @if($firstImage)
                                            <img src="{{ asset('storage/' . $firstImage->image_path) }}" alt="{{ $product->title }}" class="w-full h-full object-cover">
                                        @else
                                            <div class="w-full h-full flex items-center justify-center text-xs text-slate-400">بێ وێنە</div>
                                        @endif
                                    </div>
                                </td>
                                <td class="py-3 px-4 font-bold text-slate-900">
                                    <a href="{{ route('staff.products.show', $product->id) }}" class="hover:text-indigo-600 transition">
                                        {{ $product->title }}
                                    </a>
                                </td>
                                <td class="py-3 px-4 text-slate-600">
                                    {{ $product->category->name }}
                                </td>
                                <td class="py-3 px-4 font-semibold text-indigo-700 whitespace-nowrap">
                                    {{ number_format($product->price) }} {{ __('products.iqd_currency') }}
                                </td>
                                <td class="py-3 px-4">
                                    <span class="font-medium text-slate-800">{{ $sellerDisplayName }}</span>
                                    @if($product->customer->type === 'shop')
                                        <span class="text-xs bg-indigo-50 text-indigo-600 px-1.5 py-0.5 rounded-sm mr-1">فرۆشگا</span>
                                    @endif
                                </td>
                                <td class="py-3 px-4 whitespace-nowrap">
                                    <x-status-badge :status="$product->status" />
                                </td>
                                <td class="py-3 px-4 text-xs text-slate-500 whitespace-nowrap">
                                    {{ $product->created_at->format('Y-m-d H:i') }}
                                </td>
                                <td class="py-3 px-4 text-left whitespace-nowrap">
                                    <a href="{{ route('staff.products.show', $product->id) }}"
                                        class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg text-xs font-semibold text-white bg-slate-800 hover:bg-slate-900 transition">
                                        وردەکاری
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="p-4 border-t border-slate-100">
                {{ $products->links() }}
            </div>
        </div>
    @endif
</div>
@endsection

