@extends('adminlte::page')

@section('title', 'Risk Governance')

@section('content_header')
<div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
    <div>
        <h1 class="page-title">Control Assurance & Risk Acceptance</h1>
        <div class="text-muted">{{ $risk->risk_id }} · {{ $risk->risk_title }}</div>
    </div>
    <a href="{{ route('risks.show', $risk->id) }}" class="btn btn-outline-secondary btn-sm with-icon">
        <i class="fas fa-arrow-left"></i> Back to Risk
    </a>
</div>
@stop

@section('content')
@php
    $overdueControls = $controls->filter(fn ($control) => $control->next_test_at && $control->next_test_at->isPast())->count();
    $openAcceptances = $acceptances->whereIn('status', ['requested', 'approved'])->count();
@endphp

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif
@if($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">
            @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
        </ul>
    </div>
@endif

<div class="row mb-3">
    <div class="col-md-4"><div class="small-box bg-info"><div class="inner"><h3>{{ $controls->count() }}</h3><p>Registered Controls</p></div><div class="icon"><i class="fas fa-shield-alt"></i></div></div></div>
    <div class="col-md-4"><div class="small-box {{ $overdueControls > 0 ? 'bg-warning' : 'bg-success' }}"><div class="inner"><h3>{{ $overdueControls }}</h3><p>Overdue Control Tests</p></div><div class="icon"><i class="fas fa-calendar-times"></i></div></div></div>
    <div class="col-md-4"><div class="small-box {{ $openAcceptances > 0 ? 'bg-warning' : 'bg-secondary' }}"><div class="inner"><h3>{{ $openAcceptances }}</h3><p>Open or Pending Acceptances</p></div><div class="icon"><i class="fas fa-user-check"></i></div></div></div>
</div>

<div class="card mb-3">
    <div class="card-header"><strong>Control Assurance Register</strong></div>
    <div class="card-body p-0 table-responsive">
        <table class="table table-hover table-sm mb-0">
            <thead><tr><th>Code</th><th>Control</th><th>Owner</th><th>Frequency</th><th>Effectiveness</th><th>Last Test</th><th>Next Test</th><th>Action</th></tr></thead>
            <tbody>
                @forelse($controls as $control)
                    <tr>
                        <td><span class="badge badge-dark">{{ $control->control_code }}</span></td>
                        <td><strong>{{ $control->name }}</strong><div class="small text-muted">{{ \\Illuminate\\Support\\Str::limit($control->description, 100) }}</div></td>
                        <td>{{ $control->owner_name ?: '-' }}</td>
                        <td>{{ $control->review_frequency }}</td>
                        <td><span class="badge badge-{{ $control->design_effectiveness === 'effective' ? 'success' : ($control->design_effectiveness === 'ineffective' ? 'danger' : 'warning') }}">{{ str_replace('_', ' ', ucfirst($control->design_effectiveness)) }}</span></td>
                        <td>{{ optional($control->last_tested_at)->format('d M Y') ?: '-' }}</td>
                        <td class="{{ $control->next_test_at && $control->next_test_at->isPast() ? 'text-danger font-weight-bold' : '' }}">{{ optional($control->next_test_at)->format('d M Y') ?: '-' }}</td>
                        <td>
                            @if($canManage)
                                <button class="btn btn-outline-primary btn-xs" type="button" data-toggle="collapse" data-target="#test-control-{{ $control->id }}">Record Test</button>
                            @endif
                        </td>
                    </tr>
                    <tr class="collapse" id="test-control-{{ $control->id }}"><td colspan="8" class="bg-light">
                        @if($control->tests->isNotEmpty())
                            <div class="small mb-2">
                                <strong>Latest test:</strong> {{ optional($control->tests->first()->tested_at)->format('d M Y') }} · {{ str_replace('_', ' ', ucfirst($control->tests->first()->result)) }}
                                @if($control->tests->first()->findings) · {{ $control->tests->first()->findings }} @endif
                            </div>
                        @endif
                        @if($canManage)
                            <form method="POST" action="{{ route('risks.governance.controls.tests.store', [$risk->id, $control->id]) }}" class="row g-2">
                                @csrf
                                <div class="col-md-2"><input type="date" name="tested_at" class="form-control form-control-sm" value="{{ now()->format('Y-m-d') }}" required></div>
                                <div class="col-md-2"><select name="result" class="form-control form-control-sm" required><option value="effective">Effective</option><option value="partially_effective">Partially Effective</option><option value="ineffective">Ineffective</option></select></div>
                                <div class="col-md-3"><input type="text" name="findings" class="form-control form-control-sm" maxlength="5000" placeholder="Findings"></div>
                                <div class="col-md-2"><input type="date" name="remediation_due_at" class="form-control form-control-sm" title="Remediation due date"></div>
                                <div class="col-md-2"><input type="date" name="next_test_at" class="form-control form-control-sm" title="Next control test"></div>
                                <div class="col-md-1"><button class="btn btn-primary btn-sm" type="submit">Save</button></div>
                                <div class="col-md-12"><textarea name="remediation_plan" class="form-control form-control-sm" rows="2" maxlength="5000" placeholder="Remediation plan (required when outcome is not effective)"></textarea></div>
                            </form>
                        @endif
                    </td></tr>
                @empty
                    <tr><td colspan="8" class="text-center text-muted py-4">No controls have been registered for this risk.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@if($canManage)
<div class="card mb-4">
    <div class="card-header"><strong>Register a Control</strong></div>
    <div class="card-body">
        <form method="POST" action="{{ route('risks.governance.controls.store', $risk->id) }}" class="row g-3">
            @csrf
            <input type="hidden" name="status" value="active">
            <div class="col-md-2"><label class="form-label">Control Code</label><input name="control_code" class="form-control" maxlength="80" required></div>
            <div class="col-md-4"><label class="form-label">Control Name</label><input name="name" class="form-control" maxlength="255" required></div>
            <div class="col-md-3"><label class="form-label">Control Owner</label><input name="owner_name" class="form-control" maxlength="255"></div>
            <div class="col-md-3"><label class="form-label">Review Frequency</label><select name="review_frequency" class="form-control"><option>Monthly</option><option selected>Quarterly</option><option>Semi Annual</option><option>Annual</option></select></div>
            <div class="col-md-9"><label class="form-label">Control Description</label><textarea name="description" class="form-control" rows="2" maxlength="5000"></textarea></div>
            <div class="col-md-2"><label class="form-label">Next Test</label><input name="next_test_at" type="date" class="form-control"></div>
            <div class="col-md-1 d-flex align-items-end"><button class="btn btn-primary w-100" type="submit">Add</button></div>
        </form>
    </div>
</div>
@endif

<div class="card mb-3">
    <div class="card-header"><strong>Risk Acceptance Lifecycle</strong></div>
    <div class="card-body p-0 table-responsive">
        <table class="table table-sm table-hover mb-0">
            <thead><tr><th>Requested</th><th>Rationale</th><th>Compensating Controls</th><th>Accepted Until</th><th>Status</th><th>Decision</th></tr></thead>
            <tbody>
            @forelse($acceptances as $acceptance)
                <tr>
                    <td>{{ optional($acceptance->requester)->name ?: '-' }}<div class="small text-muted">{{ optional($acceptance->created_at)->format('d M Y H:i') }}</div></td>
                    <td>{{ \\Illuminate\\Support\\Str::limit($acceptance->rationale, 140) }}</td>
                    <td>{{ \\Illuminate\\Support\\Str::limit($acceptance->compensating_controls, 120) ?: '-' }}</td>
                    <td>{{ optional($acceptance->accepted_until)->format('d M Y') }}</td>
                    <td><span class="badge badge-{{ $acceptance->status === 'approved' ? 'success' : ($acceptance->status === 'rejected' ? 'danger' : 'warning') }}">{{ ucfirst($acceptance->status) }}</span></td>
                    <td>
                        @if($canDecide && $acceptance->isOpen())
                            <form method="POST" action="{{ route('risks.governance.acceptances.decision', [$risk->id, $acceptance->id]) }}" class="d-flex gap-1">
                                @csrf @method('PUT')
                                <input name="decision_notes" class="form-control form-control-sm" maxlength="5000" placeholder="Decision notes">
                                <button name="decision" value="approved" class="btn btn-success btn-sm">Approve</button>
                                <button name="decision" value="rejected" class="btn btn-danger btn-sm">Reject</button>
                            </form>
                        @else
                            {{ $acceptance->decision_notes ?: (optional($acceptance->reviewer)->name ?: '-') }}
                        @endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" class="text-center text-muted py-4">No risk acceptance has been requested.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>

@if($canManage)
<div class="card">
    <div class="card-header"><strong>Request Risk Acceptance</strong></div>
    <div class="card-body">
        <form method="POST" action="{{ route('risks.governance.acceptances.store', $risk->id) }}" class="row g-3">
            @csrf
            <div class="col-md-6"><label class="form-label">Business Rationale</label><textarea name="rationale" class="form-control" rows="4" maxlength="5000" required></textarea></div>
            <div class="col-md-4"><label class="form-label">Compensating Controls</label><textarea name="compensating_controls" class="form-control" rows="4" maxlength="5000"></textarea></div>
            <div class="col-md-2"><label class="form-label">Acceptance Expiry</label><input type="date" name="accepted_until" class="form-control" required><button class="btn btn-warning w-100 mt-2" type="submit">Submit</button></div>
        </form>
    </div>
</div>
@endif
@stop
