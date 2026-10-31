@extends('layouts.app')

@section('title', __('products.my_products') . ' - ' . __('app.site_name'))

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Header with Action Button -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-8">
        <div>
            <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">{{ __('products.my_products') }}</h1>
            <p class="mt-1 text-sm text-gray-500">
                بەڕێوەبردنی سەرجەم بەرهەم و ریکلامەکانت لە بازاڕی ئەلیکترۆنی کوردستان
            </p>
        </div>

        <div>
            <a href="{{ route('customer.products.create') }}"
                class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl text-sm font-semibold text-white bg-indigo-600 hover:bg-indigo-700 shadow-xs transition">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                {{ __('products.add_product') }}
            </a>
        </div>
    </div>

    @if($products->isEmpty())
        <div class="bg-white rounded-2xl border border-gray-200 p-12 text-center shadow-xs">
            <div class="w-16 h-16 mx-auto mb-4 rounded-full bg-indigo-50 text-indigo-600 flex items-center justify-center">
                <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                </svg>
            </div>
            <h3 class="text-lg font-bold text-gray-800 mb-1">{{ __('products.no_products') }}</h3>
            <p class="text-sm text-gray-500 mb-6">دەتوانیت هەر ئێستا بە خۆڕایی یەکەم بەرهەمت دابنێیت بۆ فرۆشتن.</p>
            <a href="{{ route('customer.products.create') }}"
                class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl text-sm font-semibold text-white bg-indigo-600 hover:bg-indigo-700 transition">
                {{ __('products.add_product') }}
            </a>
        </div>
    @else
        <div class="grid grid-cols-1 gap-4">
            @foreach($products as $product)
                @php
                    $mainImage = $product->images->firstWhere('is_main', true) ?? $product->images->first();
                @endphp
                <div class="bg-white rounded-xl border border-gray-200 p-4 sm:p-5 flex flex-col sm:flex-row gap-4 items-start sm:items-center justify-between shadow-xs transition hover:border-gray-300">
                    <!-- Image and Details -->
                    <div class="flex items-center gap-4 w-full sm:w-auto">
                        <div class="w-20 h-20 sm:w-24 sm:h-24 rounded-lg bg-gray-100 overflow-hidden flex-shrink-0 border border-gray-200">
                            @if($mainImage)
                                <img src="{{ asset('storage/' . $mainImage->image_path) }}" alt="{{ $product->title }}" class="w-full h-full object-cover">
                            @else
                                <div class="w-full h-full flex items-center justify-center text-gray-400 text-xs">
                                    بێ وێنە
                                </div>
                            @endif
                        </div>

                        <div class="space-y-1">
                            <div class="flex items-center gap-2 flex-wrap">
                                <h3 class="text-base sm:text-lg font-bold text-gray-900">{{ $product->title }}</h3>

                                <!-- Status Badge -->
                                <x-status-badge :status="$product->status" />
                            </div>

                            <div class="flex items-center gap-3 text-xs sm:text-sm text-gray-500">
                                <span class="font-semibold text-indigo-600">{{ number_format($product->price) }} {{ __('products.iqd_currency') }}</span>
                                <span>&bull;</span>
                                <span>{{ $product->category->name }}</span>
                                <span>&bull;</span>
                                <span>{{ $product->condition === 'new' ? __('products.condition_new') : __('products.condition_used') }}</span>
                            </div>

                            @if($product->status === 'rejected' && $product->rejection_reason)
                                <div class="mt-2 text-xs text-red-600 bg-red-50/70 p-2 rounded-md border border-red-100">
                                    <span class="font-bold">{{ __('products.rejection_reason_label') }}</span> {{ $product->rejection_reason }}
                                </div>
                            @endif

                            @if($product->status === 'sold' && $product->sold_at)
                                <div class="text-xs text-gray-500">
                                    {{ __('products.sold_at_label') }} {{ $product->sold_at->format('Y-m-d') }}
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Actions Buttons -->
                    <div class="flex items-center gap-2 w-full sm:w-auto justify-end border-t sm:border-t-0 pt-3 sm:pt-0 border-gray-100">
                        @if($product->status === 'approved')
                            <form action="{{ route('customer.products.sold', $product->id) }}" method="POST" class="inline">
                                @csrf
                                <button type="submit"
                                    class="px-3 py-1.5 rounded-lg text-xs font-semibold text-emerald-700 bg-emerald-50 hover:bg-emerald-100 border border-emerald-200 transition">
                                    {{ __('products.mark_as_sold') }}
                                </button>
                            </form>
                        @endif

                        @if(in_array($product->status, ['pending', 'approved', 'rejected'], true))
                            <a href="{{ route('customer.products.edit', $product->id) }}"
                                class="px-3 py-1.5 rounded-lg text-xs font-semibold text-indigo-700 bg-indigo-50 hover:bg-indigo-100 border border-indigo-200 transition">
                                {{ __('products.edit') }}
                            </a>
                        @endif

                        <form action="{{ route('customer.products.destroy', $product->id) }}" method="POST" class="inline"
                            onsubmit="return confirm('{{ __('products.delete_confirm') }}')">
                            @csrf
                            @method('DELETE')
                            <button type="submit"
                                class="px-3 py-1.5 rounded-lg text-xs font-semibold text-red-600 hover:bg-red-50 border border-transparent hover:border-red-200 transition">
                                {{ __('products.delete') }}
                            </button>
                        </form>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="mt-6">
            {{ $products->links() }}
        </div>
    @endif
</div>
@endsection

