@extends('layouts.tabler')

@php
    $rawView = strtolower((string) request()->query('view', $defaultView ?? 'strategic'));
    $requestedView = match ($rawView) {
        'operational', 'monitoring' => 'operational',
        'analytical' => 'analytical',
        default => 'strategic',
    };

    $pageTitle = match ($requestedView) {
        'operational' => 'Operational Risk Command Center',
        'analytical' => 'Analytical Risk Intelligence Center',
        default => 'Strategic Risk Command Center',
    };
@endphp

@section('title', $pageTitle)

@section('css')
<style>
    /* Command Center Design Tokens */
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

    /* 3-Tier Command Center Mode Switcher */
    .dashboard-mode-selector {
        background: #f1f5f9;
        border: 1px solid #cbd5e1;
        border-radius: 0.75rem;
        padding: 0.3rem;
        display: inline-flex;
        gap: 0.25rem;
    }

    .dashboard-mode-btn {
        display: inline-flex;
        align-items: center;
        gap: 0.45rem;
        padding: 0.45rem 0.95rem;
        border-radius: 0.55rem;
        font-size: 0.82rem;
        font-weight: 650;
        color: #475569;
        text-decoration: none;
        border: 1px solid transparent;
        transition: all 0.2s ease;
    }

    .dashboard-mode-btn:hover {
        background: #ffffff;
        color: #0f172a;
    }

    .dashboard-mode-btn.active {
        background: #1e3a8a;
        color: #ffffff;
        box-shadow: 0 4px 12px rgba(30, 58, 138, 0.25);
    }

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

    @media (max-width: 767.98px) {
        .management-hero-score { margin-top: 1.1rem; text-align: left; }
        .management-hero-score .score { font-size: 2.3rem; }
        .posture-meter { align-items: flex-start; }
    }
</style>
@endsection

@section('content')
<div class="management-dashboard">
    {{-- Top Action & Filter Header Bar --}}
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3 pb-2 border-bottom">
        <div>
            <div class="dashboard-mode-selector" role="tablist" aria-label="Risk Command Center Tiers">
                <a
                    href="?view=strategic"
                    class="dashboard-mode-btn {{ $requestedView === 'strategic' ? 'active' : '' }}"
                    id="btnViewStrategic"
                    role="tab"
                    aria-selected="{{ $requestedView === 'strategic' ? 'true' : 'false' }}"
                    title="Executive Board Posture, Risk Appetite & Strategic Governance"
                >
                    <i class="fas fa-chart-line"></i>
                    <span>1. Strategic (Board)</span>
                </a>
                <a
                    href="?view=operational"
                    class="dashboard-mode-btn {{ $requestedView === 'operational' ? 'active' : '' }}"
                    id="btnViewOperational"
                    role="tab"
                    aria-selected="{{ $requestedView === 'operational' ? 'true' : 'false' }}"
                    title="Daily/Weekly Operator Health, KRIs, Overdue SLAs & Incidents"
                >
                    <i class="fas fa-tachometer-alt"></i>
                    <span>2. Operational (Action)</span>
                </a>
                <a
                    href="?view=analytical"
                    class="dashboard-mode-btn {{ $requestedView === 'analytical' ? 'active' : '' }}"
                    id="btnViewAnalytical"
                    role="tab"
                    aria-selected="{{ $requestedView === 'analytical' ? 'true' : 'false' }}"
                    title="Deep Analytical 5x5 Heatmap Matrix, Efficacy Variance & Root Causes"
                >
                    <i class="fas fa-th-large"></i>
                    <span>3. Analytical (Matrix)</span>
                </a>
            </div>
        </div>

        <div class="d-flex align-items-center flex-wrap gap-2">
            {{-- Quick Filter Form --}}
            <form method="GET" action="{{ url()->current() }}" class="d-flex align-items-center gap-1" id="dashboardFilterForm">
                <input type="hidden" name="view" value="{{ $requestedView }}" id="filterFormViewInput">
                <select name="risk_category_id" class="form-select form-select-sm" onchange="this.form.submit()" style="max-width: 170px;">
                    <option value="">All Risk Categories</option>
                    @foreach($categories ?? [] as $category)
                        <option value="{{ $category->id }}" {{ (string) request('risk_category_id') === (string) $category->id ? 'selected' : '' }}>
                            {{ $category->name }}
                        </option>
                    @endforeach
                </select>
                <select name="risk_cause_category_id" class="form-select form-select-sm" onchange="this.form.submit()" style="max-width: 160px;">
                    <option value="">All Cause Categories</option>
                    @foreach($causeCategories ?? [] as $causeCat)
                        <option value="{{ $causeCat->id }}" {{ (string) request('risk_cause_category_id') === (string) $causeCat->id ? 'selected' : '' }}>
                            {{ $causeCat->name }}
                        </option>
                    @endforeach
                </select>
                @if(request('risk_category_id') || request('risk_cause_category_id'))
                    <a href="?view={{ $requestedView }}" class="btn btn-outline-secondary btn-sm" title="Clear Filters">
                        <i class="fas fa-times"></i>
                    </a>
                @endif
            </form>

            <span class="badge bg-light text-secondary border d-none d-md-inline-block">
                <i class="far fa-clock me-1"></i> {{ $dashboardSummary['generated_at'] ?? now()->format('Y-m-d H:i') }}
            </span>

            @include('reports.export-buttons', [
                'scope' => 'monitoring',
                'scopeLabel' => 'Executive Report',
            ])

            @if(auth()->check() && !auth()->user()->isSuperadmin())
                <a href="{{ route('risks.create') }}" class="btn btn-success btn-sm with-icon">
                    <i class="fas fa-plus"></i> Add Risk
                </a>
            @endif
        </div>
    </div>

    {{-- Tier 1: Strategic Command Center (Board & Executive View) --}}
    <div id="strategicViewContainer" style="{{ $requestedView === 'strategic' ? '' : 'display: none;' }}">
        @include('risks.partials.strategic-dashboard', [
            'dashboardSummary' => $dashboardSummary,
            'strategicDecisions' => $strategicDecisions ?? [],
            'riskAppetites' => $riskAppetites ?? [],
        ])
    </div>

    {{-- Tier 2: Operational Command Center (Risk Operators & Department Heads) --}}
    <div id="operationalViewContainer" style="{{ $requestedView === 'operational' ? '' : 'display: none;' }}">
        @include('risks.partials.operational-dashboard', [
            'dashboardSummary' => $dashboardSummary,
            'operationalKriAlerts' => $operationalKriAlerts ?? [],
            'operationalIncidents' => $operationalIncidents ?? [],
        ])
    </div>

    {{-- Tier 3: Analytical Intelligence Center (5x5 Heatmap Matrix & Deep Analytics) --}}
    <div id="analyticalViewContainer" style="{{ $requestedView === 'analytical' ? '' : 'display: none;' }}">
        @include('risks.partials.analytical-dashboard', [
            'dashboardSummary' => $dashboardSummary,
            'heatmapMatrix' => $heatmapMatrix ?? [],
        ])
    </div>
