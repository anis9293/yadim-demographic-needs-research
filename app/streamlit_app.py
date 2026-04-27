from __future__ import annotations

import sys
from pathlib import Path

import folium
from folium.plugins import (
    Draw,
    Fullscreen,
    LocateControl,
    MarkerCluster,
    MiniMap,
    MeasureControl,
    MousePosition,
)
import pandas as pd
import plotly.express as px
import streamlit as st
from streamlit_folium import st_folium

ROOT = Path(__file__).resolve().parents[1]
sys.path.append(str(ROOT / "src"))

from data_loader import load_real_or_demo, prepare_from_dosm_raw  # noqa: E402
from need_index_model import compute_cni  # noqa: E402
from recommendation_engine import add_recommendations  # noqa: E402

st.set_page_config(page_title="YADIM Needs Map", page_icon="🕌", layout="wide")

st.title("🕌 YADIM Demographic Needs Intelligence Map")
st.caption("Interactive decision-support prototype for targeted CSR, dakwah, education and community support.")

with st.sidebar:
    st.header("Data")
    if st.button("Prepare real DOSM data from data/raw"):
        try:
            out = prepare_from_dosm_raw()
            st.success(f"Prepared: {out.name}")
        except Exception as exc:
            st.error(f"Could not prepare real data: {exc}")

    df, data_source = load_real_or_demo()
    st.info(f"Using: {data_source}")

    st.header("CNI weights")
    poverty_w = st.slider("Poverty", 0.0, 1.0, 0.35, 0.05)
    income_w = st.slider("Low income pressure", 0.0, 1.0, 0.25, 0.05)
    youth_w = st.slider("Youth share", 0.0, 1.0, 0.15, 0.05)
    education_w = st.slider("Education gap", 0.0, 1.0, 0.20, 0.05)
    population_w = st.slider("Population demand", 0.0, 1.0, 0.05, 0.05)

    st.header("Map controls")
    basemap = st.selectbox(
        "Base map",
        ["Light map", "OpenStreetMap", "Dark map", "Terrain"],
        index=0,
    )
    show_cluster = st.checkbox("Cluster markers", value=True)
    show_minimap = st.checkbox("Mini map", value=True)
    show_draw = st.checkbox("Draw tools", value=True)
    show_measure = st.checkbox("Measure ruler", value=True)
    selected_state = st.multiselect("Filter state", sorted(df["state"].dropna().unique()))

scored = compute_cni(
    df,
    poverty_weight=poverty_w,
    income_weight=income_w,
    youth_weight=youth_w,
    education_weight=education_w,
    population_weight=population_w,
)
scored = add_recommendations(scored)

if selected_state:
    scored = scored[scored["state"].isin(selected_state)]

priority_order = ["Critical", "High", "Medium", "Low"]
priority_filter = st.multiselect("Priority filter", priority_order, default=priority_order)
if priority_filter:
    scored = scored[scored["priority"].isin(priority_filter)]

if scored.empty:
    st.warning("No records match the filters.")
    st.stop()

k1, k2, k3, k4 = st.columns(4)
k1.metric("Districts", f"{len(scored):,}")
k2.metric("Average CNI", f"{scored['cni_score'].mean():.1f}")
k3.metric("Critical/High areas", f"{scored['priority'].isin(['Critical','High']).sum():,}")
k4.metric("Avg poverty rate", f"{scored['poverty_rate'].mean():.1f}%")

# Map center
center_lat = float(scored["latitude"].mean())
center_lon = float(scored["longitude"].mean())

m = folium.Map(
    location=[center_lat, center_lon],
    zoom_start=6,
    tiles=None,
    control_scale=True,
    prefer_canvas=True,
)

# Basemap layers
folium.TileLayer("CartoDB positron", name="Light map", control=True, show=basemap == "Light map").add_to(m)
folium.TileLayer("OpenStreetMap", name="OpenStreetMap", control=True, show=basemap == "OpenStreetMap").add_to(m)
folium.TileLayer("CartoDB dark_matter", name="Dark map", control=True, show=basemap == "Dark map").add_to(m)
try:
    folium.TileLayer("Stamen Terrain", name="Terrain", control=True, show=basemap == "Terrain").add_to(m)
