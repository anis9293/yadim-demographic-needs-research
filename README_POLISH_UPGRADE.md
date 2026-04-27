# YADIM Laravel Polish Upgrade

This overlay upgrades the Laravel dashboard with:

- CNI info button and Bootstrap modal explaining the calculation method
- Mosque favicon (`public/favicon.svg`)
- Sidebar brand logo asset (`public/images/yadim-logo.svg`)
- CNI distribution choropleth layer on the Leaflet map
- CNI legend and score breakdown bars
- Improved popup and selected-district panel
- Fallback demo polygons so the map shows area distribution even before real district boundaries are imported

## Install over current project

Extract/copy this package into:

```bat
C:\xampp\htdocs\yadim-demographic-needs-research
```

Then run:

```bat
cd C:\xampp\htdocs\yadim-demographic-needs-research
php artisan optimize:clear
php artisan migrate
php artisan db:seed --class=DemoDistrictSeeder
php artisan serve
```

Open:

```text
http://127.0.0.1:8000
```

## Notes on the logo

`public/images/yadim-logo.svg` is a clean placeholder wordmark for the dashboard. Replace it with the official YADIM logo file if your team has the approved asset.

## Real boundary upgrade

For production, replace fallback square polygons by importing real Malaysia district GeoJSON into the `districts.geometry_geojson` column. The map already supports polygon geometry.
