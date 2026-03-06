
@extends('layouts.tabler')

@section('title', 'Audit')

@php
    $auditDashboard = is_array($auditDashboard ?? null) ? $auditDashboard : [];
    $period = is_array($auditDashboard['period'] ?? null) ? $auditDashboard['period'] : [];
    $filters = is_array($auditDashboard['filters'] ?? null) ? $auditDashboard['filters'] : [];
    $summaryCards = is_array($auditDashboard['summary_cards'] ?? null) ? $auditDashboard['summary_cards'] : [];
    $rekap = is_array($auditDashboard['rekap'] ?? null) ? $auditDashboard['rekap'] : [];
    $rekapRows = is_array($rekap['rows'] ?? null) ? $rekap['rows'] : [];
    $maps = is_array($auditDashboard['maps'] ?? null) ? $auditDashboard['maps'] : [];
    $mapPoints = is_array($maps['points'] ?? null) ? $maps['points'] : [];
    $progress = is_array($auditDashboard['progress'] ?? null) ? $auditDashboard['progress'] : [];
    $progressByBankRows = is_array($progress['by_bank']['rows'] ?? null) ? $progress['by_bank']['rows'] : [];
    $progressByRegionRows = is_array($progress['by_region']['rows'] ?? null) ? $progress['by_region']['rows'] : [];
    $progressByProductRows = is_array($progress['by_product']['rows'] ?? null) ? $progress['by_product']['rows'] : [];
    $detailOutstanding = is_array($auditDashboard['detail_outstanding'] ?? null) ? $auditDashboard['detail_outstanding'] : [];
    $detailRows = is_array($detailOutstanding['rows'] ?? null) ? $detailOutstanding['rows'] : [];
    $detailRows = array_slice($detailRows, 0, 200);
    $dueMaturity = is_array($auditDashboard['duedate_maturity'] ?? null) ? $auditDashboard['duedate_maturity'] : [];
    $dueByBank = is_array($dueMaturity['by_bank'] ?? null) ? $dueMaturity['by_bank'] : [];
    $dueByDivision = is_array($dueMaturity['by_division'] ?? null) ? $dueMaturity['by_division'] : [];
    $dueByProduct = is_array($dueMaturity['by_product'] ?? null) ? $dueMaturity['by_product'] : [];
@endphp

@section('css')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" crossorigin="">
<style>
.audit-header-actions .form-select,
.audit-header-actions .btn {
    min-width: 140px;
}

/* Fix dropdown visibility - ensure it's not clipped by parent overflow */
.audit-header-actions {
    overflow: visible !important;
}

/* Audit Export Dropdown - same style as working report export */
.audit-export-dropdown {
    position: relative;
}

.audit-export-toggle {
    display: inline-flex;
    align-items: center;
    gap: 0.42rem;
}

.audit-export-menu {
    display: none;
    min-width: 12rem;
    position: absolute;
    top: calc(100% + 0.3rem);
    right: 0;
    left: auto;
    z-index: 2100;
    background-color: #ffffff !important;
    opacity: 1;
    backdrop-filter: none;
    -webkit-backdrop-filter: none;
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    border: 1px solid #d8e3ef;
}

.audit-export-menu.show {
    display: block;
}

.audit-export-menu .dropdown-item i {
    width: 1rem;
}

.audit-export-menu .dropdown-item {
    padding: 0.5rem 1rem;
    color: #0f172a;
    background-color: transparent;
    transition: all 0.2s ease;
}

.audit-export-menu .dropdown-item:hover {
    background-color: #eaf1f8;
    transform: translateX(2px);
}

.audit-subtitle {
    font-size: 0.83rem;
    color: #64748b;
}

.audit-tabs .nav-link {
    border-radius: 0.65rem 0.65rem 0 0;
    font-weight: 600;
    white-space: nowrap;
    color: #0f172a;
    border: 1px solid #d8e3ef;
    background: #eef5fc;
    margin-right: 0.35rem;
}

.audit-tabs .nav-link.active {
    background: #0f3d73;
    border-color: #0f3d73;
    color: #ffffff;
}

.audit-tab-content {
    min-height: 360px;
    border: 1px solid #d8e3ef;
    border-top: 0;
    background: linear-gradient(180deg, #f8fbff 0%, #ffffff 100%);
    padding: 1rem 1rem 1.25rem;
}

.audit-pane-grid {
    display: grid;
    grid-template-columns: repeat(12, minmax(0, 1fr));
    gap: 0.85rem;
}

.audit-panel {
    background: #ffffff;
    border: 1px solid #dbe6f1;
    border-radius: 0.75rem;
    box-shadow: 0 8px 20px rgba(15, 23, 42, 0.06);
    overflow: hidden;
}

.audit-panel-head {
    background: #eaf1f8;
    color: #0f172a;
    font-weight: 700;
    padding: 0.55rem 0.75rem;
    border-bottom: 1px solid #d5e3f1;
}

.audit-panel-body {
    padding: 0.75rem;
}

.audit-mini-card-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
    gap: 0.7rem;
}

.audit-mini-card {
    border: 1px solid #dde7f3;
    border-radius: 0.65rem;
    overflow: hidden;
    background: #ffffff;
}

.audit-mini-title {
    background: #0f3d73;
    color: #ffffff;
    font-size: 0.86rem;
    font-weight: 700;
    padding: 0.45rem 0.65rem;
    text-transform: uppercase;
    letter-spacing: 0.02em;
}

.audit-mini-table {
    margin: 0;
}

.audit-mini-table td,
.audit-mini-table th {
    padding: 0.35rem 0.52rem;
    font-size: 0.76rem;
    border-color: #ecf2f8;
}

.audit-status-dot {
    width: 9px;
    height: 9px;
    border-radius: 999px;
    display: inline-block;
    margin-right: 0.35rem;
}

.audit-table-sm th {
    font-size: 0.7rem;
    text-transform: uppercase;
    letter-spacing: 0.02em;
    color: #475569;
}

.audit-table-sm td {
    font-size: 0.74rem;
    vertical-align: middle;
}

.audit-table-scroll {
    max-height: 560px;
    overflow: auto;
}

.audit-map-shell {
    height: 500px;
    border-radius: 0.65rem;
    border: 1px solid #dbe6f1;
}

.audit-map-legend {
    max-height: 500px;
    overflow: auto;
}

.audit-pct {
    font-weight: 700;
    color: #0f172a;
}

.audit-note {
    color: #334155;
    font-size: 0.81rem;
}

.audit-highlight {
    background: #fff7ed;
    border: 1px solid #fed7aa;
    color: #9a3412;
    border-radius: 0.55rem;
    padding: 0.58rem 0.66rem;
    font-size: 0.78rem;
}

