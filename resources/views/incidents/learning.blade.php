@extends('layouts.tabler')

@section('title', 'Incident Learning & CAPA')

@section('content_header')
<div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
    <div>
        <h1 class="page-title">Incident Learning & CAPA</h1>
        <div class="text-muted">{{ $incident->incident_code }} · {{ $incident->title }}</div>
    </div>
    <a href="{{ route('incidents.index') }}" class="btn btn-outline-secondary btn-sm"><i class="fas fa-arrow-left"></i> Incident Dashboard</a>
</div>
@stop

@section('content')
@php
    $openActions = $incident->actions->whereNotIn('status', ['completed', 'verified'])->count();
    $overdueActions = $incident->actions->filter(fn ($action) => $action->due_at && $action->due_at->isPast() && !in_array($action->status, ['completed', 'verified'], true))->count();
@endphp
@if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
@if($errors->any())<div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif

<div class="row row-deck row-cards mb-3">
    <div class="col-md-4"><div class="card"><div class="card-body"><div class="subheader">Linked Risk</div><div class="h3 mt-1"><a href="{{ route('risks.show', $incident->risk->id) }}">{{ $incident->risk->risk_id }}</a></div><div class="text-muted">{{ $incident->risk->risk_title }}</div></div></div></div>
    <div class="col-md-4"><div class="card"><div class="card-body"><div class="subheader">Open CAPA Actions</div><div class="h1 mt-1 mb-0">{{ $openActions }}</div></div></div></div>
    <div class="col-md-4"><div class="card {{ $overdueActions > 0 ? 'bg-warning' : '' }}"><div class="card-body"><div class="subheader">Overdue CAPA Actions</div><div class="h1 mt-1 mb-0">{{ $overdueActions }}</div></div></div></div>
</div>

<div class="card mb-3">
    <div class="card-header"><strong>Root Cause and Learning Context</strong></div>
    <div class="card-body row g-3">
        <div class="col-md-6"><div class="subheader">Root Cause</div><div>{{ $incident->root_cause ?: 'Not documented yet.' }}</div></div>
        <div class="col-md-6"><div class="subheader">Immediate Action</div><div>{{ $incident->immediate_action ?: 'Not documented yet.' }}</div></div>
        <div class="col-md-12"><div class="subheader">Existing Corrective Action</div><div>{{ $incident->corrective_action ?: 'Not documented yet.' }}</div></div>
    </div>
</div>

<div class="card mb-3">
    <div class="card-header"><strong>Corrective and Preventive Actions</strong></div>
    <div class="table-responsive"><table class="table table-hover table-sm mb-0"><thead><tr><th>Type</th><th>Action</th><th>Owner</th><th>Due</th><th>Status</th><th>Completion</th><th>Action</th></tr></thead><tbody>
        @forelse($incident->actions as $action)
            <tr>
                <td><span class="badge bg-{{ $action->action_type === 'Corrective' ? 'danger' : 'info' }}">{{ $action->action_type }}</span></td>
                <td><strong>{{ $action->title }}</strong><div class="small text-muted">{{ $action->description }}</div></td>
                <td>{{ $action->owner_name }}</td>
                <td class="{{ $action->due_at && $action->due_at->isPast() && !in_array($action->status, ['completed', 'verified']) ? 'text-danger fw-bold' : '' }}">{{ optional($action->due_at)->format('d M Y') ?: '-' }}</td>
                <td><span class="badge bg-{{ $action->status === 'verified' ? 'success' : ($action->status === 'completed' ? 'primary' : 'secondary') }}">{{ str_replace('_', ' ', ucfirst($action->status)) }}</span></td>
                <td>{{ $action->completion_notes ?: '-' }}</td>
                <td>
                    @if($canManage && !in_array($action->status, ['completed', 'verified']))
                        <form method="POST" action="{{ route('incidents.learning.actions.update', [$incident->id, $action->id]) }}" class="d-flex gap-1">@csrf @method('PUT')
                            <select name="status" class="form-select form-select-sm"><option value="in_progress">In Progress</option><option value="completed">Complete</option>@if(auth()->user()->isComplianceDivision())<option value="verified">Verify</option>@endif</select>
                            <input name="completion_notes" class="form-control form-control-sm" maxlength="5000" placeholder="Completion note">
                            <button class="btn btn-primary btn-sm">Save</button>
                        </form>
                    @else
                        <span class="text-muted">{{ optional($action->completer)->name ?: '-' }}</span>
                    @endif
                </td>
            </tr>
        @empty
            <tr><td colspan="7" class="text-center text-muted py-4">No corrective or preventive action has been registered.</td></tr>
        @endforelse
    </tbody></table></div>
</div>

@if($canManage)
<div class="card mb-3"><div class="card-header"><strong>Add CAPA Action</strong></div><div class="card-body">
    <form method="POST" action="{{ route('incidents.learning.actions.store', $incident->id) }}" class="row g-3">@csrf
        <div class="col-md-2"><label class="form-label">Type</label><select name="action_type" class="form-select"><option>Corrective</option><option>Preventive</option></select></div>
        <div class="col-md-4"><label class="form-label">Action Title</label><input name="title" class="form-control" maxlength="255" required></div>
        <div class="col-md-3"><label class="form-label">Owner</label><input name="owner_name" class="form-control" maxlength="255" required></div>
        <div class="col-md-2"><label class="form-label">Due Date</label><input type="date" name="due_at" class="form-control"></div>
        <div class="col-md-1 d-flex align-items-end"><button class="btn btn-primary w-100">Add</button></div>
        <div class="col-md-12"><label class="form-label">Description</label><textarea name="description" class="form-control" rows="2" maxlength="5000"></textarea></div>
    </form>
</div></div>

<div class="card"><div class="card-header"><strong>Close the Learning Loop</strong></div><div class="card-body">
    <p class="text-muted">Complete this only after every CAPA action is completed or verified. It confirms that the incident has been assessed for changes to the linked risk, its controls, and relevant KRIs.</p>
    <form method="POST" action="{{ route('incidents.learning.review', $incident->id) }}" class="row g-3">@csrf @method('PUT')
        <div class="col-md-12"><label class="form-label">Lessons Learned</label><textarea name="lessons_learned" class="form-control" rows="4" maxlength="5000" required>{{ old('lessons_learned', $incident->lessons_learned) }}</textarea></div>
        @foreach(['risk_review_status' => 'Risk Register Review', 'control_review_status' => 'Control Assurance Review', 'kri_review_status' => 'KRI Review'] as $field => $label)
            <div class="col-md-4"><label class="form-label">{{ $label }}</label><select name="{{ $field }}" class="form-select"><option value="reviewed" {{ $incident->$field === 'reviewed' ? 'selected' : '' }}>Reviewed</option><option value="not_required" {{ $incident->$field === 'not_required' ? 'selected' : '' }}>Not Required</option></select></div>
        @endforeach
        <div class="col-md-12"><button class="btn btn-success">Complete Post-Incident Review</button></div>
    </form>
</div></div>
@endif
@stop
