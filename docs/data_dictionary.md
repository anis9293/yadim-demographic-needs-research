# Data Dictionary

Expected processed file:

```text
data/processed/community_needs_processed.csv
```

Required columns:

| Column | Type | Description |
|---|---|---|
| state | string | Malaysian state |
| district | string | District/locality name |
| latitude | float | Map latitude centroid |
| longitude | float | Map longitude centroid |
| population | integer | Estimated population |
| poverty_score | number 0-100 | Higher means higher poverty vulnerability |
| education_gap_score | number 0-100 | Higher means lower education attainment/access |
| youth_risk_score | number 0-100 | Higher means higher youth vulnerability proxy |
| health_social_proxy_score | number 0-100 | Higher means higher wellbeing/social concern proxy |
| access_gap_score | number 0-100 | Higher means weaker access to services or outreach |

Optional columns:

| Column | Description |
|---|---|
| muslim_population_share | Useful for dakwah/resource planning |
| muallaf_count | Useful if official/authorized data is available |
| unemployment_rate | Can support youth risk calculation |
| median_income | Can support poverty score calculation |
| internet_access_rate | Can support digital outreach planning |
