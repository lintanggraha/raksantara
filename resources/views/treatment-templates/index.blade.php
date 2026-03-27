@extends('layouts.tabler')

@section('title', 'Mitigation Playbooks')

@section('content_header')
<div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
    <div>
        <h1 class="page-title">Mitigation Playbooks</h1>
        <div class="text-muted mt-1">Reusable treatment templates and evidence checklists for recurring risks.</div>
    </div>
    <a href="{{ route('risks.index') }}" class="btn btn-outline-secondary btn-sm with-icon">
        <i class="fas fa-arrow-left"></i> Back to Register
    </a>
</div>
@stop

@section('content')
@if(!$canManage)
    <div class="alert alert-info">
        Your account is read-only for mitigation playbook management.
    </div>
@endif

@if($canManage)
    <div class="card mb-3">
        <div class="card-header">
            <strong>Create Playbook</strong>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('treatment-templates.store') }}">
                @csrf
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Playbook Name</label>
                        <input type="text" name="name" class="form-control" maxlength="255" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Risk Owner</label>
                        <select name="risk_owner" class="form-control" required>
                            <option value="">Select owner</option>
                            @foreach($allowedRiskOwners as $owner)
                                <option value="{{ $owner }}">{{ $owner }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Treatment Option</label>
                        <select name="option" class="form-control" required>
                            <option value="Avoidance">Avoidance</option>
                            <option value="Mitigation" selected>Mitigation</option>
                            <option value="Transfer">Transfer/Insurance</option>
                            <option value="Acceptance">Acceptance</option>
                        </select>
                    </div>
                    <div class="col-md-8">
                        <label class="form-label">Default Action Description</label>
                        <textarea name="description" class="form-control" rows="3" maxlength="5000" required></textarea>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Default PIC</label>
                        <input type="text" name="default_pic" class="form-control" maxlength="255" placeholder="Optional">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Effectiveness</label>
                        <select name="default_effectiveness" class="form-control" required>
                            <option value="Low">Low</option>
                            <option value="Medium" selected>Medium</option>
                            <option value="High">High</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Target Deadline (Days)</label>
                        <input type="number" name="target_days" class="form-control" min="0" max="3650" placeholder="e.g. 30">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Next Action Offset (Days)</label>
                        <input type="number" name="next_action_offset_days" class="form-control" min="0" max="3650" placeholder="e.g. 7">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label d-block">Status</label>
                        <label class="form-check form-switch mt-2">
                            <input type="checkbox" class="form-check-input" name="is_active" value="1" checked>
                            <span class="form-check-label">Active</span>
                        </label>
                    </div>
                    <div class="col-md-12">
                        <label class="form-label">Checklist</label>
                        <textarea name="checklist" class="form-control" rows="4" maxlength="5000" placeholder="One line per evidence or step"></textarea>
                    </div>
                </div>
                <div class="mt-3">
                    <button type="submit" class="btn btn-primary">Save Playbook</button>
                </div>
            </form>
        </div>
    </div>
@endif

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <strong>Playbook Library</strong>
        <span class="text-muted small">{{ $templates->count() }} playbooks</span>
    </div>
    <div class="card-body">
        @forelse($templates as $template)
            <div class="border rounded p-3 mb-3">
                <div class="d-flex justify-content-between align-items-start flex-wrap gap-2">
                    <div>
                        <div class="fw-bold">{{ $template->name }}</div>
                        <div class="small text-muted">
                            {{ $template->risk_owner }} · {{ $template->option }} · {{ $template->default_effectiveness }}
                            @if(!$template->is_active)
                                · Inactive
                            @endif
                        </div>
                    </div>
                    <div class="small text-muted">
                        {{ optional($template->creator)->name ?? 'Unknown creator' }}
                    </div>
                </div>

                <div class="mt-2">{{ $template->description }}</div>

                <div class="row g-2 mt-1">
                    <div class="col-md-3">
                        <div class="small text-muted">Default PIC</div>
                        <div>{{ $template->default_pic ?: '-' }}</div>
                    </div>
                    <div class="col-md-3">
                        <div class="small text-muted">Target Deadline</div>
                        <div>{{ is_null($template->target_days) ? '-' : $template->target_days . ' days' }}</div>
                    </div>
                    <div class="col-md-3">
                        <div class="small text-muted">Next Action</div>
                        <div>{{ is_null($template->next_action_offset_days) ? '-' : $template->next_action_offset_days . ' days' }}</div>
                    </div>
                    <div class="col-md-3">
                        <div class="small text-muted">Checklist Lines</div>
                        <div>{{ collect(preg_split('/\r\n|\r|\n/', (string) $template->checklist))->filter()->count() }}</div>
                    </div>
                </div>

                @if(trim((string) $template->checklist) !== '')
                    <div class="mt-2">
                        <div class="small text-muted mb-1">Checklist Preview</div>
                        <pre class="mb-0 p-2 bg-light border rounded small" style="white-space: pre-wrap;">{{ $template->checklist }}</pre>
                    </div>
                @endif

                @if($canManage && $template->canManageBy(auth()->user()))
                    <details class="mt-3">
                        <summary class="btn btn-sm btn-outline-primary">Edit Playbook</summary>
                        <div class="mt-3">
                            <form method="POST" action="{{ route('treatment-templates.update', $template->id) }}">
                                @csrf
                                @method('PUT')
                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <label class="form-label">Playbook Name</label>
                                        <input type="text" name="name" class="form-control" value="{{ $template->name }}" maxlength="255" required>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Risk Owner</label>
                                        <select name="risk_owner" class="form-control" required>
                                            @foreach($allowedRiskOwners as $owner)
                                                <option value="{{ $owner }}" {{ $template->risk_owner === $owner ? 'selected' : '' }}>{{ $owner }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Treatment Option</label>
                                        <select name="option" class="form-control" required>
                                            <option value="Avoidance" {{ $template->option === 'Avoidance' ? 'selected' : '' }}>Avoidance</option>
                                            <option value="Mitigation" {{ $template->option === 'Mitigation' ? 'selected' : '' }}>Mitigation</option>
                                            <option value="Transfer" {{ $template->option === 'Transfer' ? 'selected' : '' }}>Transfer/Insurance</option>
                                            <option value="Acceptance" {{ $template->option === 'Acceptance' ? 'selected' : '' }}>Acceptance</option>
                                        </select>
                                    </div>
                                    <div class="col-md-8">
                                        <label class="form-label">Default Action Description</label>
                                        <textarea name="description" class="form-control" rows="3" maxlength="5000" required>{{ $template->description }}</textarea>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Default PIC</label>
                                        <input type="text" name="default_pic" class="form-control" value="{{ $template->default_pic }}" maxlength="255">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">Effectiveness</label>
                                        <select name="default_effectiveness" class="form-control" required>
                                            <option value="Low" {{ $template->default_effectiveness === 'Low' ? 'selected' : '' }}>Low</option>
                                            <option value="Medium" {{ $template->default_effectiveness === 'Medium' ? 'selected' : '' }}>Medium</option>
                                            <option value="High" {{ $template->default_effectiveness === 'High' ? 'selected' : '' }}>High</option>
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">Target Deadline (Days)</label>
                                        <input type="number" name="target_days" class="form-control" min="0" max="3650" value="{{ $template->target_days }}">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">Next Action Offset (Days)</label>
                                        <input type="number" name="next_action_offset_days" class="form-control" min="0" max="3650" value="{{ $template->next_action_offset_days }}">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label d-block">Status</label>
                                        <label class="form-check form-switch mt-2">
                                            <input type="checkbox" class="form-check-input" name="is_active" value="1" {{ $template->is_active ? 'checked' : '' }}>
                                            <span class="form-check-label">Active</span>
                                        </label>
                                    </div>
                                    <div class="col-md-12">
                                        <label class="form-label">Checklist</label>
                                        <textarea name="checklist" class="form-control" rows="4" maxlength="5000">{{ $template->checklist }}</textarea>
                                    </div>
                                </div>
                                <div class="d-flex flex-wrap gap-2 mt-3">
                                    <button type="submit" class="btn btn-primary btn-sm">Update</button>
                                </div>
                            </form>
                            <form method="POST" action="{{ route('treatment-templates.destroy', $template->id) }}" class="mt-2">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-outline-danger btn-sm" onclick="return confirm('Delete this playbook?')">Delete Playbook</button>
                            </form>
                        </div>
                    </details>
                @endif
            </div>
        @empty
            <div class="text-muted">No mitigation playbooks available yet.</div>
        @endforelse
    </div>
</div>
@stop
