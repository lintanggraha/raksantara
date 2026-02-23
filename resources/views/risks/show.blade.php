@extends('layouts.tabler')

@section('title', 'Risk Details')

@section('content_header')
@php
    $canManage = !auth()->user()->isSuperadmin()
        && auth()->user()->canManageRiskOwner($risk->risk_owner);
    $canComment = $canManage;
    $isSuperadmin = auth()->user()->isSuperadmin();
@endphp
<div class="d-flex justify-content-between align-items-center">
    <h1>Risk Details - {{ $risk->risk_id }}</h1>
    <div class="d-flex gap-2">
        @if($canManage)
            <a href="{{ route('risks.edit', $risk->id) }}" class="btn btn-warning btn-sm with-icon">
                <i class="fas fa-edit"></i> Edit
            </a>
        @endif
        <a href="{{ route('risks.index') }}" class="btn btn-secondary btn-sm with-icon">
            <i class="fas fa-arrow-left"></i> Back
        </a>
    </div>
</div>
@stop

@section('content')
<ul class="nav nav-tabs" role="tablist">
    <li class="nav-item">
        <a class="nav-link active" href="#basic" role="tab" data-toggle="tab">Basic</a>
    </li>
    <li class="nav-item">
        <a class="nav-link" href="#inherent" role="tab" data-toggle="tab">Inherent</a>
    </li>
    <li class="nav-item">
        <a class="nav-link" href="#treatment" role="tab" data-toggle="tab">Treatment</a>
    </li>
    <li class="nav-item">
        <a class="nav-link" href="#residual" role="tab" data-toggle="tab">Residual</a>
    </li>
    <li class="nav-item">
        <a class="nav-link" href="#governance" role="tab" data-toggle="tab">Governance</a>
    </li>
</ul>

