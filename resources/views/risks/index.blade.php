@extends('layouts.tabler')

@section('title', 'Risk Register')

@section('content')

{{-- DATATABLE CSS --}}
<link rel="stylesheet" href="{{ asset('vendor/datatables/css/dataTables.bootstrap5.min.css') }}">

@if(session('import_output'))
    <div class="alert alert-info">
        <strong>Import Output</strong>
        <pre class="mb-0 mt-2" style="white-space: pre-wrap;">{{ session('import_output') }}</pre>
    </div>
@endif

<style>
.risk-header {
    background-color: #d69b72;
    color: white;
    font-weight: bold;
    text-align: center;
}

.analysis-header {
    background-color: #9fbf8a;
    color: white;
    font-weight: bold;
    text-align: center;
}

table th {
    text-align: center;
    vertical-align: middle !important;
    font-size: 13px;
}

table td {
    vertical-align: middle;
    font-size: 13px;
    line-height: 1.35;
}

.btn-sm {
    padding: 0.25rem 0.5rem;
    font-size: 12px;
}

.risk-toolbar {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 0.5rem;
    justify-content: flex-end;
}

.risk-toolbar .btn.with-icon {
    display: inline-flex;
    align-items: center;
    gap: 0.38rem;
    border-radius: 0.45rem;
    padding: 0.36rem 0.72rem;
    font-weight: 600;
}

.risk-toolbar .btn.with-icon i {
    margin: 0;
    line-height: 1;
}

.risk-summary-shell {
    border: 1px solid #d8e2ef;
    border-radius: 0.85rem;
    background: linear-gradient(165deg, #f8fbff 0%, #f3f7fc 100%);
    padding: 0.85rem;
    margin-bottom: 0.9rem;
}

.risk-summary-shell .small-box {
    border-radius: 0.75rem;
    box-shadow: 0 8px 18px rgba(15, 23, 42, 0.08);
}

.risk-tab-panels {
    margin-top: 0.35rem;
}

.risk-tab-panel {
    display: none;
}

.risk-tab-panel.active {
    display: block;
    animation: riskTabFadeIn 0.18s ease;
}

@keyframes riskTabFadeIn {
    from {
        opacity: 0;
        transform: translateY(4px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.table-controls-panel {
    background: #f8fafc;
    border: 1px solid #dbe7f3;
    border-radius: 0.65rem;
    padding: 0.85rem 0.95rem;
    margin-bottom: 0.95rem;
}

.table-controls-grid {
    display: grid;
    grid-template-columns: minmax(260px, 360px) minmax(360px, 1fr);
    gap: 0.9rem 1rem;
    align-items: end;
}

.table-control-block {
    display: flex;
    flex-direction: column;
}

.table-control-block .control-label {
    display: inline-flex;
    align-items: center;
    gap: 0.42rem;
    font-size: 0.82rem;
    font-weight: 700;
    color: #334155;
    letter-spacing: 0.02em;
    margin-bottom: 0.38rem;
    text-transform: uppercase;
}

.table-control-block .control-label i {
    color: #2563eb;
}

.table-control-body {
    min-height: 44px;
    display: flex;
    align-items: center;
}

.table-toolbar-body {
    justify-content: flex-end;
}

#ownerFilter {
    width: 100%;
}

.column-preset-group {
    display: flex;
    flex-wrap: wrap;
    gap: 0.45rem;
    align-items: center;
    min-height: 44px;
}

.column-preset-group .btn {
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
    border-radius: 999px;
    padding: 0.3rem 0.72rem;
    min-height: 36px;
    font-weight: 600;
}

.column-preset-group .btn.active {
    box-shadow: none;
}

.column-preset-group .btn i {
    margin: 0;
    line-height: 1;
}

.risk-table-card-header {
    background: #ffffff;
    border-bottom: 1px solid #e2e8f0;
    padding: 0.85rem 1rem;
}

.risk-table-title {
    margin: 0;
    font-size: 1.12rem;
    font-weight: 700;
    color: #0f172a;
}

thead th {
    position: sticky;
    top: 0;
    z-index: 2;
}

#riskTable_wrapper .dataTables_filter {
    text-align: right;
}

#riskTable_wrapper .dataTables_filter label {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
}

#riskTable_wrapper .dataTables_length label {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    white-space: nowrap;
}

#riskTable_wrapper .dataTables_length select {
    width: auto;
    min-width: 82px;
}

#riskTable_wrapper .dataTables_filter input {
    margin-left: 0 !important;
    min-width: 220px;
}

#riskTable_wrapper .dataTables_length,
#riskTable_wrapper .dataTables_filter {
    margin-bottom: 0.75rem;
}

.chip {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 30px;
    padding: 0.2rem 0.55rem;
    border-radius: 999px;
    font-size: 12px;
    font-weight: 700;
    line-height: 1.2;
    white-space: nowrap;
}

.chip-score {
    background: #dbeafe;
    color: #1d4ed8;
}

.chip-score-risk {
    background: #fee2e2;
    color: #b91c1c;
}

.chip-score-adj {
    background: #e2e8f0;
    color: #334155;
}

