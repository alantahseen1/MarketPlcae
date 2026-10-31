@extends('layouts.app')

@php
    $isEdit = $product !== null;
    $title = $isEdit ? __('products.edit_title') : __('products.create_title');
    $action = $isEdit ? route('customer.products.update', $product->id) : route('customer.products.store');
    $buttonText = $isEdit ? __('products.update_btn') : __('products.save_btn');
@endphp

@section('title', $title . ' - ' . __('app.site_name'))

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">{{ $title }}</h1>
            <p class="mt-1 text-sm text-gray-500">
                {{ $isEdit ? __('products.edit_title') : __('products.create_title') }}
            </p>
        </div>
        <a href="{{ route('customer.products.index') }}" class="text-sm font-medium text-gray-600 hover:text-gray-900 bg-white border border-gray-300 px-4 py-2 rounded-lg transition">
            &larr; {{ __('products.cancel') }}
        </a>
    </div>

    <div class="bg-white rounded-2xl border border-gray-200 shadow-xs p-6 sm:p-8">
        <form action="{{ $action }}" method="POST" enctype="multipart/form-data" class="space-y-6">
            @csrf
            @if($isEdit)
                @method('PUT')
            @endif

            <!-- Category -->
            <div>
                <label for="category_id" class="block text-sm font-semibold text-gray-700 mb-1.5">
                    {{ __('products.category') }} <span class="text-red-500">*</span>
                </label>
                <select name="category_id" id="category_id" required
                    class="w-full px-3.5 py-2.5 rounded-lg border @error('category_id') border-red-300 bg-red-50/20 @else border-gray-300 @enderror focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm transition">
                    <option value="">-- {{ __('products.select_category') }} --</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}" {{ (string) old('category_id', $product?->category_id) === (string) $category->id ? 'selected' : '' }}>
                            {{ $category->name }}
                        </option>
                    @endforeach
                </select>
                @error('category_id')
                    <p class="mt-1.5 text-xs text-red-600 font-medium">{{ $message }}</p>
                @enderror
            </div>

            <!-- Title -->
            <div>
                <label for="title" class="block text-sm font-semibold text-gray-700 mb-1.5">
                    {{ __('products.title') }} <span class="text-red-500">*</span>
                </label>
                <input type="text" name="title" id="title" value="{{ old('title', $product?->title) }}" required
                    placeholder="{{ __('products.title_placeholder') }}"
                    class="w-full px-3.5 py-2.5 rounded-lg border @error('title') border-red-300 bg-red-50/20 @else border-gray-300 @enderror focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm transition">
                @error('title')
                    <p class="mt-1.5 text-xs text-red-600 font-medium">{{ $message }}</p>
                @enderror
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                <!-- Price -->
                <div>
                    <label for="price" class="block text-sm font-semibold text-gray-700 mb-1.5">
                        {{ __('products.price') }} <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <input type="number" name="price" id="price" value="{{ old('price', $product?->price) }}" required min="1" step="1"
                            placeholder="250000"
                            class="w-full px-3.5 py-2.5 pl-14 rounded-lg border @error('price') border-red-300 bg-red-50/20 @else border-gray-300 @enderror focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm transition">
                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-xs font-semibold text-gray-500 pointer-events-none">
                            {{ __('products.iqd_currency') }}
                        </span>
                    </div>
                    <p class="mt-1 text-xs text-gray-500">{{ __('products.price_hint') }}</p>
                    @error('price')
                        <p class="mt-1.5 text-xs text-red-600 font-medium">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Condition -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1.5">
                        {{ __('products.condition') }} <span class="text-red-500">*</span>
                    </label>
                    <div class="grid grid-cols-2 gap-3 pt-0.5">
                        <label class="cursor-pointer border rounded-lg p-2.5 flex items-center justify-between transition border-gray-300 hover:border-indigo-500 has-checked:border-indigo-600 has-checked:bg-indigo-50/40">
                            <div class="flex items-center gap-2">
                                <input type="radio" name="condition" value="used"
                                    {{ old('condition', $product?->condition ?? 'used') === 'used' ? 'checked' : '' }}
                                    class="text-indigo-600 focus:ring-indigo-500">
                                <span class="text-sm font-medium text-gray-800">{{ __('products.condition_used') }}</span>
                            </div>
                        </label>

                        <label class="cursor-pointer border rounded-lg p-2.5 flex items-center justify-between transition border-gray-300 hover:border-indigo-500 has-checked:border-indigo-600 has-checked:bg-indigo-50/40">
                            <div class="flex items-center gap-2">
                                <input type="radio" name="condition" value="new"
                                    {{ old('condition', $product?->condition) === 'new' ? 'checked' : '' }}
                                    class="text-indigo-600 focus:ring-indigo-500">
                                <span class="text-sm font-medium text-gray-800">{{ __('products.condition_new') }}</span>
                            </div>
                        </label>
                    </div>
                    @error('condition')
                        <p class="mt-1.5 text-xs text-red-600 font-medium">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Description -->
            <div>
                <label for="description" class="block text-sm font-semibold text-gray-700 mb-1.5">
                    {{ __('products.description') }} <span class="text-red-500">*</span>
                </label>
                <textarea name="description" id="description" rows="5" required
                    placeholder="{{ __('products.description_placeholder') }}"
                    class="w-full px-3.5 py-2.5 rounded-lg border @error('description') border-red-300 bg-red-50/20 @else border-gray-300 @enderror focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm transition">{{ old('description', $product?->description) }}</textarea>
                @error('description')
                    <p class="mt-1.5 text-xs text-red-600 font-medium">{{ $message }}</p>
                @enderror
            </div>

            <!-- Current Photos Section (Only for Edit) -->
            @if($isEdit && $product->images->isNotEmpty())
                <div class="pt-4 border-t border-gray-200">
                    <h3 class="text-base font-bold text-gray-900 mb-2">
                        {{ __('products.current_photos') }}
                    </h3>
                    <p class="text-xs text-gray-500 mb-4">
                        {{ __('products.current_photos') }} ({{ $product->images->count() }}). دەتوانیت وێنەیەک دیاری بکەیت وەک سەرەکی، یان هەندێکیان بسڕیتەوە:
                    </p>

                    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-5 gap-4">
                        @foreach($product->images as $image)
                            <div class="relative bg-gray-50 border rounded-xl p-2.5 flex flex-col justify-between transition hover:shadow-xs {{ $image->is_main ? 'border-indigo-500 ring-2 ring-indigo-200' : 'border-gray-200' }}">
                                <div class="aspect-square w-full rounded-lg overflow-hidden bg-gray-200 mb-2">
                                    <img src="{{ asset('storage/' . $image->image_path) }}" alt="Product Image" class="w-full h-full object-cover">
                                </div>

                                <div class="space-y-2 pt-1 border-t border-gray-100">
                                    <!-- Main photo radio -->
                                    <label class="flex items-center gap-1.5 text-xs text-gray-700 cursor-pointer">
                                        <input type="radio" name="main_photo_id" value="{{ $image->id }}"
                                            {{ (string) old('main_photo_id', $image->is_main ? $image->id : '') === (string) $image->id ? 'checked' : '' }}
                                            class="text-indigo-600 focus:ring-indigo-500">
                                        <span class="{{ $image->is_main ? 'font-bold text-indigo-700' : '' }}">
                                            {{ __('products.main_photo') }}
                                        </span>
                                    </label>

                                    <!-- Delete checkbox -->
                                    <label class="flex items-center gap-1.5 text-xs text-red-600 hover:text-red-700 cursor-pointer">
                                        <input type="checkbox" name="delete_photos[]" value="{{ $image->id }}"
                                            {{ in_array($image->id, (array) old('delete_photos', [])) ? 'checked' : '' }}
                                            class="text-red-600 focus:ring-red-500 rounded-sm">
                                        <span>{{ __('products.delete_photo') }}</span>
                                    </label>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    @error('delete_photos')
                        <p class="mt-2 text-xs text-red-600 font-medium">{{ $message }}</p>
                    @enderror
                    @error('main_photo_id')
                        <p class="mt-2 text-xs text-red-600 font-medium">{{ $message }}</p>
                    @enderror
                </div>
            @endif

            <!-- Photos Upload -->
            <div class="pt-4 border-t border-gray-200">
                <label for="photos" class="block text-sm font-semibold text-gray-700 mb-1">
                    {{ $isEdit ? __('products.photos') . ' (' . __('app.register') . ' / نوێ)' : __('products.photos') }}
                    @if(!$isEdit) <span class="text-red-500">*</span> @endif
                </label>
                <p class="text-xs text-gray-500 mb-2">{{ __('products.photos_hint') }}</p>

                <input type="file" name="photos[]" id="photos" multiple accept="image/jpeg,image/png,image/webp"
                    class="w-full text-sm text-gray-500 file:mr-0 file:ml-4 file:py-2.5 file:px-4 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 cursor-pointer border border-gray-300 rounded-lg p-2 bg-gray-50/50">

                @error('photos')
                    <p class="mt-1.5 text-xs text-red-600 font-medium">{{ $message }}</p>
                @enderror
                @error('photos.*')
                    <p class="mt-1.5 text-xs text-red-600 font-medium">{{ $message }}</p>
                @enderror
            </div>

            <!-- Submit & Cancel Buttons -->
            <div class="pt-6 border-t border-gray-200 flex items-center justify-end gap-3">
                <a href="{{ route('customer.products.index') }}"
                    class="px-5 py-2.5 rounded-xl text-sm font-medium text-gray-700 hover:bg-gray-100 transition">
                    {{ __('products.cancel') }}
                </a>
                <button type="submit"
                    class="px-6 py-2.5 rounded-xl text-sm font-semibold text-white bg-indigo-600 hover:bg-indigo-700 shadow-xs transition">
                    {{ $buttonText }}
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

