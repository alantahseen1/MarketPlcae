@extends('layouts.app')

@section('title', __('app.site_name'))

@section('content')
<div class="py-16 sm:py-24">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <h1 class="text-3xl sm:text-5xl font-black text-gray-900 tracking-tight">
            {{ __('app.welcome_heading') }}
        </h1>
        <p class="mt-4 text-lg text-gray-600 max-w-2xl mx-auto">
            {{ __('app.welcome_subheading') }}
        </p>

        @if(!auth('customer')->check())
            <div class="mt-8 flex justify-center gap-4">
                <a href="{{ route('register') }}" class="px-6 py-3 text-base font-medium rounded-xl text-white bg-indigo-600 hover:bg-indigo-700 shadow-sm transition">
                    {{ __('app.register') }}
                </a>
                <a href="{{ route('login') }}" class="px-6 py-3 text-base font-medium rounded-xl text-indigo-600 bg-white border border-gray-300 hover:bg-gray-50 shadow-xs transition">
                    {{ __('app.login') }}
                </a>
            </div>
        @endif
    </div>
</div>
@endsection