.chip-radar {
    background: #1e293b;
    color: #f8fafc;
}

.chip-level-adjust {
    background: #e0f2fe;
    color: #0369a1;
}

.chip-kri {
    text-decoration: none;
    border: 1px solid transparent;
    font-size: 10px;
    padding: 0.2rem 0.45rem;
}

.chip-kri:hover {
    text-decoration: none;
    color: inherit;
    opacity: 0.92;
}

.chip-kri-ok {
    background: #dcfce7;
    color: #166534;
    border-color: #86efac;
}

.chip-kri-watch {
    background: #fef3c7;
    color: #92400e;
    border-color: #fcd34d;
}

.chip-kri-alert {
    background: #fee2e2;
    color: #b91c1c;
    border-color: #fca5a5;
}

.chip-kri-none {
    background: #e5e7eb;
    color: #374151;
    border-color: #cbd5e1;
}

.kri-breakdown {
    font-size: 10px;
    color: #64748b;
    font-weight: 600;
    letter-spacing: 0.01em;
}
.risk-level-pill,
.status-pill {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 0.28rem 0.58rem;
    border-radius: 0.35rem;
    font-size: 11px;
    font-weight: 700;
    line-height: 1.2;
    white-space: nowrap;
}

.risk-level-pill.low {
    background: #dcfce7;
    color: #166534;
}

.risk-level-pill.low-moderate {
    background: #cffafe;
    color: #0e7490;
}

.risk-level-pill.moderate {
    background: #fef3c7;
    color: #92400e;
}

.risk-level-pill.moderate-high {
    background: #ffedd5;
    color: #c2410c;
}

.risk-level-pill.high {
    background: #fee2e2;
    color: #b91c1c;
}

.risk-level-pill.unknown {
    background: #e5e7eb;
    color: #374151;
}

.status-pill.open {
    background: #fee2e2;
    color: #b91c1c;
}

.status-pill.monitoring {
    background: #fef3c7;
    color: #92400e;
}

.status-pill.closed {
    background: #dcfce7;
    color: #166534;
}

.risk-action-stack {
    display: inline-flex;
    flex-direction: column;
    align-items: center;
    gap: 0.3rem;
}

#riskTable_wrapper .dataTables_paginate .pagination {
    display: inline-flex !important;
    align-items: center;
    gap: 0.35rem;
    margin: 0;
    padding-left: 0;
}

#riskTable_wrapper .dataTables_paginate .page-item {
    float: none !important;
}

#riskTable_wrapper .dataTables_paginate .page-item .page-link {
    border-radius: 0.35rem;
    padding: 0.3rem 0.55rem;
    margin: 0 !important;
}

#riskTable_wrapper .dataTables_paginate .page-item + .page-item .page-link {
    margin-left: 0 !important;
}

/* Fallback for non-bootstrap pagination renderer */
#riskTable_wrapper .dataTables_paginate {
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
    white-space: nowrap;
}

#riskTable_wrapper .dataTables_paginate > a,
#riskTable_wrapper .dataTables_paginate a.paginate_button {
    display: inline-flex !important;
    align-items: center;
    justify-content: center;
    min-width: 2rem;
    padding: 0.3rem 0.55rem !important;
    margin: 0 0.22rem !important;
    border-radius: 0.35rem;
}

#riskTable_wrapper .dataTables_paginate span {
    display: inline-flex !important;
    align-items: center;
    gap: 0.35rem;
}

#riskTable_wrapper .dataTables_paginate span .paginate_button {
    margin: 0 !important;
}

#riskTable_wrapper .dataTables_paginate .paginate_button.previous,
#riskTable_wrapper .dataTables_paginate .page-item.previous .page-link {
    margin-right: 0.4rem !important;
}

#riskTable_wrapper .dataTables_paginate .paginate_button.next,
#riskTable_wrapper .dataTables_paginate .page-item.next .page-link {
    margin-left: 0.4rem !important;
}

@media (max-width: 768px) {
    .table-controls-grid {
        grid-template-columns: 1fr;
    }

    .table-control-body,
    .column-preset-group {
        min-height: 0;
    }

    .column-preset-group .btn {
        padding: 0.28rem 0.64rem;
    }

    .risk-table-card-header {
        flex-direction: column;
        align-items: flex-start !important;
        gap: 0.7rem;
    }

    .risk-toolbar {
        width: 100%;
        justify-content: flex-start;
    }

    #riskTable_wrapper .dataTables_filter {
        text-align: left;
    }

    #riskTable_wrapper .dataTables_filter input {
        min-width: 160px;
    }

    #riskTable_wrapper .dataTables_paginate {
        margin-top: 0.5rem;
        text-align: left;
    }
}
</style>

