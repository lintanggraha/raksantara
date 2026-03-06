@extends('layouts.tabler')

@section('title', 'OJK Mitigation Monitoring')

@section('css')
<link rel="stylesheet" href="{{ asset('vendor/datatables/css/dataTables.bootstrap5.min.css') }}">
<style>
    .ojk-summary-shell {
        border: 1px solid #d8e2ef;
        border-radius: 0.85rem;
        background: linear-gradient(165deg, #f8fbff 0%, #f3f7fc 100%);
        padding: 0.85rem;
        margin-bottom: 0.9rem;
    }
    .ojk-summary-shell .small-box {
        border-radius: 0.75rem;
        box-shadow: 0 8px 18px rgba(15, 23, 42, 0.08);
        min-height: 108px;
    }
    .ojk-summary-meta {
        margin-bottom: 0.65rem;
    }
    .ojk-summary-title {
        margin: 0;
        font-size: 1.06rem;
        font-weight: 700;
        color: #0f172a;
    }
    .ojk-summary-subtitle {
        color: #64748b;
        font-size: 0.78rem;
    }
    .ojk-data-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 0.65rem;
        flex-wrap: wrap;
    }
    .ojk-import-inline {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
    }
    .ojk-import-note-inline {
        font-size: 0.75rem;
        color: #64748b;
    }
    .ojk-card {
        border: 1px solid #dbe4ef;
        border-radius: 12px;
        background: #ffffff;
        box-shadow: 0 6px 16px rgba(15, 23, 42, 0.05);
    }
    .ojk-card .card-header {
        border-bottom: 1px solid #e5edf6;
    }
    .status-pill {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 100px;
        padding: 0.2rem 0.5rem;
        border-radius: 999px;
        font-size: 0.72rem;
        font-weight: 700;
    }
    .status-not-started {
        background: #e2e8f0;
        color: #334155;
    }
    .status-on-progress {
        background: #dbeafe;
        color: #1d4ed8;
    }
    .status-done {
        background: #dcfce7;
        color: #15803d;
    }
    .status-blocked {
        background: #fee2e2;
        color: #b91c1c;
    }
    .ojk-table textarea {
        min-height: 76px;
        resize: vertical;
        font-size: 0.82rem;
        line-height: 1.35;
    }
    .ojk-table .form-control-sm,
    .ojk-table .form-select-sm {
        font-size: 0.8rem;
    }
    .ojk-table-shell {
        border: 1px solid #dbe5f0;
        border-radius: 10px;
        padding: 0.35rem 0.5rem 0.45rem;
        background: #ffffff;
    }
    .ojk-table th,
    .ojk-table td {
        vertical-align: middle;
        padding: 0.72rem 0.6rem;
    }
    .ojk-table td.ojk-col-no {
        vertical-align: top;
        padding-top: 0.85rem;
    }
    .ojk-table th:first-child,
    .ojk-table td:first-child {
        padding-left: 0.95rem;
    }
    .ojk-table th:last-child,
    .ojk-table td:last-child {
        padding-right: 0.95rem;
    }
    #ojkMitigationTable_wrapper .row {
        margin-left: 0;
        margin-right: 0;
    }
    #ojkMitigationTable_wrapper .dataTables_length,
    #ojkMitigationTable_wrapper .dataTables_filter {
        margin-bottom: 0.6rem;
    }
    #ojkMitigationTable_wrapper .dataTables_length {
        text-align: left;
    }
    #ojkMitigationTable_wrapper .dataTables_length label {
        display: inline-flex;
        align-items: center;
        gap: 0.45rem;
        white-space: nowrap;
        margin-bottom: 0;
    }
    #ojkMitigationTable_wrapper .dataTables_length select {
        width: auto;
        min-width: 82px;
        display: inline-block;
    }
    #ojkMitigationTable_wrapper .dataTables_filter {
        text-align: right;
    }
    #ojkMitigationTable_wrapper .dataTables_filter label {
        display: inline-flex;
        align-items: center;
        gap: 0.45rem;
        margin-bottom: 0;
    }
    #ojkMitigationTable_wrapper .dataTables_filter input {
        margin-left: 0 !important;
        min-width: 220px;
    }
    #ojkMitigationTable_wrapper .dataTables_paginate {
        margin-top: 0.55rem;
    }
    @media (max-width: 768px) {
        #ojkMitigationTable_wrapper .dataTables_filter {
            text-align: left;
        }
        #ojkMitigationTable_wrapper .dataTables_filter input {
            min-width: 160px;
        }
    }
    .timeline-wrapper {
        height: 420px;
        position: relative;
    }
    .timeline-empty {
        border: 1px dashed #cbd5e1;
        border-radius: 10px;
        padding: 1rem;
        color: #64748b;
        background: #f8fafc;
    }
    .import-note {
        font-size: 0.78rem;
        color: #64748b;
    }
    .timeline-meta {
        display: flex;
        flex-direction: column;
        align-items: flex-end;
        gap: 0.1rem;
    }
    .timeline-remaining {
        color: #0f766e;
        font-weight: 600;
    }
