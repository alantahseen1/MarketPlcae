# Kurdistan Electronics Marketplace (بازاڕی ئەلیکترۆنیی کوردستان)

A modern, fast, mobile-first electronics classifieds marketplace tailored for the Kurdistan Region and Iraq. The platform allows individuals and professional electronic shops to list mobile phones, laptops, desktop computers, accessories, and audio hardware, while buyers discover deals and contact sellers directly by phone call.

The application is built entirely in **Kurdish (Sorani - `ckb`)** with a native Right-to-Left (**RTL**) layout, optimized typography using **Noto Sans Arabic**, and pricing in **Iraqi Dinar (IQD)**.

---

## Architecture & Tech Stack

- **Framework**: [Laravel 11](https://laravel.com) on **PHP 8.3+**
- **Database**: **MySQL 8.0+** with strict foreign keys, CHECK constraints (`chk_one_target`, `chk_condition`, `chk_status`), and composite indexes for query performance.
- **Frontend**: Laravel Blade + [Tailwind CSS](https://tailwindcss.com) + Vite
- **Typography**: Kurdish Sorani (`ckb`), RTL direction, Noto Sans Arabic font stack
- **Authentication**: Dual independent guards (`customer` for buyers/sellers and `staff` for review and moderation)
- **Image Pipeline**: Native PHP GD library for automated upload processing:
  - Downscaled to max 1600px longest dimension, converted to JPEG quality 82 (with white background fill for transparent PNG/WebP).
  - 400px thumbnail generation (`thumb_{filename}`) for lightning-fast catalog browsing.
- **Privacy-Friendly Visitor Analytics**:
  - Zero third-party trackers or cookies.
  - Anonymized SHA-256 daily salted hashes (`sha256(IP + UA + Date + APP_KEY)`). Raw IP addresses are **never** persisted.
  - Automated retention pruning (`visits:prune` older than 400 days).

---

## Core System Features

### 1. Customer & Shop Features
- **Registration & Authentication**: Separate customer accounts with Iraqi mobile number validation (`07XXXXXXXXX`).
- **Regular vs. Shop Accounts**:
  - Regular customers have personal profiles.
  - Shop accounts feature custom branding (shop name, dedicated logo, shop storefront page, and an admin-granted **Verified Shop** badge).
- **Product Listing Management**:
  - Multi-photo upload (1 to 5 images, max 4MB each, drag-and-drop support).
  - Formatted IQD pricing (e.g. `250,000 د.ع`).
  - Condition selection (`نوێ` / New or `بەکارهاتوو` / Used).
  - Edit listings (approved listings transition back to `pending` review upon edit).
  - Mark items as `sold` or soft-delete as `removed` (images and database records are preserved for audit).
- **Profile & Security**:
  - Update full name, phone number (instant update across all active listings), address, and shop logo.
  - Secure password changes with current password confirmation.
- **Abuse Reporting**: Logged-in customers can report suspicious listings or fraudulent sellers with categorized reasons and details.

### 2. Public Browsing & SEO
- **Catalog Search & Filtering**: Instant search across titles and descriptions, category navigation, condition filters, and price ranges.
- **Dedicated Shop Pages**: Public showcase of verified shops and their active inventory.
- **Social Sharing & Meta**: Kurdish Open Graph meta tags (`og:title`, `og:description`, `og:image`, `og:url`), responsive favicon, and dynamic `/sitemap.xml`.
- **Search Engine Directives**: Production-ready `/robots.txt` blocking private staff and user account paths.
- **Error Pages**: Custom Kurdish, RTL error views for `404`, `403`, `419`, `429`, and `500` status codes.

### 3. Staff & Administrative Panel (`/staff`)
- **Role-Based Access Control**:
  - **Subadmin**: Product review queue (approve listings or reject with mandatory Kurdish explanation).
  - **Admin**: Full authority including staff user management, abuse report resolution/dismissal, customer ban/unban with cascade product hiding, shop verification toggle, category management, and visitor analytics.
- **Visitor Statistics Dashboard**:
  - Admin-only metric overview: Today's visitors, today's page views, 7-day visitors, 30-day visitors, and total unique visitor-days (cached for 5 minutes).
  - 14-day interactive pure HTML/CSS bar chart displaying daily unique device trends.

---

## Getting Started

### Prerequisites
- PHP >= 8.3 with extensions: `pdo_mysql`, `gd`, `mbstring`, `bcmath`, `fileinfo`, `curl`
- Composer 2.x
- Node.js 18+ & NPM
- MySQL 8.0+

### Installation Steps

1. **Clone the repository**:
   ```bash
   git clone <repository_url> marketplace
   cd marketplace
   ```

2. **Install PHP and Node dependencies**:
   ```bash
   composer install
   npm install
   ```

3. **Configure Environment**:
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```
   Edit `.env` and set your MySQL database connection:
   ```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=marketplace
   DB_USERNAME=root
   DB_PASSWORD=
   ```

4. **Create Storage Symlink**:
   ```bash
   php artisan storage:link
   ```

5. **Run Migrations & Seed Baseline Data**:
   ```bash
   php artisan migrate:fresh --seed
   ```
   *Seeds the default admin (`admin@example.com` / `password123`) and official categories.*

6. **Seed Realistic Demo Data (Optional for Development)**:
   ```bash
   php artisan db:seed --class=DemoDataSeeder
   ```
   *Populates 25 customers, 8 shops, ~80 products with GD-generated product images and thumbnails, reports, subadmins, and 30 days of traffic analytics.*

7. **Compile Frontend Assets**:
   ```bash
   npm run build
   # or for live local development:
   npm run dev
   ```

8. **Start Local Development Server**:
   ```bash
   php artisan serve
   ```
   Open [http://127.0.0.1:8000](http://127.0.0.1:8000) in your browser.

---

## Default Credentials

### Staff Panel (`/staff/login`)
| Role | Email | Password |
| :--- | :--- | :--- |
| **Admin** | `admin@example.com` | `password123` |
| **Subadmin** | `subadmin1@example.com` | `password123` |
| **Subadmin** | `subadmin2@example.com` | `password123` |

### Demo Customers (`/login`)
| Customer Type | Email | Password | Details |
| :--- | :--- | :--- | :--- |
| **Verified Shop** | `faststore@example.com` | `password123` | فاست ستۆر (Hewler) |
| **Shop** | `slemani_phone@example.com` | `password123` | کۆشکی مۆبایل (Slemani) |
| **Regular** | `alan@example.com` | `password123` | ئالان عوسمان |

---

## Scheduled Tasks & Console Commands

- **Prune Old Visitor Records**:
  ```bash
  php artisan visits:prune --days=400
  ```
  *(Registered to run daily via Laravel's task scheduler).*

- **Run Scheduler Locally**:
  ```bash
  php artisan schedule:work
  ```

---

## Testing & Code Quality

The application has comprehensive automated test suites covering customer auth, listings, staff reviews, moderation, categories, profile changes, SEO, GD image generation, rate limiters, security headers, and visitor analytics.

Run the test suite:
```bash
php artisan test
```

Run Laravel Pint code formatter:
```bash
vendor/bin/pint --format agent
```

---

## License

This software is developed and maintained under the [MIT License](LICENSE).
