"""Rule-based intervention recommender for CSR and dakwah planning."""

from __future__ import annotations

import pandas as pd


def recommend_interventions(row: pd.Series) -> str:
    recommendations: list[str] = []

    if row.get("poverty_score", 0) >= 70:
        recommendations.append("Food aid / basic necessities / zakat-CSR coordination")

    if row.get("education_gap_score", 0) >= 65:
        recommendations.append("Education support, tuition, and basic Islamic learning modules")

    if row.get("youth_risk_score", 0) >= 65:
        recommendations.append("Youth engagement, career exposure, mentoring, and digital dakwah")

    if row.get("health_social_proxy_score", 0) >= 65:
        recommendations.append("Community wellbeing support and spiritual counselling referral")

    if row.get("access_gap_score", 0) >= 65:
        recommendations.append("Mobile outreach unit / on-ground follow-up programme")

    if not recommendations:
        recommendations.append("General community engagement and periodic monitoring")

    return "; ".join(recommendations)


def attach_recommendations(df: pd.DataFrame) -> pd.DataFrame:
    result = df.copy()
    result["recommended_action"] = result.apply(recommend_interventions, axis=1)
    return result
