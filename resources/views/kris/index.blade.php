@extends('layouts.tabler')

@section('title', 'Key Risk Indicators Dashboard')

@section('content_header')
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h1 class="page-title">Key Risk Indicators (KRIs)</h1>
        <div class="text-muted mt-1">Enterprise Risk Threshold Dashboard</div>
    </div>
    @if($canManage)
        @if($availableRisks->isNotEmpty())
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#quickAddKriModal" data-toggle="modal" data-target="#quickAddKriModal">
                <i class="fas fa-plus"></i> Add KRI
            </button>
        @else
            <a href="{{ route('risks.index') }}" class="btn btn-outline-primary">
                <i class="fas fa-plus"></i> Add KRI
            </a>
        @endif
    @endif
</div>
@stop

@section('content')
<div class="row row-deck row-cards mb-4">
    <div class="col-md-3">
        <div class="card bg-success text-white">
            <div class="card-body">
                <div class="subheader text-white">Healthy Parameters</div>
                <div class="h1 mt-2 mb-0">{{ $summary['green'] ?? 0 }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-warning text-dark">
            <div class="card-body">
                <div class="subheader text-dark">Warning Thresholds Hit</div>
                <div class="h1 mt-2 mb-0">{{ $summary['amber'] ?? 0 }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-danger text-white">
            <div class="card-body">
                <div class="subheader text-white">Critical Violations</div>
                <div class="h1 mt-2 mb-0">{{ $summary['red'] ?? 0 }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-secondary text-white">
            <div class="card-body">
                <div class="subheader text-white">Total KRI Metrics</div>
                <div class="h1 mt-2 mb-0">{{ $summary['total'] ?? 0 }}</div>
            </div>
        </div>
    </div>
</div>

@if(!$canManage)
    <div class="alert alert-info mb-3">
        Your account is read-only for KRI updates. Use an owner/compliance account to add or edit KRI.
    </div>
@endif

@if($canManage && $availableRisks->isEmpty())
    <div class="alert alert-warning mb-3">
        No accessible risk in your scope yet. Import/upload Risk Register first to auto-generate KRI metrics.
    </div>
@endif

<div class="card mb-3">
    <div class="card-body">
        <form method="GET" action="{{ route('kris.index') }}" class="row g-2 align-items-end">
            <div class="col-md-3">
                <label class="form-label">Status</label>
                <select name="status" class="form-select">
                    <option value="">All Status</option>
                    <option value="Green" {{ ($statusFilter ?? '') === 'Green' ? 'selected' : '' }}>Green</option>
                    <option value="Amber" {{ ($statusFilter ?? '') === 'Amber' ? 'selected' : '' }}>Amber</option>
                    <option value="Red" {{ ($statusFilter ?? '') === 'Red' ? 'selected' : '' }}>Red</option>
                </select>
            </div>
            <div class="col-md-5">
                <label class="form-label">Risk</label>
                <select name="risk_id" class="form-select">
                    <option value="">All Risks</option>
                    @foreach($availableRisks as $risk)
                        <option value="{{ $risk->id }}" {{ (string) ($riskIdFilter ?? '') === (string) $risk->id ? 'selected' : '' }}>
                            {{ $risk->risk_id }} - {{ \Illuminate\Support\Str::limit($risk->risk_title, 70) }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100">Apply</button>
            </div>
            <div class="col-md-2">
                <a href="{{ route('kris.index') }}" class="btn btn-outline-secondary w-100">Reset</a>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header border-0">
        <h3 class="card-title">All Key Risk Indicators</h3>
    </div>
    <div class="table-responsive">
        <table class="table card-table table-vcenter table-hover">
            <thead>
                <tr>
                    <th>Metric & Linked Risk</th>
                    <th class="text-center">Current Value</th>
                    <th>Status</th>
                    <th>Threshold Setting</th>
                    <th>Last Updated</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($kris as $kri)
                    <tr>
                        <td>
                            <div class="fw-bold">{{ $kri->metric_name }}</div>
                            <div class="text-muted mt-n1">
                                <a href="{{ route('risks.show', $kri->risk->id) }}">{{ $kri->risk->risk_id }}</a>
                                - {{ \Illuminate\Support\Str::limit($kri->risk->risk_title, 55) }}
                            </div>
                            <div class="text-muted small">Owner: {{ $kri->risk->risk_owner ?? '-' }}</div>
                        </td>
                        <td class="text-center fw-bold fs-3">
                            {{ $kri->current_value !== null ? $kri->current_value : '-' }}
                        </td>
                        <td>
                            @if($kri->status === 'Red')
                                <span class="badge bg-danger">Critical</span>
                            @elseif($kri->status === 'Amber')
                                <span class="badge bg-warning text-dark">Warning</span>
                            @else
                                <span class="badge bg-success">Healthy</span>
                            @endif
                        </td>
                        <td>
                            <div class="small">
                                <span class="text-muted">Type:</span>
                                {{ $kri->threshold_type === 'lower_is_better' ? 'Lower is Better' : 'Higher is Better' }}<br>
                                <span class="text-warning">Warn:</span> {{ $kri->threshold_warn ?? 'None' }}<br>
                                <span class="text-danger">Crit:</span> {{ $kri->threshold_critical ?? 'None' }}
                            </div>
                        </td>
                        <td>
                            <div>{{ optional($kri->updater)->name ?? 'Unknown' }}</div>
                            <div class="text-muted small">{{ $kri->updated_at->diffForHumans() }}</div>
                        </td>
                        <td>
                            <a href="{{ route('risks.show', $kri->risk->id) }}#kris" class="btn btn-primary btn-sm btn-pill">
                                Open Risk <i class="fas fa-arrow-right ms-2"></i>
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted py-5">
                            <i class="fas fa-chart-line fa-3x mb-3 text-secondary"></i>
                            <h4>No Key Risk Indicators Setup</h4>
                            <p class="mb-2">No KRI metrics found yet. KRI is auto-generated after Risk Register import.</p>
                            <a href="{{ route('risks.index') }}" class="btn btn-outline-primary btn-sm">Open Risk Register</a>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($kris->hasPages())
        <div class="card-footer d-flex align-items-center">
            {{ $kris->links('pagination::bootstrap-5') }}
        </div>
    @endif
</div>

@if($canManage && $availableRisks->isNotEmpty())
    <div class="modal fade" id="quickAddKriModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form id="quick-kri-form" method="POST" action="{{ url('/risks/__RISK_ID__/kris') }}">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">Add Key Risk Indicator</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" data-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label required">Risk</label>
                            <select id="quick-kri-risk" name="risk_id_ref" class="form-select" required>
                                <option value="">Select risk</option>
                                @foreach($availableRisks as $risk)
                                    <option value="{{ $risk->id }}">{{ $risk->risk_id }} - {{ \Illuminate\Support\Str::limit($risk->risk_title, 65) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label required">Metric Name</label>
                            <input type="text" name="metric_name" class="form-control" placeholder="e.g., SLA Breach Count" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea name="description" class="form-control" rows="2"></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label required">Threshold Direction</label>
                            <select name="threshold_type" class="form-select" required>
                                <option value="lower_is_better" selected>Lower is Better</option>
                                <option value="higher_is_better">Higher is Better</option>
                            </select>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Warning Threshold (Amber)</label>
                                <input type="number" step="0.01" name="threshold_warn" class="form-control">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Critical Threshold (Red)</label>
                                <input type="number" step="0.01" name="threshold_critical" class="form-control">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Save KRI</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endif
@stop

@section('scripts')
@parent
@if($canManage && $availableRisks->isNotEmpty())
<script>
(function () {
    var form = document.getElementById('quick-kri-form');
    var riskSelect = document.getElementById('quick-kri-risk');

    if (!form || !riskSelect) {
        return;
    }

    form.addEventListener('submit', function (event) {
        var riskId = riskSelect.value;
        if (!riskId) {
            event.preventDefault();
            alert('Please select a risk first.');
            return;
        }

        var actionTemplate = '{{ url('/risks/__RISK_ID__/kris') }}';
        form.setAttribute('action', actionTemplate.replace('__RISK_ID__', riskId));
    });
})();
</script>
@endif
@stop