</style>
@endsection

@section('content_header')
    <h2 class="page-title mb-1">Monitoring Mitigasi OJK</h2>
    <div class="text-muted">Import data timeline mitigasi OJK, update status mitigasi, dan pantau timeline eksekusi.</div>
@endsection

@section('content')
@php
    $statusClasses = [
        'Not Started' => 'status-not-started',
        'On Progress' => 'status-on-progress',
        'Done' => 'status-done',
        'Blocked' => 'status-blocked',
    ];
    $statusSortOrder = [
        'Not Started' => 1,
        'On Progress' => 2,
        'Blocked' => 3,
        'Done' => 4,
    ];
@endphp

<div class="ojk-summary-shell">
    <div class="ojk-summary-meta">
        <h3 class="ojk-summary-title">Ringkasan Status</h3>
        <div class="ojk-summary-subtitle">Snapshot status mitigasi OJK saat ini.</div>
    </div>
    <div class="row g-2 mb-0">
        <div class="col-6 col-md-4 col-xl">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3>{{ $timeline['total_items'] ?? 0 }}</h3>
                    <p>Total Point</p>
                </div>
                <div class="icon"><i class="fas fa-layer-group"></i></div>
            </div>
        </div>
        <div class="col-6 col-md-4 col-xl">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3>{{ $timeline['status_counts']['Done'] ?? 0 }}</h3>
                    <p>Done</p>
                </div>
                <div class="icon"><i class="fas fa-check-circle"></i></div>
            </div>
        </div>
        <div class="col-6 col-md-4 col-xl">
            <div class="small-box bg-primary">
                <div class="inner">
                    <h3>{{ $timeline['status_counts']['On Progress'] ?? 0 }}</h3>
                    <p>On Progress</p>
                </div>
                <div class="icon"><i class="fas fa-spinner"></i></div>
            </div>
        </div>
        <div class="col-6 col-md-4 col-xl">
            <div class="small-box bg-danger">
                <div class="inner">
                    <h3>{{ $timeline['status_counts']['Blocked'] ?? 0 }}</h3>
                    <p>Blocked</p>
                </div>
                <div class="icon"><i class="fas fa-ban"></i></div>
            </div>
        </div>
        <div class="col-6 col-md-4 col-xl">
            <div class="small-box bg-secondary">
                <div class="inner">
                    <h3>{{ $timeline['undated_items'] ?? 0 }}</h3>
                    <p>Belum Ada Tanggal</p>
                </div>
                <div class="icon"><i class="fas fa-calendar-times"></i></div>
            </div>
        </div>
    </div>
</div>

