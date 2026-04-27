const map = L.map('map', {
    center: [4.2105, 101.9758],
    zoom: 6,
    zoomControl: true,
    fullscreenControl: true,
    scrollWheelZoom: 'center',
    wheelPxPerZoomLevel: 90,
    markerZoomAnimation: false,
    preferCanvas: true,
});
map.scrollWheelZoom.disable();

const baseLayers = {
    'Light Map': L.tileLayer('https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png', {
        maxZoom: 20,
        attribution: '&copy; OpenStreetMap contributors &copy; CARTO'
    }),
    'Street Map': L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '&copy; OpenStreetMap contributors'
    })
};

baseLayers['Light Map'].addTo(map);

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
let markerLayer = L.markerClusterGroup({
    disableClusteringAtZoom: 10,
    spiderfyOnMaxZoom: true,
    showCoverageOnHover: false,
});
let cniLayer = L.geoJSON(null);

const overlays = {
    'CNI distribution': cniLayer,
    'Drawn selection': drawnItems,
};

L.control.layers(baseLayers, overlays, { collapsed: false }).addTo(map);
cniLayer.addTo(map);
markerLayer.addTo(map);

function colorByScore(score) {
    if (score >= 70) return '#8b1e1e';
    if (score >= 50) return '#d97706';
    if (score >= 30) return '#facc15';
    return '#22c55e';
}

function colorByPriority(priority, score) {
    return {
        Critical: '#8b1e1e',
        High: '#d97706',
        Medium: '#facc15',
        Low: '#22c55e'
    }[priority] || colorByScore(score || 0);
}

function styleFeature(feature) {
    const p = feature.properties;
    return {
        fillColor: colorByPriority(p.priority_level, p.cni_score),
        weight: 1.5,
        opacity: 1,
        color: '#ffffff',
        dashArray: '2',
        fillOpacity: 0.72
    };
}

function popupHtml(p) {
    const actions = (p.recommended_actions || []).map(action => `<li>${action}</li>`).join('');
    return `
        <div class="popup-title">${p.district}, ${p.state}</div>
        <div>CNI: <span class="popup-score">${Number(p.cni_score).toFixed(1)}</span></div>
        <div>Priority: <span class="badge ${p.priority_level}">${p.priority_level}</span></div>
        <hr>
        <strong>Score breakdown</strong>
        <div>Poverty: ${Number(p.poverty_score).toFixed(1)}</div>
        <div>Education: ${Number(p.education_score).toFixed(1)}</div>
        <div>Youth risk: ${Number(p.youth_risk_score).toFixed(1)}</div>
        <div>Religious access gap: ${Number(p.religious_access_score).toFixed(1)}</div>
        <hr>
        <strong>Recommended actions</strong>
        <ul>${actions || '<li>Monitor and maintain community engagement.</li>'}</ul>
    `;
}

function selectedHtml(p) {
    const actions = (p.recommended_actions || []).map(action => `<li>${action}</li>`).join('');
    return `
        <strong>${p.district}, ${p.state}</strong>
        <p>CNI <b>${Number(p.cni_score).toFixed(1)}</b> · <span class="badge ${p.priority_level}">${p.priority_level}</span></p>
        <div class="score-bars">
            ${bar('Poverty', p.poverty_score)}
            ${bar('Education', p.education_score)}
            ${bar('Youth risk', p.youth_risk_score)}
            ${bar('Religious gap', p.religious_access_score)}
        </div>
        <strong>Recommended actions</strong>
        <ul class="action-list">${actions || '<li>Monitor and maintain community engagement.</li>'}</ul>
    `;
}

function bar(label, value) {
    const v = Math.max(0, Math.min(100, Number(value || 0)));
    return `
        <div class="score-row">
            <span>${label}</span><b>${v.toFixed(0)}</b>
            <div class="score-track"><div class="score-fill" style="width:${v}%"></div></div>
        </div>
    `;
}

function getFeatureCenter(feature) {
    return L.geoJSON(feature).getBounds().getCenter();
}

function markerFor(feature) {
    const coords = feature.geometry.type === 'Point'
        ? [feature.geometry.coordinates[1], feature.geometry.coordinates[0]]
        : getFeatureCenter(feature);

    const p = feature.properties;
    const marker = L.circleMarker(coords, {
        radius: Math.max(8, Math.min(24, p.cni_score / 4)),
        fillColor: colorByPriority(p.priority_level, p.cni_score),
        color: '#ffffff',
        weight: 2,
        opacity: 1,
        fillOpacity: 0.92
    });

    marker.bindPopup(popupHtml(p));
    marker.on('click', () => {
        document.getElementById('selectedInfo').innerHTML = selectedHtml(p);
    });

    return marker;
}

function render(features) {
    const state = document.getElementById('stateFilter').value;
    const priority = document.getElementById('priorityFilter').value;

    const filtered = features.filter(feature => {
        const p = feature.properties;
        return (!state || p.state === state) && (!priority || p.priority_level === priority);
    });

    cniLayer.clearLayers();
    markerLayer.clearLayers();

    cniLayer = L.geoJSON(filtered, {
        style: styleFeature,
        onEachFeature: (feature, layer) => {
            const p = feature.properties;
            layer.bindPopup(popupHtml(p));
            layer.on({
                mouseover: (e) => {
                    e.target.setStyle({ weight: 3, color: '#123524', fillOpacity: 0.88 });
                    e.target.bringToFront();
                },
                mouseout: (e) => cniLayer.resetStyle(e.target),
                click: () => {
                    document.getElementById('selectedInfo').innerHTML = selectedHtml(p);
                }
            });
        }
    });

    filtered.forEach(feature => markerLayer.addLayer(markerFor(feature)));

    cniLayer.addTo(map);
    markerLayer.addTo(map);

    document.getElementById('districtCount').textContent = filtered.length;
    document.getElementById('criticalCount').textContent = filtered.filter(f => f.properties.priority_level === 'Critical').length;

    if (filtered.length > 0) {
        const bounds = L.geoJSON(filtered).getBounds();
        if (bounds.isValid()) map.fitBounds(bounds.pad(0.18));
    }
}

fetch(window.YADIM_GEOJSON_URL)
    .then(response => response.json())
    .then(data => {
        allFeatures = data.features || [];
        render(allFeatures);
    })
    .catch(error => {
        console.error(error);
        document.getElementById('selectedInfo').innerHTML = '<strong>Map data failed to load.</strong><p>Check the Laravel API route /api/community-need-geojson.</p>';
    });

document.getElementById('stateFilter').addEventListener('change', () => render(allFeatures));
document.getElementById('priorityFilter').addEventListener('change', () => render(allFeatures));

const zoomHint = L.control({ position: 'topleft' });
zoomHint.onAdd = function () {
    const div = L.DomUtil.create('div', 'map-zoom-hint');
    div.innerHTML = 'Hold Ctrl + scroll to zoom';
    L.DomEvent.disableClickPropagation(div);
    L.DomEvent.disableScrollPropagation(div);
    return div;
};
zoomHint.addTo(map);
