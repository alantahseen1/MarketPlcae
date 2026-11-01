@extends('layouts.app')

@section('title', ($targetType === 'product' ? __('reports.report_product_title') : __('reports.report_customer_title')) . ' - ' . __('app.site_name'))

@section('content')
<div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
        {{-- Card Header --}}
        <div class="bg-gray-50 border-b border-gray-200 p-6">
            <h1 class="text-xl sm:text-2xl font-black text-gray-900 flex items-center gap-2">
                <svg class="w-6 h-6 text-red-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
                <span>{{ $targetType === 'product' ? __('reports.report_product_title') : __('reports.report_customer_title') }}</span>
            </h1>
        </div>

        <div class="p-6 sm:p-8 space-y-6">
            {{-- Target Preview Box --}}
            <div class="bg-gray-50 border border-gray-200 rounded-xl p-4 flex items-center gap-4">
                @if($targetType === 'product' && $product)
                    @php
                        $mainImg = $product->images->firstWhere('is_main', true) ?? $product->images->first();
                    @endphp
                    @if($mainImg)
                        <img src="{{ asset('storage/' . $mainImg->image_path) }}" 
                             alt="{{ $product->title }}" 
                             class="w-16 h-16 rounded-lg object-cover border border-gray-200 shrink-0">
                    @else
                        <div class="w-16 h-16 rounded-lg bg-gray-200 flex items-center justify-center text-gray-400 shrink-0">
                            <svg class="w-8 h-8 stroke-current" fill="none" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                        </div>
                    @endif

                    <div class="min-w-0 flex-1">
                        <span class="text-xs text-indigo-600 font-semibold block">{{ $product->category->name }}</span>
                        <h2 class="text-sm sm:text-base font-bold text-gray-900 truncate">{{ $product->title }}</h2>
                        <div class="text-xs text-gray-500 mt-1 flex items-center gap-2">
                            <span>{{ \App\Helpers\MarketplaceHelper::formatPrice($product->price) }}</span>
                            <span>•</span>
                            <span>{{ $product->customer->shop_name ?? $product->customer->full_name }}</span>
                        </div>
                    </div>
                @elseif($targetType === 'customer' && $targetCustomer)
                    @if($targetCustomer->type === 'shop' && $targetCustomer->logo)
                        <img src="{{ asset('storage/' . $targetCustomer->logo) }}" 
                             alt="{{ $targetCustomer->shop_name }}" 
                             class="w-16 h-16 rounded-xl object-cover border border-gray-200 shrink-0">
                    @else
                        <div class="w-16 h-16 rounded-xl bg-indigo-50 border border-indigo-100 text-indigo-600 font-bold text-2xl flex items-center justify-center shrink-0">
                            {{ mb_substr($targetCustomer->shop_name ?? $targetCustomer->full_name, 0, 1) }}
                        </div>
                    @endif

                    <div class="min-w-0 flex-1">
                        <div class="flex items-center gap-1.5">
                            <h2 class="text-base font-bold text-gray-900 truncate">
                                {{ $targetCustomer->shop_name ?? $targetCustomer->full_name }}
                            </h2>
                            @if($targetCustomer->type === 'shop' && $targetCustomer->is_verified)
                                <svg class="w-4 h-4 text-blue-500 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M6.267 3.455a3.066 3.066 0 001.745-.723 3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 3.066 3.066 0 012.812 2.812c.051.643.304 1.254.723 1.745a3.066 3.066 0 010 3.976 3.066 3.066 0 00-.723 1.745 3.066 3.066 0 01-2.812 2.812 3.066 3.066 0 00-1.745.723 3.066 3.066 0 01-3.976 0 3.066 3.066 0 00-1.745-.723 3.066 3.066 0 01-2.812-2.812 3.066 3.066 0 00-.723-1.745 3.066 3.066 0 010-3.976 3.066 3.066 0 00.723-1.745 3.066 3.066 0 012.812-2.812zm7.44 5.252a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                </svg>
                            @endif
                        </div>
                        <p class="text-xs text-gray-500 mt-1 truncate">{{ $targetCustomer->address }}</p>
                    </div>
                @endif
            </div>

            {{-- Validation Errors --}}
            @if($errors->any())
                <div class="p-4 rounded-xl bg-red-50 border border-red-200 text-red-800 text-sm">
                    <ul class="list-disc list-inside space-y-1">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- Form --}}
            <form action="{{ $targetType === 'product' ? route('reports.product.store', $product->id) : route('reports.customer.store', $targetCustomer->id) }}" 
                  method="POST" 
                  class="space-y-6">
                @csrf

                {{-- Reason Radio Group --}}
                <div>
                    <label class="block text-sm font-bold text-gray-900 mb-3">
                        {{ __('reports.reason') }} <span class="text-red-500">*</span>
                    </label>

                    <div class="space-y-2.5">
                        @php
                            $reasons = [
                                'spam' => __('reports.reason_spam'),
                                'scam' => __('reports.reason_scam'),
                                'fake_item' => __('reports.reason_fake_item'),
                                'other' => __('reports.reason_other'),
                            ];
                        @endphp

                        @foreach($reasons as $val => $label)
                            <label class="flex items-center gap-3 p-3.5 rounded-xl border border-gray-200 hover:bg-gray-50 cursor-pointer transition">
                                <input type="radio" 
                                       name="reason" 
                                       value="{{ $val }}" 
                                       {{ old('reason') === $val ? 'checked' : '' }}
                                       required
                                       class="w-4 h-4 text-red-600 focus:ring-red-500 border-gray-300">
                                <span class="text-sm font-medium text-gray-800">{{ $label }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>

                {{-- Details --}}
                <div>
                    <label for="details" class="block text-sm font-bold text-gray-900 mb-1.5">
                        {{ __('reports.details') }}
                    </label>
                    <p class="text-xs text-gray-500 mb-2">
                        {{ __('reports.details_required_for_other') }}
                    </p>
                    <textarea name="details" 
                              id="details" 
                              rows="4" 
                              maxlength="1000"
                              placeholder="{{ __('reports.details_placeholder') }}"
                              class="w-full px-3 py-2 text-sm rounded-xl border border-gray-300 focus:ring-red-500 focus:border-red-500">{{ old('details') }}</textarea>
                </div>

                {{-- Actions --}}
                <div class="flex items-center justify-between gap-4 pt-4 border-t border-gray-100">
                    <button type="submit" 
                            class="px-6 py-2.5 bg-red-600 hover:bg-red-700 text-white font-bold rounded-xl text-sm shadow-xs transition">
                        {{ __('reports.submit_report') }}
                    </button>

                    <a href="{{ $targetType === 'product' ? route('products.show', $product->id) : ($targetCustomer->type === 'shop' ? route('shops.show', $targetCustomer->id) : route('home')) }}" 
                       class="px-4 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold rounded-xl text-sm transition">
                        {{ __('reports.back') }}
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

