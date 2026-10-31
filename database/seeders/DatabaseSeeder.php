<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Admin staff user
        User::create([
            'full_name' => 'بەڕێوەبەر',
            'email' => 'admin@example.com',
            'password_hash' => Hash::make('password123'),
            'role' => 'admin',
            'is_active' => true,
        ]);

        // 2. Initial categories in Kurdish (Sorani)
        $categories = [
            'مۆبایل',                          // Phones
            'لاپتۆپ',                          // Laptops
            'کۆمپیوتەر',                       // PCs
            'بارگاویکەرەوە و پێداویستییەکان', // Chargers and accessories
            'ئەوانی تر',                       // Other
        ];

        foreach ($categories as $categoryName) {
            Category::create([
                'name' => $categoryName,
                'is_active' => true,
            ]);
        }
    }
}
