# DOSM / OpenDOSM Import Plan

Use `https://open.dosm.gov.my/data-catalogue` as the official source catalogue.

Recommended first datasets:

1. Population by district
2. Household income / poverty by district
3. Education indicators by district or state
4. Labour force / youth unemployment indicators
5. Supporting proxy data for religious access gap, such as distance to service points, programme coverage, or internal YADIM outreach records

Suggested import flow:

1. Download CSV from OpenDOSM.
2. Normalize district names and state names.
3. Insert/update `districts`.
4. Insert/update `demographic_stats`.
5. Run:

```bash
php artisan yadim:recalculate-cni
```

For real deployment, replace `religious_access_gap_rate` demo values with an internal YADIM-derived indicator such as:

```text
religious_access_gap = 100 - programme_coverage_score
```

or

```text
religious_access_gap = weighted score of distance to outreach centre, number of programmes, muallaf follow-up coverage, and community requests
```