.audit-overdue-badge {
    display: inline-flex;
    align-items: center;
    font-size: 0.67rem;
    font-weight: 700;
    letter-spacing: 0.01em;
    line-height: 1.2;
    padding: 0.14rem 0.48rem;
    border-radius: 999px;
    background: #fee2e2;
    color: #b91c1c;
    border: 1px solid #fecaca;
}

.audit-status-cell {
    display: inline-flex;
    align-items: center;
    flex-wrap: wrap;
    gap: 0.32rem;
}

.audit-status-chip {
    display: inline-flex;
    align-items: center;
    border-radius: 999px;
    padding: 0.14rem 0.5rem;
    font-size: 0.68rem;
    font-weight: 700;
    letter-spacing: 0.01em;
    line-height: 1.2;
    border: 1px solid transparent;
}

.audit-status-chip.is-closed {
    background: #dcfce7;
    color: #166534;
    border-color: #bbf7d0;
}

.audit-status-chip.is-on-progress {
    background: #dbeafe;
    color: #1d4ed8;
    border-color: #bfdbfe;
}

.audit-status-chip.is-domain-bank {
    background: #ffedd5;
    color: #9a3412;
    border-color: #fed7aa;
}

.audit-status-chip.is-default {
    background: #e2e8f0;
    color: #334155;
    border-color: #cbd5e1;
}

.audit-pie-shell {
    height: 320px;
}

.audit-pie-legend {
    margin-top: 0.6rem;
    border-top: 1px solid #e2e8f0;
    padding-top: 0.55rem;
    max-height: 180px;
    overflow: auto;
}

.audit-pie-legend-item {
    display: flex;
    align-items: center;
    gap: 0.45rem;
    font-size: 0.72rem;
    line-height: 1.3;
    color: #334155;
    margin-bottom: 0.3rem;
}

.audit-pie-legend-swatch {
    width: 10px;
    height: 10px;
    border-radius: 2px;
    flex: 0 0 auto;
}

.audit-pie-legend-label {
    flex: 1 1 auto;
}

.audit-pie-legend-count {
    font-weight: 700;
    color: #0f172a;
}

.audit-rekap-map {
    margin-top: 0.65rem;
    border-top: 1px solid #e2e8f0;
    padding-top: 0.55rem;
    max-height: 180px;
    overflow: auto;
}

.audit-rekap-map-item {
    display: grid;
    grid-template-columns: 2.2rem minmax(0, 1fr);
    gap: 0.35rem;
    margin-bottom: 0.32rem;
    font-size: 0.73rem;
    line-height: 1.35;
    color: #334155;
}

.audit-rekap-map-key {
    font-weight: 700;
    color: #0f3d73;
}

@media (max-width: 1199px) {
    .audit-progress-col,
    .audit-progress-right,
    .audit-rekap-table-col,
    .audit-rekap-chart-col,
    .audit-map-col,
    .audit-map-legend-col,
    .audit-due-col,
    .audit-due-chart-col {
        grid-column: span 12 !important;
    }
}

@media (max-width: 767px) {
    .audit-header-actions {
        width: 100%;
    }

    .audit-header-actions form {
        width: 100%;
    }

    .audit-header-actions .form-select,
    .audit-header-actions .btn {
        width: 100%;
    }
}
</style>
@endsection

@section('content_header')
<div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
    <div>
        <h1 class="mb-0">Audit Monitoring Dashboard</h1>
        <div class="audit-subtitle">
            Data posisi {{ $period['month_label'] ?? '-' }} ({{ $period['week_label'] ?? '-' }}) | Refresh: {{ $auditDashboard['generated_at'] ?? '-' }}
        </div>
    </div>
    <div class="audit-header-actions d-flex flex-wrap gap-2 align-items-center justify-content-end">
        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-triangle me-2"></i>
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i>
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
        <form method="GET" action="{{ route('audit.placeholder') }}" class="d-flex align-items-center flex-wrap gap-2">
            <select name="year" class="form-select form-select-sm">
                <option value="">All Years</option>
                @foreach(($filters['years'] ?? []) as $yearOption)
                    <option value="{{ $yearOption }}" {{ (string) ($filters['selected_year'] ?? '') === (string) $yearOption ? 'selected' : '' }}>
                        {{ $yearOption }}
                    </option>
                @endforeach
            </select>
            <select name="bank" class="form-select form-select-sm">
                <option value="">All Bank</option>
                @foreach(($filters['banks'] ?? []) as $bankOption)
                    <option value="{{ $bankOption }}" {{ (string) ($filters['selected_bank'] ?? '') === (string) $bankOption ? 'selected' : '' }}>
                        {{ $bankOption }}
                    </option>
                @endforeach
            </select>
            <button class="btn btn-dark btn-sm with-icon" type="submit">
                <i class="fas fa-filter"></i> Apply
            </button>
            <a href="{{ route('audit.placeholder') }}" class="btn btn-outline-secondary btn-sm with-icon">
                <i class="fas fa-undo"></i> Reset
            </a>
            @if(auth()->check() && !auth()->user()->isSuperadmin() && auth()->user()->isComplianceDivision())
                <button type="button" class="btn btn-primary btn-sm with-icon" data-bs-toggle="modal" data-bs-target="#auditImportModal" data-toggle="modal" data-target="#auditImportModal">
                    <i class="fas fa-file-import"></i> Import XLS
                </button>
            @endif
            @if(auth()->check() && !auth()->user()->isSuperadmin())
                <div class="audit-export-dropdown" data-audit-export>
                    <button
                        class="btn btn-success btn-sm audit-export-toggle"
                        type="button"
                        data-audit-export-toggle
                        aria-expanded="false"
                    >
                        <i class="fas fa-file-export"></i>
                        <span>Export</span>
                        <i class="fas fa-chevron-down"></i>
                    </button>
                    <div class="audit-export-menu" data-audit-export-menu>
                        <a class="dropdown-item" data-audit-export-link href="{{ route('audit.export', 'pdf') }}?year={{ request('year') }}&bank={{ request('bank') }}">
                            <i class="fas fa-file-pdf me-2 text-danger"></i>PDF
                        </a>
                        <a class="dropdown-item" data-audit-export-link href="{{ route('audit.export', 'xlsx') }}?year={{ request('year') }}&bank={{ request('bank') }}">
                            <i class="fas fa-file-excel me-2 text-success"></i>XLSX
                        </a>
                        <a class="dropdown-item" data-audit-export-link href="{{ route('audit.export', 'pptx') }}?year={{ request('year') }}&bank={{ request('bank') }}">
                            <i class="fas fa-file-powerpoint me-2 text-warning"></i>PPTX
                        </a>
                    </div>
                </div>
            @endif
        </form>
    </div>