<div class="tab-content mt-3">
    <div role="tabpanel" class="tab-pane active" id="basic">
        <div class="card">
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-3"><strong>Risk ID</strong><div>{{ $risk->risk_id }}</div></div>
                    <div class="col-md-3"><strong>Year</strong><div>{{ $risk->year }}</div></div>
                    <div class="col-md-6"><strong>Process</strong><div>{{ $risk->risk_title }}</div></div>
                    <div class="col-md-12"><strong>Risk Identification</strong><div>{{ $risk->risk_description ?? '-' }}</div></div>
                    <div class="col-md-4"><strong>Category</strong><div>{{ optional($risk->category)->name ?? '-' }}</div></div>
                    <div class="col-md-4"><strong>Cause Category</strong><div>{{ optional($risk->causeCategory)->name ?? '-' }}</div></div>
                    <div class="col-md-4"><strong>Owner</strong><div>{{ $risk->risk_owner }}</div></div>
                    <div class="col-md-6"><strong>Cause</strong><div>{{ $risk->cause ?? '-' }}</div></div>
                    <div class="col-md-6"><strong>Existing Control</strong><div>{{ $risk->existing_control ?? '-' }}</div></div>
                    <div class="col-md-4"><strong>Status</strong><div>{{ $risk->status }}</div></div>
                </div>
                @if($risk->statusChangeRequests->where('status', 'pending')->isNotEmpty())
                    <div class="alert alert-warning mt-3 mb-0">
                        Pending status change request exists.
                        @if($isSuperadmin)
                            <a href="{{ route('risk-status-approvals.index') }}" class="alert-link">Open approval queue</a>.
                        @endif
                    </div>
                @endif
            </div>
        </div>
    </div>

    <div role="tabpanel" class="tab-pane" id="inherent">
        <div class="card">
            <div class="card-body">
                @if($risk->inherentAnalysis)
                    <div class="row g-3">
                        <div class="col-md-6"><strong>Probability Description</strong><div>{{ $risk->inherentAnalysis->probability_description ?? '-' }}</div></div>
                        <div class="col-md-6"><strong>Probability Score</strong><div>{{ $risk->inherentAnalysis->probability_score ?? '-' }}</div></div>
                        <div class="col-md-6"><strong>Impact Description</strong><div>{{ $risk->inherentAnalysis->impact_description ?? '-' }}</div></div>
                        <div class="col-md-6"><strong>Impact Score</strong><div>{{ $risk->inherentAnalysis->impact_score ?? '-' }}</div></div>
                        <div class="col-md-6"><strong>Inherent Risk Score</strong><div>{{ $risk->inherentAnalysis->inherent_risk_score ?? '-' }}</div></div>
                        <div class="col-md-6"><strong>Risk Level</strong><div>{{ $risk->inherentAnalysis->risk_level ?? '-' }}</div></div>
                    </div>
                @else
                    <div class="text-muted">No inherent analysis data.</div>
                @endif
            </div>
        </div>
    </div>

    <div role="tabpanel" class="tab-pane" id="treatment">
        <div class="card">
            <div class="card-body">
                @if($risk->treatments->isEmpty())
                    <div class="text-muted">No treatment options.</div>
                @else
                    @foreach($risk->treatments as $treatment)
                        <div class="border rounded p-3 mb-3">
                            <div class="row g-2">
                                <div class="col-md-2"><strong>Option</strong><div>{{ $treatment->option }}</div></div>
                                <div class="col-md-4"><strong>Description</strong><div>{{ $treatment->description ?? '-' }}</div></div>
                                <div class="col-md-2"><strong>PIC</strong><div>{{ $treatment->pic }}</div></div>
                                <div class="col-md-2"><strong>Deadline</strong><div>{{ $treatment->deadline ? \Carbon\Carbon::parse($treatment->deadline)->format('d-m-Y') : '-' }}</div></div>
                                <div class="col-md-2"><strong>Effectiveness</strong><div>{{ $treatment->effectiveness }}</div></div>
                            </div>
                            <hr>
                            <div class="d-flex justify-content-between align-items-center">
                                <strong>Treatment Evidence</strong>
                                @if($canManage)
                                    <form class="d-flex gap-2" action="{{ route('risks.treatments.attachments.store', [$risk->id, $treatment->id]) }}" method="POST" enctype="multipart/form-data">
                                        @csrf
                                        <input type="file" name="file" class="form-control form-control-sm" required>
                                        <input type="text" name="notes" class="form-control form-control-sm" maxlength="1000" placeholder="Notes">
                                        <button type="submit" class="btn btn-sm btn-primary">Upload</button>
                                    </form>
                                @endif
                            </div>
                            <div class="mt-2">
                                @forelse($treatment->attachments as $attachment)
                                    <div class="d-flex justify-content-between align-items-center border rounded px-2 py-1 mb-1">
                                        <div>
                                            <strong>{{ $attachment->original_name }}</strong>
                                            <div class="text-muted small">
                                                {{ optional($attachment->uploader)->name ?? 'System' }}
                                                - {{ optional($attachment->created_at)->format('d-m-Y H:i') ?? '-' }}
                                            </div>
                                        </div>
                                        <div class="d-flex gap-1">
                                            <a href="{{ route('attachments.download', $attachment->id) }}" class="btn btn-sm btn-outline-primary">Download</a>
                                            @if($canManage)
                                                <form action="{{ route('attachments.destroy', $attachment->id) }}" method="POST">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this attachment?')">Delete</button>
                                                </form>
                                            @endif
                                        </div>
                                    </div>
                                @empty
                                    <div class="text-muted">No treatment evidence.</div>
                                @endforelse
                            </div>
                        </div>
                    @endforeach
                @endif
            </div>
        </div>
    </div>

    <div role="tabpanel" class="tab-pane" id="residual">
        <div class="card">
            <div class="card-body">
                @if($risk->residualAnalysis)
                    <div class="row g-3">
                        <div class="col-md-6"><strong>Probability Description</strong><div>{{ $risk->residualAnalysis->probability_description ?? '-' }}</div></div>
                        <div class="col-md-6"><strong>Probability Score</strong><div>{{ $risk->residualAnalysis->probability_score ?? '-' }}</div></div>
                        <div class="col-md-6"><strong>Impact Description</strong><div>{{ $risk->residualAnalysis->impact_description ?? '-' }}</div></div>
                        <div class="col-md-6"><strong>Impact Score</strong><div>{{ $risk->residualAnalysis->impact_score ?? '-' }}</div></div>
                        <div class="col-md-6"><strong>Residual Risk Score</strong><div>{{ $risk->residualAnalysis->residual_risk_score ?? '-' }}</div></div>
                        <div class="col-md-6"><strong>Risk Level</strong><div>{{ $risk->residualAnalysis->risk_level ?? '-' }}</div></div>
                        <div class="col-md-6"><strong>Risk Adjustment</strong><div>{{ $risk->residualAnalysis->risk_adjustment ?? '-' }}</div></div>
                        <div class="col-md-6"><strong>Overall Adjustment</strong><div>{{ $risk->residualAnalysis->adjustment ?? '-' }}</div></div>
                        <div class="col-md-6"><strong>Radar Description</strong><div>{{ optional($risk->residualAnalysis->radarGroup)->description ?? $risk->residualAnalysis->radar_description ?? '-' }}</div></div>
                        <div class="col-md-6"><strong>Radar Code</strong><div>{{ optional($risk->residualAnalysis->radarGroup)->code ?? $risk->residualAnalysis->radar_code ?? '-' }}</div></div>
                    </div>
                @else
                    <div class="text-muted">No residual analysis data.</div>
                @endif
            </div>
        </div>
    </div>

    <div role="tabpanel" class="tab-pane" id="governance">
        <div class="card mb-3">
            <div class="card-header d-flex justify-content-between align-items-center">
                <strong>Risk Evidence</strong>
            </div>
            <div class="card-body">
                @if($canManage)
                    <form class="d-flex gap-2 mb-3" action="{{ route('risks.attachments.store', $risk->id) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <input type="file" name="file" class="form-control form-control-sm" required>
                        <input type="text" name="notes" class="form-control form-control-sm" maxlength="1000" placeholder="Notes">
                        <button type="submit" class="btn btn-sm btn-primary">Upload</button>
                    </form>
                @endif
                @forelse($risk->attachments as $attachment)
                    <div class="d-flex justify-content-between align-items-center border rounded px-2 py-1 mb-1">
                        <div>
                            <strong>{{ $attachment->original_name }}</strong>
                            <div class="text-muted small">
                                {{ optional($attachment->uploader)->name ?? 'System' }}
                                - {{ optional($attachment->created_at)->format('d-m-Y H:i') ?? '-' }}
                            </div>
                        </div>
                        <div class="d-flex gap-1">
                            <a href="{{ route('attachments.download', $attachment->id) }}" class="btn btn-sm btn-outline-primary">Download</a>
                            @if($canManage)
                                <form action="{{ route('attachments.destroy', $attachment->id) }}" method="POST">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this attachment?')">Delete</button>
                                </form>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="text-muted">No risk evidence.</div>
                @endforelse
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-header d-flex justify-content-between align-items-center">
                <strong>Discussion Thread</strong>
                <span class="badge bg-blue-lt">{{ $risk->comments->count() }} comments</span>
            </div>
            <div class="card-body">
                @if($canComment)
                    <form class="mb-3" action="{{ route('risks.comments.store', $risk->id) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="mb-2">
                            <label class="form-label">Comment</label>
                            <textarea name="content" class="form-control form-control-sm" rows="3" maxlength="5000" placeholder="Type your comment. Use @username to mention." required>{{ old('content') }}</textarea>
                        </div>
                        <div class="mb-2">
                            <label class="form-label">Attachments (optional)</label>
                            <input type="file" name="attachments[]" class="form-control form-control-sm" multiple>
                        </div>
                        <button type="submit" class="btn btn-sm btn-primary">Post Comment</button>
                    </form>
                @else
                    <div class="alert alert-warning py-2">
                        You do not have permission to post comments on this risk.
                    </div>
                @endif

                <div class="mt-2">
                    @forelse($risk->comments as $comment)
                        @php
                            $rawContent = (string) ($comment->content ?? '');
                            $escapedContent = e($rawContent);
                            $contentWithMentions = preg_replace(
                                '/@([A-Za-z0-9_.-]+)/',
                                '<span class="badge bg-blue-lt">@$1</span>',
                                $escapedContent
                            );
                        @endphp
                        <div class="border rounded p-2 mb-2">
                            <div class="d-flex justify-content-between align-items-start flex-wrap gap-2">
                                <div>
                                    <div class="fw-bold">{{ optional($comment->user)->name ?? 'Unknown User' }}</div>
                                    <div class="text-muted small">
                                        {{ '@' . (optional($comment->user)->username ?? '-') }}
                                        · {{ optional($comment->created_at)->format('d-m-Y H:i') ?? '-' }}
                                        @if(!is_null($comment->edited_at))
                                            · edited {{ optional($comment->edited_at)->format('d-m-Y H:i') }}
                                        @endif
                                    </div>
                                </div>
                                @if($canComment)
                                    <div class="d-flex gap-1">
                                        <details>
                                            <summary class="btn btn-sm btn-outline-warning">Edit</summary>
                                            <form class="mt-2" action="{{ route('risks.comments.update', [$risk->id, $comment->id]) }}" method="POST">
                                                @csrf
                                                @method('PUT')
                                                <textarea name="content" class="form-control form-control-sm mb-2" rows="3" maxlength="5000" required>{{ $comment->content }}</textarea>
                                                <button type="submit" class="btn btn-sm btn-warning">Save</button>
                                            </form>
                                        </details>
                                        <form action="{{ route('risks.comments.destroy', [$risk->id, $comment->id]) }}" method="POST">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this comment?')">Delete</button>
                                        </form>
                                    </div>
                                @endif
                            </div>

                            <div class="mt-2">{!! nl2br($contentWithMentions) !!}</div>

                            @if($comment->mentions->isNotEmpty())
                                <div class="small text-muted mt-2">
                                    Mentioned:
                                    {{ $comment->mentions->pluck('username')->map(function ($username) { return '@' . $username; })->implode(', ') }}
                                </div>
                            @endif

                            <div class="mt-2">
                                @forelse($comment->attachments as $attachment)
                                    <div class="d-flex justify-content-between align-items-center border rounded px-2 py-1 mb-1">
                                        <div>
                                            <strong>{{ $attachment->original_name }}</strong>
                                            <div class="text-muted small">
                                                {{ optional($attachment->uploader)->name ?? 'System' }}
                                                · {{ optional($attachment->created_at)->format('d-m-Y H:i') ?? '-' }}
                                            </div>
                                        </div>
                                        <div class="d-flex gap-1">
                                            <a href="{{ route('attachments.download', $attachment->id) }}" class="btn btn-sm btn-outline-primary">Download</a>
                                            @if($canComment)
                                                <form action="{{ route('attachments.destroy', $attachment->id) }}" method="POST">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this attachment?')">Delete</button>
                                                </form>
                                            @endif
                                        </div>
                                    </div>
                                @empty
                                    <div class="text-muted small">No comment attachments.</div>
                                @endforelse
                            </div>

                            @if($canComment)
                                <form class="d-flex gap-2 mt-2" action="{{ route('risks.comments.attachments.store', [$risk->id, $comment->id]) }}" method="POST" enctype="multipart/form-data">
                                    @csrf
                                    <input type="file" name="file" class="form-control form-control-sm" required>
                                    <input type="text" name="notes" class="form-control form-control-sm" maxlength="1000" placeholder="Attachment notes (optional)">
                                    <button type="submit" class="btn btn-sm btn-outline-primary">Attach</button>
                                </form>
                            @endif
                        </div>
                    @empty
                        <div class="text-muted">No comments yet.</div>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-header">
                <strong>Activity Feed</strong>
            </div>
            <div class="card-body table-responsive">
                <table class="table table-sm table-bordered align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Time</th>
                            <th>Actor</th>
                            <th>Action</th>
                            <th>Entity</th>
                            <th>Details</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($activityLogs as $activity)
                            <tr>
                                <td>{{ $activity['time'] }}</td>
                                <td>{{ $activity['actor'] }}</td>
                                <td>{{ $activity['event_label'] }}</td>
                                <td>{{ $activity['entity'] }}</td>
                                <td>{{ $activity['details'] }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted">No activity logs for this risk.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-header d-flex justify-content-between align-items-center">
                <strong>Status Approval History</strong>
                @if($isSuperadmin)
                    <a href="{{ route('risk-status-approvals.index') }}" class="btn btn-sm btn-outline-primary">Approval Queue</a>
                @endif
            </div>
            <div class="card-body table-responsive">
                <table class="table table-sm table-bordered">
                    <thead>
                        <tr>
                            <th>Requested At</th>
                            <th>Requester</th>
                            <th>From</th>
                            <th>To</th>
                            <th>Status</th>
                            <th>Reason</th>
                            <th>Review</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($risk->statusChangeRequests as $statusRequest)
                            <tr>
                                <td>{{ optional($statusRequest->created_at)->format('d-m-Y H:i') ?? '-' }}</td>
                                <td>{{ optional($statusRequest->requester)->name ?? '-' }}</td>
                                <td>{{ $statusRequest->from_status }}</td>
                                <td>{{ $statusRequest->to_status }}</td>
                                <td>{{ strtoupper($statusRequest->status) }}</td>
                                <td>{{ $statusRequest->reason ?: '-' }}</td>
                                <td>
                                    @if($statusRequest->status === 'pending')
                                        -
                                    @else
                                        {{ optional($statusRequest->reviewer)->name ?? '-' }}
                                        ({{ optional($statusRequest->reviewed_at)->format('d-m-Y H:i') ?? '-' }})
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted">No status change requests.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <strong>Risk Alerts</strong>
                @if($isSuperadmin)
                    <a href="{{ route('risk-alerts.index') }}" class="btn btn-sm btn-outline-primary">Alert Center</a>
                @endif
            </div>
            <div class="card-body table-responsive">
                <table class="table table-sm table-bordered">
                    <thead>
                        <tr>
                            <th>Type</th>
                            <th>Severity</th>
                            <th>Status</th>
                            <th>Due Date</th>
                            <th>Message</th>
                            <th>Created</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($risk->alerts as $alert)
                            <tr>
                                <td>{{ $alert->alert_type }}</td>
                                <td>{{ strtoupper($alert->severity) }}</td>
                                <td>{{ strtoupper($alert->status) }}</td>
                                <td>{{ $alert->due_date ? \Carbon\Carbon::parse($alert->due_date)->format('d-m-Y') : '-' }}</td>
                                <td>{{ $alert->message }}</td>
                                <td>{{ optional($alert->created_at)->format('d-m-Y H:i') ?? '-' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted">No alerts.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@stop

