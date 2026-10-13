@php
    $exec = $executiveMetrics ?? [];
    $summary = $dashboardSummary ?? [];

    $estLoss = $exec['est_loss_formatted'] ?? 'Rp 4.44M';
    $overallRating = $exec['overall_rating'] ?? 'Medium';
    $overallRatingColor = $exec['overall_rating_color'] ?? '#d97706';
    $overallRatingNote = $exec['overall_rating_note'] ?? '▸ Unchanged from Q3';

    $openCount = $exec['open_items_count'] ?? 37;
    $openSubtext = $exec['open_items_subtext'] ?? '↓ 6 from Q3';

    $coveragePct = $exec['control_coverage_pct'] ?? 84;
    $coverageSubtext = $exec['control_coverage_subtext'] ?? '↑ 6% from Q3';

    $highRiskCount = $exec['high_risk_count'] ?? 3;
    $matrixDetail = $exec['matrix_detail'] ?? [];

    $yoyPrev = $exec['yoy_baseline'] ?? 100;
    $yoyCurr = $exec['yoy_current'] ?? 63;
    $yoyRed = $exec['yoy_reduction_pct'] ?? 37;

    $resilienceScore = $exec['resilience_score'] ?? 72;
    $resilienceRating = $exec['resilience_rating'] ?? 'Good';

    $topCatLabels = $exec['top_categories_labels'] ?? ['Operasional', 'IT & Cyber', 'Kepatuhan', 'Finansial', 'Lainnya'];
    $topCatPcts = $exec['top_categories_percentages'] ?? [34, 28, 19, 12, 7];

    $trendLabels = $exec['trend_line_labels'] ?? ['Q2\'24', 'Q3\'24', 'Q4\'24', 'Q1\'25', 'Q2\'25', 'Q3\'25', 'Q4\'25', 'Q1\'26'];
    $trendValues = $exec['trend_line_values'] ?? [14.2, 13.8, 13.5, 12.9, 12.4, 11.8, 11.2, 10.4];

    // Colors for 5x5 heatmap grid
    // Row 5 (Almost Certain)
    // Row 4 (Likely)
    // Row 3 (Possible)
    // Row 2 (Unlikely)
    // Row 1 (Rare)
    $cellBgMap = [
        5 => [1 => '#ea580c', 2 => '#dc2626', 3 => '#dc2626', 4 => '#b91c1c', 5 => '#b91c1c'],
        4 => [1 => '#16a34a', 2 => '#d97706', 3 => '#ea580c', 4 => '#dc2626', 5 => '#dc2626'],
        3 => [1 => '#16a34a', 2 => '#16a34a', 3 => '#d97706', 4 => '#dc2626', 5 => '#dc2626'],
        2 => [1 => '#16a34a', 2 => '#16a34a', 3 => '#16a34a', 4 => '#d97706', 5 => '#ea580c'],
        1 => [1 => '#16a34a', 2 => '#16a34a', 3 => '#16a34a', 4 => '#16a34a', 5 => '#d97706'],
    ];

    $rowLabels = [
        5 => 'Almost Certain',
        4 => 'Likely',
        3 => 'Possible',
        2 => 'Unlikely',
        1 => 'Rare',
    ];

    $colLabels = [
        1 => 'Negligible',
        2 => 'Minor',
        3 => 'Moderate',
        4 => 'Major',
        5 => 'Catastrophic',
    ];

    // Speedometer angle: 0 score = -90 deg (left), 100 score = +90 deg (right)
    $gaugeAngle = -90 + ($resilienceScore / 100) * 180;
@endphp

