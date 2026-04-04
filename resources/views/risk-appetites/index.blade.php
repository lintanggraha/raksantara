@extends('layouts.tabler')

@section('title', 'Corporate Risk Appetite')

@section('content')
<div class="container-xl">
    @php($emptyColspan = auth()->user()->isSuperadmin() ? 6 : 5)
    <div class="row mb-3 align-items-center">
        <div class="col">
            <h2 class="page-title">
                Corporate Risk Appetite Framework
            </h2>
            <div class="text-muted mt-1">Manage global risk thresholds and tolerance limits for the organization</div>
        </div>
        <div class="col-auto ms-auto d-print-none">
            <div class="btn-list">
                @if(auth()->user()->isSuperadmin())
                <a href="{{ route('risk-appetites.create') }}" class="btn btn-primary d-none d-sm-inline-block">
                    <i class="fas fa-plus"></i> Add New Appetite Limit
                </a>
                @endif
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible" role="alert">
            <div class="d-flex">
                <div>
                    <i class="fas fa-check-circle me-2"></i>
                </div>
                <div>
                    {{ session('success') }}
                </div>
            </div>
            <a class="btn-close" data-bs-dismiss="alert" aria-label="close"></a>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible" role="alert">
            <div class="d-flex">
                <div>
                    <i class="fas fa-exclamation-triangle me-2"></i>
                </div>
                <div>
                    {{ session('error') }}
                </div>
            </div>
            <a class="btn-close" data-bs-dismiss="alert" aria-label="close"></a>
        </div>
    @endif

    <div class="card">
        <div class="table-responsive">
            <table class="table card-table table-vcenter text-nowrap datatable">
                <thead>
                    <tr>
                        <th>Category</th>
                        <th>Name</th>
                        <th>Appetite Statement</th>
                        <th>Metrics (Appetite / Tolerance)</th>
                        <th>Status</th>
                        @if(auth()->user()->isSuperadmin())
                        <th>Actions</th>
                        @endif
                    </tr>
                </thead>
                <tbody>
                    @forelse($appetites as $appetite)
                        <tr>
                            <td><span class="badge bg-blue-lt">{{ $appetite->risk_category ?? 'General' }}</span></td>
                            <td class="font-weight-bold">
                                <a href="{{ route('risk-appetites.show', $appetite->id) }}">{{ $appetite->name }}</a>
                            </td>
                            <td class="text-wrap" style="max-width: 300px;">{{ $appetite->appetite_statement }}</td>
                            <td>
                                <div class="text-muted small mb-1">Metric: {{ $appetite->threshold_metric_label }}</div>
                                @if($appetite->appetite_limit_value !== null || $appetite->tolerance_limit_value !== null)
                                    <div>
                                        <strong>Appetite Target:</strong>
                                        {{ $appetite->appetite_limit_value !== null ? number_format($appetite->appetite_limit_value, 2) : '-' }}
                                        {{ $appetite->unit }}
                                    </div>
                                    <div class="text-danger mt-1">
                                        <strong>Max Tolerance:</strong>
                                        {{ $appetite->tolerance_limit_value !== null ? number_format($appetite->tolerance_limit_value, 2) : '-' }}
                                        {{ $appetite->unit }}
                                    </div>
                                @else
                                    <span class="text-muted">Qualitative only</span>
                                @endif
                            </td>
                            <td>
                                @if($appetite->is_active)
                                    <span class="badge bg-success">Active</span>
                                @else
                                    <span class="badge bg-secondary">Inactive</span>
                                @endif
                            </td>
                            @if(auth()->user()->isSuperadmin())
                            <td>
                                <a href="{{ route('risk-appetites.edit', $appetite->id) }}" class="btn btn-sm btn-outline-primary">Edit</a>
                            </td>
                            @endif
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ $emptyColspan }}" class="text-center text-muted py-4">No risk appetite limits declared yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
