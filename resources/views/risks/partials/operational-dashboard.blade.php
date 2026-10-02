@php
    $summary = is_array($dashboardSummary ?? null) ? $dashboardSummary : [];
    $portfolio = is_array($summary['portfolio_overview'] ?? null) ? $summary['portfolio_overview'] : [];
    $sla = is_array($summary['sla'] ?? null) ? $summary['sla'] : [];
    $mitigation = is_array($summary['mitigation'] ?? null) ? $summary['mitigation'] : [];
    $kpi = is_array($summary['kpi_dashboard'] ?? null) ? $summary['kpi_dashboard'] : [];
    $deptPerf = is_array($summary['department_performance'] ?? null) ? $summary['department_performance'] : [];
    $deptRows = collect($deptPerf['rows'] ?? []);

    $totalRisks = (int) ($summary['total_risks'] ?? 0);
    $overdueTreatments = (int) ($sla['overdue_count'] ?? 0);
    $dueSoonTreatments = (int) (collect($portfolio['treatment_timeline'] ?? [])->firstWhere('label', 'Due Soon')['count'] ?? 0);
    $slaActual = (float) ($sla['actual_percent'] ?? 100);
    $coveragePercent = (float) ($mitigation['risk_coverage_percent'] ?? 0);

    $kriAlerts = collect($operationalKriAlerts ?? []);
    $breachedKriCount = $kriAlerts->where('status', 'red')->count();
    $warningKriCount = $kriAlerts->where('status', 'amber')->count();

    $incidents = collect($operationalIncidents ?? []);
    $openIncidentsCount = $incidents->count();
@endphp