<style>
    /* Executive Board Dashboard Theme */
    .executive-dashboard {
        font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
        color: #1e293b;
    }

    .executive-header-banner {
        background: #0f172a;
        color: #ffffff;
        border-radius: 10px;
        padding: 1.15rem 1.4rem;
        box-shadow: 0 6px 18px rgba(15, 23, 42, 0.12);
    }

    .executive-title {
        font-size: 1.65rem;
        font-weight: 800;
        letter-spacing: -0.02em;
        margin: 0;
        color: #ffffff;
    }

    .executive-subtitle {
        font-size: 0.76rem;
        color: #94a3b8;
        font-weight: 500;
        letter-spacing: 0.02em;
        margin-top: 0.25rem;
    }

    .live-status-pill {
        display: inline-flex;
        align-items: center;
        gap: 0.45rem;
        font-size: 0.72rem;
        font-weight: 700;
        letter-spacing: 0.06em;
        text-transform: uppercase;
        color: #e2e8f0;
    }

    .live-dot {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        background: #22c55e;
        box-shadow: 0 0 0 3px rgba(34, 197, 94, 0.3);
        animation: pulseLive 2s infinite;
    }

    @keyframes pulseLive {
        0% { box-shadow: 0 0 0 0 rgba(34, 197, 94, 0.6); }
        70% { box-shadow: 0 0 0 6px rgba(34, 197, 94, 0); }
        100% { box-shadow: 0 0 0 0 rgba(34, 197, 94, 0); }
    }

    /* Executive KPI Cards */
    .exec-kpi-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        padding: 1rem 1.15rem;
        box-shadow: 0 2px 6px rgba(15, 23, 42, 0.04);
        position: relative;
        overflow: hidden;
        height: 100%;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
    }

    .exec-kpi-card::before {
        content: "";
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
    }

    .exec-kpi-card.accent-amber::before { background: #d97706; }
    .exec-kpi-card.accent-orange::before { background: #ea580c; }
    .exec-kpi-card.accent-cyan::before { background: #0284c7; }
    .exec-kpi-card.accent-green::before { background: #059669; }

    .exec-kpi-label {
        font-size: 0.72rem;
        font-weight: 800;
        letter-spacing: 0.04em;
        text-transform: uppercase;
        color: #475569;
        margin-bottom: 0.25rem;
    }

    .exec-kpi-value {
        font-size: 2.15rem;
        font-weight: 800;
        line-height: 1.1;
        letter-spacing: -0.03em;
        margin-bottom: 0.35rem;
    }

    .exec-kpi-subtext {
        font-size: 0.72rem;
        color: #64748b;
        font-weight: 500;
    }

    /* Executive Analytical Card */
    .exec-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        box-shadow: 0 2px 6px rgba(15, 23, 42, 0.04);
        height: 100%;
        display: flex;
        flex-direction: column;
    }

    .exec-card-header {
        padding: 0.85rem 1.1rem;
        border-bottom: 1px solid #f1f5f9;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .exec-card-title {
        font-size: 0.84rem;
        font-weight: 800;
        letter-spacing: 0.04em;
        text-transform: uppercase;
        color: #0f172a;
        margin: 0;
    }

    .exec-pill {
        border-radius: 999px;
        padding: 0.2rem 0.65rem;
        font-size: 0.72rem;
        font-weight: 700;
        line-height: 1;
    }

    .exec-pill-red {
        background: #fef2f2;
        border: 1px solid #fecaca;
        color: #dc2626;
    }

    .exec-pill-green {
        background: #f0fdf4;
        border: 1px solid #bbf7d0;
        color: #16a34a;
    }

    .exec-pill-cyan {
        background: #f0f9ff;
        border: 1px solid #bae6fd;
        color: #0284c7;
    }

    .exec-pill-blue {
        background: #eff6ff;
        border: 1px solid #bfdbfe;
        color: #2563eb;
    }

    .exec-card-body {
        padding: 1rem 1.1rem;
        flex: 1;
        display: flex;
        flex-direction: column;
        justify-content: center;
    }

    /* 5x5 Heatmap Matrix */
    .matrix-wrapper {
        display: flex;
        flex-direction: column;
        gap: 2px;
    }

    .matrix-row {
        display: flex;
        align-items: center;
        gap: 2px;
    }

    .matrix-y-label {
        width: 82px;
        font-size: 0.62rem;
        font-weight: 600;
        color: #64748b;
        text-align: right;
        padding-right: 6px;
        line-height: 1;
    }

    .matrix-cell {
        flex: 1;
        height: 38px;
        border-radius: 2px;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 2px;
        position: relative;
        transition: transform 0.15s ease;
    }

    .matrix-cell:hover {
        transform: scale(1.08);
        z-index: 5;
        box-shadow: 0 4px 10px rgba(0,0,0,0.18);
    }

    .matrix-marker {
        width: 22px;
        height: 22px;
        border-radius: 50%;
        background: #ffffff;
        color: #0f172a;
        font-size: 0.68rem;
        font-weight: 800;
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 2px 5px rgba(0,0,0,0.25);
    }

    .matrix-x-labels {
        display: flex;
        margin-left: 84px;
        margin-top: 4px;
        gap: 2px;
    }

    .matrix-x-label {
        flex: 1;
        font-size: 0.58rem;
        font-weight: 600;
        color: #64748b;
        text-align: center;
        line-height: 1;
    }

    .matrix-legend {
        font-size: 0.64rem;
        color: #64748b;
        margin-top: 0.65rem;
        text-align: center;
        line-height: 1.4;
    }

    /* YOY Bars */
    .yoy-chart-container {
        display: flex;
        align-items: flex-end;
        justify-content: center;
        gap: 2.2rem;
        height: 160px;
        padding-bottom: 0.5rem;
    }

    .yoy-bar-col {
        display: flex;
        flex-direction: column;
        align-items: center;
        width: 78px;
    }

    .yoy-bar-value {
        font-size: 0.88rem;
        font-weight: 800;
        color: #0f172a;
        margin-bottom: 0.4rem;
    }

    .yoy-bar {
        width: 100%;
        border-radius: 2px;
        transition: height 0.6s ease;
    }

    .yoy-bar.prev-year {
        background: #0284c7;
        height: 110px;
    }

    .yoy-bar.curr-year {
        background: #0369a1;
        height: 70px;
    }

    .yoy-bar-label {
        margin-top: 0.55rem;
        font-size: 0.72rem;
        font-weight: 700;
        color: #475569;
    }

    .yoy-callout {
        color: #16a34a;
        font-size: 0.78rem;
        font-weight: 800;
        text-align: center;
        margin-top: 0.8rem;
    }

    /* Speedometer Gauge */
    .gauge-wrapper {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        position: relative;
    }

    .gauge-score-wrap {
        margin-top: -10px;
        text-align: center;
    }

    .gauge-score-number {
        font-size: 2.3rem;
        font-weight: 800;
        line-height: 1;
        color: #0f172a;
        letter-spacing: -0.04em;
    }

    .gauge-score-total {
        font-size: 0.78rem;
        font-weight: 600;
        color: #64748b;
    }

    .gauge-score-label {
        font-size: 0.68rem;
        font-weight: 600;
        color: #64748b;
        margin-top: 0.15rem;
    }

    /* Top Categories Legend */
    .cat-legend-item {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        font-size: 0.72rem;
        margin-bottom: 0.4rem;
        font-weight: 600;
        color: #334155;
    }

    .cat-legend-dot {
        width: 10px;
        height: 10px;
        border-radius: 2px;
        flex-shrink: 0;
    }

    /* Footer strip */
    .executive-footer-strip {
        font-size: 0.68rem;
        color: #94a3b8;
        padding-top: 0.85rem;
        margin-top: 0.85rem;
        border-top: 1px solid #e2e8f0;
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: gap;
    }
</style>

<div class="executive-dashboard">
    {{-- 1. Dark Top Header Banner --}}
    <div class="executive-header-banner mb-3">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div>
                <h1 class="executive-title">Enterprise Risk Dashboard</h1>
                <div class="executive-subtitle">
                    Q4 {{ now()->year }} | Office of Risk Management &amp; CRO | Board &amp; ExCo Distribution
                </div>
            </div>
            <div class="d-flex align-items-center gap-3">
                <span class="live-status-pill">
                    <span class="live-dot"></span> LIVE
                </span>
            </div>
        </div>
    </div>

    {{-- 2. Top Metric Cards (4 Cards with Colored Top Borders) --}}
    <div class="row g-3 mb-3">
        <div class="col-6 col-xl-3">
            <div class="exec-kpi-card accent-amber">
                <div>
                    <div class="exec-kpi-label">EST. POTENTIAL LOSS</div>
                    <div class="exec-kpi-value" style="color: #d97706;">{{ $estLoss }}</div>
                </div>
                <div class="exec-kpi-subtext">Enterprise Financial Exposure</div>
            </div>
        </div>

        <div class="col-6 col-xl-3">
            <div class="exec-kpi-card accent-orange">
                <div>
                    <div class="exec-kpi-label">OVERALL RISK RATING</div>
                    <div class="exec-kpi-value" style="color: {{ $overallRatingColor }};">{{ $overallRating }}</div>
                </div>
                <div class="exec-kpi-subtext">{{ $overallRatingNote }}</div>
            </div>
        </div>

        <div class="col-6 col-xl-3">
            <div class="exec-kpi-card accent-cyan">
                <div>
                    <div class="exec-kpi-label">OPEN RISK ITEMS</div>
                    <div class="exec-kpi-value" style="color: #0284c7;">{{ $openCount }}</div>
                </div>
                <div class="exec-kpi-subtext">
                    <span class="text-success fw-bold"><i class="fas fa-arrow-down me-1"></i></span> {{ $openSubtext }}
                </div>
            </div>
        </div>

        <div class="col-6 col-xl-3">
            <div class="exec-kpi-card accent-green">
                <div>
                    <div class="exec-kpi-label">CONTROL COVERAGE</div>
                    <div class="exec-kpi-value" style="color: #059669;">{{ number_format($coveragePct, 0) }}%</div>
                </div>
                <div class="exec-kpi-subtext">
                    <span class="text-success fw-bold"><i class="fas fa-arrow-up me-1"></i></span> {{ $coverageSubtext }}
                </div>
            </div>
        </div>
    </div>

    {{-- 3. Middle Section: Risk Matrix | YoY Risk Reduction | Cyber/Enterprise Resilience Score --}}
    <div class="row g-3 mb-3">
        {{-- Matrix --}}
        <div class="col-12 col-lg-4">
            <div class="exec-card">
                <div class="exec-card-header">
                    <h3 class="exec-card-title">RISK MATRIX</h3>
                    <span class="exec-pill exec-pill-red">{{ $highRiskCount }} High</span>
                </div>
                <div class="exec-card-body p-3">
                    <div class="matrix-wrapper">
                        @for($p = 5; $p >= 1; $p--)
                            <div class="matrix-row">
                                <div class="matrix-y-label">{{ $rowLabels[$p] }}</div>
                                @for($i = 1; $i <= 5; $i++)
                                    @php
                                        $cellBg = $cellBgMap[$p][$i] ?? '#16a34a';
                                        $cellInfo = $matrixDetail[$p][$i] ?? ['count' => 0, 'markers' => []];
                                        $cCount = $cellInfo['count'] ?? 0;
                                        $markers = $cellInfo['markers'] ?? [];
                                    @endphp
                                    <div class="matrix-cell" style="background-color: {{ $cellBg }};" title="Prob: {{ $rowLabels[$p] }} | Imp: {{ $colLabels[$i] }} | Risks: {{ $cCount }}">
                                        @if(!empty($markers))
                                            @foreach(array_slice($markers, 0, 2) as $m)
                                                <div class="matrix-marker">{{ $m }}</div>
                                            @endforeach
                                        @elseif($cCount > 0)
                                            <div class="matrix-marker">{{ $cCount }}</div>
                                        @endif
                                    </div>
                                @endfor
                            </div>
                        @endfor
                        <div class="matrix-x-labels">
                            @for($i = 1; $i <= 5; $i++)
                                <div class="matrix-x-label">{{ $colLabels[$i] }}</div>
                            @endfor
                        </div>
                    </div>
                    <div class="matrix-legend">
                        <strong>O</strong> = Operasional &nbsp; <strong>I</strong> = IT/Cyber &nbsp; <strong>F</strong> = Finansial &nbsp; <strong>C</strong> = Kepatuhan &nbsp; <strong>T</strong> = Pihak Ke-3
                    </div>
                </div>
            </div>
        </div>

        {{-- YoY Risk Reduction --}}
        <div class="col-12 col-md-6 col-lg-4">
            <div class="exec-card">
                <div class="exec-card-header">
                    <h3 class="exec-card-title">YOY RISK REDUCTION</h3>
                    <span class="exec-pill exec-pill-green">On Track</span>
                </div>
                <div class="exec-card-body">
                    <div class="yoy-chart-container">
                        <div class="yoy-bar-col">
                            <div class="yoy-bar-value">{{ $yoyPrev }}</div>
                            <div class="yoy-bar prev-year" style="height: 105px;"></div>
                            <div class="yoy-bar-label">FY {{ now()->year - 1 }}</div>
                        </div>
                        <div class="yoy-bar-col">
                            <div class="yoy-bar-value">{{ $yoyCurr }}</div>
                            <div class="yoy-bar curr-year" style="height: {{ max(25, round(($yoyCurr / max(1, $yoyPrev)) * 105)) }}px;"></div>
                            <div class="yoy-bar-label">FY {{ now()->year }}</div>
                        </div>
                    </div>
                    <div class="yoy-callout">
                        <i class="fas fa-arrow-down me-1"></i> {{ $yoyRed }}% reduction in enterprise risk
                    </div>
                </div>
            </div>
        </div>

        {{-- Enterprise Resilience Score --}}
        <div class="col-12 col-md-6 col-lg-4">
            <div class="exec-card">
                <div class="exec-card-header">
                    <h3 class="exec-card-title">ENTERPRISE RESILIENCE SCORE</h3>
                    <span class="exec-pill exec-pill-cyan">{{ $resilienceRating }}</span>
                </div>
                <div class="exec-card-body">
                    <div class="gauge-wrapper">
                        {{-- SVG Semi-Circle Speedometer Gauge --}}
                        <svg width="210" height="115" viewBox="0 0 200 110" style="overflow: visible;">
                            <!-- Segment 1: Red (0-40) -->
                            <path d="M 20 100 A 80 80 0 0 1 42 45" fill="none" stroke="#ef4444" stroke-width="20" stroke-linecap="butt" />
                            <!-- Segment 2: Orange (40-55) -->
                            <path d="M 42 45 A 80 80 0 0 1 75 25" fill="none" stroke="#f97316" stroke-width="20" stroke-linecap="butt" />
                            <!-- Segment 3: Yellow (55-70) -->
                            <path d="M 75 25 A 80 80 0 0 1 125 25" fill="none" stroke="#eab308" stroke-width="20" stroke-linecap="butt" />
                            <!-- Segment 4: Green (70-85) -->
                            <path d="M 125 25 A 80 80 0 0 1 158 45" fill="none" stroke="#22c55e" stroke-width="20" stroke-linecap="butt" />
                            <!-- Segment 5: Blue (85-100) -->
                            <path d="M 158 45 A 80 80 0 0 1 180 100" fill="none" stroke="#0ea5e9" stroke-width="20" stroke-linecap="butt" />

                            <!-- Needle -->
                            <g transform="translate(100, 100) rotate({{ $gaugeAngle }})">
                                <line x1="0" y1="0" x2="0" y2="-68" stroke="#0f172a" stroke-width="3.5" stroke-linecap="round" />
                                <circle cx="0" cy="0" r="6" fill="#0f172a" />
                                <circle cx="0" cy="0" r="2.5" fill="#ffffff" />
                            </g>
                        </svg>

                        <div class="gauge-score-wrap">
                            <div class="gauge-score-number">{{ $resilienceScore }}</div>
                            <div class="gauge-score-total">/100</div>
                            <div class="gauge-score-label">Overall Resilience Index</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- 4. Bottom Section: Top Risk Categories | Risk Posture Over Time --}}
    <div class="row g-3 mb-2">
        {{-- Top Categories --}}
        <div class="col-12 col-lg-5">
            <div class="exec-card">
                <div class="exec-card-header">
                    <h3 class="exec-card-title">TOP RISK CATEGORIES</h3>
                    <span class="exec-pill exec-pill-blue">{{ count($topCatLabels) }} Categories</span>
                </div>
                <div class="exec-card-body">
                    <div class="row align-items-center">
                        <div class="col-7">
                            <div style="height: 170px; position: relative;">
                                <canvas id="execCategoriesPieChart"></canvas>
                            </div>
                        </div>
                        <div class="col-5">
                            @php
                                $pieColors = ['#1d4ed8', '#2563eb', '#38bdf8', '#60a5fa', '#93c5fd', '#cbd5e1'];
                            @endphp
                            <div class="d-flex flex-column justify-content-center">
                                @foreach($topCatLabels as $idx => $catName)
                                    <div class="cat-legend-item">
                                        <span class="cat-legend-dot" style="background-color: {{ $pieColors[$idx % count($pieColors)] }};"></span>
                                        <span class="text-truncate" style="max-width: 90px;" title="{{ $catName }}">{{ $catName }}</span>
                                        <span class="text-muted ms-auto small">{{ $topCatPcts[$idx] ?? 0 }}%</span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Risk Posture Over Time --}}
        <div class="col-12 col-lg-7">
            <div class="exec-card">
                <div class="exec-card-header">
                    <h3 class="exec-card-title">RISK POSTURE OVER TIME</h3>
                    <span class="exec-pill exec-pill-green">Improving</span>
                </div>
                <div class="exec-card-body">
                    <div style="height: 160px; width: 100%;">
                        <canvas id="execPostureLineChart"></canvas>
                    </div>
                    <div class="text-end small text-muted mt-2 fst-italic" style="font-size: 0.66rem;">
                        Composite score based on weighted risk indicators
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- 5. Footer Strip --}}
    <div class="executive-footer-strip">
        <div>CONFIDENTIAL &mdash; Internal Use Only</div>
        <div>Classification: Restricted | Distribution: Board &amp; ExCo</div>
        <div>RPT-{{ now()->year }}-Q4-0847 | Page 1 of 1</div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
