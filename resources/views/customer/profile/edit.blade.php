@extends('layouts.app')

@section('title', __('profile.my_profile') . ' - ' . __('app.site_name'))

@section('content')
<div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-10 space-y-8">
    {{-- Header & Sub-nav --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 border-b border-gray-200 pb-5">
        <div>
            <h1 class="text-2xl sm:text-3xl font-black text-gray-900">{{ __('profile.my_profile') }}</h1>
            <p class="text-sm text-gray-500 mt-1">
                {{ $customer->type === 'shop' ? __('customers.type_shop') : __('customers.type_regular') }} • {{ $customer->email }}
            </p>
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ route('customer.profile.edit') }}" 
               class="px-4 py-2 text-xs font-bold rounded-xl bg-indigo-600 text-white shadow-xs">
                {{ __('profile.edit_profile') }}
            </a>
            <a href="{{ route('customer.password.edit') }}" 
               class="px-4 py-2 text-xs font-semibold rounded-xl bg-gray-100 hover:bg-gray-200 text-gray-700 transition">
                {{ __('profile.change_password') }}
            </a>
        </div>
    </div>

    {{-- Edit Form --}}
    <div class="bg-white rounded-2xl border border-gray-200 p-6 sm:p-8 shadow-xs">
        <form action="{{ route('customer.profile.update') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
            @csrf
            @method('PUT')

            {{-- Full Name --}}
            <div>
                <label for="full_name" class="block text-sm font-bold text-gray-900 mb-1.5">
                    {{ __('customers.full_name') }} <span class="text-red-500">*</span>
                </label>
                <input type="text" 
                       name="full_name" 
                       id="full_name" 
                       value="{{ old('full_name', $customer->full_name) }}" 
                       required 
                       maxlength="100"
                       class="w-full px-4 py-2.5 text-sm rounded-xl border border-gray-300 focus:ring-indigo-500 focus:border-indigo-500 @error('full_name') border-red-500 @enderror">
                @error('full_name')
                    <p class="mt-1.5 text-xs text-red-600 font-semibold">{{ $message }}</p>
                @enderror
            </div>

            {{-- Shop Name (Shops only) --}}
            @if($customer->type === 'shop')
                <div>
                    <label for="shop_name" class="block text-sm font-bold text-gray-900 mb-1.5">
                        {{ __('customers.shop_name') }} <span class="text-red-500">*</span>
                    </label>
                    <input type="text" 
                           name="shop_name" 
                           id="shop_name" 
                           value="{{ old('shop_name', $customer->shop_name) }}" 
                           required 
                           maxlength="100"
                           class="w-full px-4 py-2.5 text-sm rounded-xl border border-gray-300 focus:ring-indigo-500 focus:border-indigo-500 @error('shop_name') border-red-500 @enderror">
                    @error('shop_name')
                        <p class="mt-1.5 text-xs text-red-600 font-semibold">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Logo (Shops only) --}}
                <div class="p-4 bg-gray-50 rounded-xl border border-gray-200 space-y-3">
                    <label class="block text-sm font-bold text-gray-900">
                        {{ __('profile.shop_logo') }}
                    </label>

                    <div class="flex items-center gap-4">
                        @if($customer->logo)
                            <img src="{{ asset('storage/' . $customer->logo) }}" 
                                 alt="{{ $customer->shop_name }}" 
                                 class="w-16 h-16 rounded-xl object-cover border border-gray-300 shrink-0">
                        @else
                            <div class="w-16 h-16 rounded-xl bg-indigo-50 border border-indigo-100 text-indigo-600 font-bold text-2xl flex items-center justify-center shrink-0">
                                {{ mb_substr($customer->shop_name ?? $customer->full_name, 0, 1) }}
                            </div>
                        @endif

                        <div class="flex-1">
                            <input type="file" 
                                   name="logo" 
                                   id="logo" 
                                   accept="image/jpeg,image/png,image/webp"
                                   class="text-xs text-gray-500 file:mr-0 file:ml-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                            <p class="text-xs text-gray-400 mt-1">JPG، PNG یان WebP (ئەوپەڕی ٢ مێگابایت)</p>
                        </div>
                    </div>
                    @error('logo')
                        <p class="mt-1 text-xs text-red-600 font-semibold">{{ $message }}</p>
                    @enderror
                </div>
            @endif

            {{-- Phone --}}
            <div>
                <label for="phone" class="block text-sm font-bold text-gray-900 mb-1.5">
                    {{ __('customers.phone') }} <span class="text-red-500">*</span>
                </label>
                <input type="text" 
                       name="phone" 
                       id="phone" 
                       dir="ltr"
                       value="{{ old('phone', $customer->phone) }}" 
                       required 
                       placeholder="0750XXXXXXX"
                       class="w-full px-4 py-2.5 text-sm rounded-xl border border-gray-300 focus:ring-indigo-500 focus:border-indigo-500 @error('phone') border-red-500 @enderror">
                <p class="text-xs text-gray-400 mt-1">ژمارەی تەلەفۆن بۆ پەیوەندی و وەتسئەپی سەر بەرهەمەکانت بەکاردێت.</p>
                @error('phone')
                    <p class="mt-1.5 text-xs text-red-600 font-semibold">{{ $message }}</p>
                @enderror
            </div>

            {{-- Address --}}
            <div>
                <label for="address" class="block text-sm font-bold text-gray-900 mb-1.5">
                    {{ __('customers.address') }} <span class="text-red-500">*</span>
                </label>
                <input type="text" 
                       name="address" 
                       id="address" 
                       value="{{ old('address', $customer->address) }}" 
                       required 
                       maxlength="255"
                       placeholder="شار - گەڕەک / شەقام"
                       class="w-full px-4 py-2.5 text-sm rounded-xl border border-gray-300 focus:ring-indigo-500 focus:border-indigo-500 @error('address') border-red-500 @enderror">
                @error('address')
                    <p class="mt-1.5 text-xs text-red-600 font-semibold">{{ $message }}</p>
                @enderror
            </div>

            {{-- Readonly Email Notice --}}
            <div>
                <label class="block text-sm font-bold text-gray-400 mb-1.5">
                    {{ __('customers.email') }}
                </label>
                <input type="text" 
                       value="{{ $customer->email }}" 
                       disabled 
                       class="w-full px-4 py-2.5 text-sm rounded-xl bg-gray-100 border border-gray-200 text-gray-500 cursor-not-allowed">
                <p class="text-xs text-gray-400 mt-1">{{ __('profile.email_cannot_change') }}</p>
            </div>

            {{-- Submit --}}
            <div class="pt-4 border-t border-gray-100">
                <button type="submit" 
                        class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl text-sm shadow-xs transition">
                    {{ __('profile.save_changes') }}
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

