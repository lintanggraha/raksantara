@extends('layouts.tabler')

@section('title', 'Risk Status Approvals')

@section('content_header')
<div class="d-flex justify-content-between align-items-center">
    <h1>Risk Status Approvals</h1>
    <a href="{{ route('risks.index') }}" class="btn btn-secondary btn-sm with-icon">
        <i class="fas fa-arrow-left"></i> Back
    </a>
</div>
@stop

@section('content')
<div class="card mb-3">
    <div class="card-body">
        <form method="GET" class="row g-2">
            <div class="col-md-3">
                <label class="form-label">Status</label>
                <select name="status" class="form-control">
                    <option value="pending" {{ $status === 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="approved" {{ $status === 'approved' ? 'selected' : '' }}>Approved</option>
                    <option value="rejected" {{ $status === 'rejected' ? 'selected' : '' }}>Rejected</option>
                    <option value="all" {{ $status === 'all' ? 'selected' : '' }}>All</option>
                </select>
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <button type="submit" class="btn btn-primary">Filter</button>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-body table-responsive">
        <table class="table table-sm table-bordered align-middle">
            <thead>
                <tr>
                    <th>Requested At</th>
                    <th>Risk</th>
                    <th>Requester</th>
                    <th>From</th>
                    <th>To</th>
                    <th>Status</th>
                    <th>Reason</th>
                    <th>Review</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($requests as $requestRow)
                    <tr>
                        <td>{{ optional($requestRow->created_at)->format('d-m-Y H:i') ?? '-' }}</td>
                        <td>
                            @if($requestRow->risk)
                                <a href="{{ route('risks.show', $requestRow->risk->id) }}">
                                    {{ $requestRow->risk->risk_id ?: ('ID-' . $requestRow->risk->id) }}
                                </a>
                            @else
                                -
                            @endif
                        </td>
                        <td>{{ optional($requestRow->requester)->name ?? '-' }}</td>
                        <td>{{ $requestRow->from_status }}</td>
                        <td>{{ $requestRow->to_status }}</td>
                        <td>{{ strtoupper($requestRow->status) }}</td>
                        <td>{{ $requestRow->reason ?: '-' }}</td>
                        <td>
                            @if($requestRow->status === 'pending')
                                -
                            @else
                                {{ optional($requestRow->reviewer)->name ?? '-' }}
                                ({{ optional($requestRow->reviewed_at)->format('d-m-Y H:i') ?? '-' }})
                                @if(!empty($requestRow->review_note))
                                    <div class="text-muted mt-1">{{ $requestRow->review_note }}</div>
                                @endif
                            @endif
                        </td>
                        <td>
                            @if($requestRow->status === 'pending')
                                <form action="{{ route('risk-status-approvals.approve', $requestRow->id) }}" method="POST" class="mb-1">
                                    @csrf
                                    @method('PUT')
                                    <input type="text" name="review_note" class="form-control form-control-sm mb-1" placeholder="Review note (optional)">
                                    <button type="submit" class="btn btn-sm btn-success w-100">Approve</button>
                                </form>
                                <form action="{{ route('risk-status-approvals.reject', $requestRow->id) }}" method="POST">
                                    @csrf
                                    @method('PUT')
                                    <input type="text" name="review_note" class="form-control form-control-sm mb-1" placeholder="Review note (required)">
                                    <button type="submit" class="btn btn-sm btn-danger w-100">Reject</button>
                                </form>
                            @else
                                <span class="text-muted">Reviewed</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="text-center text-muted">No status requests found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        {{ $requests->links() }}
    </div>
</div>
@stop
