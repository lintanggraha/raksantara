@extends('layouts.tabler')

@section('title', 'RCSA Workspace')

@section('content_header')
<div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
    <div>
        <h1 class="page-title">RCSA Workspace - {{ $risk->risk_id }}</h1>
        <div class="text-muted mt-1">{{ $risk->risk_title }}</div>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('risks.show', $risk->id) }}" class="btn btn-outline-secondary btn-sm with-icon">
            <i class="fas fa-arrow-left"></i> Back to Risk
        </a>
    </div>
</div>
@stop

@section('content')
<div class="card mb-3">
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-3">
                <div class="small text-muted">Risk Owner</div>
                <div class="fw-bold">{{ \App\Support\RiskOwnerAccess::displayLabel($risk->risk_owner) }}</div>
            </div>
            <div class="col-md-3">
                <div class="small text-muted">Category</div>
                <div>{{ optional($risk->category)->name ?? '-' }}</div>
            </div>
            <div class="col-md-3">
                <div class="small text-muted">Status</div>
                <div>{{ $risk->status }}</div>
            </div>
            <div class="col-md-3">
                <div class="small text-muted">Last Update</div>
                <div>
                    @if($workspace)
                        {{ optional($workspace->updated_at)->format('d-m-Y H:i') ?? '-' }}
                        @if($workspace->updater)
                            · {{ $workspace->updater->name }}
                        @endif
                    @else
                        -
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@if(!$canManage)
    <div class="alert alert-info">
        This RCSA workspace is read-only for your account.
    </div>
@endif

<form method="POST" action="{{ route('risks.rcsa.update', $risk->id) }}">
    @csrf
    @method('PUT')

    <div class="card mb-3">
        <div class="card-header">
            <strong>Assessment Context</strong>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Process Owner</label>
                    <input type="text" name="process_owner" class="form-control" maxlength="255" value="{{ old('process_owner', optional($workspace)->process_owner) }}" {{ $canManage ? '' : 'disabled' }}>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Review Frequency</label>
                    <select name="review_frequency" class="form-control" {{ $canManage ? '' : 'disabled' }}>
                        <option value="">Select frequency</option>
                        @foreach(['Monthly', 'Quarterly', 'Semi Annual', 'Annual'] as $frequency)
                            <option value="{{ $frequency }}" {{ old('review_frequency', optional($workspace)->review_frequency) === $frequency ? 'selected' : '' }}>{{ $frequency }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Next Review Date</label>
                    <input type="date" name="next_review_at" class="form-control" value="{{ old('next_review_at', optional(optional($workspace)->next_review_at)->format('Y-m-d')) }}" {{ $canManage ? '' : 'disabled' }}>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Objective</label>
                    <textarea name="objective" class="form-control" rows="4" maxlength="5000" {{ $canManage ? '' : 'disabled' }}>{{ old('objective', optional($workspace)->objective) }}</textarea>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Scope</label>
                    <textarea name="scope" class="form-control" rows="4" maxlength="5000" {{ $canManage ? '' : 'disabled' }}>{{ old('scope', optional($workspace)->scope) }}</textarea>
                </div>
            </div>
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-header">
            <strong>Control Assessment</strong>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Key Controls</label>
                    <textarea name="key_controls" class="form-control" rows="5" maxlength="5000" {{ $canManage ? '' : 'disabled' }}>{{ old('key_controls', optional($workspace)->key_controls) }}</textarea>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Control Gaps / Improvement Themes</label>
                    <textarea name="control_gaps" class="form-control" rows="5" maxlength="5000" {{ $canManage ? '' : 'disabled' }}>{{ old('control_gaps', optional($workspace)->control_gaps) }}</textarea>
                </div>
            </div>
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-header">
            <strong>Residual Rationale</strong>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Likelihood Rationale</label>
                    <textarea name="likelihood_rationale" class="form-control" rows="5" maxlength="5000" {{ $canManage ? '' : 'disabled' }}>{{ old('likelihood_rationale', optional($workspace)->likelihood_rationale) }}</textarea>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Impact Rationale</label>
                    <textarea name="impact_rationale" class="form-control" rows="5" maxlength="5000" {{ $canManage ? '' : 'disabled' }}>{{ old('impact_rationale', optional($workspace)->impact_rationale) }}</textarea>
                </div>
                <div class="col-md-12">
                    <label class="form-label">Notes</label>
                    <textarea name="notes" class="form-control" rows="4" maxlength="5000" {{ $canManage ? '' : 'disabled' }}>{{ old('notes', optional($workspace)->notes) }}</textarea>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <strong>Sign-Off</strong>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Sign-Off Name</label>
                    <input type="text" name="sign_off_name" class="form-control" maxlength="255" value="{{ old('sign_off_name', optional($workspace)->sign_off_name) }}" {{ $canManage ? '' : 'disabled' }}>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Sign-Off Role</label>
                    <input type="text" name="sign_off_role" class="form-control" maxlength="255" value="{{ old('sign_off_role', optional($workspace)->sign_off_role) }}" {{ $canManage ? '' : 'disabled' }}>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Signed-Off Date</label>
                    <input type="date" name="signed_off_at" class="form-control" value="{{ old('signed_off_at', optional(optional($workspace)->signed_off_at)->format('Y-m-d')) }}" {{ $canManage ? '' : 'disabled' }}>
                </div>
            </div>

            @if($canManage)
                <div class="mt-3">
                    <button type="submit" class="btn btn-primary">Save RCSA Workspace</button>
                </div>
            @endif
        </div>
    </div>
</form>
@stop
