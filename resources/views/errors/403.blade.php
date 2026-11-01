@extends('layouts.app')

@section('title', '403 - ڕێگەپێنەدراو')

@section('content')
<div class="min-h-[60vh] flex items-center justify-center px-4 sm:px-6 lg:px-8 py-16">
    <div class="text-center max-w-md mx-auto">
        <p class="text-6xl sm:text-7xl font-black text-amber-600">403</p>
        <h1 class="mt-4 text-2xl sm:text-3xl font-bold text-gray-900">دەسەڵاتی بینینی ئەم پەڕەیەت نییە</h1>
        <p class="mt-3 text-sm text-gray-500 leading-relaxed">
            ببورە، هەژمارەکەت دەسەڵاتی پێویستی نییە بۆ گەیشتن بەم بەشە.
        </p>
        <div class="mt-8">
            <a href="{{ route('home') }}" class="inline-flex items-center gap-2 px-6 py-3 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl text-sm shadow-xs transition">
                <svg class="w-4 h-4 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                <span>گەڕانەوە بۆ پەڕەی سەرەکی</span>
            </a>
        </div>
    </div>
</div>
@endsection
