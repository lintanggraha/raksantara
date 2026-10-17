@extends('layouts.tabler')

@section('title', 'Add Risk')

@section('css')
<style>
    /* Hide inactive tabs properly */
    .tab-pane {
        display: none;
    }
    
    .tab-pane.active {
        display: block;
        animation: fadeIn 0.3s ease;
    }
    
    @keyframes fadeIn {
        from { opacity: 0.7; }
        to { opacity: 1; }
    }
    
    /* Tab highlight animation */
    .nav-link {
        transition: all 0.3s ease;
    }

    .risk-step-tabs .nav-link.has-error {
        border-bottom: 2px solid #dc3545 !important;
        color: #dc3545 !important;
        font-weight: 600;
    }
    .risk-step-tabs .nav-link {
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
    }

    .risk-step-tabs .nav-link i {
        margin-right: 0;
        line-height: 1;
    }
    
    .nav-link.active-highlight {
        animation: tabPulse 1.5s;
        background-color: #ffc107 !important;
        color: #000 !important;
    }
    
    @keyframes tabPulse {
        0% { background-color: #ffc107; }
        50% { background-color: #ffed4e; }
        100% { background-color: transparent; }
    }
    
    /* Make tab content more visible when active */
    .nav-link.active {
        font-weight: bold;
        border-bottom: 3px solid #007bff;
    }

    .btn i.fa-arrow-left {
        margin-right: 0.45rem;
    }

    .btn i.fa-arrow-right {
        margin-left: 0.45rem;
    }
</style>
@stop

@section('content_header')
    <h1>Add Risk - Complete Form (6 Sections)</h1>
@stop

@section('content')
@php
    $probabilityOptions = collect($probabilityScales ?? []);
    // Validation error detection across tabs
    $basicFields = ['risk_title', 'sasaran_target', 'risk_description', 'risk_category_id', 'risk_appetite_id', 'risk_cause_category_id', 'vendor_id', 'cause', 'existing_control', 'risk_owner', 'status', 'status_change_reason'];
    $inherentFields = ['inherent_probability_score', 'inherent_probability_description', 'inherent_impact_scale_id', 'inherent_impact_score', 'inherent_impact_description', 'inherent_impact_amount', 'inherent_probability_percent', 'inherent_potential_loss'];
    $residualFields = ['residual_probability_score', 'residual_probability_description', 'residual_impact_scale_id', 'residual_impact_score', 'residual_impact_description', 'residual_impact_amount', 'residual_probability_percent', 'residual_potential_loss'];
    $radarFields = ['radar_group_id', 'radar_description', 'radar_code'];
    
    $basicErrors = $errors->hasAny($basicFields);
    $inherentErrors = $errors->hasAny($inherentFields);
    $treatmentErrors = $errors->has('treatment_*') || $errors->hasAny([
        'treatment_id', 'treatment_template_id', 'treatment_option', 'treatment_description', 
        'treatment_pic', 'treatment_deadline', 'treatment_cost', 'treatment_status', 
        'treatment_realization', 'treatment_residual_target_score', 'treatment_preventive_action', 
        'treatment_recovery_action'
    ]);
    $residualErrors = $errors->hasAny($residualFields);
    $radarErrors = $errors->hasAny($radarFields);

    $activeTab = 'basic';
    if ($errors->any()) {
        if ($basicErrors) {
            $activeTab = 'basic';
        } elseif ($inherentErrors) {
            $activeTab = 'inherent';
        } elseif ($treatmentErrors) {
            $activeTab = 'treatment';
        } elseif ($residualErrors) {
            $activeTab = 'residual';
        } elseif ($radarErrors) {
            $activeTab = 'group-radar';
        }
    }
@endphp

<!-- Nav tabs -->
<ul class="nav nav-tabs risk-step-tabs" role="tablist">
    <li class="nav-item">
        <a class="nav-link {{ $activeTab === 'basic' ? 'active' : '' }} {{ $basicErrors ? 'has-error' : '' }}" href="#basic" role="tab" data-toggle="tab" data-bs-toggle="tab" data-bs-target="#basic">
            <i class="fas fa-info-circle"></i> 1. Risk
            @if($basicErrors)
                <span class="badge bg-danger ms-1" title="Errors in this section"><i class="fas fa-exclamation-circle"></i></span>
            @endif
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link {{ $activeTab === 'inherent' ? 'active' : '' }} {{ $inherentErrors ? 'has-error' : '' }}" href="#inherent" role="tab" data-toggle="tab" data-bs-toggle="tab" data-bs-target="#inherent">
            <i class="fas fa-chart-bar"></i> 2. Inherent Analysis
            @if($inherentErrors)
                <span class="badge bg-danger ms-1" title="Errors in this section"><i class="fas fa-exclamation-circle"></i></span>
            @endif
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link {{ $activeTab === 'treatment' ? 'active' : '' }} {{ $treatmentErrors ? 'has-error' : '' }}" href="#treatment" role="tab" data-toggle="tab" data-bs-toggle="tab" data-bs-target="#treatment">
            <i class="fas fa-stethoscope"></i> 3. Risk Treatment
            @if($treatmentErrors)
                <span class="badge bg-danger ms-1" title="Errors in this section"><i class="fas fa-exclamation-circle"></i></span>
            @endif
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link {{ $activeTab === 'residual' ? 'active' : '' }} {{ $residualErrors ? 'has-error' : '' }}" href="#residual" role="tab" data-toggle="tab" data-bs-toggle="tab" data-bs-target="#residual">
            <i class="fas fa-chart-line"></i> 4. Residual Analysis
            @if($residualErrors)
                <span class="badge bg-danger ms-1" title="Errors in this section"><i class="fas fa-exclamation-circle"></i></span>
            @endif
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link {{ $activeTab === 'group-radar' ? 'active' : '' }} {{ $radarErrors ? 'has-error' : '' }}" href="#group-radar" role="tab" data-toggle="tab" data-bs-toggle="tab" data-bs-target="#group-radar">
            <i class="fas fa-bullseye"></i> 5. Group Radar
            @if($radarErrors)
                <span class="badge bg-danger ms-1" title="Errors in this section"><i class="fas fa-exclamation-circle"></i></span>
            @endif
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link {{ $activeTab === 'quantitative' ? 'active' : '' }}" href="#quantitative" role="tab" data-toggle="tab" data-bs-toggle="tab" data-bs-target="#quantitative">
            <i class="fas fa-calculator"></i> 6. Quantitative Result
        </a>
    </li>
</ul>

<!-- Tab panes -->
<div class="tab-content mt-3">

    <form id="riskForm" action="{{ route('risks.store') }}" method="POST">
        @csrf
        @if ($errors->any())
            <div class="alert alert-danger alert-dismissible mb-3 shadow-sm border-danger" role="alert">
                <div class="d-flex align-items-start">
                    <div class="me-3 fs-2 text-danger">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                    <div class="flex-grow-1">
                        <h4 class="alert-title fw-bold text-danger mb-1">Form Validation Failed ({{ $errors->count() }} {{ Str::plural('issue', $errors->count()) }} found)</h4>
                        <div class="text-secondary small mb-2">The form could not be submitted. Please inspect the highlighted section(s) above and correct the errors:</div>
                        <ul class="mb-0 ps-3 small text-danger">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        @endif


        @if(!empty($fromIncident))
            <input type="hidden" name="from_incident_id" value="{{ $fromIncident->id }}">
            <div class="alert alert-info d-flex align-items-center mb-3">
                <i class="fas fa-lightbulb fa-2x me-3 text-info"></i>
                <div>
                    <h5 class="alert-heading mb-1">Mendaftarkan Risk Baru dari Temuan Insiden [{{ $fromIncident->incident_code }}]</h5>
                    <div>Data proses, deskripsi, penyebab, dan estimasi kerugian telah diisikan otomatis dari insiden <strong>{{ $fromIncident->title }}</strong>. Silakan lengkapi kategori dan analisis risiko, lalu simpan. Insiden akan otomatis terhubung ke risk baru ini.</div>
                </div>
            </div>
        @endif

        {{-- ==================== TAB 1: BASIC INFO ==================== --}}
        <div role="tabpanel" class="tab-pane {{ $activeTab === 'basic' ? 'active' : '' }}" id="basic">
            <div class="card">
                <div class="card-header bg-primary">
                    <h5 class="mb-0 text-white"><i class="fas fa-info-circle"></i> Risk</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Risk ID</label>
                                <input type="text" class="form-control" value="Auto Generate" readonly>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Year</label>
                                <input type="text" class="form-control" value="{{ date('Y') }}" readonly>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Risk Category <span class="text-danger">*</span></label>
                                <select name="risk_category_id" class="form-control" required>
                                    <option value="">Select Category</option>
                                    @foreach($categories as $cat)
                                        <option value="{{ $cat->id }}" {{ (string) old('risk_category_id') === (string) $cat->id ? 'selected' : '' }}>
                                             {{ $cat->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-8">
                            <div class="form-group">
                                <label>Process <span class="text-danger">*</span></label>
                                <input type="text" name="risk_title" class="form-control" value="{{ old('risk_title', optional($fromIncident ?? null)->title) }}" required>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Risk Owner <span class="text-danger">*</span></label>
                                @php($riskOwners = $allowedRiskOwners ?? [])
                                <select name="risk_owner" class="form-control" required>
                                    <option value="">Select Risk Owner</option>
                                    @foreach($riskOwners as $owner)
                                        <option value="{{ $owner }}" {{ old('risk_owner', optional($fromIncident ?? null)->effective_risk_owner) === $owner ? 'selected' : '' }}>
                                            {{ $owner }}
                                        </option>
                                    @endforeach
                                </select>
                                <small class="text-muted">Pilih owner dari daftar, tanpa input manual.</small>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Risk Appetite (override)</label>
                                <select name="risk_appetite_id" class="form-control">
                                    <option value="">Ikuti appetite kategori (default)</option>
                                    @foreach($riskAppetites ?? [] as $appetite)
                                        <option value="{{ $appetite->id }}" {{ (string) old('risk_appetite_id') === (string) $appetite->id ? 'selected' : '' }}>
                                            {{ $appetite->name }}
                                            @if($appetite->isQuantitative())
                                                - {{ $appetite->threshold_metric_label }}
                                            @endif
                                        </option>
                                    @endforeach
                                </select>
                                <small class="text-muted">Kosongkan agar risk memakai appetite kategorinya. Pilih hanya bila risk ini butuh batas berbeda.</small>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Cause Category</label>
                                <select name="risk_cause_category_id" class="form-control">
                                    <option value="">Select Cause Category</option>
                                    @foreach($causeCategories as $c)
                                        <option value="{{ $c->id }}" {{ (string) old('risk_cause_category_id') === (string) $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Third-Party Vendor</label>
                                <select name="vendor_id" class="form-control">
                                    <option value="">None / Not Applicable</option>
                                    @foreach($vendors ?? [] as $vendor)
                                        <option value="{{ $vendor->id }}" {{ (string) old('vendor_id') === (string) $vendor->id ? 'selected' : '' }}>
                                            {{ $vendor->name }}
                                        </option>
                                    @endforeach
                                </select>
                                <small class="text-muted">Select if risk is vendor-related</small>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label>Sasaran Target</label>
                                <textarea name="sasaran_target" class="form-control" rows="2">{{ old('sasaran_target') }}</textarea>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label>Risk Identification</label>
                                <textarea name="risk_description" class="form-control" rows="2">{{ old('risk_description', optional($fromIncident ?? null)->incident_description) }}</textarea>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label>Cause</label>
                                <textarea name="cause" class="form-control" rows="2">{{ old('cause', optional($fromIncident ?? null)->root_cause) }}</textarea>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label>Existing Control</label>
                                <textarea name="existing_control" class="form-control" rows="2">{{ old('existing_control', optional($fromIncident ?? null)->immediate_action ?: optional($fromIncident ?? null)->corrective_action) }}</textarea>
                            </div>
                        </div>
                    </div>

                    <hr>
                    <button type="button" class="btn btn-primary btn-lg btn-block" onclick="scrollToTab('inherent')">
                        Next: Inherent Analysis <i class="fas fa-arrow-right"></i>
                    </button>
                </div>
            </div>
        </div>

        {{-- ==================== TAB 2: INHERENT ANALYSIS ==================== --}}
        <div role="tabpanel" class="tab-pane {{ $activeTab === 'inherent' ? 'active' : '' }}" id="inherent">
            <!-- BASIC INFO SUMMARY (GREYED OUT) -->
            <div class="card mb-3">
                <div class="card-header bg-light">
                    <h5 class="mb-0">Summary (Reference)</h5>
                </div>
                <div class="card-body" style="background-color: #f8f9fa; opacity: 0.7;">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label><small class="text-muted">Risk ID</small></label>
                                <input type="text" id="summary_risk_id_inherent" class="form-control form-control-sm" readonly>
                            </div>
                        </div>
                        <div class="col-md-8">
                            <div class="form-group">
                                <label><small class="text-muted">Process</small></label>
                                <input type="text" id="summary_risk_title_inherent" class="form-control form-control-sm" readonly>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label><small class="text-muted">Risk Identification</small></label>
                                <textarea id="summary_risk_desc_inherent" class="form-control form-control-sm" rows="2" readonly></textarea>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header bg-info">
                    <h5 class="mb-0 text-white"><i class="fas fa-chart-bar"></i> Inherent Analysis (EDIT)</h5>
                </div>
                <div class="card-body">
                    <small class="text-muted">Risk BEFORE treatment applied</small>
                    
                    <div class="row mt-3">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Probability Description <span class="text-danger">*</span></label>
                                <select id="inherent_probability" name="inherent_probability_score" class="form-control" required>
                                    <option value="">Select Probability</option>
                                    @foreach($probabilityOptions as $scale)
                                        @php($probabilityLabel = trim((string) $scale->description) !== '' ? trim((string) $scale->description) : 'Legacy value')
                                        <option
                                            value="{{ $scale->score }}"
                                            data-description="{{ $scale->description }}"
                                            {{ (string) old('inherent_probability_score') === (string) $scale->score ? 'selected' : '' }}
                                        >
                                            {{ $scale->score }}. {{ $probabilityLabel }}
                                        </option>
                                    @endforeach
                                </select>
                                <input type="hidden" name="inherent_probability_description" id="inherent_prob_desc" value="{{ old('inherent_probability_description') }}">
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Impact Category <span class="text-danger">*</span></label>
                                <select id="inherent_impact_category" class="form-control" required>
                                    <option value="">Select Impact Category</option>
                                    @foreach($impactCategories as $cat)
                                        <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Impact Description <span class="text-danger">*</span></label>
                                <select id="inherent_impact_scale" name="inherent_impact_scale_id" class="form-control" required>
                                    <option value="">Select Impact Description</option>
                                </select>
                                <input type="hidden" name="inherent_impact_score" id="inherent_impact_score">
                                <input type="hidden" name="inherent_impact_description" id="inherent_impact_desc">
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Inherent Risk Score (Auto)</label>
                                <input type="text" id="inherent_risk_score" class="form-control" readonly style="font-weight: bold; font-size: 16px; background-color: #fff3cd;">
                                <input type="hidden" name="inherent_risk_score">
                                <input type="hidden" name="inherent_risk_level" id="inherent_risk_level">
                            </div>
                        </div>
                    </div>

                    <hr>
                    <div class="row">
                        <div class="col-md-6">
                            <button type="button" class="btn btn-secondary btn-lg btn-block" onclick="scrollToTab('basic')">
                                <i class="fas fa-arrow-left"></i> Back
                            </button>
                        </div>
                        <div class="col-md-6">
                            <button type="button" class="btn btn-primary btn-lg btn-block" onclick="scrollToTab('treatment')">
                                Next: Risk Treatment <i class="fas fa-arrow-right"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ==================== TAB 3: TREATMENT ==================== --}}
        <div role="tabpanel" class="tab-pane {{ $activeTab === 'treatment' ? 'active' : '' }}" id="treatment">
            <!-- BASIC INFO SUMMARY (GREYED OUT) -->
            <div class="card mb-3">
                <div class="card-header bg-light">
                    <h5 class="mb-0">Summary (Reference)</h5>
                </div>
                <div class="card-body" style="background-color: #f8f9fa; opacity: 0.7;">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label><small class="text-muted">Risk ID</small></label>
                                <input type="text" id="summary_risk_id_treatment" class="form-control form-control-sm" readonly>
                            </div>
                        </div>
                        <div class="col-md-8">
                            <div class="form-group">
                                <label><small class="text-muted">Process</small></label>
                                <input type="text" id="summary_risk_title_treatment" class="form-control form-control-sm" readonly>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label><small class="text-muted">Risk Identification</small></label>
                                <textarea id="summary_risk_desc_treatment" class="form-control form-control-sm" rows="2" readonly></textarea>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header bg-warning">
                    <h5 class="mb-0"><i class="fas fa-stethoscope"></i> Risk Treatment</h5>
                </div>
                <div class="card-body">
                    <small class="text-muted">Fill Risk Treatment manually like before. Mitigation playbook is optional and only helps prefill the same fields.</small>

                    <div class="row g-3 align-items-end mt-1">
                        <div class="col-md-5">
                            <div class="d-flex flex-wrap gap-2">
                                <button type="button" class="btn btn-success" onclick="addTreatmentRow()">
                                    <i class="fas fa-plus"></i> Add Treatment Manually
                                </button>
                                <a href="{{ route('treatment-templates.index') }}" class="btn btn-outline-secondary">
                                    <i class="fas fa-book"></i> Manage Playbooks
                                </a>
                            </div>
                        </div>
                        <div class="col-md-7">
                            <div class="form-group mb-0">
                                <label>Mitigation Playbook (Optional)</label>
                                <select id="treatment-template-select" class="form-control">
                                    <option value="">Select playbook</option>
                                </select>
                                <small class="text-muted">Playbooks follow the selected risk owner from tab 1 and only prefill Treatment Option, Description, PIC, Deadline, Effectiveness, Next Action Date, Blocked Reason, and Playbook Checklist.</small>
                                <div class="mt-2">
                                    <button type="button" class="btn btn-primary" id="apply-template-button" onclick="applySelectedTreatmentTemplate()">
                                    <i class="fas fa-copy"></i> Apply Playbook
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div id="treatments-container">
                        <!-- Treatment rows will be added here -->
                    </div>

                    <hr>
                    <div class="row">
                        <div class="col-md-6">
                            <button type="button" class="btn btn-secondary btn-lg btn-block" onclick="scrollToTab('inherent')">
                                <i class="fas fa-arrow-left"></i> Back
                            </button>
                        </div>
                        <div class="col-md-6">
                            <button type="button" class="btn btn-primary btn-lg btn-block" onclick="scrollToTab('residual')">
                                Next: Residual Analysis <i class="fas fa-arrow-right"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ==================== TAB 4: RESIDUAL ANALYSIS ==================== --}}
        <div role="tabpanel" class="tab-pane {{ $activeTab === 'residual' ? 'active' : '' }}" id="residual">
            <!-- BASIC INFO SUMMARY (GREYED OUT) -->
            <div class="card mb-3">
                <div class="card-header bg-light">
                    <h5 class="mb-0">Summary (Reference)</h5>
                </div>
                <div class="card-body" style="background-color: #f8f9fa; opacity: 0.7;">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label><small class="text-muted">Risk ID</small></label>
                                <input type="text" id="summary_risk_id_residual" class="form-control form-control-sm" readonly>
                            </div>
                        </div>
                        <div class="col-md-8">
                            <div class="form-group">
                                <label><small class="text-muted">Process</small></label>
                                <input type="text" id="summary_risk_title_residual" class="form-control form-control-sm" readonly>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label><small class="text-muted">Risk Identification</small></label>
                                <textarea id="summary_risk_desc_residual" class="form-control form-control-sm" rows="2" readonly></textarea>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header bg-success">
                    <h5 class="mb-0 text-white"><i class="fas fa-chart-line"></i> Residual Analysis</h5>
                </div>
                <div class="card-body">
                    <small class="text-muted">Risk AFTER treatment applied</small>

                    <div class="row mt-3">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Probability Description <span class="text-danger">*</span></label>
                                <select id="residual_probability" name="residual_probability_score" class="form-control" required>
                                    <option value="">Select Probability</option>
                                    @foreach($probabilityOptions as $scale)
                                        @php($probabilityLabel = trim((string) $scale->description) !== '' ? trim((string) $scale->description) : 'Legacy value')
                                        <option
                                            value="{{ $scale->score }}"
                                            data-description="{{ $scale->description }}"
                                            {{ (string) old('residual_probability_score') === (string) $scale->score ? 'selected' : '' }}
                                        >
                                            {{ $scale->score }}. {{ $probabilityLabel }}
                                        </option>
                                    @endforeach
                                </select>
                                <input type="hidden" name="residual_probability_description" id="residual_prob_desc" value="{{ old('residual_probability_description') }}">
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Impact Category <span class="text-danger">*</span></label>
                                <select id="residual_impact_category" class="form-control" required>
                                    <option value="">Select Impact Category</option>
                                    @foreach($impactCategories as $cat)
                                        <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Impact Description <span class="text-danger">*</span></label>
                                <select id="residual_impact_scale" name="residual_impact_scale_id" class="form-control" required>
                                    <option value="">Select Impact Description</option>
                                </select>
                                <input type="hidden" name="residual_impact_score" id="residual_impact_score">
                                <input type="hidden" name="residual_impact_description" id="residual_impact_desc">
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Residual Risk Score (Auto)</label>
                                <input type="text" id="residual_risk_score" class="form-control" readonly style="font-weight: bold; font-size: 16px; background-color: #d1e7dd;">
                                <input type="hidden" name="residual_risk_score">
                                <input type="hidden" name="residual_risk_level" id="residual_risk_level">
                                <div class="mt-2">
                                    <label>Residual Risk Adjustment</label>
                                    <div class="input-group">
                                        <input type="text" id="residual_adjustment_display" class="form-control" readonly style="font-weight:bold; background:#fff;">
                                        <input type="hidden" name="risk_adjustment" value="0">
                                    </div>
                                </div>

                                <div class="mt-2">
                                    <label>Level Adjustment</label>
                                    <div class="input-group">
                                        <input type="text" id="risk_level_adjustment_display" class="form-control" readonly style="font-weight:bold; background:#fff;">
                                        <input type="hidden" name="risk_level_adjustment" value="">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <hr>
                    <div class="row">
                        <div class="col-md-6">
                            <button type="button" class="btn btn-secondary btn-lg btn-block" onclick="scrollToTab('treatment')">
                                <i class="fas fa-arrow-left"></i> Back
                            </button>
                        </div>
                        <div class="col-md-6">
                            <button type="button" class="btn btn-primary btn-lg btn-block" onclick="scrollToTab('group-radar')">
                                Next: Group Radar <i class="fas fa-arrow-right"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ==================== TAB 5: GROUP RADAR ==================== --}}
        <div role="tabpanel" class="tab-pane {{ $activeTab === 'group-radar' ? 'active' : '' }}" id="group-radar">
            <div class="card">
                <div class="card-header bg-secondary">
                    <h5 class="mb-0 text-white"><i class="fas fa-bullseye"></i> Group Radar</h5>
                </div>
                <div class="card-body">
                    <div class="row mt-2">
                        <div class="col-md-8">
                            <div class="form-group">
                                <label>Group Radar Description</label>
                                <select id="radar_group_id" name="radar_group_id" class="form-control">
                                    <option value="">Select Radar Description</option>
                                    @foreach($radarGroups as $radarGroup)
                                    <option
                                        value="{{ $radarGroup->id }}"
                                        data-code="{{ $radarGroup->code }}"
                                        data-description="{{ $radarGroup->description }}"
                                        data-residual-factor="{{ number_format((float) optional($radarGroup->quantProfile)->residual_loss_factor, 4, '.', '') }}"
                                    >
                                        {{ $radarGroup->description }}
                                    </option>
                                    @endforeach
                                </select>
                                <input type="hidden" name="radar_description" id="radar_description" value="">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Group Radar Code</label>
                                <div class="input-group">
                                    <input type="text" id="radar_code_display" class="form-control" readonly style="font-weight:bold; background:#fff;">
                                    <input type="hidden" name="radar_code" id="radar_code" value="N/A">
                                </div>
                            </div>
                        </div>
                    </div>

                    <hr>
                    <div class="row">
                        <div class="col-md-6">
                            <button type="button" class="btn btn-secondary btn-lg btn-block" onclick="scrollToTab('residual')">
                                <i class="fas fa-arrow-left"></i> Back
                            </button>
                        </div>
                        <div class="col-md-6">
                            <button type="button" class="btn btn-primary btn-lg btn-block" onclick="scrollToTab('quantitative')">
                                Next: Quantitative Result <i class="fas fa-arrow-right"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ==================== TAB 6: QUANTITATIVE RESULT ==================== --}}
        <div role="tabpanel" class="tab-pane {{ $activeTab === 'quantitative' ? 'active' : '' }}" id="quantitative">
            <div class="card">
                <div class="card-header bg-dark">
                    <h5 class="mb-0 text-white"><i class="fas fa-calculator"></i> Quantitative Result</h5>
                </div>
                <div class="card-body">
                    <h6 class="font-weight-bold">Inherent Risk</h6>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Inherent Impact (Rp)</label>
                                <input
                                    type="number"
                                    min="0"
                                    step="1"
                                    id="inherent_impact_amount"
                                    name="inherent_impact_amount"
                                    class="form-control"
                                    placeholder="Contoh: 250000000"
                                >
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Inherent Probability (%)</label>
                                <input
                                    type="number"
                                    min="0"
                                    max="100"
                                    step="0.01"
                                    id="inherent_probability_percent"
                                    name="inherent_probability_percent"
                                    class="form-control"
                                    value="100"
                                >
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Inherent Potential Loss (Rp)</label>
                                <input
                                    type="number"
                                    min="0"
                                    step="1"
                                    id="inherent_potential_loss"
                                    name="inherent_potential_loss"
                                    class="form-control"
                                    value="{{ old('inherent_potential_loss', (!empty($fromIncident) && $fromIncident->loss_amount > 0) ? (int)$fromIncident->loss_amount : '') }}"
                                    placeholder="Auto dari Impact x Probability"
                                >
                            </div>
                        </div>
                    </div>

                    <hr>

                    <h6 class="font-weight-bold">Residual Risk</h6>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Residual Impact (Rp)</label>
                                <input
                                    type="number"
                                    min="0"
                                    step="1"
                                    id="residual_impact_amount"
                                    name="residual_impact_amount"
                                    class="form-control"
                                    placeholder="Contoh: 250000000"
                                >
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Residual Probability (%)</label>
                                <input
                                    type="number"
                                    min="0"
                                    max="100"
                                    step="0.01"
                                    id="residual_probability_percent"
                                    name="residual_probability_percent"
                                    class="form-control"
                                    value="30"
                                >
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Residual Potential Loss (Rp)</label>
                                <input
                                    type="number"
                                    min="0"
                                    step="1"
                                    id="residual_potential_loss"
                                    name="residual_potential_loss"
                                    class="form-control"
                                    placeholder="Auto dari Impact x Probability"
                                >
                            </div>
                        </div>
                    </div>

                    <hr>
                    <div class="row">
                        <div class="col-md-4">
                            <button type="button" class="btn btn-secondary btn-lg btn-block" onclick="scrollToTab('group-radar')">
                                <i class="fas fa-arrow-left"></i> Back
                            </button>
                        </div>
                        <div class="col-md-4">
                            <button type="submit" class="btn btn-success btn-lg btn-block">
                                <i class="fas fa-save"></i> SAVE COMPLETE RISK
                            </button>
                        </div>
                        <div class="col-md-4">
                            <button type="submit" name="post_submit_action" value="bcm" class="btn btn-info btn-lg btn-block">
                                <i class="fas fa-life-ring"></i> SAVE & LINK TO SERVICES
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </form>

