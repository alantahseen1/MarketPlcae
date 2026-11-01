@extends('layouts.staff')

@section('title', __('staff.customers') . ' - ' . __('staff.panel_title'))

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl sm:text-3xl font-bold text-slate-900">{{ __('staff.customers') }}</h1>
            <p class="mt-1 text-sm text-slate-500">
                بەڕێوەبردنی کڕیاران و فرۆشگاکان، باوەڕپێکردنی فرۆشگا، و سڕکردنی هەژمارە سەرپێچیکارەکان
            </p>
        </div>
    </div>

    {{-- Search & Filters Bar --}}
    <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-xs">
        <form action="{{ route('staff.customers.index') }}" method="GET" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
            {{-- Search input --}}
            <div class="lg:col-span-2">
                <label for="search" class="block text-xs font-semibold text-slate-700 mb-1">
                    {{ __('staff.customer_search') }}
                </label>
                <input type="text" 
                       name="search" 
                       id="search" 
                       value="{{ request('search') }}"
                       placeholder="{{ __('staff.customer_search') }}" 
                       class="w-full px-3 py-2 text-sm rounded-lg border border-slate-300 focus:ring-indigo-500 focus:border-indigo-500">
            </div>

            {{-- Type Filter --}}
            <div>
                <label for="type" class="block text-xs font-semibold text-slate-700 mb-1">
                    {{ __('staff.seller_type') }}
                </label>
                <select name="type" id="type" class="w-full px-3 py-2 text-sm rounded-lg border border-slate-300 focus:ring-indigo-500 focus:border-indigo-500 bg-white">
                    <option value="">{{ __('staff.filter_all_types') }}</option>
                    <option value="regular" {{ request('type') === 'regular' ? 'selected' : '' }}>{{ __('staff.regular_user') }}</option>
                    <option value="shop" {{ request('type') === 'shop' ? 'selected' : '' }}>{{ __('staff.shop_user') }}</option>
                </select>
            </div>

            {{-- Banned Filter --}}
            <div>
                <label for="banned" class="block text-xs font-semibold text-slate-700 mb-1">
                    {{ __('staff.seller_status') }}
                </label>
                <select name="banned" id="banned" class="w-full px-3 py-2 text-sm rounded-lg border border-slate-300 focus:ring-indigo-500 focus:border-indigo-500 bg-white">
                    <option value="">{{ __('staff.filter_all_status') }}</option>
                    <option value="1" {{ request('banned') === '1' ? 'selected' : '' }}>{{ __('staff.filter_banned') }}</option>
                    <option value="0" {{ request('banned') === '0' ? 'selected' : '' }}>{{ __('staff.filter_not_banned') }}</option>
                </select>
            </div>

            {{-- Verified Filter --}}
            <div>
                <label for="verified" class="block text-xs font-semibold text-slate-700 mb-1">
                    باوەڕپێکراوی فرۆشگا
                </label>
                <select name="verified" id="verified" class="w-full px-3 py-2 text-sm rounded-lg border border-slate-300 focus:ring-indigo-500 focus:border-indigo-500 bg-white">
                    <option value="">هەموو</option>
                    <option value="1" {{ request('verified') === '1' ? 'selected' : '' }}>{{ __('staff.filter_verified') }}</option>
                    <option value="0" {{ request('verified') === '0' ? 'selected' : '' }}>{{ __('staff.filter_unverified') }}</option>
                </select>
            </div>

            {{-- Actions --}}
            <div class="lg:col-span-5 flex items-center gap-3 pt-2">
                <button type="submit" class="px-5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold rounded-lg text-sm transition">
                    {{ __('public.apply_filters') }}
                </button>
                @if(request()->hasAny(['search', 'type', 'banned', 'verified']))
                    <a href="{{ route('staff.customers.index') }}" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold rounded-lg text-sm transition">
                        {{ __('public.clear_filters') }}
                    </a>
                @endif
            </div>
        </form>
    </div>

    {{-- Customers Table --}}
    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
        @if($customers->isEmpty())
            <div class="p-12 text-center text-slate-500">
                <p class="font-medium text-base">هیچ بەکارهێنەرێک بەم مەرجانە نەدۆزرایەوە.</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-right">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-6 py-3.5 text-xs font-bold text-slate-600 uppercase">{{ __('staff.full_name') }}</th>
                            <th class="px-6 py-3.5 text-xs font-bold text-slate-600 uppercase">{{ __('staff.seller_type') }}</th>
                            <th class="px-6 py-3.5 text-xs font-bold text-slate-600 uppercase">{{ __('staff.seller_phone') }}</th>
                            <th class="px-6 py-3.5 text-xs font-bold text-slate-600 uppercase">{{ __('staff.products_count') }}</th>
                            <th class="px-6 py-3.5 text-xs font-bold text-slate-600 uppercase">{{ __('staff.reports_against_count') }}</th>
                            <th class="px-6 py-3.5 text-xs font-bold text-slate-600 uppercase">{{ __('staff.status') }}</th>
                            <th class="px-6 py-3.5 text-xs font-bold text-slate-600 uppercase">{{ __('staff.actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200">
                        @foreach($customers as $customer)
                            <tr class="hover:bg-slate-50 transition">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center gap-3">
                                        @if($customer->type === 'shop' && $customer->logo)
                                            <img src="{{ asset('storage/' . $customer->logo) }}" class="w-10 h-10 rounded-lg object-cover border border-slate-200 shrink-0">
                                        @else
                                            <div class="w-10 h-10 rounded-lg bg-indigo-50 text-indigo-600 font-bold flex items-center justify-center shrink-0 text-sm">
                                                {{ mb_substr($customer->shop_name ?? $customer->full_name, 0, 1) }}
                                            </div>
                                        @endif
                                        <div>
                                            <a href="{{ route('staff.customers.show', $customer->id) }}" class="font-bold text-slate-900 hover:text-indigo-600">
                                                {{ $customer->shop_name ?? $customer->full_name }}
                                            </a>
                                            @if($customer->shop_name)
                                                <span class="text-xs text-slate-500 block">({{ $customer->full_name }})</span>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-xs">
                                    @if($customer->type === 'shop')
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full font-semibold bg-indigo-50 text-indigo-700 border border-indigo-200">
                                            {{ __('staff.shop_user') }}
                                            @if($customer->is_verified)
                                                <svg class="w-3.5 h-3.5 text-blue-500" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M6.267 3.455a3.066 3.066 0 001.745-.723 3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 3.066 3.066 0 012.812 2.812c.051.643.304 1.254.723 1.745a3.066 3.066 0 010 3.976 3.066 3.066 0 00-.723 1.745 3.066 3.066 0 01-2.812 2.812 3.066 3.066 0 00-1.745.723 3.066 3.066 0 01-3.976 0 3.066 3.066 0 00-1.745-.723 3.066 3.066 0 01-2.812-2.812zm7.44 5.252a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                                </svg>
                                            @endif
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full font-semibold bg-slate-100 text-slate-700">
                                            {{ __('staff.regular_user') }}
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-600" dir="ltr">
                                    {{ $customer->phone }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-slate-800 text-center">
                                    {{ $customer->products_count }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-center">
                                    @if($customer->open_reports_count > 0)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold bg-red-100 text-red-800">
                                            {{ $customer->open_reports_count }} کراوە
                                        </span>
                                    @else
                                        <span class="text-xs text-slate-400">٠</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-xs">
                                    @if($customer->is_banned)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full font-bold bg-red-100 text-red-800 border border-red-200">
                                            {{ __('staff.banned') }}
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full font-semibold bg-emerald-100 text-emerald-800 border border-emerald-200">
                                            {{ __('staff.active') }}
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <div class="flex items-center gap-2">
                                        <a href="{{ route('staff.customers.show', $customer->id) }}" 
                                           class="px-3 py-1.5 bg-slate-100 hover:bg-indigo-50 hover:text-indigo-600 text-slate-700 rounded-lg text-xs font-semibold transition">
                                            پڕۆفایل
                                        </a>

                                        @if($customer->is_banned)
                                            <form action="{{ route('staff.customers.unban', $customer->id) }}" method="POST" onsubmit="return confirm('{{ __('staff.unban_confirm') }}')">
                                                @csrf
                                                <button type="submit" class="px-2.5 py-1.5 bg-emerald-50 hover:bg-emerald-100 text-emerald-700 rounded-lg text-xs font-semibold transition">
                                                    {{ __('staff.unban_btn') }}
                                                </button>
                                            </form>
                                        @else
                                            <form action="{{ route('staff.customers.ban', $customer->id) }}" method="POST" onsubmit="return confirm('{{ __('staff.ban_confirm_text') }}')">
                                                @csrf
                                                <button type="submit" class="px-2.5 py-1.5 bg-red-50 hover:bg-red-100 text-red-700 rounded-lg text-xs font-semibold transition">
                                                    {{ __('staff.ban_btn') }}
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="p-4 border-t border-slate-200">
                {{ $customers->links() }}
            </div>
        @endif
    </div>
</div>
@endsection

