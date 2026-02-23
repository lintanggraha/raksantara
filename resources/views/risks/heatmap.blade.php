@extends('layouts.tabler')

@section('title', 'Risk Heat Map')

@section('css')
<style>
.header-toolbar .btn.with-icon {
    display: inline-flex;
    align-items: center;
    gap: 0.38rem;
}

.header-toolbar .btn.with-icon i {
    margin: 0;
    line-height: 1;
}
</style>
@stop

@section('content_header')
<div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
    <div class="d-flex flex-wrap align-items-center gap-2 header-toolbar">
        @include('reports.export-buttons', [
            'scope' => 'heatmap',
            'scopeLabel' => 'Heat Map',
        ])
        <a href="{{ route('risks.monitoring') }}" class="btn btn-dark btn-sm with-icon">
            <i class="fas fa-chart-line"></i> Monitoring
        </a>
        <a href="{{ route('risks.recap') }}" class="btn btn-info btn-sm with-icon">
            <i class="fas fa-chart-bar"></i> Recap
        </a>
        <a href="{{ route('risks.index') }}" class="btn btn-secondary btn-sm with-icon">
            <i class="fas fa-table"></i> Back to Risk Register
        </a>
    </div>
    <h1 class="mb-0">Risk Heat Map</h1>
</div>
@stop

@section('content')
<style>
.heatmap-card .card-body {
    overflow-x: auto;
}

