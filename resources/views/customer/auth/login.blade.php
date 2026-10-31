@extends('layouts.auth')

@section('title', __('app.login') . ' - ' . __('app.site_name'))
@section('heading', __('app.login'))

@section('content')
<form action="{{ route('login') }}" method="POST" class="space-y-5">
    @csrf

    <!-- Email -->
    <div>
        <label for="email" class="block text-sm font-medium text-gray-700 mb-1">
            {{ __('app.email') }}
        </label>
        <input type="email" name="email" id="email" value="{{ old('email') }}" required autofocus dir="ltr"
            class="w-full text-right px-3.5 py-2.5 rounded-lg border @error('email') border-red-300 bg-red-50/20 @else border-gray-300 @enderror focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm transition">
        @error('email')
            <p class="mt-1.5 text-xs text-red-600 font-medium">{{ $message }}</p>
        @enderror
    </div>

    <!-- Password -->
    <div>
        <label for="password" class="block text-sm font-medium text-gray-700 mb-1">
            {{ __('app.password') }}
        </label>
        <input type="password" name="password" id="password" required dir="ltr"
            class="w-full px-3.5 py-2.5 rounded-lg border @error('password') border-red-300 bg-red-50/20 @else border-gray-300 @enderror focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm transition">
        @error('password')
            <p class="mt-1.5 text-xs text-red-600 font-medium">{{ $message }}</p>
        @enderror
    </div>

    <!-- Remember Me -->
    <div class="flex items-center justify-between">
        <label class="flex items-center gap-2 cursor-pointer">
            <input type="checkbox" name="remember" id="remember" class="rounded-sm border-gray-300 text-indigo-600 focus:ring-indigo-500">
            <span class="text-sm text-gray-600">{{ __('app.remember_me') }}</span>
        </label>
    </div>

    <!-- Submit Button -->
    <div class="pt-2">
        <button type="submit"
            class="w-full py-3 px-4 rounded-xl text-white font-medium bg-indigo-600 hover:bg-indigo-700 focus:outline-hidden focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition shadow-sm">
            {{ __('app.login_btn') }}
        </button>
    </div>

    <div class="text-center pt-2">
        <a href="{{ route('register') }}" class="text-sm text-indigo-600 hover:text-indigo-800 font-medium transition">
            {{ __('app.dont_have_account') }}
        </a>
    </div>
</form>
@endsection