</div>
@endsection

@push('scripts_body')
<script>
    (function () {
        function closeAllAuditExportDropdowns(exceptEl) {
            document.querySelectorAll('[data-audit-export]').forEach(function (wrapper) {
                if (exceptEl && wrapper === exceptEl) {
                    return;
                }
                const menu = wrapper.querySelector('[data-audit-export-menu]');
                const toggle = wrapper.querySelector('[data-audit-export-toggle]');
                if (menu) {
                    menu.classList.remove('show');
                }
                if (toggle) {
                    toggle.setAttribute('aria-expanded', 'false');
                }
            });
        }

        document.addEventListener('click', function (event) {
            const toggle = event.target.closest('[data-audit-export-toggle]');
            if (toggle) {
                event.preventDefault();
                event.stopPropagation();

                const wrapper = toggle.closest('[data-audit-export]');
                const menu = wrapper ? wrapper.querySelector('[data-audit-export-menu]') : null;
                if (!wrapper || !menu) {
                    return;
                }

                const willOpen = !menu.classList.contains('show');
                closeAllAuditExportDropdowns(wrapper);
                menu.classList.toggle('show', willOpen);
                toggle.setAttribute('aria-expanded', willOpen ? 'true' : 'false');
                return;
            }

            if (!event.target.closest('[data-audit-export]')) {
                closeAllAuditExportDropdowns();
            }
        });

        document.addEventListener('click', function (event) {
            const exportLink = event.target.closest('[data-audit-export-link]');
            if (!exportLink) {
                return;
            }

            const wrapper = exportLink.closest('[data-audit-export]');
            if (!wrapper) {
                return;
            }

            if (wrapper.dataset.exportBusy === '1') {
                event.preventDefault();
                return;
            }

            wrapper.dataset.exportBusy = '1';
            const links = wrapper.querySelectorAll('[data-audit-export-link]');
            links.forEach(function (linkEl) {
                linkEl.classList.add('disabled');
                linkEl.setAttribute('aria-disabled', 'true');
                linkEl.style.pointerEvents = 'none';
            });

            closeAllAuditExportDropdowns();

            // Reset after 3 seconds (faster recovery from errors)
            window.setTimeout(function () {
                wrapper.dataset.exportBusy = '';
                links.forEach(function (linkEl) {
                    linkEl.classList.remove('disabled');
                    linkEl.removeAttribute('aria-disabled');
                    linkEl.style.pointerEvents = '';
                });
            }, 3000);
        });

        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape') {
                closeAllAuditExportDropdowns();
            }
        });
    })();
</script>
@endpush