{{-- SUMMARY --}}
@php
    $levelCounts = [
        'Low' => 0,
        'Low to Moderate' => 0,
        'Moderate' => 0,
        'Moderate to High' => 0,
        'High' => 0,
        'Extreme' => 0,
    ];

    $kriSummary = [
        'total' => 0,
        'green' => 0,
        'amber' => 0,
        'red' => 0,
        'risks_without_kri' => 0,
    ];

    foreach ($risks as $risk) {
        $level = optional($risk->residualAnalysis)->risk_level ?? $risk->risk_level;
        if (isset($levelCounts[$level])) {
            $levelCounts[$level]++;
        }

        $kriTotal = (int) ($risk->kris_count ?? 0);
        $kriGreen = (int) ($risk->kris_green_count ?? 0);
        $kriAmber = (int) ($risk->kris_amber_count ?? 0);
        $kriRed = (int) ($risk->kris_red_count ?? 0);

        $kriSummary['total'] += $kriTotal;
        $kriSummary['green'] += $kriGreen;
        $kriSummary['amber'] += $kriAmber;
        $kriSummary['red'] += $kriRed;

        if ($kriTotal === 0) {
            $kriSummary['risks_without_kri']++;
        }
    }

    $risksWithKri = max(0, $risks->count() - $kriSummary['risks_without_kri']);
    $kriCoveragePct = $risks->count() > 0
        ? round(($risksWithKri / $risks->count()) * 100, 1)
        : 0;
@endphp

<div class="risk-summary-shell">
<div class="mb-2">
    <h2 class="h4 mb-1">Risk</h2>
    <small class="text-muted">Last refresh: {{ now()->format('Y-m-d H:i:s') }}</small>
</div>

<div class="row g-2 mb-3">
    <div class="col-6 col-sm-4 col-md-2">
        <div class="card card-sm border-0 shadow-sm" style="border-left: 3px solid #206bc4 !important;">
            <div class="card-body p-2 px-3">
                <div class="d-flex align-items-center">
                    <div class="subheader text-muted">Total Risks</div>
                </div>
                <div class="h2 mb-0 fw-bold text-primary">{{ $risks->count() }}</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-sm-4 col-md-2">
        <div class="card card-sm border-0 shadow-sm" style="border-left: 3px solid #2fb344 !important;">
            <div class="card-body p-2 px-3">
                <div class="d-flex align-items-center">
                    <div class="subheader text-success">Low</div>
                </div>
                <div class="h2 mb-0 fw-bold text-success">{{ $levelCounts['Low'] ?? 0 }}</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-sm-4 col-md-2">
        <div class="card card-sm border-0 shadow-sm" style="border-left: 3px solid #17a2b8 !important;">
            <div class="card-body p-2 px-3">
                <div class="d-flex align-items-center">
                    <div class="subheader text-info">Low-Mod</div>
                </div>
                <div class="h2 mb-0 fw-bold text-info">{{ $levelCounts['Low to Moderate'] ?? 0 }}</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-sm-4 col-md-2">
        <div class="card card-sm border-0 shadow-sm" style="border-left: 3px solid #f59f00 !important;">
            <div class="card-body p-2 px-3">
                <div class="d-flex align-items-center">
                    <div class="subheader text-warning">Moderate</div>
                </div>
                <div class="h2 mb-0 fw-bold text-warning">{{ $levelCounts['Moderate'] ?? 0 }}</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-sm-4 col-md-2">
        <div class="card card-sm border-0 shadow-sm" style="border-left: 3px solid #fd7e14 !important;">
            <div class="card-body p-2 px-3">
                <div class="d-flex align-items-center">
                    <div class="subheader" style="color: #fd7e14;">Mod-High</div>
                </div>
                <div class="h2 mb-0 fw-bold" style="color: #fd7e14;">{{ $levelCounts['Moderate to High'] ?? 0 }}</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-sm-4 col-md-2">
        <div class="card card-sm border-0 shadow-sm" style="border-left: 3px solid #d63939 !important;">
            <div class="card-body p-2 px-3">
                <div class="d-flex align-items-center">
                    <div class="subheader text-danger">High / Extreme</div>
                </div>
                <div class="h2 mb-0 fw-bold text-danger">{{ ($levelCounts['High'] ?? 0) + ($levelCounts['Extreme'] ?? 0) }}</div>
            </div>
        </div>
    </div>
</div>
</div>

@include('risks.partials.register-nav-tabs', [
    'workspaceMode' => true,
    'activeRiskTab' => $activeRiskTab ?? 'register',
])

<div class="risk-tab-panels">
<div class="risk-tab-panel active" data-risk-tab-panel="register">

