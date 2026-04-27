from __future__ import annotations

import pandas as pd
import numpy as np


def minmax(series: pd.Series, invert: bool = False) -> pd.Series:
    s = pd.to_numeric(series, errors="coerce").astype(float)
    if s.max(skipna=True) == s.min(skipna=True):
        out = pd.Series(50.0, index=s.index)
    else:
        out = (s - s.min(skipna=True)) / (s.max(skipna=True) - s.min(skipna=True)) * 100
    if invert:
        out = 100 - out
    return out.fillna(out.median())


def compute_cni(
    df: pd.DataFrame,
    poverty_weight: float = 0.35,
    income_weight: float = 0.25,
    youth_weight: float = 0.15,
    education_weight: float = 0.20,
    population_weight: float = 0.05,
) -> pd.DataFrame:
    """Compute a transparent Community Need Index from 0-100."""
    data = df.copy()

    data["poverty_score"] = minmax(data.get("poverty_rate", 0))
    data["income_pressure_score"] = minmax(data.get("median_income", 0), invert=True)
    data["youth_score"] = minmax(data.get("youth_share", 0))
    data["education_score"] = minmax(data.get("education_gap", 0))
    data["population_score"] = minmax(data.get("population", 0))

    total_weight = poverty_weight + income_weight + youth_weight + education_weight + population_weight
    if total_weight <= 0:
        total_weight = 1

    data["cni_score"] = (
        poverty_weight * data["poverty_score"]
        + income_weight * data["income_pressure_score"]
        + youth_weight * data["youth_score"]
        + education_weight * data["education_score"]
        + population_weight * data["population_score"]
    ) / total_weight

    data["cni_score"] = data["cni_score"].round(1)
    data["priority"] = pd.cut(
        data["cni_score"],
        bins=[-1, 35, 55, 75, 101],
        labels=["Low", "Medium", "High", "Critical"],
    ).astype(str)
    return data
