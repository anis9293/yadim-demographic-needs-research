from __future__ import annotations

from pathlib import Path
import pandas as pd

ROOT = Path(__file__).resolve().parents[1]
DEMO_DATA = ROOT / "data" / "processed" / "demo_district_needs.csv"
RAW_DIR = ROOT / "data" / "raw"
DISTRICT_GEOJSON = ROOT / "data" / "geojson" / "malaysia-districts.geojson"
FALLBACK_LATITUDE = 4.2105
FALLBACK_LONGITUDE = 101.9758


def load_demo_data() -> pd.DataFrame:
    return pd.read_csv(DEMO_DATA)


def load_real_or_demo() -> tuple[pd.DataFrame, str]:
    """Load processed real data if available; otherwise demo data."""
    processed = ROOT / "data" / "processed" / "district_needs_real.csv"
    if processed.exists():
        df = apply_district_centroids(pd.read_csv(processed))
        return df, "real processed data"
    return load_demo_data(), "demo data"


def apply_district_centroids(df: pd.DataFrame) -> pd.DataFrame:
    """Fill district coordinates from local district polygons when available."""
    if not DISTRICT_GEOJSON.exists() or "district" not in df.columns:
        return df

    try:
        import geopandas as gpd
    except ImportError:
        return df

    districts = gpd.read_file(DISTRICT_GEOJSON)[["name", "geometry"]].copy()
    projected = districts.to_crs(epsg=3857)
    points = projected.representative_point().to_crs(epsg=4326)

    centroids = pd.DataFrame({
        "district_key": districts["name"].map(normalize_name),
        "district_latitude": points.y,
        "district_longitude": points.x,
    })

    out = df.copy()
    out["district_key"] = out["district"].map(normalize_name)
    out = out.merge(centroids, on="district_key", how="left")

    fallback_coord = (
        out["latitude"].round(4).eq(FALLBACK_LATITUDE)
        & out["longitude"].round(4).eq(FALLBACK_LONGITUDE)
    )
    missing_coord = out["latitude"].isna() | out["longitude"].isna()
    should_replace = (fallback_coord | missing_coord) & out["district_latitude"].notna()

    out.loc[should_replace, "latitude"] = out.loc[should_replace, "district_latitude"]
    out.loc[should_replace, "longitude"] = out.loc[should_replace, "district_longitude"]

    return out.drop(columns=["district_key", "district_latitude", "district_longitude"])


def normalize_name(value: object) -> str:
    return str(value or "").strip().lower()


def prepare_from_dosm_raw() -> Path:
    """Create a basic app-ready CSV from downloaded DOSM raw files.

    This uses HIES/pov data when available and merges with demo centroids.
    Replace centroids with official district boundary centroids for production.
    """
    demo = load_demo_data()[["state", "district", "latitude", "longitude", "youth_share", "education_gap"]]

    hies_path = RAW_DIR / "hies_district.parquet"
    poverty_path = RAW_DIR / "hh_poverty_district.parquet"
    population_path = RAW_DIR / "population_district.parquet"

    if hies_path.exists():
        hies = pd.read_parquet(hies_path)
    elif (RAW_DIR / "hies_district.csv").exists():
        hies = pd.read_csv(RAW_DIR / "hies_district.csv")
    else:
        raise FileNotFoundError("Missing hies_district file. Run src/fetch_dosm.py first.")

    latest_hies = hies.copy()
    latest_hies["date"] = pd.to_datetime(latest_hies["date"], errors="coerce")
    latest_hies = latest_hies.sort_values("date").groupby(["state", "district"], as_index=False).tail(1)
    latest_hies = latest_hies.rename(columns={"poverty": "poverty_rate", "income_median": "median_income"})

    base = latest_hies[["state", "district", "poverty_rate", "median_income"]].copy()

    if population_path.exists():
        pop = pd.read_parquet(population_path)
    elif (RAW_DIR / "population_district.csv").exists():
        pop = pd.read_csv(RAW_DIR / "population_district.csv")
    else:
        pop = None

    if pop is not None and "population" in pop.columns:
        p = pop.copy()
        p["date"] = pd.to_datetime(p["date"], errors="coerce")
        for col, val in {"sex":"both", "age":"overall", "ethnicity":"overall"}.items():
            if col in p.columns:
                p = p[p[col].astype(str).str.lower().eq(val)]
        p = p.sort_values("date").groupby(["state", "district"], as_index=False).tail(1)
        p["population"] = pd.to_numeric(p["population"], errors="coerce") * 1000
        base = base.merge(p[["state", "district", "population"]], on=["state", "district"], how="left")
    else:
        base["population"] = pd.NA

    out = demo.merge(base, on=["state", "district"], how="right")
    out = apply_district_centroids(out)
    out["latitude"] = out["latitude"].fillna(FALLBACK_LATITUDE)
    out["longitude"] = out["longitude"].fillna(FALLBACK_LONGITUDE)
    out["youth_share"] = out["youth_share"].fillna(30)
    out["education_gap"] = out["education_gap"].fillna(50)
    out["population"] = out["population"].fillna(out["population"].median())

    output = ROOT / "data" / "processed" / "district_needs_real.csv"
    out.to_csv(output, index=False)
    return output
