# Data Dictionary

## app-ready district table

Required columns:

| Column | Type | Description |
|---|---|---|
| state | string | Malaysian state |
| district | string | Administrative district |
| latitude | float | District centroid latitude |
| longitude | float | District centroid longitude |
| population | float | Population count or estimate |
| poverty_rate | float | Poverty rate, percent |
| median_income | float | Median monthly household income in RM |
| youth_share | float | Approximate youth share, percent |
| education_gap | float | Proxy score, 0-100 |

Generated columns:

| Column | Description |
|---|---|
| cni_score | Community Need Index score, 0-100 |
| priority | Low / Medium / High / Critical |
| recommended_actions | Suggested CSR/community/dakwah actions |
