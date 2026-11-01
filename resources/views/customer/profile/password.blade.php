@extends('layouts.app')

@section('title', __('profile.change_password') . ' - ' . __('app.site_name'))

@section('content')
<div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-10 space-y-8">
    {{-- Header & Sub-nav --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 border-b border-gray-200 pb-5">
        <div>
            <h1 class="text-2xl sm:text-3xl font-black text-gray-900">{{ __('profile.change_password') }}</h1>
            <p class="text-sm text-gray-500 mt-1">
                پاراستنی هەژمارەکەت بە وشەیەکی نهێنی بەهێز
            </p>
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ route('customer.profile.edit') }}" 
               class="px-4 py-2 text-xs font-semibold rounded-xl bg-gray-100 hover:bg-gray-200 text-gray-700 transition">
                {{ __('profile.edit_profile') }}
            </a>
            <a href="{{ route('customer.password.edit') }}" 
               class="px-4 py-2 text-xs font-bold rounded-xl bg-indigo-600 text-white shadow-xs">
                {{ __('profile.change_password') }}
            </a>
        </div>
    </div>

    {{-- Password Change Form --}}
    <div class="bg-white rounded-2xl border border-gray-200 p-6 sm:p-8 shadow-xs">
        <form action="{{ route('customer.password.update') }}" method="POST" class="space-y-6">
            @csrf
            @method('PUT')

            {{-- Current Password --}}
            <div>
                <label for="current_password" class="block text-sm font-bold text-gray-900 mb-1.5">
                    {{ __('profile.current_password') }} <span class="text-red-500">*</span>
                </label>
                <input type="password" 
                       name="current_password" 
                       id="current_password" 
                       required 
                       class="w-full px-4 py-2.5 text-sm rounded-xl border border-gray-300 focus:ring-indigo-500 focus:border-indigo-500 @error('current_password') border-red-500 @enderror">
                @error('current_password')
                    <p class="mt-1.5 text-xs text-red-600 font-semibold">{{ $message }}</p>
                @enderror
            </div>

            {{-- New Password --}}
            <div>
                <label for="new_password" class="block text-sm font-bold text-gray-900 mb-1.5">
                    {{ __('profile.new_password') }} <span class="text-red-500">*</span>
                </label>
                <input type="password" 
                       name="new_password" 
                       id="new_password" 
                       required 
                       minlength="8"
                       class="w-full px-4 py-2.5 text-sm rounded-xl border border-gray-300 focus:ring-indigo-500 focus:border-indigo-500 @error('new_password') border-red-500 @enderror">
                <p class="text-xs text-gray-400 mt-1">لانیکەم ٨ پیت یان هێما</p>
                @error('new_password')
                    <p class="mt-1.5 text-xs text-red-600 font-semibold">{{ $message }}</p>
                @enderror
            </div>

            {{-- Confirm New Password --}}
            <div>
                <label for="new_password_confirmation" class="block text-sm font-bold text-gray-900 mb-1.5">
                    {{ __('profile.new_password_confirmation') }} <span class="text-red-500">*</span>
                </label>
                <input type="password" 
                       name="new_password_confirmation" 
                       id="new_password_confirmation" 
                       required 
                       minlength="8"
                       class="w-full px-4 py-2.5 text-sm rounded-xl border border-gray-300 focus:ring-indigo-500 focus:border-indigo-500">
            </div>

            {{-- Submit --}}
            <div class="pt-4 border-t border-gray-100">
                <button type="submit" 
                        class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl text-sm shadow-xs transition">
                    {{ __('profile.change_password') }}
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