except Exception:
    pass

priority_colors = {
    "Critical": "red",
    "High": "orange",
    "Medium": "blue",
    "Low": "green",
}

marker_parent = MarkerCluster(name="District clusters").add_to(m) if show_cluster else folium.FeatureGroup(name="District markers").add_to(m)

for _, row in scored.iterrows():
    color = priority_colors.get(row["priority"], "gray")
    popup_html = f"""
    <div style='width:280px'>
        <h4>{row['district']}, {row['state']}</h4>
        <b>CNI:</b> {row['cni_score']}<br>
        <b>Priority:</b> {row['priority']}<br>
        <b>Population:</b> {row['population']:,.0f}<br>
        <b>Poverty:</b> {row['poverty_rate']:.1f}%<br>
        <b>Median income:</b> RM {row['median_income']:,.0f}<br>
        <b>Youth share:</b> {row['youth_share']:.1f}%<br>
        <hr>
        <b>Recommended action:</b><br>{row['recommended_actions']}
    </div>
    """
    folium.CircleMarker(
        location=[row["latitude"], row["longitude"]],
        radius=max(7, min(24, row["cni_score"] / 4)),
        popup=folium.Popup(popup_html, max_width=320),
        tooltip=f"{row['district']} | CNI {row['cni_score']} | {row['priority']}",
        color=color,
        fill=True,
        fill_color=color,
        fill_opacity=0.65,
        weight=2,
    ).add_to(marker_parent)

# Optional choropleth if a user adds GeoJSON with matching district names
geojson_files = list((ROOT / "data" / "geojson").glob("*.geojson")) + list((ROOT / "data" / "geojson").glob("*.json"))
if geojson_files:
    geo_path = geojson_files[0]
    try:
        choropleth_data = scored[["district", "cni_score"]].copy()
        folium.Choropleth(
            geo_data=str(geo_path),
            name="CNI choropleth",
            data=choropleth_data,
            columns=["district", "cni_score"],
            key_on="feature.properties.district",
            fill_opacity=0.55,
            line_opacity=0.3,
            legend_name="Community Need Index",
        ).add_to(m)
    except Exception as exc:
        st.warning(f"GeoJSON found but choropleth could not be created: {exc}")

Fullscreen(position="topleft").add_to(m)
LocateControl(auto_start=False).add_to(m)
MousePosition(position="bottomright", separator=" | ", prefix="Lat/Lon:").add_to(m)
if show_minimap:
    MiniMap(toggle_display=True, minimized=False).add_to(m)
if show_measure:
    MeasureControl(position="topleft", primary_length_unit="kilometers").add_to(m)
if show_draw:
    Draw(export=True, filename="selected_area.geojson", position="topleft").add_to(m)
folium.LayerControl(collapsed=False).add_to(m)

st.subheader("Interactive needs map")
map_event = st_folium(m, height=680, use_container_width=True, returned_objects=["last_object_clicked", "last_active_drawing"])

clicked = map_event.get("last_object_clicked") if map_event else None
if clicked:
    with st.expander("Last clicked coordinates"):
        st.json(clicked)

st.subheader("Top priority areas")
top = scored.sort_values("cni_score", ascending=False).head(10)
st.dataframe(
    top[["state", "district", "cni_score", "priority", "recommended_actions"]],
    hide_index=True,
    use_container_width=True,
)

st.subheader("CNI ranking chart")
fig = px.bar(
    scored.sort_values("cni_score", ascending=True),
    x="cni_score",
    y="district",
    color="priority",
    orientation="h",
    hover_data=["state", "poverty_rate", "median_income", "recommended_actions"],
    title="Community Need Index by district",
)
st.plotly_chart(fig, use_container_width=True)

st.subheader("Download app-ready data")
st.download_button(
    "Download scored CSV",
    data=scored.to_csv(index=False).encode("utf-8"),
    file_name="yadim_scored_district_needs.csv",
    mime="text/csv",
)

with st.expander("Raw scored table"):
    st.dataframe(scored, hide_index=True, use_container_width=True)
