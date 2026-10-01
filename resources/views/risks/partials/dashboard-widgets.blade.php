@php
    $widgetOptions = [
        ['id' => 'status_metrics', 'label' => 'Status & Core Metrics'],
        ['id' => 'portfolio_overview', 'label' => 'Portfolio Command Center'],
        ['id' => 'benchmark_scoring', 'label' => 'Benchmark Scoring'],
        ['id' => 'mitigation_sla', 'label' => 'Mitigation, SLA & KPI'],
        ['id' => 'department_performance', 'label' => 'Department / Division Performance'],
        ['id' => 'root_cause_analysis', 'label' => 'Root Cause Analysis'],
        ['id' => 'variance_analysis', 'label' => 'Variance Analysis'],
    ];
    $portfolioOverview = is_array($dashboardSummary['portfolio_overview'] ?? null) ? $dashboardSummary['portfolio_overview'] : [];
    $portfolioPriorityRows = is_array($portfolioOverview['by_priority'] ?? null) ? $portfolioOverview['by_priority'] : [];
    $portfolioCategoryRows = is_array($portfolioOverview['by_category'] ?? null) ? $portfolioOverview['by_category'] : [];
    $portfolioDivisionRows = is_array($portfolioOverview['by_division'] ?? null) ? $portfolioOverview['by_division'] : [];
    $portfolioTreatmentTimelineRows = is_array($portfolioOverview['treatment_timeline'] ?? null) ? $portfolioOverview['treatment_timeline'] : [];
    $portfolioStatusRows = is_array($portfolioOverview['risk_status'] ?? null) ? $portfolioOverview['risk_status'] : [];
    $portfolioTopRiskRows = is_array($portfolioOverview['top_risks'] ?? null) ? $portfolioOverview['top_risks'] : [];
    $varianceRows = is_array($dashboardSummary['variance']['rows'] ?? null) ? $dashboardSummary['variance']['rows'] : [];
    $benchmarkScoring = is_array($dashboardSummary['benchmark_scoring'] ?? null) ? $dashboardSummary['benchmark_scoring'] : [];
    $benchmarkSourceReferenceRaw = trim((string) ($benchmarkScoring['source_reference'] ?? '-'));
    $benchmarkSourceReferenceUrl = null;
    $benchmarkSourceReferenceNote = '';
    if (preg_match('/https?:\/\/[^\s]+/i', $benchmarkSourceReferenceRaw, $referenceMatches) === 1) {
        $benchmarkSourceReferenceUrl = rtrim((string) $referenceMatches[0], '.,;)');
        $benchmarkSourceReferenceNote = trim((string) str_replace($referenceMatches[0], '', $benchmarkSourceReferenceRaw));
    }

    $mitigationEffectivenessPercent = (float) ($dashboardSummary['mitigation']['effectiveness_percent'] ?? 0);
    $mitigationTreatmentCount = (int) ($dashboardSummary['mitigation']['treatment_count'] ?? 0);
    $mitigationCoveragePercent = (float) ($dashboardSummary['mitigation']['risk_coverage_percent'] ?? 0);

    $kpiDashboard = is_array($dashboardSummary['kpi_dashboard'] ?? null) ? $dashboardSummary['kpi_dashboard'] : [];
    $kpiAvgResponseDays = (float) ($kpiDashboard['avg_response_time_days'] ?? 0);
    $kpiResponseWithinSlaPercent = (float) ($kpiDashboard['response_within_sla_percent'] ?? 0);
    $kpiClosedRiskRate = (float) ($kpiDashboard['closed_risk_rate'] ?? 0);
    $kpiOverdueRate = (float) ($kpiDashboard['overdue_treatment_rate'] ?? 0);
    $kpiResponseSlaDays = (int) ($kpiDashboard['response_sla_days'] ?? 7);
    $kpiResponseSampleCount = (int) ($kpiDashboard['response_sample_count'] ?? 0);

    $departmentPerformance = is_array($dashboardSummary['department_performance'] ?? null) ? $dashboardSummary['department_performance'] : [];
    $departmentRows = is_array($departmentPerformance['rows'] ?? null) ? $departmentPerformance['rows'] : [];
    $departmentTopDivision = (string) ($departmentPerformance['top_division'] ?? '-');
    $departmentAverageScore = (float) ($departmentPerformance['average_score'] ?? 0);
    $departmentTotalRisks = (int) ($departmentPerformance['total_risks'] ?? collect($departmentRows)->sum(function ($row) {
        return (int) ($row['risk_count'] ?? 0);
    }));
    $departmentDivisionCount = (int) ($departmentPerformance['division_count'] ?? count($departmentRows));

    $rootCauseAnalysis = is_array($dashboardSummary['root_cause_analysis'] ?? null) ? $dashboardSummary['root_cause_analysis'] : [];
    $rootCauseCategoryRows = is_array($rootCauseAnalysis['top_categories'] ?? null) ? $rootCauseAnalysis['top_categories'] : [];
    $rootCauseKeywordRows = is_array($rootCauseAnalysis['top_keywords'] ?? null) ? $rootCauseAnalysis['top_keywords'] : [];
    $rootCausePatternRows = is_array($rootCauseAnalysis['patterns'] ?? null) ? $rootCauseAnalysis['patterns'] : [];

    $slaActualPercent = (float) ($dashboardSummary['sla']['actual_percent'] ?? 0);
    $slaTargetPercent = (float) ($dashboardSummary['sla']['target_percent'] ?? 95);
    $slaDueCount = (int) ($dashboardSummary['sla']['due_count'] ?? 0);
    $slaCompliantCount = (int) ($dashboardSummary['sla']['compliant_count'] ?? 0);
    $slaOverdueCount = (int) ($dashboardSummary['sla']['overdue_count'] ?? 0);

    $mitigationBarWidth = max(0, min(100, $mitigationEffectivenessPercent));
    $slaBarWidth = max(0, min(100, $slaActualPercent));
