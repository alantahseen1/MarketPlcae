@extends('layouts.app')

@section('title', __('app.site_name') . ' - ' . __('public.latest_products'))

@section('content')
<div class="space-y-12 pb-16">
    {{-- Hero & Search Section --}}
    <section class="bg-linear-to-b from-indigo-50/70 to-white py-12 sm:py-16 border-b border-gray-100">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h1 class="text-3xl sm:text-4xl lg:text-5xl font-black text-gray-900 tracking-tight leading-tight">
                {{ __('app.site_name') }}
            </h1>
            <p class="mt-3 text-base sm:text-lg text-gray-600 max-w-2xl mx-auto">
                {{ __('app.welcome_subheading') }}
            </p>

            {{-- Main Search Bar --}}
            <form action="{{ route('products.index') }}" method="GET" class="mt-8 max-w-2xl mx-auto">
                <div class="relative flex items-center shadow-lg rounded-2xl bg-white border border-gray-200 overflow-hidden focus-within:ring-2 focus-within:ring-indigo-500 focus-within:border-indigo-500 transition">
                    <div class="pr-4 pl-2 text-gray-400">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                    <input type="text" 
                           name="q" 
                           value="{{ request('q') }}"
                           placeholder="{{ __('public.search_placeholder') }}" 
                           class="w-full py-4 pr-1 pl-4 text-gray-900 placeholder-gray-400 focus:outline-hidden text-sm sm:text-base border-none">
                    <div class="pl-2 pr-2">
                        <button type="submit" class="px-5 sm:px-7 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold rounded-xl text-sm transition">
                            {{ __('public.search_btn') }}
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </section>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-14">
        {{-- Categories Section --}}
        @if($categories->isNotEmpty())
            <section>
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-xl sm:text-2xl font-bold text-gray-900">
                        {{ __('public.all_categories') }}
                    </h2>
                </div>
                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-5 gap-3 sm:gap-4">
                    @foreach($categories as $cat)
                        <a href="{{ route('products.index', ['category' => $cat->id]) }}" 
                           class="group bg-white rounded-xl border border-gray-200 p-4 text-center hover:border-indigo-400 hover:shadow-md transition">
                            <div class="w-12 h-12 mx-auto rounded-full bg-indigo-50 group-hover:bg-indigo-600 text-indigo-600 group-hover:text-white flex items-center justify-center transition mb-3">
                                <svg class="w-6 h-6 stroke-current" fill="none" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                </svg>
                            </div>
                            <div class="font-bold text-gray-900 group-hover:text-indigo-600 transition text-sm sm:text-base">
                                {{ $cat->name }}
                            </div>
                            <div class="text-xs text-gray-500 mt-1">
                                {{ $cat->products_count }} {{ __('products.title') }}
                            </div>
                        </a>
                    @endforeach
                </div>
            </section>
        @endif

        {{-- Latest Approved Products --}}
        <section>
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h2 class="text-xl sm:text-2xl font-bold text-gray-900">
                        {{ __('public.latest_products') }}
                    </h2>
                    <p class="text-sm text-gray-500 mt-0.5">
                        {{ __('app.welcome_heading') }}
                    </p>
                </div>
                <a href="{{ route('products.index') }}" class="text-sm font-semibold text-indigo-600 hover:text-indigo-700 flex items-center gap-1 transition">
                    <span>{{ __('public.browse_products') }}</span>
                    <svg class="w-4 h-4 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </a>
            </div>

            @if($latestProducts->isEmpty())
                <div class="bg-white rounded-xl border border-gray-200 p-8 text-center text-gray-500">
                    {{ __('public.no_products_found') }}
                </div>
            @else
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 sm:gap-6">
                    @foreach($latestProducts as $product)
                        <x-product-card :product="$product" />
                    @endforeach
                </div>
            @endif
        </section>

        {{-- Featured / Verified Shops --}}
        @if($featuredShops->isNotEmpty())
            <section class="pt-6 border-t border-gray-200">
                <div class="flex items-center justify-between mb-6">
                    <div>
                        <h2 class="text-xl sm:text-2xl font-bold text-gray-900">
                            {{ __('public.featured_shops') }}
                        </h2>
                        <p class="text-sm text-gray-500 mt-0.5">
                            {{ __('public.verified_shop_badge') }}
                        </p>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4 sm:gap-6">
                    @foreach($featuredShops as $shop)
                        <a href="{{ route('shops.show', $shop->id) }}" class="group bg-white rounded-xl border border-gray-200 p-5 hover:border-indigo-400 hover:shadow-md transition flex items-center gap-4">
                            @if($shop->logo)
                                <img src="{{ asset('storage/' . $shop->logo) }}" 
                                     alt="{{ $shop->shop_name }}" 
                                     class="w-16 h-16 rounded-xl object-cover border border-gray-100 shrink-0">
                            @else
                                <div class="w-16 h-16 rounded-xl bg-indigo-50 border border-indigo-100 text-indigo-600 font-black text-xl flex items-center justify-center shrink-0">
                                    {{ mb_substr($shop->shop_name ?? $shop->full_name, 0, 1) }}
                                </div>
                            @endif

                            <div class="min-w-0 flex-1">
                                <div class="flex items-center gap-1.5">
                                    <h3 class="font-bold text-gray-900 group-hover:text-indigo-600 transition truncate">
                                        {{ $shop->shop_name }}
                                    </h3>
                                    @if($shop->is_verified)
                                        <svg class="w-4 h-4 text-blue-500 shrink-0" fill="currentColor" viewBox="0 0 20 20" title="{{ __('public.verified_shop_badge') }}">
                                            <path fill-rule="evenodd" d="M6.267 3.455a3.066 3.066 0 001.745-.723 3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 3.066 3.066 0 012.812 2.812c.051.643.304 1.254.723 1.745a3.066 3.066 0 010 3.976 3.066 3.066 0 00-.723 1.745 3.066 3.066 0 01-2.812 2.812 3.066 3.066 0 00-1.745.723 3.066 3.066 0 01-3.976 0 3.066 3.066 0 00-1.745-.723 3.066 3.066 0 01-2.812-2.812 3.066 3.066 0 00-.723-1.745 3.066 3.066 0 010-3.976 3.066 3.066 0 00.723-1.745 3.066 3.066 0 012.812-2.812zm7.44 5.252a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                        </svg>
                                    @endif
                                </div>
                                <div class="text-xs text-gray-500 mt-1 truncate">
                                    {{ $shop->address }}
                                </div>
                                <div class="text-xs font-medium text-indigo-600 mt-2">
                                    {{ __('public.shop_products_count', ['count' => $shop->products_count]) }}
                                </div>
                            </div>
                        </a>
                    @endforeach
                </div>
            </section>
        @endif

        {{-- Notice Banner --}}
        <div class="bg-amber-50 border border-amber-200 rounded-xl p-4 sm:p-5 flex items-start gap-3.5 text-amber-900 text-sm">
            <svg class="w-5 h-5 text-amber-600 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <div>
                <span class="font-bold">{{ __('public.offline_payment_notice') }}</span>
            </div>
        </div>
    </div>
</div>
@endsection
