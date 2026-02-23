@extends('layouts.tabler')

@section('title', 'Risk Monitoring')

@section('content')

<style>
.dashboard-section {
    background: linear-gradient(160deg, #f8fafc 0%, #ffffff 48%, #f1f5f9 100%);
    border: 1px solid #d6e1ee;
    border-radius: 0.8rem;
    padding: 1rem;
}

.dashboard-title {
    font-size: 1.14rem;
    font-weight: 700;
    color: #0f172a;
}

.dashboard-widget-grid {
    display: grid;
    grid-template-columns: repeat(12, minmax(0, 1fr));
    gap: 0.8rem;
}

.dashboard-widget {
    grid-column: span 12;
    border: 1px solid #dbe4ef;
    box-shadow: 0 8px 20px rgba(15, 23, 42, 0.05);
    transition: box-shadow 0.2s ease, border-color 0.2s ease;
}

.dashboard-widget:hover {
    box-shadow: 0 12px 22px rgba(15, 23, 42, 0.08);
    border-color: #cdd9e8;
}

.dashboard-widget[data-widget-id="trend_scores"],
.dashboard-widget[data-widget-id="mitigation_sla"] {
    grid-column: span 6;
}

.dashboard-widget-hidden {
    display: none !important;
}

.dashboard-widget.dragging {
    opacity: 0.55;
    border-style: dashed;
}

.widget-drag-handle {
    color: #64748b;
    cursor: grab;
    user-select: none;
}

.widget-config-menu {
    min-width: 270px;
    z-index: 2200;
}

.widget-config-menu.show {
    display: block;
}

.metric-card {
    border-radius: 0.6rem;
    padding: 0.7rem 0.75rem;
}

.metric-label {
    font-size: 0.76rem;
    font-weight: 700;
    letter-spacing: 0.02em;
    text-transform: uppercase;
    opacity: 0.95;
}

.metric-value {
    font-size: 1.44rem;
    font-weight: 700;
    line-height: 1.2;
    margin-top: 0.2rem;
}

.metric-blue {
    background: #dbeafe;
    color: #1e3a8a;
}

.metric-red {
    background: #fee2e2;
    color: #7f1d1d;
}

.metric-yellow {
    background: #fef3c7;
    color: #78350f;
}

.metric-green {
    background: #dcfce7;
    color: #14532d;
}

.metric-inline {
    border: 1px solid #e2e8f0;
    border-radius: 0.5rem;
    padding: 0.5rem 0.62rem;
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 0.4rem;
}

.metric-inline-label {
    font-size: 0.79rem;
    color: #475569;
}

.metric-inline-value {
    font-weight: 700;
    color: #0f172a;
}

.trend-bars {
    display: grid;
    grid-template-columns: 1fr;
    gap: 0.45rem;
}

.trend-bar-row {
    border: 1px solid #e2e8f0;
    border-radius: 0.52rem;
    padding: 0.45rem 0.55rem;
    background: #ffffff;
}

.trend-bar-label {
    font-size: 0.75rem;
    font-weight: 700;
    color: #334155;
    margin-bottom: 0.36rem;
}

.trend-bar-item {
    display: flex;
    align-items: center;
    gap: 0.42rem;
    margin-bottom: 0.25rem;
}

.trend-bar-item:last-child {
    margin-bottom: 0;
}

.trend-bar-key {
    font-size: 0.68rem;
    font-weight: 700;
    width: 28px;
    text-transform: uppercase;
}

.trend-bar-track {
    flex: 1;
    height: 8px;
    border-radius: 999px;
    background: #e2e8f0;
    overflow: hidden;
}

.trend-bar-fill {
    height: 100%;
    border-radius: 999px;
}

.trend-bar-fill.inherent {
    background: #2563eb;
}

.trend-bar-fill.residual {
    background: #16a34a;
}

.trend-bar-fill.overall {
    background: #f59e0b;
}

.trend-bar-value {
    width: 46px;
    text-align: right;
    font-size: 0.72rem;
    font-weight: 700;
    color: #1f2937;
}

.summary-chart-panel {
    border: 1px solid #e2e8f0;
    border-radius: 0.6rem;
    background: #ffffff;
    padding: 0.65rem;
    min-height: 220px;
}

.status-pie-shell {
    position: relative;
    width: 178px;
    height: 178px;
    margin: 0 auto 0.7rem;
}

.status-pie-chart {
    width: 100%;
    height: 100%;
    border-radius: 50%;
    background: #e2e8f0;
}

.status-pie-center {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    width: 96px;
    height: 96px;
    border-radius: 50%;
    background: #ffffff;
    border: 1px solid #e2e8f0;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    line-height: 1.1;
}

.status-pie-total {
    font-size: 1.15rem;
    font-weight: 700;
    color: #0f172a;
}

.status-pie-label {
    font-size: 0.72rem;
    color: #64748b;
}

.status-pie-legend {
    display: grid;
    grid-template-columns: 1fr;
    gap: 0.32rem;
}

.status-legend-item {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 0.45rem;
    font-size: 0.75rem;
}

.status-legend-left {
    display: inline-flex;
    align-items: center;
    gap: 0.36rem;
    color: #334155;
}

.status-legend-dot {
    width: 10px;
    height: 10px;
    border-radius: 999px;
}

.status-legend-value {
    color: #0f172a;
    font-weight: 700;
}

.category-bar-chart {
    display: grid;
    grid-template-columns: 1fr;
    gap: 0.55rem;
}

.category-bar-row {
    border: 1px solid #edf2f7;
    border-radius: 0.48rem;
    padding: 0.42rem 0.5rem;
    background: #ffffff;
}

.category-bar-head {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 0.4rem;
    margin-bottom: 0.32rem;
}

.category-bar-label {
    font-size: 0.74rem;
    color: #334155;
    font-weight: 600;
}

.category-bar-value {
    font-size: 0.74rem;
    color: #0f172a;
    font-weight: 700;
}

.category-bar-track {
    height: 8px;
    width: 100%;
    border-radius: 999px;
    background: #e2e8f0;
    overflow: hidden;
}

.category-bar-fill {
    height: 100%;
    border-radius: 999px;
}

@media (max-width: 991px) {
    .dashboard-widget[data-widget-id="trend_scores"],
    .dashboard-widget[data-widget-id="mitigation_sla"] {
        grid-column: span 12;
    }
}
</style>

<div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
    <div class="d-flex flex-wrap align-items-center gap-2">
        @include('reports.export-buttons', [
            'scope' => 'monitoring',
            'scopeLabel' => 'Monitoring',
        ])
        <a href="{{ route('risks.index') }}" class="btn btn-secondary btn-sm with-icon">
            <i class="fas fa-table"></i> Risk Register
        </a>
        <a href="{{ route('risks.recap') }}" class="btn btn-info btn-sm with-icon">
            <i class="fas fa-chart-bar"></i> Recap
        </a>
        <a href="{{ route('risks.heatmap') }}" class="btn btn-dark btn-sm with-icon">
            <i class="fas fa-th"></i> Heat Map
        </a>
    </div>
    <h1 class="mb-0">Risk Monitoring</h1>
</div>

<div id="riskDashboardRoot" data-summary-url="{{ route('risks.dashboard-summary') }}" data-refresh-ms="30000">
    @include('risks.partials.dashboard-widgets', ['dashboardSummary' => $dashboardSummary])
</div>
<script id="riskDashboardInitialData" type="application/json">@json($dashboardSummary)</script>

@stop

@section('js')
<script>
(function () {
    const dashboardRoot = document.getElementById('riskDashboardRoot');
    if (!dashboardRoot) {
        return;
    }

    const summaryUrl = dashboardRoot.dataset.summaryUrl;
    const refreshMs = Number(dashboardRoot.dataset.refreshMs || 30000);
    const widgetGrid = document.getElementById('dashboardWidgetGrid');
    const visibilityKey = 'risk_dashboard_widget_visibility_v1';
    const orderKey = 'risk_dashboard_widget_order_v1';
    const statusColorMap = {
        Open: '#dc2626',
        Monitoring: '#f59e0b',
        Closed: '#16a34a',
        Unknown: '#64748b',
    };
    const defaultChartColors = ['#2563eb', '#14b8a6', '#8b5cf6', '#f97316', '#0ea5e9', '#94a3b8'];

    function clampPercent(value) {
        const number = Number(value);
        if (!Number.isFinite(number)) {
            return 0;
        }

        return Math.max(0, Math.min(100, number));
    }

    function escapeHtml(value) {
        const source = String(value ?? '-');
        return source
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function formatNumber(value, decimals = 0) {
        const number = Number(value);
        const safeValue = Number.isFinite(number) ? number : 0;
        return new Intl.NumberFormat('en-US', {
            minimumFractionDigits: decimals,
            maximumFractionDigits: decimals,
        }).format(safeValue);
    }

    function normalizeCountRows(rows) {
        if (!Array.isArray(rows)) {
            return [];
        }

        return rows.map(function (row) {
            const count = Number(row && row.count ? row.count : 0);
            return {
                label: row && row.label ? String(row.label) : '-',
                count: Number.isFinite(count) ? count : 0,
            };
        }).filter(function (row) {
            return row.count > 0;
        });
    }

    function colorByLabel(label, index) {
        if (statusColorMap[label]) {
            return statusColorMap[label];
        }

        return defaultChartColors[index % defaultChartColors.length];
    }

    function setText(id, value) {
        const element = document.getElementById(id);
        if (!element) {
            return;
        }
        element.textContent = value;
    }

    function setProgressBar(id, value) {
        const bar = document.getElementById(id);
        if (!bar) {
            return;
        }

        const width = clampPercent(value);
        bar.style.width = width.toFixed(2) + '%';
    }

    function renderCountRows(bodyId, rows, emptyMessage = 'No data') {
        const body = document.getElementById(bodyId);
        if (!body) {
            return;
        }

        if (!Array.isArray(rows) || rows.length === 0) {
            body.innerHTML = '<tr><td colspan="2" class="text-center text-muted">' + emptyMessage + '</td></tr>';
            return;
        }

        body.innerHTML = rows.map(function (row) {
            const label = escapeHtml(row && row.label ? row.label : '-');
            const count = formatNumber(row && row.count ? row.count : 0, 0);
            return '<tr><td>' + label + '</td><td class="text-end">' + count + '</td></tr>';
        }).join('');
    }

    function renderTrendRows(trendRows) {
        const tableBody = document.getElementById('riskTrendTableBody');
        if (!tableBody) {
            return;
        }

        if (!Array.isArray(trendRows) || trendRows.length === 0) {
            tableBody.innerHTML = '<tr><td colspan="4" class="text-center text-muted">No trend data</td></tr>';
            return;
        }

        tableBody.innerHTML = trendRows.map(function (row) {
            return [
                '<tr>',
                '<td>', escapeHtml(row && row.label ? row.label : (row && row.month ? row.month : '-')), '</td>',
                '<td class="text-end">', formatNumber(row && row.inherent ? row.inherent : 0, 2), '</td>',
                '<td class="text-end">', formatNumber(row && row.residual ? row.residual : 0, 2), '</td>',
                '<td class="text-end">', formatNumber(row && row.overall ? row.overall : 0, 2), '</td>',
                '</tr>'
            ].join('');
        }).join('');
    }

    function renderTrendBars(trendRows) {
        const barsContainer = document.getElementById('riskTrendBars');
        if (!barsContainer) {
            return;
        }

        if (!Array.isArray(trendRows) || trendRows.length === 0) {
            barsContainer.innerHTML = '<small class="text-muted">No trend data</small>';
            return;
        }

        let maxValue = 0;
        trendRows.forEach(function (row) {
            maxValue = Math.max(
                maxValue,
                Number(row && row.inherent ? row.inherent : 0),
                Number(row && row.residual ? row.residual : 0),
                Number(row && row.overall ? row.overall : 0)
            );
        });
        if (maxValue <= 0) {
            maxValue = 1;
        }

        barsContainer.innerHTML = trendRows.map(function (row) {
            const inherent = Number(row && row.inherent ? row.inherent : 0);
            const residual = Number(row && row.residual ? row.residual : 0);
            const overall = Number(row && row.overall ? row.overall : 0);

            const inherentWidth = clampPercent((inherent / maxValue) * 100);
            const residualWidth = clampPercent((residual / maxValue) * 100);
            const overallWidth = clampPercent((overall / maxValue) * 100);
            const label = escapeHtml(row && row.label ? row.label : (row && row.month ? row.month : '-'));

            return [
                '<div class="trend-bar-row">',
                '<div class="trend-bar-label">', label, '</div>',
                '<div class="trend-bar-item"><span class="trend-bar-key">Inh</span><div class="trend-bar-track"><div class="trend-bar-fill inherent" style="width:', inherentWidth.toFixed(2), '%"></div></div><span class="trend-bar-value">', formatNumber(inherent, 2), '</span></div>',
                '<div class="trend-bar-item"><span class="trend-bar-key">Res</span><div class="trend-bar-track"><div class="trend-bar-fill residual" style="width:', residualWidth.toFixed(2), '%"></div></div><span class="trend-bar-value">', formatNumber(residual, 2), '</span></div>',
                '<div class="trend-bar-item"><span class="trend-bar-key">All</span><div class="trend-bar-track"><div class="trend-bar-fill overall" style="width:', overallWidth.toFixed(2), '%"></div></div><span class="trend-bar-value">', formatNumber(overall, 2), '</span></div>',
                '</div>'
            ].join('');
        }).join('');
    }

    function renderVarianceRows(rows) {
        const body = document.getElementById('varianceAnalysisBody');
        if (!body) {
            return;
        }

        if (!Array.isArray(rows) || rows.length === 0) {
            body.innerHTML = '<tr><td colspan="5" class="text-center text-muted">No variance data</td></tr>';
            return;
        }

        body.innerHTML = rows.map(function (row) {
            return [
                '<tr>',
                '<td>', escapeHtml(row && row.category ? row.category : '-'), '</td>',
                '<td class="text-end">', formatNumber(row && row.risk_count ? row.risk_count : 0, 0), '</td>',
                '<td class="text-end">', formatNumber(row && row.avg_inherent ? row.avg_inherent : 0, 2), '</td>',
                '<td class="text-end">', formatNumber(row && row.avg_residual ? row.avg_residual : 0, 2), '</td>',
                '<td class="text-end">', formatNumber(row && row.variance ? row.variance : 0, 2), '</td>',
                '</tr>'
            ].join('');
        }).join('');
    }

    function renderStatusPie(statusRows) {
        const pie = document.getElementById('statusPieChart');
        const center = document.getElementById('statusPieCenter');
        const legend = document.getElementById('statusPieLegend');
        if (!pie || !center || !legend) {
            return;
        }

        const rows = normalizeCountRows(statusRows);
        if (rows.length === 0) {
            pie.style.background = '#e2e8f0';
            center.innerHTML = '<div class="status-pie-total">0</div><div class="status-pie-label">Risks</div>';
            legend.innerHTML = '<div class="text-muted small text-center">No status data</div>';
            return;
        }

        const total = rows.reduce(function (sum, row) {
            return sum + row.count;
        }, 0);

        let currentPercent = 0;
        const gradientSegments = rows.map(function (row, index) {
            const color = colorByLabel(row.label, index);
            const percentage = (row.count / total) * 100;
            const start = currentPercent;
            currentPercent += percentage;
            row.__percentage = percentage;
            row.__color = color;
            return color + ' ' + start.toFixed(2) + '% ' + currentPercent.toFixed(2) + '%';
        });

        pie.style.background = 'conic-gradient(' + gradientSegments.join(', ') + ')';
        center.innerHTML = '<div class="status-pie-total">' + formatNumber(total, 0) + '</div><div class="status-pie-label">Risks</div>';

        legend.innerHTML = rows.map(function (row) {
            return [
                '<div class="status-legend-item">',
                '<span class="status-legend-left"><span class="status-legend-dot" style="background:', row.__color, '"></span>', escapeHtml(row.label), '</span>',
                '<span class="status-legend-value">', formatNumber(row.count, 0), ' (', formatNumber(row.__percentage, 1), '%)</span>',
                '</div>'
            ].join('');
        }).join('');
    }

    function renderCategoryBars(categoryRows) {
        const container = document.getElementById('categoryBarChart');
        if (!container) {
            return;
        }

        const rows = normalizeCountRows(categoryRows).slice(0, 5);
        if (rows.length === 0) {
            container.innerHTML = '<div class="text-muted small text-center py-2">No category data</div>';
            return;
        }

        const maxCount = rows.reduce(function (max, row) {
            return row.count > max ? row.count : max;
        }, 0) || 1;

        container.innerHTML = rows.map(function (row, index) {
            const width = clampPercent((row.count / maxCount) * 100);
            const color = defaultChartColors[index % defaultChartColors.length];

            return [
                '<div class="category-bar-row">',
                '<div class="category-bar-head">',
                '<span class="category-bar-label">', escapeHtml(row.label), '</span>',
                '<span class="category-bar-value">', formatNumber(row.count, 0), '</span>',
                '</div>',
                '<div class="category-bar-track"><div class="category-bar-fill" style="width:', width.toFixed(2), '%;background:', color, '"></div></div>',
                '</div>'
            ].join('');
        }).join('');
    }

    function renderDashboardSummary(summary) {
        if (!summary || typeof summary !== 'object') {
            return;
        }

        setText('dashboardGeneratedAt', summary.generated_at || '-');
        setText('metricTotalRisks', formatNumber(summary.total_risks || 0, 0));
        setText('metricOpenRisks', formatNumber(summary.open_count || 0, 0));
        setText('metricMonitoringRisks', formatNumber(summary.monitoring_count || 0, 0));
        setText('metricClosedRisks', formatNumber(summary.closed_count || 0, 0));

        const averageScores = summary.average_scores || {};
        setText('metricAvgInherent', formatNumber(averageScores.inherent || 0, 2));
        setText('metricAvgResidual', formatNumber(averageScores.residual || 0, 2));
        setText('metricAvgOverall', formatNumber(averageScores.overall || 0, 2));

        const variance = summary.variance || {};
        setText('metricGlobalVariance', formatNumber(variance.global || 0, 2));

        const mitigation = summary.mitigation || {};
        const mitigationPercent = Number(mitigation.effectiveness_percent || 0);
        setText('mitigationEffectivenessText', formatNumber(mitigationPercent, 2) + '%');
        setText('mitigationTreatmentCount', formatNumber(mitigation.treatment_count || 0, 0));
        setText('mitigationCoveragePercent', formatNumber(mitigation.risk_coverage_percent || 0, 2) + '%');
        setProgressBar('mitigationEffectivenessBar', mitigationPercent);

        const sla = summary.sla || {};
        const slaActual = Number(sla.actual_percent || 0);
        const slaTarget = Number(sla.target_percent || 95);
        setText('slaActualPercent', formatNumber(slaActual, 2) + '%');
        setText('slaTargetPercent', formatNumber(slaTarget, 2) + '%');
        setText('slaDueCount', formatNumber(sla.due_count || 0, 0));
        setText('slaCompliantCount', formatNumber(sla.compliant_count || 0, 0));
        setText('slaOverdueCount', formatNumber(sla.overdue_count || 0, 0));
        setProgressBar('slaComplianceBar', slaActual);

        const slaBar = document.getElementById('slaComplianceBar');
        if (slaBar) {
            slaBar.classList.remove('bg-success', 'bg-danger');
            slaBar.classList.add(slaActual >= slaTarget ? 'bg-success' : 'bg-danger');
        }

        const trendRows = Array.isArray(summary.trend) ? summary.trend : [];
        renderTrendBars(trendRows);
        renderTrendRows(trendRows);

        renderCountRows('summaryByCategoryBody', summary.summary_by_category || []);
        renderCountRows('summaryByOwnerBody', summary.summary_by_owner || []);
        renderCountRows('summaryByStatusBody', summary.summary_by_status || []);
        renderStatusPie(summary.summary_by_status || []);
        renderCategoryBars(summary.summary_by_category || []);
        renderVarianceRows((summary.variance && summary.variance.rows) || []);
    }

    function readJsonStorage(key, fallbackValue) {
        try {
            const raw = localStorage.getItem(key);
            if (!raw) {
                return fallbackValue;
            }
            const parsed = JSON.parse(raw);
            return parsed || fallbackValue;
        } catch (error) {
            return fallbackValue;
        }
    }

    function writeJsonStorage(key, value) {
        try {
            localStorage.setItem(key, JSON.stringify(value));
        } catch (error) {
            // Ignore blocked storage.
        }
    }

    function applyWidgetOrder() {
        if (!widgetGrid) {
            return;
        }

        const storedOrder = readJsonStorage(orderKey, []);
        if (!Array.isArray(storedOrder) || storedOrder.length === 0) {
            return;
        }

        const widgets = Array.from(widgetGrid.querySelectorAll('.dashboard-widget'));
        const widgetMap = {};
        widgets.forEach(function (widget) {
            widgetMap[widget.dataset.widgetId] = widget;
        });

        storedOrder.forEach(function (widgetId) {
            if (widgetMap[widgetId]) {
                widgetGrid.appendChild(widgetMap[widgetId]);
            }
        });
    }

    function persistWidgetOrder() {
        if (!widgetGrid) {
            return;
        }

        const order = Array.from(widgetGrid.querySelectorAll('.dashboard-widget'))
            .map(function (widget) {
                return widget.dataset.widgetId;
            })
            .filter(Boolean);
        writeJsonStorage(orderKey, order);
    }

    function setupWidgetCustomizeDropdown() {
        const toggle = document.querySelector('[data-widget-customize-toggle]');
        const menu = document.querySelector('[data-widget-customize-menu]');
        if (!toggle || !menu) {
            return;
        }

        const container = toggle.closest('.dropdown') || dashboardRoot;

        function isOpen() {
            return menu.classList.contains('show');
        }

        function openMenu() {
            menu.classList.add('show');
            if (container) {
                container.classList.add('show');
            }
            toggle.setAttribute('aria-expanded', 'true');
        }

        function closeMenu() {
            menu.classList.remove('show');
            if (container) {
                container.classList.remove('show');
            }
            toggle.setAttribute('aria-expanded', 'false');
        }

        function toggleMenu() {
            if (isOpen()) {
                closeMenu();
            } else {
                openMenu();
            }
        }

        toggle.addEventListener('click', function (event) {
            event.preventDefault();
            event.stopPropagation();
            toggleMenu();
        });

        toggle.addEventListener('keydown', function (event) {
            if (event.key === 'Enter' || event.key === ' ') {
                event.preventDefault();
                event.stopPropagation();
                toggleMenu();
            }

            if (event.key === 'Escape') {
                closeMenu();
            }
        });

        menu.addEventListener('click', function (event) {
            event.stopPropagation();
        });

        document.addEventListener('click', function (event) {
            if (!menu.contains(event.target) && !toggle.contains(event.target)) {
                closeMenu();
            }
        });

        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape') {
                closeMenu();
            }
        });
    }

    function applyWidgetVisibilityState(state) {
        if (!widgetGrid) {
            return;
        }

        const safeState = state && typeof state === 'object' ? state : {};
        widgetGrid.querySelectorAll('.dashboard-widget').forEach(function (widget) {
            const widgetId = widget.dataset.widgetId;
            const isVisible = safeState[widgetId] !== false;
            widget.classList.toggle('dashboard-widget-hidden', !isVisible);
        });

        document.querySelectorAll('.widget-visibility-toggle').forEach(function (checkbox) {
            const widgetId = checkbox.dataset.widgetId;
            checkbox.checked = safeState[widgetId] !== false;
        });
    }

    function setupWidgetVisibility() {
        const currentState = readJsonStorage(visibilityKey, {});
        applyWidgetVisibilityState(currentState);

        document.querySelectorAll('.widget-visibility-toggle').forEach(function (checkbox) {
            checkbox.addEventListener('change', function () {
                const latestState = readJsonStorage(visibilityKey, {});
                latestState[checkbox.dataset.widgetId] = checkbox.checked;
                writeJsonStorage(visibilityKey, latestState);
                applyWidgetVisibilityState(latestState);
            });
        });
    }

    function setupWidgetDragDrop() {
        if (!widgetGrid) {
            return;
        }

        let activeWidget = null;
        const widgets = Array.from(widgetGrid.querySelectorAll('.dashboard-widget'));

        widgets.forEach(function (widget) {
            widget.addEventListener('dragstart', function (event) {
                activeWidget = widget;
                widget.classList.add('dragging');
                if (event.dataTransfer) {
                    event.dataTransfer.effectAllowed = 'move';
                    event.dataTransfer.setData('text/plain', widget.dataset.widgetId || '');
                }
            });

            widget.addEventListener('dragend', function () {
                widget.classList.remove('dragging');
                activeWidget = null;
                persistWidgetOrder();
            });
        });

        widgetGrid.addEventListener('dragover', function (event) {
            if (!activeWidget) {
                return;
            }
            event.preventDefault();

            const sibling = findDropTarget(widgetGrid, event.clientY);
            if (!sibling) {
                widgetGrid.appendChild(activeWidget);
                return;
            }

            if (sibling !== activeWidget) {
                widgetGrid.insertBefore(activeWidget, sibling);
            }
        });
    }

    function findDropTarget(container, mouseY) {
        const candidates = Array.from(container.querySelectorAll('.dashboard-widget:not(.dragging):not(.dashboard-widget-hidden)'));
        let closest = {
            offset: Number.NEGATIVE_INFINITY,
            element: null,
        };

        candidates.forEach(function (candidate) {
            const rect = candidate.getBoundingClientRect();
            const offset = mouseY - rect.top - (rect.height / 2);
            if (offset < 0 && offset > closest.offset) {
                closest = {
                    offset: offset,
                    element: candidate,
                };
            }
        });

        return closest.element;
    }

    function parseInitialSummary() {
        const summaryNode = document.getElementById('riskDashboardInitialData');
        if (!summaryNode) {
            return null;
        }

        try {
            return JSON.parse(summaryNode.textContent || '{}');
        } catch (error) {
            return null;
        }
    }

    let fetchInFlight = false;
    async function refreshDashboardSummary() {
        if (!summaryUrl || fetchInFlight) {
            return;
        }

        fetchInFlight = true;
        try {
            const query = new URLSearchParams(window.location.search);
            const endpoint = query.toString() ? (summaryUrl + '?' + query.toString()) : summaryUrl;
            const response = await fetch(endpoint, {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                },
                credentials: 'same-origin',
            });

            if (!response.ok) {
                throw new Error('Failed to load dashboard summary.');
            }

            const payload = await response.json();
            renderDashboardSummary(payload);
        } catch (error) {
            // Keep previous values if refresh fails.
        } finally {
            fetchInFlight = false;
        }
    }

    applyWidgetOrder();
    setupWidgetCustomizeDropdown();
    setupWidgetVisibility();
    setupWidgetDragDrop();

    const initialSummary = parseInitialSummary();
    if (initialSummary) {
        renderDashboardSummary(initialSummary);
    } else {
        refreshDashboardSummary();
    }

    const intervalMs = Number.isFinite(refreshMs) && refreshMs >= 10000 ? refreshMs : 30000;
    window.setInterval(refreshDashboardSummary, intervalMs);
})();
</script>
@stop
