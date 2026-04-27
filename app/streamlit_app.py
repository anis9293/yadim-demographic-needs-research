from __future__ import annotations

import sys
from pathlib import Path

import pandas as pd
import plotly.express as px
import streamlit as st

ROOT = Path(__file__).resolve().parents[1]
sys.path.append(str(ROOT))

from src.cni_model import calculate_cni
from src.data_loader import load_processed_data
from src.recommendation_engine import attach_recommendations


st.set_page_config(
    page_title="YADIM Community Need Intelligence",
    page_icon="🕌",
    layout="wide",
)

st.title("YADIM Community Need Intelligence Dashboard")
st.caption("Prototype dashboard for visualizing demographic needs and CSR/dakwah intervention priorities.")

with st.sidebar:
    st.header("Controls")
    st.write("This prototype uses demo data unless `data/processed/community_needs_processed.csv` exists.")
    priority_filter = st.multiselect(
        "Priority level",
        ["High Priority", "Medium Priority", "Low Priority"],
        default=["High Priority", "Medium Priority", "Low Priority"],
    )

raw_df = load_processed_data()
df = attach_recommendations(calculate_cni(raw_df))
filtered = df[df["priority"].isin(priority_filter)]

col1, col2, col3, col4 = st.columns(4)
col1.metric("Communities", len(filtered))
col2.metric("Average CNI", f"{filtered['cni_score'].mean():.1f}" if not filtered.empty else "0")
col3.metric("High Priority", int((filtered["priority"] == "High Priority").sum()))
col4.metric("Population Covered", f"{filtered['population'].sum():,}")

st.subheader("Map Distribution")

if filtered.empty:
    st.warning("No data available for selected filters.")
else:
    fig_map = px.scatter_mapbox(
        filtered,
        lat="latitude",
        lon="longitude",
        color="priority",
        size="cni_score",
        hover_name="district",
        hover_data={
            "state": True,
            "population": ":,",
            "cni_score": True,
            "poverty_score": True,
            "education_gap_score": True,
            "latitude": False,
            "longitude": False,
        },
        zoom=4.6,
        height=520,
        mapbox_style="open-street-map",
    )
    fig_map.update_layout(margin={"r": 0, "t": 0, "l": 0, "b": 0})
    st.plotly_chart(fig_map, use_container_width=True)

left, right = st.columns([1, 1])

with left:
    st.subheader("CNI Ranking")
    ranking_cols = [
        "state",
        "district",
        "population",
        "cni_score",
        "priority",
    ]
    st.dataframe(filtered[ranking_cols], use_container_width=True, hide_index=True)

with right:
    st.subheader("Recommended Actions")
    action_cols = ["district", "priority", "recommended_action"]
    st.dataframe(filtered[action_cols], use_container_width=True, hide_index=True)

st.subheader("Indicator Breakdown")
selected_district = st.selectbox("Select district", filtered["district"].tolist() if not filtered.empty else [])

if selected_district:
    row = filtered[filtered["district"] == selected_district].iloc[0]
    breakdown = pd.DataFrame(
        {
            "indicator": [
                "Poverty",
                "Education Gap",
                "Youth Risk",
                "Health/Social Proxy",
                "Access Gap",
            ],
            "score": [
                row["poverty_score"],
                row["education_gap_score"],
                row["youth_risk_score"],
                row["health_social_proxy_score"],
                row["access_gap_score"],
            ],
        }
    )
    fig_bar = px.bar(breakdown, x="indicator", y="score", range_y=[0, 100])
    st.plotly_chart(fig_bar, use_container_width=True)

csv = filtered.to_csv(index=False).encode("utf-8")
st.download_button(
    "Download filtered planning data as CSV",
    data=csv,
    file_name="yadim_community_need_index.csv",
    mime="text/csv",
)
