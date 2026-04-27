# Methodology

## Objective

Build a practical decision-support system that helps identify where community support should be prioritised and what type of intervention should be delivered.

## Core concept: Community Need Index (CNI)

The CNI is a weighted composite score from 0 to 100.

Default indicators:

| Indicator | Direction | Meaning |
|---|---:|---|
| Poverty rate | Higher = more need | Financial vulnerability |
| Low income score | Lower income = more need | Household economic pressure |
| Youth share | Higher = more need | Youth-focused outreach or development potential |
| Education gap proxy | Higher = more need | Need for learning, guidance, and exposure |
| Population density proxy | Higher = more need | Higher service demand |

Default weights can be changed in the Streamlit sidebar.

## Recommendation logic

The recommendation engine uses transparent rules instead of black-box ML:

- High poverty + low income: food aid, zakat/CSR, livelihood support.
- Youth-heavy area: youth mentoring, volunteerism, digital dakwah.
- Education gap: basic Islamic education, tuition, family learning programmes.
- High CNI: integrated community intervention package.

## Map design

The map supports:

- zoom in/out
- drag/pan
- fullscreen mode
- minimap
- layer switching
- marker clustering
- measurement ruler
- draw tools
- choropleth layer when a valid district GeoJSON is available

## Data quality warning

For presentation, label demo values clearly as sample data. Use official DOSM/OpenDOSM data for actual planning.