<div class="card ojk-card mb-3">
    <div class="card-header">
        <div class="ojk-data-header">
            <h3 class="card-title mb-0">Data Point Mitigasi</h3>
            @if(!auth()->user()->isSuperadmin())
                <form id="ojkImportInlineForm" method="POST" action="{{ route('ojk-mitigation.import') }}" enctype="multipart/form-data" class="ojk-import-inline">
                    @csrf
                    <input type="file" id="ojkImportFileInput" name="file" class="d-none" accept=".xls,.xlsx" required>
                    <button type="button" id="ojkImportTriggerBtn" class="btn btn-primary btn-sm with-icon">
                        <i class="fas fa-file-import"></i> Import XLS
                    </button>
                </form>
            @endif
        </div>
    </div>
    <div class="card-body">
        <div class="ojk-import-note-inline mb-2">
            Kolom minimum import: <strong>NO</strong>, <strong>ACTION PLAN</strong>, <strong>PIC</strong>, <strong>SOLUSI</strong>, <strong>TARGET</strong>.
        </div>
        <div class="ojk-table-shell">
            <div class="table-responsive">
                <table id="ojkMitigationTable" class="table table-sm table-vcenter table-hover mb-0 ojk-table" data-has-data="{{ $items->isNotEmpty() ? '1' : '0' }}">
                <thead class="table-light">
                    <tr>
                        <th style="width:90px;" class="text-center">Aksi</th>
                        <th style="width:70px;">No</th>
                        <th style="min-width:260px;">Action Plan</th>
                        <th style="min-width:170px;">PIC</th>
                        <th style="min-width:240px;">Solusi</th>
                        <th style="min-width:200px;">Target</th>
                        <th style="width:150px;">Status Mitigasi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($items as $item)
                        @php
                            $statusClass = $statusClasses[$item->mitigation_status] ?? 'status-on-progress';
                            $sortByNumber = is_numeric($item->item_number) ? (int) $item->item_number : (int) $item->sort_order;
                            $targetSortOrder = !is_null($item->target_date) ? (int) $item->target_date->format('Ymd') : 99999999;
                            $statusOrder = $statusSortOrder[$item->mitigation_status] ?? 99;
                            $deleteFormId = 'delete-form-' . $item->id;
                        @endphp
                        <tr>
                            <td class="text-center" data-order="{{ $sortByNumber }}">
                                <div class="d-flex flex-column align-items-center gap-1">
                                    <a href="{{ route('ojk-mitigation.edit', $item->id) }}" class="btn btn-warning btn-xs" title="Edit" data-toggle="tooltip" aria-label="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <button
                                        type="submit"
                                        form="{{ $deleteFormId }}"
                                        class="btn btn-danger btn-xs"
                                        title="Delete"
                                        data-toggle="tooltip"
                                        aria-label="Delete"
                                        onclick="return confirm('Hapus point mitigasi ini?')"
                                    >
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                            <td class="ojk-col-no" data-order="{{ $sortByNumber }}">
                                <span class="fw-bold">{{ $item->item_number }}</span>
                            </td>
                            <td>
                                {!! nl2br(e((string) $item->action_plan)) !!}
                            </td>
                            <td>
                                {{ $item->pic ?: '-' }}
                            </td>
                            <td>
                                {!! nl2br(e((string) ($item->solution ?: '-'))) !!}
                            </td>
                            <td data-order="{{ $targetSortOrder }}">
                                {!! nl2br(e((string) ($item->target ?: '-'))) !!}
                                @if(!is_null($item->target_date))
                                    <div class="mt-1 small text-muted">Timeline: {{ $item->target_date->format('d M Y') }}</div>
                                @else
                                    <div class="mt-1 small text-muted">Timeline: -</div>
                                @endif
                            </td>
                            <td data-order="{{ $statusOrder }}">
                                <span class="status-pill {{ $statusClass }}">{{ $item->mitigation_status }}</span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-4 text-muted">
                                Belum ada data mitigasi OJK. Silakan import file terlebih dahulu.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="card ojk-card mb-3">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h3 class="card-title mb-0">Timeline Chart Mitigasi OJK</h3>
        <div class="timeline-meta">
            <span class="text-muted small">Point dengan target date terdeteksi: {{ $timeline['dated_items'] ?? 0 }}</span>
            <span class="small timeline-remaining">{{ $timeline['remaining_label'] ?? 'Remaining: -' }}</span>
        </div>
    </div>
    <div class="card-body">
        @if(($timeline['dated_items'] ?? 0) > 0 && !empty($timeline['point_labels']))
            <div class="timeline-wrapper">
                <canvas id="ojkMitigationTimelineChart"></canvas>
            </div>
        @else
            <div class="timeline-empty">
                Belum ada data target date yang valid untuk divisualisasikan. Isi kolom target dengan tanggal (contoh: <strong>10 Maret 2026</strong>).
            </div>
        @endif
    </div>
</div>

@foreach($items as $item)
    <form id="delete-form-{{ $item->id }}" method="POST" action="{{ route('ojk-mitigation.destroy', $item->id) }}">
        @csrf
        @method('DELETE')
    </form>
@endforeach
@endsection

