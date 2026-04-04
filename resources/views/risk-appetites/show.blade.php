@extends('layouts.tabler')

@section('title', 'Corporate Risk Appetite: ' . $riskAppetite->name)

@section('content')
<div class="container-xl">
    <div class="row mb-3 align-items-center">
        <div class="col">
            <h2 class="page-title">{{ $riskAppetite->name }}</h2>
            <div class="text-muted mt-1">Corporate Risk Appetite Framework detail and visible linked risks</div>
        </div>
        <div class="col-auto ms-auto d-print-none">
            <div class="btn-list">
                <a href="{{ route('risk-appetites.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left"></i> Back
                </a>
                @if(auth()->user()->isSuperadmin())
                    <a href="{{ route('risk-appetites.edit', $riskAppetite->id) }}" class="btn btn-primary">
                        <i class="fas fa-edit"></i> Edit Framework
                    </a>
                @endif
            </div>
        </div>
    </div>

    <div class="row row-cards">
        <div class="col-lg-5">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Framework Summary</h3>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <div class="text-muted small">Category</div>
                        <div><span class="badge bg-blue-lt">{{ $riskAppetite->risk_category ?? 'General' }}</span></div>
                    </div>

                    <div class="mb-3">
                        <div class="text-muted small">Status</div>
                        <div>
                            @if($riskAppetite->is_active)
                                <span class="badge bg-success">Active</span>
                            @else
                                <span class="badge bg-secondary">Inactive</span>
                            @endif
                        </div>
                    </div>

                    <div class="mb-3">
                        <div class="text-muted small">Threshold Metric</div>
                        <div class="fw-bold">{{ $riskAppetite->threshold_metric_label }}</div>
                    </div>

                    <div class="mb-3">
                        <div class="text-muted small">Appetite Statement</div>
                        <div>{{ $riskAppetite->appetite_statement }}</div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <div class="text-muted small">Appetite Target</div>
                            <div class="fw-bold">
                                {{ $riskAppetite->appetite_limit_value !== null ? number_format($riskAppetite->appetite_limit_value, 2) : '-' }}
                                {{ $riskAppetite->unit }}
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="text-muted small">Max Tolerance</div>
                            <div class="fw-bold text-danger">
                                {{ $riskAppetite->tolerance_limit_value !== null ? number_format($riskAppetite->tolerance_limit_value, 2) : '-' }}
                                {{ $riskAppetite->unit }}
                            </div>
                        </div>
                    </div>

                    <div class="mb-0">
                        <div class="text-muted small">Visible Linked Risks</div>
                        <div class="fw-bold">{{ $linkedRisks->count() }}</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-7">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Linked Risks In Your Scope</h3>
                </div>
                <div class="table-responsive">
                    <table class="table table-vcenter card-table">
                        <thead>
                            <tr>
                                <th>Risk ID</th>
                                <th>Title</th>
                                <th>Owner</th>
                                <th>Residual</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($linkedRisks as $risk)
                                <tr>
                                    <td>
                                        <a href="{{ route('risks.show', $risk->id) }}">{{ $risk->risk_id ?: 'ID-' . $risk->id }}</a>
                                    </td>
                                    <td>{{ $risk->risk_title }}</td>
                                    <td>{{ \App\Support\RiskOwnerAccess::displayLabel($risk->risk_owner) }}</td>
                                    <td>
                                        @if($risk->residualAnalysis)
                                            {{ $risk->residualAnalysis->residual_risk_score ?? '-' }}
                                            <span class="text-muted">/ {{ $risk->residualAnalysis->risk_level ?? '-' }}</span>
                                        @else
                                            <span class="text-muted">Not assessed</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center text-muted py-4">No linked risks are visible in your current scope.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
