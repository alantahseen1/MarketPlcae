@extends('layouts.app')

@section('title', $product->title . ' - ' . __('app.site_name'))
@section('meta_description', $product->title . ' بە نرخی ' . \App\Helpers\MarketplaceHelper::formatPrice($product->price) . '. ' . \Illuminate\Support\Str::limit(strip_tags($product->description), 130))
@section('og_title', $product->title . ' - ' . \App\Helpers\MarketplaceHelper::formatPrice($product->price))
@section('og_description', $product->title . ' بە نرخی ' . \App\Helpers\MarketplaceHelper::formatPrice($product->price) . '. ' . \Illuminate\Support\Str::limit(strip_tags($product->description), 130))
@section('og_type', 'product')
@if(\App\Helpers\MarketplaceHelper::mainImageUrl($product))
    @section('og_image', \App\Helpers\MarketplaceHelper::mainImageUrl($product))
@endif

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8">
    {{-- Breadcrumb --}}
    <nav class="flex items-center gap-2 text-xs sm:text-sm text-gray-500">
        <a href="{{ route('home') }}" class="hover:text-indigo-600 transition">{{ __('app.home') }}</a>
        <span class="text-gray-300">/</span>
        <a href="{{ route('products.index', ['category' => $product->category_id]) }}" class="hover:text-indigo-600 transition">{{ $product->category->name }}</a>
        <span class="text-gray-300">/</span>
        <span class="text-gray-900 font-medium truncate max-w-xs sm:max-w-md">{{ $product->title }}</span>
    </nav>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 lg:gap-10">
        {{-- Left / Center: Gallery & Product Info (2 columns) --}}
        <div class="lg:col-span-2 space-y-6">
            {{-- Image Gallery --}}
            <div class="bg-white rounded-2xl border border-gray-200 p-4 shadow-xs space-y-4">
                @php
                    $mainImg = $product->images->firstWhere('is_main', true) ?? $product->images->first();
                @endphp

                <div class="relative aspect-[4/3] bg-gray-100 rounded-xl overflow-hidden flex items-center justify-center">
                    @if($mainImg)
                        <img id="main-product-image" 
                             src="{{ asset('storage/' . $mainImg->image_path) }}" 
                             alt="{{ $product->title }}" 
                             class="w-full h-full object-contain bg-gray-900/5">
                    @else
                        <div class="text-gray-400 flex flex-col items-center">
                            <svg class="w-16 h-16 stroke-current" fill="none" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                        </div>
                    @endif
                </div>

                {{-- Thumbnails --}}
                @if($product->images->count() > 1)
                    <div class="flex gap-3 overflow-x-auto pb-2">
                        @foreach($product->images as $img)
                            <button type="button" 
                                    onclick="document.getElementById('main-product-image').src = '{{ asset('storage/' . $img->image_path) }}'"
                                    class="w-20 h-20 rounded-lg overflow-hidden border-2 border-transparent hover:border-indigo-600 focus:border-indigo-600 shrink-0 transition">
                                <img src="{{ asset('storage/' . $img->image_path) }}" 
                                     alt="{{ $product->title }}" 
                                     class="w-full h-full object-cover">
                            </button>
                        @endforeach
                    </div>
                @endif
            </div>

            {{-- Description Section --}}
            <div class="bg-white rounded-2xl border border-gray-200 p-6 shadow-xs space-y-4">
                <h2 class="text-lg font-bold text-gray-900 border-b border-gray-100 pb-3">
                    {{ __('products.description') }}
                </h2>
                <div class="text-gray-700 leading-relaxed text-sm sm:text-base whitespace-pre-line">
                    {{ $product->description }}
                </div>
            </div>
        </div>

        {{-- Right: Product Summary & Seller Card (1 column) --}}
        <div class="space-y-6">
            {{-- Price & Overview Card --}}
            <div class="bg-white rounded-2xl border border-gray-200 p-6 shadow-xs space-y-5">
                <div class="flex items-center justify-between gap-2">
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold {{ $product->condition === 'new' ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }}">
                        {{ __('products.condition_' . $product->condition) }}
                    </span>
                    <a href="{{ route('products.index', ['category' => $product->category_id]) }}" class="text-xs font-medium text-indigo-600 hover:underline">
                        {{ $product->category->name }}
                    </a>
                </div>

                <h1 class="text-xl sm:text-2xl font-black text-gray-900 leading-snug">
                    {{ $product->title }}
                </h1>

                <div class="p-4 bg-gray-50 rounded-xl border border-gray-100">
                    <span class="text-xs text-gray-500 block mb-1">{{ __('products.price') }}</span>
                    <span class="text-2xl sm:text-3xl font-extrabold text-indigo-700">
                        {{ \App\Helpers\MarketplaceHelper::formatPrice($product->price) }}
                    </span>
                </div>

                <div class="text-xs text-gray-500 flex items-center justify-between border-t border-gray-100 pt-3">
                    <span>{{ __('public.posted_at') }}</span>
                    <span class="font-medium text-gray-700">{{ $product->created_at?->format('Y-m-d') }} ({{ $product->created_at?->diffForHumans() }})</span>
                </div>
            </div>

            {{-- Seller Contact Card --}}
            @php
                $seller = $product->customer;
                $isShop = $seller && $seller->type === 'shop';
                $sellerName = $isShop ? ($seller->shop_name ?? $seller->full_name) : ($seller->full_name ?? '');
            @endphp

            <div class="bg-white rounded-2xl border border-gray-200 p-6 shadow-xs space-y-5">
                <h2 class="text-base font-bold text-gray-900 border-b border-gray-100 pb-3 flex items-center justify-between">
                    <span>{{ __('public.seller_contact') }}</span>
                    @if($isShop)
                        <span class="text-xs bg-indigo-50 text-indigo-600 px-2 py-0.5 rounded-full font-medium">
                            {{ __('customers.type_shop') }}
                        </span>
                    @endif
                </h2>

                <div class="flex items-center gap-4">
                    @if($isShop && $seller->logo)
                        <img src="{{ asset('storage/' . $seller->logo) }}" 
                             alt="{{ $sellerName }}" 
                             class="w-14 h-14 rounded-full object-cover border border-gray-200 shrink-0">
                    @else
                        <div class="w-14 h-14 rounded-full bg-indigo-50 border border-indigo-100 text-indigo-600 font-black text-xl flex items-center justify-center shrink-0">
                            {{ mb_substr($sellerName, 0, 1) }}
                        </div>
                    @endif

                    <div class="min-w-0 flex-1">
                        <div class="flex items-center gap-1.5">
                            <span class="font-bold text-gray-900 truncate">{{ $sellerName }}</span>
                            @if($isShop && $seller->is_verified)
                                <svg class="w-4 h-4 text-blue-500 shrink-0" fill="currentColor" viewBox="0 0 20 20" title="{{ __('public.verified_shop_badge') }}">
                                    <path fill-rule="evenodd" d="M6.267 3.455a3.066 3.066 0 001.745-.723 3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 3.066 3.066 0 012.812 2.812c.051.643.304 1.254.723 1.745a3.066 3.066 0 010 3.976 3.066 3.066 0 00-.723 1.745 3.066 3.066 0 01-2.812 2.812 3.066 3.066 0 00-1.745.723 3.066 3.066 0 01-3.976 0 3.066 3.066 0 00-1.745-.723 3.066 3.066 0 01-2.812-2.812 3.066 3.066 0 00-.723-1.745 3.066 3.066 0 010-3.976 3.066 3.066 0 00.723-1.745 3.066 3.066 0 012.812-2.812zm7.44 5.252a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                </svg>
                            @endif
                        </div>
                        <p class="text-xs text-gray-500 mt-1 truncate">
                            {{ $seller->address }}
                        </p>
                    </div>
                </div>

                {{-- Contact Actions --}}
                <div class="space-y-3 pt-2">
                    {{-- Phone Call Button --}}
                    <a href="tel:{{ $seller->phone }}" 
                       class="w-full flex items-center justify-center gap-2 py-3 px-4 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl shadow-xs transition">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                        </svg>
                        <span>{{ __('public.call_seller') }}: <span dir="ltr">{{ $seller->phone }}</span></span>
                    </a>

                    {{-- WhatsApp Button --}}
                    <a href="{{ \App\Helpers\MarketplaceHelper::whatsappUrl($seller->phone) }}" 
                       target="_blank" 
                       rel="noopener noreferrer"
                       class="w-full flex items-center justify-center gap-2 py-3 px-4 bg-[#25D366] hover:bg-[#20ba59] text-white font-bold rounded-xl shadow-xs transition">
                        <svg class="w-5 h-5 fill-current" viewBox="0 0 24 24">
                            <path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z" />
                        </svg>
                        <span>{{ __('public.whatsapp_seller') }}</span>
                    </a>

                    @if($isShop)
                        <a href="{{ route('shops.show', $seller->id) }}" 
                           class="w-full block text-center py-2.5 px-4 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 font-semibold rounded-xl text-sm transition">
                            {{ __('public.view_shop') }}
                        </a>
                    @endif
                </div>
            </div>

            {{-- Offline Payment Notice --}}
            <div class="bg-amber-50 border border-amber-200 rounded-xl p-4 text-xs text-amber-900 leading-relaxed">
                {{ __('public.offline_payment_notice') }}
            </div>

            {{-- Report Button --}}
            <div class="text-center pt-1">
                <a href="{{ route('reports.product.create', $product->id) }}" 
                   class="inline-flex items-center gap-1.5 text-xs font-semibold text-gray-400 hover:text-red-600 transition">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                    <span>{{ __('reports.report_btn') }}</span>
                </a>
            </div>
        </div>
    </div>

    {{-- Related Products Section --}}
    @if($relatedProducts->isNotEmpty())
        <section class="pt-8 border-t border-gray-200">
            <h2 class="text-xl font-bold text-gray-900 mb-6">
                {{ __('public.related_products') }}
            </h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4 sm:gap-6">
                @foreach($relatedProducts as $related)
                    <x-product-card :product="$related" />
                @endforeach
            </div>
        </section>
    @endif
</div>
@endsection

