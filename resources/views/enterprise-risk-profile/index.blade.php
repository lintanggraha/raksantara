@extends('layouts.tabler')

@section('title', 'Enterprise Risk Profile')

@section('content_header')
<div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
    <div><h1 class="page-title">Enterprise Risk Profile</h1><div class="text-muted">Portfolio concentration, top exposure, and committee decision follow-through.</div></div>
    <a href="{{ route('risks.monitoring') }}" class="btn btn-outline-secondary btn-sm"><i class="fas fa-chart-line"></i> Risk Monitoring</a>
</div>
@stop

@section('content')
@if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
@if($errors->any())<div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
<div class="row row-deck row-cards mb-3">
    <div class="col-md-3"><div class="card"><div class="card-body"><div class="subheader">Portfolio Risks</div><div class="h1 mt-1 mb-0">{{ $summary['total_risks'] }}</div></div></div></div>
    <div class="col-md-3"><div class="card bg-danger-lt"><div class="card-body"><div class="subheader">High Residual Exposure</div><div class="h1 mt-1 mb-0">{{ $summary['high_risks'] }}</div></div></div></div>
    <div class="col-md-3"><div class="card bg-warning-lt"><div class="card-body"><div class="subheader">Open Decisions</div><div class="h1 mt-1 mb-0">{{ $summary['open_decisions'] }}</div></div></div></div>
    <div class="col-md-3"><div class="card {{ $summary['overdue_decisions'] > 0 ? 'bg-danger-lt' : '' }}"><div class="card-body"><div class="subheader">Overdue Decisions</div><div class="h1 mt-1 mb-0">{{ $summary['overdue_decisions'] }}</div></div></div></div>
</div>

<div class="row g-3 mb-3">
    <div class="col-lg-7"><div class="card h-100"><div class="card-header"><strong>Top Residual Risks</strong></div><div class="table-responsive"><table class="table table-sm table-hover mb-0"><thead><tr><th>Risk</th><th>Owner</th><th>Category</th><th>Residual Score</th></tr></thead><tbody>@forelse($topRisks as $risk)<tr><td><a href="{{ route('risks.show', $risk->id) }}">{{ $risk->risk_id }}</a><div class="small text-muted">{{ \Illuminate\Support\Str::limit($risk->risk_title, 55) }}</div></td><td>{{ $risk->risk_owner ?: '-' }}</td><td>{{ optional($risk->category)->name ?: '-' }}</td><td class="fw-bold">{{ optional($risk->residualAnalysis)->residual_risk_score ?? $risk->risk_score ?? '-' }}</td></tr>@empty<tr><td colspan="4" class="text-center text-muted py-4">No risks available in your scope.</td></tr>@endforelse</tbody></table></div></div></div>
    <div class="col-lg-5"><div class="card h-100"><div class="card-header"><strong>Concentration by Owner</strong></div><div class="table-responsive"><table class="table table-sm table-hover mb-0"><thead><tr><th>Owner</th><th>Total</th><th>High</th></tr></thead><tbody>@forelse($concentrationByOwner as $item)<tr><td>{{ $item['owner'] }}</td><td>{{ $item['count'] }}</td><td class="text-danger fw-bold">{{ $item['high'] }}</td></tr>@empty<tr><td colspan="3" class="text-center text-muted py-4">No data.</td></tr>@endforelse</tbody></table></div></div></div>
</div>

