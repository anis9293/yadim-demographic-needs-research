<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>YADIM Demographic Needs Dashboard</title>
    <link rel="icon" href="{{ asset('favicon.ico') }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.5.3/dist/MarkerCluster.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.5.3/dist/MarkerCluster.Default.css">
    <style>
        :root { --ink:#14231c; --muted:#66746b; --line:#e2e8df; --green:#11533f; --green-dark:#0d3b2e; }
        body { background:#f6f8f5; color:var(--ink); }
        aside { width:320px; min-height:100vh; background:#ffffff; color:var(--ink); position:fixed; left:0; top:0; border-right:1px solid var(--line); padding:24px; }
        main { margin-left:320px; padding:28px; }
        .brand { justify-content:center; padding:8px 0 18px !important; border-bottom:1px solid var(--line); }
        .brand img { width:220px; max-width:100%; height:auto; object-fit:contain; }
        .project-title { margin:22px 0 4px; font-size:1.35rem; line-height:1.15; font-weight:800; letter-spacing:0; }
        .project-motto { margin:0; color:var(--muted); font-size:.94rem; line-height:1.45; }
        .cni-note { margin-top:20px; padding:16px; border:1px solid #d8e6dc; border-radius:16px; background:linear-gradient(180deg,#f8fbf4,#ffffff); }
        .cni-note h2 { margin:0 0 10px; font-size:.95rem; font-weight:800; color:var(--green-dark); }
        .cni-formula { margin:0 0 12px; padding:12px; border-radius:12px; background:#edf6ef; color:#183d2f; font-size:.85rem; line-height:1.5; font-weight:700; }
        .cni-note ul { margin:0; padding-left:18px; color:var(--muted); font-size:.86rem; line-height:1.55; }
        #map { height:70vh; min-height:520px; border-radius:16px; box-shadow:0 14px 36px rgba(20,35,28,.08); }
        .stat-card { border:1px solid var(--line); border-radius:16px; box-shadow:0 12px 28px rgba(20,35,28,.05); }
        .legend { background:white; padding:10px 12px; border-radius:10px; box-shadow:0 4px 16px rgba(0,0,0,.16); line-height:1.5; }
        .legend i { width:14px; height:14px; float:left; margin-right:8px; opacity:.85; border-radius:3px; }
        .map-zoom-hint { margin-left:44px; padding:7px 10px; border:1px solid #d7dfd6; border-radius:999px; background:rgba(255,255,255,.94); color:#55645b; font-size:.78rem; box-shadow:0 4px 16px rgba(20,35,28,.10); pointer-events:none; }
        .small-muted { color:var(--muted); font-size:.85rem; }
        @media (max-width: 900px) { aside { position:relative; width:100%; min-height:auto; } main { margin-left:0; padding:18px; } }
    </style>
</head>
<body>
<aside>
    <div class="brand d-flex align-items-center gap-3 p-3">
        <img src="{{ asset('images/yadim-logo-jata.png') }}" alt="Yayasan Dakwah Islamiah Malaysia (YADIM)">
    </div>
    <h1 class="project-title">YADIM Demographic Needs Research</h1>
    <p class="project-motto">Data-guided dakwah, CSR, and community support for every district.</p>

    <section class="cni-note" aria-labelledby="cni-note-title">
        <h2 id="cni-note-title">How CNI is calculated</h2>
        <p class="cni-formula">CNI = 0.40 Poverty + 0.25 Income Gap + 0.20 Youth Risk + 0.15 Inequality</p>
        <ul>
            <li>Poverty uses district poverty rate.</li>
            <li>Income gap is based on inverse median household income.</li>
            <li>Youth risk uses population aged 15-29.</li>
            <li>Inequality uses the district Gini coefficient.</li>
        </ul>
    </section>
</aside>

<main>
    <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
        <div>
            <h1 class="h3 mb-1">Demographic Needs Dashboard</h1>
            <div class="small-muted">
                Live OpenDOSM OpenAPI refresh on page load.
                @if(($openDosmStatus['ok'] ?? false) && isset($openDosmStatus['result']))
                    Population: {{ $openDosmStatus['result']['population_source_date'] }} · HIES: {{ $openDosmStatus['result']['hies_source_date'] }}
                @elseif(!($openDosmStatus['ok'] ?? true))
                    Showing local cached data because OpenDOSM refresh failed.
                @endif
            </div>
        </div>
    </div>

    @if($districts->count() === 0)
        <div class="alert alert-warning">
            No real data found yet. Run: <code>php artisan migrate</code> then <code>php artisan yadim:import-dosm</code>.
        </div>
    @endif

    <div class="row g-3 mb-4">
        <div class="col-md-3"><div class="card stat-card"><div class="card-body"><div class="small-muted">Districts Loaded</div><div class="h3">{{ $districts->count() }}</div></div></div></div>
        <div class="col-md-3"><div class="card stat-card"><div class="card-body"><div class="small-muted">Highest CNI</div><div class="h3">{{ number_format((float)$districts->max('cni_score'), 1) }}</div></div></div></div>
        <div class="col-md-3"><div class="card stat-card"><div class="card-body"><div class="small-muted">Avg Poverty Rate</div><div class="h3">{{ number_format((float)$districts->avg('poverty_rate'), 1) }}%</div></div></div></div>
        <div class="col-md-3"><div class="card stat-card"><div class="card-body"><div class="small-muted">Data Method</div><div class="h6 mt-2">OpenDOSM Real Data</div></div></div></div>
    </div>

    <div class="card stat-card mb-4">
        <div class="card-body">
            <div id="map"></div>
            <div class="small-muted mt-2">
                If district boundaries do not appear, add <code>public/data/malaysia.district.geojson</code>. The dashboard still uses real imported CNI data for the ranking table.
            </div>
        </div>
    </div>

    <div class="card stat-card">
        <div class="card-body">
            <h2 class="h5 mb-3">Priority District Ranking</h2>
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead><tr><th>District</th><th>State</th><th>CNI</th><th>Poverty</th><th>Median Income</th><th>Youth Share</th><th>Gini</th></tr></thead>
                    <tbody>
                    @foreach($districts->take(50) as $d)
                        <tr>
                            <td>{{ $d->name }}</td><td>{{ $d->state }}</td><td><strong>{{ number_format((float)$d->cni_score, 1) }}</strong></td>
                            <td>{{ number_format((float)$d->poverty_rate, 1) }}%</td><td>RM {{ number_format((float)$d->income_median) }}</td>
                            <td>{{ number_format((float)$d->youth_share, 1) }}%</td><td>{{ number_format((float)$d->gini, 3) }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</main>

<div class="modal fade" id="cniInfoModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header"><h5 class="modal-title">Community Need Index Calculation</h5><button class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <p><strong>Real-data CNI v1</strong> uses only fields imported from OpenDOSM district datasets.</p>
                <pre class="bg-light p-3 rounded">CNI = 0.40(Poverty Score)
    + 0.25(Income Gap Score)
    + 0.20(Youth Risk Score)
    + 0.15(Inequality Score)</pre>
                <p>All components are normalized from 0–100 across available districts.</p>
                <ul>
                    <li><strong>Poverty Score:</strong> based on district poverty rate.</li>
                    <li><strong>Income Gap Score:</strong> inverse score from median household income.</li>
                    <li><strong>Youth Risk Score:</strong> share of population aged 15–29.</li>
                    <li><strong>Inequality Score:</strong> based on Gini coefficient.</li>
                </ul>
                <p class="mb-0">Religious access or YADIM programme coverage should be added later as an internal YADIM dataset, not guessed from DOSM.</p>
            </div>
        </div>
    </div>
</div>

<script>window.districtRows = @json($districts);</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="https://unpkg.com/leaflet.markercluster@1.5.3/dist/leaflet.markercluster.js"></script>
<script src="{{ asset('js/map-dashboard.js') }}"></script>
</body>
</html>
