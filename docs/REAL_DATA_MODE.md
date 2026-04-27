# Real Data Mode

This upgrade removes dependence on demo seed data for the dashboard metrics.

## Data sources imported

1. `https://storage.dosm.gov.my/population/population_district.csv`
   - District population, sex, age group and ethnicity.
   - The importer uses the latest available year, `sex=both`, `ethnicity=overall`.
   - Youth share is calculated from age groups 15-19, 20-24 and 25-29.

2. `https://storage.dosm.gov.my/hies/hies_district.csv`
   - District household income, expenditure, poverty and Gini coefficient.
   - The importer uses the latest available HIES district year.

## Run

```bat
cd C:\xampp\htdocs\yadim-demographic-needs-research
php artisan optimize:clear
php artisan migrate
php artisan yadim:import-dosm
php artisan serve
```

Open:

```text
http://127.0.0.1:8000/dashboard
```

## Optional state-only import

```bat
php artisan yadim:import-dosm --state=Selangor
```

## CNI method

```text
CNI = 0.40(Poverty Score)
    + 0.25(Income Gap Score)
    + 0.20(Youth Risk Score)
    + 0.15(Inequality Score)
```

All components are normalized to 0-100 across imported districts.

## Important note on religious access data

OpenDOSM does not provide YADIM programme coverage, mosque class coverage or muallaf follow-up coverage as public district indicators. This upgrade does **not** invent that data. Religious/program access should be added later from YADIM internal datasets.

## District boundaries

The real statistical data works immediately. For true polygon choropleth boundaries, place a Malaysia district GeoJSON file here:

```text
public/data/malaysia.district.geojson
```

Recommended public boundary sources include geoBoundaries/Humanitarian Data Exchange or an appropriate official Malaysian GIS source. Ensure the district name field matches DOSM district names as closely as possible.