{{-- FILTER --}}
<div class="table-controls-panel">
    <div class="table-controls-grid">
        <div class="table-control-block">
            <div class="control-label">
                <i class="fas fa-filter"></i> Filter Owner
            </div>
            <div class="table-control-body">
                @php
                    $ownerFilterOptions = $ownerFilterOptions ?? collect();
                    $selectedOwnerFilter = $selectedOwnerFilter ?? trim((string) request('risk_owner', ''));
                @endphp
                <form method="GET" action="{{ route('risks.index') }}" class="w-100 m-0">
                    <input type="hidden" name="risk_tab" value="register">
                    @if(request()->filled('risk_category_id'))
                        <input type="hidden" name="risk_category_id" value="{{ request('risk_category_id') }}">
                    @endif
                    @if(request()->filled('risk_cause_category_id'))
                        <input type="hidden" name="risk_cause_category_id" value="{{ request('risk_cause_category_id') }}">
                    @endif
                    @if(request()->filled('year'))
                        <input type="hidden" name="year" value="{{ request('year') }}">
                    @endif
                    <select id="ownerFilter" name="risk_owner" class="form-control" onchange="this.form.submit()">
                        <option value="">All Owner</option>

                        @foreach($ownerFilterOptions as $owner)
                        <option value="{{ $owner }}" {{ $selectedOwnerFilter === (string) $owner ? 'selected' : '' }}>{{ $owner }}</option>
                        @endforeach

                    </select>
                </form>
            </div>
        </div>

        <div class="table-control-block">
            <div class="table-control-body table-toolbar-body">
                <div class="risk-toolbar">
                    @include('reports.export-buttons', [
                        'scope' => 'risks',
                        'scopeLabel' => 'Risk Register',
                    ])
                    @if(!auth()->user()->isSuperadmin() && auth()->user()->isComplianceDivision())
                        <button
                            type="button"
                            class="btn btn-dark btn-sm with-icon"
                            data-bs-toggle="modal"
                            data-bs-target="#riskImportModal"
                            data-toggle="modal"
                            data-target="#riskImportModal"
                        >
                            <i class="fas fa-file-import"></i> Import XLS
                        </button>
                    @endif
                    <a href="{{ route('risks.monitoring') }}" class="btn btn-dark btn-sm with-icon">
                        <i class="fas fa-chart-line"></i> Monitoring
                    </a>
                    @if(!auth()->user()->isSuperadmin())
                        <a href="{{ route('risks.create') }}" class="btn btn-primary btn-sm with-icon">
                            <i class="fas fa-plus"></i> Add Risk
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

{{-- TABLE --}}
<div class="card shadow">

<div class="card-header risk-table-card-header d-flex justify-content-between align-items-center">
<div>
    <h3 class="risk-table-title">Risk Register Table</h3>
</div>

<div class="column-preset-group">
    <button type="button" class="btn btn-sm btn-outline-primary active" data-column-preset="overview">
        <i class="fas fa-table"></i> Overview
    </button>
    <button type="button" class="btn btn-sm btn-outline-primary" data-column-preset="full">
        <i class="fas fa-th-large"></i> Full Table
    </button>
    <button type="button" class="btn btn-sm btn-outline-primary" data-column-preset="analysis">
        <i class="fas fa-chart-line"></i> Analysis
    </button>
    <button type="button" class="btn btn-sm btn-outline-primary" data-column-preset="treatment">
        <i class="fas fa-stethoscope"></i> Treatment
    </button>
    <button type="button" class="btn btn-sm btn-outline-primary" data-column-preset="quantitative">
        <i class="fas fa-calculator"></i> Quantitative
    </button>
</div>
</div>

<div class="card-body table-responsive">
<table id="riskTable" class="table table-hover table-bordered table-sm">

<thead>

<tr>
<th colspan="2" class="text-center column-group-head" data-start="0" data-end="1" style="background:#f8f9fa">STATUS & ACTIONS</th>
<th colspan="5" class="text-center column-group-head" data-start="2" data-end="6" style="background:#e3e6f0">BASIC INFO</th>
<th colspan="5" class="risk-header column-group-head" data-start="7" data-end="11">RISK</th>
<th colspan="6" class="analysis-header column-group-head" data-start="12" data-end="17">INHERENT ANALYSIS</th>
<th colspan="6" class="text-center column-group-head" data-start="18" data-end="23" style="background:#fff3cd">TREATMENT</th>
<th colspan="6" class="text-center column-group-head" data-start="24" data-end="29" style="background:#d1e7dd">RESIDUAL ANALYSIS</th>
<th colspan="2" class="text-center column-group-head" data-start="30" data-end="31" style="background:#cfe2ff">RISK CONCLUSION</th>
<th colspan="2" class="text-center column-group-head" data-start="32" data-end="33" style="background:#e8f5e9">GROUP RADAR</th>
<th colspan="6" class="text-center column-group-head" data-start="34" data-end="39" style="background:#e7d9c5">QUANTITATIVE RESULT</th>
</tr>

<tr>
<th>Status</th>
<th>Actions</th>
<th>Risk ID</th>
<th>Year</th>
<th>Process</th>
<th>Sasaran Target</th>
<th>Risk Identification</th>

<th>Category</th>
<th>Cause Category</th>
<th>Cause</th>
<th>Control</th>
<th>Owner</th>

<th>Prob Desc</th>
<th>Prob Score</th>
<th>Impact Desc</th>
<th>Impact Score</th>
<th>Inherent Risk</th>
<th>Risk Level</th>

<th>Treatment</th>
<th>Description</th>
<th>PIC</th>
<th>Deadline</th>
<th>Effectiveness</th>
<th>Effectiveness Score</th>

<th>Prob Desc</th>
<th>Prob Score</th>
<th>Impact Desc</th>
<th>Impact Score</th>
<th>Residual Risk</th>
<th>Residual Risk Adjustment</th>
<th>Risk Level</th>

