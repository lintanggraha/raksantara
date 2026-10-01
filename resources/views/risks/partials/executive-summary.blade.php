@php
    $summary = is_array($dashboardSummary ?? null) ? $dashboardSummary : [];
    $portfolio = is_array($summary['portfolio_overview'] ?? null) ? $summary['portfolio_overview'] : [];
    $priorityRows = collect($portfolio['by_priority'] ?? []);
    $topRiskRows = collect($portfolio['top_risks'] ?? [])->filter(function ($row) {
        return in_array(strtolower((string) ($row['priority_bucket'] ?? '')), ['high', 'extreme'], true);
    })->take(5);
    $trendRows = collect($summary['trend'] ?? []);
    $maxTrend = max(1, (float) $trendRows->max('overall'));
    $totalRisks = (int) ($summary['total_risks'] ?? 0);
    $highRiskCount = (int) $priorityRows->whereIn('label', ['High', 'Extreme'])->sum('count');
    $overdueCount = (int) ($summary['sla']['overdue_count'] ?? 0);
    $slaActual = (float) ($summary['sla']['actual_percent'] ?? 0);
    $residualAverage = (float) ($summary['average_scores']['residual'] ?? 0);
    $inherentAverage = (float) ($summary['average_scores']['inherent'] ?? 0);
    $postureLabel = $highRiskCount > 0 ? 'Attention Required' : 'Under Control';
    $postureCopy = $highRiskCount > 0
        ? 'High and extreme priority risks require active management monitoring and risk owner escalation.'
        : 'All risks within current scope are within moderate or low tolerance levels.';
    $posturePercent = $totalRisks > 0 ? min(100, round(($highRiskCount / $totalRisks) * 100)) : 0;
@endphp

