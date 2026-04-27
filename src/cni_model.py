"""Community Need Index (CNI) scoring model.

The model expects normalized input columns between 0 and 100.
Higher score means higher community need.
"""

from __future__ import annotations

from dataclasses import dataclass
from typing import Dict

import pandas as pd


DEFAULT_WEIGHTS: Dict[str, float] = {
    "poverty_score": 0.35,
    "education_gap_score": 0.25,
    "youth_risk_score": 0.15,
    "health_social_proxy_score": 0.15,
    "access_gap_score": 0.10,
}


@dataclass(frozen=True)
class CNIConfig:
    weights: Dict[str, float]

    @staticmethod
    def default() -> "CNIConfig":
        return CNIConfig(weights=DEFAULT_WEIGHTS.copy())


def validate_weights(weights: Dict[str, float]) -> None:
    total = round(sum(weights.values()), 6)
    if total != 1.0:
        raise ValueError(f"CNI weights must sum to 1.0. Current total: {total}")


def calculate_cni(df: pd.DataFrame, config: CNIConfig | None = None) -> pd.DataFrame:
    """Calculate CNI score and priority class.

    Required columns are the keys from DEFAULT_WEIGHTS.
    """
    config = config or CNIConfig.default()
    validate_weights(config.weights)

    missing = [col for col in config.weights if col not in df.columns]
    if missing:
        raise ValueError(f"Missing CNI columns: {missing}")

    result = df.copy()
    result["cni_score"] = 0.0

    for column, weight in config.weights.items():
        result["cni_score"] += result[column].fillna(0).clip(0, 100) * weight

    result["cni_score"] = result["cni_score"].round(2)
    result["priority"] = result["cni_score"].apply(classify_priority)
    return result.sort_values("cni_score", ascending=False).reset_index(drop=True)


def classify_priority(score: float) -> str:
    if score >= 70:
        return "High Priority"
    if score >= 45:
        return "Medium Priority"
    return "Low Priority"