<th>Level Adjustment</th>

<th>Code</th>
<th>Description</th>
<th>Inherent Impact (Rp)</th>
<th>Inherent Probability</th>
<th>Inherent Potential Loss (Rp)</th>
<th>Residual Impact (Rp)</th>
<th>Residual Probability</th>
<th>Residual Potential Loss (Rp)</th>
</tr>

</thead>

<tbody>
@foreach($risks as $risk)
@php
    $ownerLabel = \App\Support\RiskOwnerAccess::displayLabel($risk->risk_owner);
    $ownerFilterKey = strtolower(trim(preg_replace('/\s+/u', ' ', (string) $ownerLabel)));
@endphp
<tr data-owner-filter="{{ $ownerFilterKey }}" data-owner="{{ $ownerLabel }}">

{{-- STATUS & ACTIONS --}}
<td>
@php
    $statusClass = match($risk->status) {
        'Open' => 'open',
        'Monitoring' => 'monitoring',
        'Closed' => 'closed',
        default => 'monitoring',
    };
@endphp
<span class="status-pill {{ $statusClass }}">
    {{ $risk->status }}
</span>
</td>

<td style="white-space: nowrap; text-align: center;">
@php
    $viewer = auth()->user();
    // Mirrors RiskAccess::canManage (creator or owner-unit scope, which includes
    // Compliance), with the RBAC permission resolved once for the whole list.
    $viewerHasManagePermission ??= !$viewer->hasRoleAssignments() || $viewer->hasPermission('risks.manage');
    $canManage = !$viewer->isSuperadmin()
        && $viewerHasManagePermission
        && ((int) ($risk->created_by ?? 0) === (int) $viewer->id || $viewer->canManageRiskOwner($risk->risk_owner));
@endphp
@php
    $kriTotal = (int) ($risk->kris_count ?? 0);
    $kriGreenCount = (int) ($risk->kris_green_count ?? 0);
    $kriAmberCount = (int) ($risk->kris_amber_count ?? 0);
    $kriRedCount = (int) ($risk->kris_red_count ?? 0);
    $kriChipClass = $kriTotal === 0
        ? 'chip-kri-none'
        : ($kriRedCount > 0 ? 'chip-kri-alert' : ($kriAmberCount > 0 ? 'chip-kri-watch' : 'chip-kri-ok'));
@endphp
<div class="risk-action-stack">
    <a href="{{ route('risks.show', $risk->id) }}" class="btn btn-info btn-xs" title="View" data-toggle="tooltip">
    <i class="fas fa-eye"></i>
    </a>
    @if($canManage)
        <a href="{{ route('risks.edit', $risk->id) }}" class="btn btn-warning btn-xs" title="Edit" data-toggle="tooltip">
        <i class="fas fa-edit"></i>
        </a>
        <button type="button" class="btn btn-danger btn-xs" onclick="if(confirm('Are you sure you want to permanently delete risk [{{ $risk->risk_id }}] - {{ addslashes($risk->risk_title) }}?\n\nWarning: Associated treatments, KRI links, and historical analyses will be removed.')) { document.getElementById('form-delete-{{ $risk->id }}').submit(); }" title="Delete Risk {{ $risk->risk_id }}" aria-label="Delete Risk {{ $risk->risk_id }}" data-toggle="tooltip">
        <i class="fas fa-trash"></i>
        </button>
    @endif
    <a href="{{ route('risks.show', $risk->id) }}#kris" class="chip chip-kri {{ $kriChipClass }}" title="Open KRI">
        KRI {{ $kriTotal }}
    </a>
    @if($kriTotal > 0)
        <span class="kri-breakdown">G:{{ $kriGreenCount }} A:{{ $kriAmberCount }} R:{{ $kriRedCount }}</span>
    @endif
</div>
@if($canManage)
    <form id="form-delete-{{ $risk->id }}" action="{{ route('risks.destroy', $risk->id) }}" method="POST" style="display:none;">
    @csrf
    @method('DELETE')
    </form>
@endif
</td>

{{-- BASIC INFO --}}
<td>
<a href="{{ route('risks.show', $risk->id) }}" class="font-weight-bold text-primary">
{{ $risk->risk_id }}
</a>
</td>
<td>{{ $risk->year }}</td>
<td>{{ $risk->risk_title }}</td>
<td>{{ $risk->sasaran_target }}</td>
<td>{{ $risk->risk_description }}</td>

{{-- RISK --}}
<td>{{ optional($risk->category)->name }}</td>
<td>{{ optional($risk->causeCategory)->name }}</td>
<td>{{ $risk->cause }}</td>
<td>{{ $risk->existing_control }}</td>
<td>{{ $ownerLabel }}</td>

{{-- INHERENT ANALYSIS --}}
<td>
@if($risk->inherentAnalysis)
    {{ $risk->inherentAnalysis->probability_description }}
@else
    <span class="text-muted">-</span>
@endif
</td>
<td>
@if($risk->inherentAnalysis)
    <span class="chip chip-score">{{ $risk->inherentAnalysis->probability_score }}</span>
