@extends('layouts.staff')

@php
    $isEdit = $category->exists;
    $title = $isEdit ? __('staff.edit_category') : __('staff.add_category');
@endphp

@section('title', $title . ' - ' . __('staff.panel_title'))

@section('content')
<div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
        {{-- Card Header --}}
        <div class="bg-slate-50 border-b border-slate-100 p-6">
            <h1 class="text-xl sm:text-2xl font-bold text-slate-900">{{ $title }}</h1>
        </div>

        <form action="{{ $isEdit ? route('staff.categories.update', $category->id) : route('staff.categories.store') }}" 
              method="POST" 
              class="p-6 sm:p-8 space-y-6">
            @csrf
            @if($isEdit)
                @method('PUT')
            @endif

            {{-- Category Name --}}
            <div>
                <label for="name" class="block text-sm font-bold text-slate-700 mb-1.5">
                    {{ __('staff.category_name') }} <span class="text-red-500">*</span>
                </label>
                <input type="text" 
                       name="name" 
                       id="name" 
                       value="{{ old('name', $category->name) }}" 
                       required 
                       maxlength="100"
                       placeholder="ناوی هاوپۆل بنووسە..."
                       class="w-full px-4 py-2.5 text-sm rounded-xl border border-slate-300 focus:ring-indigo-500 focus:border-indigo-500 @error('name') border-red-500 @enderror">
                @error('name')
                    <p class="mt-1.5 text-xs text-red-600 font-semibold">{{ $message }}</p>
                @enderror
            </div>

            {{-- Is Active Checkbox --}}
            <div class="p-4 bg-slate-50 rounded-xl border border-slate-200">
                <label class="flex items-center gap-3 cursor-pointer">
                    <input type="checkbox" 
                           name="is_active" 
                           value="1" 
                           {{ old('is_active', $category->is_active) ? 'checked' : '' }}
                           class="w-5 h-5 text-indigo-600 rounded-md border-slate-300 focus:ring-indigo-500">
                    <div>
                        <span class="text-sm font-bold text-slate-900 block">{{ __('staff.category_active') }}</span>
                        <span class="text-xs text-slate-500 block mt-0.5">ئەگەر ناچالاک بێت، هاوپۆلەکە و بەرهەمەکانی لە ماڵپەڕ نیشان نادرێن.</span>
                    </div>
                </label>
            </div>

            {{-- Form Actions --}}
            <div class="flex items-center justify-between gap-4 pt-4 border-t border-slate-100">
                <button type="submit" 
                        class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl text-sm shadow-xs transition">
                    {{ __('staff.save_staff_btn') }}
                </button>

                <a href="{{ route('staff.categories.index') }}" 
                   class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold rounded-xl text-sm transition">
                    {{ __('reports.back') }}
                </a>
            </div>
        </form>
    </div>
</div>
@endsection

