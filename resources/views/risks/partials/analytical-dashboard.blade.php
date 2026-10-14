@php
    $summary = is_array($dashboardSummary ?? null) ? $dashboardSummary : [];
    $portfolio = is_array($summary['portfolio_overview'] ?? null) ? $summary['portfolio_overview'] : [];
    $varianceRows = collect($summary['variance']['rows'] ?? []);
    $rootCause = is_array($summary['root_cause_analysis'] ?? null) ? $summary['root_cause_analysis'] : [];
    $rootCategories = collect($rootCause['top_categories'] ?? []);
    $rootKeywords = collect($rootCause['top_keywords'] ?? [])->take(10);
    $rootPatterns = collect($rootCause['patterns'] ?? []);
    $benchmark = is_array($summary['benchmark_scoring'] ?? null) ? $summary['benchmark_scoring'] : [];

    // Heatmap Matrix Data (Passed from Controller or calculated fallback)
    $matrixResidual = $heatmapMatrix['residual'] ?? array_fill(1, 5, array_fill(1, 5, 0));
    $matrixInherent = $heatmapMatrix['inherent'] ?? array_fill(1, 5, array_fill(1, 5, 0));

    $probLabels = [
        5 => '5 - Almost Certain',
        4 => '4 - Likely',
        3 => '3 - Moderate',
        2 => '2 - Unlikely',
        1 => '1 - Rare',
    ];
    $impactLabels = [
        1 => '1 - Insignificant',
        2 => '2 - Minor',
        3 => '3 - Moderate',
        4 => '4 - Major',
        5 => '5 - Catastrophic',
    ];

    // Priority color helper
    $getCellStyle = function($prob, $imp) {
        $score = $prob * $imp;
        if ($score >= 16) {
            return ['bg' => '#fee2e2', 'border' => '#f87171', 'text' => '#991b1b', 'level' => 'Extreme'];
        } elseif ($score >= 11) {
            return ['bg' => '#ffedd5', 'border' => '#fb923c', 'text' => '#9a3412', 'level' => 'High'];
        } elseif ($score >= 6) {
            return ['bg' => '#fef9c3', 'border' => '#facc15', 'text' => '#854d0e', 'level' => 'Moderate'];
        }
        return ['bg' => '#dcfce7', 'border' => '#4ade80', 'text' => '#166534', 'level' => 'Low'];
    };
@endphp

