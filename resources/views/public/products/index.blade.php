@extends('layouts.app')

@section('title', ($selectedCategory ? $selectedCategory->name . ' - ' : '') . __('public.browse_products'))

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">
    {{-- Header / Breadcrumb & Title --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 border-b border-gray-200 pb-5">
        <div>
            <h1 class="text-2xl sm:text-3xl font-black text-gray-900">
                @if($selectedCategory)
                    {{ $selectedCategory->name }}
                @else
                    {{ __('public.browse_products') }}
                @endif
            </h1>
            <p class="text-sm text-gray-500 mt-1">
                {{ __('public.results_count', ['count' => $products->total()]) }}
            </p>
        </div>

        {{-- Mobile Filter Toggle Button --}}
        <div class="lg:hidden flex items-center justify-between gap-2">
            <button type="button" 
                    onclick="document.getElementById('filter-drawer').classList.toggle('hidden')" 
                    class="inline-flex items-center gap-2 px-4 py-2 bg-white border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50 transition">
                <svg class="w-4 h-4 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                </svg>
                <span>{{ __('public.filters') }}</span>
            </button>
        </div>
    </div>

    {{-- Main Grid: Filters Sidebar + Products --}}
    <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
        {{-- Filters Sidebar (Desktop and Mobile Drawer) --}}
        <aside id="filter-drawer" class="hidden lg:block lg:col-span-1">
            <div class="bg-white rounded-xl border border-gray-200 p-5 shadow-xs sticky top-20">
                <div class="flex items-center justify-between mb-4 border-b border-gray-100 pb-3">
                    <h2 class="text-base font-bold text-gray-900 flex items-center gap-2">
                        <svg class="w-5 h-5 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4" />
                        </svg>
                        <span>{{ __('public.filters') }}</span>
                    </h2>
                    @if(request()->hasAny(['q', 'category', 'condition', 'min_price', 'max_price', 'sort']))
                        <a href="{{ route('products.index') }}" class="text-xs font-semibold text-red-600 hover:text-red-700 transition">
                            {{ __('public.clear_filters') }}
                        </a>
                    @endif
                </div>

                <form action="{{ route('products.index') }}" method="GET" class="space-y-4">
                    {{-- Search Query --}}
                    <div>
                        <label for="q" class="block text-xs font-semibold text-gray-700 mb-1.5">
                            {{ __('public.search_placeholder') }}
                        </label>
                        <input type="text" 
                               name="q" 
                               id="q"
                               value="{{ request('q') }}" 
                               placeholder="{{ __('public.search_btn') }}..." 
                               class="w-full px-3 py-2 text-sm rounded-lg border border-gray-300 focus:ring-indigo-500 focus:border-indigo-500">
                    </div>

                    {{-- Category --}}
                    <div>
                        <label for="category" class="block text-xs font-semibold text-gray-700 mb-1.5">
                            {{ __('public.filter_by_category') }}
                        </label>
                        <select name="category" 
                                id="category" 
                                class="w-full px-3 py-2 text-sm rounded-lg border border-gray-300 focus:ring-indigo-500 focus:border-indigo-500 bg-white">
                            <option value="">{{ __('public.all_categories_option') }}</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" {{ (string) request('category') === (string) $category->id ? 'selected' : '' }}>
                                    {{ $category->name }} ({{ $category->products_count }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Condition --}}
                    <div>
                        <label for="condition" class="block text-xs font-semibold text-gray-700 mb-1.5">
                            {{ __('public.filter_by_condition') }}
                        </label>
                        <select name="condition" 
                                id="condition" 
                                class="w-full px-3 py-2 text-sm rounded-lg border border-gray-300 focus:ring-indigo-500 focus:border-indigo-500 bg-white">
                            <option value="">{{ __('public.all_conditions') }}</option>
                            <option value="new" {{ request('condition') === 'new' ? 'selected' : '' }}>
                                {{ __('products.condition_new') }}
                            </option>
                            <option value="used" {{ request('condition') === 'used' ? 'selected' : '' }}>
                                {{ __('products.condition_used') }}
                            </option>
                        </select>
                    </div>

                    {{-- Price Range --}}
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1.5">
                            {{ __('public.filter_by_price') }}
                        </label>
                        <div class="grid grid-cols-2 gap-2">
                            <input type="number" 
                                   name="min_price" 
                                   value="{{ request('min_price') }}" 
                                   placeholder="{{ __('public.min_price') }}" 
                                   min="0"
                                   step="1000"
                                   class="w-full px-3 py-2 text-xs rounded-lg border border-gray-300 focus:ring-indigo-500 focus:border-indigo-500">
                            <input type="number" 
                                   name="max_price" 
                                   value="{{ request('max_price') }}" 
                                   placeholder="{{ __('public.max_price') }}" 
                                   min="0"
                                   step="1000"
                                   class="w-full px-3 py-2 text-xs rounded-lg border border-gray-300 focus:ring-indigo-500 focus:border-indigo-500">
                        </div>
                    </div>

                    {{-- Sort --}}
                    <div>
                        <label for="sort" class="block text-xs font-semibold text-gray-700 mb-1.5">
                            {{ __('public.sort_by') }}
                        </label>
                        <select name="sort" 
                                id="sort" 
                                class="w-full px-3 py-2 text-sm rounded-lg border border-gray-300 focus:ring-indigo-500 focus:border-indigo-500 bg-white">
                            <option value="newest" {{ request('sort', 'newest') === 'newest' ? 'selected' : '' }}>
                                {{ __('public.sort_newest') }}
                            </option>
                            <option value="price_asc" {{ request('sort') === 'price_asc' ? 'selected' : '' }}>
                                {{ __('public.sort_price_asc') }}
                            </option>
                            <option value="price_desc" {{ request('sort') === 'price_desc' ? 'selected' : '' }}>
                                {{ __('public.sort_price_desc') }}
                            </option>
                        </select>
                    </div>

                    {{-- Actions --}}
                    <div class="pt-2 flex flex-col gap-2">
                        <button type="submit" 
                                class="w-full py-2.5 px-4 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold rounded-lg text-sm transition">
                            {{ __('public.apply_filters') }}
                        </button>

                        @if(request()->hasAny(['q', 'category', 'condition', 'min_price', 'max_price', 'sort']))
                            <a href="{{ route('products.index') }}" 
                               class="w-full py-2 px-4 bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium rounded-lg text-xs text-center transition">
                                {{ __('public.clear_filters') }}
                            </a>
                        @endif
                    </div>
                </form>
            </div>
        </aside>

        {{-- Products Grid --}}
        <section class="lg:col-span-3">
            @if($products->isEmpty())
                <div class="bg-white rounded-xl border border-gray-200 p-12 text-center">
                    <div class="w-16 h-16 mx-auto rounded-full bg-gray-100 text-gray-400 flex items-center justify-center mb-4">
                        <svg class="w-8 h-8 stroke-current" fill="none" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-bold text-gray-900 mb-1">
                        {{ __('public.no_products_found') }}
                    </h3>
                    <p class="text-sm text-gray-500 max-w-md mx-auto mb-6">
                        {{ __('public.try_other_search') }}
                    </p>
                    @if(request()->hasAny(['q', 'category', 'condition', 'min_price', 'max_price', 'sort']))
                        <a href="{{ route('products.index') }}" 
                           class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold rounded-lg text-sm transition">
                            {{ __('public.clear_filters') }}
                        </a>
                    @endif
                </div>
            @else
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4 sm:gap-6">
                    @foreach($products as $product)
                        <x-product-card :product="$product" />
                    @endforeach
                </div>

                {{-- Pagination Links --}}
                <div class="mt-8">
                    {{ $products->links() }}
                </div>
            @endif
        </section>
    </div>
</div>
@endsection