</div>

@stop

@section('js')

<script>

let treatmentCount = {{ max(0, old('treatments') ? count(old('treatments')) : 0) }};
const treatmentTemplateCatalog = <?php echo json_encode(
    ($treatmentTemplates ?? collect())
        ->map(function ($template) {
            return [
                'id' => (int) $template->id,
                'name' => (string) $template->name,
                'risk_owner' => (string) $template->risk_owner,
                'option' => (string) $template->option,
                'description' => (string) $template->description,
                'default_pic' => (string) ($template->default_pic ?? ''),
                'default_effectiveness' => (string) $template->default_effectiveness,
                'target_days' => is_null($template->target_days) ? null : (int) $template->target_days,
                'next_action_offset_days' => is_null($template->next_action_offset_days) ? null : (int) $template->next_action_offset_days,
                'checklist' => (string) ($template->checklist ?? ''),
            ];
        })
        ->values(),
    JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT
); ?>;
const oldTreatmentRows = <?php echo json_encode(
    collect(old('treatment_option', []))
        ->values()
        ->map(function ($option, $index) {
            return [
                'templateId' => old("treatment_template_id.$index"),
                'option' => $option,
                'description' => old("treatment_description.$index"),
                'pic' => old("treatment_pic.$index"),
                'deadline' => old("treatment_deadline.$index"),
                'nextActionAt' => old("treatment_next_action_at.$index"),
                'blockedReason' => old("treatment_blocked_reason.$index"),
                'checklist' => old("treatment_playbook_checklist.$index"),
                'effectiveness' => old("treatment_effectiveness.$index"),
                'effectivenessScore' => old("treatment_effectiveness_score.$index"),
            ];
        })
        ->filter(function ($row) {
            return trim((string) ($row['option'] ?? '')) !== ''
                || trim((string) ($row['description'] ?? '')) !== '';
        })
        ->values(),
    JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT
); ?>;

