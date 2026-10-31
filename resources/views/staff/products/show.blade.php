@extends('layouts.staff')

@section('title', $product->title . ' - ' . __('staff.product_details'))

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Top Bar -->
    <div class="mb-6 flex items-center justify-between">
        <div class="flex items-center gap-3">
            <a href="{{ route('staff.products.index') }}" class="text-sm font-semibold text-slate-600 hover:text-slate-900 bg-white border border-slate-300 px-3.5 py-2 rounded-xl transition">
                &larr; {{ __('staff.review_queue_title') }}
            </a>
            <h1 class="text-xl sm:text-2xl font-bold text-slate-900">{{ __('staff.product_details') }}</h1>
        </div>
        <div>
            <x-status-badge :status="$product->status" />
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Main Product Information (Left 2 cols in RTL) -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Photos Gallery -->
            <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-xs">
                <h3 class="text-base font-bold text-slate-900 mb-4">{{ __('products.photos') }} ({{ $product->images->count() }})</h3>
                <div class="grid grid-cols-2 sm:grid-cols-3 gap-4">
                    @foreach($product->images as $image)
                        <div class="relative aspect-square rounded-xl overflow-hidden border {{ $image->is_main ? 'border-indigo-600 ring-2 ring-indigo-200' : 'border-slate-200' }} bg-slate-100">
                            <img src="{{ asset('storage/' . $image->image_path) }}" alt="{{ $product->title }}" class="w-full h-full object-cover">
                            @if($image->is_main)
                                <span class="absolute top-2 right-2 px-2 py-0.5 rounded-md text-xs font-bold bg-indigo-600 text-white shadow-xs">
                                    {{ __('products.main_photo') }}
                                </span>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Product Specs -->
            <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-xs space-y-4">
                <div>
                    <h2 class="text-2xl font-extrabold text-slate-900">{{ $product->title }}</h2>
                    <div class="flex items-center gap-3 mt-2 text-sm text-slate-500">
                        <span class="text-xl font-bold text-indigo-600">{{ number_format($product->price) }} {{ __('products.iqd_currency') }}</span>
                        <span>&bull;</span>
                        <span class="font-medium text-slate-700">{{ $product->category->name }}</span>
                        <span>&bull;</span>
                        <span class="font-medium text-slate-700">{{ $product->condition === 'new' ? __('products.condition_new') : __('products.condition_used') }}</span>
                        <span>&bull;</span>
                        <span>{{ $product->created_at->format('Y-m-d H:i') }}</span>
                    </div>
                </div>

                <div class="pt-4 border-t border-slate-100">
                    <h4 class="text-sm font-bold text-slate-800 mb-2">{{ __('products.description') }}</h4>
                    <div class="text-sm text-slate-700 leading-relaxed whitespace-pre-line bg-slate-50 p-4 rounded-xl border border-slate-200">
                        {{ $product->description }}
                    </div>
                </div>

                @if($product->reviewed_by)
                    <div class="pt-4 border-t border-slate-100 text-xs text-slate-500 space-y-1">
                        <div><span class="font-bold text-slate-700">{{ __('staff.reviewed_by') }}</span> {{ $product->reviewer?->full_name ?? '#' . $product->reviewed_by }}</div>
                        <div><span class="font-bold text-slate-700">{{ __('staff.reviewed_at') }}</span> {{ $product->reviewed_at?->format('Y-m-d H:i') }}</div>
                        @if($product->rejection_reason)
                            <div class="text-red-600 bg-red-50 p-2.5 rounded-lg border border-red-200 mt-2">
                                <span class="font-bold">{{ __('staff.rejection_reason') }}:</span> {{ $product->rejection_reason }}
                            </div>
                        @endif
                    </div>
                @endif
            </div>
        </div>

        <!-- Right Side: Seller Info & Review Actions -->
        <div class="space-y-6">
            <!-- Review Actions (Only when pending) -->
            @if($product->status === 'pending')
                <div class="bg-white rounded-2xl border border-indigo-200 p-6 shadow-xs space-y-4">
                    <h3 class="text-base font-bold text-slate-900 border-b border-slate-100 pb-3">بڕیاری پێداچوونەوە</h3>

                    <!-- Approve Form -->
                    <form action="{{ route('staff.products.approve', $product->id) }}" method="POST"
                        onsubmit="return confirm('{{ __('staff.approve_confirm') }}')">
                        @csrf
                        <button type="submit"
                            class="w-full py-3 px-4 rounded-xl text-white font-bold bg-emerald-600 hover:bg-emerald-700 transition shadow-sm flex items-center justify-center gap-2">
                            <span>✓</span> {{ __('staff.approve_btn') }}
                        </button>
                    </form>

                    <!-- Reject Form -->
                    <div class="pt-4 border-t border-slate-100">
                        <form action="{{ route('staff.products.reject', $product->id) }}" method="POST"
                            onsubmit="return confirm('{{ __('staff.reject_confirm') }}')">
                            @csrf
                            <label for="rejection_reason" class="block text-xs font-bold text-slate-700 mb-1">
                                {{ __('staff.rejection_reason') }} <span class="text-red-500">*</span>
                            </label>
                            <textarea name="rejection_reason" id="rejection_reason" rows="3" required
                                placeholder="{{ __('staff.rejection_reason_placeholder') }}"
                                class="w-full text-xs p-3 rounded-lg border @error('rejection_reason') border-red-300 bg-red-50/20 @else border-slate-300 @enderror focus:ring-2 focus:ring-red-500 mb-2">{{ old('rejection_reason') }}</textarea>
                            @error('rejection_reason')
                                <p class="text-xs text-red-600 font-medium mb-2">{{ $message }}</p>
                            @enderror

                            <button type="submit"
                                class="w-full py-2.5 px-4 rounded-xl text-white font-bold bg-red-600 hover:bg-red-700 transition shadow-sm flex items-center justify-center gap-2">
                                <span>&times;</span> {{ __('staff.reject_btn') }}
                            </button>
                        </form>
                    </div>
                </div>
            @endif

            <!-- Seller Information Card -->
            <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-xs space-y-3">
                <h3 class="text-base font-bold text-slate-900 border-b border-slate-100 pb-3">{{ __('staff.seller_info') }}</h3>

                <div>
                    <span class="block text-xs text-slate-400">{{ __('staff.seller_name') }}</span>
                    <span class="text-sm font-bold text-slate-900">{{ $product->customer->full_name }}</span>
                    @if($product->customer->type === 'shop' && $product->customer->shop_name)
                        <span class="block text-xs font-semibold text-indigo-600">فرۆشگای {{ $product->customer->shop_name }}</span>
                    @endif
                </div>

                <div>
                    <span class="block text-xs text-slate-400">{{ __('staff.seller_type') }}</span>
                    <span class="text-xs font-semibold text-slate-700">
                        {{ $product->customer->type === 'shop' ? __('staff.shop_user') : __('staff.regular_user') }}
                    </span>
                    @if($product->customer->is_verified)
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold bg-blue-50 text-blue-700 border border-blue-200 mr-2">
                            ✓ {{ __('staff.verified_shop') }}
                        </span>
                    @endif
                </div>

                <div>
                    <span class="block text-xs text-slate-400">{{ __('staff.seller_phone') }}</span>
                    <a href="tel:{{ $product->customer->phone }}" class="text-sm font-semibold text-indigo-600 hover:underline" dir="ltr">
                        {{ $product->customer->phone }}
                    </a>
                </div>

                <div>
                    <span class="block text-xs text-slate-400">{{ __('staff.seller_address') }}</span>
                    <span class="text-sm text-slate-700">{{ $product->customer->address }}</span>
                </div>

                <div>
                    <span class="block text-xs text-slate-400">{{ __('staff.seller_status') }}</span>
                    @if($product->customer->is_banned)
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold bg-red-100 text-red-800">
                            {{ __('staff.banned') }}
                        </span>
                    @else
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700">
                            {{ __('staff.active') }}
                        </span>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

