<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Customer;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\Report;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class DemoDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (app()->environment('production')) {
            $this->command->error('DemoDataSeeder cannot be run in production environment!');

            return;
        }

        $this->command->info('Seeding demo data for Kurdistan Electronics Marketplace...');

        // 1. Staff users
        $admin = User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'full_name' => 'بەڕێوەبەری گشتی',
                'password_hash' => Hash::make('password123'),
                'role' => 'admin',
                'is_active' => true,
            ]
        );

        $subadmin1 = User::updateOrCreate(
            ['email' => 'subadmin1@example.com'],
            [
                'full_name' => 'ئاراس ئەحمەد',
                'password_hash' => Hash::make('password123'),
                'role' => 'subadmin',
                'is_active' => true,
            ]
        );

        $subadmin2 = User::updateOrCreate(
            ['email' => 'subadmin2@example.com'],
            [
                'full_name' => 'شێنێ کەریم',
                'password_hash' => Hash::make('password123'),
                'role' => 'subadmin',
                'is_active' => true,
            ]
        );

        User::updateOrCreate(
            ['email' => 'inactive@example.com'],
            [
                'full_name' => 'ڕێبین عەلی',
                'password_hash' => Hash::make('password123'),
                'role' => 'subadmin',
                'is_active' => false,
            ]
        );

        $staffReviewers = [$admin->id, $subadmin1->id, $subadmin2->id];

        // 2. Categories (existing 5 categories)
        $categories = Category::all();
        if ($categories->isEmpty()) {
            $this->call(DatabaseSeeder::class);
            $categories = Category::all();
        }
        $categoryMap = $categories->pluck('id', 'name')->toArray();

        // 3. Customers (25 customers)
        Storage::disk('public')->makeDirectory('logos');
        Storage::disk('public')->makeDirectory('products');

        $regularCustomersData = [
            ['full_name' => 'ئالان عوسمان', 'email' => 'alan@example.com', 'phone' => '07501234501', 'address' => 'هەولێر - بەختیاری'],
            ['full_name' => 'دیار ئەحمەد', 'email' => 'diar@example.com', 'phone' => '07701234502', 'address' => 'سلێمانی - سەرچنار'],
            ['full_name' => 'میران محەمەد', 'email' => 'miran@example.com', 'phone' => '07501234503', 'address' => 'دهۆک - ماسیکێ'],
            ['full_name' => 'هەوراز فەتاح', 'email' => 'hawraz@example.com', 'phone' => '07801234504', 'address' => 'هەولێر - کانی گەنجان'],
            ['full_name' => 'ڕەوەند عەلی', 'email' => 'rawand@example.com', 'phone' => '07701234505', 'address' => 'سلێمانی - توی مەلیک'],
            ['full_name' => 'کارۆخ مەحموود', 'email' => 'karokh@example.com', 'phone' => '07501234506', 'address' => 'کەرکووک - ڕەحیماوا'],
            ['full_name' => 'سۆران قادر', 'email' => 'soran@example.com', 'phone' => '07701234507', 'address' => 'هەڵەبجە - شارەوانی'],
            ['full_name' => 'پەیوەند سالار', 'email' => 'paywand@example.com', 'phone' => '07501234508', 'address' => 'زاخۆ - بێدارێ'],
            ['full_name' => 'بەرهەم ڕەشید', 'email' => 'barham@example.com', 'phone' => '07801234509', 'address' => 'کۆیە - بفڕین'],
            ['full_name' => 'هۆزان ئیبراهیم', 'email' => 'hozan@example.com', 'phone' => '07501234510', 'address' => 'هەولێر - عین کاوە'],
            ['full_name' => 'شوان تاهیر', 'email' => 'shwan@example.com', 'phone' => '07701234511', 'address' => 'سلێمانی - ڕاپەڕین'],
            ['full_name' => 'دانەر عەزیز', 'email' => 'danar@example.com', 'phone' => '07501234512', 'address' => 'دهۆک - ماڵتا'],
            ['full_name' => 'لاوەند جەبار', 'email' => 'lawand@example.com', 'phone' => '07801234513', 'address' => 'هەولێر - شەقامی 100 مەتری'],
            ['full_name' => 'زانا حەسەن', 'email' => 'zana@example.com', 'phone' => '07701234514', 'address' => 'سلێمانی - ئاشتی'],
            ['full_name' => 'ڕێبوار کەمال', 'email' => 'rebwar@example.com', 'phone' => '07501234515', 'address' => 'هەولێر - دریم ستی'],
        ];

        $customers = [];
        foreach ($regularCustomersData as $regData) {
            $customer = Customer::updateOrCreate(
                ['email' => $regData['email']],
                [
                    'type' => 'regular',
                    'full_name' => $regData['full_name'],
                    'shop_name' => null,
                    'logo' => null,
                    'phone' => $regData['phone'],
                    'password_hash' => Hash::make('password123'),
                    'address' => $regData['address'],
                    'is_verified' => false,
                    'is_banned' => false,
                ]
            );
            $customers[] = $customer;
        }

        // 8 Shops (3 verified, 5 unverified)
        $shopsData = [
            [
                'full_name' => 'بەختیار نەجمەدین',
                'shop_name' => 'شاری مۆبایل',
                'email' => 'shop1@example.com',
                'phone' => '07502223301',
                'address' => 'هەولێر - شەقامی 60 مەتری',
                'is_verified' => true,
                'color' => [37, 99, 235], // Blue
            ],
            [
                'full_name' => 'کامەران ساڵح',
                'shop_name' => 'تەکنەلۆژیای پایتەخت',
                'email' => 'shop2@example.com',
                'phone' => '07702223302',
                'address' => 'سلێمانی - شەقامی سەهۆڵەکە',
                'is_verified' => true,
                'color' => [16, 185, 129], // Emerald
            ],
            [
                'full_name' => 'سەرهەد یووسف',
                'shop_name' => 'دهۆک کۆمپیوتەر',
                'email' => 'shop3@example.com',
                'phone' => '07502223303',
                'address' => 'دهۆک - ناوچەی KRO',
                'is_verified' => true,
                'color' => [99, 102, 241], // Indigo
            ],
            [
                'full_name' => 'پۆڵا عەبدوڵڵا',
                'shop_name' => 'گێکس ئایتی',
                'email' => 'shop4@example.com',
                'phone' => '07502223304',
                'address' => 'هەولێر - عەنکاوە',
                'is_verified' => false,
                'color' => [217, 119, 6], // Amber
            ],
            [
                'full_name' => 'ئاری مەجید',
                'shop_name' => 'کوردستان ئەپڵ ستۆر',
                'email' => 'shop5@example.com',
                'phone' => '07702223305',
                'address' => 'سلێمانی - شەقامی سالم',
                'is_verified' => false,
                'color' => [147, 51, 234], // Purple
            ],
            [
                'full_name' => 'جەمال تۆفیق',
                'shop_name' => 'لیدەر ئیلیکترۆنیکس',
                'email' => 'shop6@example.com',
                'phone' => '07802223306',
                'address' => 'کەرکووک - قۆریە',
                'is_verified' => false,
                'color' => [225, 29, 72], // Rose
            ],
            [
                'full_name' => 'بێوار ڕەمەزان',
                'shop_name' => 'فاست مۆبایل',
                'email' => 'shop7@example.com',
                'phone' => '07502223307',
                'address' => 'زاخۆ - ناو بازاڕ',
                'is_verified' => false,
                'color' => [13, 148, 136], // Teal
            ],
            [
                'full_name' => 'سەردار فەقێ',
                'shop_name' => 'سمارت سێنتەر کۆیە',
                'email' => 'shop8@example.com',
                'phone' => '07802223308',
                'address' => 'کۆیە - ناو شار',
                'is_verified' => false,
                'color' => [79, 70, 229], // Indigo
            ],
        ];

        $shops = [];
        foreach ($shopsData as $idx => $sData) {
            $logoRelPath = "logos/shop_{$idx}.png";
            $this->generateLogoImage(storage_path("app/public/{$logoRelPath}"), $sData['shop_name'], $sData['color']);

            $shop = Customer::updateOrCreate(
                ['email' => $sData['email']],
                [
                    'type' => 'shop',
                    'full_name' => $sData['full_name'],
                    'shop_name' => $sData['shop_name'],
                    'logo' => $logoRelPath,
                    'phone' => $sData['phone'],
                    'password_hash' => Hash::make('password123'),
                    'address' => $sData['address'],
                    'is_verified' => $sData['is_verified'],
                    'is_banned' => false,
                ]
            );
            $shops[] = $shop;
            $customers[] = $shop;
        }

        // 2 Banned Customers (1 regular, 1 shop)
        $bannedRegular = Customer::updateOrCreate(
            ['email' => 'banned1@example.com'],
            [
                'type' => 'regular',
                'full_name' => 'هێرش نەوزاد (بلۆککراو)',
                'shop_name' => null,
                'logo' => null,
                'phone' => '07509998801',
                'password_hash' => Hash::make('password123'),
                'address' => 'هەولێر - تەعجیل',
                'is_verified' => false,
                'is_banned' => true,
            ]
        );
        $customers[] = $bannedRegular;

        $bannedLogoPath = 'logos/shop_banned.png';
        $this->generateLogoImage(storage_path("app/public/{$bannedLogoPath}"), 'بلۆک', [100, 116, 139]);

        $bannedShop = Customer::updateOrCreate(
            ['email' => 'banned2@example.com'],
            [
                'type' => 'shop',
                'full_name' => 'نەبەز کامیل',
                'shop_name' => 'بازاڕی ساختە (بلۆککراو)',
                'logo' => $bannedLogoPath,
                'phone' => '07709998802',
                'password_hash' => Hash::make('password123'),
                'address' => 'سلێمانی - بەکرەجۆ',
                'is_verified' => false,
                'is_banned' => true,
            ]
        );
        $customers[] = $bannedShop;

        // 4. Products (~80 products)
        $productsCatalog = [
            // Phones (مۆبایل)
            [
                'cat' => 'مۆبایل',
                'title' => 'Apple iPhone 15 Pro Max 256GB تایتانیۆم سروشتی',
                'desc' => "مۆبایلی ئایفۆن ١٥ پڕۆ ماکس، قەبارەی ٢٥٦ گێگابایت، ڕەنگی Natural Titanium.\nپاک و بێ کێشە، پاتری ٩٨٪. هەموو کەلوپەل و کارتۆنی لەگەڵدایە.",
                'price' => 1480000,
                'cond' => 'used',
            ],
            [
                'cat' => 'مۆبایل',
                'title' => 'Samsung Galaxy S24 Ultra 512GB نوێ سفر',
                'desc' => "سامسۆنگ گالاکسی ئێس ٢٤ ئۆڵترا، ٥١٢ گێگابایت، ڕامی ١٢ گێگا.\nنوێیە و لە کارتۆن نەکراوەتەوە بە زەمانەتی فەرمی ساڵێک.",
                'price' => 1650000,
                'cond' => 'new',
            ],
            [
                'cat' => 'مۆبایل',
                'title' => 'iPhone 14 Pro 128GB ڕەش Deep Purple',
                'desc' => "ئایفۆن ١٤ پڕۆ، ١٢٨ گێگابایت، پاتری ٨٨٪، زۆر پاکە و هیچ شوختێکی پێوە نییە.\nلەگەڵ بارگاویکەرەوەی ئەسڵی و کەڤەر.",
                'price' => 1120000,
                'cond' => 'used',
            ],
            [
                'cat' => 'مۆبایل',
                'title' => 'Google Pixel 8 Pro 256GB شین کەناری',
                'desc' => "گووگڵ پیکسڵ ٨ پڕۆ بە کامێرای باوەڕپێنەکراو و ژیری دەستکردی گووگڵ.\nپاک وەک نوێ، ٢٥٦ گێگابایت.",
                'price' => 980000,
                'cond' => 'used',
            ],
            [
                'cat' => 'مۆبایل',
                'title' => 'Xiaomi 14 Ultra 512GB لەگەڵ کیتی فۆتۆگرافی',
                'desc' => "شاومی ١٤ ئۆڵترا لەگەڵ Photography Kit، بەهێزترین کامێرا لە جیهانی مۆبایل بە هاوبەشی لەگەڵ Leica.\nنوێ لە پاکەت نەکراوەتەوە.",
                'price' => 1400000,
                'cond' => 'new',
            ],
            [
                'cat' => 'مۆبایل',
                'title' => 'iPhone 13 128GB شین Starlight',
                'desc' => 'ئایفۆن ١٣ ئاسایی ١٢٨ گێگا، پاتری ٨٥٪، کاردەکات لەسەر هەموو سیمکارتێک بێ هیچ کێشەیەک.',
                'price' => 690000,
                'cond' => 'used',
            ],
            [
                'cat' => 'مۆبایل',
                'title' => 'Samsung Galaxy A55 5G 256GB نوێ',
                'desc' => "مۆبایلی گالاکسی A55 بە پەیکەری ئەلەمنیۆم و شاشەی Super AMOLED 120Hz، پاتری ٥٠٠٠ میلی ئەمپێر.\nسفر بە کارتن.",
                'price' => 450000,
                'cond' => 'new',
            ],
            [
                'cat' => 'مۆبایل',
                'title' => 'OnePlus 12 512GB سەوز Emerald',
                'desc' => "وان پڵەس ١٢ بە پڕۆسێسەری Snapdragon 8 Gen 3 و بارگاویبوونەوەی خێرای ١٠٠ واط.\nزۆر خاوێنە و کەم بەکارهاتووە.",
                'price' => 950000,
                'cond' => 'used',
            ],
            [
                'cat' => 'مۆبایل',
                'title' => 'Nothing Phone (2) 256GB سپی',
                'desc' => "ناسینگ فۆن ٢ بە ڕووناکییە ناوازەکانی Glyph لە پشتەوە، سیستەمی بێ خەوش و خێرا.\nبەکارهاتووی زۆر پاک.",
                'price' => 620000,
                'cond' => 'used',
            ],
            [
                'cat' => 'مۆبایل',
                'title' => 'Samsung Galaxy Z Fold 5 512GB ڕەش',
                'desc' => "گالاکسی زێد فۆڵد ٥ قەدکراو بە دوو شاشەی گەورە، گونجاو بۆ کار و مالتیمیدیا.\nهیچ کێشەی میحوەری شاشەی نییە، وەک نوێ.",
                'price' => 1550000,
                'cond' => 'used',
            ],
            [
                'cat' => 'مۆبایل',
                'title' => 'Honor Magic 6 Pro 512GB سەوز',
                'desc' => "هۆنەر ماجیک ٦ پڕۆ بە باشترین باتری لە پۆلی خۆیدا و کامێرای پێشکەوتوو.\nلەگەڵ بارگاویکەرەوەی ٨٠ واط و کارتۆن.",
                'price' => 1100000,
                'cond' => 'used',
            ],
            [
                'cat' => 'مۆبایل',
                'title' => 'iPhone 11 128GB ڕەش',
                'desc' => 'ئایفۆن ١١، ١٢٨ گێگابایت، پاتری گۆڕاوە بە ئەسڵی 100%، هەموو بەشەکانی تری بێ دەستکاریکردنن.',
                'price' => 380000,
                'cond' => 'used',
            ],
            [
                'cat' => 'مۆبایل',
                'title' => 'Redmi Note 13 Pro+ 5G 512GB',
                'desc' => "شاومی ڕێدمی نۆت ١٣ پڕۆ پڵەس، شاشەی چەماوە، بارگاویکەرەوەی ١٢٠ واط (تەنها لە ١٩ خولەک پڕ دەبێتەوە).\nسفر بە زەمانەت.",
                'price' => 470000,
                'cond' => 'new',
            ],
            [
                'cat' => 'مۆبایل',
                'title' => 'Realme GT 5 Pro 256GB',
                'desc' => 'مۆبایلی گەیمینگی ڕیەڵمی جی تی ٥ پڕۆ، ساردکەرەوەی پێشکەوتوو و کامێرای زۆم.',
                'price' => 740000,
                'cond' => 'used',
            ],
            [
                'cat' => 'مۆبایل',
                'title' => 'iPhone 15 128GB پەمەیی Pink سفر',
                'desc' => "ئایفۆن ١٥ ئاسایی، ڕەنگی پەمەیی سەرنجڕاکێش، داینامیک ئایلەند و دەرچەی Type-C.\nسفر نەکراوەتەوە.",
                'price' => 1050000,
                'cond' => 'new',
            ],
            [
                'cat' => 'مۆبایل',
                'title' => 'Sony Xperia 1 V 256GB پلاتینیۆم',
                'desc' => 'سۆنی ئێکسپێریا تایبەت بە وێنەگران، دەرچەی هێدفۆن و کارت میمۆری لەخۆدەگرێت.',
                'price' => 1020000,
                'cond' => 'used',
            ],

            // Laptops (لاپتۆپ)
            [
                'cat' => 'لاپتۆپ',
                'title' => 'Apple MacBook Pro 16" M3 Max 36GB 1TB بۆری',
                'desc' => "ماکبووک پڕۆ ١٦ ئینج بە چیپی بەهێزی M3 Max، ٣٦ گێگا ڕام و ١ تێرابایت SSD.\nپاتری ١٠٠٪ و سایکڵ تەنها ١٢، وەک نوێ بە هەموو کەلوپەلەکانیەوە.",
                'price' => 3900000,
                'cond' => 'used',
            ],
            [
                'cat' => 'لاپتۆپ',
                'title' => 'Apple MacBook Air 15" M2 8GB 256GB نیمچە شەو',
                'desc' => "ماکبووک ئەیر ١٥ ئینج، تەنک و سووک، بە پاترییەک کە تا ١٨ کاتژمێر دەمێنێتەوە.\nپاک و بێ هیچ عەیبێک.",
                'price' => 1350000,
                'cond' => 'used',
            ],
            [
                'cat' => 'لاپتۆپ',
                'title' => 'Dell XPS 15 9530 i7-13700H RTX 4060 OLED 3.5K',
                'desc' => "دێڵ ئێکس پی ئێس ١٥، شاشەی سەرسوڕهێنەری OLED تاچ، ٣٢ گێگا ڕام، ١ تێرابایت SSD، گرافیک کارتی RTX 4060.\nسفر بە کارتۆن.",
                'price' => 2450000,
                'cond' => 'new',
            ],
            [
                'cat' => 'لاپتۆپ',
                'title' => 'Lenovo ThinkPad X1 Carbon Gen 11 i7 16GB 512GB',
                'desc' => 'تینکپاد ئێکس ١ کاربۆن، تایبەت بە کار و بازرگانی، کێشی کەمتر لە ١.١ کیلۆگرامە، کیبۆردی زۆر ئاسوودە.',
                'price' => 1500000,
                'cond' => 'used',
            ],
            [
                'cat' => 'لاپتۆپ',
                'title' => 'ASUS ROG Zephyrus G14 Ryzen 9 RTX 4070 32GB',
                'desc' => 'لاپتۆپی گەیمینگی ناسراوی ئەسوس، شاشەی ROG Nebula OLED 120Hz، زۆر بەهێز بۆ هەموو یارییە قورسەکان.',
                'price' => 2300000,
                'cond' => 'used',
            ],
            [
                'cat' => 'لاپتۆپ',
                'title' => 'HP Spectre x360 14 2-in-1 Core Ultra 7 OLED',
                'desc' => "ئێچ پی سپێکتەر دوو لە یەک، دەبێت بە ئایپاد، شاشەی OLED، پێنووسی زیرەکی لەگەڵدایە.\nنوێ بە زەمانەت.",
                'price' => 1850000,
                'cond' => 'new',
            ],
            [
                'cat' => 'لاپتۆپ',
                'title' => 'Acer Predator Helios 16 i9-13900HX RTX 4080',
                'desc' => 'ئەیسەر پریداتۆر ١٦ بۆ یاری و کاری ڕێندەر و مۆنتاژ، گرافیکی RTX 4080 12GB، شاشەی 240Hz.',
                'price' => 2800000,
                'cond' => 'used',
            ],
            [
                'cat' => 'لاپتۆپ',
                'title' => 'MSI Stealth 14 Studio i7 RTX 4060 سپی',
                'desc' => 'ئێم ئێس ئای ستێڵس ١٤ تەنک و بەهێز، گونجاو بۆ ئەندازیاران و دیزاینەران.',
                'price' => 1650000,
                'cond' => 'used',
            ],
            [
                'cat' => 'لاپتۆپ',
                'title' => 'Apple MacBook Pro 14" M1 Pro 16GB 512GB',
                'desc' => 'ماکبووک پڕۆ ١٤ بە چیپی M1 Pro، دەنگی نایاب، پاتری زۆر باش، شاشەی Liquid Retina XDR.',
                'price' => 1450000,
                'cond' => 'used',
            ],
            [
                'cat' => 'لاپتۆپ',
                'title' => 'Microsoft Surface Pro 9 i7 16GB 256GB لەگەڵ کیبۆرد',
                'desc' => 'سەرفەیس پڕۆ ٩ مایکرۆسۆفت، شاشەی تاچ 120Hz، پاتری نوێ، کیبۆردی ئەسڵی پەیوەستکراوی لەگەڵە.',
                'price' => 1250000,
                'cond' => 'used',
            ],
            [
                'cat' => 'لاپتۆپ',
                'title' => 'ASUS TUF Gaming A15 Ryzen 7 RTX 4050 نوێ',
                'desc' => "ئەسوس تەف گەیمینگ، ١٦ گێگا ڕام، ٥١٢ SSD، زۆر ئابووری و بەهێزە بۆ یاری.\nنوێیە.",
                'price' => 1100000,
                'cond' => 'new',
            ],
            [
                'cat' => 'لاپتۆپ',
                'title' => 'Dell Latitude 5420 i5 11th Gen 16GB 256GB',
                'desc' => 'دێڵ لاتیتود گونجاو بۆ خوێندکاران و مامۆستایان، خاوێن و بێ کێشە، پاتری چوار کاتژمێر دەمێنێتەوە.',
                'price' => 450000,
                'cond' => 'used',
            ],

            // PCs (کۆمپیوتەر)
            [
                'cat' => 'کۆمپیوتەر',
                'title' => 'کەیسی گەیمینگ Core i7-14700K RTX 4080 Super 32GB DDR5',
                'desc' => "کۆمپیوتەری دێسکتۆپ بۆ یاری لەسەر 4K و مۆنتاژی ڤیدیۆی قورس:\nپڕۆسێسەر: i7-14700K\nمەزەربۆرد: ASUS Z790 WiFi\nگرافیک: RTX 4080 Super 16GB\nپاوەر: Corsair 850W Gold\nکەیسی NZXT H9 Flow سپی.",
                'price' => 3100000,
                'cond' => 'new',
            ],
            [
                'cat' => 'کۆمپیوتەر',
                'title' => 'کەیسی گەیمینگی ئابووری Ryzen 5 5600 RTX 3060 16GB',
                'desc' => "کۆمپیوتەر بۆ هەموو یارییەکانی وەک PUBG, GTA V, Valorant بە فریمێکی زۆر بەرز.\nزۆر پاک بەکارهاتووە و پلەی گەرمی ساردە.",
                'price' => 880000,
                'cond' => 'used',
            ],
            [
                'cat' => 'کۆمپیوتەر',
                'title' => 'مۆنیتەری Samsung Odyssey G7 27" 240Hz 1440p چەماوە',
                'desc' => "شاشەی سەرسوڕهێنەری سامسۆنگ ئۆدیسی جی ٧، چەماوەی 1000R، خێرایی 240 هێرتز بە کوالیتی 2K.\nبێ هیچ پیکسڵێکی مردوو.",
                'price' => 580000,
                'cond' => 'used',
            ],
            [
                'cat' => 'کۆمپیوتەر',
                'title' => 'مۆنیتەری LG UltraGear 34" Curved WQHD 160Hz نوێ',
                'desc' => "شاشەی پان و گەورەی ئێڵ جی ٣٤ ئینج تایبەت بە کار و گەیمینگ، Nano IPS.\nنوێ بە کارتۆن.",
                'price' => 750000,
                'cond' => 'new',
            ],
            [
                'cat' => 'کۆمپیوتەر',
                'title' => 'کۆمپیوتەری Apple Mac Studio M2 Max 32GB 512GB',
                'desc' => "ئەپڵ ماک ستۆدیۆ، زۆر بێدەنگ و خێرا بۆ بەرنامەکانی فۆتۆشۆپ و پریمیەر و ئەفتر ئیفێکتس.\nوەک نوێ.",
                'price' => 2400000,
                'cond' => 'used',
            ],
            [
                'cat' => 'کۆمپیوتەر',
                'title' => 'کیبۆردی میکانیکی Corsair K70 RGB PRO لەگەڵ ماوسی گەیمینگ',
                'desc' => 'کیبۆردی کۆرسێر سۆفتوێری iCUE لەگەڵ سویچی سوور، ماوسی Logitech G502 HERO بێ کێشە.',
                'price' => 190000,
                'cond' => 'used',
            ],
            [
                'cat' => 'کۆمپیوتەر',
                'title' => 'کەیسی ئۆفیسی Dell OptiPlex Core i5 8GB 256GB SSD',
                'desc' => 'دێڵ ئۆپتیپلێکس گونجاو بۆ فەرمانگە و ژمێریاری و فرۆشگاکان، تێکناچێت و بەردەوام کاردەکات.',
                'price' => 220000,
                'cond' => 'used',
            ],
            [
                'cat' => 'کۆمپیوتەر',
                'title' => 'گرافیک کارتی Gigabyte GeForce RTX 4070 Ti Super 16GB',
                'desc' => 'کارت شاشەی جیگابایت سێ پانکە، بەکارنەهاتووە و تەنها بۆ پشکنین کراوەتەوە.',
                'price' => 1200000,
                'cond' => 'new',
            ],

            // Chargers & Accessories (بارگاویکەرەوە و پێداویستییەکان)
            [
                'cat' => 'بارگاویکەرەوە و پێداویستییەکان',
                'title' => 'Anker 737 Power Bank 24,000mAh 140W بارگاویکەرەوەی گەڕۆک',
                'desc' => "پاوەربانکی ناسراوی ئەنکەر بە شاشەی دیجیتاڵی، توانای شەحنکردنەوەی ماکبووک و هەموو مۆبایلەکان بە خێرایی ١٤٠ واط هەیە.\nئەسڵی و نوێ.",
                'price' => 175000,
                'cond' => 'new',
            ],
            [
                'cat' => 'بارگاویکەرەوە و پێداویستییەکان',
                'title' => 'Apple AirPods Pro 2nd Generation بە دەرچەی USB-C',
                'desc' => "ئێرپۆدز پڕۆ ٢ ئەپڵ، ڕاگرتنی دەنگەدەنگ (ANC) زۆر بەهێزە، پاتری نوێ.\nپاک و بێ شوخت.",
                'price' => 270000,
                'cond' => 'used',
            ],
            [
                'cat' => 'بارگاویکەرەوە و پێداویستییەکان',
                'title' => 'هێدفۆنی بێوایەری Sony WH-1000XM5 ڕەش',
                'desc' => "باشترین هێدفۆن بۆ گەشت و کارکردن لە دەنگەدەنگدا، دەنگی سەرسوڕهێنەر و کوالیتی بەرز.\nلەگەڵ کیف و کەیبڵەکان.",
                'price' => 380000,
                'cond' => 'used',
            ],
            [
                'cat' => 'بارگاویکەرەوە و پێداویستییەکان',
                'title' => 'سەری شەحنی ئەسڵی Apple 20W USB-C لەگەڵ وایەری ئەسڵی',
                'desc' => "بارگاویکەرەوەی ئەسڵی ئەپڵ بۆ ئایفۆن و ئایپاد، پارێزگاری لە پاتری مۆبایل دەکات.\nنوێ بە زەمانەت.",
                'price' => 35000,
                'cond' => 'new',
            ],
            [
                'cat' => 'بارگاویکەرەوە و پێداویستییەکان',
                'title' => 'Baseus Blade 100W Power Bank تەنک بۆ لاپتۆپ',
                'desc' => 'پاوەربانکی تەنکی بیسۆس، گونجاوە بۆ ناو جانتای لاپتۆپ، شەحنی لاپتۆپ و مۆبایل دەکاتەوە.',
                'price' => 95000,
                'cond' => 'new',
            ],
            [
                'cat' => 'بارگاویکەرەوە و پێداویستییەکان',
                'title' => 'Samsung Galaxy Buds 2 Pro شین شەوەکی',
                'desc' => 'هێدفۆنی سامسۆنگ گالاکسی بەدەنگی Hi-Fi 24bit، پاک و کەم بەکارهاتووە.',
                'price' => 140000,
                'cond' => 'used',
            ],
            [
                'cat' => 'بارگاویکەرەوە و پێداویستییەکان',
                'title' => 'شەحنی بێ وایەری Belkin BoostCharge Pro 3-in-1 MagSafe',
                'desc' => 'ستاندی شەحنکردنەوەی هاوکاتی ئایفۆن و ئەپڵ وۆچ و ئێرپۆدز، شیک و ڕێک بۆ سەر مێزی خەوتن.',
                'price' => 160000,
                'cond' => 'used',
            ],
            [
                'cat' => 'بارگاویکەرەوە و پێداویستییەکان',
                'title' => 'هاردی دەرەکی SanDisk Extreme Portable SSD 1TB دژە ئاو',
                'desc' => "هارد ئێس ئێس دی خێرایی ١٠٥٠ مێگابایت لە چرکەیەکدا، بچووک و دژە زەبر و دژە تۆز و ئاو.\nنوێ لە کارتۆن.",
                'price' => 145000,
                'cond' => 'new',
            ],

            // Other (ئەوانی تر)
            [
                'cat' => 'ئەوانی تر',
                'title' => 'Sony PlayStation 5 Slim 1TB لەگەڵ دوو دەسک و یاری',
                'desc' => "پلەیستەیشن ٥ سلێم، هاردی ١ تێرابایت، زۆر خاوێنە و یارییەکانی FC 24 و Spider-Man 2 لەسەرە.\nلەگەڵ دوو دەسکی ئەسڵی.",
                'price' => 640000,
                'cond' => 'used',
            ],
            [
                'cat' => 'ئەوانی تر',
                'title' => 'Xbox Series X 1TB ڕەش نوێ سفر',
                'desc' => "ئێکس بۆکس سیریس ئێکس، بەهێزترین کۆنسۆڵی مایکرۆسۆفت، گرافیکی 4K 120fps.\nسفر لە کارتۆن نەکراوەتەوە.",
                'price' => 670000,
                'cond' => 'new',
            ],
            [
                'cat' => 'ئەوانی تر',
                'title' => 'Nintendo Switch OLED مۆدێلی سپی',
                'desc' => "نینتێندۆ سویچ بە شاشەی ڕوونی OLED، لەگەڵ میمۆری ١٢٨ گێگا و کیفی پارێزەر.\nبێ هیچ شوختێک لەسەر شاشەکەی.",
                'price' => 360000,
                'cond' => 'used',
            ],
            [
                'cat' => 'ئەوانی تر',
                'title' => 'Apple Watch Ultra 2 49mm تایتانیۆم لەگەڵ قایشی پرتەقاڵی',
                'desc' => "کاتژمێری زیرەکی ئەپڵ وۆچ ئۆڵترا ٢، بەهێزترین پاتری و بەرگەگرتنی ئاو تا قوڵایی ١٠٠ مەتر.\nپاتری ١٠٠٪ و کەلوپەل تەواو.",
                'price' => 980000,
                'cond' => 'used',
            ],
            [
                'cat' => 'ئەوانی تر',
                'title' => 'کامێرای وەرزشی GoPro HERO12 Black بە ملحەقاتەوە',
                'desc' => 'گۆپڕۆ ١٢ بە توانای تۆمارکردنی ڤیدیۆی 5.3K HDR، سێ پاتری و ستیك و چەسپێنەری لەگەڵدایە.',
                'price' => 480000,
                'cond' => 'used',
            ],
            [
                'cat' => 'ئەوانی تر',
                'title' => 'Apple iPad Pro 11" M4 256GB تەنکترین دیزاین',
                'desc' => "ئایپاد پڕۆ چیپی M4 بە شاشەی نوێی Tandem OLED، سەرسوڕهێنەر بۆ وێنەکێشان و خوێندنەوە.\nسفر بە زەمانەت.",
                'price' => 1350000,
                'cond' => 'new',
            ],
            [
                'cat' => 'ئەوانی تر',
                'title' => 'DJI Mini 4 Pro فڕۆکەی بێفڕۆکەوان (درۆن) بە تەواوی کەلوپەلەکانەوە',
                'desc' => "درۆنی دی جی ئای کێشی ٢٤٩ گرام، سێنسەری سەلامەتی ٣٦٠ پلە، تۆماری 4K 60fps.\nوەک نوێ لەگەڵ دوو باتری زیادە.",
                'price' => 1150000,
                'cond' => 'used',
            ],
            [
                'cat' => 'ئەوانی تر',
                'title' => 'Samsung Galaxy Watch 6 Classic 47mm زیوی',
                'desc' => 'کاتژمێری سامسۆنگ کلاسیك بە بازنەی خولاوە، پێوانی پەستانی خوێن و لێدانی دڵ، زۆر شیک و فەرمی.',
                'price' => 260000,
                'cond' => 'used',
            ],
        ];

        // Replicate items with realistic variations to get to ~80 products
        $fullCatalog = [];
        $variationIndex = 1;
        while (count($fullCatalog) < 80) {
            foreach ($productsCatalog as $item) {
                if (count($fullCatalog) >= 80) {
                    break;
                }
                $cloned = $item;
                if ($variationIndex > 1) {
                    $cloned['title'] = $cloned['title']." (دانەی {$variationIndex})";
                    $cloned['price'] = (int) ($cloned['price'] + ($variationIndex * 5000));
                }
                $fullCatalog[] = $cloned;
            }
            $variationIndex++;
        }

        // Status distributions:
        // Approved: ~55
        // Pending: ~10
        // Rejected: ~6
        // Sold: ~5
        // Removed: ~4
        $rejectionReasons = [
            'وێنەی بەرهەمەکە ڕوون نییە، تکایە وێنەی ڕوون و لەبەرچاو دابنێ.',
            'زانیاری پەیوەندی لە ناو وەسفەکەدا دانراوە کە پێچەوانەی یاساکانە.',
            'نرخی بەرهەمەکە واقیعی نییە و لەگەڵ بازاڕ یەکناگرێتەوە.',
            'بەرهەمی ئەلیکترۆنی نییە و لەم بەشەدا وەرناگیرێت.',
            'زانیاری وەسف تەواو نییە و کەم و کوڕی تێدایە.',
            'وێنەی داگیراو لە ئینتەرنێت دانراوە نەک وێنەی ڕاستەقینەی بەرهەمەکە.',
        ];

        $products = [];
        foreach ($fullCatalog as $i => $item) {
            $catId = $categoryMap[$item['cat']] ?? $categories->first()->id;

            // Assign customers:
            // Ensure some items belong to banned users (e.g. index 75 to bannedRegular, index 76 to bannedShop)
            if ($i === 75) {
                $customer = $bannedRegular;
            } elseif ($i === 76) {
                $customer = $bannedShop;
            } else {
                // Distribute between regular customers and shops
                $customer = $customers[$i % (count($customers) - 2)]; // exclude the 2 banned from random loop
            }

            // Determine status
            if ($i < 55) {
                $status = 'approved';
                $reviewedBy = $staffReviewers[$i % count($staffReviewers)];
                $reviewedAt = Carbon::now()->subDays(rand(1, 30))->subHours(rand(1, 23));
                $rejectionReason = null;
                $soldAt = null;
            } elseif ($i < 65) {
                $status = 'pending';
                $reviewedBy = null;
                $reviewedAt = null;
                $rejectionReason = null;
                $soldAt = null;
            } elseif ($i < 71) {
                $status = 'rejected';
                $reviewedBy = $staffReviewers[$i % count($staffReviewers)];
                $reviewedAt = Carbon::now()->subDays(rand(1, 10));
                $rejectionReason = $rejectionReasons[$i % count($rejectionReasons)];
                $soldAt = null;
            } elseif ($i < 76) {
                $status = 'sold';
                $reviewedBy = $staffReviewers[$i % count($staffReviewers)];
                $reviewedAt = Carbon::now()->subDays(rand(15, 40));
                $rejectionReason = null;
                $soldAt = Carbon::now()->subDays(rand(1, 14));
            } else {
                $status = 'removed';
                $reviewedBy = $staffReviewers[$i % count($staffReviewers)];
                $reviewedAt = Carbon::now()->subDays(rand(10, 20));
                $rejectionReason = null;
                $soldAt = null;
            }

            $product = Product::create([
                'customer_id' => $customer->id,
                'category_id' => $catId,
                'title' => $item['title'],
                'description' => $item['desc'],
                'price' => $item['price'],
                'condition' => $item['cond'],
                'status' => $status,
                'rejection_reason' => $rejectionReason,
                'reviewed_by' => $reviewedBy,
                'reviewed_at' => $reviewedAt,
                'sold_at' => $soldAt,
                'created_at' => Carbon::now()->subDays(rand(1, 45))->subHours(rand(1, 23)),
                'updated_at' => Carbon::now()->subDays(rand(0, 5)),
            ]);

            // 5. Product Images (1 to 4 images per product)
            $imageCount = rand(1, 4);
            $productDir = "products/{$product->id}";
            Storage::disk('public')->makeDirectory($productDir);

            for ($imgIdx = 1; $imgIdx <= $imageCount; $imgIdx++) {
                $imagePath = "{$productDir}/img_{$imgIdx}.jpg";
                $this->generateProductImage(
                    storage_path("app/public/{$imagePath}"),
                    $product->title,
                    $imgIdx,
                    $item['cond']
                );

                $thumbPath = "{$productDir}/thumb_img_{$imgIdx}.jpg";
                $this->generateProductThumbnail(
                    storage_path("app/public/{$thumbPath}"),
                    $product->title,
                    $imgIdx,
                    $item['cond']
                );

                ProductImage::create([
                    'product_id' => $product->id,
                    'image_path' => $imagePath,
                    'is_main' => ($imgIdx === 1),
                    'sort_order' => $imgIdx,
                ]);
            }

            $products[] = $product;
        }

        // 6. Reports (12 reports respecting chk_one_target CHECK constraint)
        $reportReasons = ['spam', 'scam', 'fake_item', 'other'];
        $reportDetails = [
            'بەرهەمی ساختەیە و وەک ئەسڵی دانراوە',
            'فرۆشیار لە پەیوەندیکردندا ڕەفتاری نەشیاوی نواندووە',
            'نرخی دانراو زۆر لە نرخی ڕاستەقینەی بازاڕ بەرزترە',
            'ژمارەی تەلەفۆن داخراوە و پەیوەندی پێوە ناکرێت',
            'ڕیکلامی هەڵخەڵەتێنەر لە تێکستەکەدا هەیە',
            'فرۆشگاکە دوو جار هەمان بەرهەمی داناوەتەوە',
        ];

        $reportStatuses = ['open', 'resolved', 'dismissed'];

        for ($r = 1; $r <= 12; $r++) {
            $reporter = $customers[$r % 15]; // one of regular customers
            $status = $reportStatuses[$r % 3];
            $reason = $reportReasons[$r % count($reportReasons)];
            $details = $reportDetails[$r % count($reportDetails)];

            $handledBy = $status !== 'open' ? $staffReviewers[$r % count($staffReviewers)] : null;
            $handledAt = $status !== 'open' ? Carbon::now()->subDays(rand(1, 10)) : null;

            // Half reporting products, half reporting customers (exactly one target!)
            if ($r % 2 === 1) {
                // Product report
                $targetProduct = $products[($r * 5) % count($products)];
                Report::create([
                    'reporter_id' => $reporter->id,
                    'reported_product_id' => $targetProduct->id,
                    'reported_customer_id' => null,
                    'reason' => $reason,
                    'details' => $details,
                    'status' => $status,
                    'handled_by' => $handledBy,
                    'handled_at' => $handledAt,
                    'created_at' => Carbon::now()->subDays(rand(1, 20)),
                ]);
            } else {
                // Customer report
                $targetCustomer = $shops[$r % count($shops)];
                Report::create([
                    'reporter_id' => $reporter->id,
                    'reported_product_id' => null,
                    'reported_customer_id' => $targetCustomer->id,
                    'reason' => $reason,
                    'details' => $details,
                    'status' => $status,
                    'handled_by' => $handledBy,
                    'handled_at' => $handledAt,
                    'created_at' => Carbon::now()->subDays(rand(1, 20)),
                ]);
            }
        }

        // 7. Visitor Statistics (30 days of realistic traffic, 40-300 unique devices/day)
        $this->command->info('Seeding visitor statistics for the last 30 days...');
        $visitsToInsert = [];
        $appKey = (string) config('app.key', 'marketplace_fallback_salt');

        for ($d = 29; $d >= 0; $d--) {
            $date = Carbon::today()->subDays($d);
            $dateStr = $date->toDateString();
            $isWeekend = in_array($date->dayOfWeek, [Carbon::FRIDAY, Carbon::SATURDAY]);
            $dailyVisitorCount = $isWeekend ? rand(150, 300) : rand(40, 180);

            for ($v = 1; $v <= $dailyVisitorCount; $v++) {
                $fakeIp = '192.168.'.rand(1, 100).'.'.rand(1, 254);
                $fakeUa = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) DemoBrowser/'.$v;
                $visitorHash = hash('sha256', $fakeIp.'|'.$fakeUa.'|'.$dateStr.'|'.$appKey);
                $pageViews = rand(1, 7);

                $createdAt = $date->copy()->addHours(rand(0, 23))->addMinutes(rand(0, 59));
                $visitsToInsert[] = [
                    'visitor_hash' => $visitorHash,
                    'visit_date' => $dateStr,
                    'page_views' => $pageViews,
                    'created_at' => $createdAt,
                    'updated_at' => $createdAt,
                ];

                if (count($visitsToInsert) >= 200) {
                    DB::table('visit')->insertOrIgnore($visitsToInsert);
                    $visitsToInsert = [];
                }
            }
        }

        if (! empty($visitsToInsert)) {
            DB::table('visit')->insertOrIgnore($visitsToInsert);
        }

        // 8. Output summary table
        $this->command->newLine();
        $this->command->info('Demo data seeded successfully!');
        $this->command->table(
            ['Table', 'Count'],
            [
                ['user', User::count()],
                ['category', Category::count()],
                ['customer', Customer::count()],
                ['product', Product::count()],
                ['product_image', ProductImage::count()],
                ['report', Report::count()],
                ['visit', DB::table('visit')->count()],
            ]
        );
    }

    /**
     * Generate a square shop logo with GD.
     *
     * @param  array{0: int, 1: int, 2: int}  $color
     */
    protected function generateLogoImage(string $fullPath, string $shopName, array $color): void
    {
        $size = 200;
        $image = imagecreatetruecolor($size, $size);
        if (! $image) {
            return;
        }

        $bgColor = imagecolorallocate($image, $color[0], $color[1], $color[2]);
        $white = imagecolorallocate($image, 255, 255, 255);
        $innerBg = imagecolorallocate($image, min(255, $color[0] + 30), min(255, $color[1] + 30), min(255, $color[2] + 30));

        imagefilledrectangle($image, 0, 0, $size, $size, $bgColor);
        imagefilledellipse($image, 100, 100, 160, 160, $innerBg);
        imageellipse($image, 100, 100, 170, 170, $white);

        $initial = strtoupper(mb_substr($shopName, 0, 1));
        imagestring($image, 5, 95, 90, $initial, $white);

        imagepng($image, $fullPath);
        imagedestroy($image);
    }

    /**
     * Generate a 4:3 product placeholder image with GD.
     */
    protected function generateProductImage(string $fullPath, string $title, int $sortOrder, string $condition): void
    {
        $width = 640;
        $height = 480;
        $image = imagecreatetruecolor($width, $height);
        if (! $image) {
            return;
        }

        // Modern background color palette based on condition & sort
        $palettes = [
            ['bg' => [241, 245, 249], 'accent' => [79, 70, 229], 'card' => [255, 255, 255]],
            ['bg' => [248, 250, 252], 'accent' => [16, 185, 129], 'card' => [255, 255, 255]],
            ['bg' => [243, 244, 246], 'accent' => [37, 99, 235], 'card' => [255, 255, 255]],
            ['bg' => [254, 243, 199], 'accent' => [217, 119, 6], 'card' => [255, 255, 255]],
        ];
        $palette = $palettes[($sortOrder - 1) % count($palettes)];

        $bgColor = imagecolorallocate($image, $palette['bg'][0], $palette['bg'][1], $palette['bg'][2]);
        $accentColor = imagecolorallocate($image, $palette['accent'][0], $palette['accent'][1], $palette['accent'][2]);
        $cardColor = imagecolorallocate($image, $palette['card'][0], $palette['card'][1], $palette['card'][2]);
        $grayText = imagecolorallocate($image, 100, 116, 139);
        $darkText = imagecolorallocate($image, 30, 41, 59);
        $white = imagecolorallocate($image, 255, 255, 255);

        // Fill background
        imagefilledrectangle($image, 0, 0, $width, $height, $bgColor);

        // Center card
        imagefilledrectangle($image, 40, 40, $width - 40, $height - 40, $cardColor);
        imagerectangle($image, 40, 40, $width - 40, $height - 40, $bgColor);

        // Top accent strip
        imagefilledrectangle($image, 40, 40, $width - 40, 50, $accentColor);

        // Circular device illustration placeholder
        imagefilledellipse($image, (int) ($width / 2), 200, 180, 180, $bgColor);
        imagefilledellipse($image, (int) ($width / 2), 200, 160, 160, $cardColor);
        imagefilledrectangle($image, (int) ($width / 2) - 40, 140, (int) ($width / 2) + 40, 260, $accentColor);
        imagefilledrectangle($image, (int) ($width / 2) - 34, 150, (int) ($width / 2) + 34, 250, $white);

        // Labels
        $photoLabel = "PHOTO #{$sortOrder}";
        imagestring($image, 5, (int) ($width / 2) - 35, 300, $photoLabel, $darkText);

        $condLabel = strtoupper($condition);
        imagestring($image, 4, (int) ($width / 2) - 20, 330, "({$condLabel})", $grayText);

        $marketplaceTag = 'KURDISTAN ELECTRONICS MARKETPLACE';
        imagestring($image, 2, (int) ($width / 2) - 110, 400, $marketplaceTag, $grayText);

        imagejpeg($image, $fullPath, 85);
        imagedestroy($image);
    }

    /**
     * Generate a 400x300 product thumbnail image with GD.
     */
    protected function generateProductThumbnail(string $fullPath, string $title, int $sortOrder, string $condition): void
    {
        $width = 400;
        $height = 300;
        $image = imagecreatetruecolor($width, $height);
        if (! $image) {
            return;
        }

        $bgColor = imagecolorallocate($image, 241, 245, 249);
        $accentColor = imagecolorallocate($image, 79, 70, 229);
        $cardColor = imagecolorallocate($image, 255, 255, 255);
        $darkText = imagecolorallocate($image, 30, 41, 59);

        imagefilledrectangle($image, 0, 0, $width, $height, $bgColor);
        imagefilledrectangle($image, 20, 20, $width - 20, $height - 20, $cardColor);
        imagefilledrectangle($image, 20, 20, $width - 20, 26, $accentColor);

        $label = "THUMB #{$sortOrder}";
        imagestring($image, 4, (int) ($width / 2) - 30, 130, $label, $darkText);

        imagejpeg($image, $fullPath, 82);
        imagedestroy($image);
    }
}