@else
    <span class="text-muted">-</span>
@endif
</td>
<td>
@if($risk->inherentAnalysis)
    {{ $risk->inherentAnalysis->impact_description }}
@else
    <span class="text-muted">-</span>
@endif
</td>
<td>
@if($risk->inherentAnalysis)
    <span class="chip chip-score">{{ $risk->inherentAnalysis->impact_score }}</span>
@else
    <span class="text-muted">-</span>
@endif
</td>
<td>
@if($risk->inherentAnalysis)
    <span class="chip chip-score-risk">{{ $risk->inherentAnalysis->inherent_risk_score }}</span>
@else
    <span class="text-muted">-</span>
@endif
</td>
<td>
@if($risk->inherentAnalysis)
    @php
        $inherentLevelClass = match($risk->inherentAnalysis->risk_level){
            'Low' => 'low',
            'Low to Moderate' => 'low-moderate',
            'Moderate' => 'moderate',
            'Moderate to High' => 'moderate-high',
            'High' => 'high',
            default => 'unknown'
        };
    @endphp
    <span class="risk-level-pill {{ $inherentLevelClass }}">
        {{ $risk->inherentAnalysis->risk_level }}
    </span>
@else
    <span class="text-muted">-</span>
@endif
</td>

{{-- TREATMENT (First Treatment or Summary) --}}
@php
    $firstTreatment = $risk->treatments->first();
@endphp
<td>
@if($firstTreatment)
    <strong>{{ $firstTreatment->option }}</strong>
    @if($risk->treatments->count() > 1)
        <br><small class="text-muted">+{{ $risk->treatments->count() - 1 }} more</small>
    @endif
@else
    <span class="text-muted">-</span>
@endif
</td>
<td>
@if($firstTreatment)
    {{ Str::limit($firstTreatment->description, 30) }}
@else
    <span class="text-muted">-</span>
@endif
</td>
<td>
@if($firstTreatment)
    {{ $firstTreatment->pic }}
@else
    <span class="text-muted">-</span>
@endif
</td>
<td>
@if($firstTreatment)
    {{ \Carbon\Carbon::parse($firstTreatment->deadline)->format('d-m-Y') }}
@else
    <span class="text-muted">-</span>
@endif
</td>
<td>
@if($firstTreatment)
    <span class="chip chip-score">{{ $firstTreatment->effectiveness }}</span>
@else
    <span class="text-muted">-</span>
@endif
</td>
<td>
@if($firstTreatment)
    <span class="chip chip-score-adj">{{ $firstTreatment->effectiveness_score }}</span>
@else
    <span class="text-muted">-</span>
@endif
</td>

{{-- RESIDUAL ANALYSIS --}}
<td>
@if($risk->residualAnalysis)
    {{ $risk->residualAnalysis->probability_description }}
@else
    <span class="text-muted">-</span>
@endif
</td>
<td>
@if($risk->residualAnalysis)
    <span class="chip chip-score">{{ $risk->residualAnalysis->probability_score }}</span>
@else
    <span class="text-muted">-</span>
@endif
</td>
<td>
@if($risk->residualAnalysis)
    {{ $risk->residualAnalysis->impact_description }}
@else
    <span class="text-muted">-</span>
@endif
</td>
<td>
@if($risk->residualAnalysis)
    <span class="chip chip-score">{{ $risk->residualAnalysis->impact_score }}</span>
@else
    <span class="text-muted">-</span>
@endif
</td>
<td>
@if($risk->residualAnalysis)
    <span class="chip chip-score-risk">{{ $risk->residualAnalysis->residual_risk_score }}</span>
@else
    <span class="text-muted">-</span>
@endif
</td>
<td>
@if($risk->residualAnalysis)
    <span class="chip chip-score-adj">{{ $risk->residualAnalysis->adjustment ?? $risk->residualAnalysis->risk_adjustment ?? '-' }}</span>
@else
    <span class="text-muted">-</span>
@endif
</td>
<td>
@if($risk->residualAnalysis)
    @php
        $residualLevelClass = match($risk->residualAnalysis->risk_level){
            'Low' => 'low',
            'Low to Moderate' => 'low-moderate',
            'Moderate' => 'moderate',
            'Moderate to High' => 'moderate-high',
            'High' => 'high',
            default => 'unknown'
        };
    @endphp
    <span class="risk-level-pill {{ $residualLevelClass }}">
        {{ $risk->residualAnalysis->risk_level }}
    </span>
@else
    <span class="text-muted">-</span>
@endif
</td>
<td>
@if($risk->residualAnalysis)
    <span class="chip chip-level-adjust">{{ $risk->residualAnalysis->risk_level_adjustment ?? '-' }}</span>
@else
    <span class="text-muted">-</span>
@endif
</td>
<td>
@php
    $radarGroup = optional($risk->residualAnalysis)->radarGroup;
    $radarCode = optional($radarGroup)->code ?? optional($risk->residualAnalysis)->radar_code;
@endphp
@if($radarCode)
    <span class="chip chip-radar">{{ $radarCode }}</span>
@else
    <span class="text-muted">-</span>
