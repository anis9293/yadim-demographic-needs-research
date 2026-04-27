# YADIM Laravel Real Data Upgrade

Apply by extracting this archive over the project root.

```bat
cd C:\xampp\htdocs\yadim-demographic-needs-research
tar -xzf C:\path\to\yadim_laravel_realdata_upgrade.tar.gz
php artisan optimize:clear
php artisan migrate
php artisan yadim:import-dosm
php artisan serve
```

Open `http://127.0.0.1:8000/dashboard`.

This upgrade imports OpenDOSM district population and HIES district income/poverty data, calculates CNI, and displays the result in the dashboard.