</div>

<script id="riskDashboardInitialData" type="application/json">@json($dashboardSummary)</script>
@endsection

@section('js')
<script>
window.switchDashboardView = function (targetView) {
    const strategicContainer = document.getElementById('strategicViewContainer');
    const operationalContainer = document.getElementById('operationalViewContainer');
    const analyticalContainer = document.getElementById('analyticalViewContainer');

    const btnStrategic = document.getElementById('btnViewStrategic');
    const btnOperational = document.getElementById('btnViewOperational');
    const btnAnalytical = document.getElementById('btnViewAnalytical');
    const filterViewInput = document.getElementById('filterFormViewInput');

    // Normalize target view name
    if (targetView === 'summary') targetView = 'strategic';
    if (targetView === 'monitoring') targetView = 'operational';

    // Update containers
    if (strategicContainer) strategicContainer.style.display = (targetView === 'strategic') ? '' : 'none';
    if (operationalContainer) operationalContainer.style.display = (targetView === 'operational') ? '' : 'none';
    if (analyticalContainer) analyticalContainer.style.display = (targetView === 'analytical') ? '' : 'none';

    // Update buttons
    [
        { btn: btnStrategic, view: 'strategic' },
        { btn: btnOperational, view: 'operational' },
        { btn: btnAnalytical, view: 'analytical' }
    ].forEach(item => {
        if (item.btn) {
            if (item.view === targetView) {
                item.btn.classList.add('active');
                item.btn.setAttribute('aria-selected', 'true');
            } else {
                item.btn.classList.remove('active');
                item.btn.setAttribute('aria-selected', 'false');
            }
        }
    });

    if (filterViewInput) {
        filterViewInput.value = targetView;
    }

    try {
        const url = new URL(window.location.href);
        url.searchParams.set('view', targetView);
        window.history.replaceState({}, '', url.toString());
        localStorage.setItem('risk_dashboard_view_mode', targetView);
    } catch (e) {}
};

// Wire client-side tab switching without page reload
document.getElementById('btnViewStrategic')?.addEventListener('click', function(e) {
    e.preventDefault();
    window.switchDashboardView('strategic');
});

document.getElementById('btnViewOperational')?.addEventListener('click', function(e) {
    e.preventDefault();
    window.switchDashboardView('operational');
});

document.getElementById('btnViewAnalytical')?.addEventListener('click', function(e) {
    e.preventDefault();
    window.switchDashboardView('analytical');
});
</script>
@endsection
