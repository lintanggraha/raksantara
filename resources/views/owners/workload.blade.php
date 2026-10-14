@extends('layouts.tabler')

@section('title', 'Owner Workload Dashboard')

@section('content_header')
<div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
    <div>
        <h1 class="page-title">Owner Workload Dashboard</h1>
        <div class="text-muted mt-1">Snapshot of owner workload across risk actions, pending approvals, and red KRIs in your visible scope.</div>
    </div>
    <a href="{{ route('risks.monitoring') }}" class="btn btn-outline-secondary btn-sm with-icon">
        <i class="fas fa-arrow-left"></i> Back to Monitoring
    </a>
</div>
@stop

@section('content')
<div class="row g-3 mb-3">
    <div class="col-md-2 col-sm-6">
        <div class="card">
            <div class="card-body">
                <div class="text-muted small">Owners in Scope</div>
                <div class="h2 mb-0">{{ $summary['owners'] }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-2 col-sm-6">
        <div class="card">
            <div class="card-body">
                <div class="text-muted small">Open Actions</div>
                <div class="h2 mb-0">{{ $summary['open_treatments'] }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-2 col-sm-6">
        <div class="card">
            <div class="card-body">
                <div class="text-muted small">Overdue</div>
                <div class="h2 mb-0 text-danger">{{ $summary['overdue_treatments'] }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-2 col-sm-6">
        <div class="card">
            <div class="card-body">
                <div class="text-muted small">Blocked</div>
                <div class="h2 mb-0 text-warning">{{ $summary['blocked_treatments'] }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-2 col-sm-6">
        <div class="card">
            <div class="card-body">
                <div class="text-muted small">Pending Approvals</div>
                <div class="h2 mb-0">{{ $summary['pending_approvals'] }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-2 col-sm-6">
        <div class="card">
            <div class="card-body">
                <div class="text-muted small">Red KRIs</div>
                <div class="h2 mb-0 text-danger">{{ $summary['kri_red_count'] }}</div>
            </div>
        </div>
    </div>
</div>

<div class="card mb-3">
    <div class="card-body d-flex flex-wrap justify-content-between align-items-center gap-2">
        <div>
            <div class="small text-muted text-uppercase fw-bold">Highest Attention Owner</div>
            <div class="h3 mb-0">{{ $summary['highest_owner'] }}</div>
        </div>
        <div class="text-end">
            <div class="small text-muted text-uppercase fw-bold">Workload Score</div>
            <div class="h3 mb-0">{{ $summary['highest_score'] }}</div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <strong>Owner Queue</strong>
        <span class="text-muted small">Sorted by highest workload score</span>
    </div>
    <div class="card-body table-responsive">
        <table class="table table-sm table-bordered table-vcenter mb-0">
            <thead>
                <tr>
                    <th>Owner</th>
                    <th class="text-center">Attention</th>
                    <th class="text-end">Risk Count</th>
                    <th class="text-end">High Risks</th>
                    <th class="text-end">Open Actions</th>
                    <th class="text-end">Overdue</th>
                    <th class="text-end">Blocked</th>
                    <th class="text-end">Due Soon</th>
                    <th class="text-end">Pending Approvals</th>
                    <th class="text-end">Red KRIs</th>
                    <th class="text-end">Score</th>
                </tr>
            </thead>
            <tbody>
                @forelse($rows as $row)
                    @php
                        $attentionClass = 'bg-success text-white';
                        if ($row['attention_level'] === 'Critical Focus') {
                            $attentionClass = 'bg-danger text-white';
                        } elseif ($row['attention_level'] === 'High Attention') {
                            $attentionClass = 'bg-warning text-dark';
                        } elseif ($row['attention_level'] === 'Watch') {
                            $attentionClass = 'bg-info text-white';
                        }
                    @endphp
                    <tr>
                        <td>
                            <div class="fw-bold">{{ $row['owner_label'] }}</div>
                            <div class="small text-muted">{{ $row['owner'] }}</div>
                        </td>
                        <td class="text-center">
                            <span class="badge {{ $attentionClass }}">{{ $row['attention_level'] }}</span>
                        </td>
                        <td class="text-end">{{ $row['risk_count'] }}</td>
                        <td class="text-end">{{ $row['high_risk_count'] }}</td>
                        <td class="text-end">{{ $row['open_treatments'] }}</td>
                        <td class="text-end text-danger">{{ $row['overdue_treatments'] }}</td>
                        <td class="text-end text-warning">{{ $row['blocked_treatments'] }}</td>
                        <td class="text-end">{{ $row['due_soon_treatments'] }}</td>
                        <td class="text-end">{{ $row['pending_approvals'] }}</td>
                        <td class="text-end text-danger">{{ $row['kri_red_count'] }}</td>
                        <td class="text-end fw-bold">{{ $row['workload_score'] }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="11" class="text-center text-muted py-4">No owner workload data available in the current scope.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@stop
