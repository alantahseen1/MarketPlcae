<!DOCTYPE html>
<html lang="ckb" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', __('app.site_name'))</title>
    <meta name="description" content="@yield('meta_description', 'گەورەترین بازاڕی ئەلیکترۆنی لە کوردستان بۆ کڕین و فرۆشتنی مۆبایل، لاپتۆپ، کۆمپیوتەر و کەلوپەلی ئەلیکترۆنی.')">

    <!-- Open Graph / Social Sharing -->
    <meta property="og:title" content="@yield('og_title', View::yieldContent('title', __('app.site_name')))">
    <meta property="og:description" content="@yield('og_description', View::yieldContent('meta_description', 'گەورەترین بازاڕی ئەلیکترۆنی لە کوردستان بۆ کڕین و فرۆشتنی مۆبایل، لاپتۆپ، کۆمپیوتەر و کەلوپەلی ئەلیکترۆنی.'))">
    <meta property="og:url" content="@yield('og_url', url()->current())">
    <meta property="og:type" content="@yield('og_type', 'website')">
    <meta property="og:locale" content="ckb_IQ">
    @hasSection('og_image')
        <meta property="og:image" content="@yield('og_image')">
    @endif

    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50 text-gray-800 antialiased min-h-screen flex flex-col justify-between">
    <!-- Header -->
    <header class="bg-white border-b border-gray-200 sticky top-0 z-40 shadow-xs">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <!-- Site Name / Logo -->
                <div class="flex items-center gap-6">
                    <a href="{{ route('home') }}" class="text-xl sm:text-2xl font-bold text-indigo-600 hover:text-indigo-700 transition">
                        {{ __('app.site_name') }}
                    </a>

                    <!-- Desktop Nav Links -->
                    <nav class="hidden md:flex items-center gap-2">
                        <a href="{{ route('home') }}" class="px-3 py-2 text-sm font-medium text-gray-700 hover:text-indigo-600 rounded-md transition {{ request()->routeIs('home') ? 'text-indigo-600 font-bold' : '' }}">
                            {{ __('app.home') }}
                        </a>

                        <a href="{{ route('products.index') }}" class="px-3 py-2 text-sm font-medium text-gray-700 hover:text-indigo-600 rounded-md transition {{ request()->routeIs('products.*') ? 'text-indigo-600 font-bold' : '' }}">
                            {{ __('public.browse_products') }}
                        </a>

                        @if(auth('customer')->check())
                            <a href="{{ route('customer.products.index') }}" class="px-3 py-2 text-sm font-medium text-gray-700 hover:text-indigo-600 rounded-md transition {{ request()->routeIs('customer.products.*') ? 'text-indigo-600 font-bold' : '' }}">
                                {{ __('products.my_products') }}
                            </a>
                        @endif
                    </nav>
                </div>

                <!-- Desktop Navigation (User / Auth) -->
                <nav class="hidden md:flex items-center gap-4">
                    @if(auth('customer')->check())
                        <div class="flex items-center gap-3">
                            <a href="{{ route('customer.products.create') }}"
                                class="inline-flex items-center gap-1.5 px-4 py-2 text-sm font-semibold text-white bg-indigo-600 hover:bg-indigo-700 rounded-lg shadow-xs transition">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                </svg>
                                {{ __('products.add_product') }}
                            </a>

                            <div class="flex items-center gap-3 border-r border-gray-200 pr-4 mr-2">
                                <a href="{{ route('customer.profile.edit') }}" 
                                   class="text-sm font-semibold text-gray-900 hover:text-indigo-600 flex items-center gap-2 transition {{ request()->routeIs('customer.profile.*', 'customer.password.*') ? 'text-indigo-600 font-bold' : '' }}">
                                    <span class="w-8 h-8 rounded-full bg-indigo-100 text-indigo-700 flex items-center justify-center text-xs font-bold">
                                        {{ mb_substr(auth('customer')->user()->full_name, 0, 1) }}
                                    </span>
                                    <span>{{ auth('customer')->user()->full_name }}</span>
                                    @if(auth('customer')->user()->type === 'shop')
                                        <span class="text-xs bg-indigo-50 text-indigo-600 px-2 py-0.5 rounded-full border border-indigo-200">
                                            {{ auth('customer')->user()->shop_name }}
                                        </span>
                                    @endif
                                </a>

                                <form action="{{ route('logout') }}" method="POST" class="inline">
                                    @csrf
                                    <button type="submit" class="px-3 py-2 text-sm font-medium text-red-600 hover:bg-red-50 rounded-md transition">
                                        {{ __('app.logout') }}
                                    </button>
                                </form>
                            </div>
                        </div>
                    @else
                        <div class="flex items-center gap-2 border-r border-gray-200 pr-4 mr-2">
                            <a href="{{ route('login') }}" class="px-4 py-2 text-sm font-medium text-gray-700 hover:text-indigo-600 transition">
                                {{ __('app.login') }}
                            </a>
                            <a href="{{ route('register') }}" class="px-4 py-2 text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 rounded-lg shadow-xs transition">
                                {{ __('app.register') }}
                            </a>
                        </div>
                    @endif
                </nav>

                <!-- Mobile Menu Button -->
                <div class="flex items-center md:hidden">
                    <button id="mobile-menu-btn" type="button" class="p-2 rounded-md text-gray-600 hover:text-gray-900 hover:bg-gray-100 focus:outline-hidden" aria-label="Toggle navigation">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Mobile Navigation Menu -->
            <div id="mobile-menu" class="hidden md:hidden pb-4 pt-2 border-t border-gray-100">
                <div class="flex flex-col gap-2">
                    <a href="{{ route('home') }}" class="px-3 py-2 text-base font-medium text-gray-700 hover:bg-gray-100 rounded-md">
                        {{ __('app.home') }}
                    </a>

                    <a href="{{ route('products.index') }}" class="px-3 py-2 text-base font-medium text-gray-700 hover:bg-gray-100 rounded-md">
                        {{ __('public.browse_products') }}
                    </a>

                    @if(auth('customer')->check())
                        <a href="{{ route('customer.products.index') }}" class="px-3 py-2 text-base font-medium text-gray-700 hover:bg-gray-100 rounded-md">
                            {{ __('products.my_products') }}
                        </a>
                        <a href="{{ route('customer.profile.edit') }}" class="px-3 py-2 text-base font-medium text-gray-700 hover:bg-gray-100 rounded-md">
                            {{ __('profile.my_profile') }}
                        </a>
                        <a href="{{ route('customer.products.create') }}" class="px-3 py-2 text-base font-medium text-center text-white bg-indigo-600 hover:bg-indigo-700 rounded-lg">
                            {{ __('products.add_product') }}
                        </a>

                        <div class="px-3 py-2 text-base font-semibold text-gray-900 border-t border-gray-100 mt-2 pt-2">
                            {{ auth('customer')->user()->full_name }}
                            @if(auth('customer')->user()->type === 'shop')
                                <span class="text-xs text-indigo-600 block">({{ auth('customer')->user()->shop_name }})</span>
                            @endif
                        </div>
                        <form action="{{ route('logout') }}" method="POST" class="w-full">
                            @csrf
                            <button type="submit" class="w-full text-right px-3 py-2 text-base font-medium text-red-600 hover:bg-red-50 rounded-md">
                                {{ __('app.logout') }}
                            </button>
                        </form>
                    @else
                        <div class="flex flex-col gap-2 pt-2 border-t border-gray-100">
                            <a href="{{ route('login') }}" class="px-3 py-2 text-base font-medium text-gray-700 hover:bg-gray-100 rounded-md">
                                {{ __('app.login') }}
                            </a>
                            <a href="{{ route('register') }}" class="px-3 py-2 text-base font-medium text-center text-white bg-indigo-600 hover:bg-indigo-700 rounded-lg">
                                {{ __('app.register') }}
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="flex-grow">
        <!-- Flash Messages -->
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-4">
            @if(session('success'))
                <div class="mb-4 p-4 rounded-lg bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm flex items-center justify-between">
                    <span>{{ session('success') }}</span>
                    <button type="button" onclick="this.parentElement.remove()" class="text-emerald-600 hover:text-emerald-900 text-lg font-bold leading-none">&times;</button>
                </div>
            @endif

            @if(session('error'))
                <div class="mb-4 p-4 rounded-lg bg-red-50 border border-red-200 text-red-800 text-sm flex items-center justify-between">
                    <span>{{ session('error') }}</span>
                    <button type="button" onclick="this.parentElement.remove()" class="text-red-600 hover:text-red-900 text-lg font-bold leading-none">&times;</button>
                </div>
            @endif
        </div>

        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="bg-white border-t border-gray-200 py-6 mt-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center text-sm text-gray-500">
            <p>{{ __('app.site_name') }} &copy; {{ date('Y') }}. {{ __('app.all_rights_reserved') }}.</p>
        </div>
    </footer>

    <script>
        const btn = document.getElementById('mobile-menu-btn');
        const menu = document.getElementById('mobile-menu');
        if (btn && menu) {
            btn.addEventListener('click', () => {
                menu.classList.toggle('hidden');
            });
        }
    </script>
</body>
</html>
