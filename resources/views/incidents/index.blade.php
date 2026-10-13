@extends('layouts.tabler')

@section('title', 'Incident Management')

@section('css')
<link rel="stylesheet" href="{{ asset('vendor/datatables/css/dataTables.bootstrap5.min.css') }}">
<style>
    .incident-card {
        border: 1px solid #dbe4ef;
        border-radius: 12px;
        background: #ffffff;
        box-shadow: 0 6px 16px rgba(15, 23, 42, 0.05);
        margin-bottom: 1rem;
    }

    .incident-card .card-header {
        border-bottom: 1px solid #e5edf6;
        background: #f8fbff;
    }

    .incident-kpi {
        border: 1px solid #dbe4ef;
        border-radius: 12px;
        background: linear-gradient(170deg, #f8fbff 0%, #f4f8ff 100%);
        padding: 0.85rem;
        margin-bottom: 1rem;
    }

    .incident-kpi .small-box {
        border-radius: 0.75rem;
        box-shadow: 0 8px 18px rgba(15, 23, 42, 0.08);
        min-height: 104px;
    }

    .financial-kpi-card {
        border: 1px solid #fed7aa;
        background: linear-gradient(135deg, #fffbeb 0%, #fef3c7 100%);
        border-radius: 12px;
        box-shadow: 0 4px 12px rgba(245, 158, 11, 0.08);
    }

    .severity-badge,
    .status-badge,
    .verdict-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 999px;
        padding: 0.2rem 0.55rem;
        font-size: 0.72rem;
        font-weight: 700;
        white-space: nowrap;
    }

    .severity-low { background: #dcfce7; color: #15803d; }
    .severity-medium { background: #e0f2fe; color: #0369a1; }
    .severity-high { background: #fef3c7; color: #a16207; }
    .severity-critical { background: #fee2e2; color: #b91c1c; }

    .status-open { background: #dbeafe; color: #1d4ed8; }
    .status-investigating { background: #fef3c7; color: #a16207; }
    .status-mitigated { background: #dcfce7; color: #15803d; }
    .status-closed { background: #e2e8f0; color: #334155; }

    .verdict-not-tested { background: #e2e8f0; color: #334155; }
    .verdict-failed { background: #fee2e2; color: #b91c1c; }
    .verdict-partially-effective { background: #fef3c7; color: #a16207; }
    .verdict-effective { background: #dcfce7; color: #15803d; }

    .incident-table-shell {
        border: 1px solid #dbe5f0;
        border-radius: 10px;
        padding: 0.4rem 0.5rem 0.5rem;
        background: #ffffff;
    }

    .incident-table th,
    .incident-table td {
        vertical-align: middle;
        padding: 0.7rem 0.58rem;
    }

    .incident-table .incident-summary {
        max-width: 260px;
        color: #334155;
    }

    .incident-chart-wrap {
        height: 280px;
    }

    .incident-form-label {
        font-size: 0.78rem;
        letter-spacing: 0.02em;
        text-transform: uppercase;
        font-weight: 700;
        color: #64748b;
    }

    .currency-tag {
        font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
        font-weight: 700;
    }
</style>
@endsection

@section('content_header')
<div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
    <div>
        <h2 class="page-title mb-1">Incident Management &amp; Loss Event</h2>
        <div class="text-muted">Pencatatan insiden operasional, temuan baru, dan rekapitulasi nominal kerugian per bulan dan per tahun.</div>
    </div>
    @if($canWrite)
        <div>
            <a href="{{ route('incidents.create') }}" class="btn btn-primary with-icon shadow-sm">
                <i class="fas fa-plus-circle me-1"></i> Catat Insiden / Temuan Baru
            </a>
        </div>
    @endif
</div>
@endsection

@section('content')
@php
    $severityClassMap = [
        'Low' => 'severity-low',
        'Medium' => 'severity-medium',
        'High' => 'severity-high',
        'Critical' => 'severity-critical',
    ];
    $statusClassMap = [
        'Open' => 'status-open',
        'Investigating' => 'status-investigating',
        'Mitigated' => 'status-mitigated',
        'Closed' => 'status-closed',
    ];
    $verdictClassMap = [
        'Not Tested' => 'verdict-not-tested',
        'Failed' => 'verdict-failed',
        'Partially Effective' => 'verdict-partially-effective',
        'Effective' => 'verdict-effective',
    ];
@endphp

{{-- Filter Toolbar Bar --}}
<div class="card mb-3 shadow-sm border-0" style="border-radius: 10px; background: #ffffff; border: 1px solid #e2e8f0;">
    <div class="card-body py-2 px-3">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
            <div class="d-flex align-items-center gap-2">
                <span class="badge bg-blue-lt px-2 py-1">
                    <i class="fas fa-filter me-1"></i> Filter Data
                </span>
                <span class="text-muted small">
                    Tahun: <strong class="text-dark">{{ $selectedYear }}</strong>
                    @if(request('affected_client'))
                        &bull; Client: <span class="badge bg-light text-primary border">{{ request('affected_client') }}</span>
                    @endif
                </span>
            </div>
            <form method="GET" action="{{ route('incidents.index') }}" class="d-flex align-items-center gap-2 flex-wrap m-0">
                <div class="input-group input-group-sm" style="width: auto;">
                    <span class="input-group-text bg-light text-muted fw-bold">
                        <i class="far fa-calendar-alt me-1"></i> Tahun
                    </span>
                    <select name="year" class="form-select form-select-sm no-search-select" data-no-search="true" onchange="this.form.submit()" style="width: 105px;">
                        @foreach($availableYears as $yr)
                            <option value="{{ $yr }}" @if($selectedYear === (int)$yr) selected @endif>{{ $yr }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="input-group input-group-sm" style="width: auto;">
                    <span class="input-group-text bg-light text-muted fw-bold">
                        <i class="fas fa-building me-1"></i> Client
                    </span>
                    <select name="affected_client" class="form-select form-select-sm no-search-select" data-no-search="true" onchange="this.form.submit()" style="min-width: 180px; max-width: 250px;">
                        <option value="">-- Semua Client --</option>
                        @foreach($clientOptions as $cOpt)
                            <option value="{{ $cOpt }}" @if($selectedClient === $cOpt) selected @endif>{{ $cOpt }}</option>
                        @endforeach
                    </select>
                </div>
                @if(request('affected_client'))
                    <a href="{{ route('incidents.index', ['year' => $selectedYear]) }}" class="btn btn-outline-danger btn-sm" title="Reset filter client">
                        <i class="fas fa-times me-1"></i> Reset
                    </a>
                @endif
            </form>
        </div>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fas fa-check-circle me-1"></i> {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

@if($errors->any())
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fas fa-exclamation-circle me-1"></i>
        <strong>Periksa kembali input Anda:</strong>
        <ul class="mb-0 mt-1 ps-3">
            @foreach($errors->all() as $err)
                <li>{{ $err }}</li>
            @endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

{{-- Operational Status KPI Strip --}}
<div class="incident-kpi">
    <div class="row g-2 mb-0">
        <div class="col-6 col-md-4 col-xl">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3>{{ $summary['total'] ?? 0 }}</h3>
                    <p>Total Incident</p>
                </div>
                <div class="icon"><i class="fas fa-exclamation-triangle"></i></div>
            </div>
        </div>
        <div class="col-6 col-md-4 col-xl">
            <div class="small-box bg-warning">
                <div class="inner">
                    <h3>{{ $summary['active'] ?? 0 }}</h3>
                    <p>Active Handling</p>
                </div>
                <div class="icon"><i class="fas fa-tools"></i></div>
            </div>
        </div>
        <div class="col-6 col-md-4 col-xl">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3>{{ $summary['closed'] ?? 0 }}</h3>
                    <p>Closed</p>
                </div>
                <div class="icon"><i class="fas fa-check-circle"></i></div>
            </div>
        </div>
        <div class="col-6 col-md-4 col-xl">
            <div class="small-box bg-purple" style="background-color: #6366f1 !important; color: #fff;">
                <div class="inner">
                    <h3>{{ $summary['unlinked_findings'] ?? 0 }}</h3>
                    <p>Temuan Baru (Unlinked)</p>
                </div>
                <div class="icon"><i class="fas fa-folder-plus"></i></div>
            </div>
        </div>
        <div class="col-6 col-md-4 col-xl">
            <div class="small-box bg-danger">
                <div class="inner">
                    <h3>{{ $summary['failed'] ?? 0 }}</h3>
                    <p>Mitigation Failed</p>
                </div>
                <div class="icon"><i class="fas fa-radiation-alt"></i></div>
            </div>
        </div>
        <div class="col-6 col-md-4 col-xl">
            <div class="small-box bg-secondary">
                <div class="inner">
                    <h3>{{ $summary['affected_risks'] ?? 0 }}</h3>
                    <p>Affected Risks</p>
                </div>
                <div class="icon"><i class="fas fa-link"></i></div>
            </div>
        </div>
    </div>
</div>

{{-- Financial Loss KPI Summary Strip --}}
<div class="card financial-kpi-card mb-3">
    <div class="card-body p-3">
        <div class="row g-3 align-items-center">
            <div class="col-12 col-md-3 border-end-md">
                <div class="text-uppercase text-muted fw-bold small">Total Kerugian Finansial</div>
                <div class="h2 mb-0 fw-bold text-danger currency-tag">
                    Rp {{ number_format($summary['total_loss_all_time'] ?? 0, 0, ',', '.') }}
                </div>
                <div class="small text-muted mt-1">{{ $summary['incidents_with_loss_count'] ?? 0 }} insiden berkerugian (all-time)</div>
            </div>
            <div class="col-6 col-md-3 border-end-md">
                <div class="text-uppercase text-muted fw-bold small">Kerugian Tahun Ini ({{ now()->year }})</div>
                <div class="h3 mb-0 fw-bold text-dark currency-tag">
                    Rp {{ number_format($summary['total_loss_this_year'] ?? 0, 0, ',', '.') }}
                </div>
                <div class="small text-muted mt-1">Akumulasi tahun berjalan</div>
            </div>
            <div class="col-6 col-md-3 border-end-md">
                <div class="text-uppercase text-muted fw-bold small">Kerugian Bulan Ini ({{ now()->translatedFormat('F') }})</div>
                <div class="h3 mb-0 fw-bold text-dark currency-tag">
                    Rp {{ number_format($summary['total_loss_this_month'] ?? 0, 0, ',', '.') }}
                </div>
                <div class="small text-muted mt-1">Akumulasi bulan berjalan</div>
            </div>
            <div class="col-12 col-md-3">
                <div class="text-uppercase text-muted fw-bold small">Rata-Rata Kerugian / Kasus</div>
                <div class="h3 mb-0 fw-bold text-primary currency-tag">
                    Rp {{ number_format($summary['avg_loss_per_incident'] ?? 0, 0, ',', '.') }}
                </div>
                <div class="small text-muted mt-1">Berdasarkan insiden yang mencatat kerugian</div>
            </div>
        </div>
    </div>
</div>

@if(!$canWrite)
    <div class="alert alert-warning">
        Superadmin hanya dapat melihat incident dashboard (read-only).
    </div>
@endif

{{-- Rekapitulasi & Visualisasi Kerugian Finansial --}}
<div class="card incident-card mb-3">
    <div class="card-header">
        <ul class="nav nav-tabs card-header-tabs" data-bs-toggle="tabs" role="tablist">
            <li class="nav-item" role="presentation">
                <a href="#tab-loss-chart" class="nav-link active" data-bs-toggle="tab" aria-selected="true" role="tab">
                    <i class="fas fa-chart-line me-1 text-primary"></i> Tren Kerugian &amp; Insiden
                </a>
            </li>
            <li class="nav-item" role="presentation">
                <a href="#tab-monthly-recap" class="nav-link" data-bs-toggle="tab" aria-selected="false" role="tab" tabindex="-1">
                    <i class="fas fa-calendar-alt me-1 text-warning"></i> Rekap Kerugian Bulanan ({{ $selectedYear }})
                </a>
            </li>
            <li class="nav-item" role="presentation">
                <a href="#tab-yearly-recap" class="nav-link" data-bs-toggle="tab" aria-selected="false" role="tab" tabindex="-1">
                    <i class="fas fa-chart-bar me-1 text-success"></i> Rekap Kerugian Tahunan
                </a>
            </li>
        </ul>
    </div>
    <div class="card-body">
        <div class="tab-content">
            {{-- TAB 1: CHART TREN KERUGIAN & INSIDEN --}}
            <div class="tab-pane active show" id="tab-loss-chart" role="tabpanel">
                <div class="row g-3">
                    <div class="col-lg-7">
                        <div class="fw-bold mb-2 text-dark"><i class="fas fa-money-bill-wave text-danger me-1"></i> Tren Nominal Kerugian Finansial (6 Bulan Terakhir)</div>
                        <div class="incident-chart-wrap">
                            <canvas id="incidentLossChart"></canvas>
                        </div>
                    </div>
                    <div class="col-lg-5">
                        <div class="fw-bold mb-2 text-dark"><i class="fas fa-chart-area text-primary me-1"></i> Tren Frekuensi Insiden (6 Bulan Terakhir)</div>
                        <div class="incident-chart-wrap">
                            <canvas id="incidentTrendChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            {{-- TAB 2: REKAP BULANAN TAHUN TERPILIH --}}
            <div class="tab-pane" id="tab-monthly-recap" role="tabpanel">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <h4 class="mb-0 fw-bold">Rekapitulasi Kerugian Finansial Per Bulan - Tahun {{ $selectedYear }}</h4>
                        <div class="text-muted small">Rincian kejadian dan nominal kerugian untuk tiap bulan pada tahun {{ $selectedYear }}.</div>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-sm table-bordered table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 180px;">Bulan</th>
                                <th class="text-center" style="width: 130px;">Jumlah Insiden</th>
                                <th class="text-center" style="width: 170px;">Insiden Berkerugian</th>
                                <th class="text-end" style="width: 220px;">Total Kerugian (Rp)</th>
                                <th class="text-end" style="width: 200px;">Rata-Rata Kerugian (Rp)</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $totalYearIncidents = 0;
                                $totalYearLossIncidents = 0;
                                $totalYearLossAmount = 0;
                            @endphp
                            @foreach($monthlyLossRecapForYear as $mRow)
                                @php
                                    $totalYearIncidents += $mRow['incident_count'];
                                    $totalYearLossIncidents += $mRow['loss_incidents'];
                                    $totalYearLossAmount += $mRow['total_loss'];
                                @endphp
                                <tr @if($mRow['total_loss'] > 0) class="table-warning bg-opacity-25" @endif>
                                    <td class="fw-semibold">{{ $mRow['month_name'] }} {{ $selectedYear }}</td>
                                    <td class="text-center">{{ $mRow['incident_count'] }}</td>
                                    <td class="text-center">{{ $mRow['loss_incidents'] }}</td>
                                    <td class="text-end fw-bold {{ $mRow['total_loss'] > 0 ? 'text-danger' : 'text-muted' }} currency-tag">
                                        Rp {{ number_format($mRow['total_loss'], 0, ',', '.') }}
                                    </td>
                                    <td class="text-end text-muted currency-tag">
                                        Rp {{ number_format($mRow['avg_loss'], 0, ',', '.') }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="table-light fw-bold">
                            <tr>
                                <th>Total Tahun {{ $selectedYear }}</th>
                                <th class="text-center">{{ $totalYearIncidents }}</th>
                                <th class="text-center">{{ $totalYearLossIncidents }}</th>
                                <th class="text-end text-danger currency-tag">Rp {{ number_format($totalYearLossAmount, 0, ',', '.') }}</th>
                                <th class="text-end text-primary currency-tag">
                                    Rp {{ number_format($totalYearLossIncidents > 0 ? ($totalYearLossAmount / $totalYearLossIncidents) : 0, 0, ',', '.') }}
                                </th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            {{-- TAB 3: REKAP TAHUNAN --}}
            <div class="tab-pane" id="tab-yearly-recap" role="tabpanel">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <h4 class="mb-0 fw-bold">Rekapitulasi Kerugian Finansial Per Tahun</h4>
                        <div class="text-muted small">Perbandingan total kerugian finansial antar tahun untuk pelaporan manajemen &amp; audit.</div>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-sm table-bordered table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 150px;">Tahun</th>
                                <th class="text-center" style="width: 150px;">Total Insiden</th>
                                <th class="text-center" style="width: 180px;">Insiden Berkerugian</th>
                                <th class="text-end" style="width: 250px;">Total Kerugian (Rp)</th>
                                <th class="text-end" style="width: 220px;">Rata-Rata Kerugian / Kasus</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $grandTotalIncidents = 0;
                                $grandTotalLossIncidents = 0;
                                $grandTotalLossAmount = 0;
                            @endphp
                            @forelse($lossRecapByYear as $yRow)
                                @php
                                    $grandTotalIncidents += $yRow['total_incidents'];
                                    $grandTotalLossIncidents += $yRow['loss_incidents'];
                                    $grandTotalLossAmount += $yRow['total_loss'];
                                @endphp
                                <tr>
                                    <td class="fw-bold">
                                        <a href="{{ route('incidents.index', ['year' => $yRow['year']]) }}" class="text-decoration-none">
                                            {{ $yRow['year'] }} @if($yRow['year'] === (int)now()->year) <span class="badge bg-blue-lt ms-1">Tahun Berjalan</span> @endif
                                        </a>
                                    </td>
                                    <td class="text-center">{{ $yRow['total_incidents'] }}</td>
                                    <td class="text-center">{{ $yRow['loss_incidents'] }}</td>
                                    <td class="text-end fw-bold text-danger currency-tag">
                                        Rp {{ number_format($yRow['total_loss'], 0, ',', '.') }}
                                    </td>
                                    <td class="text-end text-muted currency-tag">
                                        Rp {{ number_format($yRow['avg_loss'], 0, ',', '.') }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-3 text-muted">Belum ada data rekap tahunan.</td>
                                </tr>
                            @endforelse
                        </tbody>
                        <tfoot class="table-light fw-bold">
                            <tr>
                                <th>Grand Total (All-Time)</th>
                                <th class="text-center">{{ $grandTotalIncidents }}</th>
                                <th class="text-center">{{ $grandTotalLossIncidents }}</th>
                                <th class="text-end text-danger currency-tag">Rp {{ number_format($grandTotalLossAmount, 0, ',', '.') }}</th>
                                <th class="text-end text-primary currency-tag">
                                    Rp {{ number_format($grandTotalLossIncidents > 0 ? ($grandTotalLossAmount / $grandTotalLossIncidents) : 0, 0, ',', '.') }}
                                </th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mb-3">
    <div class="col-lg-12">
        <div class="card incident-card h-100">
            <div class="card-header">
                <h3 class="card-title mb-0">Mitigation Verdict Distribution</h3>
            </div>
            <div class="card-body">
                <div class="incident-chart-wrap" style="max-height: 220px;">
                    <canvas id="verdictChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card incident-card">
    <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div>
            <h3 class="card-title mb-0">Incident &amp; Finding Register</h3>
            <div class="text-muted small">Daftar lengkap seluruh insiden dan temuan operasional beserta nominal kerugian.</div>
        </div>
        <div class="d-flex align-items-center gap-2">
            <span class="badge bg-blue-lt">{{ $incidents->count() }} rows</span>
            @if($canWrite)
                <a href="{{ route('incidents.create') }}" class="btn btn-primary btn-xs with-icon">
                    <i class="fas fa-plus-circle me-1"></i> Catat Insiden
                </a>
            @endif
        </div>
    </div>
    <div class="card-body">
        <div class="incident-table-shell">
            <div class="table-responsive">
                <table id="incidentTable" class="table table-sm table-vcenter table-hover mb-0 incident-table" data-has-data="{{ $incidents->isNotEmpty() ? '1' : '0' }}">
                    <thead class="table-light">
                        <tr>
                            <th style="min-width:130px;">Incident ID</th>
                            <th style="min-width:130px;">Occurred</th>
                            <th style="min-width:200px;">Risk Link / Finding</th>
                            <th style="min-width:140px;">Client Terdampak</th>
                            <th style="min-width:140px;" class="text-end">Nominal Kerugian</th>
                            <th style="width:90px;">Severity</th>
                            <th style="width:110px;">Status</th>
                            <th style="width:140px;">Mitigation Verdict</th>
                            <th style="min-width:120px;">Reporter</th>
                            <th style="min-width:120px;">Last Update</th>
                            <th style="min-width:240px;">Summary</th>
                            @if($canWrite)
                                <th style="min-width:130px;">Action</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($incidents as $incident)
                            @php
                                $severityClass = $severityClassMap[$incident->severity] ?? 'severity-medium';
                                $statusClass = $statusClassMap[$incident->status] ?? 'status-open';
                                $verdictClass = $verdictClassMap[$incident->mitigation_verdict] ?? 'verdict-not-tested';
                                $reporterName = optional($incident->reporter)->name ?? '-';
                                $updatedBy = optional($incident->updater)->name ?? '-';
                            @endphp
                            <tr>
                                <td class="fw-bold">
                                    {{ $incident->incident_code }}
                                    <div class="mt-1">
                                        <a href="{{ route('incidents.learning.show', $incident->id) }}" class="btn btn-outline-primary btn-xs">Learning &amp; CAPA</a>
                                    </div>
                                </td>
                                <td data-order="{{ optional($incident->occurred_at)->timestamp ?? 0 }}">
                                    {{ optional($incident->occurred_at)->format('d M Y H:i') ?? '-' }}
                                </td>
                                <td>
                                    @if($incident->risk)
                                        <div class="fw-semibold text-primary">
                                            <a href="{{ route('risks.show', $incident->risk->id) }}" class="text-decoration-none">
                                                <i class="fas fa-link me-1"></i>{{ $incident->risk->risk_id }}
                                            </a>
                                        </div>
                                        <div class="small text-muted text-truncate" style="max-width: 200px;">{{ $incident->risk->risk_title }}</div>
                                    @else
                                        <span class="badge bg-purple-lt mb-1">
                                            <i class="fas fa-sparkles me-1"></i>Temuan Baru
                                        </span>
                                        <div class="small text-muted fw-semibold">
                                            {{ $incident->risk_owner ?: 'Unlinked' }}
                                        </div>
                                    @endif
                                </td>
                                <td>
                                    @if(!empty($incident->affected_client))
                                        <span class="badge bg-blue-lt fw-bold">
                                            <i class="fas fa-building me-1"></i>{{ $incident->affected_client }}
                                        </span>
                                    @else
                                        <span class="text-muted small">-</span>
                                    @endif
                                </td>
                                <td class="text-end" data-order="{{ (float)($incident->loss_amount ?? 0) }}">
                                    @if((float)($incident->loss_amount ?? 0) > 0)
                                        <span class="fw-bold text-danger currency-tag">
                                            Rp {{ number_format($incident->loss_amount, 0, ',', '.') }}
                                        </span>
                                    @else
                                        <span class="text-muted currency-tag">Rp 0</span>
                                    @endif
                                </td>
                                <td><span class="severity-badge {{ $severityClass }}">{{ $incident->severity }}</span></td>
                                <td><span class="status-badge {{ $statusClass }}">{{ $incident->status }}</span></td>
                                <td><span class="verdict-badge {{ $verdictClass }}">{{ $incident->mitigation_verdict }}</span></td>
                                <td>{{ $reporterName }}</td>
                                <td>
                                    <div>{{ optional($incident->updated_at)->format('d M Y H:i') ?? '-' }}</div>
                                    <div class="small text-muted">{{ $updatedBy }}</div>
                                </td>
                                <td class="incident-summary">
                                    <div class="fw-semibold">{{ $incident->title }}</div>
                                    <div class="small text-muted">{{ \Illuminate\Support\Str::limit((string) $incident->incident_description, 110) }}</div>
                                </td>
                                @if($canWrite)
                                    <td>
                                        <div class="d-flex flex-column gap-1">
                                            <a
                                                href="{{ route('incidents.edit', $incident->id) }}"
                                                class="btn btn-warning btn-xs with-icon justify-content-center"
                                            >
                                                <i class="fas fa-edit"></i> Edit
                                            </a>

                                            @if(!$incident->risk_id)
                                                <a
                                                    href="{{ route('risks.create', ['from_incident' => $incident->id]) }}"
                                                    class="btn btn-outline-success btn-xs with-icon justify-content-center"
                                                    title="Daftarkan temuan ini menjadi Risk Baru di Risk Register"
                                                >
                                                    <i class="fas fa-plus-circle"></i> Buat Risk
                                                </a>
                                            @endif

                                            <form method="POST" action="{{ route('incidents.destroy', $incident->id) }}">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger btn-xs with-icon justify-content-center w-100" onclick="return confirm('Delete incident {{ $incident->incident_code }}?')">
                                                    <i class="fas fa-trash"></i> Delete
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                @endif
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ $canWrite ? 11 : 10 }}" class="text-center py-4 text-muted">
                                    Belum ada incident yang tercatat.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                    @if($incidents->isNotEmpty())
                        <tfoot class="table-light">
                            <tr>
                                <th colspan="3" class="text-end">Total Kerugian (Dari {{ $incidents->count() }} Data):</th>
                                <th class="text-end text-danger fw-bold currency-tag">
                                    Rp {{ number_format($incidents->sum('loss_amount'), 0, ',', '.') }}
                                </th>
                                <th colspan="{{ $canWrite ? 7 : 6 }}"></th>
                            </tr>
                        </tfoot>
                    @endif
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@section('js')
<script src="{{ asset('vendor/datatables/js/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('vendor/datatables/js/dataTables.bootstrap5.min.js') }}"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
<script>
    (function () {
        const table = $('#incidentTable');
        const hasData = table.data('has-data') === 1 || table.data('has-data') === '1';
        if (table.length && hasData) {
            table.DataTable({
                order: [[1, 'desc']],
                pageLength: 10,
                lengthMenu: [[10, 25, 50, -1], [10, 25, 50, 'All']],
                autoWidth: false,
            });
        }

        const trendCtx = document.getElementById('incidentTrendChart');
        const lossCtx = document.getElementById('incidentLossChart');
        const verdictCtx = document.getElementById('verdictChart');

        const monthlyTrend = @json($monthlyTrend ?? ['labels' => [], 'values' => []]);
        const monthlyLossTrend = @json($monthlyLossTrend ?? ['labels' => [], 'values' => []]);
        const verdictDistribution = @json($verdictDistribution ?? ['labels' => [], 'values' => []]);

        // Monthly Loss Trend Chart
        if (lossCtx && Array.isArray(monthlyLossTrend.labels) && monthlyLossTrend.labels.length > 0) {
            new Chart(lossCtx, {
                type: 'bar',
                data: {
                    labels: monthlyLossTrend.labels,
                    datasets: [{
                        label: 'Total Kerugian (Rp)',
                        data: monthlyLossTrend.values,
                        borderColor: '#dc2626',
                        backgroundColor: 'rgba(239, 68, 68, 0.45)',
                        borderWidth: 1.5,
                        borderRadius: 4,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        tooltip: {
                            callbacks: {
                                label: function (context) {
                                    const val = context.parsed.y || 0;
                                    return ' Kerugian: Rp ' + new Intl.NumberFormat('id-ID').format(val);
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function (value) {
                                    if (value >= 1000000000) {
                                        return 'Rp ' + (value / 1000000000).toFixed(1) + ' M';
                                    }
                                    if (value >= 1000000) {
                                        return 'Rp ' + (value / 1000000).toFixed(0) + ' Jt';
                                    }
                                    if (value >= 1000) {
                                        return 'Rp ' + (value / 1000).toFixed(0) + ' Rb';
                                    }
                                    return 'Rp ' + value;
                                }
                            }
                        }
                    }
                }
            });
        }

        // Monthly Incident Count Chart
        if (trendCtx && Array.isArray(monthlyTrend.labels) && monthlyTrend.labels.length > 0) {
            new Chart(trendCtx, {
                type: 'line',
                data: {
                    labels: monthlyTrend.labels,
                    datasets: [{
                        label: 'Jumlah Insiden',
                        data: monthlyTrend.values,
                        borderColor: '#2563eb',
                        backgroundColor: 'rgba(37, 99, 235, 0.18)',
                        borderWidth: 2,
                        pointRadius: 4,
                        tension: 0.25,
                        fill: true,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                precision: 0
                            }
                        }
                    }
                }
            });
        }

        // Verdict Doughnut Chart
        if (verdictCtx && Array.isArray(verdictDistribution.labels) && verdictDistribution.labels.length > 0) {
            new Chart(verdictCtx, {
                type: 'doughnut',
                data: {
                    labels: verdictDistribution.labels,
                    datasets: [{
                        data: verdictDistribution.values,
                        backgroundColor: ['#94a3b8', '#ef4444', '#f59e0b', '#22c55e'],
                        borderColor: '#ffffff',
                        borderWidth: 2
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom'
                        }
                    }
                }
            });
        }
    })();
</script>
@endsection