// ================= UPDATE SUMMARY FIELDS =================
function updateSummaryFields() {
    let riskTitle = $('input[name="risk_title"]').val() || '';
    let riskDesc = $('textarea[name="risk_description"]').val() || '';
    let riskId = 'Auto Generate';
    
    // Update all summary fields on all tabs
    $('#summary_risk_id_inherent').val(riskId);
    $('#summary_risk_title_inherent').val(riskTitle);
    $('#summary_risk_desc_inherent').val(riskDesc);
    
    $('#summary_risk_id_treatment').val(riskId);
    $('#summary_risk_title_treatment').val(riskTitle);
    $('#summary_risk_desc_treatment').val(riskDesc);
    
    $('#summary_risk_id_residual').val(riskId);
    $('#summary_risk_title_residual').val(riskTitle);
    $('#summary_risk_desc_residual').val(riskDesc);
}

// Update summary as user types in Basic tab
$('input[name="risk_title"]').on('keyup change', function() {
    updateSummaryFields();
});
$('textarea[name="risk_description"]').on('keyup change', function() {
    updateSummaryFields();
});

// ================= VALIDATION FUNCTIONS =================
function validateBasicTab() {
    let riskTitle = $('input[name="risk_title"]').val();
    let riskOwner = $('[name="risk_owner"]').val();
    let riskCategory = $('select[name="risk_category_id"]').val();
    
    if(!riskTitle.trim()) {
        alert('❌ Process wajib diisi!');
        return false;
    }
    if(!riskCategory) {
        alert('❌ Risk Category wajib dipilih!');
        return false;
    }
    if(!riskOwner || !riskOwner.trim()) {
        alert('❌ Risk Owner wajib diisi!');
        return false;
    }
    
    updateSummaryFields();
    return true;
}

