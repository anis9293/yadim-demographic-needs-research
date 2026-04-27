/**
 * Malaysia Demographic Map Renderer
 * Uses D3.js and SVG patterns for data visualization.
 */
export async function renderMalaysiaMap(selector, feed, options = {}) {
    const d3 = window.d3;
    const container = document.querySelector(selector);

    if (!container) {
        return;
    }

    if (!d3) {
        container.innerHTML = '<p class="map-error">D3.js failed to load.</p>';
        return;
    }

    const width = 1000;
    const height = 620;
    const geojsonUrl = options.geojsonUrl || '/js/malaysia-states.json';
    const tooltip = d3.select(options.tooltipSelector || '#tooltip');

    container.innerHTML = '';

    const svg = d3.select(container)
        .append('svg')
        .attr('viewBox', `0 0 ${width} ${height}`)
        .attr('preserveAspectRatio', 'xMidYMid meet');

    const defs = svg.append('defs');
    definePatterns(defs);

    try {
        const geojson = await d3.json(geojsonUrl);
        const projection = d3.geoMercator().fitSize([width - 60, height - 120], geojson);
        const path = d3.geoPath().projection(projection);
        const stateData = new Map((feed.data || []).map((state) => [normalizeName(state.state), state]));

        const mapGroup = svg.append('g').attr('transform', 'translate(30, 24)');

        mapGroup.selectAll('path')
            .data(geojson.features)
            .enter()
            .append('path')
            .attr('d', path)
            .attr('class', 'state')
            .attr('fill', (feature) => {
                const data = stateData.get(normalizeName(feature.properties.name));
                if (!data) {
                    return 'url(#pattern-none)';
                }

                return `url(#pattern-${getDominantSubject(data)})`;
            })
            .on('mousemove', (event, feature) => {
                const name = feature.properties.name;
                const data = stateData.get(normalizeName(name));

                tooltip
                    .style('opacity', 1)
                    .style('left', `${event.pageX}px`)
                    .style('top', `${event.pageY}px`)
                    .html(buildTooltip(name, data));
            })
            .on('mouseout', () => tooltip.style('opacity', 0));

        renderLegend(svg);
    } catch (error) {
        console.error(error);
        container.innerHTML = '<p class="map-error">Unable to load Malaysia map data.</p>';
    }
}

function definePatterns(defs) {
    defs.append('pattern')
        .attr('id', 'pattern-subject-1')
        .attr('width', 10)
        .attr('height', 10)
        .attr('patternUnits', 'userSpaceOnUse')
        .attr('patternTransform', 'rotate(45)')
        .append('line')
        .attr('x1', 0)
        .attr('y1', 0)
        .attr('x2', 0)
        .attr('y2', 10)
        .attr('stroke', '#2563eb')
        .attr('stroke-width', 3);

    const dots = defs.append('pattern')
        .attr('id', 'pattern-subject-2')
        .attr('width', 9)
        .attr('height', 9)
        .attr('patternUnits', 'userSpaceOnUse');

    dots.append('rect')
        .attr('width', 9)
        .attr('height', 9)
        .attr('fill', '#ffffff');

    dots.append('circle')
        .attr('cx', 4.5)
        .attr('cy', 4.5)
        .attr('r', 2.2)
        .attr('fill', '#dc2626');

    defs.append('pattern')
        .attr('id', 'pattern-subject-3')
        .attr('width', 10)
        .attr('height', 10)
        .attr('patternUnits', 'userSpaceOnUse')
        .append('path')
        .attr('d', 'M 10 0 L 0 10 M 0 0 L 10 10')
        .attr('stroke', '#16a34a')
        .attr('stroke-width', 1.5);

    defs.append('pattern')
        .attr('id', 'pattern-none')
        .attr('width', 10)
        .attr('height', 10)
        .attr('patternUnits', 'userSpaceOnUse')
        .append('rect')
        .attr('width', 10)
        .attr('height', 10)
        .attr('fill', '#eef2f0');
}

function renderLegend(svg) {
    const legendData = [
        { label: 'Subject 1 High', pattern: 'url(#pattern-subject-1)' },
        { label: 'Subject 2 High', pattern: 'url(#pattern-subject-2)' },
        { label: 'Subject 3 High', pattern: 'url(#pattern-subject-3)' },
        { label: 'No Data', pattern: 'url(#pattern-none)' },
    ];

    const legend = svg.append('g')
        .attr('transform', 'translate(24, 520)');

    legendData.forEach((item, index) => {
        const row = legend.append('g')
            .attr('transform', `translate(0, ${index * 24})`);

        row.append('rect')
            .attr('width', 18)
            .attr('height', 18)
            .attr('fill', item.pattern)
            .attr('stroke', '#a8b4af');

        row.append('text')
            .attr('x', 28)
            .attr('y', 13)
            .attr('class', 'legend')
            .text(item.label);
    });
}

function getDominantSubject(stateData) {
    const subjects = Object.keys(stateData).filter((key) => key.startsWith('subject-'));

    if (subjects.length === 0) {
        return 'none';
    }

    return subjects.reduce((highest, subject) => {
        return Number(stateData[subject] || 0) > Number(stateData[highest] || 0) ? subject : highest;
    }, subjects[0]);
}

function buildTooltip(name, stateData) {
    if (!stateData) {
        return `<strong>${name}</strong><br>No data available`;
    }

    return [
        `<strong>${name}</strong>`,
        `Subject 1: ${stateData['subject-1'] || 0}%`,
        `Subject 2: ${stateData['subject-2'] || 0}%`,
        `Subject 3: ${stateData['subject-3'] || 0}%`,
    ].join('<br>');
}

function normalizeName(name) {
    return String(name || '').trim().toLowerCase();
}
