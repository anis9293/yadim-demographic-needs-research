# Methodology

## 1. Study Objective

To identify community needs using available statistics and convert them into actionable recommendations for religious outreach, charity, and CSR planning.

## 2. Unit of Analysis

Recommended levels:

1. State level for early prototype
2. District level for operational planning
3. Mukim/locality level if boundary and reliable data are available

## 3. Community Need Index

The Community Need Index (CNI) combines multiple indicators into one decision score.

Default weights:

| Indicator | Weight | Rationale |
|---|---:|---|
| Poverty score | 35% | Direct indicator of aid/CSR urgency |
| Education gap score | 25% | Proxy for learning and religious exposure need |
| Youth risk score | 15% | Supports youth dakwah, mentoring, and prevention programmes |
| Health/social proxy score | 15% | Captures wellbeing and social vulnerability |
| Access gap score | 10% | Identifies areas needing mobile or physical outreach |

## 4. Priority Classification

| CNI Score | Classification |
|---:|---|
| 70–100 | High Priority |
| 45–69.99 | Medium Priority |
| 0–44.99 | Low Priority |

## 5. Recommended Action Logic

The recommendation engine uses transparent rules rather than black-box AI. This is suitable for early policy/CSR planning because it is easy to explain to management and stakeholders.

Example:

- High poverty + low education → food aid, tuition, basic Islamic learning modules
- High youth risk → youth engagement, mentoring, career exposure, digital dakwah
- High access gap → mobile outreach unit or periodic ground follow-up

## 6. Data Ethics

Avoid labeling communities negatively. Use the scores to prioritize support, not to stigmatize people or areas.

## 7. Limitations

- Demo data is not official and must be replaced with verified statistics.
- Mental health may require proxy indicators if direct data is unavailable.
- District-level data may hide smaller vulnerable pockets.