@section('content')
<div class="card">
    <div class="card-body">
        <ul class="nav nav-tabs audit-tabs flex-nowrap overflow-auto" id="auditTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="audit-tab-executive-summary" data-bs-toggle="tab" data-bs-target="#audit-pane-executive-summary" type="button" role="tab" aria-controls="audit-pane-executive-summary" aria-selected="true">
                    Executive Summary
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="audit-tab-rekap-detail-temuan" data-bs-toggle="tab" data-bs-target="#audit-pane-rekap-detail-temuan" type="button" role="tab" aria-controls="audit-pane-rekap-detail-temuan" aria-selected="false">
                    Rekap Detail Temuan
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="audit-tab-maps-temuan" data-bs-toggle="tab" data-bs-target="#audit-pane-maps-temuan" type="button" role="tab" aria-controls="audit-pane-maps-temuan" aria-selected="false">
                    MAPS Temuan
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="audit-tab-progress-temuan" data-bs-toggle="tab" data-bs-target="#audit-pane-progress-temuan" type="button" role="tab" aria-controls="audit-pane-progress-temuan" aria-selected="false">
                    Progress Temuan
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="audit-tab-trend-temuan" data-bs-toggle="tab" data-bs-target="#audit-pane-trend-temuan" type="button" role="tab" aria-controls="audit-pane-trend-temuan" aria-selected="false">
                    Trend Temuan
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="audit-tab-detail-outstanding" data-bs-toggle="tab" data-bs-target="#audit-pane-detail-outstanding" type="button" role="tab" aria-controls="audit-pane-detail-outstanding" aria-selected="false">
                    Detail Outstanding
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="audit-tab-duedate-maturity" data-bs-toggle="tab" data-bs-target="#audit-pane-duedate-maturity" type="button" role="tab" aria-controls="audit-pane-duedate-maturity" aria-selected="false">
                    Duedate Maturity
                </button>
            </li>
        </ul>

        <div class="tab-content audit-tab-content" id="auditTabsContent">
            <div class="tab-pane fade show active" id="audit-pane-executive-summary" role="tabpanel" aria-labelledby="audit-tab-executive-summary" tabindex="0">
                <div class="audit-pane-grid">
                    <div class="audit-progress-col" style="grid-column: span 12;">
                        <div class="audit-panel">
                            <div class="audit-panel-head">Executive Summary (Introduction)</div>
                            <div class="audit-panel-body">
                                <p class="audit-note mb-2">
                                    Dalam memastikan pencapaian target perusahaan dan terselesaikannya temuan audit, Divisi Compliance melakukan langkah-langkah eksekusi berikut:
                                </p>
                                <ul class="mb-2">
                                    @foreach(($auditDashboard['executive_actions'] ?? []) as $actionItem)
                                        <li>{{ $actionItem }}</li>
                                    @endforeach
                                </ul>
                                <p class="audit-note mb-0">
                                    Untuk memastikan bahwa temuan audit terselesaikan sesuai dengan komitmen yang telah disepakati antara perusahaan dengan klien.
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="audit-progress-col" style="grid-column: span 12;">
                        <div class="audit-mini-card-grid">
                            @foreach($summaryCards as $summaryCard)
                                <div class="audit-mini-card">
                                    <div class="audit-mini-title">{{ $summaryCard['title'] ?? '-' }}</div>
                                    <table class="table table-sm audit-mini-table">
                                        <tbody>
                                            @foreach(($summaryCard['rows'] ?? []) as $row)
                                                @php
                                                    $label = (string) ($row['label'] ?? '-');
                                                    $statusColors = is_array($auditDashboard['status_colors'] ?? null) ? $auditDashboard['status_colors'] : [];
                                                @endphp
                                                <tr>
                                                    <td>
                                                        <span class="audit-status-dot" style="background: {{ $statusColors[$label] ?? '#94a3b8' }};"></span>
                                                        {{ $label }}
                                                    </td>
                                                    <td class="text-end fw-bold">{{ number_format((int) ($row['count'] ?? 0)) }}</td>
                                                </tr>
                                            @endforeach
                                            <tr class="fw-bold table-light">
                                                <td>Grand Total</td>
                                                <td class="text-end">{{ number_format((int) ($summaryCard['grand_total'] ?? 0)) }}</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            <div class="tab-pane fade" id="audit-pane-rekap-detail-temuan" role="tabpanel" aria-labelledby="audit-tab-rekap-detail-temuan" tabindex="0">
                <div class="audit-pane-grid">
                    <div class="audit-rekap-table-col" style="grid-column: span 6;">
                        <div class="audit-panel">
                            <div class="audit-panel-head">Rekap Detail Temuan</div>
                            <div class="audit-panel-body audit-table-scroll">
                                <table class="table table-sm table-striped table-hover audit-table-sm mb-0">
                                    <thead>
                                        <tr>
                                            <th>Region</th>
                                            <th>Bank</th>
                                            <th>Years</th>
                                            <th>Auditee</th>
                                            <th class="text-end">Total</th>
                                            <th class="text-end">Closed</th>
                                            <th class="text-end">On Progress</th>
                                            <th class="text-end">Domain Bank</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($rekapRows as $row)
                                            <tr>
                                                <td>{{ $row['region'] ?? '-' }}</td>
                                                <td>{{ $row['bank'] ?? '-' }}</td>
                                                <td>{{ $row['year'] ?? '-' }}</td>
                                                <td>{{ $row['auditee'] ?? '-' }}</td>
                                                <td class="text-end">{{ number_format((int) ($row['total'] ?? 0)) }}</td>
                                                <td class="text-end">{{ number_format((int) ($row['closed'] ?? 0)) }}</td>
                                                <td class="text-end">{{ number_format((int) ($row['on_progress'] ?? 0)) }}</td>
                                                <td class="text-end">{{ number_format((int) ($row['domain_bank'] ?? 0)) }}</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="8" class="text-center text-muted">No data available.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div class="audit-rekap-chart-col" style="grid-column: span 6;">
                        <div class="audit-panel">
                            <div class="audit-panel-head">Rekap Detail Temuan Bar</div>
                            <div class="audit-panel-body audit-table-scroll" style="max-height: 560px;">
                                <canvas id="auditRekapBarChart" height="520"></canvas>
                                <div id="auditRekapBarMap" class="audit-rekap-map"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="tab-pane fade" id="audit-pane-maps-temuan" role="tabpanel" aria-labelledby="audit-tab-maps-temuan" tabindex="0">
                <div class="audit-pane-grid">
                    <div class="audit-map-col" style="grid-column: span 9;">
                        <div class="audit-panel">
                            <div class="audit-panel-head">Total Temuan Based On Maps</div>
                            <div class="audit-panel-body">
                                <div id="auditMapsContainer" class="audit-map-shell"></div>
                            </div>
                        </div>
                    </div>
                    <div class="audit-map-legend-col" style="grid-column: span 3;">
                        <div class="audit-panel">
                            <div class="audit-panel-head">Daftar Audit</div>
                            <div class="audit-panel-body audit-map-legend p-0">
                                <table class="table table-sm table-striped mb-0 audit-table-sm">
                                    <thead>
                                        <tr>
                                            <th>Bank</th>
                                            <th class="text-end">Total</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($mapPoints as $point)
                                            <tr>
                                                <td>{{ $point['bank'] ?? '-' }}</td>
                                                <td class="text-end">{{ number_format((int) ($point['total'] ?? 0)) }}</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="2" class="text-center text-muted">No map points</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="tab-pane fade" id="audit-pane-progress-temuan" role="tabpanel" aria-labelledby="audit-tab-progress-temuan" tabindex="0">
                <div class="audit-pane-grid">
                    <div class="audit-progress-col" style="grid-column: span 6;">
                        <div class="audit-panel">
                            <div class="audit-panel-head">Total Temuan Per Status</div>
                            <div class="audit-panel-body">
                                <canvas id="auditProgressStatusChart" height="240"></canvas>
                            </div>
                        </div>
                    </div>
                    <div class="audit-progress-right" style="grid-column: span 6;">
                        <div class="audit-panel">
                            <div class="audit-panel-head">Progress Temuan Per Produk</div>
                            <div class="audit-panel-body audit-table-scroll" style="max-height: 260px;">
                                <table class="table table-sm table-striped table-hover audit-table-sm mb-0">
                                    <thead>
                                        <tr>
                                            <th>Product</th>
                                            <th class="text-end">Closed</th>
                                            <th class="text-end">Domain Bank</th>
                                            <th class="text-end">On Progress</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($progressByProductRows as $row)
                                            <tr>
                                                <td>{{ $row['group'] ?? '-' }}</td>
                                                <td class="text-end audit-pct">{{ number_format((float) ($row['percentages']['Closed'] ?? 0), 2) }}%</td>
                                                <td class="text-end audit-pct">{{ number_format((float) ($row['percentages']['Domain Bank'] ?? 0), 2) }}%</td>
                                                <td class="text-end audit-pct">{{ number_format((float) ($row['percentages']['On Progress'] ?? 0), 2) }}%</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="4" class="text-center text-muted">No product progress data.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div class="audit-progress-col" style="grid-column: span 6;">
                        <div class="audit-panel">
                            <div class="audit-panel-head">Progress Temuan Per Status (By Bank)</div>
                            <div class="audit-panel-body audit-table-scroll" style="max-height: 290px;">
                                <table class="table table-sm table-striped table-hover audit-table-sm mb-0">
                                    <thead>
                                        <tr>
                                            <th>Bank</th>
                                            <th class="text-end">Closed</th>
                                            <th class="text-end">Domain Bank</th>
                                            <th class="text-end">On Progress</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($progressByBankRows as $row)
                                            <tr>
                                                <td>{{ $row['group'] ?? '-' }}</td>
                                                <td class="text-end audit-pct">{{ number_format((float) ($row['percentages']['Closed'] ?? 0), 2) }}%</td>
                                                <td class="text-end audit-pct">{{ number_format((float) ($row['percentages']['Domain Bank'] ?? 0), 2) }}%</td>
                                                <td class="text-end audit-pct">{{ number_format((float) ($row['percentages']['On Progress'] ?? 0), 2) }}%</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="4" class="text-center text-muted">No bank progress data.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div class="audit-progress-right" style="grid-column: span 6;">
                        <div class="audit-panel">
                            <div class="audit-panel-head">Progress Temuan Per Region</div>
                            <div class="audit-panel-body">
                                <canvas id="auditProgressRegionChart" height="240"></canvas>
                                <div class="audit-highlight mt-2">
                                    Region view diturunkan dari mapping bank internal deck: Region 1 (Aceh/Bengkulu/Sumut), Region 2 (Sulteng), Internal Collega.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="tab-pane fade" id="audit-pane-trend-temuan" role="tabpanel" aria-labelledby="audit-tab-trend-temuan" tabindex="0">
                <div class="audit-pane-grid">
                    <div class="audit-progress-col" style="grid-column: span 12;">
                            <div class="audit-panel">
                                <div class="audit-panel-head">Trend Temuan Bulanan by Created Date</div>
                                <div class="audit-panel-body">
                                    <canvas id="auditTrendCreatedChart" height="220"></canvas>
                                </div>
                            </div>
                        </div>
                    <div class="audit-progress-col" style="grid-column: span 12;">
                        <div class="audit-panel">
                            <div class="audit-panel-head">Trend Temuan Bulanan by Closed Date</div>
                            <div class="audit-panel-body">
                                <canvas id="auditTrendClosedChart" height="220"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="tab-pane fade" id="audit-pane-detail-outstanding" role="tabpanel" aria-labelledby="audit-tab-detail-outstanding" tabindex="0">
                <div class="audit-pane-grid">
                    <div class="audit-progress-col" style="grid-column: span 6;">
                        <div class="audit-panel">
                            <div class="audit-panel-head">Total Temuan Per Status</div>
                            <div class="audit-panel-body">
                                <canvas id="auditOutstandingStatusChart" height="210"></canvas>
                            </div>
                        </div>
                    </div>
                    <div class="audit-progress-right" style="grid-column: span 6;">
                        <div class="audit-panel">
                            <div class="audit-panel-head">Resume Temuan</div>
                            <div class="audit-panel-body p-0">
                                <table class="table table-sm table-striped audit-table-sm mb-0">
                                    <thead>
                                        <tr>
                                            <th>Status Exit</th>
                                            <th class="text-end">Count</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach(($summaryCards['resume_temuan']['rows'] ?? []) as $row)
                                            <tr>
                                                <td>{{ $row['label'] ?? '-' }}</td>
                                                <td class="text-end">{{ number_format((int) ($row['count'] ?? 0)) }}</td>
                                            </tr>
                                        @endforeach
                                        <tr class="table-light fw-bold">
                                            <td>Grand Total</td>
                                            <td class="text-end">{{ number_format((int) ($summaryCards['resume_temuan']['grand_total'] ?? 0)) }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div class="audit-progress-col" style="grid-column: span 12;">
                        <div class="audit-panel">
                            <div class="audit-panel-head">Detail Resume Temuan Per Bank</div>
                            <div class="audit-panel-body audit-table-scroll">
                                <table class="table table-sm table-striped table-hover audit-table-sm mb-0">
                                    <thead>
                                        <tr>
                                            <th>Bank</th>
                                            <th>Date</th>
                                            <th>Month</th>
                                            <th>Year</th>
                                            <th>Product</th>
                                            <th>Product Name</th>
                                            <th>Status Detail</th>
                                            <th>PIC</th>
                                            <th>Log CSS</th>
                                            <th>Status (Summary)</th>
                                            <th>Task</th>
                                            <th>Assigned To</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($detailRows as $row)
                                            @php
                                                $statusSummaryLabel = (string) ($row['status_summary'] ?? '-');
                                                $statusSummaryKey = strtolower(trim($statusSummaryLabel));
                                                $statusSummaryClass = 'is-default';
                                                if ($statusSummaryKey === 'closed') {
                                                    $statusSummaryClass = 'is-closed';
                                                } elseif ($statusSummaryKey === 'on progress') {
                                                    $statusSummaryClass = 'is-on-progress';
                                                } elseif ($statusSummaryKey === 'domain bank') {
                                                    $statusSummaryClass = 'is-domain-bank';
                                                }
                                            @endphp
                                            <tr>
                                                <td>{{ $row['bank'] ?? '-' }}</td>
                                                <td>{{ $row['date'] ?? '-' }}</td>
                                                <td>{{ $row['month'] ?? '-' }}</td>
                                                <td>{{ $row['year'] ?? '-' }}</td>
                                                <td>{{ $row['product'] ?? '-' }}</td>
                                                <td>{{ $row['product_name'] ?? '-' }}</td>
                                                <td>{{ $row['status_detail'] ?? '-' }}</td>
                                                <td>{{ $row['pic'] ?? '-' }}</td>
                                                <td>{{ $row['log_css'] ?? '-' }}</td>
                                                <td>
                                                    <span class="audit-status-cell">
                                                        <span class="audit-status-chip {{ $statusSummaryClass }}">{{ $statusSummaryLabel }}</span>
                                                    @if(!empty($row['is_overdue']))
                                                        <span class="audit-overdue-badge">Overdue</span>
                                                    @endif
                                                    </span>
                                                </td>
                                                <td>{{ \Illuminate\Support\Str::limit((string) ($row['task'] ?? '-'), 180) }}</td>
                                                <td>{{ $row['assigned_to'] ?? '-' }}</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="12" class="text-center text-muted">No outstanding detail available.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="tab-pane fade" id="audit-pane-duedate-maturity" role="tabpanel" aria-labelledby="audit-tab-duedate-maturity" tabindex="0">
                <div class="audit-pane-grid">
                    <div class="audit-progress-col" style="grid-column: span 12;">
                        <div class="audit-panel">
                            <div class="audit-panel-head">Dashboard Duedate Maturity {{ $period['month_label'] ?? '-' }} ({{ $period['week_label'] ?? '-' }})</div>
                            <div class="audit-panel-body">
                                <div class="d-flex flex-wrap gap-3 align-items-center">
                                    <div class="audit-highlight">
                                        Total overdue item: <strong>{{ number_format((int) ($dueMaturity['grand_total'] ?? 0)) }}</strong>
                                    </div>
                                    <div class="audit-note">
                                        Jatuh tempo sd tanggal {{ $period['overdue_until'] ?? '-' }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="audit-due-col" style="grid-column: span 4;">
                        <div class="audit-panel">
                            <div class="audit-panel-head">Duedate Maturity Per Bank</div>
                            <div class="audit-panel-body p-0 audit-table-scroll" style="max-height: 290px;">
                                <table class="table table-sm table-striped mb-0 audit-table-sm">
                                    <thead>
                                        <tr>
                                            <th>Bank</th>
                                            <th class="text-end"><=6 Bulan</th>
                                            <th class="text-end"><=9 Bulan</th>
                                            <th class="text-end">>9 Bulan</th>
                                            <th class="text-end">Grand Total</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($dueByBank as $row)
                                            <tr class="{{ !empty($row['is_total']) ? 'table-light fw-bold' : '' }}">
                                                <td>{{ $row['group'] ?? '-' }}</td>
                                                <td class="text-end">{{ number_format((int) ($row['<=6 Bulan'] ?? 0)) }}</td>
                                                <td class="text-end">{{ number_format((int) ($row['<=9 Bulan'] ?? 0)) }}</td>
                                                <td class="text-end">{{ number_format((int) ($row['>9 Bulan'] ?? 0)) }}</td>
                                                <td class="text-end">{{ number_format((int) ($row['grand_total'] ?? 0)) }}</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="5" class="text-center text-muted">No maturity data.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div class="audit-due-col" style="grid-column: span 4;">
                        <div class="audit-panel">
                            <div class="audit-panel-head">Duedate Maturity Per Divisi</div>
                            <div class="audit-panel-body p-0 audit-table-scroll" style="max-height: 290px;">
                                <table class="table table-sm table-striped mb-0 audit-table-sm">
                                    <thead>
                                        <tr>
                                            <th>Divisi</th>
                                            <th class="text-end"><=6 Bulan</th>
                                            <th class="text-end"><=9 Bulan</th>
                                            <th class="text-end">>9 Bulan</th>
                                            <th class="text-end">Grand Total</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($dueByDivision as $row)
                                            <tr class="{{ !empty($row['is_total']) ? 'table-light fw-bold' : '' }}">
                                                <td>{{ $row['group'] ?? '-' }}</td>
                                                <td class="text-end">{{ number_format((int) ($row['<=6 Bulan'] ?? 0)) }}</td>
                                                <td class="text-end">{{ number_format((int) ($row['<=9 Bulan'] ?? 0)) }}</td>
                                                <td class="text-end">{{ number_format((int) ($row['>9 Bulan'] ?? 0)) }}</td>
                                                <td class="text-end">{{ number_format((int) ($row['grand_total'] ?? 0)) }}</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="5" class="text-center text-muted">No maturity data.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div class="audit-due-col" style="grid-column: span 4;">
                        <div class="audit-panel">
                            <div class="audit-panel-head">Duedate Maturity Per Product</div>
                            <div class="audit-panel-body p-0 audit-table-scroll" style="max-height: 290px;">
                                <table class="table table-sm table-striped mb-0 audit-table-sm">
                                    <thead>
                                        <tr>
                                            <th>Product</th>
                                            <th class="text-end"><=6 Bulan</th>
                                            <th class="text-end"><=9 Bulan</th>
                                            <th class="text-end">>9 Bulan</th>
                                            <th class="text-end">Grand Total</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($dueByProduct as $row)
                                            <tr class="{{ !empty($row['is_total']) ? 'table-light fw-bold' : '' }}">
                                                <td>{{ $row['group'] ?? '-' }}</td>
                                                <td class="text-end">{{ number_format((int) ($row['<=6 Bulan'] ?? 0)) }}</td>
                                                <td class="text-end">{{ number_format((int) ($row['<=9 Bulan'] ?? 0)) }}</td>
                                                <td class="text-end">{{ number_format((int) ($row['>9 Bulan'] ?? 0)) }}</td>
                                                <td class="text-end">{{ number_format((int) ($row['grand_total'] ?? 0)) }}</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="5" class="text-center text-muted">No maturity data.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div class="audit-due-chart-col" style="grid-column: span 4;">
                        <div class="audit-panel">
                            <div class="audit-panel-head">Pie - Bank</div>
                            <div class="audit-panel-body">
                                <div class="audit-pie-shell">
                                    <canvas id="auditDuePieBank" height="320"></canvas>
                                </div>
                                <div id="auditDuePieBankLegend" class="audit-pie-legend"></div>
                            </div>
                        </div>
                    </div>
                    <div class="audit-due-chart-col" style="grid-column: span 4;">
                        <div class="audit-panel">
                            <div class="audit-panel-head">Pie - Divisi</div>
                            <div class="audit-panel-body">
                                <div class="audit-pie-shell">
                                    <canvas id="auditDuePieDivision" height="320"></canvas>
                                </div>
                                <div id="auditDuePieDivisionLegend" class="audit-pie-legend"></div>
                            </div>
                        </div>
                    </div>
                    <div class="audit-due-chart-col" style="grid-column: span 4;">
                        <div class="audit-panel">
                            <div class="audit-panel-head">Pie - Product</div>
                            <div class="audit-panel-body">
                                <div class="audit-pie-shell">
                                    <canvas id="auditDuePieProduct" height="320"></canvas>
                                </div>
                                <div id="auditDuePieProductLegend" class="audit-pie-legend"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script id="auditDashboardPayload" type="application/json">@json($auditDashboard)</script>
@if(auth()->check() && !auth()->user()->isSuperadmin() && auth()->user()->isComplianceDivision())
<div class="modal fade" id="auditImportModal" tabindex="-1" aria-labelledby="auditImportModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('audit.import.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="auditImportModalLabel">Import Audit Monitoring</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" data-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Excel File (.xls/.xlsx)</label>
                        <input type="file" name="file" class="form-control" accept=".xls,.xlsx" required>
                        <small class="text-muted">Gunakan file summary monitoring audit. Sistem akan memprioritaskan sheet <strong>SUMMARY UPDATE</strong>.</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Start Import</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif
@endsection

@section('js')
<script src="https://cdn.jsdelivr.net/npm/chart.js@2.9.4/dist/Chart.min.js"></script>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" crossorigin=""></script>
<script>
(function () {
    const payloadNode = document.getElementById('auditDashboardPayload');
    if (!payloadNode) {
        return;
    }

    let payload = {};
    try {
        payload = JSON.parse(payloadNode.textContent || '{}');
    } catch (error) {
        payload = {};
    }

    if (!payload || typeof payload !== 'object' || Array.isArray(payload)) {
        payload = {};
    }

    const statusColors = payload.status_colors || {};
    const fallbackColors = ['#2563eb', '#14b8a6', '#f59e0b', '#ef4444', '#22c55e', '#f97316', '#8b5cf6'];
    const chartInstances = [];

    function toColor(label, index) {
        return statusColors[label] || fallbackColors[index % fallbackColors.length];
    }

    function chartConfig(type, labels, datasets, options = {}) {
        return {
            type: type,
            data: {
                labels: labels || [],
                datasets: (datasets || []).map(function (dataset, index) {
                    const color = dataset.backgroundColor || toColor(dataset.label, index);
                    return Object.assign({
                        borderColor: dataset.borderColor || color,
                        backgroundColor: color,
                        borderWidth: 1,
                        fill: false,
                    }, dataset);
                }),
            },
            options: Object.assign({
                responsive: true,
                maintainAspectRatio: false,
                legend: {
                    position: 'top',
                    labels: {
                        boxWidth: 10,
                        fontSize: 11,
                        fontColor: '#334155',
                    },
                },
                layout: {
                    padding: {
                        top: 2,
                        right: 8,
                        bottom: 8,
                        left: 6,
                    },
                },
                tooltips: {
                    mode: 'index',
                    intersect: false,
                },
                scales: {
                    yAxes: [{
                        ticks: {
                            beginAtZero: true,
                            fontColor: '#334155',
                            fontSize: 11,
                            precision: 0,
                            padding: 6,
                        },
                        gridLines: {
                            color: '#e2e8f0',
                            drawBorder: false,
                        },
                    }],
                    xAxes: [{
                        ticks: {
                            autoSkip: true,
                            maxTicksLimit: 12,
                            maxRotation: 40,
                            minRotation: 0,
                            fontColor: '#334155',
                            fontSize: 11,
                            padding: 6,
                        },
                        gridLines: {
                            color: '#edf2f7',
                            drawBorder: false,
                        },
                    }],
                },
            }, options),
        };
    }

    function renderChart(canvasId, config) {
        const canvas = document.getElementById(canvasId);
        if (!canvas || typeof window.Chart === 'undefined') {
            return null;
        }

        const declaredHeight = Number(canvas.getAttribute('height') || 0);
        if (canvas.parentElement && declaredHeight > 0) {
            // Chart.js v2 responsive needs a measurable parent height.
            canvas.parentElement.style.position = canvas.parentElement.style.position || 'relative';
            if (!canvas.parentElement.style.height) {
                canvas.parentElement.style.height = declaredHeight + 'px';
            }
            canvas.style.height = declaredHeight + 'px';
        }

        const ctx = canvas.getContext('2d');
        const chart = new window.Chart(ctx, config);
        chartInstances.push(chart);

        return chart;
    }

    function refreshCharts() {
        chartInstances.forEach(function (chart) {
            if (!chart) {
                return;
            }

            if (typeof chart.resize === 'function') {
                chart.resize();
            }

            if (typeof chart.update === 'function') {
                chart.update();
            }
        });
    }

    const rekapBar = (((payload.rekap || {}).bar_chart) || {});
    const rekapRawLabels = rekapBar.labels || [];
    const rekapLabels = rekapRawLabels.map(function (_, index) {
        return '#' + (index + 1);
    });
    const rekapCanvas = document.getElementById('auditRekapBarChart');
    if (rekapCanvas) {
        const dynamicHeight = Math.max(520, rekapLabels.length * 24);
        rekapCanvas.setAttribute('height', String(dynamicHeight));
    }
    const rekapMapNode = document.getElementById('auditRekapBarMap');
    if (rekapMapNode) {
        if (rekapRawLabels.length === 0) {
            rekapMapNode.innerHTML = '<div class="text-muted small">No rekap mapping.</div>';
        } else {
            rekapMapNode.innerHTML = rekapRawLabels.map(function (label, index) {
                return [
                    '<div class="audit-rekap-map-item">',
                    '<div class="audit-rekap-map-key">#' + (index + 1) + '</div>',
                    '<div>' + escapeHtml(label) + '</div>',
                    '</div>',
                ].join('');
            }).join('');
        }
    }

    renderChart('auditRekapBarChart', chartConfig('horizontalBar', rekapLabels, rekapBar.datasets || [], {
        legend: {
            position: 'top',
            labels: {
                boxWidth: 11,
                fontSize: 11,
            },
        },
        tooltips: {
            mode: 'nearest',
            intersect: true,
            callbacks: {
                title: function (tooltipItems) {
                    if (!tooltipItems || !tooltipItems.length) {
                        return '';
                    }

                    const index = Number(tooltipItems[0].index || 0);

                    return '#'+ (index + 1) + ' ' + String(rekapRawLabels[index] || '');
                },
            },
        },
        scales: {
            xAxes: [{
                stacked: false,
                gridLines: {
                    color: '#e2e8f0',
                },
                ticks: {
                    beginAtZero: true,
                    precision: 0,
                },
            }],
            yAxes: [{
                stacked: false,
                gridLines: {
                    display: false,
                },
                ticks: {
                    autoSkip: false,
                    fontSize: 11,
                    fontColor: '#334155',
                    padding: 6,
                },
            }],
        },
    }));

    const progressStatusChart = (((payload.progress || {}).status_by_bank_chart) || {});
    renderChart('auditProgressStatusChart', chartConfig('bar', progressStatusChart.labels || [], progressStatusChart.datasets || [], {
        scales: {
            yAxes: [{
                ticks: {
                    beginAtZero: true,
                },
            }],
            xAxes: [{
                stacked: true,
                ticks: {
                    autoSkip: false,
                },
            }],
        },
    }));

    const progressRegionChart = (((payload.progress || {}).region_percentage_chart) || {});
    renderChart('auditProgressRegionChart', chartConfig('bar', progressRegionChart.labels || [], progressRegionChart.datasets || [], {
        scales: {
            yAxes: [{
                stacked: true,
                ticks: {
                    beginAtZero: true,
                    max: 100,
                    callback: function (value) {
                        return value + '%';
                    },
                },
            }],
            xAxes: [{
                stacked: true,
                ticks: {
                    autoSkip: false,
                },
            }],
        },
    }));

    renderChart('auditOutstandingStatusChart', chartConfig('bar', progressStatusChart.labels || [], progressStatusChart.datasets || [], {
        scales: {
            yAxes: [{
                ticks: {
                    beginAtZero: true,
                },
            }],
            xAxes: [{
                stacked: true,
                ticks: {
                    autoSkip: false,
                },
            }],
        },
    }));

    const trendCreated = (((payload.trend || {}).created) || {});
    renderChart('auditTrendCreatedChart', chartConfig('line', trendCreated.labels || [], trendCreated.datasets || [], {
        elements: {
            line: {
                tension: 0.25,
                borderWidth: 2,
            },
            point: {
                radius: 3.5,
                hoverRadius: 5,
            },
        },
        legend: {
            position: 'top',
            labels: {
                boxWidth: 11,
                fontSize: 12,
                fontColor: '#334155',
            },
        },
        scales: {
            yAxes: [{
                ticks: {
                    beginAtZero: true,
                    precision: 0,
                },
                scaleLabel: {
                    display: true,
                    labelString: 'Jumlah Temuan',
                    fontColor: '#475569',
                    fontSize: 11,
                    padding: 8,
                },
            }],
            xAxes: [{
                gridLines: {
                    display: false,
                },
                ticks: {
                    autoSkip: true,
                    maxTicksLimit: 12,
                    maxRotation: 0,
                    minRotation: 0,
                    callback: function (value) {
                        const text = String(value || '');
                        return text.length > 14 ? text.slice(0, 14) + '...' : text;
                    },
                },
                scaleLabel: {
                    display: true,
                    labelString: 'Periode',
                    fontColor: '#475569',
                    fontSize: 11,
                    padding: 8,
                },
            }],
        },
    }));

    const trendClosed = (((payload.trend || {}).closed) || {});
    renderChart('auditTrendClosedChart', chartConfig('line', trendClosed.labels || [], trendClosed.datasets || [], {
        elements: {
            line: {
                tension: 0.25,
                borderWidth: 2,
            },
            point: {
                radius: 3.5,
                hoverRadius: 5,
            },
        },
        legend: {
            position: 'top',
            labels: {
                boxWidth: 11,
                fontSize: 12,
                fontColor: '#334155',
            },
        },
        scales: {
            yAxes: [{
                ticks: {
                    beginAtZero: true,
                    precision: 0,
                },
                scaleLabel: {
                    display: true,
                    labelString: 'Jumlah Temuan',
                    fontColor: '#475569',
                    fontSize: 11,
                    padding: 8,
                },
            }],
            xAxes: [{
                gridLines: {
                    display: false,
                },
                ticks: {
                    autoSkip: true,
                    maxTicksLimit: 12,
                    maxRotation: 0,
                    minRotation: 0,
                    callback: function (value) {
                        const text = String(value || '');
                        return text.length > 14 ? text.slice(0, 14) + '...' : text;
                    },
                },
                scaleLabel: {
                    display: true,
                    labelString: 'Periode',
                    fontColor: '#475569',
                    fontSize: 11,
                    padding: 8,
                },
            }],
        },
    }));

    function escapeHtml(text) {
        return String(text)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function piePalette(size) {
        const colors = [];
        if (size <= 0) {
            return colors;
        }

        for (let i = 0; i < size; i += 1) {
            const hue = Math.round((360 / size) * i);
            colors.push('hsl(' + hue + ', 70%, 48%)');
        }

        return colors;
    }

    function renderPie(canvasId, legendId, slices) {
        const labels = (slices || []).map(function (item) {
            return item.label;
        });
        const data = (slices || []).map(function (item) {
            return Number(item.count || 0);
        });
        const colors = piePalette(labels.length);

        const legendNode = document.getElementById(legendId);
        if (legendNode) {
            if (labels.length === 0) {
                legendNode.innerHTML = '<div class="text-muted small">No pie data.</div>';
            } else {
                legendNode.innerHTML = labels.map(function (label, index) {
                    return [
                        '<div class="audit-pie-legend-item">',
                        '<span class="audit-pie-legend-swatch" style="background:' + colors[index] + ';"></span>',
                        '<span class="audit-pie-legend-label">' + escapeHtml(label) + '</span>',
                        '<span class="audit-pie-legend-count">' + Number(data[index] || 0) + '</span>',
                        '</div>'
                    ].join('');
                }).join('');
            }
        }

        renderChart(canvasId, {
            type: 'pie',
            data: {
                labels: labels,
                datasets: [{
                    data: data,
                    backgroundColor: colors,
                    borderWidth: 1,
                }],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutoutPercentage: 0,
                legend: {
                    display: false,
                },
                tooltips: {
                    callbacks: {
                        label: function (tooltipItem, chartData) {
                            const label = (chartData.labels || [])[tooltipItem.index] || '-';
                            const value = Number(((chartData.datasets || [])[0] || {}).data[tooltipItem.index] || 0);

                            return label + ': ' + value;
                        }
                    }
                }
            },
        });
    }

    const due = payload.duedate_maturity || {};
    renderPie('auditDuePieBank', 'auditDuePieBankLegend', due.pie_by_bank || []);
    renderPie('auditDuePieDivision', 'auditDuePieDivisionLegend', due.pie_by_division || []);
    renderPie('auditDuePieProduct', 'auditDuePieProductLegend', due.pie_by_product || []);

    let auditMap = null;
    function initializeAuditMap() {
        if (auditMap || typeof window.L === 'undefined') {
            return;
        }

        const mapContainer = document.getElementById('auditMapsContainer');
        if (!mapContainer) {
            return;
        }

        const points = (((payload.maps || {}).points) || []);
        auditMap = window.L.map(mapContainer, {
            zoomControl: true,
            attributionControl: true,
            scrollWheelZoom: false,
        }).setView([-2.2, 117.2], 4);

        window.L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 18,
            attribution: '&copy; OpenStreetMap contributors',
        }).addTo(auditMap);

        points.forEach(function (point) {
            const marker = window.L.circleMarker([point.lat, point.lng], {
                radius: 8,
                color: '#0f3d73',
                fillColor: '#38bdf8',
                fillOpacity: 0.9,
                weight: 2,
            }).addTo(auditMap);

            marker.bindPopup([
                '<strong>' + (point.bank || '-') + '</strong>',
                'Total: ' + (point.total || 0),
                'Closed: ' + (point.closed || 0),
                'Domain Bank: ' + (point.domain_bank || 0),
                'On Progress: ' + (point.on_progress || 0),
            ].join('<br>'));
        });
    }

    const mapTabButton = document.getElementById('audit-tab-maps-temuan');
    if (mapTabButton) {
        mapTabButton.addEventListener('shown.bs.tab', function () {
            initializeAuditMap();
            refreshCharts();
            if (auditMap) {
                window.setTimeout(function () {
                    auditMap.invalidateSize();
                }, 150);
            }
        });
    }

    document.querySelectorAll('#auditTabs [data-bs-toggle="tab"]').forEach(function (tabButton) {
        tabButton.addEventListener('shown.bs.tab', function () {
            window.setTimeout(function () {
                refreshCharts();
            }, 120);
        });
    });

    const mapPane = document.getElementById('audit-pane-maps-temuan');
    if (mapPane && mapPane.classList.contains('show') && mapPane.classList.contains('active')) {
        initializeAuditMap();
    }

    window.setTimeout(function () {
        refreshCharts();
    }, 120);
})();
</script>
@endsection