@endif
</td>
<td>
@php
    $radarDescription = optional($radarGroup)->description ?? optional($risk->residualAnalysis)->radar_description;
@endphp
@if($radarDescription)
    {{ $radarDescription }}
@else
    <span class="text-muted">-</span>
@endif
</td>
@php
    $inherentImpactAmount = optional($risk->inherentAnalysis)->impact_amount;
    $inherentProbability = optional($risk->inherentAnalysis)->probability_percent;
    $inherentPotentialLoss = optional($risk->inherentAnalysis)->potential_loss;
    $residualImpactAmountRaw = optional($risk->residualAnalysis)->impact_amount;
    $residualImpactAmount = is_null($residualImpactAmountRaw) ? $inherentImpactAmount : $residualImpactAmountRaw;
    $residualProbability = optional($risk->residualAnalysis)->probability_percent;
    $residualPotentialLoss = optional($risk->residualAnalysis)->potential_loss;
@endphp
<td class="text-right">{{ is_null($inherentImpactAmount) ? '-' : number_format((float) $inherentImpactAmount, 0, ',', '.') }}</td>
<td class="text-center">{{ is_null($inherentProbability) ? '-' : number_format(((float) $inherentProbability) * 100, 2, '.', '') . '%' }}</td>
<td class="text-right">{{ is_null($inherentPotentialLoss) ? '-' : number_format((float) $inherentPotentialLoss, 0, ',', '.') }}</td>
<td class="text-right">{{ is_null($residualImpactAmount) ? '-' : number_format((float) $residualImpactAmount, 0, ',', '.') }}</td>
<td class="text-center">{{ is_null($residualProbability) ? '-' : number_format(((float) $residualProbability) * 100, 2, '.', '') . '%' }}</td>
<td class="text-right">{{ is_null($residualPotentialLoss) ? '-' : number_format((float) $residualPotentialLoss, 0, ',', '.') }}</td>

</tr>
@endforeach
</tbody>

</table>
</div>
</div>
</div>

<div class="risk-tab-panel" data-risk-tab-panel="heatmap">
    <div class="d-flex justify-content-end mb-2">
        @include('reports.export-buttons', [
            'scope' => 'heatmap',
            'scopeLabel' => 'Heat Map',
        ])
    </div>
    @include('risks.partials.heatmap-content')
</div>

<div class="risk-tab-panel" data-risk-tab-panel="recap">
    @include('risks.partials.recap-content', [
        'recapFormAction' => route('risks.index'),
    ])
</div>
</div>

@stop

@section('js')

<script src="{{ asset('vendor/datatables/js/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('vendor/datatables/js/dataTables.bootstrap5.min.js') }}"></script>

