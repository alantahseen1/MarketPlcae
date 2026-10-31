<?php

return [
    'accepted' => 'دەبێت :attribute قبوڵ بکرێت.',
    'confirmed' => 'دووبارەکردنەوەی :attribute یەک ناگرێتەوە.',
    'email' => 'دەبێت ناونیشانێکی دروستی ئیمەیڵ بێت.',
    'image' => 'دەبێت :attribute وێنە بێت.',
    'in' => ':attribute هەڵبژێردراو نادروستە.',
    'max' => [
        'numeric' => 'نابێت :attribute لە :max زیاتر بێت.',
        'file' => 'نابێت قەبارەی :attribute لە :max کیلۆبایت زیاتر بێت.',
        'string' => 'نابێت :attribute لە :max پیت زیاتر بێت.',
        'array' => 'نابێت :attribute لە :max بڕگە زیاتر بێت.',
    ],
    'mimes' => 'دەبێت :attribute لە جۆری: :values بێت.',
    'min' => [
        'numeric' => 'دەبێت :attribute لانیکەم :min بێت.',
        'file' => 'دەبێت قەبارەی :attribute لانیکەم :min کیلۆبایت بێت.',
        'string' => 'دەبێت :attribute لانیکەم :min پیت بێت.',
        'array' => 'دەبێت :attribute لانیکەم :min بڕگەی هەبێت.',
    ],
    'nullable' => 'خانەی :attribute دەتوانرێت بە بەتاڵی بمێنێتەوە.',
    'prohibited_if' => 'نابێت خانەی :attribute پڕبکرێتەوە کاتێک :other بریتییە لە :value.',
    'regex' => 'شێوازی :attribute نادروستە.',
    'required' => 'تکایە خانەی :attribute پڕبکەرەوە.',
    'required_if' => 'خانەی :attribute پێویستە کاتێک :other بریتییە لە :value.',
    'string' => 'دەبێت :attribute دەق بێت.',
    'unique' => 'ئەم :attribute پێشتر بەکارهێنراوە و تۆمارکراوە.',

    'attributes' => [
        'full_name' => 'ناوی تەواو',
        'type' => 'جۆری هەژمار',
        'shop_name' => 'ناوی فرۆشگا',
        'logo' => 'لۆگۆی فرۆشگا',
        'phone' => 'ژمارەی مۆبایل',
        'email' => 'ئیمەیڵ',
        'address' => 'ناونیشان',
        'password' => 'وشەی نهێنی',
        'password_confirmation' => 'دووبارەکردنەوەی وشەی نهێنی',
    ],

    'custom' => [
        'phone' => [
            'regex' => 'ژمارەی مۆبایل دەبێت بە شێوازی عێراقی بێت (وەک: 07XXXXXXXXX یان +9647XXXXXXXXX).',
        ],
        'shop_name' => [
            'required_if' => 'ناوی فرۆشگا پێویستە کاتێک جۆری هەژمار فرۆشگایە.',
            'prohibited_if' => 'ناوی فرۆشگا بۆ کڕیاری ئاسایی ناکرێت دابنرێت.',
        ],
        'logo' => [
            'max' => 'قەبارەی لۆگۆ نابێت لە ٢ مێگابایت (2MB) زیاتر بێت.',
            'image' => 'لۆگۆ دەبێت وێنە بێت.',
            'mimes' => 'وێنەی لۆگۆ دەبێت لە جۆری jpg، png، یان webp بێت.',
        ],
    ],
];

