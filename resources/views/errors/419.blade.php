@extends('layouts.app')

@section('title', '419 - ماوەی دانیشتن بەسەرچوو')

@section('content')
<div class="min-h-[60vh] flex items-center justify-center px-4 sm:px-6 lg:px-8 py-16">
    <div class="text-center max-w-md mx-auto">
        <p class="text-6xl sm:text-7xl font-black text-slate-600">419</p>
        <h1 class="mt-4 text-2xl sm:text-3xl font-bold text-gray-900">ماوەی دانیشتنەکەت بەسەرچوو</h1>
        <p class="mt-3 text-sm text-gray-500 leading-relaxed">
            تکایە بگەڕێوە و دووبارە هەوڵ بدەرەوە. ئەم کێشەیە بەزۆری کاتێک ڕوودەدات کە فۆڕمێک بۆ ماوەیەکی زۆر بە کراوەیی بمێنێتەوە.
        </p>
        <div class="mt-8 flex items-center justify-center gap-3">
            <button type="button" onclick="window.history.back()" class="inline-flex items-center gap-2 px-6 py-3 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl text-sm shadow-xs transition">
                <span>بگەڕێوە و دووبارە هەوڵ بدەرەوە</span>
            </button>
            <a href="{{ route('home') }}" class="px-5 py-3 bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold rounded-xl text-sm transition">
                پەڕەی سەرەکی
            </a>
        </div>
    </div>
</div>
@endsection