<script>
$(document).ready(function(){

let table = $('#riskTable').DataTable({
responsive:false,
autoWidth:false,
scrollX: true,
pageLength: 5,
lengthMenu: [[5, 10, 25, 50, 100], [5, 10, 25, 50, 100]],
dom:
    "<'row mb-2 align-items-center'<'col-md-6'l><'col-md-6 text-end'f>>" +
    "rt" +
    "<'row mt-2'<'col-md-5'i><'col-md-7 text-end'p>>",
columnDefs: [
    { targets: 1, orderable: false, searchable: false }
],
language: {
    paginate: {
        previous: "&laquo; Previous",
        next: "Next &raquo;"
    }
}
});

const allColumnIndexes = table.columns().indexes().toArray();
const columnPresetMap = {
    overview: [
        0, 1,
        2, 3, 4, 5, 6,
        7, 8, 9, 10, 11,
        16, 17,
        28, 30, 31,
        32, 33,
    ],
    full: allColumnIndexes,
    analysis: [
        0, 1,
        2, 3, 4, 5, 6,
        7, 11,
        12, 13, 14, 15, 16, 17,
        24, 25, 26, 27, 28, 29,
        30, 31,
        32, 33,
    ],
    treatment: [
        0, 1,
        2, 3, 4, 5, 6,
        7, 11,
        18, 19, 20, 21, 22, 23,
        30, 31,
    ],
    quantitative: [
        0, 1,
        2, 3, 4, 5, 6,
        7, 11,
        30, 31,
        32, 33,
        34, 35, 36, 37, 38, 39,
    ],
};

function updateColumnGroupHeaders() {
    $('#riskTable thead tr:first-child th.column-group-head').each(function () {
        const start = parseInt($(this).data('start'), 10);
        const end = parseInt($(this).data('end'), 10);

        let visibleCount = 0;
        for (let i = start; i <= end; i++) {
            if (table.column(i).visible()) {
                visibleCount++;
            }
        }

        if (visibleCount === 0) {
            $(this).hide();
            return;
        }

        $(this).attr('colspan', visibleCount).show();
    });
}

function setActivePresetButton(presetName) {
    $('[data-column-preset]').removeClass('active btn-primary').addClass('btn-outline-primary');

    const activeButton = $(`[data-column-preset="${presetName}"]`);
    if (activeButton.length) {
        activeButton.addClass('active btn-primary').removeClass('btn-outline-primary');
    }
}

function applyColumnPreset(presetName) {
    const selected = columnPresetMap[presetName] || columnPresetMap.overview;
    const selectedSet = new Set(selected);

    allColumnIndexes.forEach(function (columnIndex) {
        table.column(columnIndex).visible(selectedSet.has(columnIndex), false);
    });

    table.columns.adjust().draw(false);
    updateColumnGroupHeaders();
    setActivePresetButton(presetName);

    try {
        localStorage.setItem('risk_table_column_preset', presetName);
    } catch (error) {
        // Ignore storage issues (private mode or blocked storage).
    }
}

function enforcePagerSpacing() {
    const paginate = $('#riskTable_wrapper .dataTables_paginate');
    paginate.find('a.paginate_button').css({
        display: 'inline-flex',
        alignItems: 'center',
        justifyContent: 'center',
        marginLeft: '0.35rem',
        marginRight: '0.35rem'
    });
    paginate.find('a.paginate_button.previous').css({
        marginLeft: '0',
        marginRight: '0.45rem'
    });
    paginate.find('a.paginate_button.next').css({
        marginLeft: '0.45rem',
        marginRight: '0'
    });
    paginate.find('span').css({
        display: 'inline-flex',
        alignItems: 'center',
        gap: '0.35rem',
        margin: '0 0.1rem'
    });
}

table.on('draw.dt', function () {
    enforcePagerSpacing();
});
enforcePagerSpacing();

$('[data-column-preset]').on('click', function () {
    applyColumnPreset($(this).data('column-preset'));
});

let initialPreset = 'overview';
try {
    const savedPreset = localStorage.getItem('risk_table_column_preset');
    if (savedPreset && columnPresetMap[savedPreset]) {
        initialPreset = savedPreset;
    }
} catch (error) {
    // Ignore storage read issues.
}

applyColumnPreset(initialPreset);

const riskWorkspaceButtons = document.querySelectorAll('[data-risk-workspace-tab]');
const riskWorkspacePanels = document.querySelectorAll('[data-risk-tab-panel]');
const riskWorkspaceTabs = ['register', 'heatmap', 'recap'];
const serverActiveRiskTab = @json($activeRiskTab ?? 'register');

function normalizeRiskWorkspaceTab(value) {
    const normalized = String(value || '').toLowerCase().trim();
    if (riskWorkspaceTabs.indexOf(normalized) >= 0) {
        return normalized;
    }

    return 'register';
}

function applyRiskWorkspaceTab(tabId, updateUrl = true) {
    const safeTab = normalizeRiskWorkspaceTab(tabId);

    riskWorkspaceButtons.forEach(function (button) {
        const isActive = normalizeRiskWorkspaceTab(button.getAttribute('data-risk-workspace-tab')) === safeTab;
        button.classList.toggle('active', isActive);
        button.setAttribute('aria-selected', isActive ? 'true' : 'false');
    });

    riskWorkspacePanels.forEach(function (panel) {
        const panelTab = normalizeRiskWorkspaceTab(panel.getAttribute('data-risk-tab-panel'));
        panel.classList.toggle('active', panelTab === safeTab);
    });

    if (safeTab === 'register') {
        table.columns.adjust().draw(false);
        updateColumnGroupHeaders();
    }

    if (updateUrl && window.history && typeof window.history.replaceState === 'function') {
        const url = new URL(window.location.href);
        if (safeTab === 'register') {
            url.searchParams.delete('risk_tab');
        } else {
            url.searchParams.set('risk_tab', safeTab);
        }
        window.history.replaceState({}, '', url.toString());
    }
}

riskWorkspaceButtons.forEach(function (button) {
    button.addEventListener('click', function (event) {
        event.preventDefault();
        applyRiskWorkspaceTab(button.getAttribute('data-risk-workspace-tab'));
    });
});

applyRiskWorkspaceTab(serverActiveRiskTab, false);

});
</script>

@if(!auth()->user()->isSuperadmin() && auth()->user()->isComplianceDivision())
<div class="modal fade" id="riskImportModal" tabindex="-1" aria-labelledby="riskImportModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('risks.import.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="riskImportModalLabel">Import Risk Register</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" data-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Excel File (.xls/.xlsx)</label>
                        <input type="file" name="file" class="form-control" accept=".xls,.xlsx" required>
                        <small class="text-muted">Gunakan template RM-02 yang sama seperti file lama kamu.</small>
                        <small class="text-muted d-block">Default sheet import: BT, DI, CR, OM, CS, BS, HC, SUM (sheet hidden otomatis di-skip).</small>
                        <div class="mt-2">
                            <a class="btn btn-outline-primary btn-sm" href="{{ route('import-templates.download', 'risk-register') }}">
                                <i class="fas fa-file-download"></i> Download Template
                            </a>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Year (Optional)</label>
                        <input type="number" min="2000" max="2100" name="year" class="form-control" placeholder="Auto detect dari file">
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" value="1" id="dry_run" name="dry_run">
                        <label class="form-check-label" for="dry_run">
                            Dry-run dulu (cek hasil tanpa simpan data)
                        </label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Start Import</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

@stop

