@php
    $levelClass = function ($level) {
        switch ($level) {
            case 'Low':
                return 'recap-level-low';
            case 'Low to Moderate':
                return 'recap-level-low-moderate';
            case 'Moderate':
                return 'recap-level-moderate';
            case 'Moderate to High':
                return 'recap-level-moderate-high';
            case 'High':
                return 'recap-level-high';
            default:
                return '';
        }
    };

    $recapFormAction = $recapFormAction ?? route('risks.recap');
    $recapReportQuery = !empty($selectedYear) ? ['year' => (int) $selectedYear] : [];
@endphp

@once
    <style>
        .recap-filter-card {
            background: #f8fafc;
            border: 1px solid #dbe7f3;
            border-radius: 0.65rem;
        }

        .recap-filter-form {
            display: flex;
            flex-wrap: wrap;
            align-items: flex-end;
            gap: 0.75rem 1rem;
        }

        .recap-filter-field {
            display: flex;
            flex-direction: column;
            gap: 0.35rem;
            min-width: 220px;
        }

        .recap-filter-label {
            display: inline-flex;
            align-items: center;
            gap: 0.42rem;
            font-size: 0.82rem;
            font-weight: 700;
            color: #334155;
            letter-spacing: 0.02em;
            text-transform: uppercase;
            margin: 0;
        }

        .recap-filter-label i {
            color: #2563eb;
        }

        .recap-year-select {
            width: 220px;
            max-width: 100%;
        }

        .recap-filter-toolbar {
            display: inline-flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 0.45rem;
            margin-left: auto;
        }

        .recap-toolbar-btn {
            display: inline-flex;
            align-items: center;
            gap: 0.38rem;
            border-radius: 0.45rem;
            padding: 0.36rem 0.72rem;
            font-weight: 600;
        }

        .recap-toolbar-btn i {
            margin: 0;
            line-height: 1;
        }

        @media (max-width: 575.98px) {
            .recap-filter-field {
                min-width: 0;
                width: 100%;
            }

            .recap-year-select {
                width: 100%;
            }

            .recap-filter-toolbar {
                margin-left: 0;
            }
        }

        .recap-table th,
        .recap-table td {
            font-size: 12px;
            vertical-align: middle;
        }

        .recap-table .head-main {
            background: #f2d98d;
            text-align: center;
            font-weight: 700;
        }

        .recap-table .head-group {
            background: #d9dde2;
            text-align: center;
            font-weight: 700;
        }

        .recap-table .head-sub {
            background: #f7f7f7;
            text-align: center;
            font-weight: 700;
        }

        .recap-table .total-row {
            background: #cfe2f3;
            font-weight: 700;
        }

        .code-badge {
            display: inline-block;
            min-width: 38px;
            text-align: center;
            font-weight: 700;
            background: #f6c28b;
            border-radius: 3px;
            padding: 2px 6px;
        }

        .level-badge {
            display: inline-block;
            padding: 3px 6px;
            border-radius: 3px;
            font-weight: 700;
        }

        .recap-level-low { background: #28a745; color: #fff; }
        .recap-level-low-moderate { background: #7cc576; color: #111; }
        .recap-level-moderate { background: #ffeb3b; color: #111; }
        .recap-level-moderate-high { background: #ff9800; color: #111; }
        .recap-level-high { background: #f44336; color: #fff; }
    </style>
@endonce

<div class="card mb-3">
    <div class="card-body recap-filter-card">
        <form method="GET" action="{{ $recapFormAction }}" class="recap-filter-form">
            @if($recapFormAction === route('risks.index'))
                <input type="hidden" name="risk_tab" value="recap">
            @endif
            <div class="recap-filter-field">
                <label for="year" class="recap-filter-label">
                    <i class="fas fa-filter"></i> Filter Year
                </label>
                <select
                    name="year"
                    id="year"
                    class="form-control form-control-sm recap-year-select"
                    onchange="this.form.submit()"
                >
                    <option value="">All Year</option>
                    @foreach($availableYears as $year)
                        <option value="{{ $year }}" {{ (string) $selectedYear === (string) $year ? 'selected' : '' }}>
                            {{ $year }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="recap-filter-toolbar">
                @include('reports.export-buttons', [
                    'scope' => 'recap',
                    'scopeLabel' => 'Recap',
                    'reportQuery' => $recapReportQuery,
                    'overallQuery' => $recapReportQuery,
                ])
                <a href="{{ route('risks.monitoring') }}" class="btn btn-dark btn-sm recap-toolbar-btn">
                    <i class="fas fa-chart-line"></i> Monitoring
                </a>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header bg-dark text-white">
        <h3 class="card-title mb-0">Summary Recap (By Radar Code)</h3>
    </div>
    <div class="card-body table-responsive p-0">
        <table class="table table-bordered table-sm mb-0 recap-table">
            <thead>
                <tr>
                    <th class="head-main" rowspan="2">Risk Categorization</th>
                    <th class="head-main" rowspan="2">Risk</th>
                    <th class="head-main" rowspan="2">Risk (n)</th>
                    <th class="head-group" colspan="3">Inherent</th>
                    <th class="head-group" colspan="4">Residual</th>
                </tr>
                <tr>
                    <th class="head-sub">&Sigma;Prob</th>
                    <th class="head-sub">&Sigma;Imp</th>
                    <th class="head-sub">&Sigma;Risk</th>
                    <th class="head-sub">&Sigma;Prob</th>
                    <th class="head-sub">&Sigma;Imp</th>
                    <th class="head-sub">&Sigma;Risk</th>
                    <th class="head-sub">&Sigma;Risk Adj</th>
                </tr>
            </thead>
            <tbody>
                @forelse($summaryRows as $row)
                    <tr>
                        <td>{{ $row['category'] }}</td>
                        <td class="text-center"><span class="code-badge">{{ $row['code'] }}</span></td>
                        <td class="text-right">{{ number_format($row['n'], 0, ',', '.') }}</td>
                        <td class="text-right">{{ number_format($row['inh_prob_sum'], 0, ',', '.') }}</td>
                        <td class="text-right">{{ number_format($row['inh_imp_sum'], 0, ',', '.') }}</td>
                        <td class="text-right">{{ number_format($row['inh_risk_sum'], 0, ',', '.') }}</td>
                        <td class="text-right">{{ number_format($row['res_prob_sum'], 0, ',', '.') }}</td>
                        <td class="text-right">{{ number_format($row['res_imp_sum'], 0, ',', '.') }}</td>
                        <td class="text-right">{{ number_format($row['res_risk_sum'], 0, ',', '.') }}</td>
                        <td class="text-right">{{ number_format($row['res_adj_sum'], 2, '.', ',') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="10" class="text-center text-muted">No recap data available.</td>
                    </tr>
                @endforelse
            </tbody>
            <tfoot>
                <tr class="total-row">
                    <td colspan="2">Total Risiko</td>
                    <td class="text-right">{{ number_format($summaryTotals['n'], 0, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($summaryTotals['inh_prob_sum'], 0, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($summaryTotals['inh_imp_sum'], 0, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($summaryTotals['inh_risk_sum'], 0, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($summaryTotals['res_prob_sum'], 0, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($summaryTotals['res_imp_sum'], 0, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($summaryTotals['res_risk_sum'], 0, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($summaryTotals['res_adj_sum'], 2, '.', ',') }}</td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>

<div class="card mt-3">
    <div class="card-header bg-primary text-white">
        <h3 class="card-title mb-0">Detailed Recap</h3>
    </div>
    <div class="card-body table-responsive p-0">
        <table class="table table-bordered table-sm mb-0 recap-table">
            <thead>
                <tr>
                    <th class="head-main" rowspan="2">Risk Categorization</th>
                    <th class="head-main" rowspan="2">Risk</th>
                    <th class="head-main" rowspan="2">Risk (n)</th>
                    <th class="head-group" colspan="4">Inherent</th>
                    <th class="head-group" colspan="5">Residual</th>
                    <th class="head-group" colspan="2">Quantitative Result</th>
                </tr>
                <tr>
                    <th class="head-sub">Prob Inh</th>
                    <th class="head-sub">Imp Inh</th>
                    <th class="head-sub">Inh Risk</th>
                    <th class="head-sub">Evaluasi Level</th>
                    <th class="head-sub">Prob Res</th>
                    <th class="head-sub">Imp Res</th>
                    <th class="head-sub">Res Risk</th>
                    <th class="head-sub">Res Adj</th>
                    <th class="head-sub">R. Level</th>
                    <th class="head-sub">Potential Loss Inh (Rp)</th>
                    <th class="head-sub">Potential Loss Res (Rp)</th>
                </tr>
            </thead>
            <tbody>
                @forelse($detailRows as $row)
                    <tr>
                        <td>{{ $row['category'] }}</td>
                        <td class="text-center"><span class="code-badge">{{ $row['code'] }}</span></td>
                        <td class="text-right">{{ number_format($row['n'], 0, ',', '.') }}</td>
                        <td class="text-right">{{ number_format($row['inh_prob_avg'], 2, '.', '') }}</td>
                        <td class="text-right">{{ number_format($row['inh_imp_avg'], 2, '.', '') }}</td>
                        <td class="text-right">{{ number_format($row['inh_risk_avg'], 2, '.', '') }}</td>
                        <td class="text-center">
                            <span class="level-badge {{ $levelClass($row['inh_level']) }}">{{ $row['inh_level'] }}</span>
                        </td>
                        <td class="text-right">{{ number_format($row['res_prob_avg'], 2, '.', '') }}</td>
                        <td class="text-right">{{ number_format($row['res_imp_avg'], 2, '.', '') }}</td>
                        <td class="text-right">{{ number_format($row['res_risk_avg'], 2, '.', '') }}</td>
                        <td class="text-right">{{ number_format($row['res_adj_avg'], 2, '.', '') }}</td>
                        <td class="text-center">
                            <span class="level-badge {{ $levelClass($row['res_level']) }}">{{ $row['res_level'] }}</span>
                        </td>
                        <td class="text-right">{{ number_format($row['potential_loss_inherent'], 0, ',', '.') }}</td>
                        <td class="text-right">{{ number_format($row['potential_loss_residual'], 0, ',', '.') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="14" class="text-center text-muted">No detail recap data available.</td>
                    </tr>
                @endforelse
            </tbody>
            <tfoot>
                <tr class="total-row">
                    <td colspan="2">Total</td>
                    <td class="text-right">{{ number_format($detailTotals['n'], 0, ',', '.') }}</td>
                    <td colspan="9"></td>
                    <td class="text-right">{{ number_format($detailTotals['potential_loss_inherent'], 0, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($detailTotals['potential_loss_residual'], 0, ',', '.') }}</td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>
