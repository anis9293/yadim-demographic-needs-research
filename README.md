# YADIM Demographic Needs Research

A data-driven foundation for mapping community needs and recommending targeted dakwah, charity, and CSR interventions in Malaysia.

## Purpose

This project converts demographic and socio-economic statistics into a visual decision-support app. It helps identify **where support is most needed**, **what type of support is suitable**, and **how NGOs/CSR teams can prioritize outreach**.

Core focus areas:

- Poverty and household vulnerability
- Education and religious exposure gap
- Youth/community risk indicators
- Access to support services
- CSR, charity, dakwah, and muallaf-support planning

## Key Output

The main output is a **Community Need Index (CNI)**:

```text
CNI = weighted score combining poverty, education gap, youth vulnerability, health/social proxy, and service access gap
```

Each district/community is classified as:

- High Priority
- Medium Priority
- Low Priority

The app then recommends suitable interventions such as food aid, education support, spiritual counselling, youth programmes, muallaf follow-up, or community harmony initiatives.

## Suggested Data Source

Primary statistical source: DOSM Open Data Catalogue  
<https://open.dosm.gov.my/data-catalogue>

Suggested dataset categories:

1. Population by district/state
2. Household income and poverty
3. Education attainment
4. Labour force and youth unemployment
5. Health, wellbeing, or social indicators
6. ICT/internet access
7. Administrative boundary files for mapping

## Project Structure

```text
.
├── app/
│   └── streamlit_app.py
├── data/
│   ├── raw/
│   └── processed/
├── docs/
│   ├── data_dictionary.md
│   └── methodology.md
├── notebooks/
│   └── 01_demo_cni_workflow.ipynb
├── outputs/
│   ├── maps/
│   └── reports/
├── src/
│   ├── cni_model.py
│   ├── data_loader.py
│   ├── demo_data.py
│   └── recommendation_engine.py
├── requirements.txt
└── run_app.sh
```

## Quick Start

```bash
python -m venv .venv
source .venv/bin/activate   # Windows: .venv\Scripts\activate
pip install -r requirements.txt
streamlit run app/streamlit_app.py
```

## Current Status

This version includes a working demo dataset so the dashboard can run immediately. Replace the demo data with cleaned DOSM datasets once available.

## App Features

- District-level CNI ranking
- Priority classification
- Interactive map
- Recommended intervention package
- CSV download for planning/reporting

## Roadmap

- Import real DOSM datasets
- Add district/mukim boundary GeoJSON
- Add state and district filters
- Add YADIM programme alignment tags
- Add reporting export in PDF/PowerPoint format
