@props(['product'])

@php
    $imageUrl = \App\Helpers\MarketplaceHelper::thumbnailImageUrl($product);
    $seller = $product->customer;
    $isShop = $seller && $seller->type === 'shop';
    $sellerName = $isShop ? ($seller->shop_name ?? $seller->full_name) : ($seller->full_name ?? '');
    $isVerified = $isShop && $seller->is_verified;
@endphp

<article class="group bg-white rounded-xl border border-gray-200 overflow-hidden shadow-sm hover:shadow-md hover:border-gray-300 transition duration-200 flex flex-col h-full">
    {{-- Image Container --}}
    <a href="{{ route('products.show', $product->id) }}" class="relative block aspect-[4/3] bg-gray-100 overflow-hidden">
        @if($imageUrl)
            <img src="{{ $imageUrl }}" 
                 alt="{{ $product->title }}" 
                 class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                 loading="lazy">
        @else
            <div class="w-full h-full flex flex-col items-center justify-center text-gray-400 bg-gray-50">
                <svg class="w-12 h-12 stroke-current" fill="none" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
            </div>
        @endif

        {{-- Condition Badge --}}
        <div class="absolute top-2.5 right-2.5">
            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $product->condition === 'new' ? 'bg-emerald-100 text-emerald-800 border border-emerald-200' : 'bg-amber-100 text-amber-800 border border-amber-200' }}">
                {{ __('products.condition_'.$product->condition) }}
            </span>
        </div>

        {{-- Category Pill --}}
        @if($product->category)
            <div class="absolute bottom-2.5 right-2.5">
                <span class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-medium bg-black/60 text-white backdrop-blur-sm">
                    {{ $product->category->name }}
                </span>
            </div>
        @endif
    </a>

    {{-- Content --}}
    <div class="p-4 flex-1 flex flex-col justify-between">
        <div>
            <h3 class="font-bold text-gray-900 line-clamp-2 leading-snug group-hover:text-blue-600 transition-colors">
                <a href="{{ route('products.show', $product->id) }}">
                    {{ $product->title }}
                </a>
            </h3>

            <div class="mt-2 text-lg font-extrabold text-blue-700">
                {{ \App\Helpers\MarketplaceHelper::formatPrice($product->price) }}
            </div>
        </div>

        <div class="mt-4 pt-3 border-t border-gray-100 flex items-center justify-between text-xs text-gray-500">
            {{-- Seller info --}}
            <div class="flex items-center gap-1.5 truncate">
                @if($isShop)
                    <a href="{{ route('shops.show', $seller->id) }}" class="font-medium text-gray-700 hover:text-blue-600 truncate flex items-center gap-1">
                        <span class="truncate">{{ $sellerName }}</span>
                        @if($isVerified)
                            <svg class="w-4 h-4 text-blue-500 shrink-0" fill="currentColor" viewBox="0 0 20 20" title="{{ __('public.verified_shop_badge') }}">
                                <path fill-rule="evenodd" d="M6.267 3.455a3.066 3.066 0 001.745-.723 3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 3.066 3.066 0 012.812 2.812c.051.643.304 1.254.723 1.745a3.066 3.066 0 010 3.976 3.066 3.066 0 00-.723 1.745 3.066 3.066 0 01-2.812 2.812 3.066 3.066 0 00-1.745.723 3.066 3.066 0 01-3.976 0 3.066 3.066 0 00-1.745-.723 3.066 3.066 0 01-2.812-2.812 3.066 3.066 0 00-.723-1.745 3.066 3.066 0 010-3.976 3.066 3.066 0 00.723-1.745 3.066 3.066 0 012.812-2.812zm7.44 5.252a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                            </svg>
                        @endif
                    </a>
                @else
                    <span class="truncate">{{ $sellerName }}</span>
                @endif
            </div>

            {{-- Date --}}
            <span class="shrink-0 text-gray-400">
                {{ $product->created_at?->diffForHumans() }}
            </span>
        </div>
    </div>
</article>

