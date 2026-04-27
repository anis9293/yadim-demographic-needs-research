<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>YADIM Community Needs Intelligence</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" crossorigin="">
    <link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.5.3/dist/MarkerCluster.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.5.3/dist/MarkerCluster.Default.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet.fullscreen@2.4.0/Control.FullScreen.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet.draw/1.0.4/leaflet.draw.css">
    <link rel="stylesheet" href="{{ asset('css/yadim-dashboard.css') }}">
</head>
<body>
<div class="layout">
    <aside class="sidebar">
        <div class="brand">
            <img src="{{ asset('images/yadim-logo-jata.png') }}" alt="Yayasan Dakwah Islamiah Malaysia (YADIM)" class="brand-logo">
        </div>

        <h1 class="project-title">YADIM Demographic Needs Research</h1>
        <p class="project-motto">Data-guided dakwah, CSR, and community support for every district.</p>

        <section class="cni-note" aria-labelledby="cni-note-title">
            <h2 id="cni-note-title">How CNI is calculated</h2>
            <p class="cni-mini-formula">CNI = 35% Poverty + 25% Education + 20% Youth Risk + 20% Religious Access Gap</p>
            <ul>
                <li>Poverty reflects income pressure and basic-needs vulnerability.</li>
                <li>Education gap highlights learning-support need.</li>
                <li>Youth risk captures youth unemployment and vulnerability proxy.</li>
                <li>Religious access gap reflects programme coverage needs.</li>
            </ul>
        </section>

        <label for="stateFilter">State filter</label>
        <select id="stateFilter">
            <option value="">All states</option>
            @foreach($states as $state)
                <option value="{{ $state }}">{{ $state }}</option>
            @endforeach
        </select>

        <label for="priorityFilter">Priority filter</label>
        <select id="priorityFilter">
            <option value="">All priorities</option>
            <option value="Critical">Critical</option>
            <option value="High">High</option>
            <option value="Medium">Medium</option>
            <option value="Low">Low</option>
        </select>

        <div class="stats">
            <div><span id="districtCount">0</span><small>Districts</small></div>
            <div><span id="criticalCount">0</span><small>Critical</small></div>
        </div>

        <div class="legend-card">
            <strong>CNI distribution</strong>
            <div><i class="legend-swatch critical"></i> 70–100 Critical</div>
            <div><i class="legend-swatch high"></i> 50–69 High</div>
            <div><i class="legend-swatch medium"></i> 30–49 Medium</div>
            <div><i class="legend-swatch low"></i> 0–29 Low</div>
        </div>

        <div id="selectedInfo" class="selected-card">
            <strong>Select a district</strong>
            <p>Click a coloured CNI zone or marker on the map to view recommended CSR/dakwah actions.</p>
        </div>
    </aside>

    <main>
        <div id="map"></div>
    </main>
</div>

<div class="modal fade" id="cniInfoModal" tabindex="-1" aria-labelledby="cniInfoModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="cniInfoModalLabel">How Community Need Index (CNI) is calculated</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p><strong>CNI</strong> ranks each district by intervention priority for CSR, dakwah, education support and community support.</p>
                <div class="formula-box">CNI = 0.35(Poverty) + 0.25(Education Gap) + 0.20(Youth Risk) + 0.20(Religious Access Gap)</div>
                <div class="row g-3 mt-2">
                    <div class="col-md-6"><div class="method-card"><strong>35% Poverty</strong><br>Income pressure, poverty rate and basic-needs vulnerability.</div></div>
                    <div class="col-md-6"><div class="method-card"><strong>25% Education Gap</strong><br>Low education exposure, dropout proxy and learning-support need.</div></div>
                    <div class="col-md-6"><div class="method-card"><strong>20% Youth Risk</strong><br>Youth unemployment and social vulnerability proxy.</div></div>
                    <div class="col-md-6"><div class="method-card"><strong>20% Religious Access Gap</strong><br>Low dakwah/programme coverage, muallaf follow-up gap or distance to support.</div></div>
                </div>
                <hr>
                <p class="mb-1"><strong>Interpretation:</strong></p>
                <ul class="mb-0">
                    <li><strong>70–100:</strong> Critical priority intervention</li>
                    <li><strong>50–69:</strong> High priority targeted programme</li>
                    <li><strong>30–49:</strong> Medium priority monitoring and selective programme</li>
                    <li><strong>0–29:</strong> Low priority monitoring</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" crossorigin=""></script>
<script src="https://unpkg.com/leaflet.markercluster@1.5.3/dist/leaflet.markercluster.js"></script>
<script src="https://unpkg.com/leaflet.fullscreen@2.4.0/Control.FullScreen.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet.draw/1.0.4/leaflet.draw.js"></script>
<script>
    window.YADIM_GEOJSON_URL = "{{ route('dashboard.geojson') }}";
</script>
<script src="{{ asset('js/yadim-map-dashboard.js') }}"></script>
</body>
</html>
