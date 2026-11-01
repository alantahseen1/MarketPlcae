<!DOCTYPE html>
<html lang="ckb" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', __('staff.panel_title'))</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-slate-100 text-slate-800 antialiased min-h-screen flex flex-col justify-between">
    <!-- Top Header -->
    <header class="bg-slate-900 text-white sticky top-0 z-40 shadow-md">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <!-- Brand & Main Nav Links -->
                <div class="flex items-center gap-6">
                    <a href="{{ route('staff.dashboard') }}" class="text-lg sm:text-xl font-bold text-indigo-400 flex items-center gap-2">
                        <span class="w-8 h-8 rounded-lg bg-indigo-600 text-white flex items-center justify-center font-black text-sm">
                            S
                        </span>
                        {{ __('staff.panel_title') }}
                    </a>

                    <!-- Desktop Nav -->
                    <nav class="hidden md:flex items-center gap-1">
                        <a href="{{ route('staff.dashboard') }}"
                            class="px-3 py-2 text-sm font-medium rounded-lg transition {{ request()->routeIs('staff.dashboard') ? 'bg-slate-800 text-white font-bold' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                            {{ __('staff.dashboard') }}
                        </a>

                        <a href="{{ route('staff.products.index') }}"
                            class="px-3 py-2 text-sm font-medium rounded-lg transition {{ request()->routeIs('staff.products.*') ? 'bg-slate-800 text-white font-bold' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                            {{ __('staff.product_reviews') }}
                        </a>

                        @if(auth('staff')->user()->role === 'admin')
                            @php
                                $globalOpenReports = \App\Models\Report::where('status', 'open')->count();
                            @endphp
                            <a href="{{ route('staff.reports.index') }}"
                                class="px-3 py-2 text-sm font-medium rounded-lg transition flex items-center gap-1.5 {{ request()->routeIs('staff.reports.*') ? 'bg-slate-800 text-white font-bold' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                                <span>{{ __('staff.reports') }}</span>
                                @if($globalOpenReports > 0)
                                    <span class="inline-flex items-center justify-center px-1.5 py-0.5 text-xs font-bold leading-none text-white bg-red-600 rounded-full">
                                        {{ $globalOpenReports }}
                                    </span>
                                @endif
                            </a>

                            <a href="{{ route('staff.customers.index') }}"
                                class="px-3 py-2 text-sm font-medium rounded-lg transition {{ request()->routeIs('staff.customers.*') ? 'bg-slate-800 text-white font-bold' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                                {{ __('staff.customers') }}
                            </a>

                            <a href="{{ route('staff.categories.index') }}"
                                class="px-3 py-2 text-sm font-medium rounded-lg transition {{ request()->routeIs('staff.categories.*') ? 'bg-slate-800 text-white font-bold' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                                {{ __('staff.categories') }}
                            </a>

                            <a href="{{ route('staff.users.index') }}"
                                class="px-3 py-2 text-sm font-medium rounded-lg transition {{ request()->routeIs('staff.users.*') ? 'bg-slate-800 text-white font-bold' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                                {{ __('staff.staff_users') }}
                            </a>
                        @endif
                    </nav>
                </div>

                <!-- Staff Profile & Logout -->
                <div class="hidden md:flex items-center gap-4">
                    <div class="flex items-center gap-3 pl-4 border-l border-slate-700">
                        <div class="text-left">
                            <span class="block text-sm font-semibold text-white">
                                {{ auth('staff')->user()->full_name }}
                            </span>
                            <span class="block text-xs text-indigo-300">
                                {{ auth('staff')->user()->role === 'admin' ? __('staff.role_admin') : __('staff.role_subadmin') }}
                            </span>
                        </div>
                        <div class="w-9 h-9 rounded-full bg-slate-700 text-slate-200 flex items-center justify-center text-xs font-bold border border-slate-600">
                            {{ mb_substr(auth('staff')->user()->full_name, 0, 1) }}
                        </div>
                    </div>

                    <form action="{{ route('staff.logout') }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" class="px-3 py-2 text-sm font-medium text-red-400 hover:bg-slate-800 hover:text-red-300 rounded-lg transition">
                            {{ __('app.logout') }}
                        </button>
                    </form>
                </div>

                <!-- Mobile Menu Button -->
                <div class="flex items-center md:hidden">
                    <button id="staff-menu-btn" type="button" class="p-2 rounded-md text-slate-300 hover:text-white hover:bg-slate-800" aria-label="Toggle navigation">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Mobile Menu -->
            <div id="staff-menu" class="hidden md:hidden pb-4 pt-2 border-t border-slate-800">
                <div class="flex flex-col gap-2">
                    <a href="{{ route('staff.dashboard') }}" class="px-3 py-2 text-sm font-medium text-slate-200 hover:bg-slate-800 rounded-lg">
                        {{ __('staff.dashboard') }}
                    </a>
                    <a href="{{ route('staff.products.index') }}" class="px-3 py-2 text-sm font-medium text-slate-200 hover:bg-slate-800 rounded-lg">
                        {{ __('staff.product_reviews') }}
                    </a>
                    @if(auth('staff')->user()->role === 'admin')
                        <a href="{{ route('staff.reports.index') }}" class="px-3 py-2 text-sm font-medium text-slate-200 hover:bg-slate-800 rounded-lg flex items-center justify-between">
                            <span>{{ __('staff.reports') }}</span>
                            @if($globalOpenReports > 0)
                                <span class="px-1.5 py-0.5 text-xs font-bold text-white bg-red-600 rounded-full">
                                    {{ $globalOpenReports }}
                                </span>
                            @endif
                        </a>
                        <a href="{{ route('staff.customers.index') }}" class="px-3 py-2 text-sm font-medium text-slate-200 hover:bg-slate-800 rounded-lg">
                            {{ __('staff.customers') }}
                        </a>
                        <a href="{{ route('staff.categories.index') }}" class="px-3 py-2 text-sm font-medium text-slate-200 hover:bg-slate-800 rounded-lg">
                            {{ __('staff.categories') }}
                        </a>
                        <a href="{{ route('staff.users.index') }}" class="px-3 py-2 text-sm font-medium text-slate-200 hover:bg-slate-800 rounded-lg">
                            {{ __('staff.staff_users') }}
                        </a>
                    @endif

                    <div class="pt-3 border-t border-slate-800 mt-2 flex items-center justify-between px-3">
                        <div>
                            <span class="block text-sm font-semibold text-white">{{ auth('staff')->user()->full_name }}</span>
                            <span class="block text-xs text-indigo-400">{{ auth('staff')->user()->role === 'admin' ? __('staff.role_admin') : __('staff.role_subadmin') }}</span>
                        </div>
                        <form action="{{ route('staff.logout') }}" method="POST">
                            @csrf
                            <button type="submit" class="px-3 py-1.5 text-xs text-red-400 hover:bg-slate-800 rounded-lg">
                                {{ __('app.logout') }}
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="flex-grow">
        <!-- Flash Messages -->
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-4">
            @if(session('success'))
                <div class="mb-4 p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm flex items-center justify-between shadow-xs">
                    <span>{{ session('success') }}</span>
                    <button type="button" onclick="this.parentElement.remove()" class="text-emerald-600 hover:text-emerald-900 text-lg font-bold leading-none">&times;</button>
                </div>
            @endif

            @if(session('error'))
                <div class="mb-4 p-4 rounded-xl bg-red-50 border border-red-200 text-red-800 text-sm flex items-center justify-between shadow-xs">
                    <span>{{ session('error') }}</span>
                    <button type="button" onclick="this.parentElement.remove()" class="text-red-600 hover:text-red-900 text-lg font-bold leading-none">&times;</button>
                </div>
            @endif
        </div>

        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="bg-white border-t border-slate-200 py-5 mt-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center text-xs text-slate-500">
            <p>{{ __('staff.panel_title') }} &bull; {{ config('app.name') }} &copy; {{ date('Y') }}</p>
        </div>
    </footer>

    <script>
        const btn = document.getElementById('staff-menu-btn');
        const menu = document.getElementById('staff-menu');
        if (btn && menu) {
            btn.addEventListener('click', () => {
                menu.classList.toggle('hidden');
            });
        }
    </script>
</body>
</html>

