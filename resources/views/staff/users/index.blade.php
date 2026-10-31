@extends('layouts.staff')

@section('title', __('staff.staff_users') . ' - ' . __('staff.panel_title'))

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl sm:text-3xl font-bold text-slate-900">{{ __('staff.staff_users') }}</h1>
            <p class="mt-1 text-sm text-slate-500">
                بەڕێوەبردنی هەژماری بەڕێوەبەر و کارمەندەکانی سیستەم
            </p>
        </div>

        <div>
            <a href="{{ route('staff.users.create') }}"
                class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl text-sm font-semibold text-white bg-indigo-600 hover:bg-indigo-700 shadow-xs transition">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                {{ __('staff.add_staff') }}
            </a>
        </div>
    </div>

    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-right text-sm">
                <thead class="bg-slate-50 text-slate-600 font-semibold border-b border-slate-200">
                    <tr>
                        <th class="py-3 px-4">{{ __('staff.full_name') }}</th>
                        <th class="py-3 px-4">{{ __('staff.email') }}</th>
                        <th class="py-3 px-4">{{ __('staff.role') }}</th>
                        <th class="py-3 px-4">{{ __('staff.status') }}</th>
                        <th class="py-3 px-4">بەرواری دروستکردن</th>
                        <th class="py-3 px-4 text-left">{{ __('staff.actions') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($users as $user)
                        <tr class="hover:bg-slate-50 transition">
                            <td class="py-3 px-4 font-bold text-slate-900">
                                {{ $user->full_name }}
                                @if(auth('staff')->id() === $user->id)
                                    <span class="text-xs text-indigo-600 mr-1.5">(هەژماری تۆ)</span>
                                @endif
                            </td>
                            <td class="py-3 px-4 text-slate-600" dir="ltr">
                                {{ $user->email }}
                            </td>
                            <td class="py-3 px-4">
                                @if($user->role === 'admin')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-indigo-50 text-indigo-700 border border-indigo-200">
                                        {{ __('staff.role_admin') }}
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-slate-100 text-slate-700 border border-slate-200">
                                        {{ __('staff.role_subadmin') }}
                                    </span>
                                @endif
                            </td>
                            <td class="py-3 px-4">
                                @if($user->is_active)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        {{ __('staff.active') }}
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-red-50 text-red-700 border border-red-200">
                                        {{ __('staff.inactive') }}
                                    </span>
                                @endif
                            </td>
                            <td class="py-3 px-4 text-xs text-slate-500 whitespace-nowrap">
                                {{ $user->created_at->format('Y-m-d') }}
                            </td>
                            <td class="py-3 px-4 text-left whitespace-nowrap">
                                <a href="{{ route('staff.users.edit', $user->id) }}"
                                    class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg text-xs font-semibold text-slate-700 bg-slate-100 hover:bg-slate-200 transition">
                                    {{ __('products.edit') }}
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="p-4 border-t border-slate-100">
            {{ $users->links() }}
        </div>
    </div>
</div>
@endsection

