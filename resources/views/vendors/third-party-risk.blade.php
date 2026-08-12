@extends('layouts.tabler')

@section('title', 'Third-Party Risk Lifecycle')

@section('content_header')
<div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
    <div><h1 class="page-title">Third-Party Risk Lifecycle</h1><div class="text-muted">{{ $vendor->name }} · onboarding, due diligence, monitoring, and remediation.</div></div>
    <div class="d-flex gap-2"><a href="{{ route('vendors.show', $vendor->id) }}" class="btn btn-outline-secondary btn-sm">Vendor Overview</a><a href="{{ route('vendors.index') }}" class="btn btn-outline-secondary btn-sm">Vendor Register</a></div>
</div>
@stop

@section('content')
@if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
@if($errors->any())<div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
<div class="row row-deck row-cards mb-3">
    <div class="col-md-3"><div class="card"><div class="card-body"><div class="subheader">Lifecycle State</div><div class="h3 mt-1 mb-0 text-capitalize">{{ str_replace('_', ' ', $vendor->onboarding_status) }}</div></div></div></div>
    <div class="col-md-3"><div class="card"><div class="card-body"><div class="subheader">Risk Rating</div><div class="h1 mt-1 mb-0">{{ $vendor->risk_rating ?: '-' }}</div></div></div></div>
    <div class="col-md-3"><div class="card"><div class="card-body"><div class="subheader">Open Assessment Findings</div><div class="h1 mt-1 mb-0">{{ $summary['open_findings'] }}</div></div></div></div>
    <div class="col-md-3"><div class="card {{ $summary['due_review'] ? 'bg-warning-lt' : '' }}"><div class="card-body"><div class="subheader">Next Review</div><div class="h3 mt-1 mb-0">{{ optional($vendor->next_review_at)->format('d M Y') ?: '-' }}</div></div></div></div>
</div>

<div class="card mb-3"><div class="card-header"><strong>Third-Party Context</strong></div><div class="card-body row g-3"><div class="col-md-4"><div class="subheader">Service Owner</div><div>{{ $vendor->service_owner ?: '-' }}</div></div><div class="col-md-4"><div class="subheader">Data Classification</div><div>{{ $vendor->data_classification ?: '-' }}</div></div><div class="col-md-4"><div class="subheader">Contract End</div><div>{{ optional($vendor->contract_end_date)->format('d M Y') ?: '-' }}</div></div><div class="col-md-12"><div class="subheader">Linked Risks</div>@forelse($vendor->risks as $risk)<a href="{{ route('risks.show', $risk->id) }}" class="badge bg-secondary me-1">{{ $risk->risk_id }}</a>@empty<span class="text-muted">No risk linked.</span>@endforelse</div></div></div>

@if($canManage)
<div class="card mb-3"><div class="card-header"><strong>Record Assessment</strong></div><div class="card-body"><form method="POST" action="{{ route('vendors.third-party-risk.assessments.store', $vendor->id) }}" class="row g-3">@csrf
    <div class="col-md-3"><label class="form-label">Assessment Type</label><select name="assessment_type" class="form-select">@foreach($assessmentTypes as $type)<option>{{ $type }}</option>@endforeach</select></div>
    <div class="col-md-2"><label class="form-label">Assessed At</label><input type="date" name="assessed_at" value="{{ now()->format('Y-m-d') }}" class="form-control" required></div>
    <div class="col-md-2"><label class="form-label">Inherent (1–5)</label><input type="number" min="1" max="5" name="inherent_rating" class="form-control"></div>
    <div class="col-md-2"><label class="form-label">Residual (1–5)</label><input type="number" min="1" max="5" name="residual_rating" class="form-control"></div>
    <div class="col-md-3"><label class="form-label">Decision</label><select name="decision" class="form-select">@foreach($decisions as $decision)<option>{{ $decision }}</option>@endforeach</select></div>
    <div class="col-md-3"><label class="form-label">Valid Until / Next Review</label><input type="date" name="valid_until" class="form-control"></div>
    <div class="col-md-3"><label class="form-label">Assessment Status</label><select name="status" class="form-select">@foreach($statusOptions as $status)<option value="{{ $status }}">{{ str_replace('_', ' ', ucfirst($status)) }}</option>@endforeach</select></div>
    <div class="col-md-6"><label class="form-label">Findings</label><textarea name="findings" rows="2" maxlength="5000" class="form-control"></textarea></div>
    <div class="col-md-12"><label class="form-label">Risk Treatment</label><textarea name="risk_treatment" rows="2" maxlength="5000" class="form-control"></textarea></div>
    <div class="col-md-12"><button class="btn btn-primary">Save Assessment</button></div>
</form></div></div>
@endif

<div class="card"><div class="card-header"><strong>Assessment History</strong></div><div class="table-responsive"><table class="table table-hover table-sm mb-0"><thead><tr><th>Date</th><th>Type</th><th>Ratings</th><th>Decision</th><th>Valid Until</th><th>Findings / Treatment</th><th>Status</th></tr></thead><tbody>
@forelse($vendor->assessments as $assessment)
<tr><td>{{ optional($assessment->assessed_at)->format('d M Y') }}</td><td>{{ $assessment->assessment_type }}</td><td>{{ $assessment->inherent_rating ?: '-' }} → {{ $assessment->residual_rating ?: '-' }}</td><td><span class="badge bg-{{ $assessment->decision === 'Approved' ? 'success' : ($assessment->decision === 'Conditional' ? 'warning text-dark' : 'danger') }}">{{ $assessment->decision }}</span></td><td>{{ optional($assessment->valid_until)->format('d M Y') ?: '-' }}</td><td><div>{{ $assessment->findings ?: '-' }}</div><div class="small text-muted">{{ $assessment->risk_treatment ?: '' }}</div></td><td>{{ str_replace('_', ' ', ucfirst($assessment->status)) }}</td></tr>
@empty<tr><td colspan="7" class="text-center text-muted py-4">No vendor risk assessment recorded.</td></tr>@endforelse
</tbody></table></div></div>
@stop
