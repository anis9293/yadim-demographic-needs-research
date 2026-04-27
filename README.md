# YADIM Demographic Needs Intelligence System

A map-first prototype for identifying community needs and recommending targeted CSR/dakwah interventions using Malaysia demographic and household indicators.

## What this app does

- Loads demo data immediately so the app runs out of the box.
- Supports real DOSM/OpenDOSM datasets for population, household income, and poverty.
- Computes a Community Need Index (CNI) per district.
- Shows an interactive map with zoom, pan, fullscreen, layer control, minimap, ruler, draw tools, and marker clustering.
- Recommends suitable CSR/community/dakwah interventions based on local indicators.

## Quick start on Windows

```bash
cd C:\xampp\htdocs\yadim-demographic-needs-research
pip install -r requirements.txt
streamlit run app/streamlit_app.py
```

Open the Local URL shown by Streamlit, usually http://localhost:8501.

## Recommended workflow

1. Start with demo data to validate the dashboard.
2. Add a Malaysia district GeoJSON into `data/geojson/`.
3. Run the DOSM downloader to fetch real CSV/parquet data.
4. Adjust CNI weights in the sidebar.
5. Replace or improve recommendation rules in `src/recommendation_engine.py`.

## Fetch DOSM data

```bash
python src/fetch_dosm.py
```

This attempts to download:
- population_district
- hies_district
- hh_poverty_district

Downloaded files are saved in `data/raw/`. The app will still work with demo data if downloads fail.

## Folder structure

```text
app/                    Streamlit app
src/                    data, scoring, recommendation modules
data/raw/               downloaded original datasets
data/processed/         cleaned/merged data
data/geojson/           district boundary GeoJSON files
docs/                   method notes and data dictionary
outputs/maps/           exported map/report outputs
```

## Notes

The app is intentionally simple and explainable. It is designed as a policy/CSR decision-support prototype, not a final official statistics product.