@section('js')
<script src="{{ asset('vendor/datatables/js/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('vendor/datatables/js/dataTables.bootstrap5.min.js') }}"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
<script>
    $(document).ready(function () {
        const importForm = document.getElementById('ojkImportInlineForm');
        const importFileInput = document.getElementById('ojkImportFileInput');
        const importTriggerBtn = document.getElementById('ojkImportTriggerBtn');

        if (importForm && importFileInput && importTriggerBtn) {
            importTriggerBtn.addEventListener('click', function () {
                importFileInput.click();
            });

            importFileInput.addEventListener('change', function () {
                if (!importFileInput.files || !importFileInput.files.length) {
                    return;
                }

                importTriggerBtn.setAttribute('disabled', 'disabled');
                importTriggerBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Importing...';
                importForm.submit();
            });
        }

        const table = $('#ojkMitigationTable');
        const hasData = table.data('has-data') === 1 || table.data('has-data') === '1';
        if (table.length && hasData) {
            table.DataTable({
                responsive: false,
                autoWidth: false,
                scrollX: true,
                pageLength: 10,
                lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, 'All']],
                order: [[1, 'asc']],
                ordering: true,
                dom: "<'row mb-2 align-items-center'<'col-md-6'l><'col-md-6 text-end'f>>" +
                    "rt" +
                    "<'row mt-2'<'col-md-5'i><'col-md-7 text-end'p>>",
                columnDefs: [
                    { targets: [0, 1], type: 'num' },
                ],
                language: {
                    paginate: {
                        previous: '&laquo; Previous',
                        next: 'Next &raquo;',
                    },
                },
            });
        }
    });

    (function () {
        const chartEl = document.getElementById('ojkMitigationTimelineChart');
        if (!chartEl || !window.Chart) {
            return;
        }

        const timeline = @json($timeline);
        const rawDatasets = (timeline.datasets || []).filter(function (dataset) {
            return Array.isArray(dataset.data) && dataset.data.length > 0;
        });

        if (!rawDatasets.length) {
            return;
        }

        const segmentLines = [];
        const datasets = [];

        rawDatasets.forEach(function (dataset) {
            const endpointRows = [];
            let pendingStart = null;

            (dataset.data || []).forEach(function (row) {
                if (!row || typeof row !== 'object') {
                    pendingStart = null;
                    return;
                }

                if (row.segment === 'start') {
                    pendingStart = row;
                    return;
                }

                if (row.segment === 'end') {
                    const startRow = pendingStart && pendingStart.y === row.y ? pendingStart : row;
                    segmentLines.push({
                        color: dataset.borderColor || dataset.backgroundColor || '#2563eb',
                        xStart: startRow.x || row.x,
                        xEnd: row.x,
                        y: row.y
                    });
                    endpointRows.push(row);
                    pendingStart = null;
                    return;
                }

                endpointRows.push(row);
            });

            if (!endpointRows.length) {
                return;
            }

            datasets.push({
                label: dataset.label,
                data: endpointRows,
                backgroundColor: dataset.backgroundColor,
                borderColor: dataset.borderColor,
                pointRadius: 5,
                pointHoverRadius: 7,
                pointHitRadius: 10,
                showLine: false
            });
        });

        if (!datasets.length) {
            return;
        }

        const pointCount = (timeline.point_labels || []).length;
        const minHeight = 420;
        const computedHeight = Math.max(minHeight, pointCount * 44);
        if (chartEl.parentElement) {
            chartEl.parentElement.style.height = computedHeight + 'px';
        }

        const segmentLinePlugin = {
            id: 'segmentLinePlugin',
            beforeDatasetsDraw: function (chart) {
                const xScale = chart.scales.x;
                const yScale = chart.scales.y;
                if (!xScale || !yScale) {
                    return;
                }

                const chartArea = chart.chartArea || {};
                if (!Number.isFinite(chartArea.left) || !Number.isFinite(chartArea.right)) {
                    return;
                }

                const ctx = chart.ctx;
                ctx.save();
                ctx.lineWidth = 4;
                ctx.lineCap = 'round';

                segmentLines.forEach(function (line) {
                    const xStart = xScale.getPixelForValue(line.xStart);
                    const xEnd = xScale.getPixelForValue(line.xEnd);
                    const y = yScale.getPixelForValue(line.y);

                    if (!Number.isFinite(xStart) || !Number.isFinite(xEnd) || !Number.isFinite(y)) {
                        return;
                    }

                    ctx.strokeStyle = line.color || '#2563eb';
                    ctx.beginPath();
                    ctx.moveTo(xStart, y);
                    ctx.lineTo(xEnd, y);
                    ctx.stroke();
                });

                ctx.restore();
            }
        };

        const todayMarkerPlugin = {
            id: 'todayMarkerPlugin',
            afterDatasetsDraw: function (chart, _args, pluginOptions) {
                const xScale = chart.scales.x;
                if (!xScale) {
                    return;
                }

                const todayLabel = (pluginOptions && pluginOptions.todayLabel) || '';
                if (!todayLabel) {
                    return;
                }

                const labels = typeof xScale.getLabels === 'function' ? xScale.getLabels() : [];
                if (!Array.isArray(labels) || labels.indexOf(todayLabel) === -1) {
                    return;
                }

                const x = xScale.getPixelForValue(todayLabel);
                const chartArea = chart.chartArea || {};
                if (!Number.isFinite(x) || !Number.isFinite(chartArea.top) || !Number.isFinite(chartArea.bottom)) {
                    return;
                }

                const ctx = chart.ctx;
                ctx.save();
                ctx.strokeStyle = (pluginOptions && pluginOptions.lineColor) || '#f97316';
                ctx.lineWidth = 2;
                ctx.setLineDash([7, 5]);
                ctx.beginPath();
                ctx.moveTo(x, chartArea.top);
                ctx.lineTo(x, chartArea.bottom);
                ctx.stroke();
                ctx.setLineDash([]);
                ctx.fillStyle = (pluginOptions && pluginOptions.textColor) || '#c2410c';
                ctx.font = '600 11px Arial, sans-serif';
                ctx.textAlign = 'left';
                ctx.textBaseline = 'top';
                ctx.fillText('Today', x + 6, chartArea.top + 4);
                ctx.restore();
            }
        };

        try {
            new Chart(chartEl, {
                type: 'scatter',
                plugins: [segmentLinePlugin, todayMarkerPlugin],
                data: {
                    datasets: datasets
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: {
                        mode: 'nearest',
                        axis: 'xy',
                        intersect: false
                    },
                    plugins: {
                        todayMarkerPlugin: {
                            todayLabel: timeline.today_label || null,
                            lineColor: '#f97316',
                            textColor: '#c2410c'
                        },
                        legend: {
                            position: 'top'
                        },
                        tooltip: {
                            callbacks: {
                                title: function (items) {
                                    if (!items.length) {
                                        return '';
                                    }
                                    return 'Point: ' + (items[0].raw.y || '-');
                                },
                                label: function (context) {
                                    const raw = context.raw || {};
                                    const lines = [];
                                    lines.push('Target: ' + (raw.target_label || raw.x || '-'));
                                    lines.push('Range: ' + (raw.start_label || '-') + ' -> ' + (raw.end_label || '-'));
                                    lines.push('Status: ' + (context.dataset.label || '-'));
                                    lines.push('PIC: ' + (raw.pic || '-'));
                                    if (Number.isInteger(raw.remaining_days)) {
                                        if (raw.remaining_days > 0) {
                                            lines.push('Remaining: ' + raw.remaining_days + ' hari');
                                        } else if (raw.remaining_days === 0) {
                                            lines.push('Remaining: target hari ini');
                                        } else {
                                            lines.push('Remaining: overdue ' + Math.abs(raw.remaining_days) + ' hari');
                                        }
                                    }
                                    lines.push('Action: ' + (raw.action_plan || '-'));
                                    return lines;
                                }
                            }
                        }
                    },
                    scales: {
                        x: {
                            type: 'category',
                            labels: timeline.date_labels || [],
                            offset: true,
                            ticks: {
                                maxRotation: 35,
                                minRotation: 35
                            },
                            grid: {
                                color: '#e2e8f0'
                            },
                            title: {
                                display: true,
                                text: 'Tanggal Target'
                            }
                        },
                        y: {
                            type: 'category',
                            labels: timeline.point_labels || [],
                            offset: true,
                            grid: {
                                color: '#eef2f7'
                            },
                            title: {
                                display: true,
                                text: 'Point Mitigasi'
                            }
                        }
                    }
                }
            });
        } catch (error) {
            if (chartEl.parentElement) {
                chartEl.parentElement.innerHTML = '<div class=\"timeline-empty\">Timeline gagal dirender. Silakan refresh halaman.</div>';
            }
            console.error('OJK timeline render error:', error);
        }
    })();
</script>
@endsection
