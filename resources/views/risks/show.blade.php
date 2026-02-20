@extends('layouts.tabler')

@section('title', 'Risk Details')

@section('css')
<style>
    .risk-header-actions {
        display: inline-flex;
        align-items: center;
        gap: 0.6rem;
    }

    .risk-details-page .risk-tab-nav {
        gap: 0.35rem;
        border-bottom: 1px solid #dbe3ea;
    }

    .risk-details-page .risk-tab-nav .nav-link {
        display: inline-flex;
        align-items: center;
        gap: 0.45rem;
        color: #475569;
        background: #f8fafc;
        border: 1px solid transparent;
        border-bottom: none;
        border-radius: 0.6rem 0.6rem 0 0;
        padding: 0.62rem 0.95rem;
        font-weight: 600;
    }

    .risk-details-page .risk-tab-nav .nav-link:hover {
        color: #0f172a;
        background: #f1f5f9;
    }

    .risk-details-page .risk-tab-nav .nav-link.active {
        color: #0f766e;
        background: #ecfeff;
        border-color: #99f6e4 #99f6e4 #ecfeff;
    }

    .risk-details-page .card {
        border-color: #dbe3ea;
        box-shadow: 0 2px 10px rgba(15, 23, 42, 0.04);
    }

    .risk-details-page .card-header {
        background: #f8fafc;
        border-bottom: 1px solid #e2e8f0;
        padding: 0.85rem 1rem;
    }

    .risk-details-page .tab-card-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
    }

    .risk-details-page .tab-card-header .card-title {
        margin: 0;
        font-size: 1.08rem;
        font-weight: 700;
        color: #0f172a;
    }

    .btn-with-icon {
        display: inline-flex;
        align-items: center;
        gap: 0.45rem;
    }

    .btn-with-icon i {
        margin: 0;
        line-height: 1;
    }

    .risk-details-page .action-inline {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
    }

    .risk-details-page .badge-soft {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 0.28rem 0.62rem;
        border-radius: 0.45rem;
        font-size: 0.78rem;
        font-weight: 700;
        line-height: 1.2;
    }

    .risk-details-page .badge-soft-lg {
        font-size: 0.96rem;
        padding: 0.35rem 0.74rem;
    }

    .risk-details-page .badge-soft-info {
        background: #dbeafe;
        color: #1d4ed8;
    }

    .risk-details-page .badge-soft-danger {
        background: #fee2e2;
        color: #b91c1c;
    }

    .risk-details-page .badge-soft-secondary {
        background: #e2e8f0;
        color: #334155;
    }

    .risk-details-page .badge-soft-dark {
        background: #e2e8f0;
        color: #0f172a;
    }

    .risk-details-page .badge-status-open {
        background: #fee2e2;
        color: #b91c1c;
    }

    .risk-details-page .badge-status-monitoring {
        background: #fef3c7;
        color: #92400e;
    }

    .risk-details-page .badge-status-closed {
        background: #dcfce7;
        color: #166534;
    }

    .risk-details-page .badge-level-low {
        background: #dcfce7;
        color: #166534;
    }

    .risk-details-page .badge-level-low-moderate {
        background: #cffafe;
        color: #0e7490;
    }

    .risk-details-page .badge-level-moderate {
        background: #fef3c7;
        color: #92400e;
    }

    .risk-details-page .badge-level-moderate-high {
        background: #ffedd5;
        color: #c2410c;
    }

    .risk-details-page .badge-level-high {
        background: #fee2e2;
        color: #b91c1c;
    }

    .risk-details-page .badge-level-default {
        background: #e5e7eb;
        color: #374151;
    }
</style>
@stop

@section('content_header')
    @php
        $canManage = auth()->user()->canManageRiskOwner($risk->risk_owner);
    @endphp
    <div class="d-flex justify-content-between align-items-center">
        <h1>Risk Details - {{ $risk->risk_id }}</h1>
        <div class="risk-header-actions">
            @if($canManage)
                <a href="{{ route('risks.edit', $risk->id) }}" class="btn btn-warning btn-sm btn-with-icon">
                    <i class="fas fa-edit"></i> Edit Basic Info
                </a>
            @endif
            <a href="{{ route('risks.index') }}" class="btn btn-secondary btn-sm btn-with-icon">
                <i class="fas fa-arrow-left"></i> Back
            </a>
        </div>
    </div>
