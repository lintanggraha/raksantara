@extends('layouts.tabler')

@section('title', 'Risk Alerts')

@section('content_header')
<div class="d-flex justify-content-between align-items-center">
    <h1>Risk Alerts</h1>
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
                    <option value="open" {{ $status === 'open' ? 'selected' : '' }}>Open</option>
                    <option value="resolved" {{ $status === 'resolved' ? 'selected' : '' }}>Resolved</option>
                    <option value="all" {{ $status === 'all' ? 'selected' : '' }}>All</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Type</label>
                <select name="type" class="form-control">
                    <option value="">All Types</option>
                    @foreach($types as $alertType)
                        <option value="{{ $alertType }}" {{ $type === $alertType ? 'selected' : '' }}>{{ $alertType }}</option>
                    @endforeach
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
                    <th>Created</th>
                    <th>Risk</th>
                    <th>Treatment</th>
                    <th>Type</th>
                    <th>Severity</th>
                    <th>Status</th>
                    <th>Due Date</th>
                    <th>Message</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($alerts as $alert)
                    <tr>
                        <td>{{ optional($alert->created_at)->format('d-m-Y H:i') ?? '-' }}</td>
                        <td>
                            @if($alert->risk)
                                <a href="{{ route('risks.show', $alert->risk->id) }}">
                                    {{ $alert->risk->risk_id ?: ('ID-' . $alert->risk->id) }}
                                </a>
                            @else
                                -
                            @endif
                        </td>
                        <td>{{ $alert->risk_treatment_id ?: '-' }}</td>
                        <td>{{ $alert->alert_type }}</td>
                        <td>{{ strtoupper($alert->severity) }}</td>
                        <td>{{ strtoupper($alert->status) }}</td>
                        <td>{{ $alert->due_date ? \Carbon\Carbon::parse($alert->due_date)->format('d-m-Y') : '-' }}</td>
                        <td>{{ $alert->message }}</td>
                        <td>
                            @if($alert->status === 'open')
                                <form action="{{ route('risk-alerts.resolve', $alert->id) }}" method="POST">
                                    @csrf
                                    @method('PUT')
                                    <button type="submit" class="btn btn-sm btn-success">Resolve</button>
                                </form>
                            @else
                                <span class="text-muted">Resolved</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="text-center text-muted">No alerts found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        {{ $alerts->links() }}
    </div>
</div>
@stop