<div class="executive-summary-wrapper">
    {{-- Executive Hero Banner --}}
    <div class="management-hero mb-3">
        <div class="d-flex flex-wrap justify-content-between align-items-end gap-3">
            <div class="management-hero-meta">
                <div class="management-eyebrow">Strategic Executive View · Risk Governance</div>
                <h1 class="h2 mb-1">Executive Risk Summary</h1>
                <p class="mb-0 text-white-50">Enterprise risk posture, risk appetite alignment, and high-priority escalation triggers for senior leadership.</p>
                <div class="small mt-2 text-white-50">
                    Last updated: <strong>{{ $summary['generated_at'] ?? '-' }}</strong> · Portfolio Scope: <strong>{{ number_format($totalRisks) }}</strong> risks
                </div>
            </div>
            <div class="management-hero-score">
                <div class="score">{{ number_format($residualAverage, 2) }}</div>
                <div class="label">Average Residual Risk Score</div>
                <div class="small text-white-50 mt-1">Inherent Baseline: {{ number_format($inherentAverage, 2) }}</div>
            </div>
        </div>
    </div>

    {{-- Macro Governance KPIs --}}
    <div class="row g-3 mb-3">
        <div class="col-sm-6 col-xl-3">
            <div class="management-kpi accent-blue">
                <div class="kpi-label">Total Portfolio Risks</div>
                <div class="kpi-value">{{ number_format($totalRisks) }}</div>
                <div class="kpi-note">Risks active in your governance scope</div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="management-kpi accent-red">
                <div class="kpi-label">High &amp; Extreme Exposure</div>
                <div class="kpi-value">{{ number_format($highRiskCount) }}</div>
                <div class="kpi-note">High &amp; Extreme · Strategic attention</div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="management-kpi accent-amber">
                <div class="kpi-label">Overdue Mitigations</div>
                <div class="kpi-value">{{ number_format($overdueCount) }}</div>
                <div class="kpi-note">Action items past operational deadline</div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="management-kpi accent-teal">
                <div class="kpi-label">Mitigation SLA Compliance</div>
                <div class="kpi-value">{{ number_format($slaActual, 1) }}%</div>
                <div class="kpi-note">Target: {{ number_format((float) ($summary['sla']['target_percent'] ?? 95), 0) }}% target threshold</div>
            </div>
        </div>
    </div>

    {{-- Posture & Priority Breakdown --}}
    <div class="row g-3 mb-3">
        <div class="col-lg-5">
            <div class="management-card">
                <div class="management-card-header">
                    <h2>Current Risk Posture</h2>
                    <p>Executive governance signal based on residual exposure</p>
                </div>
                <div class="management-card-body">
                    <div class="posture-meter">
                        <div class="posture-ring" style="background: conic-gradient({{ $highRiskCount > 0 ? '#d39127' : '#168c83' }} 0 {{ $posturePercent }}%, #e8eef4 {{ $posturePercent }}% 100%);">
                            <div class="posture-ring-inner">
                                <div>
                                    <strong>{{ $posturePercent }}%</strong>
                                    <span>high risk</span>
                                </div>
                            </div>
                        </div>
                        <div class="posture-copy">
                            <strong>{{ $postureLabel }}</strong>
                            <span>{{ $postureCopy }}</span>
                        </div>
                    </div>
                    <div class="management-footer-note mt-3">
                        Strategic posture is derived from ISO 31000 residual risk distribution across registered enterprise assets.
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-7">
            <div class="management-card">
                <div class="management-card-header">
                    <h2>Priority Distribution</h2>
                    <p>Portfolio composition by risk severity tier</p>
                </div>
                <div class="management-card-body">
                    @foreach($priorityRows as $row)
                        @php
                            $label = (string) ($row['label'] ?? '-');
                            $bucket = strtolower($label);
                            $count = (int) ($row['count'] ?? 0);
                            $width = $totalRisks > 0 ? min(100, round(($count / $totalRisks) * 100, 1)) : 0;
                        @endphp
                        <div class="priority-row">
                            <div class="priority-head">
                                <span class="name">{{ $label }}</span>
                                <span class="number">{{ number_format($count) }} <small>({{ number_format($width, 1) }}%)</small></span>
                            </div>
                            <div class="priority-track">
                                <div class="priority-fill {{ $bucket }}" style="width: {{ $width }}%"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    {{-- Attention Items & Governance Trends --}}
    <div class="row g-3 mb-3">
        <div class="col-lg-7">
            <div class="management-card">
                <div class="management-card-header d-flex justify-content-between align-items-start gap-2">
                    <div>
                        <h2>Management Attention List</h2>
                        <p>Top escalated risks requiring senior oversight</p>
                    </div>
                    <a href="javascript:void(0)" onclick="window.switchDashboardView('monitoring')" class="btn btn-outline-primary btn-sm">
                        <i class="fas fa-list me-1"></i> View Operational Details
                    </a>
                </div>
                <div class="management-card-body">
                    <div class="attention-list">
                        @forelse($topRiskRows as $row)
                            @php $priority = strtolower((string) ($row['priority_bucket'] ?? 'high')); @endphp
                            <div class="attention-item {{ $priority === 'high' ? 'high' : '' }}">
                                <span class="attention-dot"></span>
                                <div>
                                    <div class="title">
                                        <a href="{{ route('risks.show', $row['id'] ?? 0) }}" class="text-reset">
                                            {{ $row['risk_id'] ?? '-' }} · {{ $row['risk_title'] ?? '-' }}
                                        </a>
                                    </div>
                                    <div class="meta">
                                        {{ $row['division'] ?? '-' }} ·
                                        <span class="badge badge-sm bg-{{ $priority === 'extreme' ? 'danger' : 'warning text-dark' }}">{{ $row['priority_label'] ?? '-' }}</span> ·
                                        Residual Score: <strong>{{ number_format((float) ($row['risk_value'] ?? 0), 2) }}</strong>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="attention-empty">
                                <i class="fas fa-check-circle text-success mb-2" style="font-size: 1.5rem;"></i>
                                <div>No high or extreme risks identified in current governance scope.</div>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-5">
            <div class="management-card">
                <div class="management-card-header">
                    <h2>Residual Exposure Trend</h2>
                    <p>{{ ucfirst((string) ($summary['trend_granularity'] ?? 'quarterly')) }} view · Recent governance periods</p>
                </div>
                <div class="management-card-body">
                    <div class="trend-table">
                        @forelse($trendRows as $row)
                            @php
                                $value = (float) ($row['overall'] ?? 0);
                                $width = min(100, round(($value / $maxTrend) * 100, 1));
                            @endphp
                            <div class="trend-row">
                                <span class="trend-label">{{ $row['label'] ?? '-' }}</span>
                                <div class="trend-track">
                                    <div class="trend-bar" style="width: {{ $width }}%"></div>
                                </div>
                                <span class="trend-value">{{ number_format($value, 1) }}</span>
                            </div>
                        @empty
                            <div class="attention-empty">Trend data is not yet available for the selected period.</div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