<div class="analytical-dashboard-view">
    {{-- Analytical Header --}}
    <div class="card border-0 shadow-sm mb-3 bg-light">
        <div class="card-body p-3 d-flex flex-wrap justify-content-between align-items-center gap-2">
            <div>
                <h4 class="h5 mb-0 fw-bold"><i class="fas fa-microscope text-primary me-2"></i>Analytical Risk Intelligence Center</h4>
                <small class="text-muted">Multi-dimensional matrix, control variance efficacy, root cause intelligence, and regulatory benchmarking</small>
            </div>
            <div class="d-flex align-items-center gap-2">
                <a href="{{ route('risks.heatmap') }}" class="btn btn-outline-primary btn-sm">
                    <i class="fas fa-th me-1"></i> Dedicated Heatmap Page
                </a>
                <a href="{{ route('risks.recap') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="fas fa-calendar-alt me-1"></i> Annual Recap
                </a>
            </div>
        </div>
    </div>

    {{-- Interactive 5x5 Heatmap Matrix --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div>
                <h4 class="card-title h5 mb-0 fw-bold"><i class="fas fa-th-large text-primary me-2"></i>Enterprise 5&times;5 Heatmap Matrix</h4>
                <small class="text-muted">ISO 31000 Likelihood &times; Consequence matrix populated with active risk distribution</small>
            </div>
            <div class="btn-group btn-group-sm" role="group" aria-label="Matrix View Mode">
                <button type="button" class="btn btn-primary active" id="btnMatrixResidual" onclick="toggleMatrixView('residual')">
                    Residual (Current)
                </button>
                <button type="button" class="btn btn-outline-secondary" id="btnMatrixInherent" onclick="toggleMatrixView('inherent')">
                    Inherent (Gross)
                </button>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table data-no-sort class="table table-bordered text-center align-middle mb-0" style="min-width: 650px;">
                    <thead>
                        <tr>
                            <th rowspan="2" class="bg-light text-muted small" style="width: 140px; vertical-align: middle;">
                                LIKELIHOOD &darr; / IMPACT &rarr;
                            </th>
                            @foreach($impactLabels as $i => $iLabel)
                                <th class="bg-light fw-bold small text-uppercase py-2" style="width: 17%;">{{ $iLabel }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($probLabels as $p => $pLabel)
                            <tr>
                                <th class="bg-light fw-bold small text-start ps-2 py-3">{{ $pLabel }}</th>
                                @foreach([1, 2, 3, 4, 5] as $imp)
                                    @php
                                        $style = $getCellStyle($p, $imp);
                                        $rCount = (int) ($matrixResidual[$p][$imp] ?? 0);
                                        $iCount = (int) ($matrixInherent[$p][$imp] ?? 0);
                                    @endphp
                                    <td
                                        style="background-color: {{ $style['bg'] }}; border: 1px solid {{ $style['border'] }}; transition: transform 0.15s ease;"
                                        class="heatmap-matrix-cell py-3"
                                        title="{{ $style['level'] }} Priority (Score: {{ $p * $imp }})"
                                    >
                                        <div class="small fw-semibold opacity-75" style="color: {{ $style['text'] }}; font-size: 0.72rem;">
                                            Score {{ $p * $imp }}
                                        </div>
                                        <div class="matrix-count-residual fw-bold my-1" style="font-size: 1.35rem; color: {{ $style['text'] }};">
                                            @if($rCount > 0)
                                                <a href="{{ route('risks.index') }}" class="text-decoration-none" style="color: {{ $style['text'] }};">
                                                    {{ $rCount }}
                                                </a>
                                            @else
                                                <span class="opacity-25">0</span>
                                            @endif
                                        </div>
                                        <div class="matrix-count-inherent fw-bold my-1 d-none" style="font-size: 1.35rem; color: {{ $style['text'] }};">
                                            @if($iCount > 0)
                                                <a href="{{ route('risks.index') }}" class="text-decoration-none" style="color: {{ $style['text'] }};">
                                                    {{ $iCount }}
                                                </a>
                                            @else
                                                <span class="opacity-25">0</span>
                                            @endif
                                        </div>
                                        <span class="badge" style="background-color: rgba(0,0,0,0.08); color: {{ $style['text'] }}; font-size: 0.65rem;">
                                            {{ $style['level'] }}
                                        </span>
                                    </td>
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="d-flex justify-content-between align-items-center mt-3 pt-2 border-top small text-muted">
                <div class="d-flex align-items-center gap-3">
                    <span><span class="badge bg-success-subtle text-success me-1">Low (1-5)</span></span>
                    <span><span class="badge bg-warning-subtle text-dark me-1">Moderate (6-10)</span></span>
                    <span><span class="badge bg-orange text-white me-1">High (11-15)</span></span>
                    <span><span class="badge bg-danger text-white me-1">Extreme (16-25)</span></span>
                </div>
                <span>Click count to filter risk register</span>
            </div>
        </div>
    </div>

    {{-- Control Effectiveness Variance & Regulatory Benchmark --}}
    <div class="row g-3 mb-4">
        {{-- Inherent vs Residual Variance Breakdown --}}
        <div class="col-lg-7">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                    <div>
                        <h4 class="card-title h5 mb-0 fw-bold"><i class="fas fa-balance-scale text-primary me-2"></i>Control Effectiveness Variance</h4>
                        <small class="text-muted">Inherent exposure vs Net residual reduction by category</small>
                    </div>
                    <span class="badge bg-light text-dark border">
                        Global Reduction: <strong>{{ number_format((float) ($summary['variance']['global'] ?? 0), 2) }} pts</strong>
                    </span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-vcenter table-hover table-striped mb-0">
                            <thead>
                                <tr class="bg-light small text-muted text-uppercase">
                                    <th>Risk Category</th>
                                    <th>Risks</th>
                                    <th>Gross Inherent</th>
                                    <th>Net Residual</th>
                                    <th>Control Delta</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($varianceRows as $vRow)
                                    @php
                                        $var = (float) ($vRow['variance'] ?? 0);
                                    @endphp
                                    <tr>
                                        <td class="fw-bold text-dark">{{ $vRow['category'] }}</td>
                                        <td><span class="badge bg-light text-dark border">{{ $vRow['risk_count'] }}</span></td>
                                        <td><span class="text-muted fw-semibold">{{ number_format((float) $vRow['avg_inherent'], 2) }}</span></td>
                                        <td><span class="fw-bold text-dark">{{ number_format((float) $vRow['avg_residual'], 2) }}</span></td>
                                        <td>
                                            <span class="badge bg-success-subtle text-success">
                                                <i class="fas fa-arrow-down me-1"></i> -{{ number_format($var, 2) }} pts
                                            </span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center py-4 text-muted">No category variance data available.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        {{-- International Benchmark Positioning --}}
        <div class="col-lg-5">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white py-3 border-bottom">
                    <h4 class="card-title h5 mb-0 fw-bold"><i class="fas fa-globe text-primary me-2"></i>Regulatory &amp; Industry Benchmark</h4>
                    <small class="text-muted">Comparison against NHS CFA 5&times;5 Governance Standard</small>
                </div>
                <div class="card-body">
                    <div class="p-3 rounded bg-light border mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="small text-muted fw-bold text-uppercase">Position Status</span>
                            @php
                                $pos = $benchmark['position'] ?? 'better_than_industry';
                                $badgeClass = match($pos) {
                                    'ahead_of_target' => 'bg-success text-white',
                                    'better_than_industry' => 'bg-primary text-white',
                                    default => 'bg-danger text-white',
                                };
                            @endphp
                            <span class="badge {{ $badgeClass }}">{{ $benchmark['position_label'] ?? 'Better Than Industry' }}</span>
                        </div>
                        <p class="small text-muted mb-0">{{ $benchmark['position_reason'] ?? 'Internal average is within benchmark baseline.' }}</p>
                    </div>

                    <div class="row g-2 mb-3 text-center">
                        <div class="col-4">
                            <div class="p-2 border rounded bg-white">
                                <div class="small text-muted">Internal Avg</div>
                                <div class="h4 mb-0 fw-bold text-primary">{{ number_format((float) ($benchmark['internal_average_score'] ?? 0), 2) }}</div>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="p-2 border rounded bg-white">
                                <div class="small text-muted">Target Score</div>
                                <div class="h4 mb-0 fw-bold text-success">{{ number_format((float) ($benchmark['target_score'] ?? 9), 1) }}</div>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="p-2 border rounded bg-white">
                                <div class="small text-muted">Baseline</div>
                                <div class="h4 mb-0 fw-bold text-secondary">{{ number_format((float) ($benchmark['industry_baseline_score'] ?? 12), 1) }}</div>
                            </div>
                        </div>
                    </div>

                    <div class="small text-muted border-top pt-2">
                        <i class="fas fa-info-circle me-1"></i>
                        <strong>Guidance:</strong> Scores above 12.0 trigger mandatory board escalation under UK NHSCFA standards. Scores below 9.0 are considered within standard tolerance.
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Root Cause & Vulnerability Intelligence --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white py-3 border-bottom">
            <h4 class="card-title h5 mb-0 fw-bold"><i class="fas fa-search-plus text-primary me-2"></i>Root Cause &amp; Systemic Vulnerability Intelligence</h4>
            <small class="text-muted">NLP narrative keyword extraction and systemic causality patterns</small>
        </div>
        <div class="card-body">
            <div class="row g-3">
                {{-- Cause Categories --}}
                <div class="col-md-4">
                    <h6 class="fw-bold small text-uppercase text-muted mb-2">Dominant Cause Categories</h6>
                    <div class="list-group list-group-flush border rounded">
                        @forelse($rootCategories as $cat)
                            <div class="list-group-item d-flex justify-content-between align-items-center py-2">
                                <div>
                                    <div class="fw-bold small">{{ $cat['category'] }}</div>
                                    <small class="text-muted">{{ $cat['count'] }} risks ({{ $cat['share_percent'] }}%)</small>
                                </div>
                                <span class="badge bg-light text-dark border">
                                    Avg {{ number_format((float) ($cat['avg_residual_score'] ?? 0), 1) }}
                                </span>
                            </div>
                        @empty
                            <div class="list-group-item text-center small text-muted py-3">No cause categories classified yet.</div>
                        @endforelse
                    </div>
                </div>

                {{-- Frequent Narrative Keywords --}}
                <div class="col-md-4">
                    <h6 class="fw-bold small text-uppercase text-muted mb-2">High-Frequency Cause Keywords</h6>
                    <div class="d-flex flex-wrap gap-1 p-2 border rounded bg-light" style="min-height: 180px;">
                        @forelse($rootKeywords as $kw)
                            <span class="badge bg-white text-dark border p-2" title="{{ $kw['risk_count'] }} risks (Avg Residual: {{ $kw['average_residual_score'] }})">
                                {{ $kw['keyword'] }}
                                <span class="badge bg-secondary-subtle text-secondary ms-1">{{ $kw['frequency'] }}</span>
                            </span>
                        @empty
                            <span class="text-muted small p-2">No narrative cause keywords detected.</span>
                        @endforelse
                    </div>
                </div>

                {{-- Systemic Patterns & Recommendations --}}
                <div class="col-md-4">
                    <h6 class="fw-bold small text-uppercase text-muted mb-2">Systemic Recommendations</h6>
                    <div class="d-flex flex-column gap-2" style="max-height: 240px; overflow-y: auto;">
                        @forelse($rootPatterns as $pat)
                            <div class="p-2 border rounded bg-white">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <strong class="small text-truncate" style="max-width: 180px;">{{ $pat['category'] }}</strong>
                                    <span class="badge {{ $pat['severity_class'] ?? 'bg-secondary' }}">{{ $pat['severity'] ?? 'Notice' }}</span>
                                </div>
                                <p class="small text-muted mb-0" style="font-size: 0.76rem;">{{ $pat['recommendation'] }}</p>
                            </div>
                        @empty
                            <div class="p-3 border rounded text-center small text-muted">No vulnerability patterns identified.</div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function toggleMatrixView(mode) {
    const btnRes = document.getElementById('btnMatrixResidual');
    const btnInh = document.getElementById('btnMatrixInherent');
    const resCounts = document.querySelectorAll('.matrix-count-residual');
    const inhCounts = document.querySelectorAll('.matrix-count-inherent');

    if (mode === 'inherent') {
        btnRes?.classList.remove('btn-primary', 'active');
        btnRes?.classList.add('btn-outline-secondary');
        btnInh?.classList.remove('btn-outline-secondary');
        btnInh?.classList.add('btn-primary', 'active');

        resCounts.forEach(el => el.classList.add('d-none'));
        inhCounts.forEach(el => el.classList.remove('d-none'));
    } else {
        btnInh?.classList.remove('btn-primary', 'active');
        btnInh?.classList.add('btn-outline-secondary');
        btnRes?.classList.remove('btn-outline-secondary');
        btnRes?.classList.add('btn-primary', 'active');

        inhCounts.forEach(el => el.classList.add('d-none'));
        resCounts.forEach(el => el.classList.remove('d-none'));
    }
}
</script>