function validateInherentTab() {
    let probability = $('#inherent_probability').val();
    let impactCategory = $('#inherent_impact_category').val();
    let impactScale = $('#inherent_impact_scale').val();
    
    if(!probability) {
        alert('❌ Probability wajib dipilih!');
        return false;
    }
    if(!impactCategory) {
        alert('❌ Impact Category wajib dipilih!');
        return false;
    }
    if(!impactScale) {
        alert('❌ Impact Description wajib dipilih!');
        return false;
    }
    
    return true;
}

function validateResidualTab() {
    let probability = $('#residual_probability').val();
    let impactCategory = $('#residual_impact_category').val();
    let impactScale = $('#residual_impact_scale').val();
    
    if(!probability) {
        alert('❌ Probability wajib dipilih!');
        return false;
    }
    if(!impactCategory) {
        alert('❌ Impact Category wajib dipilih!');
        return false;
    }
    if(!impactScale) {
        alert('❌ Impact Description wajib dipilih!');
        return false;
    }
    
    return true;
}

// ================= SCROLL TO TAB WITH VALIDATION =================
function scrollToTab(tabName) {
    // Update summary fields before switching tabs
    updateSummaryFields();
    
    // Validate current tab before switching
    let currentTab = $('.tab-pane.active').attr('id');
    
    if(tabName !== currentTab) {
        // Validate based on which tab we're leaving
        if(currentTab === 'basic' && !validateBasicTab()) {
            return;
        }
        if(currentTab === 'inherent' && !validateInherentTab()) {
            return;
        }
        if(currentTab === 'treatment') {
            // Treatment tab is optional, no validation needed
        }
        if(currentTab === 'residual' && !validateResidualTab()) {
            return;
        }
    }
    
    // Fade out current tab
    $('[href="#' + currentTab + '"]').parent().find('a').removeClass('active-highlight');
    
    // Switch tab using Bootstrap's tab method
    $('[href="#' + tabName + '"]').tab('show');
    
    // Add visual feedback - flash the target tab
    $('[href="#' + tabName + '"]').addClass('active-highlight');
    setTimeout(function() {
        $('[href="#' + tabName + '"]').removeClass('active-highlight');
    }, 1500);
    
    // Scroll to top
    $('html, body').animate({scrollTop: 0}, 300);
}

