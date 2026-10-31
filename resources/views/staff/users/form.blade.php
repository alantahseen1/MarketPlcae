@extends('layouts.staff')

@php
    $isEdit = $user !== null;
    $title = $isEdit ? __('staff.edit_staff') : __('staff.add_staff');
    $action = $isEdit ? route('staff.users.update', $user->id) : route('staff.users.store');
    $buttonText = $isEdit ? __('staff.staff_updated') : __('staff.save_staff_btn');
@endphp

@section('title', $title . ' - ' . __('staff.panel_title'))

@section('content')
<div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-2xl sm:text-3xl font-bold text-slate-900">{{ $title }}</h1>
            <p class="mt-1 text-sm text-slate-500">
                {{ $isEdit ? __('staff.edit_staff') : __('staff.add_staff') }}
            </p>
        </div>
        <a href="{{ route('staff.users.index') }}" class="text-sm font-semibold text-slate-600 hover:text-slate-900 bg-white border border-slate-300 px-3.5 py-2 rounded-xl transition">
            &larr; {{ __('products.cancel') }}
        </a>
    </div>

    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs p-6 sm:p-8">
        <form action="{{ $action }}" method="POST" class="space-y-6">
            @csrf
            @if($isEdit)
                @method('PUT')
            @endif

            <!-- Full Name -->
            <div>
                <label for="full_name" class="block text-sm font-semibold text-slate-700 mb-1.5">
                    {{ __('staff.full_name') }} <span class="text-red-500">*</span>
                </label>
                <input type="text" name="full_name" id="full_name" value="{{ old('full_name', $user?->full_name) }}" required
                    class="w-full px-3.5 py-2.5 rounded-lg border @error('full_name') border-red-300 bg-red-50/20 @else border-slate-300 @enderror focus:ring-2 focus:ring-indigo-500 text-sm transition">
                @error('full_name')
                    <p class="mt-1.5 text-xs text-red-600 font-medium">{{ $message }}</p>
                @enderror
            </div>

            <!-- Email -->
            <div>
                <label for="email" class="block text-sm font-semibold text-slate-700 mb-1.5">
                    {{ __('staff.email') }} <span class="text-red-500">*</span>
                </label>
                <input type="email" name="email" id="email" value="{{ old('email', $user?->email) }}" required dir="ltr"
                    class="w-full text-right px-3.5 py-2.5 rounded-lg border @error('email') border-red-300 bg-red-50/20 @else border-slate-300 @enderror focus:ring-2 focus:ring-indigo-500 text-sm transition">
                @error('email')
                    <p class="mt-1.5 text-xs text-red-600 font-medium">{{ $message }}</p>
                @enderror
            </div>

            <!-- Password -->
            <div>
                <label for="password" class="block text-sm font-semibold text-slate-700 mb-1.5">
                    {{ __('staff.password') }} @if(!$isEdit) <span class="text-red-500">*</span> @endif
                </label>
                <input type="password" name="password" id="password" {{ $isEdit ? '' : 'required' }} dir="ltr"
                    class="w-full px-3.5 py-2.5 rounded-lg border @error('password') border-red-300 bg-red-50/20 @else border-slate-300 @enderror focus:ring-2 focus:ring-indigo-500 text-sm transition">
                @if($isEdit)
                    <p class="mt-1 text-xs text-slate-500">{{ __('staff.password_leave_blank') }}</p>
                @endif
                @error('password')
                    <p class="mt-1.5 text-xs text-red-600 font-medium">{{ $message }}</p>
                @enderror
            </div>

            <!-- Role -->
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1.5">
                    {{ __('staff.role') }} <span class="text-red-500">*</span>
                </label>
                <div class="grid grid-cols-2 gap-3">
                    <label class="cursor-pointer border rounded-xl p-3 flex items-center justify-between transition border-slate-300 hover:border-indigo-500 has-checked:border-indigo-600 has-checked:bg-indigo-50/40">
                        <div class="flex items-center gap-2">
                            <input type="radio" name="role" value="subadmin"
                                {{ old('role', $user?->role ?? 'subadmin') === 'subadmin' ? 'checked' : '' }}
                                class="text-indigo-600 focus:ring-indigo-500">
                            <span class="text-sm font-medium text-slate-800">{{ __('staff.role_subadmin') }}</span>
                        </div>
                    </label>

                    <label class="cursor-pointer border rounded-xl p-3 flex items-center justify-between transition border-slate-300 hover:border-indigo-500 has-checked:border-indigo-600 has-checked:bg-indigo-50/40">
                        <div class="flex items-center gap-2">
                            <input type="radio" name="role" value="admin"
                                {{ old('role', $user?->role) === 'admin' ? 'checked' : '' }}
                                class="text-indigo-600 focus:ring-indigo-500">
                            <span class="text-sm font-medium text-slate-800">{{ __('staff.role_admin') }}</span>
                        </div>
                    </label>
                </div>
                @error('role')
                    <p class="mt-1.5 text-xs text-red-600 font-medium">{{ $message }}</p>
                @enderror
            </div>

            <!-- Status (is_active) -->
            <div class="pt-2">
                <input type="hidden" name="is_active" value="0">
                <label class="flex items-center gap-3 cursor-pointer">
                    <input type="checkbox" name="is_active" value="1" id="is_active"
                        {{ old('is_active', $user?->is_active ?? true) ? 'checked' : '' }}
                        class="w-4 h-4 rounded-sm border-slate-300 text-indigo-600 focus:ring-indigo-500">
                    <span class="text-sm font-semibold text-slate-700">{{ __('staff.is_active_label') }}</span>
                </label>
                @error('is_active')
                    <p class="mt-1.5 text-xs text-red-600 font-medium">{{ $message }}</p>
                @enderror
            </div>

            <!-- Submit Button -->
            <div class="pt-6 border-t border-slate-200 flex items-center justify-end gap-3">
                <a href="{{ route('staff.users.index') }}"
                    class="px-5 py-2.5 rounded-xl text-sm font-medium text-slate-700 hover:bg-slate-100 transition">
                    {{ __('products.cancel') }}
                </a>
                <button type="submit"
                    class="px-6 py-2.5 rounded-xl text-sm font-semibold text-white bg-indigo-600 hover:bg-indigo-700 shadow-xs transition">
                    {{ $isEdit ? __('staff.staff_updated') : __('staff.save_staff_btn') }}
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