.heatmap-header {
    background: linear-gradient(135deg, #0f4c81 0%, #0b3a65 100%);
    border-bottom: 0;
}

.event-header {
    background: linear-gradient(135deg, #2f89cc 0%, #2269a5 100%);
    border-bottom: 0;
}

.heatmap-header .card-title,
.event-header .card-title {
    color: #f8fbff;
    font-weight: 700;
    letter-spacing: 0.01em;
    margin: 0;
}

.heatmap-wrapper {
    min-width: 760px;
}

.heatmap-title {
    font-weight: 700;
    margin-bottom: 10px;
}

.heatmap-grid {
    border-collapse: collapse;
}

.heatmap-grid td,
.heatmap-grid th {
    border: 1px solid #cfd4da;
}

.heatmap-grid .axis-y,
.heatmap-grid .axis-x {
    width: 32px;
    text-align: center;
    background: #f8f9fa;
    font-weight: 700;
    color: #495057;
    font-size: 12px;
}

.heat-cell {
    width: 132px;
    height: 102px;
    position: relative;
    padding: 4px;
}

.cell-level {
    font-size: 11px;
    font-weight: 700;
    line-height: 1.1;
}

.cell-score {
    position: absolute;
    bottom: 4px;
    left: 6px;
    font-weight: 800;
    font-size: 14px;
}

.marker-stack {
    margin-top: 8px;
    display: flex;
    flex-wrap: wrap;
    gap: 4px;
    max-width: 120px;
}

.risk-chip {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 32px;
    height: 22px;
    border-radius: 6px;
    font-size: 11px;
    font-weight: 700;
    border: 2px solid #111;
    padding: 0 6px;
}

.risk-chip.inherent {
    background: #111;
    color: #fff;
}

.risk-chip.residual {
    background: #fff;
    color: #111;
}

.level-low {
    background: #00b050;
    color: #fff;
}

.level-low-moderate {
    background: #8cc84b;
    color: #111;
}

.level-moderate {
    background: #fff200;
    color: #111;
}

.level-moderate-high {
    background: #ffc000;
    color: #111;
}

.level-high {
    background: #ff0000;
    color: #fff;
}

.axis-caption {
    margin-top: 8px;
    font-size: 12px;
    color: #495057;
}

.legend-title {
    font-weight: 700;
    margin-bottom: 6px;
}

.risk-legend {
    margin-top: 14px;
}

.legend-levels {
    display: grid;
    grid-template-columns: repeat(5, minmax(0, 1fr));
    gap: 4px;
}

.legend-level {
    font-size: 11px;
    text-align: center;
    padding: 6px 4px;
    border-radius: 4px;
    font-weight: 700;
}

.legend-markers {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
}

.legend-marker-item {
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 12px;
}

.event-table td,
.event-table th {
    font-size: 12px;
    vertical-align: top;
}

.event-table th.col-owner,
.event-table td.col-owner-cell {
    width: 320px;
    min-width: 320px;
}

.event-table td.col-owner-cell {
    line-height: 1.4;
    word-break: break-word;
}
</style>

<div class="card heatmap-card mb-3">
    <div class="card-header heatmap-header">
        <h3 class="card-title">Matrix 5x5 (Inherent vs Residual)</h3>
    </div>
    <div class="card-body">
        <div class="heatmap-wrapper">
            <div class="heatmap-title">Likelihood vs Impact</div>
            <table class="heatmap-grid">
                <tbody>
                @for($probability = 5; $probability >= 1; $probability--)
                    <tr>
                        <th class="axis-y">{{ $probability }}</th>
                        @for($impact = 1; $impact <= 5; $impact++)
                            @php
                                $cell = $matrix[$probability][$impact];
                                $cellMarkers = $markers[$probability][$impact];
                            @endphp
                            <td class="heat-cell {{ $cell['class'] }}">
                                <div class="cell-level">{{ $cell['level'] }}</div>
                                <div class="marker-stack">
                                    @foreach($cellMarkers as $marker)
                                        <span
                                            class="risk-chip {{ $marker['type'] }}"
                                            title="{{ strtoupper($marker['type']) }} | {{ $marker['risk_label'] }}">
                                            {{ $marker['code'] }}
                                        </span>
                                    @endforeach
                                </div>
                                <div class="cell-score">{{ $cell['score'] ?? '-' }}</div>
                            </td>
                        @endfor
                    </tr>
                @endfor
                    <tr>
                        <th class="axis-x"></th>
                        @for($impact = 1; $impact <= 5; $impact++)
                            <th class="axis-x">{{ $impact }}</th>
                        @endfor
                    </tr>
                </tbody>
            </table>
            <div class="axis-caption"><strong>Y:</strong> Likelihood (1-5), <strong>X:</strong> Impact (1-5)</div>

            <div class="risk-legend">
                <div class="legend-title">Legend Risk Level</div>
                <div class="legend-levels">
                    <div class="legend-level level-low">Low</div>
                    <div class="legend-level level-low-moderate">Low to Moderate</div>
                    <div class="legend-level level-moderate">Moderate</div>
                    <div class="legend-level level-moderate-high">Moderate to High</div>
                    <div class="legend-level level-high">High</div>
                </div>
            </div>

            <div class="risk-legend">
                <div class="legend-title">Legend Marker</div>
                <div class="legend-markers">
                    <div class="legend-marker-item">
                        <span class="risk-chip inherent">XX</span> Inherent Risk
                    </div>
                    <div class="legend-marker-item">
                        <span class="risk-chip residual">XX</span> Residual Risk
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card event-map-card">
    <div class="card-header event-header">
        <h3 class="card-title">Risk Event Mapping</h3>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-bordered table-hover table-sm mb-0 event-table">
                <thead class="bg-light">
                    <tr>
                        <th style="width:72px">Code</th>
                        <th style="width:110px">Category</th>
                        <th>Risk Event</th>
                        <th class="col-owner">Risk Owner</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($eventRows as $row)
                        <tr>
                            <td><span class="badge bg-dark">{{ $row['code'] }}</span></td>
                            <td>{{ $row['category'] }}</td>
                            <td>
                                <a href="{{ route('risks.show', $row['id']) }}">
                                    {{ $row['risk_event'] }}
                                </a>
                            </td>
                            <td class="col-owner-cell">{{ $row['risk_owner'] }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center text-muted">No risk data available.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@stop