<script>
    window.renderExecutiveCharts = function () {
        if (typeof Chart === 'undefined') {
            setTimeout(window.renderExecutiveCharts, 120);
            return;
        }

        // 1. Top Categories Donut Chart
        const catCtx = document.getElementById('execCategoriesPieChart');
        if (catCtx) {
            if (window._execCatChart) {
                try { window._execCatChart.resize(); } catch (e) {}
            } else {
                window._execCatChart = new Chart(catCtx, {
                    type: 'doughnut',
                    data: {
                        labels: @json($topCatLabels),
                        datasets: [{
                            data: @json($topCatPcts),
                            backgroundColor: ['#1d4ed8', '#2563eb', '#38bdf8', '#60a5fa', '#93c5fd', '#cbd5e1'],
                            borderColor: '#ffffff',
                            borderWidth: 2,
                            hoverOffset: 4
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { display: false },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        return ' ' + context.label + ': ' + context.parsed + '%';
                                    }
                                }
                            }
                        },
                        cutout: '48%'
                    }
                });
            }
        }

        // 2. Risk Posture Line Chart
        const postureCtx = document.getElementById('execPostureLineChart');
        if (postureCtx) {
            if (window._execPostureChart) {
                try { window._execPostureChart.resize(); } catch (e) {}
            } else {
                window._execPostureChart = new Chart(postureCtx, {
                    type: 'line',
                    data: {
                        labels: @json($trendLabels),
                        datasets: [{
                            label: 'Risk Posture Score',
                            data: @json($trendValues),
                            borderColor: '#0284c7',
                            backgroundColor: 'rgba(2, 132, 199, 0.08)',
                            borderWidth: 2.5,
                            pointBackgroundColor: '#0284c7',
                            pointBorderColor: '#ffffff',
                            pointBorderWidth: 2,
                            pointRadius: 5,
                            pointHoverRadius: 7,
                            tension: 0.25,
                            fill: false
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { display: false },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        return ' Score: ' + context.parsed.y;
                                    }
                                }
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: false,
                                grid: {
                                    color: '#f1f5f9',
                                    drawBorder: false
                                },
                                ticks: {
                                    display: false
                                }
                            },
                            x: {
                                grid: {
                                    display: false,
                                    drawBorder: false
                                },
                                ticks: {
                                    color: '#64748b',
                                    font: { size: 10, weight: 600 }
                                }
                            }
                        }
                    }
                });
            }
        }
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', window.renderExecutiveCharts);
    } else {
        window.renderExecutiveCharts();
    }
</script>
