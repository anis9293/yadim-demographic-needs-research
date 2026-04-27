"""Data loading helpers."""

from __future__ import annotations

from pathlib import Path

import pandas as pd

from src.demo_data import load_demo_data


REQUIRED_COLUMNS = {
    "state",
    "district",
    "latitude",
    "longitude",
    "population",
    "poverty_score",
    "education_gap_score",
    "youth_risk_score",
    "health_social_proxy_score",
    "access_gap_score",
}


def load_processed_data(path: str | Path | None = None) -> pd.DataFrame:
    """Load processed data or fallback to demo data."""
    if path is None:
        default_path = Path("data/processed/community_needs_processed.csv")
    else:
        default_path = Path(path)

    if default_path.exists():
        df = pd.read_csv(default_path)
        validate_schema(df)
        return df

    return load_demo_data()


def validate_schema(df: pd.DataFrame) -> None:
    missing = REQUIRED_COLUMNS - set(df.columns)
    if missing:
        raise ValueError(f"Processed dataset missing required columns: {sorted(missing)}")
