# Malaysia District GeoJSON Boundary Import

The dashboard API already returns GeoJSON from `districts.geometry_geojson`.

Recommended flow:

1. Get district boundary GeoJSON from an approved/open source.
2. Match each boundary using state + district name.
3. Save each matched boundary into `districts.geometry_geojson` as JSON.
4. Reload `/api/community-need-geojson`.

Until real boundaries are imported, `DashboardController` generates small fallback polygons around each district coordinate so CNI distribution is visible on the map.
