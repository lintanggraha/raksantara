@extends('layouts.tabler')

@section('title', 'Vendor Details: ' . $vendor->name)

@section('content_header')
<div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
    <h1 class="mb-0">Vendor Details</h1>
    <div class="d-flex gap-2">
        <a href="{{ route('vendors.index') }}" class="btn btn-secondary btn-sm with-icon">
            <i class="fas fa-arrow-left"></i> Back to List
        </a>
        <a href="{{ route('vendors.edit', $vendor->id) }}" class="btn btn-primary btn-sm with-icon">
            <i class="fas fa-edit"></i> Edit Vendor
        </a>
    </div>
</div>
@stop

@section('content')
<div class="row row-cards">
    <div class="col-md-4">
        <div class="card shadow">
            <div class="card-header border-bottom">
                <h3 class="card-title">Vendor Information</h3>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <div class="text-muted small">Vendor Name</div>
                    <div class="fw-bold fs-3">{{ $vendor->name }}</div>
                </div>
                <div class="mb-3">
                    <div class="text-muted small">Description</div>
                    <div>{{ $vendor->description ?: '-' }}</div>
                </div>
                <div class="row mb-3">
                    <div class="col-6">
                        <div class="text-muted small">Status</div>
                        <div>
                            @if($vendor->status === 'Active')
                                <span class="badge bg-green-lt">Active</span>
                            @else
                                <span class="badge bg-secondary-lt">Inactive</span>
                            @endif
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="text-muted small">Criticality</div>
                        <div>
                            @if($vendor->criticality_level === 'High')
                                <span class="badge bg-red-lt">High</span>
                            @elseif($vendor->criticality_level === 'Medium')
                                <span class="badge bg-yellow-lt">Medium</span>
                            @elseif($vendor->criticality_level === 'Low')
                                <span class="badge bg-green-lt">Low</span>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="mb-3">
                    <div class="text-muted small">Contact Person</div>
                    <div>
                        {{ $vendor->contact_name ?: '-' }}
                        @if($vendor->contact_email)
                            <br><a href="mailto:{{ $vendor->contact_email }}">{{ $vendor->contact_email }}</a>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-8">
        <div class="card shadow">
            <div class="card-header border-bottom">
                <h3 class="card-title">Associated Risks ({{ $risks->count() }})</h3>
            </div>
            <div class="table-responsive">
                <table class="table table-vcenter mb-0">
                    <thead>
                        <tr>
                            <th>Risk ID</th>
                            <th>Title</th>
                            <th>Status</th>
                            <th>Avg. Residual Risk</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($risks as $risk)
                            <tr>
                                <td>
                                    <a href="{{ route('risks.show', $risk->id) }}" class="fw-bold">
                                        {{ $risk->risk_id ?: 'ID-'.$risk->id }}
                                    </a>
                                </td>
                                <td>
                                    <div class="text-truncate" style="max-width: 250px;">
                                        {{ $risk->risk_title }}
                                    </div>
                                    <div class="text-muted small">Owner: {{ \App\Support\RiskOwnerAccess::displayLabel($risk->risk_owner) }}</div>
                                </td>
                                <td>
                                    @if($risk->status === 'Open')
                                        <span class="badge bg-red-lt">Open</span>
                                    @elseif($risk->status === 'Monitoring')
                                        <span class="badge bg-yellow-lt">Monitoring</span>
                                    @else
                                        <span class="badge bg-green-lt">Closed</span>
                                    @endif
                                </td>
                                <td>
                                    @if($risk->residualAnalysis)
                                        @php
                                            $resLevel = $risk->residualAnalysis->risk_level;
                                            $colorClass = 'badge bg-secondary-lt';
                                            if($resLevel === 'High') $colorClass = 'badge bg-red-lt';
                                            elseif($resLevel === 'Moderate to High') $colorClass = 'badge bg-orange-lt';
                                            elseif($resLevel === 'Moderate') $colorClass = 'badge bg-yellow-lt';
                                            elseif($resLevel === 'Low to Moderate') $colorClass = 'badge bg-cyan-lt';
                                            elseif($resLevel === 'Low') $colorClass = 'badge bg-green-lt';
                                        @endphp
                                        <span class="{{ $colorClass }}">
                                            {{ $risk->residualAnalysis->residual_risk_score ?? '-' }} - 
                                            {{ collect(explode(' ', $resLevel))->map(fn($w) => substr($w,0,1))->join('') }}
                                        </span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center text-muted py-4">No risks are currently associated with this vendor.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@stop
