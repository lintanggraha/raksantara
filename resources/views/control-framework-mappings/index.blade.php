@extends('layouts.tabler')

@section('title', 'Regulatory Control Mapping')

@section('content_header')
<div class="d-flex justify-content-between align-items-center flex-wrap gap-2"><div><h1 class="page-title">Regulatory Control Mapping</h1><div class="text-muted">Connect control assurance to applicable framework requirements and evidence.</div></div><a href="{{ route('risks.monitoring') }}" class="btn btn-outline-secondary btn-sm">Risk Monitoring</a></div>
@stop

@section('content')
@if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
@if($errors->any())<div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
<div class="row row-deck row-cards mb-3"><div class="col-md-3"><div class="card"><div class="card-body"><div class="subheader">Controls in Scope</div><div class="h1 mt-1 mb-0">{{ $summary['controls'] }}</div></div></div></div><div class="col-md-3"><div class="card bg-success-lt"><div class="card-body"><div class="subheader">Mapped</div><div class="h1 mt-1 mb-0">{{ $summary['mapped'] }}</div></div></div></div><div class="col-md-3"><div class="card bg-warning-lt"><div class="card-body"><div class="subheader">Partial</div><div class="h1 mt-1 mb-0">{{ $summary['partial'] }}</div></div></div></div><div class="col-md-3"><div class="card bg-danger-lt"><div class="card-body"><div class="subheader">Gaps</div><div class="h1 mt-1 mb-0">{{ $summary['gaps'] }}</div></div></div></div></div>

@if($canManage)
<div class="card mb-3"><div class="card-header"><strong>Add or Update Mapping</strong></div><div class="card-body"><form method="POST" action="{{ route('control-framework-mappings.store') }}" class="row g-3">@csrf
<div class="col-md-4"><label class="form-label">Control</label><select name="risk_control_id" class="form-select" required><option value="">Select control</option>@foreach($controls as $control)<option value="{{ $control->id }}">{{ $control->control_code }} · {{ $control->name }} · {{ optional($control->risk)->risk_id }}</option>@endforeach</select></div>
<div class="col-md-2"><label class="form-label">Framework</label><input name="framework_code" class="form-control" maxlength="80" placeholder="e.g., ISO 27001" required></div>
<div class="col-md-2"><label class="form-label">Requirement Ref.</label><input name="control_reference" class="form-control" maxlength="120" placeholder="e.g., A.5.1" required></div>
<div class="col-md-2"><label class="form-label">Mapping Status</label><select name="mapping_status" class="form-select">@foreach($mappingStatuses as $status)<option value="{{ $status }}">{{ str_replace('_', ' ', ucfirst($status)) }}</option>@endforeach</select></div>
<div class="col-md-12"><label class="form-label">Requirement Title</label><input name="requirement_title" class="form-control" maxlength="255"></div><div class="col-md-12"><label class="form-label">Evidence Notes</label><textarea name="evidence_notes" rows="2" maxlength="5000" class="form-control"></textarea></div><div class="col-md-12"><button class="btn btn-primary">Save Mapping</button></div>
</form></div></div>
@endif

<div class="card"><div class="card-header"><strong>Mapping Register</strong></div><div class="table-responsive"><table class="table table-hover table-sm mb-0"><thead><tr><th>Control</th><th>Linked Risk</th><th>Framework Requirement</th><th>Status</th><th>Evidence / Last Review</th></tr></thead><tbody>
@forelse($controls as $control)
    @forelse($control->frameworkMappings as $mapping)
    <tr><td><strong>{{ $control->control_code }}</strong><div class="small text-muted">{{ $control->name }}</div></td><td>{{ optional($control->risk)->risk_id ?: '-' }}</td><td><strong>{{ $mapping->framework_code }} · {{ $mapping->control_reference }}</strong><div class="small text-muted">{{ $mapping->requirement_title }}</div></td><td><span class="badge bg-{{ $mapping->mapping_status === 'mapped' ? 'success' : ($mapping->mapping_status === 'gap' ? 'danger' : 'warning text-dark') }}">{{ str_replace('_', ' ', ucfirst($mapping->mapping_status)) }}</span></td><td>{{ $mapping->evidence_notes ?: '-' }}<div class="small text-muted">{{ optional($mapping->last_reviewed_at)->format('d M Y') }} · {{ optional($mapping->reviewer)->name }}</div></td></tr>
    @empty
    <tr><td><strong>{{ $control->control_code }}</strong><div class="small text-muted">{{ $control->name }}</div></td><td>{{ optional($control->risk)->risk_id ?: '-' }}</td><td colspan="3" class="text-muted">No framework mapping recorded.</td></tr>
    @endforelse
@empty<tr><td colspan="5" class="text-center text-muted py-4">No control available in your scope.</td></tr>@endforelse
</tbody></table></div></div>
@stop
