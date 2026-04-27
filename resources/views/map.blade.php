<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>YADIM Demographic Needs Research</title>
    <script src="https://d3js.org/d3.v7.min.js"></script>
    <style>
        :root {
            color-scheme: light;
            font-family: Arial, Helvetica, sans-serif;
            background: #f6f7f2;
            color: #18211f;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            min-height: 100vh;
        }

        main {
            width: min(1180px, calc(100% - 32px));
            margin: 0 auto;
            padding: 28px 0;
        }

        header {
            display: flex;
            align-items: end;
            justify-content: space-between;
            gap: 20px;
            margin-bottom: 18px;
        }

        h1 {
            margin: 0;
            font-size: clamp(1.6rem, 4vw, 2.7rem);
            line-height: 1.05;
            letter-spacing: 0;
        }

        .summary {
            max-width: 560px;
            margin: 8px 0 0;
            color: #4d5b57;
            line-height: 1.55;
        }

        .status {
            flex: 0 0 auto;
            padding: 8px 10px;
            border: 1px solid #c7d3ce;
            background: #ffffff;
            font-size: 0.9rem;
        }

        #map-container {
            position: relative;
            min-height: 620px;
            border: 1px solid #d7ded9;
            background: #ffffff;
            overflow: hidden;
        }

        #map-container svg {
            display: block;
            width: 100%;
            height: 620px;
        }

        .state {
            stroke: #d6d6d6;
            stroke-width: 0.8px;
            cursor: pointer;
            transition: opacity 160ms ease, stroke 160ms ease, stroke-width 160ms ease;
        }

        .state:hover {
            opacity: 0.82;
            stroke: #7a7a7a;
            stroke-width: 1.8px;
        }

        .tooltip {
            position: absolute;
            max-width: 240px;
            padding: 10px 12px;
            border: 1px solid #24342f;
            background: rgba(24, 33, 31, 0.93);
            color: #ffffff;
            font-size: 0.82rem;
            line-height: 1.45;
            pointer-events: none;
            opacity: 0;
            transform: translate(12px, -12px);
        }

        .legend {
            font-size: 13px;
            fill: #24342f;
        }

        .map-error {
            padding: 24px;
            color: #9f1d1d;
        }

        @media (max-width: 760px) {
            main {
                width: min(100% - 20px, 1180px);
                padding: 18px 0;
            }

            header {
                display: block;
            }

            .status {
                display: inline-block;
                margin-top: 14px;
            }

            #map-container,
            #map-container svg {
                min-height: 480px;
                height: 480px;
            }
        }
    </style>
</head>
<body>
    <main>
        <header>
            <div>
                <h1>YADIM Demographic Needs Research</h1>
                <p class="summary">Interactive Malaysia state map showing the dominant research subject in each state through SVG texture patterns.</p>
            </div>
            <div class="status">Local GeoJSON loaded from <strong>public/js</strong></div>
        </header>

        <div id="map-container" aria-label="Malaysia demographic map"></div>
        <div id="tooltip" class="tooltip"></div>
    </main>

    <script>
        window.demographicFeed = @json($feed);
    </script>
    <script type="module">
        import { renderMalaysiaMap } from "{{ asset('js/malaysia-map.js') }}";

        renderMalaysiaMap('#map-container', window.demographicFeed, {
            geojsonUrl: "{{ asset('js/malaysia-states.json') }}",
            tooltipSelector: '#tooltip',
        });
    </script>
</body>
</html>
