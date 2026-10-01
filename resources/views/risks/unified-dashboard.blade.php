@extends('layouts.tabler')

@php
    $requestedView = strtolower((string) request()->query('view', $defaultView ?? 'summary'));
    if (!in_array($requestedView, ['summary', 'monitoring'], true)) {
        $requestedView = 'summary';
    }
@endphp

@section('title', $requestedView === 'summary' ? 'Executive Risk Summary' : 'Operational Risk Monitoring')

@section('css')
<style>
    /* Management / Executive View Tokens */
    .management-dashboard {
        --ink: #12243a;
        --muted: #64748b;
        --line: #dbe5ef;
        --surface: #ffffff;
        --navy: #12385b;
        --blue: #1877b9;
        --teal: #168c83;
        --amber: #d39127;
        --red: #c84b4b;
        color: var(--ink);
    }

    .management-hero {
        position: relative;
        overflow: hidden;
        border-radius: 1rem;
        padding: 1.4rem 1.5rem;
        color: #fff;
        background: linear-gradient(115deg, #102f4f 0%, #155b82 56%, #168c83 100%);
        box-shadow: 0 12px 28px rgba(18, 56, 91, 0.18);
    }

    .management-hero::after {
        content: "";
        position: absolute;
        width: 260px;
        height: 260px;
        right: -70px;
        top: -120px;
        border: 1px solid rgba(255, 255, 255, .18);
        border-radius: 50%;
        box-shadow: 0 0 0 22px rgba(255, 255, 255, .04), 0 0 0 44px rgba(255, 255, 255, .03);
    }

    .management-eyebrow {
        text-transform: uppercase;
        letter-spacing: .13em;
        font-size: .68rem;
        font-weight: 700;
        opacity: .72;
    }

    .management-hero-score { position: relative; z-index: 1; text-align: right; }
    .management-hero-score .score { font-size: 2.8rem; line-height: 1; font-weight: 800; letter-spacing: -.05em; }
    .management-hero-score .label { margin-top: .35rem; font-size: .76rem; color: rgba(255, 255, 255, .75); }

    .management-kpi {
        height: 100%;
        border: 1px solid var(--line);
        border-radius: .8rem;
        background: var(--surface);
        padding: .95rem 1rem;
        box-shadow: 0 5px 16px rgba(18, 36, 58, .045);
    }
    .management-kpi .kpi-label { color: var(--muted); font-size: .73rem; font-weight: 700; text-transform: uppercase; letter-spacing: .055em; }
    .management-kpi .kpi-value { margin-top: .22rem; font-size: 1.8rem; line-height: 1.1; font-weight: 800; color: var(--ink); }
    .management-kpi .kpi-note { margin-top: .35rem; color: var(--muted); font-size: .74rem; }
    .management-kpi.accent-blue { border-top: 3px solid var(--blue); }
    .management-kpi.accent-red { border-top: 3px solid var(--red); }
    .management-kpi.accent-amber { border-top: 3px solid var(--amber); }
    .management-kpi.accent-teal { border-top: 3px solid var(--teal); }

    .management-card {
        height: 100%;
        border: 1px solid var(--line);
        border-radius: .85rem;
        background: var(--surface);
        box-shadow: 0 5px 16px rgba(18, 36, 58, .045);
    }
    .management-card-header { padding: 1rem 1.05rem .7rem; border-bottom: 1px solid #edf2f7; }
    .management-card-header h2 { margin: 0; font-size: .94rem; font-weight: 750; }
    .management-card-header p { margin: .2rem 0 0; color: var(--muted); font-size: .75rem; }
    .management-card-body { padding: 1rem 1.05rem; }

    .posture-meter { display: flex; align-items: center; gap: 1rem; }
    .posture-ring { width: 108px; height: 108px; flex: 0 0 108px; border-radius: 50%; display: grid; place-items: center; }
    .posture-ring-inner { width: 78px; height: 78px; border-radius: 50%; display: grid; place-items: center; background: #fff; text-align: center; }
    .posture-ring-inner strong { display: block; font-size: 1.45rem; line-height: 1; }
    .posture-ring-inner span { color: var(--muted); font-size: .62rem; text-transform: uppercase; }
    .posture-copy strong { display: block; font-size: 1.05rem; }
    .posture-copy span { display: block; margin-top: .32rem; color: var(--muted); font-size: .76rem; line-height: 1.45; }

    .priority-row { margin-bottom: .75rem; }
    .priority-row:last-child { margin-bottom: 0; }
    .priority-head { display: flex; justify-content: space-between; gap: .75rem; margin-bottom: .28rem; font-size: .75rem; }
    .priority-head .name { font-weight: 650; }
    .priority-head .number { color: var(--muted); font-weight: 700; }
    .priority-track { height: 8px; overflow: hidden; border-radius: 999px; background: #edf2f7; }
    .priority-fill { height: 100%; border-radius: inherit; }
    .priority-fill.low { background: #56a86b; }
    .priority-fill.moderate { background: #e0ae42; }
    .priority-fill.high { background: #e47d34; }
    .priority-fill.extreme { background: #c84b4b; }

    .attention-list { display: grid; gap: .6rem; }
    .attention-item { display: flex; align-items: flex-start; gap: .65rem; padding: .62rem .65rem; border: 1px solid #e7edf3; border-radius: .6rem; background: #fbfdff; }
    .attention-dot { width: 8px; height: 8px; flex: 0 0 8px; margin-top: .35rem; border-radius: 50%; background: var(--red); }
    .attention-item.high .attention-dot { background: var(--amber); }
    .attention-item .title { font-size: .78rem; font-weight: 700; line-height: 1.3; }
    .attention-item .meta { margin-top: .18rem; color: var(--muted); font-size: .7rem; }
    .attention-empty { padding: 1rem; text-align: center; color: var(--muted); font-size: .78rem; background: #f8fbfd; border-radius: .55rem; }

    .trend-table { width: 100%; }
    .trend-row { display: grid; grid-template-columns: 70px 1fr 42px; align-items: center; gap: .6rem; margin-bottom: .7rem; }
    .trend-row:last-child { margin-bottom: 0; }
    .trend-label, .trend-value { color: var(--muted); font-size: .72rem; }
    .trend-value { text-align: right; font-weight: 700; color: var(--ink); }
    .trend-track { position: relative; height: 10px; overflow: hidden; border-radius: 999px; background: #edf2f7; }
    .trend-bar { height: 100%; border-radius: inherit; background: linear-gradient(90deg, #4ab8ae, #1877b9); }

    /* Mode Navigation Tabs Bar */
    .dashboard-mode-selector {
        background: #ffffff;
        border: 1px solid #d8e2ed;
        border-radius: 0.75rem;
        padding: 0.4rem;
        box-shadow: 0 2px 8px rgba(18, 36, 58, 0.04);
        display: inline-flex;
        gap: 0.35rem;
    }

    .dashboard-mode-btn {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.5rem 1rem;
        border-radius: 0.55rem;
        font-size: 0.82rem;
        font-weight: 650;
        color: #475569;
        text-decoration: none;
        border: 1px solid transparent;
        transition: all 0.2s ease;
    }

    .dashboard-mode-btn:hover {
        background: #f1f5f9;
        color: #0f172a;
    }

    .dashboard-mode-btn.active {
        background: linear-gradient(135deg, #1e40af 0%, #2563eb 100%);
        color: #ffffff;
        box-shadow: 0 4px 12px rgba(37, 99, 235, 0.25);
    }

    /* Operational Monitoring Widget Styles */
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

    .dashboard-tab-nav {
        grid-column: span 12;
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
    }

    .dashboard-tab-btn {
        border: 1px solid #d1d9e6;
        border-radius: 0.6rem;
        background: #ffffff;
        color: #334155;
        padding: 0.45rem 0.75rem;
        min-width: 190px;
        text-align: left;
        transition: all 0.2s ease;
    }

    .dashboard-tab-btn .tab-title {
        display: block;
        font-size: 0.76rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.03em;
    }

    .dashboard-tab-btn .tab-subtitle {
        display: block;
        font-size: 0.86rem;
        font-weight: 600;
    }

    .dashboard-tab-btn.active {
        border-color: #90a7c5;
        background: linear-gradient(145deg, #ecf3ff 0%, #f8fbff 100%);
        color: #0f172a;
        box-shadow: 0 6px 14px rgba(37, 99, 235, 0.12);
    }

    .dashboard-tab-btn:hover {
        border-color: #b9c7da;
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

    .dashboard-widget-hidden {
        display: none !important;
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

    .metric-inline-label { font-size: 0.79rem; color: #475569; }
    .metric-inline-value { font-weight: 700; color: #0f172a; }

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
    }

    .status-pie-center {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        text-align: center;
    }

    .status-pie-total { font-size: 1.45rem; font-weight: 800; color: #0f172a; }
    .status-pie-label { font-size: 0.7rem; color: #64748b; text-transform: uppercase; }

    .portfolio-donut-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
        gap: 0.65rem;
    }

    @media (min-width: 1200px) {
        .portfolio-donut-grid { grid-template-columns: repeat(5, minmax(0, 1fr)); }
    }

    .portfolio-donut-card {
        border: 1px solid #dde7f4;
        border-radius: 0.65rem;
        background: linear-gradient(150deg, #f8fbff 0%, #ffffff 100%);
        padding: 0.5rem 0.55rem;
        min-width: 0;
        overflow: hidden;
    }

    .portfolio-donut-title {
        font-size: 0.72rem;
        font-weight: 700;
        letter-spacing: 0.02em;
        color: #334155;
        margin-bottom: 0.3rem;
        text-align: center;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .portfolio-donut-shell {
        position: relative;
        width: 96px;
        height: 96px;
        margin: 0 auto 0.35rem;
    }

    .portfolio-mini-donut {
        width: 100%;
        height: 100%;
        border-radius: 50%;
        background: #e2e8f0;
    }

    .portfolio-mini-center {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        text-align: center;
    }

    .portfolio-mini-value { font-size: 0.95rem; font-weight: 800; line-height: 1; color: #0f172a; }
    .portfolio-mini-sub { font-size: 0.62rem; color: #64748b; text-transform: uppercase; }

    .portfolio-legend-item {
        display: flex;
        align-items: center;
        justify-content: space-between;
        font-size: 0.68rem;
        padding: 0.12rem 0;
        color: #475569;
    }

    .portfolio-legend-key {
        display: flex;
        align-items: center;
        gap: 0.3rem;
        min-width: 0;
    }

    .portfolio-legend-key span {
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        max-width: 80px;
    }

    .portfolio-priority-pill {
        border-radius: 999px;
        padding: 0.22rem 0.48rem;
        font-size: 0.72rem;
        font-weight: 700;
        letter-spacing: 0.02em;
        text-transform: uppercase;
        border: 1px solid transparent;
    }

    .portfolio-priority-pill.priority-low { background: #e8f8ed; border-color: #b8ebc7; color: #166534; }
    .portfolio-priority-pill.priority-moderate { background: #fff8db; border-color: #f7e59a; color: #7c5a00; }
    .portfolio-priority-pill.priority-high { background: #fff1e4; border-color: #ffcfa4; color: #9a3412; }
    .portfolio-priority-pill.priority-extreme { background: #fee2e2; border-color: #fecaca; color: #991b1b; }

    .division-risk-board {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 0.7rem;
    }

    .division-risk-card {
        border: 1px solid #d9e5f3;
        border-radius: 0.7rem;
        background: #ffffff;
        box-shadow: 0 8px 18px rgba(15, 23, 42, 0.05);
        overflow: hidden;
    }

    .division-risk-card-head {
        background: linear-gradient(140deg, #0d64c9 0%, #2e8fed 100%);
        color: #ffffff;
        padding: 0.55rem 0.7rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 0.5rem;
    }

    .division-risk-count { font-size: 1.35rem; font-weight: 800; line-height: 1.1; }
    .division-risk-count span { font-size: 0.72rem; font-weight: 600; opacity: 0.92; }
    .division-risk-name { margin-top: 0.15rem; font-size: 0.8rem; font-weight: 700; line-height: 1.2; }
    .division-risk-rank {
        border: 1px solid rgba(255, 255, 255, 0.45);
        border-radius: 999px;
        padding: 0.15rem 0.52rem;
        font-size: 0.72rem;
        font-weight: 700;
    }

    .division-treatment-stats {
        border-top: 1px solid #ebf1f9;
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }

    .division-treatment-item {
        padding: 0.35rem 0.48rem;
        border-right: 1px solid #ebf1f9;
        border-bottom: 1px solid #ebf1f9;
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 0.45rem;
    }

    .division-treatment-item:nth-child(2n) { border-right: none; }
    .division-treatment-item .label { font-size: 0.72rem; color: #64748b; }
    .division-treatment-item .value { font-size: 0.76rem; font-weight: 700; color: #0f172a; }
    .division-treatment-item.score { background: #f8fbff; }

    .risk-priority-dot {
        width: 9px;
        height: 9px;
        border-radius: 999px;
        display: inline-block;
        vertical-align: middle;
        margin-right: 0.35rem;
    }
    .risk-priority-dot.low { background: #4caf50; }
    .risk-priority-dot.moderate { background: #f8d14b; }
    .risk-priority-dot.high { background: #fb8c00; }
    .risk-priority-dot.extreme { background: #ef4444; }

    @media (max-width: 767.98px) {
        .management-hero-score { margin-top: 1.1rem; text-align: left; }
        .management-hero-score .score { font-size: 2.3rem; }
        .posture-meter { align-items: flex-start; }
    }
</style>
@endsection

@section('content')
<div class="management-dashboard">
    {{-- Unified Dashboard Navigation Bar --}}
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3 pb-2 border-bottom">
        <div>
            <div class="dashboard-mode-selector" role="tablist" aria-label="Risk Dashboard View Modes">
                <a
                    href="?view=summary"
                    class="dashboard-mode-btn {{ $requestedView === 'summary' ? 'active' : '' }}"
                    id="btnViewSummary"
                    role="tab"
                    aria-selected="{{ $requestedView === 'summary' ? 'true' : 'false' }}"
                >
                    <i class="fas fa-chart-line"></i>
                    <span>Executive Summary</span>
                </a>
                <a
                    href="?view=monitoring"
                    class="dashboard-mode-btn {{ $requestedView === 'monitoring' ? 'active' : '' }}"
                    id="btnViewMonitoring"
                    role="tab"
                    aria-selected="{{ $requestedView === 'monitoring' ? 'true' : 'false' }}"
                >
                    <i class="fas fa-tachometer-alt"></i>
                    <span>Operational Monitoring</span>
                </a>
            </div>
        </div>

        <div class="d-flex align-items-center gap-2">
            <span class="badge bg-light text-secondary border">
                <i class="far fa-clock me-1"></i> {{ $dashboardSummary['generated_at'] ?? now()->format('Y-m-d H:i:s') }}
            </span>
            @include('reports.export-buttons', [
                'scope' => 'monitoring',
                'scopeLabel' => 'Monitoring Report',
            ])
            @if(!auth()->user()->isSuperadmin())
                <a href="{{ route('risks.create') }}" class="btn btn-success btn-sm with-icon">
                    <i class="fas fa-plus"></i> Add Risk
                </a>
            @endif
        </div>
    </div>

    {{-- Mode 1: Executive Summary View --}}
    <div id="executiveViewContainer" style="{{ $requestedView === 'summary' ? '' : 'display: none;' }}">
        @include('risks.partials.executive-summary', ['dashboardSummary' => $dashboardSummary])
    </div>

    {{-- Mode 2: Operational Monitoring View --}}
    <div id="monitoringViewContainer" style="{{ $requestedView === 'monitoring' ? '' : 'display: none;' }}">
        <div
            id="riskDashboardRoot"
            data-summary-url="{{ route('risks.dashboard-summary') }}"
            data-pref-get-url="{{ route('dashboard-preferences.show') }}"
            data-pref-save-url="{{ route('dashboard-preferences.update') }}"
            data-refresh-ms="30000"
        >
            @include('risks.partials.dashboard-widgets', ['dashboardSummary' => $dashboardSummary])
        </div>
    </div>
</div>

<script id="riskDashboardInitialData" type="application/json">@json($dashboardSummary)</script>
@endsection

@section('js')
<script>
window.switchDashboardView = function (targetView) {
    const summaryContainer = document.getElementById('executiveViewContainer');
    const monitoringContainer = document.getElementById('monitoringViewContainer');
    const btnSummary = document.getElementById('btnViewSummary');
    const btnMonitoring = document.getElementById('btnViewMonitoring');

    if (targetView === 'monitoring') {
        if (summaryContainer) summaryContainer.style.display = 'none';
        if (monitoringContainer) monitoringContainer.style.display = '';
        if (btnSummary) {
            btnSummary.classList.remove('active');
            btnSummary.setAttribute('aria-selected', 'false');
        }
        if (btnMonitoring) {
            btnMonitoring.classList.add('active');
            btnMonitoring.setAttribute('aria-selected', 'true');
        }
    } else {
        if (monitoringContainer) monitoringContainer.style.display = 'none';
        if (summaryContainer) summaryContainer.style.display = '';
        if (btnMonitoring) {
            btnMonitoring.classList.remove('active');
            btnMonitoring.setAttribute('aria-selected', 'false');
        }
        if (btnSummary) {
            btnSummary.classList.add('active');
            btnSummary.setAttribute('aria-selected', 'true');
        }
    }

    try {
        const url = new URL(window.location.href);
        url.searchParams.set('view', targetView);
        window.history.replaceState({}, '', url.toString());
        localStorage.setItem('risk_dashboard_view_mode', targetView);
    } catch (e) {}
};

// Wire client-side tab switching without page reload
document.getElementById('btnViewSummary')?.addEventListener('click', function(e) {
    e.preventDefault();
    window.switchDashboardView('summary');
});

document.getElementById('btnViewMonitoring')?.addEventListener('click', function(e) {
    e.preventDefault();
    window.switchDashboardView('monitoring');
});
</script>

{{-- Include operational widget scripts for live refresh & interactivity --}}
@include('risks.partials.dashboard-widgets-scripts')
@endsection
