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

    // Board Risk Appetite Calculations
    $appetiteBaseline = 10.0; // Default corporate appetite threshold on 1-25 scale
    $activeAppetite = collect($riskAppetites ?? [])->first();
    if ($activeAppetite && is_numeric($activeAppetite->appetite_limit_value) && $activeAppetite->appetite_limit_value > 0) {
        $appetiteBaseline = (float) $activeAppetite->appetite_limit_value;
    }

    $isAppetiteBreached = $residualAverage > $appetiteBaseline;
    $isAppetiteWarning = !$isAppetiteBreached && ($residualAverage >= ($appetiteBaseline * 0.85));

    $postureLabel = $highRiskCount > 0 ? 'Attention Required' : 'Under Control';
    $postureCopy = $highRiskCount > 0
        ? "{$highRiskCount} high/extreme risks require active executive oversight and risk owner escalation."
        : 'All risks within current scope remain within standard operational tolerance levels.';
    $posturePercent = $totalRisks > 0 ? min(100, round(($highRiskCount / $totalRisks) * 100)) : 0;
    $committeeDecisions = collect($strategicDecisions ?? [])->take(5);
@endphp

<div class="strategic-dashboard-view">
    {{-- Executive Hero Banner --}}
    <div class="management-hero mb-3">
        <div class="d-flex flex-wrap justify-content-between align-items-end gap-3">
            <div class="management-hero-meta">
                <div class="management-eyebrow">Strategic Risk Command Center · Board &amp; Executive Oversight</div>
                <h1 class="h2 mb-1">Executive Risk Governance Summary</h1>
                <p class="mb-0 text-white-50">Enterprise risk posture, corporate appetite alignment, and high-priority escalation triggers for senior leadership.</p>
                <div class="small mt-2 text-white-50 d-flex flex-wrap align-items-center gap-3">
                    <span><i class="far fa-clock me-1"></i> As of: <strong>{{ $summary['generated_at'] ?? now()->format('Y-m-d H:i') }}</strong></span>
                    <span><i class="fas fa-layer-group me-1"></i> Scope: <strong>{{ number_format($totalRisks) }}</strong> Total Enterprise Risks</span>
                    <span>
                        <i class="fas fa-shield-alt me-1"></i> Appetite Status:
                        @if($isAppetiteBreached)
                            <span class="badge bg-danger text-white"><i class="fas fa-exclamation-triangle me-1"></i> Appetite Breached</span>
                        @elseif($isAppetiteWarning)
                            <span class="badge bg-warning text-dark"><i class="fas fa-exclamation-circle me-1"></i> Approaching Limit</span>
                        @else
                            <span class="badge bg-success text-white"><i class="fas fa-check-circle me-1"></i> Within Appetite</span>
                        @endif
                    </span>
                </div>
            </div>
            <div class="management-hero-score">
                <div class="score">{{ number_format($residualAverage, 2) }}</div>
                <div class="label">Average Residual Risk Score</div>
                <div class="small text-white-50 mt-1">Inherent Baseline: {{ number_format($inherentAverage, 2) }} ({{ round(max(0, $inherentAverage - $residualAverage), 2) }} net reduction)</div>
            </div>
        </div>
    </div>

    {{-- Macro Governance KPIs --}}
    <div class="row g-3 mb-3">
        <div class="col-sm-6 col-xl-3">
            <div class="management-kpi accent-blue">
                <div class="kpi-label">Total Portfolio Risks</div>
                <div class="kpi-value">{{ number_format($totalRisks) }}</div>
                <div class="kpi-note d-flex justify-content-between">
                    <span>Active in scope</span>
                    <a href="{{ route('risks.index') }}" class="text-primary text-decoration-none fw-semibold">View Register &rarr;</a>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="management-kpi accent-red">
                <div class="kpi-label">High &amp; Extreme Exposure</div>
                <div class="kpi-value text-danger">{{ number_format($highRiskCount) }}</div>
                <div class="kpi-note d-flex justify-content-between">
                    <span>{{ $posturePercent }}% of enterprise portfolio</span>
                    <span class="badge bg-danger-subtle text-danger">Tier 1 Focus</span>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="management-kpi accent-amber">
                <div class="kpi-label">Overdue Mitigations</div>
                <div class="kpi-value {{ $overdueCount > 0 ? 'text-warning' : 'text-success' }}">{{ number_format($overdueCount) }}</div>
                <div class="kpi-note d-flex justify-content-between">
                    <span>SLA breach items</span>
                    <a href="{{ route('treatments.kanban') }}" class="text-warning text-decoration-none fw-semibold">Open Kanban &rarr;</a>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="management-kpi accent-teal">
                <div class="kpi-label">Treatment SLA Compliance</div>
                <div class="kpi-value {{ $slaActual >= 90 ? 'text-teal' : 'text-danger' }}">{{ number_format($slaActual, 1) }}%</div>
                <div class="kpi-note d-flex justify-content-between">
                    <span>Target: {{ number_format((float) ($summary['sla']['target_percent'] ?? 95), 0) }}% threshold</span>
                    <span class="badge {{ $slaActual >= 90 ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger' }}">
                        {{ $slaActual >= 90 ? 'Compliant' : 'Sub-Target' }}
                    </span>
                </div>
            </div>
        </div>
    </div>

    {{-- Board Risk Appetite & Exposure Comparison --}}
    <div class="card mb-3 border-0 shadow-sm">
        <div class="card-body p-3">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-2">
                <div>
                    <h5 class="mb-0 fw-bold"><i class="fas fa-bullseye text-primary me-2"></i>Corporate Risk Appetite vs Current Exposure</h5>
                    <small class="text-muted">Alignment of net residual exposure against approved board risk appetite limit</small>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <span class="small text-muted">Board Limit: <strong>{{ number_format($appetiteBaseline, 1) }}</strong></span>
                    <span class="small text-muted">· Current: <strong>{{ number_format($residualAverage, 2) }}</strong></span>
                    <a href="{{ route('risk-appetites.index') }}" class="btn btn-outline-secondary btn-sm">Manage Appetites</a>
                </div>
            </div>
            <div class="position-relative pt-2 pb-1">
                <div class="d-flex justify-content-between small text-muted mb-1">
                    <span>0 (Zero Risk)</span>
                    <span class="text-success fw-semibold">Conservative (1-5)</span>
                    <span class="text-warning fw-semibold">Moderate (6-10)</span>
                    <span class="text-danger fw-semibold">Escalated (11-25)</span>
                    <span>25 (Max Risk)</span>
                </div>
                <div class="progress" style="height: 14px; border-radius: 999px; background-color: #e2e8f0;">
                    @php
                        $residualPercent = min(100, ($residualAverage / 25) * 100);
                        $appetitePercent = min(100, ($appetiteBaseline / 25) * 100);
                        $barClass = $isAppetiteBreached ? 'bg-danger' : ($isAppetiteWarning ? 'bg-warning' : 'bg-success');
                    @endphp
                    <div class="progress-bar {{ $barClass }}" role="progressbar" style="width: {{ $residualPercent }}%" aria-valuenow="{{ $residualAverage }}" aria-valuemin="0" aria-valuemax="25"></div>
                </div>
                <div class="d-flex justify-content-between align-items-center mt-2 small">
                    <span class="text-muted">
                        @if($isAppetiteBreached)
                            <strong class="text-danger"><i class="fas fa-times-circle me-1"></i>Exceeds Appetite by {{ number_format($residualAverage - $appetiteBaseline, 2) }} points.</strong> Board-level review required.
                        @elseif($isAppetiteWarning)
                            <strong class="text-warning"><i class="fas fa-exclamation-circle me-1"></i>Within tolerance buffer ({{ number_format($appetiteBaseline - $residualAverage, 2) }} points below limit).</strong>
                        @else
                            <strong class="text-success"><i class="fas fa-check-circle me-1"></i>Comfortably within Board Appetite.</strong> Buffer of {{ number_format($appetiteBaseline - $residualAverage, 2) }} points available.
                        @endif
                    </span>
                    <span class="text-muted">Benchmark: <strong>NHSCFA Public Governance Matrix</strong></span>
                </div>
            </div>
        </div>
    </div>

    {{-- Posture & Priority Breakdown --}}
    <div class="row g-3 mb-3">
        <div class="col-lg-5">
            <div class="management-card">
                <div class="management-card-header">
                    <h2>Enterprise Risk Posture</h2>
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
                        Strategic posture is calculated from ISO 31000 residual risk distribution across registered enterprise assets.
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-7">
            <div class="management-card">
                <div class="management-card-header">
                    <h2>Priority Tier Distribution</h2>
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
                                <span class="name">
                                    <span class="risk-priority-dot {{ $bucket }}"></span>
                                    {{ $label }} Priority
                                </span>
                                <span class="number">{{ number_format($count) }} <small class="text-muted">({{ number_format($width, 1) }}%)</small></span>
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

    {{-- Committee Decisions & Top Systemic Risks --}}
    <div class="row g-3 mb-3">
        {{-- Management Attention List (Top Systemic Risks) --}}
        <div class="col-lg-7">
            <div class="management-card">
                <div class="management-card-header d-flex justify-content-between align-items-start gap-2">
                    <div>
                        <h2>Management Attention List</h2>
                        <p>Top escalated risks requiring senior executive oversight</p>
                    </div>
                    <a href="javascript:void(0)" onclick="window.switchDashboardView('operational')" class="btn btn-outline-primary btn-sm">
                        <i class="fas fa-list me-1"></i> Operational View
                    </a>
                </div>
                <div class="management-card-body">
                    <div class="attention-list">
                        @forelse($topRiskRows as $row)
                            @php $priority = strtolower((string) ($row['priority_bucket'] ?? 'high')); @endphp
                            <div class="attention-item {{ $priority === 'high' ? 'high' : '' }}">
                                <div class="attention-dot"></div>
                                <div class="w-100 min-w-0">
                                    <div class="d-flex justify-content-between align-items-center gap-2">
                                        <div class="title text-truncate">
                                            <a href="{{ route('risks.show', $row['id']) }}" class="text-dark text-decoration-none fw-bold">
                                                <span class="text-muted me-1">[{{ $row['risk_id'] }}]</span>
                                                {{ $row['risk_title'] }}
                                            </a>
                                        </div>
                                        <span class="portfolio-priority-pill priority-{{ $priority }} text-nowrap">
                                            {{ number_format((float) ($row['risk_value'] ?? 0), 1) }} · {{ $row['priority_label'] }}
                                        </span>
                                    </div>
                                    <div class="meta d-flex flex-wrap gap-2 mt-1">
                                        <span><i class="fas fa-building me-1"></i>{{ $row['division'] }}</span>
                                        <span>·</span>
                                        <span><i class="fas fa-tag me-1"></i>{{ $row['category'] }}</span>
                                        <span>·</span>
                                        <span class="text-truncate" style="max-width: 280px;"><i class="fas fa-shield-alt me-1"></i>Control: {{ $row['control_focus'] }}</span>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="attention-empty">
                                <i class="fas fa-check-circle text-success me-1"></i> No high or extreme exposure risks currently detected in active scope.
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        {{-- Board / Committee Decisions Follow-Through --}}
        <div class="col-lg-5">
            <div class="management-card">
                <div class="management-card-header d-flex justify-content-between align-items-start gap-2">
                    <div>
                        <h2>Risk Committee Decisions</h2>
                        <p>Follow-through on Board &amp; Committee directives</p>
                    </div>
                    <a href="{{ route('enterprise-risk-profile.index') }}" class="btn btn-outline-secondary btn-sm">
                        All Decisions
                    </a>
                </div>
                <div class="management-card-body">
                    @if($committeeDecisions->isNotEmpty())
                        <div class="d-flex flex-column gap-2">
                            @foreach($committeeDecisions as $decision)
                                <div class="p-2 border rounded bg-light">
                                    <div class="d-flex justify-content-between align-items-start gap-1">
                                        <strong class="small text-truncate" style="max-width: 220px;">{{ $decision->title }}</strong>
                                        @php
                                            $stClass = match($decision->status) {
                                                'closed' => 'bg-success text-white',
                                                'in_progress' => 'bg-primary text-white',
                                                default => 'bg-secondary text-white',
                                            };
                                        @endphp
                                        <span class="badge {{ $stClass }}">{{ strtoupper(str_replace('_', ' ', $decision->status)) }}</span>
                                    </div>
                                    <p class="small text-muted mb-1 text-truncate" style="max-width: 320px;">{{ $decision->decision }}</p>
                                    <div class="d-flex justify-content-between small text-muted">
                                        <span>Owner: <strong>{{ $decision->owner_name ?: '-' }}</strong></span>
                                        <span>Due: {{ $decision->due_at ? $decision->due_at->format('d M Y') : 'No date' }}</span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-4 text-muted">
                            <i class="fas fa-clipboard-list fa-2x mb-2 text-secondary opacity-50"></i>
                            <p class="small mb-1">No active committee decisions pending.</p>
                            <a href="{{ route('enterprise-risk-profile.index') }}" class="btn btn-sm btn-outline-primary">Record Committee Directive</a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Strategic Trend Trajectory --}}
    <div class="card border-0 shadow-sm mb-3">
        <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
            <div>
                <h3 class="card-title h5 mb-0 fw-bold"><i class="fas fa-chart-line text-primary me-2"></i>Multi-Period Risk Trajectory</h3>
                <small class="text-muted">Trend of enterprise exposure over time across quarterly reporting cycles</small>
            </div>
            <a href="{{ route('trends.analysis') }}" class="btn btn-outline-primary btn-sm">
                <i class="fas fa-chart-area me-1"></i> In-depth Trend Analytics
            </a>
        </div>
        <div class="card-body">
            @if($trendRows->isNotEmpty())
                <div class="trend-table">
                    @foreach($trendRows as $trend)
                        @php
                            $label = (string) ($trend['label'] ?? '-');
                            $overall = (float) ($trend['overall'] ?? 0);
                            $inherent = (float) ($trend['inherent'] ?? 0);
                            $residual = (float) ($trend['residual'] ?? 0);
                            $width = min(100, round(($overall / 25) * 100));
                        @endphp
                        <div class="trend-row">
                            <div class="trend-label fw-semibold">{{ $label }}</div>
                            <div class="trend-track">
                                <div class="trend-bar" style="width: {{ $width }}%"></div>
                            </div>
                            <div class="trend-value">{{ number_format($overall, 1) }}</div>
                        </div>
                    @endforeach
                </div>
                <div class="d-flex justify-content-between align-items-center mt-3 pt-2 border-top small text-muted">
                    <span>Scale: 1-25 (Lower indicates reduced exposure under effective controls)</span>
                    <span>Goal: Downward or stabilizing residual trajectory</span>
                </div>
            @else
                <div class="text-center py-4 text-muted">
                    <p class="mb-0">No multi-period trend data available yet.</p>
                </div>
            @endif
        </div>
    </div>
</div>
