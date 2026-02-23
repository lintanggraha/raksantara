@php
    $widgetOptions = [
        ['id' => 'status_metrics', 'label' => 'Status & Core Metrics'],
        ['id' => 'trend_scores', 'label' => 'Average Risk Score Trends'],
        ['id' => 'mitigation_sla', 'label' => 'Mitigation & SLA'],
        ['id' => 'summary_breakdown', 'label' => 'Summary by Category/Owner/Status'],
        ['id' => 'variance_analysis', 'label' => 'Variance Analysis'],
    ];
    $trendRows = is_array($dashboardSummary['trend'] ?? null) ? $dashboardSummary['trend'] : [];
    $categoryRows = is_array($dashboardSummary['summary_by_category'] ?? null) ? $dashboardSummary['summary_by_category'] : [];
    $ownerRows = is_array($dashboardSummary['summary_by_owner'] ?? null) ? $dashboardSummary['summary_by_owner'] : [];
    $statusRows = is_array($dashboardSummary['summary_by_status'] ?? null) ? $dashboardSummary['summary_by_status'] : [];
    $varianceRows = is_array($dashboardSummary['variance']['rows'] ?? null) ? $dashboardSummary['variance']['rows'] : [];

    $mitigationEffectivenessPercent = (float) ($dashboardSummary['mitigation']['effectiveness_percent'] ?? 0);
    $mitigationTreatmentCount = (int) ($dashboardSummary['mitigation']['treatment_count'] ?? 0);
    $mitigationCoveragePercent = (float) ($dashboardSummary['mitigation']['risk_coverage_percent'] ?? 0);

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
                <a href="{{ route('risks.index') }}" class="btn btn-primary btn-sm with-icon">
                    <i class="fas fa-table"></i> Risk Register
                </a>
                <a href="{{ route('risks.recap') }}" class="btn btn-info btn-sm with-icon">
                    <i class="fas fa-chart-bar"></i> Recap
                </a>
                <a href="{{ route('risks.heatmap') }}" class="btn btn-secondary btn-sm with-icon">
                    <i class="fas fa-th"></i> Heat Map
                </a>
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
        <div class="card dashboard-widget" draggable="true" data-widget-id="status_metrics">
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

        <div class="card dashboard-widget" draggable="true" data-widget-id="trend_scores">
            <div class="card-header d-flex justify-content-between align-items-center">
                <strong>Average Risk Score Trends</strong>
                <span class="widget-drag-handle" title="Drag widget"><i class="fas fa-grip-vertical"></i></span>
            </div>
            <div class="card-body">
                <div id="riskTrendBars" class="trend-bars"></div>
                <div class="table-responsive mt-2">
                    <table class="table table-sm table-bordered mb-0">
                        <thead>
                            <tr>
                                <th>Month</th>
                                <th class="text-end">Inherent</th>
                                <th class="text-end">Residual</th>
                                <th class="text-end">Overall</th>
                            </tr>
                        </thead>
                        <tbody id="riskTrendTableBody">
                            @forelse($trendRows as $trendRow)
                                <tr>
                                    <td>{{ $trendRow['label'] ?? '-' }}</td>
                                    <td class="text-end">{{ number_format((float) ($trendRow['inherent'] ?? 0), 2) }}</td>
                                    <td class="text-end">{{ number_format((float) ($trendRow['residual'] ?? 0), 2) }}</td>
                                    <td class="text-end">{{ number_format((float) ($trendRow['overall'] ?? 0), 2) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center text-muted">No trend data</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="card dashboard-widget" draggable="true" data-widget-id="mitigation_sla">
            <div class="card-header d-flex justify-content-between align-items-center">
                <strong>Mitigation Effectiveness & SLA</strong>
                <span class="widget-drag-handle" title="Drag widget"><i class="fas fa-grip-vertical"></i></span>
            </div>
            <div class="card-body">
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
        </div>

        <div class="card dashboard-widget" draggable="true" data-widget-id="summary_breakdown">
            <div class="card-header d-flex justify-content-between align-items-center">
                <strong>Real-time Summary by Category, Owner, Status</strong>
                <span class="widget-drag-handle" title="Drag widget"><i class="fas fa-grip-vertical"></i></span>
            </div>
            <div class="card-body">
                <div class="row g-2 mb-3">
                    <div class="col-md-5">
                        <h6 class="mb-2">Status Distribution</h6>
                        <div class="summary-chart-panel">
                            <div class="status-pie-shell">
                                <div id="statusPieChart" class="status-pie-chart"></div>
                                <div id="statusPieCenter" class="status-pie-center"></div>
                            </div>
                            <div id="statusPieLegend" class="status-pie-legend"></div>
                        </div>
                    </div>
                    <div class="col-md-7">
                        <h6 class="mb-2">Top Categories</h6>
                        <div class="summary-chart-panel">
                            <div id="categoryBarChart" class="category-bar-chart"></div>
                        </div>
                    </div>
                </div>
                <div class="row g-2">
                    <div class="col-md-4">
                        <h6 class="mb-2">Category</h6>
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered mb-0">
                                <thead>
                                    <tr>
                                        <th>Label</th>
                                        <th class="text-end">Count</th>
                                    </tr>
                                </thead>
                                <tbody id="summaryByCategoryBody">
                                    @forelse($categoryRows as $row)
                                        <tr>
                                            <td>{{ $row['label'] ?? '-' }}</td>
                                            <td class="text-end">{{ (int) ($row['count'] ?? 0) }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="2" class="text-center text-muted">No data</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <h6 class="mb-2">Owner</h6>
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered mb-0">
                                <thead>
                                    <tr>
                                        <th>Label</th>
                                        <th class="text-end">Count</th>
                                    </tr>
                                </thead>
                                <tbody id="summaryByOwnerBody">
                                    @forelse($ownerRows as $row)
                                        <tr>
                                            <td>{{ $row['label'] ?? '-' }}</td>
                                            <td class="text-end">{{ (int) ($row['count'] ?? 0) }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="2" class="text-center text-muted">No data</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <h6 class="mb-2">Status</h6>
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered mb-0">
                                <thead>
                                    <tr>
                                        <th>Label</th>
                                        <th class="text-end">Count</th>
                                    </tr>
                                </thead>
                                <tbody id="summaryByStatusBody">
                                    @forelse($statusRows as $row)
                                        <tr>
                                            <td>{{ $row['label'] ?? '-' }}</td>
                                            <td class="text-end">{{ (int) ($row['count'] ?? 0) }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="2" class="text-center text-muted">No data</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card dashboard-widget" draggable="true" data-widget-id="variance_analysis">
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