<div class="operational-dashboard-view">
    {{-- Operational Health Warning Strip --}}
    <div class="row g-2 mb-3">
        {{-- KRI Alert --}}
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm border-start border-4 {{ ($breachedKriCount > 0) ? 'border-danger' : (($warningKriCount > 0) ? 'border-warning' : 'border-success') }} h-100">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <span class="text-uppercase text-muted fw-bold small">KRI Breach Alerts</span>
                            <div class="h2 mb-0 fw-bold {{ ($breachedKriCount > 0) ? 'text-danger' : 'text-dark' }}">
                                {{ $breachedKriCount }} <small class="text-muted fs-6">/ {{ $warningKriCount }} warn</small>
                            </div>
                        </div>
                        <div class="avatar rounded {{ ($breachedKriCount > 0) ? 'bg-danger-subtle text-danger' : 'bg-primary-subtle text-primary' }}">
                            <i class="fas fa-bell-exclamation fa-lg"></i>
                        </div>
                    </div>
                    <div class="mt-2 small d-flex justify-content-between">
                        <span class="text-muted">Early-warning metrics</span>
                        <a href="{{ route('kris.index') }}" class="fw-semibold text-primary text-decoration-none">Manage KRIs &rarr;</a>
                    </div>
                </div>
            </div>
        </div>

        {{-- Overdue Mitigations SLA --}}
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm border-start border-4 {{ ($overdueTreatments > 0) ? 'border-danger' : 'border-success') }} h-100">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <span class="text-uppercase text-muted fw-bold small">Overdue Action Plans</span>
                            <div class="h2 mb-0 fw-bold {{ ($overdueTreatments > 0) ? 'text-danger' : 'text-success' }}">
                                {{ $overdueTreatments }}
                            </div>
                        </div>
                        <div class="avatar rounded {{ ($overdueTreatments > 0) ? 'bg-danger-subtle text-danger' : 'bg-success-subtle text-success' }}">
                            <i class="fas fa-exclamation-circle fa-lg"></i>
                        </div>
                    </div>
                    <div class="mt-2 small d-flex justify-content-between">
                        <span class="text-muted">{{ $dueSoonTreatments }} due in &lt;14 days</span>
                        <a href="{{ route('treatments.kanban') }}" class="fw-semibold text-warning text-decoration-none">Kanban SLA &rarr;</a>
                    </div>
                </div>
            </div>
        </div>

        {{-- Open Operational Incidents --}}
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm border-start border-4 {{ ($openIncidentsCount > 0) ? 'border-warning' : 'border-success') }} h-100">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <span class="text-uppercase text-muted fw-bold small">Active Loss Events</span>
                            <div class="h2 mb-0 fw-bold {{ ($openIncidentsCount > 0) ? 'text-warning' : 'text-dark' }}">
                                {{ $openIncidentsCount }}
                            </div>
                        </div>
                        <div class="avatar rounded bg-warning-subtle text-warning">
                            <i class="fas fa-fire fa-lg"></i>
                        </div>
                    </div>
                    <div class="mt-2 small d-flex justify-content-between">
                        <span class="text-muted">Unresolved incidents</span>
                        <a href="{{ route('incidents.index') }}" class="fw-semibold text-primary text-decoration-none">Triage Incidents &rarr;</a>
                    </div>
                </div>
            </div>
        </div>

        {{-- Treatment Coverage --}}
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm border-start border-4 border-info h-100">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <span class="text-uppercase text-muted fw-bold small">Risk Control Coverage</span>
                            <div class="h2 mb-0 fw-bold text-info">
                                {{ number_format($coveragePercent, 1) }}%
                            </div>
                        </div>
                        <div class="avatar rounded bg-info-subtle text-info">
                            <i class="fas fa-shield-check fa-lg"></i>
                        </div>
                    </div>
                    <div class="mt-2 small d-flex justify-content-between">
                        <span class="text-muted">{{ number_format($slaActual, 1) }}% SLA on-time</span>
                        <a href="{{ route('risks.index') }}" class="fw-semibold text-info text-decoration-none">Review Controls &rarr;</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- KRI Early-Warning Monitor & Operational Incidents Triage --}}
    <div class="row g-3 mb-3">
        {{-- KRI Monitor --}}
        <div class="col-lg-7">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                    <div>
                        <h4 class="card-title h5 mb-0 fw-bold"><i class="fas fa-tachometer-alt text-primary me-2"></i>Early-Warning KRI Monitor</h4>
                        <small class="text-muted">Real-time thresholds, metrics, and breach alerts</small>
                    </div>
                    <a href="{{ route('kris.index') }}" class="btn btn-outline-primary btn-sm">All Indicators</a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-vcenter table-hover table-striped mb-0">
                            <thead>
                                <tr class="bg-light text-uppercase small text-muted">
                                    <th>Indicator &amp; Linked Risk</th>
                                    <th>Reading</th>
                                    <th>Thresholds</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($kriAlerts as $kri)
                                    @php
                                        $statusClass = match(strtolower($kri->status)) {
                                            'red' => 'bg-danger text-white',
                                            'amber' => 'bg-warning text-dark',
                                            default => 'bg-success text-white',
                                        };
                                        $statusLabel = match(strtolower($kri->status)) {
                                            'red' => 'Critical Breach',
                                            'amber' => 'Warning Level',
                                            default => 'Normal',
                                        };
                                    @endphp
                                    <tr>
                                        <td>
                                            <div class="fw-bold text-dark">{{ $kri->metric_name }}</div>
                                            @if($kri->risk)
                                                <small class="text-muted">
                                                    <a href="{{ route('risks.show', $kri->risk->id) }}" class="text-decoration-none">
                                                        [{{ $kri->risk->risk_id }}] {{ \Illuminate\Support\Str::limit($kri->risk->risk_title, 40) }}
                                                    </a>
                                                </small>
                                            @else
                                                <small class="text-muted">Unlinked KRI</small>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="fw-bold">{{ is_numeric($kri->current_value) ? number_format($kri->current_value, 2) : ($kri->current_value ?: '-') }}</span>
                                        </td>
                                        <td>
                                            <span class="small text-muted">Warn: {{ $kri->threshold_warn ?: '-' }} | Crit: {{ $kri->threshold_critical ?: '-' }}</span>
                                        </td>
                                        <td>
                                            <span class="badge {{ $statusClass }}">{{ $statusLabel }}</span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center py-4 text-muted">
                                            <i class="fas fa-check-circle text-success fa-2x mb-2"></i>
                                            <p class="mb-1">All Key Risk Indicators are operating within normal green thresholds.</p>
                                            <a href="{{ route('kris.create') }}" class="btn btn-sm btn-outline-primary mt-1">Configure New KRI</a>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        {{-- Operational Incidents Triage --}}
        <div class="col-lg-5">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                    <div>
                        <h4 class="card-title h5 mb-0 fw-bold"><i class="fas fa-exclamation-triangle text-warning me-2"></i>Recent Loss Events &amp; Incidents</h4>
                        <small class="text-muted">Active operational triage</small>
                    </div>
                    <a href="{{ route('incidents.index') }}" class="btn btn-outline-secondary btn-sm">Incident Center</a>
                </div>
                <div class="card-body p-2">
                    @forelse($incidents as $incident)
                        @php
                            $sevClass = match($incident->severity) {
                                'Critical' => 'bg-danger text-white',
                                'High' => 'bg-orange text-white',
                                'Medium' => 'bg-warning text-dark',
                                default => 'bg-info text-white',
                            };
                        @endphp
                        <div class="p-2 mb-2 border rounded bg-white">
                            <div class="d-flex justify-content-between align-items-start gap-1">
                                <a href="{{ route('incidents.show', $incident->id) }}" class="fw-bold small text-dark text-decoration-none text-truncate" style="max-width: 240px;">
                                    [{{ $incident->incident_code }}] {{ $incident->title }}
                                </a>
                                <span class="badge {{ $sevClass }}">{{ $incident->severity }}</span>
                            </div>
                            <div class="d-flex justify-content-between small text-muted mt-1">
                                <span>Status: <strong>{{ $incident->status }}</strong></span>
                                <span>{{ $incident->detected_at ? $incident->detected_at->diffForHumans() : 'Recently detected' }}</span>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-4 text-muted">
                            <i class="fas fa-shield-alt text-success fa-2x mb-2 opacity-50"></i>
                            <p class="small mb-1">No open or investigating incidents recorded.</p>
                            <a href="{{ route('incidents.create') }}" class="btn btn-sm btn-outline-primary">Log New Incident</a>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    {{-- Department / Division Operational Workload & Rank Table --}}
    <div class="card border-0 shadow-sm mb-3">
        <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
            <div>
                <h4 class="card-title h5 mb-0 fw-bold"><i class="fas fa-users-cog text-primary me-2"></i>Department Operational Risk Workload &amp; Performance</h4>
                <small class="text-muted">Operational rankings based on treatment closure, SLA timeliness, and active exposure</small>
            </div>
            <a href="{{ route('owners.workload') }}" class="btn btn-outline-primary btn-sm">Workload View</a>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-vcenter table-hover table-striped mb-0">
                    <thead>
                        <tr class="bg-light small text-muted text-uppercase">
                            <th style="width: 50px;">Rank</th>
                            <th>Division / Department</th>
                            <th>Active Risks</th>
                            <th>Closed Rate</th>
                            <th>Avg Score</th>
                            <th>Treatment Coverage</th>
                            <th>Overdue Items</th>
                            <th>Composite Score</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($deptRows as $row)
                            @php
                                $perfScore = (float) ($row['performance_score'] ?? 0);
                                $scoreBadge = $perfScore >= 75 ? 'bg-success text-white' : ($perfScore >= 50 ? 'bg-warning text-dark' : 'bg-danger text-white');
                            @endphp
                            <tr>
                                <td class="fw-bold text-center">#{{ $row['rank'] ?? '-' }}</td>
                                <td>
                                    <div class="fw-bold text-dark">{{ $row['division'] }}</div>
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark border">{{ number_format((int) ($row['risk_count'] ?? 0)) }} risks</span>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="small fw-semibold">{{ number_format((float) ($row['closed_rate'] ?? 0), 1) }}%</span>
                                        <div class="progress flex-grow-1" style="height: 6px; width: 60px;">
                                            <div class="progress-bar bg-success" style="width: {{ min(100, (float) ($row['closed_rate'] ?? 0)) }}%"></div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="fw-bold">{{ number_format((float) ($row['average_score'] ?? 0), 2) }}</span>
                                </td>
                                <td>
                                    <span class="small text-muted">{{ number_format((float) ($row['coverage_percent'] ?? 0), 1) }}%</span>
                                </td>
                                <td>
                                    @php $ovCount = (int) ($row['overdue_count'] ?? 0); @endphp
                                    <span class="badge {{ $ovCount > 0 ? 'bg-danger-subtle text-danger' : 'bg-success-subtle text-success' }}">
                                        {{ $ovCount }} overdue
                                    </span>
                                </td>
                                <td>
                                    <span class="badge {{ $scoreBadge }}">{{ number_format($perfScore, 1) }}</span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-4 text-muted">No division performance data available.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