@if($canManage)
<div class="card mb-3"><div class="card-header"><strong>Register Committee Decision</strong></div><div class="card-body"><form method="POST" action="{{ route('enterprise-risk-profile.decisions.store') }}" class="row g-3">@csrf
    <div class="col-md-2"><label class="form-label">Meeting Date</label><input type="date" name="meeting_date" class="form-control" value="{{ now()->format('Y-m-d') }}" required></div>
    <div class="col-md-3"><label class="form-label">Meeting Reference</label><input name="meeting_reference" class="form-control" maxlength="120"></div>
    <div class="col-md-4"><label class="form-label">Related Risk</label><select name="risk_id" class="form-select"><option value="">Enterprise-wide / no single risk</option>@foreach($risks as $risk)<option value="{{ $risk->id }}">{{ $risk->risk_id }} · {{ \Illuminate\Support\Str::limit($risk->risk_title, 55) }}</option>@endforeach</select></div>
    <div class="col-md-3"><label class="form-label">Action Owner</label><input name="owner_name" class="form-control" maxlength="255" required></div>
    <div class="col-md-4"><label class="form-label">Decision Title</label><input name="title" class="form-control" maxlength="255" required></div>
    <div class="col-md-6"><label class="form-label">Decision</label><textarea name="decision" class="form-control" rows="2" maxlength="5000" required></textarea></div>
    <div class="col-md-1"><label class="form-label">Due</label><input type="date" name="due_at" class="form-control"></div>
    <div class="col-md-1"><label class="form-label">Status</label><select name="status" class="form-select"><option value="open">Open</option><option value="in_progress">In Progress</option><option value="closed">Closed</option></select></div>
    <div class="col-md-12"><button class="btn btn-primary">Save Decision</button></div>
</form></div></div>
@endif

<div class="card"><div class="card-header"><strong>Committee Decision Register</strong></div><div class="table-responsive"><table class="table table-hover table-sm mb-0"><thead><tr><th>Meeting</th><th>Decision</th><th>Risk</th><th>Owner</th><th>Due</th><th>Status</th>@if($canManage)<th>Update</th>@endif</tr></thead><tbody>
@forelse($decisions as $decision)
<tr><td>{{ optional($decision->meeting_date)->format('d M Y') }}<div class="small text-muted">{{ $decision->meeting_reference ?: '-' }}</div></td><td><strong>{{ $decision->title }}</strong><div class="small text-muted">{{ \Illuminate\Support\Str::limit($decision->decision, 140) }}</div></td><td>{{ optional($decision->risk)->risk_id ?: 'Enterprise-wide' }}</td><td>{{ $decision->owner_name }}</td><td class="{{ $decision->due_at && $decision->due_at->isPast() && $decision->status !== 'closed' ? 'text-danger fw-bold' : '' }}">{{ optional($decision->due_at)->format('d M Y') ?: '-' }}</td><td><span class="badge bg-{{ $decision->status === 'closed' ? 'success' : ($decision->status === 'in_progress' ? 'warning text-dark' : 'secondary') }}">{{ str_replace('_', ' ', ucfirst($decision->status)) }}</span></td>
@if($canManage)<td><form method="POST" action="{{ route('enterprise-risk-profile.decisions.update', $decision->id) }}" class="d-flex gap-1">@csrf @method('PUT')<input type="hidden" name="meeting_date" value="{{ optional($decision->meeting_date)->format('Y-m-d') }}"><input type="hidden" name="meeting_reference" value="{{ $decision->meeting_reference }}"><input type="hidden" name="risk_id" value="{{ $decision->risk_id }}"><input type="hidden" name="title" value="{{ $decision->title }}"><input type="hidden" name="decision" value="{{ $decision->decision }}"><input type="hidden" name="owner_name" value="{{ $decision->owner_name }}"><input type="hidden" name="due_at" value="{{ optional($decision->due_at)->format('Y-m-d') }}"><select name="status" class="form-select form-select-sm"><option value="open" {{ $decision->status === 'open' ? 'selected' : '' }}>Open</option><option value="in_progress" {{ $decision->status === 'in_progress' ? 'selected' : '' }}>In Progress</option><option value="closed" {{ $decision->status === 'closed' ? 'selected' : '' }}>Closed</option></select><button class="btn btn-outline-primary btn-sm">Save</button></form></td>@endif</tr>
@empty<tr><td colspan="{{ $canManage ? 7 : 6 }}" class="text-center text-muted py-4">No committee decision has been registered.</td></tr>@endforelse
</tbody></table></div>@if($decisions->hasPages())<div class="card-footer">{{ $decisions->links('pagination::bootstrap-5') }}</div>@endif</div>
@stop
