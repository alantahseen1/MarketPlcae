@extends('layouts.app')

@section('title', '500 - هەڵەی ناوخۆیی سێرڤەر')

@section('content')
<div class="min-h-[60vh] flex items-center justify-center px-4 sm:px-6 lg:px-8 py-16">
    <div class="text-center max-w-md mx-auto">
        <p class="text-6xl sm:text-7xl font-black text-red-600">500</p>
        <h1 class="mt-4 text-2xl sm:text-3xl font-bold text-gray-900">هەڵەیەکی نەزانراو ڕوویدا</h1>
        <p class="mt-3 text-sm text-gray-500 leading-relaxed">
            ببورە، هەڵەیەک لە سێرڤەرەکەدا ڕوویدا لە کاتی جێبەجێکردنی داواکارییەکەتدا. تیمەکەمان ئاگادارکراونەتەوە بۆ چارەسەرکردنی کێشەکە.
        </p>
        <div class="mt-8">
            <a href="{{ route('home') }}" class="inline-flex items-center gap-2 px-6 py-3 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl text-sm shadow-xs transition">
                <span>گەڕانەوە بۆ پەڕەی سەرەکی</span>
            </a>
        </div>
    </div>
</div>
@endsection
