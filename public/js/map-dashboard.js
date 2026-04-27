(function () {
    const rows = (window.districtRows || []).map(r => ({
        ...r,
        cni_score: Number(r.cni_score || 0),
        poverty_rate: Number(r.poverty_rate || 0),
        income_median: Number(r.income_median || 0),
        youth_share: Number(r.youth_share || 0),
        gini: Number(r.gini || 0),
    }));

    const map = L.map('map', {
        zoomControl: true,
        scrollWheelZoom: 'center',
        wheelPxPerZoomLevel: 90,
        zoomAnimation: true,
        markerZoomAnimation: false,
        fadeAnimation: true,
        preferCanvas: true
    }).setView([4.2105, 101.9758], 6);

    map.scrollWheelZoom.disable();

    const light = L.tileLayer('https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png', {
        maxZoom: 20,
        updateWhenZooming: false,
        attribution: '&copy; OpenStreetMap contributors &copy; CARTO'
    }).addTo(map);

    const street = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        updateWhenZooming: false,
        attribution: '&copy; OpenStreetMap contributors'
    });

    const mapContainer = map.getContainer();

    mapContainer.addEventListener('wheel', (event) => {
        if (!event.ctrlKey) {
            map.scrollWheelZoom.disable();
            return;
        }

        event.preventDefault();
        event.stopPropagation();

        if (!map.scrollWheelZoom.enabled()) {
            map.scrollWheelZoom.enable();
        }
    }, { passive: false });

    mapContainer.addEventListener('mouseleave', () => {
        map.scrollWheelZoom.disable();
    });

    const byKey = new Map();
    rows.forEach(r => {
        byKey.set(norm(r.name), r);
        byKey.set(norm(`${r.state} ${r.name}`), r);
    });

    function norm(v) {
        return String(v || '').toLowerCase().replace(/[^a-z0-9]/g, '');
    }

    function getColor(cni) {
        return cni >= 70 ? '#d73027' :
               cni >= 55 ? '#fc8d59' :
               cni >= 40 ? '#fee08b' :
               cni >= 25 ? '#d9ef8b' : '#1a9850';
    }

    function popupHtml(r) {
        return `<div style="min-width:220px">
            <strong>${r.name || 'Unknown District'}</strong><br>
            <span>${r.state || ''}</span><hr class="my-2">
            <b>CNI:</b> ${Number(r.cni_score || 0).toFixed(1)}<br>
            <b>Poverty:</b> ${Number(r.poverty_rate || 0).toFixed(1)}%<br>
            <b>Median income:</b> RM ${Number(r.income_median || 0).toLocaleString()}<br>
            <b>Youth share:</b> ${Number(r.youth_share || 0).toFixed(1)}%<br>
            <b>Gini:</b> ${Number(r.gini || 0).toFixed(3)}<br>
            <small>Method: ${r.method_version || 'opendosm_realdata_v1'}</small>
        </div>`;
    }

    const markerLayer = L.markerClusterGroup({
        disableClusteringAtZoom: 8,
        maxClusterRadius: 38,
        spiderfyOnMaxZoom: true,
        showCoverageOnHover: false,
        animate: false,
        animateAddingMarkers: false
    });

    function featureNameCandidates(feature) {
        const props = feature.properties || {};
        return [props.name, props.Name, props.DISTRICT, props.district, props.DAERAH, props.shapeName, props.NAME_2].filter(Boolean);
    }

    function featureCenter(feature) {
        const geometry = feature.geometry || {};
        const polygons = geometry.type === 'Polygon'
            ? [geometry.coordinates]
            : geometry.type === 'MultiPolygon'
                ? geometry.coordinates
                : [];

        let best = null;
        polygons.forEach(polygon => {
            const ring = polygon[0] || [];
            const center = ringCentroid(ring);
            if (!center) {
                return;
            }
            if (!best || Math.abs(center.area) > Math.abs(best.area)) {
                best = center;
            }
        });

        if (best) {
            return L.latLng(best.lat, best.lng);
        }

        const bounds = L.geoJSON(feature).getBounds();
        return bounds.isValid() ? bounds.getCenter() : null;
    }

    function ringCentroid(ring) {
        if (ring.length === 0) {
            return null;
        }

        let area = 0;
        let cx = 0;
        let cy = 0;

        for (let i = 0; i < ring.length - 1; i++) {
            const [x1, y1] = ring[i];
            const [x2, y2] = ring[i + 1];
            const cross = (x1 * y2) - (x2 * y1);
            area += cross;
            cx += (x1 + x2) * cross;
            cy += (y1 + y2) * cross;
        }

        area /= 2;
        if (Math.abs(area) < 0.0000001) {
            const mean = ring.reduce((acc, [lng, lat]) => {
                acc.lng += lng;
                acc.lat += lat;
                return acc;
            }, { lat: 0, lng: 0 });

            return {
                lat: mean.lat / ring.length,
                lng: mean.lng / ring.length,
                area: 0
            };
        }

        return {
            lat: cy / (6 * area),
            lng: cx / (6 * area),
            area
        };
    }

    function rowCoordinate(row, geoByKey) {
        const feature = geoByKey.get(norm(`${row.state} ${row.name}`)) || geoByKey.get(norm(row.name));
        if (feature) {
            return featureCenter(feature);
        }

        const lat = Number(row.latitude);
        const lng = Number(row.longitude);
        if (Number.isFinite(lat) && Number.isFinite(lng)) {
            return L.latLng(lat, lng);
        }

        return null;
    }

    function renderMarkers(geoByKey = new Map()) {
        markerLayer.clearLayers();

        rows.forEach(r => {
            const point = rowCoordinate(r, geoByKey);
            if (!point) {
                return;
            }

            const marker = L.circleMarker(point, {
                radius: 7,
                fillColor: getColor(r.cni_score),
                color: '#243b2f',
                weight: 1,
                fillOpacity: 0.85
            }).bindPopup(popupHtml(r));
            markerLayer.addLayer(marker);
        });
    }

    renderMarkers();
    markerLayer.addTo(map);

    let choroplethLayer = null;
    fetch('/data/malaysia.district.geojson')
        .then(res => res.ok ? res.json() : Promise.reject(new Error('GeoJSON not found')))
        .then(geo => {
            const geoByKey = new Map();
            (geo.features || []).forEach(feature => {
                featureNameCandidates(feature).forEach(name => {
                    geoByKey.set(norm(name), feature);
                    const row = byKey.get(norm(name));
                    if (row) {
                        geoByKey.set(norm(`${row.state} ${row.name}`), feature);
                    }
                });
            });

            renderMarkers(geoByKey);

            choroplethLayer = L.geoJSON(geo, {
                style: feature => {
                    const candidates = featureNameCandidates(feature);
                    const match = candidates.map(norm).map(k => byKey.get(k)).find(Boolean);
                    const cni = match ? match.cni_score : 0;
                    return {
                        fillColor: match ? getColor(cni) : '#d9d9d9',
                        color: '#d6d6d6',
                        weight: 0.8,
                        fillOpacity: match ? 0.72 : 0.15
                    };
                },
                onEachFeature: (feature, layer) => {
                    const candidates = featureNameCandidates(feature);
                    const match = candidates.map(norm).map(k => byKey.get(k)).find(Boolean);
                    layer.bindPopup(match ? popupHtml(match) : `<b>${candidates.find(Boolean) || 'District'}</b><br>No matched CNI record yet.`);
                    layer.on('mouseover', () => layer.setStyle({ color: '#111827', weight: 2, fillOpacity: 0.9 }));
                    layer.on('mouseout', () => choroplethLayer.resetStyle(layer));
                }
            }).addTo(map);
            map.fitBounds(choroplethLayer.getBounds(), { padding: [20, 20] });
        })
        .catch(() => {
            console.warn('No district GeoJSON found at public/data/malaysia.district.geojson. Showing only records with stored coordinates.');
        });

    L.control.layers({ 'Light Map': light, 'Street Map': street }, null, { collapsed: false }).addTo(map);

    const legend = L.control({ position: 'bottomright' });
    legend.onAdd = function () {
        const div = L.DomUtil.create('div', 'legend');
        div.innerHTML = `
            <strong>CNI Score</strong><br>
            <i style="background:#d73027"></i> 70–100 Very High<br>
            <i style="background:#fc8d59"></i> 55–69 High<br>
            <i style="background:#fee08b"></i> 40–54 Medium<br>
            <i style="background:#d9ef8b"></i> 25–39 Low<br>
            <i style="background:#1a9850"></i> 0–24 Very Low
        `;
        return div;
    };
    legend.addTo(map);

    const zoomHint = L.control({ position: 'topleft' });
    zoomHint.onAdd = function () {
        const div = L.DomUtil.create('div', 'map-zoom-hint');
        div.innerHTML = 'Hold Ctrl + scroll to zoom';
        L.DomEvent.disableClickPropagation(div);
        L.DomEvent.disableScrollPropagation(div);
        return div;
    };
    zoomHint.addTo(map);
})();
