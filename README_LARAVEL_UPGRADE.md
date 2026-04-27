# YADIM Laravel Map Dashboard Upgrade

This package converts the demographic needs prototype into a Laravel + Leaflet implementation.

## What it adds

- Interactive map dashboard using Leaflet.js
- Zoom, drag, layer control, fullscreen, scale control
- Marker clustering
- Draw/select area tool
- District filters
- Community Need Index scoring
- CSR/dakwah recommendation engine
- Demo database seed data

## Install into your local project

Your local path:

```bash
C:\xampp\htdocs\yadim-demographic-needs-research
```

Copy the files in this package into that Laravel project.

Then run:

```bash
cd C:\xampp\htdocs\yadim-demographic-needs-research
composer install
copy .env.example .env
php artisan key:generate
```

Set your `.env` database:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=yadim_demographic
DB_USERNAME=root
DB_PASSWORD=
```

Create the database in phpMyAdmin or MySQL:

```sql
CREATE DATABASE yadim_demographic;
```

Run migrations and demo seeder:

```bash
php artisan migrate
php artisan db:seed --class=DemoDistrictSeeder
```

Run locally:

```bash
php artisan serve
```

Open:

```text
http://127.0.0.1:8000
```

## Important integration note

If your existing `routes/web.php` already has content, do not blindly replace it. Add these lines instead:

```php
use App\Http\Controllers\DashboardController;

Route::get('/', [DashboardController::class, 'index'])->name('dashboard.index');
Route::get('/api/community-need-geojson', [DashboardController::class, 'geojson'])->name('dashboard.geojson');
```

If your `DatabaseSeeder.php` already exists, add this inside `run()`:

```php
$this->call(DemoDistrictSeeder::class);
```

## Production deployment

This Laravel version is suitable for shared hosting, VPS, or cloud hosting as long as PHP, Composer, MySQL, and HTTPS are available.

Recommended production stack:

```text
Laravel
MySQL/MariaDB
Nginx or Apache
HTTPS/SSL
Queue/scheduler for imports later
```
