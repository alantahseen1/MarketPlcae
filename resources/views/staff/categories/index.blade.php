@extends('layouts.staff')

@section('title', __('staff.categories') . ' - ' . __('staff.panel_title'))

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl sm:text-3xl font-bold text-slate-900">{{ __('staff.categories') }}</h1>
            <p class="mt-1 text-sm text-slate-500">
                {{ __('staff.category_cannot_delete') }}
            </p>
        </div>

        <div>
            <a href="{{ route('staff.categories.create') }}" 
               class="inline-flex items-center gap-1.5 px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold rounded-xl text-sm shadow-xs transition">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                <span>{{ __('staff.add_category') }}</span>
            </a>
        </div>
    </div>

    {{-- Categories Table --}}
    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-right">
                <thead class="bg-slate-50">
                    <tr>
                        <th class="px-6 py-3.5 text-xs font-bold text-slate-600 uppercase">#</th>
                        <th class="px-6 py-3.5 text-xs font-bold text-slate-600 uppercase">{{ __('staff.category_name') }}</th>
                        <th class="px-6 py-3.5 text-xs font-bold text-slate-600 uppercase">{{ __('staff.products_count') }}</th>
                        <th class="px-6 py-3.5 text-xs font-bold text-slate-600 uppercase">{{ __('staff.status') }}</th>
                        <th class="px-6 py-3.5 text-xs font-bold text-slate-600 uppercase">{{ __('staff.actions') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @foreach($categories as $category)
                        <tr class="hover:bg-slate-50 transition">
                            <td class="px-6 py-4 whitespace-nowrap text-xs text-slate-400 font-bold">
                                {{ $category->id }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-slate-900">
                                {{ $category->name }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-slate-800">
                                {{ $category->products_count }} بەرهەم
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-xs">
                                @if($category->is_active)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full font-semibold bg-emerald-100 text-emerald-800 border border-emerald-200">
                                        {{ __('staff.active') }}
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full font-semibold bg-slate-100 text-slate-600 border border-slate-200">
                                        {{ __('staff.inactive') }}
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <div class="flex items-center gap-2">
                                    <a href="{{ route('staff.categories.edit', $category->id) }}" 
                                       class="px-3 py-1.5 bg-slate-100 hover:bg-indigo-50 hover:text-indigo-600 text-slate-700 rounded-lg text-xs font-semibold transition">
                                        {{ __('staff.edit_category') }}
                                    </a>

                                    <form action="{{ route('staff.categories.toggle', $category->id) }}" method="POST">
                                        @csrf
                                        @if($category->is_active)
                                            <button type="submit" class="px-3 py-1.5 bg-amber-50 hover:bg-amber-100 text-amber-700 rounded-lg text-xs font-semibold transition">
                                                {{ __('staff.deactivate_category_btn') }}
                                            </button>
                                        @else
                                            <button type="submit" class="px-3 py-1.5 bg-emerald-50 hover:bg-emerald-100 text-emerald-700 rounded-lg text-xs font-semibold transition">
                                                {{ __('staff.activate_category_btn') }}
                                            </button>
                                        @endif
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

