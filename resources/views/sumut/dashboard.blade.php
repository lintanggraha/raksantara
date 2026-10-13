@extends('layouts.tabler')

@section('title', 'Executive Dashboard Monitoring - Sumut 27 Point')

@section('content')
<div class="container-xl">
    <!-- Page Header -->
    <div class="page-header d-print-none mb-3">
        <div class="row align-items-center">
            <div class="col">
                <div class="page-pretitle d-flex align-items-center gap-2">
                    <span class="badge bg-primary-lt fs-6 fw-semibold"><i class="fas fa-landmark me-1"></i>Bank Sumut</span>
                    <span class="text-muted fs-7">Langkah Pencegahan & Penguatan OJK</span>
                    <span class="badge bg-green text-green-fg fs-7 fw-bold"><i class="fas fa-check-circle me-1"></i>Status: 100% Bukti Ada</span>
                </div>
                <h2 class="page-title text-dark fw-bold mt-1">
                    <i class="fas fa-shield-virus text-primary me-2"></i>Executive Dashboard: Sumut 27 Point
                </h2>
                <div class="text-muted small mt-1">
                    Monitoring komprehensif 27 action plan penguatan infrastruktur, validasi database CBS, EDR, PAM, Firewall/WAF, dan arsip bukti fisik lokal.
                </div>
            </div>
            <div class="col-auto ms-auto d-print-none d-flex gap-2">
                <a href="{{ route('sumut.items') }}" class="btn btn-primary d-inline-flex align-items-center gap-1 shadow-sm">
                    <i class="fas fa-list-check"></i>
                    <span>Buka 27 Point Checklist</span>
                </a>
                <a href="{{ route('sumut.export-excel') }}" class="btn btn-outline-success d-inline-flex align-items-center gap-1 shadow-sm">
                    <i class="fas fa-file-excel"></i>
                    <span>Export Excel Rekap</span>
                </a>
                @if(auth()->user()->isSuperadmin())
                <form action="{{ route('sumut.sync-excel') }}" method="POST" class="d-inline" onsubmit="return confirm('Sinkronisasi ulang 27 point dan scan folder evidence fisik?');">
                    @csrf
                    <button type="submit" class="btn btn-outline-secondary d-inline-flex align-items-center gap-1" title="Sinkronisasi Ulang dari Excel & Folder">
                        <i class="fas fa-sync-alt"></i>
                        <span>Sync Folder</span>
                    </button>
                </form>
                @endif
            </div>
        </div>
    </div>

    <!-- Alert Success / Error Notifications -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
            <div class="d-flex">
                <div><i class="fas fa-check-circle fs-3 me-2"></i></div>
                <div>
                    <h4 class="alert-title mb-0">Sukses!</h4>
                    <div class="text-muted small">{{ session('success') }}</div>
                </div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
            <div class="d-flex">
                <div><i class="fas fa-exclamation-triangle fs-3 me-2"></i></div>
                <div>
                    <h4 class="alert-title mb-0">Perhatian</h4>
                    <div class="text-muted small">{{ session('error') }}</div>
                </div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Navigation Sub-Tabs -->
    <div class="mb-3">
        <ul class="nav nav-bordered border-bottom">
            <li class="nav-item">
                <a class="nav-link active fw-bold" href="{{ route('sumut.dashboard') }}">
                    <i class="fas fa-chart-pie me-1 text-primary"></i> Executive Dashboard
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link text-muted" href="{{ route('sumut.items') }}">
                    <i class="fas fa-folder-open me-1"></i> 27 Point Checklist & Evidence Explorer
                </a>
            </li>
        </ul>
    </div>

    <!-- Top Summary KPI Cards -->
    <div class="row row-deck row-cards mb-3">
        <!-- Total 27 Points -->
        <div class="col-sm-6 col-lg-3">
            <div class="card card-sm border-0 shadow-sm rounded-3">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-auto">
                            <span class="avatar bg-primary-lt text-primary rounded-3">
                                <i class="fas fa-tasks fs-2"></i>
                            </span>
                        </div>
                        <div class="col">
                            <div class="font-weight-medium text-muted text-uppercase fs-7 fw-bold">Total Point Pencegahan dan Penguatan</div>
                            <div class="h1 mb-0 fw-bold text-dark">{{ $metrics['total_items'] }} <span class="fs-4 text-muted fw-normal">Point</span></div>
                            <div class="text-success small mt-1">
                                <i class="fas fa-check-double me-1"></i>100% Terdaftar Lengkap
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Bukti Ada Status -->
        <div class="col-sm-6 col-lg-3">
            <div class="card card-sm border-0 shadow-sm rounded-3 border-start border-success border-4">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-auto">
                            <span class="avatar bg-green-lt text-green rounded-3">
                                <i class="fas fa-check-circle fs-2"></i>
                            </span>
                        </div>
                        <div class="col">
                            <div class="font-weight-medium text-muted text-uppercase fs-7 fw-bold">Status Bukti: Ada</div>
                            <div class="h1 mb-0 fw-bold text-success">{{ $metrics['ada_count'] }} <span class="fs-4 text-muted fw-normal">Point</span></div>
                            <div class="text-muted small mt-1">
                                Sesuai & Memiliki Arsip Folder
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Total Files & Storage -->
        <div class="col-sm-6 col-lg-3">
            <div class="card card-sm border-0 shadow-sm rounded-3">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-auto">
                            <span class="avatar bg-azure-lt text-azure rounded-3">
                                <i class="fas fa-file-invoice fs-2"></i>
                            </span>
                        </div>
                        <div class="col">
                            <div class="font-weight-medium text-muted text-uppercase fs-7 fw-bold">Bukti Fisik Tersedia</div>
                            <div class="h1 mb-0 fw-bold text-dark">{{ $metrics['total_evidences'] }} <span class="fs-4 text-muted fw-normal">File</span></div>
                            <div class="text-muted small mt-1">
                                <i class="fas fa-database me-1"></i>Ukuran: <strong>{{ $metrics['total_size_formatted'] }}</strong>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Average Bank Sumut Progress -->
        <div class="col-sm-6 col-lg-3">
            <div class="card card-sm border-0 shadow-sm rounded-3 border-start border-primary border-4">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-auto">
                            <span class="avatar bg-blue-lt text-blue rounded-3">
                                <i class="fas fa-percent fs-2"></i>
                            </span>
                        </div>
                        <div class="col">
                            <div class="font-weight-medium text-muted text-uppercase fs-7 fw-bold">Rata-rata Progress Bank Sumut</div>
                            <div class="h1 mb-0 fw-bold text-primary">{{ $metrics['avg_progress'] }}%</div>
                            <div class="text-muted small mt-1">
                                <span class="badge bg-green-lt text-green fs-8">21 Done (100%)</span> · <span class="badge bg-yellow-lt text-yellow fs-8">6 On Progress</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="row row-cards mb-4">
        <!-- Donut Status Bukti Fisik -->
        <div class="col-lg-4">
            <div class="card shadow-sm border-0 h-100 rounded-3">
                <div class="card-header bg-transparent border-bottom">
                    <h3 class="card-title fw-bold text-dark">
                        <i class="fas fa-chart-pie me-2 text-primary"></i>Status Keberadaan Bukti
                    </h3>
                </div>
                <div class="card-body d-flex flex-column align-items-center justify-content-center">
                    <div id="chart-status-donut" style="min-height: 250px; width: 100%;"></div>
                    <div class="d-flex justify-content-around w-100 mt-3 pt-2 border-top text-center">
                        <div>
                            <span class="badge bg-success me-1"></span>
                            <span class="small fw-semibold text-dark">Ada: {{ $metrics['ada_count'] }} (100%)</span>
                        </div>
                        <div>
                            <span class="badge bg-danger me-1"></span>
                            <span class="small fw-semibold text-muted">Tidak Ada: {{ $metrics['tidak_ada_count'] }} (0%)</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Bar Kluster Kategori Penguatan -->
        <div class="col-lg-8">
            <div class="card shadow-sm border-0 h-100 rounded-3">
                <div class="card-header bg-transparent border-bottom d-flex justify-content-between align-items-center">
                    <div>
                        <h3 class="card-title fw-bold text-dark mb-0">
                            <i class="fas fa-layer-group me-2 text-primary"></i>Distribusi 7 Kluster Penguatan Keamanan
                        </h3>
                        <div class="text-muted small mt-1">
                            Jumlah point action plan OJK per kluster keamanan (Semua 27 point terverifikasi 100% Bukti Fisik Ada).
                        </div>
                    </div>
                    <span class="badge bg-green-lt text-green fs-7 fw-bold">
                        <i class="fas fa-check-double me-1"></i>100% Bukti Ada
                    </span>
                </div>
                <div class="card-body">
                    <div id="chart-categories-bar" style="min-height: 290px; width: 100%;"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- ACTION REQUIRED: Gap Analysis 6 Point Menuju 100% -->
    <div class="card shadow-sm border-0 rounded-3 mb-4 border-start border-warning border-4">
        <div class="card-header bg-transparent border-bottom py-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div>
                <div class="d-flex align-items-center gap-2">
                    <span class="badge bg-warning text-dark fw-bold px-2 py-1 fs-7">
                        <i class="fas fa-exclamation-triangle me-1"></i>ACTION REQUIRED
                    </span>
                    <h3 class="card-title fw-bold text-dark mb-0">
                        Gap Analysis: 6 Point Belum 100% &amp; Berkas Evidence yang Harus Dikumpulkan
                    </h3>
                </div>
                <div class="text-muted small mt-1">
                    Rata-rata progres saat ini <strong>{{ $metrics['avg_progress'] }}%</strong> (21 Point Selesai 100%). Pemenuhan berkas fisik dan dokumen teknis pada <strong>6 point di bawah ini</strong> diperlukan untuk menuntaskan capaian Bank Sumut menjadi <strong>100.0%</strong>.
                </div>
            </div>
            <div class="d-flex align-items-center gap-2">
                <span class="badge bg-yellow-lt text-yellow fs-7 fw-semibold">
                    <i class="fas fa-tasks me-1"></i>6 Point On Progress
                </span>
                <a href="{{ route('sumut.items', ['progress_status' => 'on_progress']) }}" class="btn btn-sm btn-outline-warning">
                    <i class="fas fa-filter me-1"></i>Filter 6 Point Ini di Checklist
                </a>
            </div>
        </div>
        <div class="card-body p-3">
            <div class="row g-3">
                @foreach($incompleteItems as $item)
                <div class="col-lg-6">
                    <div class="card border rounded-3 h-100 bg-light-subtle shadow-none">
                        <div class="card-header bg-white py-2 px-3 border-bottom d-flex justify-content-between align-items-center flex-wrap gap-2">
                            <div class="d-flex align-items-center gap-2 text-truncate">
                                <span class="badge bg-dark text-white fw-bold fs-7">Point #{{ $item->folder_number }}</span>
                                <span class="badge bg-{{ $item->category_color }}-lt text-{{ $item->category_color }} fs-8 text-truncate" style="max-width: 170px;">
                                    {{ $item->category }}
                                </span>
                                <span class="badge bg-secondary-lt fs-8 text-truncate">
                                    <i class="fas fa-user-shield me-1"></i>{{ $item->pic }}
                                </span>
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                <span class="badge bg-warning-lt text-warning fw-bold fs-7">
                                    Progress BS: {{ $item->progress_bs }}
                                </span>
                            </div>
                        </div>
                        <div class="card-body p-3 d-flex flex-column justify-content-between">
                            <div>
                                <!-- Clean Title -->
                                <h4 class="fw-bold text-dark mb-1 fs-6">
                                    {{ $item->clean_title }}
                                </h4>
                                
                                <!-- Progress Bar -->
                                <div class="d-flex align-items-center gap-2 mb-3">
                                    <div class="progress progress-sm flex-fill">
                                        <div class="progress-bar bg-warning" style="width: {{ $item->progress_percent }}%"></div>
                                    </div>
                                    <span class="fs-8 fw-bold text-muted">{{ $item->progress_percent }}%</span>
                                    @if($item->target_date)
                                    <span class="badge bg-light text-muted border fs-8 ms-1">
                                        <i class="fas fa-calendar-alt me-1"></i>{{ $item->target_date }}
                                    </span>
                                    @endif
                                </div>

                                <!-- Bukti Fisik yang Sudah Ada -->
                                <div class="mb-2 p-2 rounded bg-white border small">
                                    <div class="text-muted fs-8 fw-bold text-uppercase d-flex justify-content-between">
                                        <span><i class="fas fa-check-circle text-success me-1"></i>Bukti Fisik yang Sudah Ada:</span>
                                        <span class="badge bg-green-lt text-green fs-8">{{ $item->evidences->count() }} File Terlampir</span>
                                    </div>
                                    <div class="mt-1 text-dark text-truncate" style="max-width: 100%;" title="{{ $item->evidences->pluck('file_name')->implode(', ') }}">
                                        @forelse($item->evidences->take(3) as $ev)
                                            <span class="badge bg-light text-secondary border fs-8 me-1">{{ Str::limit($ev->file_name, 28) }}</span>
                                        @empty
                                            <span class="text-muted fst-italic">Belum ada file terlampir</span>
                                        @endforelse
                                        @if($item->evidences->count() > 3)
                                            <span class="badge bg-secondary-lt fs-8">+{{ $item->evidences->count() - 3 }} lainnya</span>
                                        @endif
                                    </div>
                                </div>

                                <!-- Hambatan / Catatan Bank Sumut -->
                                @if($item->keterangan || $item->follow_up)
                                <div class="mb-2 p-2 rounded bg-red-lt small border border-danger-subtle">
                                    <div class="fw-bold text-danger fs-8 text-uppercase">
                                        <i class="fas fa-info-circle me-1"></i>Catatan Bank Sumut / Status Lapangan:
                                    </div>
                                    <div class="text-dark mt-1 fs-8 lh-base">
                                        {{ Str::limit($item->follow_up ?: $item->keterangan, 150) }}
                                    </div>
                                </div>
                                @endif

                                <!-- Evidence yang Kurang & Wajib Dikumpulkan -->
                                <div class="p-2 rounded bg-yellow-lt small border border-warning-subtle">
                                    <div class="fw-bold text-dark fs-8 text-uppercase d-flex align-items-center gap-1">
                                        <i class="fas fa-clipboard-list text-warning"></i>
                                        <span>Evidence yang Kurang &amp; Wajib Dikumpulkan:</span>
                                    </div>
                                    <ul class="list-unstyled mb-0 mt-1 d-flex flex-column gap-1">
                                        @foreach($item->missing_evidence_list as $gap)
                                        <li class="d-flex align-items-start gap-1 fs-8 text-dark">
                                            <i class="fas fa-circle-exclamation text-warning mt-1 flex-shrink-0" style="font-size: 10px;"></i>
                                            <span>{{ $gap }}</span>
                                        </li>
                                        @endforeach
                                    </ul>
                                </div>
                            </div>

                            <!-- Footer Action -->
                            <div class="mt-3 pt-2 border-top d-flex justify-content-between align-items-center">
                                <span class="fs-8 text-muted">Folder Arsip: <code>/{{ $item->folder_number }}</code></span>
                                <a href="{{ route('sumut.items', ['search' => $item->point_number]) }}" class="btn btn-xs btn-primary d-inline-flex align-items-center gap-1">
                                    <i class="fas fa-folder-open"></i>
                                    <span>Buka Point &amp; Arsip Bukti</span>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- PIC Breakdown & Security Highlights -->
    <div class="row row-cards mb-4">
        <!-- Kluster Details Card -->
        <div class="col-lg-7">
            <div class="card shadow-sm border-0 rounded-3 h-100">
                <div class="card-header bg-transparent border-bottom">
                    <h3 class="card-title fw-bold text-dark">
                        <i class="fas fa-cubes me-2 text-primary"></i>Ringkasan Kluster & Rata-rata Progres
                    </h3>
                </div>
                <div class="table-responsive">
                    <table class="table table-vcenter table-hover card-table">
                        <thead class="table-light">
                            <tr>
                                <th>Kluster Keamanan</th>
                                <th class="text-center">Jumlah Point</th>
                                <th class="text-center">Status Bukti</th>
                                <th class="text-center">Jml Bukti Fisik</th>
                                <th class="text-center">Progres Bank Sumut</th>
                                <th class="text-end">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($metrics['categories'] as $cat)
                            <tr>
                                <td class="fw-semibold text-dark">
                                    <i class="fas fa-folder me-2 text-primary"></i>{{ $cat['name'] }}
                                </td>
                                <td class="text-center fw-bold">{{ $cat['total'] }}</td>
                                <td class="text-center">
                                    <span class="badge bg-green-lt text-green border-green">
                                        <i class="fas fa-check-circle me-1"></i>{{ $cat['ada'] }}/{{ $cat['total'] }} Ada
                                    </span>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-secondary-lt fw-normal">{{ $cat['evidences_count'] }} files</span>
                                </td>
                                <td class="text-center">
                                    <div class="d-flex align-items-center justify-content-center gap-2">
                                        <div class="progress progress-sm" style="width: 70px;">
                                            <div class="progress-bar {{ $cat['avg_progress'] >= 100 ? 'bg-success' : 'bg-warning' }}" style="width: {{ $cat['avg_progress'] }}%"></div>
                                        </div>
                                        <span class="small fw-bold">{{ $cat['avg_progress'] }}%</span>
                                    </div>
                                </td>
                                <td class="text-end">
                                    <a href="{{ route('sumut.items', ['category' => $cat['name']]) }}" class="btn btn-xs btn-outline-primary" title="Buka Point di Kluster Ini">
                                        <i class="fas fa-arrow-right"></i>
                                    </a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- PIC Distribution Card -->
        <div class="col-lg-5">
            <div class="card shadow-sm border-0 rounded-3 h-100">
                <div class="card-header bg-transparent border-bottom d-flex justify-content-between align-items-center">
                    <div>
                        <h3 class="card-title fw-bold text-dark mb-0">
                            <i class="fas fa-users-cog me-2 text-primary"></i>Distribusi PIC Pelaksana
                        </h3>
                        <div class="text-muted small mt-1">Pembagian tanggung jawab penyelesaian point penguatan OJK.</div>
                    </div>
                    <span class="badge bg-primary-lt">Total 27 Point</span>
                </div>
                <div class="card-body">
                    <!-- Donut Chart of PIC Share -->
                    <div id="chart-pic-donut" style="min-height: 240px; width: 100%;"></div>

                    <!-- Ranked Leaderboard / Progress List -->
                    <div class="mt-3 pt-3 border-top">
                        <div class="d-flex justify-content-between text-muted fs-8 fw-bold text-uppercase mb-2 px-1">
                            <span>Tim Pelaksana</span>
                            <span>Porsi &amp; Status</span>
                        </div>
                        <div class="d-flex flex-column gap-2" style="max-height: 280px; overflow-y: auto;">
                            @foreach($metrics['pic_stats'] as $idx => $pic)
                            <div class="p-2 border rounded-2 bg-light-subtle d-flex align-items-center justify-content-between gap-2">
                                <div class="d-flex align-items-center gap-2 text-truncate" style="min-width: 0;">
                                    <span class="badge bg-secondary-lt fw-bold fs-8 flex-shrink-0" style="width: 26px;">#{{ $idx + 1 }}</span>
                                    <div class="text-truncate">
                                        <a href="{{ route('sumut.items', ['pic' => $pic['pic']]) }}" class="fw-bold text-dark text-decoration-none text-truncate d-block small" title="Filter point {{ $pic['pic'] }}">
                                            {{ $pic['pic'] }}
                                        </a>
                                        <div class="d-flex align-items-center gap-2 mt-1">
                                            <div class="progress progress-xs" style="width: 90px;">
                                                <div class="progress-bar bg-primary" style="width: {{ $pic['share_percent'] }}%"></div>
                                            </div>
                                            <span class="fs-8 text-muted">{{ $pic['share_percent'] }}%</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="d-flex align-items-center gap-1 flex-shrink-0">
                                    <span class="badge bg-primary-lt fw-bold fs-8">{{ $pic['total'] }} Point</span>
                                    <span class="badge bg-green-lt text-green fs-8" title="100% Bukti Fisik Ada"><i class="fas fa-check"></i> Ada</span>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick 27 Point Matrix Table -->
    <div class="card shadow-sm border-0 rounded-3 mb-4">
        <div class="card-header bg-transparent border-bottom d-flex justify-content-between align-items-center">
            <div>
                <h3 class="card-title fw-bold text-dark mb-0">
                    <i class="fas fa-table-list me-2 text-primary"></i>Matriks 27 Point Pencegahan & Penguatan
                </h3>
                <span class="text-muted small">Status keberadaan evidence, progress Bank Sumut, dan rekomendasi bukti teknis tambahan.</span>
            </div>
            <a href="{{ route('sumut.items') }}" class="btn btn-sm btn-primary">
                Lihat Detail & Download Bukti <i class="fas fa-external-link-alt ms-1"></i>
            </a>
        </div>
        <div class="table-responsive">
            <table class="table table-vcenter table-hover card-table">
                <thead class="table-light">
                    <tr>
                        <th style="width: 50px;" class="text-center">No</th>
                        <th style="width: 140px;">Kluster</th>
                        <th>Point Kegiatan Penguatan</th>
                        <th style="width: 100px;" class="text-center">Status</th>
                        <th style="width: 100px;" class="text-center">Bukti Fisik</th>
                        <th style="width: 120px;" class="text-center">Progress Bank Sumut</th>
                        <th style="width: 110px;" class="text-center">Target</th>
                        <th>Rekomendasi Bukti Tambahan</th>
                        <th style="width: 80px;" class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($items as $item)
                    <tr>
                        <td class="text-center fw-bold text-muted">
                            <span class="badge bg-secondary-lt fs-7">#{{ $item->folder_number }}</span>
                        </td>
                        <td>
                            <span class="badge bg-{{ $item->category_color }}-lt text-{{ $item->category_color }} fs-8 text-wrap">
                                {{ $item->category }}
                            </span>
                        </td>
                        <td>
                            <div class="fw-bold text-dark">{{ $item->clean_title }}</div>
                            <div class="text-muted small text-truncate" style="max-width: 320px;" title="{{ $item->title }}">
                                {{ $item->title }}
                            </div>
                        </td>
                        <td class="text-center">
                            @if($item->status === 'Ada')
                                <span class="badge bg-green text-green-fg fw-bold">
                                    <i class="fas fa-check-circle me-1"></i>Ada
                                </span>
                            @else
                                <span class="badge bg-red text-red-fg fw-bold">
                                    <i class="fas fa-times-circle me-1"></i>Tidak Ada
                                </span>
                            @endif
                        </td>
                        <td class="text-center">
                            <span class="badge bg-blue-lt text-blue fw-semibold">
                                <i class="fas fa-file me-1"></i>{{ $item->evidences->count() }} file
                            </span>
                        </td>
                        <td class="text-center">
                            <span class="badge {{ $item->progress_percent >= 100 ? 'bg-success-lt text-success' : 'bg-warning-lt text-warning' }} fw-bold">
                                {{ $item->progress_bs ?: $item->progress }}
                            </span>
                        </td>
                        <td class="text-center small text-muted">
                            {{ $item->target_date ?: '-' }}
                        </td>
                        <td>
                            <div class="small text-muted" style="max-width: 380px;">
                                <i class="fas fa-lightbulb text-warning me-1"></i>
                                {{ $item->recommendation ? Str::limit($item->recommendation, 120) : '-' }}
                            </div>
                        </td>
                        <td class="text-center">
                            <a href="{{ route('sumut.items', ['search' => $item->point_number]) }}" class="btn btn-xs btn-outline-primary" title="Buka Point #{{ $item->folder_number }}">
                                <i class="fas fa-folder-open"></i>
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- ApexCharts Script -->
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<script>
document.addEventListener("DOMContentLoaded", function () {
    // 1. Donut Status Chart
    const statusData = @json($statusChart['data']);
    const statusLabels = @json($statusChart['labels']);
    const statusColors = @json($statusChart['colors']);

    const optionsStatus = {
        series: statusData,
        chart: {
            type: 'donut',
            height: 250,
            fontFamily: 'inherit',
        },
        labels: statusLabels,
        colors: statusColors,
        legend: {
            show: false,
        },
        dataLabels: {
            enabled: true,
            formatter: function (val, opts) {
                return opts.w.config.series[opts.seriesIndex] + " Point";
            }
        },
        plotOptions: {
            pie: {
                donut: {
                    size: '68%',
                    labels: {
                        show: true,
                        total: {
                            show: true,
                            label: 'Total Point',
                            formatter: function (w) {
                                return w.globals.seriesTotals.reduce((a, b) => a + b, 0);
                            }
                        }
                    }
                }
            }
        },
    };
    new ApexCharts(document.querySelector("#chart-status-donut"), optionsStatus).render();

    // 2. Bar Categories Chart (Clean Single Series)
    const catLabels = @json($categoryChart['labels']);
    const catTotals = @json($categoryChart['total']);
    const catAda = @json($categoryChart['ada']);
    const catAvgProgress = @json($categoryChart['avg_progress']);
    const catEvidences = @json($categoryChart['evidences_count']);

    // Formatted 2-line category labels for crisp, balanced rendering under each bar (max 14-16 chars per line)
    const catLabelMap = {
        'CBS & Integritas Transaksi': ['CBS & Integritas', 'Transaksi'],
        'Sistem Operasi & Database Hardening': ['Sistem Operasi', '& DB Hardening'],
        'Containment & Endpoint Security (EDR)': ['Containment &', 'EDR Security'],
        'Fraud Detection & Early Warning System': ['Fraud Detection', '& EWS System'],
        'Akses Kontrol, PAM & Integrasi API': ['Akses Kontrol', 'PAM & API'],
        'Keamanan Perimeter, Firewall & WAF': ['Perimeter,', 'Firewall & WAF'],
        'Rekonstruksi Sistem, HSM & Solusi Lanjutan': ['Rekonstruksi,', 'HSM Lanjutan'],
    };

    const formattedCatLabels = catLabels.map(label => catLabelMap[label] || label);

    const clusterColors = [
        '#206bc4', // CBS & Integritas Transaksi (Blue)
        '#4263eb', // Sistem Operasi & Database Hardening (Indigo)
        '#ae3ec9', // Containment & Endpoint Security (EDR) (Purple)
        '#f76707', // Fraud Detection & Early Warning System (Orange)
        '#0ca678', // Akses Kontrol, PAM & Integrasi API (Teal)
        '#17a2b8', // Keamanan Perimeter, Firewall & WAF (Azure)
        '#d6336c', // Rekonstruksi Sistem, HSM & Solusi Lanjutan (Pink)
    ];

    const optionsCat = {
        series: [{
            name: 'Jumlah Point Penguatan',
            data: catTotals
        }],
        chart: {
            type: 'bar',
            height: 310,
            toolbar: { show: false },
            fontFamily: 'inherit',
        },
        colors: clusterColors,
        plotOptions: {
            bar: {
                distributed: true,
                columnWidth: '40%',
                borderRadius: 6,
                dataLabels: {
                    position: 'top',
                }
            },
        },
        dataLabels: {
            enabled: true,
            formatter: function (val) {
                return val + " Point";
            },
            offsetY: -18,
            style: {
                fontSize: '10.5px',
                fontWeight: 'bold',
                colors: ['#334155']
            }
        },
        stroke: { show: true, width: 2, colors: ['transparent'] },
        xaxis: {
            categories: formattedCatLabels,
            labels: {
                rotate: 0,
                rotateAlways: false,
                hideOverlappingLabels: false,
                trim: false,
                maxHeight: 70,
                style: {
                    fontSize: '9.5px',
                    fontWeight: 600,
                    colors: '#475569'
                }
            },
            axisBorder: { show: false },
            axisTicks: { show: false }
        },
        yaxis: {
            title: { text: 'Jumlah Point' },
            min: 0,
            max: 7,
            tickAmount: 7
        },
        grid: {
            borderColor: '#f1f5f9',
            padding: {
                left: 25,
                right: 25,
                bottom: 10
            }
        },
        legend: { show: false },
        tooltip: {
            custom: function({series, seriesIndex, dataPointIndex, w}) {
                const name = catLabels[dataPointIndex];
                const total = catTotals[dataPointIndex];
                const ada = catAda[dataPointIndex];
                const evCount = catEvidences[dataPointIndex];
                const prog = catAvgProgress[dataPointIndex];
                return `<div class="p-2 shadow-sm rounded bg-white text-dark small border" style="min-width: 230px;">
                    <div class="fw-bold mb-1 border-bottom pb-1 text-primary">
                        <i class="fas fa-layer-group me-1"></i>${name}
                    </div>
                    <div class="d-flex justify-content-between mt-1">
                        <span class="text-muted">Total Point:</span>
                        <span class="fw-bold text-dark">${total} Point</span>
                    </div>
                    <div class="d-flex justify-content-between mt-1">
                        <span class="text-muted">Status Bukti:</span>
                        <span class="badge bg-green-lt text-green fs-8">100% Ada (${ada}/${total})</span>
                    </div>
                    <div class="d-flex justify-content-between mt-1">
                        <span class="text-muted">Berkas Evidence:</span>
                        <span class="fw-bold text-dark">${evCount} Files</span>
                    </div>
                    <div class="d-flex justify-content-between mt-1">
                        <span class="text-muted">Rata-rata Progress Bank Sumut:</span>
                        <span class="fw-bold text-primary">${prog}%</span>
                    </div>
                </div>`;
            }
        }
    };
    new ApexCharts(document.querySelector("#chart-categories-bar"), optionsCat).render();

    // 3. PIC Donut Chart (Modern, clean, no cramped text labels)
    const picLabels = @json($picChart['labels']);
    const picTotals = @json($picChart['total']);

    const picPalette = [
        '#206bc4', '#4263eb', '#0ca678', '#f76707', '#ae3ec9',
        '#17a2b8', '#d6336c', '#6f42c1', '#fd7e14', '#20c997'
    ];

    const optionsPic = {
        series: picTotals,
        labels: picLabels,
        chart: {
            type: 'donut',
            height: 240,
            fontFamily: 'inherit',
        },
        colors: picPalette,
        legend: {
            show: false,
        },
        dataLabels: {
            enabled: false,
        },
        stroke: {
            width: 2,
            colors: ['#ffffff'],
        },
        plotOptions: {
            pie: {
                donut: {
                    size: '68%',
                    labels: {
                        show: true,
                        name: {
                            show: true,
                            fontSize: '13px',
                            fontWeight: 600,
                            offsetY: -5,
                        },
                        value: {
                            show: true,
                            fontSize: '20px',
                            fontWeight: 'bold',
                            formatter: function (val) {
                                return val + " Point";
                            },
                            offsetY: 5,
                        },
                        total: {
                            show: true,
                            label: 'Total Point',
                            color: '#64748b',
                            fontSize: '12px',
                            formatter: function (w) {
                                return w.globals.seriesTotals.reduce((a, b) => a + b, 0) + " Point";
                            }
                        }
                    }
                }
            }
        },
        tooltip: {
            y: {
                formatter: function (val, opts) {
                    const total = opts.globals.seriesTotals.reduce((a, b) => a + b, 0);
                    const pct = total > 0 ? ((val / total) * 100).toFixed(1) : 0;
                    return val + " Point (" + pct + "%)";
                }
            }
        }
    };
    new ApexCharts(document.querySelector("#chart-pic-donut"), optionsPic).render();
});
</script>
@endsection