@endphp

<div class="dashboard-section mb-4">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-2">
        <div>
            <h2 class="dashboard-title mb-0">Real-time Risk Summary</h2>
            <small class="text-muted">
                Last refresh: <span id="dashboardGeneratedAt">{{ $dashboardSummary['generated_at'] ?? '-' }}</span>
            </small>
        </div>
        <div class="d-flex align-items-center justify-content-end flex-wrap gap-2 dashboard-header-actions">
            @if(request()->routeIs('risks.monitoring'))
                @include('reports.export-buttons', [
                    'scope' => 'monitoring',
                    'scopeLabel' => 'Monitoring',
                ])
                @if(!auth()->user()->isSuperadmin())
                    <a href="{{ route('risks.create') }}" class="btn btn-success btn-sm with-icon">
                        <i class="fas fa-plus"></i> Add Risk
                    </a>
                @endif
            @endif
            <div class="dropdown">
                <button
                    class="btn btn-outline-dark btn-sm with-icon"
                    type="button"
                    id="widgetCustomizeButton"
                    data-bs-toggle="dropdown"
                    data-toggle="dropdown"
                    data-widget-customize-toggle
                    aria-controls="widgetCustomizeMenu"
                    aria-expanded="false"
                >
                    <i class="fas fa-sliders-h"></i> Customize Widgets
                </button>
                <div
                    class="dropdown-menu dropdown-menu-end p-3 widget-config-menu"
                    id="widgetCustomizeMenu"
                    data-widget-customize-menu
                    aria-labelledby="widgetCustomizeButton"
                >
                    <div class="fw-bold mb-2">Show/Hide Widgets</div>
                    @foreach($widgetOptions as $widget)
                        <label class="d-flex align-items-center gap-2 mb-2">
                            <input
                                type="checkbox"
                                class="form-check-input m-0 widget-visibility-toggle"
                                data-widget-id="{{ $widget['id'] }}"
                                checked
                            >
                            <span>{{ $widget['label'] }}</span>
                        </label>
                    @endforeach
                    <small class="text-muted d-block mt-2">Drag cards by handle to reorder.</small>
                </div>
            </div>
        </div>
    </div>

    <div id="dashboardWidgetGrid" class="dashboard-widget-grid">
        <div class="dashboard-tab-nav mb-3" role="tablist" aria-label="Operational View Tabs">
            <button
                type="button"
                class="dashboard-tab-btn active"
                data-dashboard-tab="all"
                aria-selected="true"
            >
                <span class="tab-title">All Operational Widgets</span>
                <span class="tab-subtitle">Complete Operational View</span>
            </button>
            <button
                type="button"
                class="dashboard-tab-btn"
                data-dashboard-tab="ops_control"
                aria-selected="false"
            >
                <span class="tab-title">Operations &amp; SLA</span>
                <span class="tab-subtitle">Mitigations &amp; Department Performance</span>
            </button>
            <button
                type="button"
                class="dashboard-tab-btn"
                data-dashboard-tab="exec_snapshot"
                aria-selected="false"
            >
                <span class="tab-title">Portfolio Breakdown</span>
                <span class="tab-subtitle">Status &amp; Risk Command Center</span>
            </button>
            <button
                type="button"
                class="dashboard-tab-btn"
                data-dashboard-tab="risk_intelligence"
                aria-selected="false"
            >
                <span class="tab-title">Risk Intelligence</span>
                <span class="tab-subtitle">Root Cause &amp; Variance Analysis</span>
            </button>
        </div>

        <div class="card dashboard-widget" draggable="true" data-widget-id="status_metrics" data-widget-tab="exec_snapshot">
            <div class="card-header d-flex justify-content-between align-items-center">
                <strong>Status & Core Metrics</strong>
                <span class="widget-drag-handle" title="Drag widget"><i class="fas fa-grip-vertical"></i></span>
            </div>
            <div class="card-body">
                <div class="row g-2">
                    <div class="col-md-3">
                        <div class="metric-card metric-blue">
                            <div class="metric-label">Total Risks</div>
                            <div class="metric-value" id="metricTotalRisks">{{ $dashboardSummary['total_risks'] ?? 0 }}</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="metric-card metric-red">
                            <div class="metric-label">Open</div>
                            <div class="metric-value" id="metricOpenRisks">{{ $dashboardSummary['open_count'] ?? 0 }}</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="metric-card metric-yellow">
                            <div class="metric-label">Monitoring</div>
                            <div class="metric-value" id="metricMonitoringRisks">{{ $dashboardSummary['monitoring_count'] ?? 0 }}</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="metric-card metric-green">
                            <div class="metric-label">Closed</div>
                            <div class="metric-value" id="metricClosedRisks">{{ $dashboardSummary['closed_count'] ?? 0 }}</div>
                        </div>
                    </div>
                </div>

                <div class="row g-2 mt-2">
                    <div class="col-md-3">
                        <div class="metric-inline">
                            <span class="metric-inline-label">Avg Inherent</span>
                            <span class="metric-inline-value" id="metricAvgInherent">{{ number_format((float) ($dashboardSummary['average_scores']['inherent'] ?? 0), 2) }}</span>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="metric-inline">
                            <span class="metric-inline-label">Avg Residual</span>
                            <span class="metric-inline-value" id="metricAvgResidual">{{ number_format((float) ($dashboardSummary['average_scores']['residual'] ?? 0), 2) }}</span>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="metric-inline">
                            <span class="metric-inline-label">Avg Overall</span>
                            <span class="metric-inline-value" id="metricAvgOverall">{{ number_format((float) ($dashboardSummary['average_scores']['overall'] ?? 0), 2) }}</span>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="metric-inline">
                            <span class="metric-inline-label">Variance (Inh-Res)</span>
                            <span class="metric-inline-value" id="metricGlobalVariance">{{ number_format((float) ($dashboardSummary['variance']['global'] ?? 0), 2) }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card dashboard-widget" draggable="true" data-widget-id="portfolio_overview" data-widget-tab="exec_snapshot">
            <div class="card-header d-flex justify-content-between align-items-center">
                <strong>Portfolio Command Center</strong>
                <span class="widget-drag-handle" title="Drag widget"><i class="fas fa-grip-vertical"></i></span>
            </div>
            <div class="card-body">
                <div class="small text-muted mb-3">
                    Multi-angle snapshot of current risk portfolio with ranking view for management review.
                </div>

                <div class="portfolio-donut-grid mb-3">
                    <div class="portfolio-donut-card">
                        <div class="portfolio-donut-title">By Priority</div>
                        <div class="portfolio-donut-shell">
                            <div id="portfolioPriorityDonut" class="portfolio-mini-donut"></div>
                            <div id="portfolioPriorityTotal" class="portfolio-mini-center">{{ collect($portfolioPriorityRows)->sum('count') }}</div>
                        </div>
                        <div id="portfolioPriorityLegend" class="portfolio-mini-legend"></div>
                    </div>
                    <div class="portfolio-donut-card">
                        <div class="portfolio-donut-title">By Category</div>
                        <div class="portfolio-donut-shell">
                            <div id="portfolioCategoryDonut" class="portfolio-mini-donut"></div>
                            <div id="portfolioCategoryTotal" class="portfolio-mini-center">{{ collect($portfolioCategoryRows)->sum('count') }}</div>
                        </div>
                        <div id="portfolioCategoryLegend" class="portfolio-mini-legend"></div>
                    </div>
                    <div class="portfolio-donut-card">
                        <div class="portfolio-donut-title">By Division</div>
                        <div class="portfolio-donut-shell">
                            <div id="portfolioDivisionDonut" class="portfolio-mini-donut"></div>
                            <div id="portfolioDivisionTotal" class="portfolio-mini-center">{{ collect($portfolioDivisionRows)->sum('count') }}</div>
                        </div>
                        <div id="portfolioDivisionLegend" class="portfolio-mini-legend"></div>
                    </div>
                    <div class="portfolio-donut-card">
                        <div class="portfolio-donut-title">Treatment Timeline</div>
                        <div class="portfolio-donut-shell">
                            <div id="portfolioTreatmentDonut" class="portfolio-mini-donut"></div>
                            <div id="portfolioTreatmentTotal" class="portfolio-mini-center">{{ collect($portfolioTreatmentTimelineRows)->sum('count') }}</div>
                        </div>
                        <div id="portfolioTreatmentLegend" class="portfolio-mini-legend"></div>
                    </div>
                    <div class="portfolio-donut-card">
                        <div class="portfolio-donut-title">Risk Status</div>
                        <div class="portfolio-donut-shell">
                            <div id="portfolioStatusDonut" class="portfolio-mini-donut"></div>
                            <div id="portfolioStatusTotal" class="portfolio-mini-center">{{ collect($portfolioStatusRows)->sum('count') }}</div>
                        </div>
                        <div id="portfolioStatusLegend" class="portfolio-mini-legend"></div>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-sm table-bordered table-hover mb-0 portfolio-risk-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Risk</th>
                                <th>Description</th>
                                <th class="text-center">Priority</th>
                                <th class="text-end">Risk Value</th>
                                <th>Division</th>
                                <th>Owner</th>
                                <th>Category</th>
                                <th class="text-end">Likelihood</th>
                                <th class="text-end">Impact</th>
                                <th>Control Focus</th>
                            </tr>
                        </thead>
                        <tbody id="portfolioTopRiskBody">
                            @forelse($portfolioTopRiskRows as $row)
                                @php
                                    $priorityBucket = strtolower((string) ($row['priority_bucket'] ?? 'low'));
                                    $priorityBucket = in_array($priorityBucket, ['low', 'moderate', 'high', 'extreme'], true) ? $priorityBucket : 'low';
                                @endphp
                                <tr>
                                    <td>{{ (string) ($row['risk_id'] ?? '-') }}</td>
                                    <td>{{ (string) ($row['risk_title'] ?? '-') }}</td>
                                    <td>{{ (string) ($row['description'] ?? '-') }}</td>
                                    <td class="text-center">
                                        <span class="portfolio-priority-pill priority-{{ $priorityBucket }}">
                                            {{ (string) ($row['priority_label'] ?? '-') }}
                                        </span>
                                    </td>
                                    <td class="text-end">{{ number_format((float) ($row['risk_value'] ?? 0), 2) }}</td>
                                    <td>{{ (string) ($row['division'] ?? '-') }}</td>
                                    <td>{{ (string) ($row['owner'] ?? '-') }}</td>
                                    <td>{{ (string) ($row['category'] ?? '-') }}</td>
                                    <td class="text-end">
                                        @if(is_numeric($row['likelihood'] ?? null))
                                            {{ (int) ($row['likelihood'] ?? 0) }}
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        @if(is_numeric($row['impact'] ?? null))
                                            {{ (int) ($row['impact'] ?? 0) }}
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td>{{ (string) ($row['control_focus'] ?? '-') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="11" class="text-center text-muted">No top risk data available.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="card dashboard-widget" draggable="true" data-widget-id="benchmark_scoring" data-widget-tab="exec_snapshot">
            <div class="card-header d-flex justify-content-between align-items-center">
                <strong>Benchmark Scoring</strong>
                <span class="widget-drag-handle" title="Drag widget"><i class="fas fa-grip-vertical"></i></span>
            </div>
            <div class="card-body">
                <div class="row g-2 mb-2">
                    <div class="col-md-4">
                        <div class="metric-inline">
                            <span class="metric-inline-label">Internal Avg</span>
                            <span class="metric-inline-value" id="benchmarkInternalAvg">{{ number_format((float) ($benchmarkScoring['internal_average_score'] ?? 0), 2) }}</span>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="metric-inline">
                            <span class="metric-inline-label">Industry Baseline</span>
                            <span class="metric-inline-value" id="benchmarkIndustry">{{ number_format((float) ($benchmarkScoring['industry_baseline_score'] ?? 0), 2) }}</span>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="metric-inline">
                            <span class="metric-inline-label">Target</span>
                            <span class="metric-inline-value" id="benchmarkTarget">{{ number_format((float) ($benchmarkScoring['target_score'] ?? 0), 2) }}</span>
                        </div>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-sm table-bordered mb-0 benchmark-metric-table">
                        <thead>
                            <tr>
                                <th>Metric</th>
                                <th>Value</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>Gap vs Industry</td>
                                <td class="benchmark-value-number" id="benchmarkGapIndustry">{{ number_format((float) ($benchmarkScoring['gap_vs_industry'] ?? 0), 2) }}</td>
                            </tr>
                            <tr>
                                <td>Gap vs Target</td>
                                <td class="benchmark-value-number" id="benchmarkGapTarget">{{ number_format((float) ($benchmarkScoring['gap_vs_target'] ?? 0), 2) }}</td>
                            </tr>
                            <tr>
                                <td>Position</td>
                                <td class="benchmark-value-text fw-bold" id="benchmarkPosition">{{ (string) ($benchmarkScoring['position_label'] ?? '-') }}</td>
                            </tr>
                            <tr>
                                <td>Industry Source</td>
                                <td class="benchmark-value-text" id="benchmarkSourceInline">
                                    {{ (string) ($benchmarkScoring['source_name'] ?? '-') }}
                                    ({{ (string) ($benchmarkScoring['source_year'] ?? '-') }})
                                </td>
                            </tr>
                            <tr>
                                <td>Source Reference</td>
                                <td class="benchmark-value-text" id="benchmarkSourceReferenceInline">
                                    @if($benchmarkSourceReferenceUrl)
                                        <a
                                            href="{{ $benchmarkSourceReferenceUrl }}"
                                            target="_blank"
                                            rel="noopener noreferrer"
                                            class="fw-semibold text-decoration-none"
                                        >
                                            Lihat Referensi
                                        </a>
                                        @if($benchmarkSourceReferenceNote !== '')
                                            <div class="small text-muted mt-1">{{ $benchmarkSourceReferenceNote }}</div>
                                        @endif
                                    @else
                                        {{ $benchmarkSourceReferenceRaw !== '' ? $benchmarkSourceReferenceRaw : '-' }}
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td>Method</td>
                                <td class="benchmark-value-text" id="benchmarkMethodologyInline">{{ (string) ($benchmarkScoring['methodology'] ?? '-') }}</td>
                            </tr>
                            <tr>
                                <td>Why This Position</td>
                                <td class="benchmark-value-text" id="benchmarkReasonInline">{{ (string) ($benchmarkScoring['position_reason'] ?? '-') }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="mt-2 border rounded p-2 bg-light">
                    <div class="small">
                        <strong>Source:</strong>
                        <span id="benchmarkSourceName">{{ (string) ($benchmarkScoring['source_name'] ?? '-') }}</span>
                        (<span id="benchmarkSourceYear">{{ (string) ($benchmarkScoring['source_year'] ?? '-') }}</span>)
                    </div>
                    <div class="small text-muted" id="benchmarkSourceReference">
                        @if($benchmarkSourceReferenceUrl)
                            <a
                                href="{{ $benchmarkSourceReferenceUrl }}"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="fw-semibold text-decoration-none"
                            >
                                Lihat Referensi
                            </a>
                            @if($benchmarkSourceReferenceNote !== '')
                                <div class="small text-muted mt-1">{{ $benchmarkSourceReferenceNote }}</div>
                            @endif
                        @else
                            {{ $benchmarkSourceReferenceRaw !== '' ? $benchmarkSourceReferenceRaw : '-' }}
                        @endif
                    </div>
                    <div class="small mt-2">
                        <strong>Method:</strong>
                        <span id="benchmarkMethodology">{{ (string) ($benchmarkScoring['methodology'] ?? '-') }}</span>
                    </div>
                    <div class="small mt-1">
                        <strong>Position Reason:</strong>
                        <span id="benchmarkReason">{{ (string) ($benchmarkScoring['position_reason'] ?? '-') }}</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="card dashboard-widget" draggable="true" data-widget-id="mitigation_sla" data-widget-tab="ops_control">
            <div class="card-header d-flex justify-content-between align-items-center">
                <strong>Mitigation, SLA & KPI Dashboard</strong>
                <span class="widget-drag-handle" title="Drag widget"><i class="fas fa-grip-vertical"></i></span>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-lg-5">
                        <div class="mb-3">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span>Mitigation Effectiveness</span>
                                <span id="mitigationEffectivenessText">{{ number_format($mitigationEffectivenessPercent, 2) }}%</span>
                            </div>
                            <div class="progress">
                                <div
                                    id="mitigationEffectivenessBar"
                                    class="progress-bar bg-primary"
                                    role="progressbar"
                                    style="width: {{ number_format($mitigationBarWidth, 2, '.', '') }}%"
                                ></div>
                            </div>
                            <small class="text-muted">
                                Treatments: <span id="mitigationTreatmentCount">{{ $mitigationTreatmentCount }}</span> |
                                Risk Coverage: <span id="mitigationCoveragePercent">{{ number_format($mitigationCoveragePercent, 2) }}%</span>
                            </small>
                        </div>

                        <div>
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span>SLA Compliance (Target vs Actual)</span>
                                <span>
                                    <span id="slaActualPercent">{{ number_format($slaActualPercent, 2) }}%</span> /
                                    <span id="slaTargetPercent">{{ number_format($slaTargetPercent, 2) }}%</span>
                                </span>
                            </div>
                            <div class="progress">
                                <div
                                    id="slaComplianceBar"
                                    class="progress-bar {{ $slaActualPercent >= $slaTargetPercent ? 'bg-success' : 'bg-danger' }}"
                                    role="progressbar"
                                    style="width: {{ number_format($slaBarWidth, 2, '.', '') }}%"
                                ></div>
                            </div>
                            <small class="text-muted">
                                Due: <span id="slaDueCount">{{ $slaDueCount }}</span> |
                                Compliant: <span id="slaCompliantCount">{{ $slaCompliantCount }}</span> |
                                Overdue: <span id="slaOverdueCount">{{ $slaOverdueCount }}</span>
                            </small>
                        </div>
                    </div>

                    <div class="col-lg-7">
                        <div class="small text-muted fw-semibold mb-2">KPI Snapshot</div>
                        <div class="row g-2">
                            <div class="col-md-6">
                                <div class="metric-inline">
                                    <span class="metric-inline-label">Avg Response (Days)</span>
                                    <span class="metric-inline-value" id="kpiAvgResponseDays">{{ number_format($kpiAvgResponseDays, 2) }}</span>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="metric-inline">
                                    <span class="metric-inline-label">Closed Risk Rate</span>
                                    <span class="metric-inline-value" id="kpiClosedRiskRate">{{ number_format($kpiClosedRiskRate, 2) }}%</span>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="metric-inline">
                                    <span class="metric-inline-label">Response Within SLA</span>
                                    <span class="metric-inline-value" id="kpiResponseWithinSlaPercent">{{ number_format($kpiResponseWithinSlaPercent, 2) }}%</span>
                                </div>
                                <div class="small text-muted mt-1">
                                    SLA: <span id="kpiResponseSlaDays">{{ $kpiResponseSlaDays }}</span> days,
                                    N=<span id="kpiResponseSampleCount">{{ $kpiResponseSampleCount }}</span>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="metric-inline">
                                    <span class="metric-inline-label">Overdue Treatment Rate</span>
                                    <span class="metric-inline-value" id="kpiOverdueTreatmentRate">{{ number_format($kpiOverdueRate, 2) }}%</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card dashboard-widget" draggable="true" data-widget-id="department_performance" data-widget-tab="ops_control">
            <div class="card-header d-flex justify-content-between align-items-center">
                <strong>Organizational Risk Overview</strong>
                <span class="widget-drag-handle" title="Drag widget"><i class="fas fa-grip-vertical"></i></span>
            </div>
            <div class="card-body">
                <div class="org-risk-summary-box mb-3">
                    <div class="org-risk-summary-value"><span id="departmentTotalRiskCount">{{ $departmentTotalRisks }}</span></div>
                    <div class="org-risk-summary-label">Corporate Risks</div>
                    <div class="org-risk-summary-meta">
                        Across <span id="departmentDivisionCount">{{ $departmentDivisionCount }}</span> divisions |
                        Top division: <span id="departmentTopDivision">{{ $departmentTopDivision }}</span> |
                        Avg score: <span id="departmentAvgScore">{{ number_format($departmentAverageScore, 2) }}</span>
                    </div>
                </div>

                <div id="departmentCardsBoard" class="division-risk-board">
                    @forelse($departmentRows as $row)
                        @php
                            $priorityBreakdown = is_array($row['priority_breakdown'] ?? null) ? $row['priority_breakdown'] : [];
                            $inherentPriority = is_array($priorityBreakdown['inherent'] ?? null) ? $priorityBreakdown['inherent'] : [];
                            $residualPriority = is_array($priorityBreakdown['residual'] ?? null) ? $priorityBreakdown['residual'] : [];
                            $treatmentStats = is_array($row['treatment_stats'] ?? null) ? $row['treatment_stats'] : [];
                        @endphp
                        <div class="division-risk-card">
                            <div class="division-risk-card-head">
                                <div>
                                    <div class="division-risk-count">{{ (int) ($row['risk_count'] ?? 0) }} <span>Risks</span></div>
                                    <div class="division-risk-name">{{ (string) ($row['division'] ?? '-') }}</div>
                                </div>
                                <div class="division-risk-rank">#{{ (int) ($row['rank'] ?? 0) }}</div>
                            </div>

                            <table class="table table-sm mb-2 division-priority-table">
                                <thead>
                                    <tr>
                                        <th>Priority</th>
                                        <th class="text-end">Inherent</th>
                                        <th class="text-end">Residual</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td><span class="risk-priority-dot low"></span> Low</td>
                                        <td class="text-end">{{ (int) ($inherentPriority['low'] ?? 0) }}</td>
                                        <td class="text-end">{{ (int) ($residualPriority['low'] ?? 0) }}</td>
                                    </tr>
                                    <tr>
                                        <td><span class="risk-priority-dot moderate"></span> Moderate</td>
                                        <td class="text-end">{{ (int) ($inherentPriority['moderate'] ?? 0) }}</td>
                                        <td class="text-end">{{ (int) ($residualPriority['moderate'] ?? 0) }}</td>
                                    </tr>
                                    <tr>
                                        <td><span class="risk-priority-dot high"></span> High</td>
                                        <td class="text-end">{{ (int) ($inherentPriority['high'] ?? 0) }}</td>
                                        <td class="text-end">{{ (int) ($residualPriority['high'] ?? 0) }}</td>
                                    </tr>
                                    <tr>
                                        <td><span class="risk-priority-dot extreme"></span> Extreme</td>
                                        <td class="text-end">{{ (int) ($inherentPriority['extreme'] ?? 0) }}</td>
                                        <td class="text-end">{{ (int) ($residualPriority['extreme'] ?? 0) }}</td>
                                    </tr>
                                </tbody>
                            </table>

                            <div class="division-treatment-stats">
                                <div class="division-treatment-item">
                                    <span class="label">Activities</span>
                                    <span class="value">{{ (int) ($treatmentStats['activity_count'] ?? 0) }}</span>
                                </div>
                                <div class="division-treatment-item">
                                    <span class="label">Effectiveness</span>
                                    <span class="value">{{ number_format((float) ($treatmentStats['effectiveness_percent'] ?? 0), 2) }}%</span>
                                </div>
                                <div class="division-treatment-item">
                                    <span class="label">Overdue</span>
                                    <span class="value">{{ (int) ($treatmentStats['overdue_count'] ?? 0) }} / {{ (int) ($treatmentStats['due_count'] ?? 0) }}</span>
                                </div>
                                <div class="division-treatment-item">
                                    <span class="label">Closed Rate</span>
                                    <span class="value">{{ number_format((float) ($row['closed_rate'] ?? 0), 2) }}%</span>
                                </div>
                                <div class="division-treatment-item">
                                    <span class="label">Resp (Days)</span>
                                    <span class="value">{{ number_format((float) ($row['avg_response_time_days'] ?? 0), 2) }}</span>
                                </div>
                                <div class="division-treatment-item score">
                                    <span class="label">Score</span>
                                    <span class="value">{{ number_format((float) ($row['performance_score'] ?? 0), 2) }}</span>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="text-center text-muted py-3">No department performance data</div>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="card dashboard-widget" draggable="true" data-widget-id="root_cause_analysis" data-widget-tab="risk_intelligence">
            <div class="card-header d-flex justify-content-between align-items-center">
                <strong>Root Cause Analysis</strong>
                <span class="widget-drag-handle" title="Drag widget"><i class="fas fa-grip-vertical"></i></span>
            </div>
            <div class="card-body">
                <div class="row g-2 mb-2">
                    <div class="col-md-7">
                        <h6 class="mb-2">Top Cause Categories</h6>
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered mb-0">
                                <thead>
                                    <tr>
                                        <th>Category</th>
                                        <th class="text-end">Count</th>
                                        <th class="text-end">Share</th>
                                        <th class="text-end">Avg Residual</th>
                                    </tr>
                                </thead>
                                <tbody id="rootCauseCategoryBody">
                                    @forelse($rootCauseCategoryRows as $row)
                                        <tr>
                                            <td>{{ (string) ($row['category'] ?? '-') }}</td>
                                            <td class="text-end">{{ (int) ($row['count'] ?? 0) }}</td>
                                            <td class="text-end">{{ number_format((float) ($row['share_percent'] ?? 0), 2) }}%</td>
                                            <td class="text-end">{{ number_format((float) ($row['avg_residual_score'] ?? 0), 2) }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="text-center text-muted">No cause category data</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="col-md-5">
                        <h6 class="mb-2">Recurring Keywords</h6>
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered mb-0">
                                <thead>
                                    <tr>
                                        <th>Keyword</th>
                                        <th class="text-end">Count</th>
                                    </tr>
                                </thead>
                                <tbody id="rootCauseKeywordBody">
                                    @forelse($rootCauseKeywordRows as $row)
                                        <tr>
                                            <td>{{ (string) ($row['keyword'] ?? '-') }}</td>
                                            <td class="text-end">{{ (int) ($row['count'] ?? 0) }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="2" class="text-center text-muted">No recurring keyword</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <h6 class="mb-2">Pattern Insights</h6>
                <div class="table-responsive">
                    <table class="table table-sm table-bordered mb-0">
                        <thead>
                            <tr>
                                <th>Pattern</th>
                                <th>Evidence</th>
                                <th>Recommendation</th>
                            </tr>
                        </thead>
                        <tbody id="rootCausePatternBody">
                            @forelse($rootCausePatternRows as $row)
                                <tr>
                                    <td>{{ (string) ($row['pattern'] ?? '-') }}</td>
                                    <td>{{ (string) ($row['evidence'] ?? '-') }}</td>
                                    <td>{{ (string) ($row['recommendation'] ?? '-') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="text-center text-muted">No pattern insights available</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="card dashboard-widget" draggable="true" data-widget-id="variance_analysis" data-widget-tab="risk_intelligence">
            <div class="card-header d-flex justify-content-between align-items-center">
                <strong>Variance Analysis (Inherent vs Residual)</strong>
                <span class="widget-drag-handle" title="Drag widget"><i class="fas fa-grip-vertical"></i></span>
            </div>
            <div class="card-body table-responsive">
                <table class="table table-sm table-bordered mb-0">
                    <thead>
                        <tr>
                            <th>Category</th>
                            <th class="text-end">Risk Count</th>
                            <th class="text-end">Avg Inherent</th>
                            <th class="text-end">Avg Residual</th>
                            <th class="text-end">Variance</th>
                        </tr>
                    </thead>
                    <tbody id="varianceAnalysisBody">
                        @forelse($varianceRows as $row)
                            <tr>
                                <td>{{ $row['category'] ?? '-' }}</td>
                                <td class="text-end">{{ (int) ($row['risk_count'] ?? 0) }}</td>
                                <td class="text-end">{{ number_format((float) ($row['avg_inherent'] ?? 0), 2) }}</td>
                                <td class="text-end">{{ number_format((float) ($row['avg_residual'] ?? 0), 2) }}</td>
                                <td class="text-end">{{ number_format((float) ($row['variance'] ?? 0), 2) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted">No variance data</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