// ================= TREATMENT MANAGEMENT =================
function escapeHtml(value) {
    return String(value || '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#39;');
}

function escapeAttr(value) {
    return escapeHtml(value).replace(/`/g, '&#96;');
}

function selectedRiskOwner() {
    return $('select[name="risk_owner"]').val() || '';
}

function effectivenessScoreFor(level) {
    const map = { 'Low': 0.25, 'Medium': 0.5, 'High': 1 };
    return map[level] || 0;
}

function buildIsoDateFromOffset(days) {
    if (days === null || days === undefined || days === '') {
        return '';
    }

    const baseDate = new Date();
    baseDate.setHours(0, 0, 0, 0);
    baseDate.setDate(baseDate.getDate() + Number(days));

    const year = baseDate.getFullYear();
    const month = String(baseDate.getMonth() + 1).padStart(2, '0');
    const day = String(baseDate.getDate()).padStart(2, '0');

    return `${year}-${month}-${day}`;
}

function normalizeTreatmentRowData(raw = {}) {
    const templateId = raw.templateId ? Number(raw.templateId) : '';
    const template = templateId
        ? treatmentTemplateCatalog.find((item) => Number(item.id) === Number(templateId))
        : null;
    const owner = selectedRiskOwner();
    const effectiveness = raw.effectiveness || (template ? template.default_effectiveness : '');

    return {
        templateId: templateId || '',
        templateName: raw.templateName || (template ? template.name : ''),
        option: raw.option || (template ? template.option : ''),
        description: raw.description || (template ? template.description : ''),
        pic: raw.pic || (template ? (template.default_pic || owner) : owner),
        deadline: raw.deadline || (template ? buildIsoDateFromOffset(template.target_days) : ''),
        nextActionAt: raw.nextActionAt || (template ? buildIsoDateFromOffset(template.next_action_offset_days) : ''),
        blockedReason: raw.blockedReason || '',
        checklist: raw.checklist || (template ? template.checklist : ''),
        effectiveness: effectiveness,
        effectivenessScore: raw.effectivenessScore || effectivenessScoreFor(effectiveness),
    };
}

function renderTreatmentTemplateOptions() {
    const owner = selectedRiskOwner();
    const select = $('#treatment-template-select');
    const currentValue = select.val();
    const templates = treatmentTemplateCatalog.filter((template) => !owner || template.risk_owner === owner);

    select.empty().append('<option value="">Select playbook</option>');
    templates.forEach((template) => {
        select.append(`<option value="${template.id}">${escapeHtml(template.name)} - ${escapeHtml(template.option)}</option>`);
    });

    if (templates.some((template) => String(template.id) === String(currentValue))) {
        select.val(String(currentValue));
    }

    $('#apply-template-button').prop('disabled', templates.length === 0);
}

function syncTreatmentRowsWithOwner() {
    const owner = selectedRiskOwner();

    $('.treatment-row').each(function() {
        const row = $(this);
        const templateInput = row.find('input[name="treatment_template_id[]"]');
        const templateId = Number(templateInput.val() || 0);
        if (templateId) {
            const template = treatmentTemplateCatalog.find((item) => Number(item.id) === templateId);
            if (!template || template.risk_owner !== owner) {
                templateInput.val('');
                row.find('.treatment-playbook-label').text('Manual entry');
            }
        }

        const picInput = row.find('input[name="treatment_pic[]"]');
        if (owner && picInput.val().trim() === '') {
            picInput.val(owner);
        }

        syncTreatmentRowValidation(row);
    });
}

function rowHasTreatmentContent(row) {
    const templateId = Number(row.find('input[name="treatment_template_id[]"]').val() || 0);

    if (templateId > 0) {
        return true;
    }

    const fields = [
        row.find('select[name="treatment_option[]"]').val(),
        row.find('input[name="treatment_description[]"]').val(),
        row.find('input[name="treatment_pic[]"]').val(),
        row.find('input[name="treatment_deadline[]"]').val(),
        row.find('select[name="treatment_effectiveness[]"]').val(),
        row.find('input[name="treatment_next_action_at[]"]').val(),
        row.find('input[name="treatment_blocked_reason[]"]').val(),
        row.find('textarea[name="treatment_playbook_checklist[]"]').val(),
    ];

    return fields.some((value) => (value || '').toString().trim() !== '');
}

function syncTreatmentRowValidation(row) {
    const hasContent = rowHasTreatmentContent(row);

    row.find('select[name="treatment_option[]"]').prop('required', hasContent);
    row.find('input[name="treatment_description[]"]').prop('required', hasContent);
    row.find('input[name="treatment_pic[]"]').prop('required', hasContent);
    row.find('input[name="treatment_deadline[]"]').prop('required', hasContent);
    row.find('select[name="treatment_effectiveness[]"]').prop('required', hasContent);
}

function ensureAtLeastOneTreatmentRow() {
    if ($('#treatments-container .treatment-row').length === 0) {
        addTreatmentRow();
    }
}

function addTreatmentRow(rowData = {}) {
    treatmentCount++;
    const row = normalizeTreatmentRowData(rowData);
    const html = `
        <div class="card mb-2 treatment-row" id="treatment-${treatmentCount}">
            <div class="card-body">
                <input type="hidden" name="treatment_template_id[]" value="${escapeAttr(row.templateId)}">
                <div class="d-flex justify-content-between align-items-start gap-2 mb-2">
                    <div>
                        <div class="small text-uppercase text-muted fw-bold">Treatment Action</div>
                        <div class="small text-muted treatment-playbook-label">${row.templateName ? `Playbook: ${escapeHtml(row.templateName)}` : 'Manual entry'}</div>
                    </div>
                    <button type="button" class="btn btn-danger btn-sm" onclick="removeTreatment(${treatmentCount})">
                        <i class="fas fa-trash"></i> Remove
                    </button>
                </div>
                
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Treatment Option</label>
                            <select name="treatment_option[]" class="form-control" required>
                                <option value="">Select Option</option>
                                <option value="Avoidance" ${row.option === 'Avoidance' ? 'selected' : ''}>Avoidance</option>
                                <option value="Mitigation" ${row.option === 'Mitigation' ? 'selected' : ''}>Mitigation</option>
                                <option value="Transfer" ${row.option === 'Transfer' ? 'selected' : ''}>Transfer/Insurance</option>
                                <option value="Acceptance" ${row.option === 'Acceptance' ? 'selected' : ''}>Acceptance</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="col-md-8">
                        <div class="form-group">
                            <label>Description</label>
                            <input type="text" name="treatment_description[]" class="form-control" placeholder="Describe the treatment action" value="${escapeAttr(row.description)}" required>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>PIC / Related Division</label>
                            <input type="text" name="treatment_pic[]" class="form-control" value="${escapeAttr(row.pic)}" required>
                        </div>
                    </div>
                    
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Deadline</label>
                            <input type="date" name="treatment_deadline[]" class="form-control" value="${escapeAttr(row.deadline)}" required>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Effectiveness</label>
                            <div class="input-group">
                                <select name="treatment_effectiveness[]" class="form-control treatment-effectiveness-select" onchange="onTreatmentEffectivenessChange(this)" required>
                                    <option value="">Select Level</option>
                                    <option value="Low" ${row.effectiveness === 'Low' ? 'selected' : ''}>Low</option>
                                    <option value="Medium" ${row.effectiveness === 'Medium' ? 'selected' : ''}>Medium</option>
                                    <option value="High" ${row.effectiveness === 'High' ? 'selected' : ''}>High</option>
                                </select>
                                <div class="input-group-append">
                                    <input type="text" class="form-control treatment-eff-score-display" readonly style="width:90px; text-align:center;" value="${escapeAttr(row.effectivenessScore)}" placeholder="Score">
                                </div>
                            </div>
                            <input type="hidden" name="treatment_effectiveness_score[]" value="${escapeAttr(row.effectivenessScore)}">
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Next Action Date</label>
                            <input type="date" name="treatment_next_action_at[]" class="form-control" value="${escapeAttr(row.nextActionAt)}">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Blocked Reason</label>
                            <input type="text" name="treatment_blocked_reason[]" class="form-control" maxlength="1000" placeholder="Only needed when the action becomes blocked" value="${escapeAttr(row.blockedReason)}">
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group mb-0">
                            <label>Playbook Checklist</label>
                            <textarea name="treatment_playbook_checklist[]" class="form-control" rows="3" maxlength="5000" placeholder="Evidence checklist or completion checklist">${escapeHtml(row.checklist)}</textarea>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    `;
    $('#treatments-container').append(html);
    syncTreatmentRowValidation($(`#treatment-${treatmentCount}`));
    computeResidualAdjustment();
}

function removeTreatment(id) {
    $('#treatment-' + id).remove();
}

function applySelectedTreatmentTemplate() {
    const templateId = Number($('#treatment-template-select').val() || 0);
    if (!templateId) {
        alert('Select a playbook first.');
        return;
    }

    const template = treatmentTemplateCatalog.find((item) => Number(item.id) === templateId);
    if (!template) {
        alert('Selected playbook could not be loaded.');
        return;
    }

    addTreatmentRow({
        templateId: template.id,
        templateName: template.name,
    });
}

function syncPotentialLoss(prefix, force = false){
    const impact = parseFloat($(`#${prefix}_impact_amount`).val()) || 0;
    const probabilityPercent = parseFloat($(`#${prefix}_probability_percent`).val()) || 0;
    const potentialField = $(`#${prefix}_potential_loss`);
    const currentValue = potentialField.val();
    const isManual = potentialField.data('manual') === true;

    if (force || !isManual || currentValue === '') {
        const potentialLoss = Math.round(impact * (probabilityPercent / 100));
        potentialField.val(potentialLoss > 0 ? potentialLoss : '');
        if (force) {
            potentialField.data('manual', false);
        }
    }
}

$('#inherent_impact_amount, #inherent_probability_percent').on('input', function(){
    if ($('#inherent_potential_loss').val() === '') {
        $('#inherent_potential_loss').data('manual', false);
    }
    syncPotentialLoss('inherent');
});

$('#residual_impact_amount, #residual_probability_percent').on('input', function(){
    if ($('#residual_potential_loss').val() === '') {
        $('#residual_potential_loss').data('manual', false);
    }
    syncPotentialLoss('residual');
// Double submit prevention
$('#riskForm').on('submit', function() {
    var $submitBtns = $(this).find('button[type="submit"]');
    setTimeout(function() {
        $submitBtns.prop('disabled', true).addClass('disabled');
    }, 50);
});

});

$('#inherent_potential_loss').on('input', function(){
    $(this).data('manual', true);
});

$('#residual_potential_loss').on('input', function(){
    $(this).data('manual', true);
});

// ================= INHERENT ANALYSIS CALCULATIONS =================

function syncProbabilityDescription(selectSelector, inputSelector){
    const select = $(selectSelector);
    if (!select.val()) {
        $(inputSelector).val('');
        return;
    }

    const selected = select.find(':selected');
    const description = (selected.data('description') || '').toString().trim();
    const fallback = (selected.text() || '')
        .replace(/^\s*\d+\s*[\.\-]\s*/, '')
        .replace(/\s+\(Legacy\)\s*$/i, '')
        .trim();

    $(inputSelector).val(description !== '' ? description : fallback);
}

$('#inherent_probability').change(function(){
    syncProbabilityDescription('#inherent_probability', '#inherent_prob_desc');
    calculateInherentRisk();
});

$('#inherent_impact_category').change(function(){
    let id = $(this).val();
    
    if(!id) return;
    
    $.get('/impact-scales/' + id, function(data){
        $('#inherent_impact_scale').empty().append('<option value="">Select Impact</option>');
        
        data.forEach(function(scale){
            $('#inherent_impact_scale').append(
                `<option value="${scale.id}" data-score="${scale.score}" data-desc="${scale.description}">
                    ${scale.description}
                </option>`
            );
        });
    });
});

$('#inherent_impact_scale').change(function(){
    let score = $(this).find(':selected').data('score');
    let desc = $(this).find(':selected').data('desc');
    
    $('#inherent_impact_score').val(score);
    $('#inherent_impact_desc').val(desc);
    calculateInherentRisk();
});

function calculateInherentRisk(){
    let prob = parseInt($('#inherent_probability').val()) || 0;
    let impact = parseInt($('#inherent_impact_score').val()) || 0;
    
    if(prob && impact) {
        $.get('/api/risk-score-matrix?prob=' + prob + '&impact=' + impact, function(data){
            if(data.risk_score) {
                $('#inherent_risk_score').val(data.risk_score);
                $('#inherent_risk_level').val(data.risk_level);
                // recompute residual adjustment since inherent impact (Q) changed
                computeResidualAdjustment();
                syncPotentialLoss('inherent');
            }
        });
    }
}

// ================= RESIDUAL ANALYSIS CALCULATIONS =================

$('#residual_probability').change(function(){
    syncProbabilityDescription('#residual_probability', '#residual_prob_desc');
    calculateResidualRisk();
});

$('#residual_impact_category').change(function(){
    let id = $(this).val();
    
    if(!id) return;
    
    $.get('/impact-scales/' + id, function(data){
        $('#residual_impact_scale').empty().append('<option value="">Select Impact</option>');
        
        data.forEach(function(scale){
            $('#residual_impact_scale').append(
                `<option value="${scale.id}" data-score="${scale.score}" data-desc="${scale.description}">
                    ${scale.description}
                </option>`
            );
        });
    });
});

$('#residual_impact_scale').change(function(){
    let score = $(this).find(':selected').data('score');
    let desc = $(this).find(':selected').data('desc');
    
    $('#residual_impact_score').val(score);
    $('#residual_impact_desc').val(desc);
    calculateResidualRisk();
});

function calculateResidualRisk(){
    let prob = parseInt($('#residual_probability').val()) || 0;
    let impact = parseInt($('#residual_impact_score').val()) || 0;
    
    if(prob && impact) {
        $.get('/api/risk-score-matrix?prob=' + prob + '&impact=' + impact, function(data){
            if(data.risk_score) {
                $('#residual_risk_score').val(data.risk_score);
                $('#residual_risk_level').val(data.risk_level);
                // also compute residual adjustment when residual risk changes
                computeResidualAdjustment();
                syncPotentialLoss('residual');
            }
        });
    }
}

// set effectiveness score when treatment effectiveness select changes
function onTreatmentEffectivenessChange(el){
    const map = { 'Low': 0.25, 'Medium': 0.5, 'High': 1 };
    const val = $(el).val();
    const score = map[val] || 0;
    const row = $(el).closest('.treatment-row');
    row.find('.treatment-eff-score-display').val(score);
    row.find('input[name="treatment_effectiveness_score[]"]').val(score);
    computeResidualAdjustment();
}

function computeResidualAdjustment(){
    let aa = parseFloat($('#residual_probability').val()) || 0;
    // Y = effectiveness score (average when multiple treatments exist)
    let scores = $('input[name="treatment_effectiveness_score[]"]').map(function(){ return parseFloat($(this).val()) || 0; }).get();
    let y = 0;
    if(scores.length){
        let sum = scores.reduce((a,b)=>a+b,0);
        y = sum / scores.length;
    }
    // Q = inherent impact score
    let q = parseFloat($('#inherent_impact_score').val()) || 0;

    // Residual Risk Adjustment formula:
    // IF((AA-Y)*Q < 1, 1, (AA-Y)*Q)
    let calc = (aa - y) * q;
    let adjustment = calc < 1 ? 1 : calc;
    adjustment = Number(adjustment.toFixed(2));

    // Determine Risk Level Adjustment (AC = residual impact score, AE = adjustment)
    let ac = parseInt($('#residual_impact_score').val()) || 0;
    let ae = adjustment;
    let riskLevelAdjustment = 'Moderat';
    if (ac < 5 && ae < 6) {
        riskLevelAdjustment = 'Rendah';
    } else if (ac == 5 && ae >= 10) {
        riskLevelAdjustment = 'Tinggi';
    } else if (ac < 5 && ae >= 15) {
        riskLevelAdjustment = 'Tinggi';
    } else {
        riskLevelAdjustment = 'Moderat';
    }

    // Compute final risk level from AD (residual risk score)
    let ad = parseInt($('#residual_risk_score').val()) || 0;
    let finalRiskLevel = 'High';
    if (ad < 6) {
        finalRiskLevel = 'Low';
    } else if (ad >= 5 && ad < 12) {
        finalRiskLevel = 'Low to Moderate';
    } else if (ad >= 12 && ad < 16) {
        finalRiskLevel = 'Moderate';
    } else if (ad >= 16 && ad < 20) {
        finalRiskLevel = 'Moderate to High';
    } else {
        finalRiskLevel = 'High';
    }

    // set hidden inputs and displays
    $('input[name="risk_adjustment"]').val(adjustment);
    $('#residual_adjustment_display').val(adjustment);
    $('input[name="risk_level_adjustment"]').val(riskLevelAdjustment);
    $('#risk_level_adjustment_display').val(riskLevelAdjustment);
    $('#residual_risk_level').val(finalRiskLevel);
}

function updateRadarCode(){
    let selected = $('#radar_group_id').find(':selected');
    let desc = selected.data('description') || '';
    let code = selected.data('code') || 'N/A';
    let residualFactor = parseFloat(selected.data('residual-factor'));

    $('#radar_description').val(desc);
    $('#radar_code_display').val(code);
    $('#radar_code').val(code);

    if (!isNaN(residualFactor) && residualFactor > 0) {
        const residualPercent = Number((residualFactor * 100).toFixed(2));
        const currentResidualPercent = ($('#residual_probability_percent').val() || '').toString().trim();
        if (currentResidualPercent === '' || currentResidualPercent === '30' || currentResidualPercent === '30.00') {
            $('#residual_probability_percent').val(residualPercent);
            syncPotentialLoss('residual', true);
        }
    }
}

$('#radar_group_id').on('change', function(){
    updateRadarCode();
});

$('select[name="risk_owner"]').on('change', function(){
    renderTreatmentTemplateOptions();
    syncTreatmentRowsWithOwner();
});

$(document).on('input change', 'select[name="treatment_option[]"], input[name="treatment_description[]"], input[name="treatment_pic[]"], input[name="treatment_deadline[]"], select[name="treatment_effectiveness[]"], input[name="treatment_next_action_at[]"], input[name="treatment_blocked_reason[]"], textarea[name="treatment_playbook_checklist[]"]', function(){
    syncTreatmentRowValidation($(this).closest('.treatment-row'));
});

renderTreatmentTemplateOptions();
syncTreatmentRowsWithOwner();
oldTreatmentRows.forEach(function(row){
    addTreatmentRow(row);
});
ensureAtLeastOneTreatmentRow();
$('.treatment-row').each(function(){
    syncTreatmentRowValidation($(this));
});
syncProbabilityDescription('#inherent_probability', '#inherent_prob_desc');
syncProbabilityDescription('#residual_probability', '#residual_prob_desc');
updateRadarCode();
updateSummaryFields();
syncPotentialLoss('inherent');
syncPotentialLoss('residual');

</script>

@stop


