<!DOCTYPE html>
<html lang="ckb" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', __('app.site_name'))</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-100 text-gray-800 antialiased min-h-screen flex flex-col justify-center py-12 sm:px-6 lg:px-8">
    <div class="sm:mx-auto sm:w-full sm:max-w-md text-center">
        <a href="{{ route('home') }}" class="text-3xl font-extrabold text-indigo-600 hover:text-indigo-700 transition">
            {{ __('app.site_name') }}
        </a>
        <h2 class="mt-3 text-lg font-medium text-gray-600">
            @yield('heading')
        </h2>
    </div>

    <div class="mt-8 sm:mx-auto sm:w-full @yield('card_width', 'sm:max-w-md') px-4 sm:px-0">
        <div class="bg-white py-8 px-6 shadow-sm sm:rounded-2xl border border-gray-200 sm:px-10">
            <!-- Flash Errors -->
            @if(session('error'))
                <div class="mb-5 p-3 rounded-lg bg-red-50 border border-red-200 text-red-700 text-sm">
                    {{ session('error') }}
                </div>
            @endif

            @yield('content')
        </div>

        <div class="mt-6 text-center text-xs text-gray-500">
            <a href="{{ route('home') }}" class="hover:text-indigo-600 transition">
                &larr; {{ __('app.home') }}
            </a>
        </div>
    </div>
</body>
</html>

