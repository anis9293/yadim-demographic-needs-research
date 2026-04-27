from __future__ import annotations

from pathlib import Path
import requests

ROOT = Path(__file__).resolve().parents[1]
RAW_DIR = ROOT / "data" / "raw"
RAW_DIR.mkdir(parents=True, exist_ok=True)

DATASETS = {
    "population_district.parquet": "https://storage.dosm.gov.my/population/population_district.parquet",
    "hies_district.parquet": "https://storage.dosm.gov.my/hies/hies_district.parquet",
    "hh_poverty_district.parquet": "https://storage.dosm.gov.my/hies/hh_poverty_district.parquet",
}


def download(url: str, path: Path) -> None:
    print(f"Downloading {url}")
    r = requests.get(url, timeout=60)
    r.raise_for_status()
    path.write_bytes(r.content)
    print(f"Saved {path}")


if __name__ == "__main__":
    for filename, url in DATASETS.items():
        try:
            download(url, RAW_DIR / filename)
        except Exception as exc:
            print(f"Failed {filename}: {exc}")

    try:
        from data_loader import prepare_from_dosm_raw
        output = prepare_from_dosm_raw()
        print(f"Prepared app-ready file: {output}")
    except Exception as exc:
        print(f"Could not prepare processed data yet: {exc}")
