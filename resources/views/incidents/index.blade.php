@extends('layouts.tabler')

@section('title', 'Incident Management')

@section('css')
<link rel="stylesheet" href="{{ asset('vendor/datatables/css/dataTables.bootstrap5.min.css') }}">
<style>
    .incident-card {
        border: 1px solid #dbe4ef;
        border-radius: 12px;
        background: #ffffff;
        box-shadow: 0 6px 16px rgba(15, 23, 42, 0.05);
        margin-bottom: 1rem;
    }

    .incident-card .card-header {
        border-bottom: 1px solid #e5edf6;
        background: #f8fbff;
    }

    .incident-kpi {
        border: 1px solid #dbe4ef;
        border-radius: 12px;
        background: linear-gradient(170deg, #f8fbff 0%, #f4f8ff 100%);
        padding: 0.85rem;
        margin-bottom: 1rem;
    }

    .incident-kpi .small-box {
        border-radius: 0.75rem;
        box-shadow: 0 8px 18px rgba(15, 23, 42, 0.08);
        min-height: 104px;
    }

    .severity-badge,
    .status-badge,
    .verdict-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 999px;
        padding: 0.2rem 0.55rem;
        font-size: 0.72rem;
        font-weight: 700;
        white-space: nowrap;
    }

    .severity-low { background: #dcfce7; color: #15803d; }
    .severity-medium { background: #e0f2fe; color: #0369a1; }
    .severity-high { background: #fef3c7; color: #a16207; }
    .severity-critical { background: #fee2e2; color: #b91c1c; }

    .status-open { background: #dbeafe; color: #1d4ed8; }
    .status-investigating { background: #fef3c7; color: #a16207; }
    .status-mitigated { background: #dcfce7; color: #15803d; }
    .status-closed { background: #e2e8f0; color: #334155; }

    .verdict-not-tested { background: #e2e8f0; color: #334155; }
    .verdict-failed { background: #fee2e2; color: #b91c1c; }
    .verdict-partially-effective { background: #fef3c7; color: #a16207; }
    .verdict-effective { background: #dcfce7; color: #15803d; }

    .incident-table-shell {
        border: 1px solid #dbe5f0;
        border-radius: 10px;
        padding: 0.4rem 0.5rem 0.5rem;
        background: #ffffff;
    }

    .incident-table th,
    .incident-table td {
        vertical-align: middle;
        padding: 0.7rem 0.58rem;
    }

    .incident-table .incident-summary {
        max-width: 280px;
        color: #334155;
    }

    .incident-chart-wrap {
        height: 280px;
    }

    .incident-form-label {
        font-size: 0.78rem;
        letter-spacing: 0.02em;
        text-transform: uppercase;
        font-weight: 700;
        color: #64748b;
    }
</style>
@endsection

@section('content_header')
<div>
    <h2 class="page-title mb-1">Incident Management</h2>
    <div class="text-muted">Catat insiden operasional dan hubungkan ke risk register untuk mengevaluasi efektivitas mitigasi.</div>
</div>
@endsection

@section('content')
@php
    $severityClassMap = [
        'Low' => 'severity-low',
        'Medium' => 'severity-medium',
        'High' => 'severity-high',
        'Critical' => 'severity-critical',
    ];
    $statusClassMap = [
        'Open' => 'status-open',
        'Investigating' => 'status-investigating',
        'Mitigated' => 'status-mitigated',
        'Closed' => 'status-closed',
    ];
    $verdictClassMap = [
        'Not Tested' => 'verdict-not-tested',
        'Failed' => 'verdict-failed',
        'Partially Effective' => 'verdict-partially-effective',
        'Effective' => 'verdict-effective',
    ];
@endphp

<div class="incident-kpi">
    <div class="row g-2 mb-0">
        <div class="col-6 col-md-4 col-xl">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3>{{ $summary['total'] ?? 0 }}</h3>
                    <p>Total Incident</p>
                </div>
                <div class="icon"><i class="fas fa-exclamation-triangle"></i></div>
            </div>
        </div>
        <div class="col-6 col-md-4 col-xl">
            <div class="small-box bg-warning">
                <div class="inner">
                    <h3>{{ $summary['active'] ?? 0 }}</h3>
                    <p>Active Handling</p>
                </div>
                <div class="icon"><i class="fas fa-tools"></i></div>
            </div>
        </div>
        <div class="col-6 col-md-4 col-xl">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3>{{ $summary['closed'] ?? 0 }}</h3>
                    <p>Closed</p>
                </div>
                <div class="icon"><i class="fas fa-check-circle"></i></div>
            </div>
        </div>
        <div class="col-6 col-md-4 col-xl">
            <div class="small-box bg-danger">
                <div class="inner">
                    <h3>{{ $summary['failed'] ?? 0 }}</h3>
                    <p>Mitigation Failed</p>
                </div>
                <div class="icon"><i class="fas fa-radiation-alt"></i></div>
            </div>
        </div>
        <div class="col-6 col-md-4 col-xl">
            <div class="small-box bg-secondary">
                <div class="inner">
                    <h3>{{ $summary['affected_risks'] ?? 0 }}</h3>
                    <p>Affected Risks</p>
                </div>
                <div class="icon"><i class="fas fa-link"></i></div>
            </div>
        </div>
    </div>
</div>

@if(!$canWrite)
    <div class="alert alert-warning">
        Superadmin hanya dapat melihat incident dashboard (read-only).
    </div>
@endif

@if($canWrite)
    <div class="card incident-card">
        <div class="card-header">
            <h3 class="card-title mb-0">Record New Incident</h3>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('incidents.store') }}">
                @csrf
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label incident-form-label">Related Risk</label>
                        <select name="risk_id" class="form-select form-select-sm" required>
                            <option value="">Select risk</option>
                            @foreach($riskOptions as $riskOption)
                                <option value="{{ $riskOption->id }}" @if((string) old('risk_id') === (string) $riskOption->id) selected @endif>
                                    {{ $riskOption->risk_id }} - {{ $riskOption->risk_title }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label incident-form-label">Incident Title</label>
                        <input type="text" name="title" class="form-control form-control-sm" maxlength="255" value="{{ old('title') }}" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label incident-form-label">Occurred At</label>
                        <input type="datetime-local" name="occurred_at" class="form-control form-control-sm" value="{{ old('occurred_at', now()->format('Y-m-d\TH:i')) }}" required>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label incident-form-label">Detected At</label>
                        <input type="datetime-local" name="detected_at" class="form-control form-control-sm" value="{{ old('detected_at') }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label incident-form-label">Severity</label>
                        <select name="severity" class="form-select form-select-sm" required>
                            @foreach($severityOptions as $option)
                                <option value="{{ $option }}" @if(old('severity', 'Medium') === $option) selected @endif>{{ $option }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label incident-form-label">Handling Status</label>
                        <select name="status" class="form-select form-select-sm" required>
                            @foreach($statusOptions as $option)
                                <option value="{{ $option }}" @if(old('status', 'Open') === $option) selected @endif>{{ $option }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label incident-form-label">Mitigation Verdict</label>
                        <select name="mitigation_verdict" class="form-select form-select-sm" required>
                            @foreach($mitigationVerdictOptions as $option)
                                <option value="{{ $option }}" @if(old('mitigation_verdict', 'Not Tested') === $option) selected @endif>{{ $option }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-12">
                        <label class="form-label incident-form-label">Incident Description</label>
                        <textarea name="incident_description" class="form-control form-control-sm" rows="3" maxlength="10000" required>{{ old('incident_description') }}</textarea>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label incident-form-label">Business Impact (Optional)</label>
                        <textarea name="business_impact" class="form-control form-control-sm" rows="2" maxlength="5000">{{ old('business_impact') }}</textarea>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label incident-form-label">Immediate Action (Optional)</label>
                        <textarea name="immediate_action" class="form-control form-control-sm" rows="2" maxlength="5000">{{ old('immediate_action') }}</textarea>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label incident-form-label">Root Cause (Optional)</label>
                        <textarea name="root_cause" class="form-control form-control-sm" rows="2" maxlength="5000">{{ old('root_cause') }}</textarea>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label incident-form-label">Corrective Action (Optional)</label>
                        <textarea name="corrective_action" class="form-control form-control-sm" rows="2" maxlength="5000">{{ old('corrective_action') }}</textarea>
                    </div>
                    <div class="col-md-9">
                        <label class="form-label incident-form-label">Resolution Notes (Optional)</label>
                        <textarea name="resolution_notes" class="form-control form-control-sm" rows="2" maxlength="5000">{{ old('resolution_notes') }}</textarea>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label incident-form-label">Closed At (Optional)</label>
                        <input type="datetime-local" name="closed_at" class="form-control form-control-sm" value="{{ old('closed_at') }}">
                    </div>
                </div>
                <div class="mt-3">
                    <button type="submit" class="btn btn-primary btn-sm with-icon">
                        <i class="fas fa-plus-circle"></i> Save Incident
                    </button>
                </div>
            </form>
        </div>
    </div>
@endif

<div class="row g-3 mb-3">
    <div class="col-lg-8">
        <div class="card incident-card h-100">
            <div class="card-header">
                <h3 class="card-title mb-0">Incident Trend (Last 6 Months)</h3>
            </div>
            <div class="card-body">
                <div class="incident-chart-wrap">
                    <canvas id="incidentTrendChart"></canvas>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card incident-card h-100">
            <div class="card-header">
                <h3 class="card-title mb-0">Mitigation Verdict Distribution</h3>
            </div>
            <div class="card-body">
                <div class="incident-chart-wrap">
                    <canvas id="verdictChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card incident-card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h3 class="card-title mb-0">Incident Register</h3>
        <span class="badge bg-blue-lt">{{ $incidents->count() }} rows</span>
    </div>
    <div class="card-body">
        <div class="incident-table-shell">
            <div class="table-responsive">
                <table id="incidentTable" class="table table-sm table-vcenter table-hover mb-0 incident-table" data-has-data="{{ $incidents->isNotEmpty() ? '1' : '0' }}">
                    <thead class="table-light">
                        <tr>
                            <th style="min-width:130px;">Incident ID</th>
                            <th style="min-width:130px;">Occurred</th>
                            <th style="min-width:240px;">Risk Link</th>
                            <th style="width:110px;">Severity</th>
                            <th style="width:130px;">Status</th>
                            <th style="width:170px;">Mitigation Verdict</th>
                            <th style="min-width:130px;">Reporter</th>
                            <th style="min-width:130px;">Last Update</th>
                            <th style="min-width:280px;">Summary</th>
                            @if($canWrite)
                                <th style="width:110px;">Action</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($incidents as $incident)
                            @php
                                $severityClass = $severityClassMap[$incident->severity] ?? 'severity-medium';
                                $statusClass = $statusClassMap[$incident->status] ?? 'status-open';
                                $verdictClass = $verdictClassMap[$incident->mitigation_verdict] ?? 'verdict-not-tested';
                                $reporterName = optional($incident->reporter)->name ?? '-';
                                $updatedBy = optional($incident->updater)->name ?? '-';
                                $editPayload = [
                                    'id' => $incident->id,
                                    'risk_id' => $incident->risk_id,
                                    'title' => (string) $incident->title,
                                    'incident_description' => (string) $incident->incident_description,
                                    'occurred_at' => !is_null($incident->occurred_at) ? $incident->occurred_at->format('Y-m-d\TH:i') : null,
                                    'detected_at' => !is_null($incident->detected_at) ? $incident->detected_at->format('Y-m-d\TH:i') : null,
                                    'severity' => (string) $incident->severity,
                                    'status' => (string) $incident->status,
                                    'mitigation_verdict' => (string) $incident->mitigation_verdict,
                                    'business_impact' => (string) ($incident->business_impact ?? ''),
                                    'immediate_action' => (string) ($incident->immediate_action ?? ''),
                                    'root_cause' => (string) ($incident->root_cause ?? ''),
                                    'corrective_action' => (string) ($incident->corrective_action ?? ''),
                                    'resolution_notes' => (string) ($incident->resolution_notes ?? ''),
                                    'closed_at' => !is_null($incident->closed_at) ? $incident->closed_at->format('Y-m-d\TH:i') : null,
                                ];
                            @endphp
                            <tr>
                                <td class="fw-bold">{{ $incident->incident_code }}</td>
                                <td data-order="{{ optional($incident->occurred_at)->timestamp ?? 0 }}">
                                    {{ optional($incident->occurred_at)->format('d M Y H:i') ?? '-' }}
                                </td>
                                <td>
                                    <div class="fw-semibold">{{ optional($incident->risk)->risk_id ?? '-' }}</div>
                                    <div class="small text-muted">{{ optional($incident->risk)->risk_title ?? '-' }}</div>
                                </td>
                                <td><span class="severity-badge {{ $severityClass }}">{{ $incident->severity }}</span></td>
                                <td><span class="status-badge {{ $statusClass }}">{{ $incident->status }}</span></td>
                                <td><span class="verdict-badge {{ $verdictClass }}">{{ $incident->mitigation_verdict }}</span></td>
                                <td>{{ $reporterName }}</td>
                                <td>
                                    <div>{{ optional($incident->updated_at)->format('d M Y H:i') ?? '-' }}</div>
                                    <div class="small text-muted">{{ $updatedBy }}</div>
                                </td>
                                <td class="incident-summary">
                                    <div class="fw-semibold">{{ $incident->title }}</div>
                                    <div class="small text-muted">{{ \Illuminate\Support\Str::limit((string) $incident->incident_description, 120) }}</div>
                                </td>
                                @if($canWrite)
                                    <td>
                                        <div class="d-flex flex-column gap-1">
                                            <button
                                                type="button"
                                                class="btn btn-warning btn-xs with-icon justify-content-center js-incident-edit"
                                                data-incident='@json($editPayload)'
                                            >
                                                <i class="fas fa-edit"></i> Edit
                                            </button>
                                            <form method="POST" action="{{ route('incidents.destroy', $incident->id) }}">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger btn-xs with-icon justify-content-center w-100" onclick="return confirm('Delete incident {{ $incident->incident_code }}?')">
                                                    <i class="fas fa-trash"></i> Delete
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                @endif
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ $canWrite ? 10 : 9 }}" class="text-center py-4 text-muted">
                                    Belum ada incident yang tercatat.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@if($canWrite)
    <div class="modal fade" id="editIncidentModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content">
                <form id="editIncidentForm" method="POST" action="#">
                    @csrf
                    @method('PUT')
                    <div class="modal-header">
                        <h5 class="modal-title">Edit Incident</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label incident-form-label">Related Risk</label>
                                <select name="risk_id" id="edit_risk_id" class="form-select form-select-sm" required>
                                    <option value="">Select risk</option>
                                    @foreach($riskOptions as $riskOption)
                                        <option value="{{ $riskOption->id }}">
                                            {{ $riskOption->risk_id }} - {{ $riskOption->risk_title }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label incident-form-label">Incident Title</label>
                                <input type="text" name="title" id="edit_title" class="form-control form-control-sm" maxlength="255" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label incident-form-label">Occurred At</label>
                                <input type="datetime-local" name="occurred_at" id="edit_occurred_at" class="form-control form-control-sm" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label incident-form-label">Detected At</label>
                                <input type="datetime-local" name="detected_at" id="edit_detected_at" class="form-control form-control-sm">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label incident-form-label">Severity</label>
                                <select name="severity" id="edit_severity" class="form-select form-select-sm" required>
                                    @foreach($severityOptions as $option)
                                        <option value="{{ $option }}">{{ $option }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label incident-form-label">Handling Status</label>
                                <select name="status" id="edit_status" class="form-select form-select-sm" required>
                                    @foreach($statusOptions as $option)
                                        <option value="{{ $option }}">{{ $option }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label incident-form-label">Mitigation Verdict</label>
                                <select name="mitigation_verdict" id="edit_mitigation_verdict" class="form-select form-select-sm" required>
                                    @foreach($mitigationVerdictOptions as $option)
                                        <option value="{{ $option }}">{{ $option }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-12">
                                <label class="form-label incident-form-label">Incident Description</label>
                                <textarea name="incident_description" id="edit_incident_description" class="form-control form-control-sm" rows="3" maxlength="10000" required></textarea>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label incident-form-label">Business Impact (Optional)</label>
                                <textarea name="business_impact" id="edit_business_impact" class="form-control form-control-sm" rows="2" maxlength="5000"></textarea>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label incident-form-label">Immediate Action (Optional)</label>
                                <textarea name="immediate_action" id="edit_immediate_action" class="form-control form-control-sm" rows="2" maxlength="5000"></textarea>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label incident-form-label">Root Cause (Optional)</label>
                                <textarea name="root_cause" id="edit_root_cause" class="form-control form-control-sm" rows="2" maxlength="5000"></textarea>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label incident-form-label">Corrective Action (Optional)</label>
                                <textarea name="corrective_action" id="edit_corrective_action" class="form-control form-control-sm" rows="2" maxlength="5000"></textarea>
                            </div>
                            <div class="col-md-9">
                                <label class="form-label incident-form-label">Resolution Notes (Optional)</label>
                                <textarea name="resolution_notes" id="edit_resolution_notes" class="form-control form-control-sm" rows="2" maxlength="5000"></textarea>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label incident-form-label">Closed At (Optional)</label>
                                <input type="datetime-local" name="closed_at" id="edit_closed_at" class="form-control form-control-sm">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary btn-sm">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endif
@endsection

@section('js')
<script src="{{ asset('vendor/datatables/js/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('vendor/datatables/js/dataTables.bootstrap5.min.js') }}"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
<script>
    (function () {
        const table = $('#incidentTable');
        const hasData = table.data('has-data') === 1 || table.data('has-data') === '1';
        if (table.length && hasData) {
            table.DataTable({
                order: [[1, 'desc']],
                pageLength: 10,
                lengthMenu: [[10, 25, 50, -1], [10, 25, 50, 'All']],
                autoWidth: false,
            });
        }

        const trendCtx = document.getElementById('incidentTrendChart');
        const verdictCtx = document.getElementById('verdictChart');
        const monthlyTrend = @json($monthlyTrend ?? ['labels' => [], 'values' => []]);
        const verdictDistribution = @json($verdictDistribution ?? ['labels' => [], 'values' => []]);

        if (trendCtx && Array.isArray(monthlyTrend.labels) && monthlyTrend.labels.length > 0) {
            new Chart(trendCtx, {
                type: 'line',
                data: {
                    labels: monthlyTrend.labels,
                    datasets: [{
                        label: 'Incident Count',
                        data: monthlyTrend.values,
                        borderColor: '#2563eb',
                        backgroundColor: 'rgba(37, 99, 235, 0.18)',
                        borderWidth: 2,
                        pointRadius: 3,
                        tension: 0.25,
                        fill: true,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                precision: 0
                            }
                        }
                    }
                }
            });
        }

        if (verdictCtx && Array.isArray(verdictDistribution.labels) && verdictDistribution.labels.length > 0) {
            new Chart(verdictCtx, {
                type: 'doughnut',
                data: {
                    labels: verdictDistribution.labels,
                    datasets: [{
                        data: verdictDistribution.values,
                        backgroundColor: ['#94a3b8', '#ef4444', '#f59e0b', '#22c55e'],
                        borderColor: '#ffffff',
                        borderWidth: 2
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom'
                        }
                    }
                }
            });
        }

        const editModalEl = document.getElementById('editIncidentModal');
        if (!editModalEl) {
            return;
        }

        const editModal = new bootstrap.Modal(editModalEl);
        const editForm = document.getElementById('editIncidentForm');
        const updateRouteTemplate = @json(route('incidents.update', ['incident' => '__ID__']));
        const editButtons = document.querySelectorAll('.js-incident-edit');

        const fieldIds = [
            'risk_id',
            'title',
            'occurred_at',
            'detected_at',
            'severity',
            'status',
            'mitigation_verdict',
            'incident_description',
            'business_impact',
            'immediate_action',
            'root_cause',
            'corrective_action',
            'resolution_notes',
            'closed_at',
        ];

        function setFieldValue(fieldName, value) {
            const element = document.getElementById('edit_' + fieldName);
            if (!element) {
                return;
            }

            element.value = value || '';
        }

        editButtons.forEach((button) => {
            button.addEventListener('click', function () {
                let payload = {};
                try {
                    payload = JSON.parse(button.getAttribute('data-incident') || '{}');
                } catch (error) {
                    payload = {};
                }

                if (!payload.id) {
                    return;
                }

                editForm.setAttribute('action', updateRouteTemplate.replace('__ID__', String(payload.id)));

                fieldIds.forEach((fieldName) => {
                    setFieldValue(fieldName, payload[fieldName] ?? '');
                });

                editModal.show();
            });
        });
    })();
</script>
@endsection
