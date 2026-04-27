<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>YADIM Community Needs Intelligence</title>

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
        <div class="brand">YADIM</div>
        <h1>Community Needs Intelligence</h1>
        <p>Interactive CSR & dakwah targeting dashboard using Community Need Index (CNI).</p>

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

        <div id="selectedInfo" class="selected-card">
            <strong>Select a district</strong>
            <p>Click a marker on the map to view recommended CSR/dakwah actions.</p>
        </div>
    </aside>

    <main>
        <div id="map"></div>
    </main>
</div>

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
