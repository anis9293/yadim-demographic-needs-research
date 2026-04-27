const map = L.map('map', {
    center: [4.2105, 101.9758],
    zoom: 6,
    zoomControl: true,
    fullscreenControl: true,
});

const baseLayers = {
    'OpenStreetMap': L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '&copy; OpenStreetMap contributors'
    }),
    'Topo Map': L.tileLayer('https://{s}.tile.opentopomap.org/{z}/{x}/{y}.png', {
        maxZoom: 17,
        attribution: '&copy; OpenTopoMap contributors'
    })
};

baseLayers['OpenStreetMap'].addTo(map);

const drawnItems = new L.FeatureGroup();
map.addLayer(drawnItems);

map.addControl(new L.Control.Draw({
    edit: { featureGroup: drawnItems },
    draw: {
        polygon: true,
        rectangle: true,
        circle: false,
        marker: false,
        circlemarker: false,
        polyline: true,
    }
}));

map.on(L.Draw.Event.CREATED, function (event) {
    drawnItems.addLayer(event.layer);
});

L.control.scale({ imperial: false }).addTo(map);

let allFeatures = [];
let clusterLayer = L.markerClusterGroup();
let geoJsonLayer = L.geoJSON(null);

const overlays = {
    'District markers': clusterLayer,
    'District shapes/points': geoJsonLayer,
    'Drawn selection': drawnItems,
};

L.control.layers(baseLayers, overlays, { collapsed: false }).addTo(map);
clusterLayer.addTo(map);
geoJsonLayer.addTo(map);

function colorByPriority(priority) {
    return {
        Critical: '#8b1e1e',
        High: '#b65b12',
        Medium: '#8a7a10',
        Low: '#287044'
    }[priority] || '#287044';
}

function markerFor(feature) {
    const coords = feature.geometry.type === 'Point'
        ? [feature.geometry.coordinates[1], feature.geometry.coordinates[0]]
        : L.geoJSON(feature).getBounds().getCenter();

    const p = feature.properties;
    const marker = L.circleMarker(coords, {
        radius: Math.max(8, Math.min(24, p.cni_score / 4)),
        fillColor: colorByPriority(p.priority_level),
        color: '#ffffff',
        weight: 2,
        opacity: 1,
        fillOpacity: 0.85
    });

    marker.bindPopup(popupHtml(p));
    marker.on('click', () => selectDistrict(p));
    return marker;
}

function popupHtml(p) {
    return `
        <div class="popup-title">${p.district}, ${p.state}</div>
        <div>CNI: <span class="popup-score">${p.cni_score}</span></div>
        <div>Priority: <strong>${p.priority_level}</strong></div>
        <hr>
        <strong>Recommended actions</strong>
        <ul>${(p.recommended_actions || []).map(a => `<li>${a}</li>`).join('')}</ul>
    `;
}

function selectDistrict(p) {
    document.getElementById('selectedInfo').innerHTML = `
        <strong>${p.district}, ${p.state}</strong><br>
        <span class="badge ${p.priority_level}">${p.priority_level}</span>
        <p><strong>CNI Score:</strong> ${p.cni_score}</p>
        <p><strong>Score breakdown:</strong><br>
        Poverty ${p.poverty_score} · Education ${p.education_score} · Youth ${p.youth_risk_score} · Religious access ${p.religious_access_score}</p>
        <strong>Recommended actions</strong>
        <ul class="action-list">${(p.recommended_actions || []).map(a => `<li>${a}</li>`).join('')}</ul>
    `;
}

function applyFilters() {
    const state = document.getElementById('stateFilter').value;
    const priority = document.getElementById('priorityFilter').value;

    const filtered = allFeatures.filter(f => {
        const p = f.properties;
        return (!state || p.state === state) && (!priority || p.priority_level === priority);
    });

    clusterLayer.clearLayers();
    geoJsonLayer.clearLayers();

    filtered.forEach(feature => clusterLayer.addLayer(markerFor(feature)));

    geoJsonLayer.addData(filtered.map(feature => ({ ...feature })));
    geoJsonLayer.setStyle(feature => ({
        color: colorByPriority(feature.properties.priority_level),
        weight: 2,
        fillOpacity: 0.18,
    }));

    document.getElementById('districtCount').textContent = filtered.length;
    document.getElementById('criticalCount').textContent = filtered.filter(f => f.properties.priority_level === 'Critical').length;

    if (filtered.length) {
        const bounds = clusterLayer.getBounds();
        if (bounds.isValid()) map.fitBounds(bounds.pad(0.2));
    }
}

fetch(window.YADIM_GEOJSON_URL)
    .then(res => res.json())
    .then(data => {
        allFeatures = data.features || [];
        applyFilters();
    })
    .catch(error => {
        console.error(error);
        document.getElementById('selectedInfo').innerHTML = '<strong>Map data failed to load.</strong><p>Check route, database seed, and browser console.</p>';
    });

document.getElementById('stateFilter').addEventListener('change', applyFilters);
document.getElementById('priorityFilter').addEventListener('change', applyFilters);
