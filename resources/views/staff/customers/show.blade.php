@extends('layouts.staff')

@section('title', ($customer->shop_name ?? $customer->full_name) . ' - ' . __('staff.customer_profile'))

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-3">
                <a href="{{ route('staff.customers.index') }}" class="text-sm font-semibold text-indigo-600 hover:underline">
                    &larr; {{ __('staff.customers') }}
                </a>
                <span class="text-slate-300">/</span>
                <h1 class="text-2xl sm:text-3xl font-bold text-slate-900">
                    {{ $customer->shop_name ?? $customer->full_name }}
                </h1>
            </div>
            <p class="mt-1 text-sm text-slate-500">
                بەرواری تۆماربوون: {{ $customer->created_at->format('Y-m-d') }} ({{ $customer->created_at->diffForHumans() }})
            </p>
        </div>

        {{-- Top Action Buttons --}}
        <div class="flex items-center gap-3 flex-wrap">
            {{-- Verify / Unverify (Shops Only) --}}
            @if($customer->type === 'shop')
                @if($customer->is_verified)
                    <form action="{{ route('staff.customers.unverify', $customer->id) }}" method="POST" onsubmit="return confirm('{{ __('staff.unverify_confirm') }}')">
                        @csrf
                        <button type="submit" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold rounded-xl text-xs transition">
                            {{ __('staff.unverify_btn') }}
                        </button>
                    </form>
                @else
                    <form action="{{ route('staff.customers.verify', $customer->id) }}" method="POST" onsubmit="return confirm('{{ __('staff.verify_confirm') }}')">
                        @csrf
                        <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-xl text-xs transition shadow-xs">
                            {{ __('staff.verify_btn') }}
                        </button>
                    </form>
                @endif
            @endif

            {{-- Ban / Unban --}}
            @if($customer->is_banned)
                <form action="{{ route('staff.customers.unban', $customer->id) }}" method="POST" onsubmit="return confirm('{{ __('staff.unban_confirm') }}')">
                    @csrf
                    <button type="submit" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold rounded-xl text-xs transition shadow-xs">
                        {{ __('staff.unban_btn') }}
                    </button>
                </form>
            @else
                <form action="{{ route('staff.customers.ban', $customer->id) }}" method="POST" onsubmit="return confirm('{{ __('staff.ban_confirm_text') }}')">
                    @csrf
                    <button type="submit" class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white font-semibold rounded-xl text-xs transition shadow-xs">
                        {{ __('staff.ban_btn') }}
                    </button>
                </form>
            @endif
        </div>
    </div>

    {{-- Profile Details Card --}}
    <div class="bg-white rounded-2xl border border-slate-200 p-6 sm:p-8 shadow-xs">
        <div class="flex flex-col md:flex-row md:items-center gap-6">
            @if($customer->type === 'shop' && $customer->logo)
                <img src="{{ asset('storage/' . $customer->logo) }}" 
                     class="w-20 h-20 sm:w-24 sm:h-24 rounded-2xl object-cover border border-slate-200 shrink-0">
            @else
                <div class="w-20 h-20 sm:w-24 sm:h-24 rounded-2xl bg-indigo-50 text-indigo-600 font-black text-3xl flex items-center justify-center shrink-0">
                    {{ mb_substr($customer->shop_name ?? $customer->full_name, 0, 1) }}
                </div>
            @endif

            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4 flex-1">
                <div>
                    <span class="text-xs text-slate-400 block mb-1">{{ __('staff.full_name') }}</span>
                    <span class="font-bold text-slate-900">{{ $customer->full_name }}</span>
                    @if($customer->shop_name)
                        <span class="text-xs text-indigo-600 block mt-0.5">فرۆشگا: {{ $customer->shop_name }}</span>
                    @endif
                </div>

                <div>
                    <span class="text-xs text-slate-400 block mb-1">{{ __('staff.seller_type') }}</span>
                    <span class="inline-flex items-center gap-1 font-semibold text-sm">
                        {{ $customer->type === 'shop' ? __('staff.shop_user') : __('staff.regular_user') }}
                        @if($customer->is_verified)
                            <span class="text-xs bg-blue-50 text-blue-700 px-2 py-0.5 rounded-full border border-blue-200">
                                {{ __('public.verified_shop_badge') }}
                            </span>
                        @endif
                    </span>
                </div>

                <div>
                    <span class="text-xs text-slate-400 block mb-1">پەیوەندی</span>
                    <span class="font-medium text-slate-900 block" dir="ltr">{{ $customer->phone }}</span>
                    <span class="text-xs text-slate-500 block">{{ $customer->email }}</span>
                </div>

                <div>
                    <span class="text-xs text-slate-400 block mb-1">{{ __('staff.status') }}</span>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold {{ $customer->is_banned ? 'bg-red-100 text-red-800' : 'bg-emerald-100 text-emerald-800' }}">
                        {{ $customer->is_banned ? __('staff.banned') : __('staff.active') }}
                    </span>
                    <span class="text-xs text-slate-500 block mt-1">{{ $customer->address }}</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Customer Products Section --}}
    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
        <div class="p-6 border-b border-slate-100 flex items-center justify-between">
            <h2 class="text-lg font-bold text-slate-900">
                {{ __('staff.customer_products') }} ({{ $products->count() }})
            </h2>
        </div>

        @if($products->isEmpty())
            <div class="p-8 text-center text-slate-500 text-sm">
                هیچ بەرهەمێک لەلایەن ئەم بەکارهێنەرەوە دانەنراوە.
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-right">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-6 py-3 text-xs font-bold text-slate-600 uppercase">بەرهەم</th>
                            <th class="px-6 py-3 text-xs font-bold text-slate-600 uppercase">هاوپۆل</th>
                            <th class="px-6 py-3 text-xs font-bold text-slate-600 uppercase">نرخ</th>
                            <th class="px-6 py-3 text-xs font-bold text-slate-600 uppercase">بارودۆخ</th>
                            <th class="px-6 py-3 text-xs font-bold text-slate-600 uppercase">دۆخ</th>
                            <th class="px-6 py-3 text-xs font-bold text-slate-600 uppercase">بەروار</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200">
                        @foreach($products as $prod)
                            <tr class="hover:bg-slate-50 transition">
                                <td class="px-6 py-3.5 text-sm font-bold text-slate-900">
                                    <a href="{{ route('staff.products.show', $prod->id) }}" class="hover:text-indigo-600">
                                        {{ $prod->title }}
                                    </a>
                                </td>
                                <td class="px-6 py-3.5 text-xs text-slate-600">
                                    {{ $prod->category->name }}
                                </td>
                                <td class="px-6 py-3.5 text-sm font-extrabold text-indigo-600">
                                    {{ \App\Helpers\MarketplaceHelper::formatPrice($prod->price) }}
                                </td>
                                <td class="px-6 py-3.5 text-xs">
                                    <span class="px-2 py-0.5 rounded-full {{ $prod->condition === 'new' ? 'bg-emerald-50 text-emerald-700' : 'bg-amber-50 text-amber-700' }}">
                                        {{ __('products.condition_' . $prod->condition) }}
                                    </span>
                                </td>
                                <td class="px-6 py-3.5 text-xs">
                                    <x-status-badge :status="$prod->status" />
                                </td>
                                <td class="px-6 py-3.5 text-xs text-slate-400">
                                    {{ $prod->created_at->format('Y-m-d') }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    {{-- Reports Against This Customer --}}
    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
        <div class="p-6 border-b border-slate-100 flex items-center justify-between">
            <h2 class="text-lg font-bold text-slate-900 flex items-center gap-2">
                <span>{{ __('staff.reports_against') }}</span>
                <span class="text-xs px-2.5 py-0.5 rounded-full font-bold {{ $reportsAgainst->count() > 0 ? 'bg-red-100 text-red-800' : 'bg-slate-100 text-slate-600' }}">
                    {{ $reportsAgainst->count() }}
                </span>
            </h2>
        </div>

        @if($reportsAgainst->isEmpty())
            <div class="p-8 text-center text-slate-500 text-sm">
                هیچ سکاڵایەک لەسەر ئەم بەکارهێنەرە تۆمار نەکراوە.
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-right">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-6 py-3 text-xs font-bold text-slate-600 uppercase">هۆکار</th>
                            <th class="px-6 py-3 text-xs font-bold text-slate-600 uppercase">سکاڵاکار</th>
                            <th class="px-6 py-3 text-xs font-bold text-slate-600 uppercase">دۆخ</th>
                            <th class="px-6 py-3 text-xs font-bold text-slate-600 uppercase">بەروار</th>
                            <th class="px-6 py-3 text-xs font-bold text-slate-600 uppercase">کردار</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200">
                        @foreach($reportsAgainst as $rAgainst)
                            <tr class="hover:bg-slate-50 transition">
                                <td class="px-6 py-3.5 text-sm font-semibold text-slate-900">
                                    {{ __('reports.reason_' . $rAgainst->reason) }}
                                </td>
                                <td class="px-6 py-3.5 text-xs text-slate-600">
                                    {{ $rAgainst->reporter->full_name ?? '-' }}
                                </td>
                                <td class="px-6 py-3.5 text-xs">
                                    <span class="px-2.5 py-0.5 rounded-full font-semibold {{ $rAgainst->status === 'open' ? 'bg-amber-100 text-amber-800' : ($rAgainst->status === 'resolved' ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-700') }}">
                                        {{ __('reports.status_' . $rAgainst->status) }}
                                    </span>
                                </td>
                                <td class="px-6 py-3.5 text-xs text-slate-400">
                                    {{ $rAgainst->created_at->format('Y-m-d') }}
                                </td>
                                <td class="px-6 py-3.5 text-xs font-semibold">
                                    <a href="{{ route('staff.reports.show', $rAgainst->id) }}" class="text-indigo-600 hover:underline">
                                        {{ __('staff.report_details') }} &rarr;
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>
@endsection

