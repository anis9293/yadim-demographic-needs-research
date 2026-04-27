"""Demo dataset for initial dashboard testing.

Replace this with processed DOSM data when available.
"""

from __future__ import annotations

import pandas as pd


def load_demo_data() -> pd.DataFrame:
    data = [
        {
            "state": "Kelantan",
            "district": "Tumpat",
            "latitude": 6.1978,
            "longitude": 102.1710,
            "population": 161000,
            "poverty_score": 82,
            "education_gap_score": 70,
            "youth_risk_score": 58,
            "health_social_proxy_score": 63,
            "access_gap_score": 72,
        },
        {
            "state": "Sabah",
            "district": "Kota Belud",
            "latitude": 6.3510,
            "longitude": 116.4305,
            "population": 105000,
            "poverty_score": 88,
            "education_gap_score": 75,
            "youth_risk_score": 70,
            "health_social_proxy_score": 68,
            "access_gap_score": 81,
        },
        {
            "state": "Selangor",
            "district": "Petaling",
            "latitude": 3.0833,
            "longitude": 101.6500,
            "population": 2300000,
            "poverty_score": 38,
            "education_gap_score": 28,
            "youth_risk_score": 49,
            "health_social_proxy_score": 45,
            "access_gap_score": 22,
        },
        {
            "state": "Perak",
            "district": "Hulu Perak",
            "latitude": 5.5147,
            "longitude": 101.0411,
            "population": 92000,
            "poverty_score": 69,
            "education_gap_score": 66,
            "youth_risk_score": 55,
            "health_social_proxy_score": 59,
            "access_gap_score": 78,
        },
        {
            "state": "Johor",
            "district": "Johor Bahru",
            "latitude": 1.4927,
            "longitude": 103.7414,
            "population": 1700000,
            "poverty_score": 41,
            "education_gap_score": 35,
            "youth_risk_score": 52,
            "health_social_proxy_score": 44,
            "access_gap_score": 29,
        },
        {
            "state": "Pahang",
            "district": "Jerantut",
            "latitude": 3.9360,
            "longitude": 102.3626,
            "population": 91000,
            "poverty_score": 73,
            "education_gap_score": 64,
            "youth_risk_score": 61,
            "health_social_proxy_score": 62,
            "access_gap_score": 75,
        },
    ]
    return pd.DataFrame(data)
