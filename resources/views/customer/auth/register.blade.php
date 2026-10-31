@extends('layouts.auth')

@section('title', __('app.register') . ' - ' . __('app.site_name'))
@section('heading', __('app.register'))
@section('card_width', 'sm:max-w-xl')

@section('content')
<form action="{{ route('register') }}" method="POST" enctype="multipart/form-data" class="space-y-5">
    @csrf

    <!-- Account Type (Radio Buttons) -->
    <div>
        <label class="block text-sm font-semibold text-gray-700 mb-2">
            {{ __('app.customer_type') }}
        </label>
        <div class="grid grid-cols-2 gap-3">
            <label class="cursor-pointer border rounded-xl p-3 flex items-center justify-between transition border-gray-300 hover:border-indigo-500 has-checked:border-indigo-600 has-checked:bg-indigo-50/40">
                <div class="flex items-center gap-2">
                    <input type="radio" name="type" value="regular" id="type_regular"
                        {{ old('type', 'regular') === 'regular' ? 'checked' : '' }}
                        class="text-indigo-600 focus:ring-indigo-500">
                    <span class="text-sm font-medium text-gray-800">{{ __('app.regular_user') }}</span>
                </div>
            </label>

            <label class="cursor-pointer border rounded-xl p-3 flex items-center justify-between transition border-gray-300 hover:border-indigo-500 has-checked:border-indigo-600 has-checked:bg-indigo-50/40">
                <div class="flex items-center gap-2">
                    <input type="radio" name="type" value="shop" id="type_shop"
                        {{ old('type') === 'shop' ? 'checked' : '' }}
                        class="text-indigo-600 focus:ring-indigo-500">
                    <span class="text-sm font-medium text-gray-800">{{ __('app.shop_user') }}</span>
                </div>
            </label>
        </div>
        @error('type')
            <p class="mt-1.5 text-xs text-red-600 font-medium">{{ $message }}</p>
        @enderror
    </div>

    <!-- Full Name -->
    <div>
        <label for="full_name" class="block text-sm font-medium text-gray-700 mb-1">
            {{ __('app.full_name') }} <span class="text-red-500">*</span>
        </label>
        <input type="text" name="full_name" id="full_name" value="{{ old('full_name') }}" required
            class="w-full px-3.5 py-2.5 rounded-lg border @error('full_name') border-red-300 bg-red-50/20 @else border-gray-300 @enderror focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm transition">
        @error('full_name')
            <p class="mt-1.5 text-xs text-red-600 font-medium">{{ $message }}</p>
        @enderror
    </div>

    <!-- Shop Specific Fields (Hidden by default, shown for Shop) -->
    <div id="shop_fields" class="{{ old('type') === 'shop' ? '' : 'hidden' }} space-y-5 p-4 bg-indigo-50/30 rounded-xl border border-indigo-100">
        <!-- Shop Name -->
        <div>
            <label for="shop_name" class="block text-sm font-medium text-gray-700 mb-1">
                {{ __('app.shop_name') }} <span class="text-red-500">*</span>
            </label>
            <input type="text" name="shop_name" id="shop_name" value="{{ old('shop_name') }}"
                class="w-full px-3.5 py-2.5 rounded-lg border @error('shop_name') border-red-300 bg-red-50/20 @else border-gray-300 @enderror focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm bg-white transition">
            @error('shop_name')
                <p class="mt-1.5 text-xs text-red-600 font-medium">{{ $message }}</p>
            @enderror
        </div>

        <!-- Shop Logo -->
        <div>
            <label for="logo" class="block text-sm font-medium text-gray-700 mb-1">
                {{ __('app.logo') }}
            </label>
            <input type="file" name="logo" id="logo" accept="image/png,image/jpeg,image/webp"
                class="w-full text-xs text-gray-500 file:mr-0 file:ml-3 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-indigo-100 file:text-indigo-700 hover:file:bg-indigo-200 cursor-pointer">
            <p class="mt-1 text-xs text-gray-500">{{ __('app.logo_hint') }}</p>
            @error('logo')
                <p class="mt-1.5 text-xs text-red-600 font-medium">{{ $message }}</p>
            @enderror
        </div>
    </div>

    <!-- Phone -->
    <div>
        <label for="phone" class="block text-sm font-medium text-gray-700 mb-1">
            {{ __('app.phone') }} <span class="text-red-500">*</span>
        </label>
        <input type="text" name="phone" id="phone" value="{{ old('phone') }}" required dir="ltr" placeholder="0750XXXXXXX"
            class="w-full text-right px-3.5 py-2.5 rounded-lg border @error('phone') border-red-300 bg-red-50/20 @else border-gray-300 @enderror focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm transition">
        <p class="mt-1 text-xs text-gray-500">{{ __('app.phone_placeholder') }}</p>
        @error('phone')
            <p class="mt-1.5 text-xs text-red-600 font-medium">{{ $message }}</p>
        @enderror
    </div>

    <!-- Email -->
    <div>
        <label for="email" class="block text-sm font-medium text-gray-700 mb-1">
            {{ __('app.email') }} <span class="text-red-500">*</span>
        </label>
        <input type="email" name="email" id="email" value="{{ old('email') }}" required dir="ltr"
            class="w-full text-right px-3.5 py-2.5 rounded-lg border @error('email') border-red-300 bg-red-50/20 @else border-gray-300 @enderror focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm transition">
        @error('email')
            <p class="mt-1.5 text-xs text-red-600 font-medium">{{ $message }}</p>
        @enderror
    </div>

    <!-- Address -->
    <div>
        <label for="address" class="block text-sm font-medium text-gray-700 mb-1">
            {{ __('app.address') }} <span class="text-red-500">*</span>
        </label>
        <input type="text" name="address" id="address" value="{{ old('address') }}" required
            placeholder="هەولێر، سلێمانی، دهۆک..."
            class="w-full px-3.5 py-2.5 rounded-lg border @error('address') border-red-300 bg-red-50/20 @else border-gray-300 @enderror focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm transition">
        @error('address')
            <p class="mt-1.5 text-xs text-red-600 font-medium">{{ $message }}</p>
        @enderror
    </div>

    <!-- Password -->
    <div>
        <label for="password" class="block text-sm font-medium text-gray-700 mb-1">
            {{ __('app.password') }} <span class="text-red-500">*</span>
        </label>
        <input type="password" name="password" id="password" required dir="ltr"
            class="w-full px-3.5 py-2.5 rounded-lg border @error('password') border-red-300 bg-red-50/20 @else border-gray-300 @enderror focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm transition">
        @error('password')
            <p class="mt-1.5 text-xs text-red-600 font-medium">{{ $message }}</p>
        @enderror
    </div>

    <!-- Password Confirmation -->
    <div>
        <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-1">
            {{ __('app.password_confirmation') }} <span class="text-red-500">*</span>
        </label>
        <input type="password" name="password_confirmation" id="password_confirmation" required dir="ltr"
            class="w-full px-3.5 py-2.5 rounded-lg border border-gray-300 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm transition">
    </div>

    <!-- Submit Button -->
    <div class="pt-2">
        <button type="submit"
            class="w-full py-3 px-4 rounded-xl text-white font-medium bg-indigo-600 hover:bg-indigo-700 focus:outline-hidden focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition shadow-sm">
            {{ __('app.register_btn') }}
        </button>
    </div>

    <div class="text-center pt-2">
        <a href="{{ route('login') }}" class="text-sm text-indigo-600 hover:text-indigo-800 font-medium transition">
            {{ __('app.have_account') }}
        </a>
    </div>
</form>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const typeRegular = document.getElementById('type_regular');
        const typeShop = document.getElementById('type_shop');
        const shopFields = document.getElementById('shop_fields');
        const shopNameInput = document.getElementById('shop_name');

        function toggleShopFields() {
            if (typeShop.checked) {
                shopFields.classList.remove('hidden');
            } else {
                shopFields.classList.add('hidden');
                if (shopNameInput) {
                    shopNameInput.value = '';
                }
            }
        }

        typeRegular.addEventListener('change', toggleShopFields);
        typeShop.addEventListener('change', toggleShopFields);
    });
</script>
@endsection

