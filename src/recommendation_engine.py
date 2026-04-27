from __future__ import annotations

import pandas as pd


def recommend_actions(row: pd.Series) -> str:
    actions: list[str] = []

    poverty = float(row.get("poverty_rate", 0) or 0)
    income = float(row.get("median_income", 0) or 0)
    youth = float(row.get("youth_share", 0) or 0)
    education_gap = float(row.get("education_gap", 0) or 0)
    cni = float(row.get("cni_score", 0) or 0)

    if cni >= 75:
        actions.append("Integrated CSR+dakwah mission")
    if poverty >= 10 or income <= 4000:
        actions.append("Food aid, zakat/CSR assistance, livelihood support")
    if education_gap >= 55:
        actions.append("Basic Islamic education, family learning circle, tuition support")
    if youth >= 33:
        actions.append("Youth mentoring, volunteerism, digital dakwah programme")
    if 55 <= cni < 75:
        actions.append("Targeted community engagement and follow-up visits")
    if not actions:
        actions.append("Maintain engagement, periodic monitoring, light community programme")

    return "; ".join(actions)


def add_recommendations(df: pd.DataFrame) -> pd.DataFrame:
    data = df.copy()
    data["recommended_actions"] = data.apply(recommend_actions, axis=1)
    return data