@stop

@section('content')
<div class="risk-details-page">

<!-- Nav tabs -->
<ul class="nav nav-tabs risk-tab-nav" role="tablist">
    <li class="nav-item">
        <a class="nav-link active" href="#basic" role="tab" data-toggle="tab">
            <i class="fas fa-info-circle"></i> Basic Info
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link" href="#inherent" role="tab" data-toggle="tab">
            <i class="fas fa-chart-bar"></i> Inherent Analysis
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link" href="#treatment" role="tab" data-toggle="tab">
            <i class="fas fa-stethoscope"></i> Risk Treatment
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link" href="#residual" role="tab" data-toggle="tab">
            <i class="fas fa-chart-line"></i> Residual Analysis
        </a>
    </li>
</ul>

<!-- Tab panes -->
<div class="tab-content mt-3">

    {{-- ==================== TAB 1: BASIC INFO ==================== --}}
    <div role="tabpanel" class="tab-pane active" id="basic">
        <div class="card">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3">
                        <strong>Risk ID:</strong><br>
                        <span class="text-muted">{{ $risk->risk_id }}</span>
                    </div>
                    <div class="col-md-3">
                        <strong>Year:</strong><br>
                        <span class="text-muted">{{ $risk->year }}</span>
                    </div>
                    <div class="col-md-6">
                        <strong>Risk Title:</strong><br>
                        <span class="text-muted">{{ $risk->risk_title }}</span>
                    </div>
                </div>

                <div class="row mt-3">
                    <div class="col-md-12">
                        <strong>Risk Identification:</strong><br>
                        <span class="text-muted">{{ $risk->risk_description ?? '-' }}</span>
                    </div>
                </div>

                <div class="row mt-3">
                    <div class="col-md-4">
                        <strong>Risk Category:</strong><br>
                        <span class="text-muted">{{ $risk->category->name ?? '-' }}</span>
                    </div>
                    <div class="col-md-4">
                        <strong>Risk Cause Category:</strong><br>
                        <span class="text-muted">{{ $risk->causeCategory->name ?? '-' }}</span>
                    </div>
                    <div class="col-md-4">
                        <strong>Owner:</strong><br>
                        <span class="text-muted">{{ $risk->risk_owner }}</span>
                    </div>
                </div>

                <div class="row mt-3">
                    <div class="col-md-6">
                        <strong>Cause:</strong><br>
                        <span class="text-muted">{{ $risk->cause ?? '-' }}</span>
                    </div>
                    <div class="col-md-6">
                        <strong>Existing Control:</strong><br>
                        <span class="text-muted">{{ $risk->existing_control ?? '-' }}</span>
                    </div>
                </div>

                <div class="row mt-3">
                    <div class="col-md-4">
                        <strong>Status:</strong><br>
                        @php
                            $statusBadgeClass = match($risk->status) {
                                'Open' => 'badge-status-open',
                                'Monitoring' => 'badge-status-monitoring',
                                'Closed' => 'badge-status-closed',
                                default => 'badge-level-default',
                            };
                        @endphp
                        <span class="badge-soft {{ $statusBadgeClass }}">
                            {{ $risk->status }}
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ==================== TAB 2: INHERENT ANALYSIS ==================== --}}
    <div role="tabpanel" class="tab-pane" id="inherent">
        <div class="card">
            <div class="card-header">
                <div class="tab-card-header">
                    <h3 class="card-title">Risk Inherent Analysis</h3>
                    @if($risk->inherentAnalysis)
                        <button type="button" class="btn btn-sm btn-warning btn-with-icon" onclick="editInherentAnalysis()">
                            <i class="fas fa-edit"></i> Edit
                        </button>
                    @else
                        <button type="button" class="btn btn-sm btn-primary btn-with-icon" onclick="addInherentAnalysis()">
                            <i class="fas fa-plus"></i> Add Analysis
                        </button>
                    @endif
                </div>
            </div>
            <div class="card-body">
                @if($risk->inherentAnalysis)
                    <div class="row">
                        <div class="col-md-6">
                            <strong>Probability Description:</strong><br>
                            <span class="text-muted">{{ $risk->inherentAnalysis->probability_description ?? '-' }}</span>
                        </div>
                        <div class="col-md-6">
                            <strong>Probability Score:</strong><br>
                            <span class="badge-soft badge-soft-info">{{ $risk->inherentAnalysis->probability_score ?? '-' }}</span>
                        </div>
                    </div>

                    <div class="row mt-3">
                        <div class="col-md-6">
                            <strong>Impact Description:</strong><br>
                            <span class="text-muted">{{ $risk->inherentAnalysis->impact_description ?? '-' }}</span>
                        </div>
                        <div class="col-md-6">
                            <strong>Impact Score:</strong><br>
                            <span class="badge-soft badge-soft-info">{{ $risk->inherentAnalysis->impact_score ?? '-' }}</span>
                        </div>
                    </div>

                    <div class="row mt-3">
                        <div class="col-md-6">
                            <strong>Inherent Risk Score:</strong><br>
                            <span class="badge-soft badge-soft-danger badge-soft-lg">
                                {{ $risk->inherentAnalysis->inherent_risk_score ?? '-' }}
                            </span>
                        </div>
                        <div class="col-md-6">
                            <strong>Risk Level:</strong><br>
                            @php
                                $inherentLevelClass = match($risk->inherentAnalysis->risk_level) {
                                    'Low' => 'badge-level-low',
                                    'Low to Moderate' => 'badge-level-low-moderate',
                                    'Moderate' => 'badge-level-moderate',
                                    'Moderate to High' => 'badge-level-moderate-high',
                                    'High' => 'badge-level-high',
                                    default => 'badge-level-default',
                                };
                            @endphp
                            <span class="badge-soft {{ $inherentLevelClass }}">
                                {{ $risk->inherentAnalysis->risk_level ?? '-' }}
                            </span>
                        </div>
                    </div>
                @else
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> No inherent analysis data yet. Click "Add Analysis" to get started.
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- ==================== TAB 3: TREATMENT ==================== --}}
    <div role="tabpanel" class="tab-pane" id="treatment">
        <div class="card">
            <div class="card-header">
                <div class="tab-card-header">
                    <h3 class="card-title">Risk Treatment Options</h3>
                    <button type="button" class="btn btn-sm btn-primary btn-with-icon" onclick="addTreatment()">
                        <i class="fas fa-plus"></i> Add Treatment
                    </button>
                </div>
            </div>
            <div class="card-body">
                @if($risk->treatments->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered">
                            <thead class="bg-light">
                                <tr>
                                    <th>Option</th>
                                    <th>Description</th>
                                    <th>PIC / Div</th>
                                    <th>Deadline</th>
                                    <th>Effectiveness</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($risk->treatments as $treatment)
                                    <tr>
                                        <td>{{ $treatment->option }}</td>
                                        <td>{{ $treatment->description ?? '-' }}</td>
                                        <td>{{ $treatment->pic }}</td>
                                        <td>{{ \Carbon\Carbon::parse($treatment->deadline)->format('d-m-Y') }}</td>
                                        <td>
                                            <span class="badge-soft badge-soft-info">{{ $treatment->effectiveness }}</span>
                                        </td>
                                        <td>
                                            <div class="action-inline">
                                                <button class="btn btn-xs btn-warning" onclick="editTreatment({{ $treatment->id }})">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button class="btn btn-xs btn-danger" onclick="deleteTreatment({{ $treatment->id }})">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> No treatment options defined yet. Click "Add Treatment" to get started.
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- ==================== TAB 4: RESIDUAL ANALYSIS ==================== --}}
    <div role="tabpanel" class="tab-pane" id="residual">
        <div class="card">
            <div class="card-header">
                <div class="tab-card-header">
                    <h3 class="card-title">Risk Residual Analysis</h3>
                    @if($risk->residualAnalysis)
                        <button type="button" class="btn btn-sm btn-warning btn-with-icon" onclick="editResidualAnalysis()">
                            <i class="fas fa-edit"></i> Edit
                        </button>
                    @else
                        <button type="button" class="btn btn-sm btn-primary btn-with-icon" onclick="addResidualAnalysis()">
                            <i class="fas fa-plus"></i> Add Analysis
                        </button>
                    @endif
                </div>
            </div>
            <div class="card-body">
                @if($risk->residualAnalysis)
                    <div class="row">
                        <div class="col-md-6">
                            <strong>Probability Description:</strong><br>
                            <span class="text-muted">{{ $risk->residualAnalysis->probability_description ?? '-' }}</span>
                        </div>
                        <div class="col-md-6">
                            <strong>Probability Score:</strong><br>
                            <span class="badge-soft badge-soft-info">{{ $risk->residualAnalysis->probability_score ?? '-' }}</span>
                        </div>
                    </div>

                    <div class="row mt-3">
                        <div class="col-md-6">
                            <strong>Impact Description:</strong><br>
                            <span class="text-muted">{{ $risk->residualAnalysis->impact_description ?? '-' }}</span>
                        </div>
                        <div class="col-md-6">
                            <strong>Impact Score:</strong><br>
                            <span class="badge-soft badge-soft-info">{{ $risk->residualAnalysis->impact_score ?? '-' }}</span>
                        </div>
                    </div>

                    <div class="row mt-3">
                        <div class="col-md-6">
                            <strong>Residual Risk Score:</strong><br>
                            <span class="badge-soft badge-soft-danger badge-soft-lg">
                                {{ $risk->residualAnalysis->residual_risk_score ?? '-' }}
                            </span>
                        </div>
                        <div class="col-md-6">
                            <strong>Risk Level:</strong><br>
                            @php
                                $residualLevelClass = match($risk->residualAnalysis->risk_level) {
                                    'Low' => 'badge-level-low',
                                    'Low to Moderate' => 'badge-level-low-moderate',
                                    'Moderate' => 'badge-level-moderate',
                                    'Moderate to High' => 'badge-level-moderate-high',
                                    'High' => 'badge-level-high',
                                    default => 'badge-level-default',
                                };
                            @endphp
                            <span class="badge-soft {{ $residualLevelClass }}">
                                {{ $risk->residualAnalysis->risk_level ?? '-' }}
                            </span>
                        </div>
                    </div>

                    <div class="row mt-3">
                        <div class="col-md-6">
                            <strong>Risk Adjustment:</strong><br>
                            <span class="text-muted">{{ $risk->residualAnalysis->risk_adjustment ?? '-' }}</span>
                        </div>
                        <div class="col-md-6">
                            <strong>Overall Adjustment:</strong><br>
                            <span class="badge-soft badge-soft-secondary">{{ $risk->residualAnalysis->adjustment ?? '-' }}</span>
                        </div>
                    </div>

                    <div class="row mt-3">
                        <div class="col-md-6">
                            <strong>Group Radar Description:</strong><br>
                            <span class="text-muted">
                                {{ optional($risk->residualAnalysis->radarGroup)->description ?? $risk->residualAnalysis->radar_description ?? '-' }}
                            </span>
                        </div>
                        <div class="col-md-6">
                            <strong>Group Radar Code:</strong><br>
                            <span class="badge-soft badge-soft-dark">
                                {{ optional($risk->residualAnalysis->radarGroup)->code ?? $risk->residualAnalysis->radar_code ?? '-' }}
                            </span>
                        </div>
                    </div>
                @else
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> No residual analysis data yet. Click "Add Analysis" to get started.
                    </div>
                @endif
            </div>
        </div>
    </div>

</div>

</div>

@stop

@section('js')

<script>
function addInherentAnalysis() {
    alert('This will open a form to add inherent analysis');
}

function editInherentAnalysis() {
    alert('This will open a form to edit inherent analysis');
}

function addResidualAnalysis() {
    alert('This will open a form to add residual analysis');
}

function editResidualAnalysis() {
    alert('This will open a form to edit residual analysis');
}

function addTreatment() {
    alert('This will open a form to add treatment');
}

function editTreatment(id) {
    alert('This will open a form to edit treatment ' + id);
}

function deleteTreatment(id) {
    if(confirm('Are you sure you want to delete this treatment?')) {
        alert('This will delete treatment ' + id);
    }
}
</script>

@stop

