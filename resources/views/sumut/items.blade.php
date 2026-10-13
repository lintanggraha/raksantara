@extends('layouts.tabler')

@section('title', '27 Point Checklist & Evidence Explorer - Bank Sumut')

@section('content')
<div class="container-xl">
    <!-- Header -->
    <div class="page-header d-print-none mb-3">
        <div class="row align-items-center">
            <div class="col">
                <div class="page-pretitle d-flex align-items-center gap-2">
                    <span class="badge bg-primary-lt fs-6 fw-semibold"><i class="fas fa-landmark me-1"></i>Bank Sumut</span>
                    <span class="text-muted fs-7">Langkah Pencegahan & Penguatan OJK</span>
                    <span class="badge bg-green-lt text-green fs-7 fw-bold"><i class="fas fa-check-circle me-1"></i>Semua 27 Point Terverifikasi</span>
                </div>
                <h2 class="page-title text-dark fw-bold mt-1">
                    <i class="fas fa-folder-tree text-primary me-2"></i>27 Point Checklist & Evidence Explorer
                </h2>
                <div class="text-muted small mt-1">
                    Detail evaluasi kesesuaian berkas, arsip bukti fisik lokal, status keberadaan (Ada / Tidak Ada), dan rekomendasi bukti teknis tambahan.
                </div>
            </div>
            <div class="col-auto ms-auto d-print-none d-flex gap-2">
                <a href="{{ route('sumut.dashboard') }}" class="btn btn-outline-primary d-inline-flex align-items-center gap-1 shadow-sm">
                    <i class="fas fa-chart-pie"></i>
                    <span>Kembali ke Dashboard</span>
                </a>
                <a href="{{ route('sumut.export-excel') }}" class="btn btn-outline-success d-inline-flex align-items-center gap-1">
                    <i class="fas fa-file-excel"></i>
                    <span>Export Excel</span>
                </a>
            </div>
        </div>
    </div>

    <!-- Alert Messages -->
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

    <!-- Navigation Tabs -->
    <div class="mb-3">
        <ul class="nav nav-bordered border-bottom">
            <li class="nav-item">
                <a class="nav-link text-muted" href="{{ route('sumut.dashboard') }}">
                    <i class="fas fa-chart-pie me-1"></i> Executive Dashboard
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link active fw-bold" href="{{ route('sumut.items') }}">
                    <i class="fas fa-folder-open me-1 text-primary"></i> 27 Point Checklist & Evidence Explorer
                </a>
            </li>
        </ul>
    </div>

    <!-- Filter Card -->
    <div class="card shadow-sm border-0 mb-4 rounded-3">
        <div class="card-body">
            <form method="GET" action="{{ route('sumut.items') }}" class="row g-2 align-items-center">
                <!-- Search Input -->
                <div class="col-md-3">
                    <div class="input-icon">
                        <span class="input-icon-addon">
                            <i class="fas fa-search text-muted"></i>
                        </span>
                        <input type="text" name="search" class="form-control form-control-sm" placeholder="Cari nomor point, judul, PIC..." value="{{ request('search') }}">
                    </div>
                </div>

                <!-- Category Filter -->
                <div class="col-md-3">
                    <select name="category" class="form-select form-select-sm">
                        <option value="">Semua Kluster Keamanan</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat }}" {{ request('category') === $cat ? 'selected' : '' }}>
                                {{ $cat }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Progress Filter -->
                <div class="col-md-2">
                    <select name="progress_status" class="form-select form-select-sm">
                        <option value="">Semua Progres</option>
                        <option value="on_progress" {{ request('progress_status') === 'on_progress' ? 'selected' : '' }}>On Progress (< 100%)</option>
                        <option value="done" {{ request('progress_status') === 'done' ? 'selected' : '' }}>Done (100%)</option>
                    </select>
                </div>

                <!-- Status Filter -->
                <div class="col-md-2">
                    <select name="status" class="form-select form-select-sm">
                        <option value="">Semua Status Bukti</option>
                        <option value="Ada" {{ request('status') === 'Ada' ? 'selected' : '' }}>Ada (Sesuai & Ada Bukti)</option>
                        <option value="Tidak Ada" {{ request('status') === 'Tidak Ada' ? 'selected' : '' }}>Tidak Ada (Belum Ada Bukti)</option>
                    </select>
                </div>

                <!-- PIC Filter -->
                <div class="col-md-1">
                    <select name="pic" class="form-select form-select-sm">
                        <option value="">PIC</option>
                        @foreach($pics as $p)
                            <option value="{{ $p }}" {{ request('pic') === $p ? 'selected' : '' }}>
                                {{ $p }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Action Buttons -->
                <div class="col-md-1 d-flex gap-1">
                    <button type="submit" class="btn btn-sm btn-primary w-100" title="Terapkan Filter">
                        <i class="fas fa-filter"></i>
                    </button>
                    @if(request()->hasAny(['search', 'category', 'status', 'pic', 'progress_status']))
                    <a href="{{ route('sumut.items') }}" class="btn btn-sm btn-outline-secondary" title="Reset Filter">
                        <i class="fas fa-undo"></i>
                    </a>
                    @endif
                </div>
            </form>
        </div>
        <div class="card-footer bg-light py-2 px-3 d-flex justify-content-between align-items-center">
            <div class="small text-muted">
                Menampilkan <strong>{{ $items->total() }}</strong> dari <strong>{{ $totalItems }}</strong> point penguatan.
            </div>
            <div class="d-flex gap-2">
                <span class="badge bg-green-lt text-green border-green fs-8">
                    <i class="fas fa-check-circle me-1"></i>Ada: {{ $adaCount }} Point
                </span>
                <span class="badge bg-red-lt text-red border-red fs-8">
                    <i class="fas fa-times-circle me-1"></i>Tidak Ada: {{ $tidakAdaCount }} Point
                </span>
            </div>
        </div>
    </div>

    <!-- Points List -->
    @if($items->count() === 0)
        <div class="card shadow-sm border-0 text-center py-5">
            <div class="card-body">
                <i class="fas fa-search-minus fs-1 text-muted mb-3"></i>
                <h3 class="fw-bold text-dark">Tidak Ada Point yang Sesuai Filter</h3>
                <p class="text-muted small">Coba ubah kata kunci pencarian atau reset filter untuk menampilkan seluruh 27 point.</p>
                <a href="{{ route('sumut.items') }}" class="btn btn-primary btn-sm mt-2">Reset Filter</a>
            </div>
        </div>
    @else
        <div class="row row-cards">
            @foreach($items as $item)
            <div class="col-12" id="point-{{ $item->point_number }}">
                <div class="card shadow-sm border-0 rounded-3 mb-3 border-start border-{{ $item->status === 'Ada' ? 'success' : 'danger' }} border-4">
                    <!-- Card Header -->
                    <div class="card-header bg-transparent py-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <div class="d-flex align-items-center gap-2 flex-wrap">
                            <span class="badge bg-dark text-white fw-bold fs-6 px-3 py-2 rounded-2">
                                Point #{{ $item->folder_number }}
                            </span>
                            <span class="badge bg-{{ $item->category_color }}-lt text-{{ $item->category_color }} fs-7 fw-semibold">
                                <i class="fas fa-tag me-1"></i>{{ $item->category }}
                            </span>
                            <span class="badge bg-secondary-lt fs-7">
                                <i class="fas fa-user-shield me-1"></i>PIC: {{ $item->pic ?: 'Div. TI' }}
                            </span>
                            @if($item->target_date)
                            <span class="badge bg-light text-muted border fs-7">
                                <i class="fas fa-calendar-alt me-1"></i>Target: {{ $item->target_date }}
                            </span>
                            @endif
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <!-- Progress Bank Sumut Badge -->
                            <span class="badge {{ $item->progress_percent >= 100 ? 'bg-success text-white' : 'bg-warning text-dark' }} fs-7 fw-bold px-2 py-1">
                                Progress Bank Sumut: {{ $item->progress_bs ?: $item->progress }}
                            </span>

                            <!-- Status Bukti Badge -->
                            @if($item->status === 'Ada')
                                <span class="badge bg-green text-green-fg fs-7 fw-bold px-3 py-1">
                                    <i class="fas fa-check-circle me-1"></i>Bukti Ada ({{ $item->evidences->count() }} file)
                                </span>
                            @else
                                <span class="badge bg-red text-red-fg fs-7 fw-bold px-3 py-1">
                                    <i class="fas fa-times-circle me-1"></i>Bukti Tidak Ada
                                </span>
                            @endif

                            <!-- Edit Modal Button -->
                            <button type="button" class="btn btn-xs btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#modal-edit-{{ $item->id }}" title="Edit Status & Catatan">
                                <i class="fas fa-edit"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Card Body -->
                    <div class="card-body py-3">
                        <!-- Full Title / Activity Description -->
                        <div class="mb-3">
                            <h4 class="fw-bold text-dark mb-1">
                                {{ $item->clean_title }}
                            </h4>
                            <p class="text-muted small mb-0 lh-base">
                                {{ $item->title }}
                            </p>
                            @if($item->keterangan)
                            <div class="small text-secondary mt-1 fst-italic">
                                <i class="fas fa-info-circle me-1"></i><strong>Keterangan:</strong> {{ $item->keterangan }}
                            </div>
                            @endif
                            @if($item->follow_up)
                            <div class="small text-danger mt-1">
                                <i class="fas fa-exclamation-circle me-1"></i><strong>Catatan Bank Sumut / Tindak Lanjut:</strong> {{ $item->follow_up }}
                            </div>
                            @endif
                        </div>

                        <!-- 1. Physical Evidence Files Explorer -->
                        <div class="card bg-light border-0 rounded-2 mb-3">
                            <div class="card-header bg-transparent py-2 px-3 d-flex justify-content-between align-items-center">
                                <span class="fw-bold fs-7 text-dark">
                                    <i class="fas fa-folder-open text-warning me-1"></i>
                                    Arsip Bukti Fisik (Folder: <code>{{ $item->folder_number }}</code>)
                                </span>
                                <span class="badge bg-primary-lt fs-8">
                                    {{ $item->evidences->count() }} Dokumen / File Terlampir
                                </span>
                            </div>
                            <div class="card-body p-2">
                                @if($item->evidences->count() === 0)
                                    <div class="text-center py-3 text-muted small">
                                        <i class="fas fa-file-excel fs-3 text-danger mb-1 d-block"></i>
                                        Tidak ada file evidence pada folder ini.
                                    </div>
                                @else
                                    <div class="row g-2">
                                        @foreach($item->evidences as $ev)
                                        <div class="col-md-6 col-lg-4">
                                            <div class="p-2 border rounded bg-white d-flex align-items-center justify-content-between gap-2 shadow-none hover-shadow-sm">
                                                <div class="d-flex align-items-center gap-2 text-truncate">
                                                    <i class="{{ $ev->icon_class }} fs-3"></i>
                                                    <div class="text-truncate">
                                                        <div class="fw-semibold small text-truncate text-dark" title="{{ $ev->file_name }}">
                                                            {{ $ev->file_name }}
                                                        </div>
                                                        <div class="text-muted fs-8">
                                                            {{ $ev->formatted_size }} · <span class="text-uppercase">{{ $ev->file_extension }}</span>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="d-flex gap-1 flex-shrink-0">
                                                    <a href="{{ route('sumut.evidence.preview', $ev->id) }}" target="_blank" class="btn btn-xs btn-ghost-secondary" title="Preview / Lihat">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <a href="{{ route('sumut.evidence.download', $ev->id) }}" class="btn btn-xs btn-ghost-primary" title="Download File">
                                                        <i class="fas fa-download"></i>
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        </div>

                        <!-- 2. Analisis Kesesuaian Point & Bukti -->
                        <div class="alert alert-info border-0 rounded-2 py-2 px-3 mb-2 small shadow-none">
                            <div class="d-flex align-items-start gap-2">
                                <i class="fas fa-check-double text-info mt-1"></i>
                                <div>
                                    <strong class="text-dark">Analisis Kesesuaian Point &amp; Bukti:</strong>
                                    <div class="text-dark mt-0">
                                        {{ $item->notes ?: 'Telah diverifikasi sesuai dengan target langkah pencegahan dan arsip dokumen fisik terlampir.' }}
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- 3. Rekomendasi Bukti Lain (Menurut Analisa Ahli) -->
                        <div class="alert alert-warning border-0 rounded-2 py-2 px-3 mb-0 small shadow-none" style="background-color: #fefce8; border-left: 4px solid #f59e0b !important;">
                            <div class="d-flex align-items-start gap-2">
                                <i class="fas fa-shield-alt text-warning mt-1 fs-5"></i>
                                <div>
                                    <strong class="text-dark d-flex align-items-center gap-1">
                                        <span>Rekomendasi Bukti Tambahan (Menurut Analisa Forensik &amp; Security):</span>
                                        <span class="badge bg-warning-lt text-warning fs-8">Saran Audit</span>
                                    </strong>
                                    <div class="text-dark mt-1 lh-base">
                                        {{ $item->recommendation ?: 'Lampirkan log pengujian terperinci, laporan vulnerability assessment pasca-instalasi, dan Berita Acara verifikasi teknis.' }}
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- 4. Evidence yang Masih Kurang & Harus Dikumpulkan (Khusus Point < 100%) -->
                        @if($item->progress_percent < 100 && count($item->missing_evidence_list) > 0)
                        <div class="alert alert-danger border-0 rounded-2 py-2 px-3 mt-2 mb-0 small shadow-none" style="background-color: #fef2f2; border-left: 4px solid #ef4444 !important;">
                            <div class="d-flex align-items-start gap-2">
                                <i class="fas fa-exclamation-triangle text-danger mt-1 fs-5"></i>
                                <div>
                                    <strong class="text-dark d-flex align-items-center gap-1">
                                        <span>Evidence yang Masih Kurang &amp; Harus Dikumpulkan Menuju 100%:</span>
                                        <span class="badge bg-danger text-white fs-8">Wajib Dilengkapi</span>
                                    </strong>
                                    <ul class="list-unstyled mb-0 mt-1 d-flex flex-column gap-1">
                                        @foreach($item->missing_evidence_list as $gap)
                                        <li class="d-flex align-items-start gap-1 text-dark">
                                            <i class="fas fa-times-circle text-danger mt-1 flex-shrink-0" style="font-size: 11px;"></i>
                                            <span>{{ $gap }}</span>
                                        </li>
                                        @endforeach
                                    </ul>
                                </div>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Modal Edit Item -->
            <div class="modal fade" id="modal-edit-{{ $item->id }}" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <form action="{{ route('sumut.items.update', $item->id) }}" method="POST">
                            @csrf
                            <div class="modal-header">
                                <h5 class="modal-title fw-bold">
                                    <i class="fas fa-edit me-1 text-primary"></i>Edit Point #{{ $item->folder_number }}
                                </h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Judul Kegiatan</label>
                                    <div class="form-control-plaintext small text-muted">{{ $item->title }}</div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Status Keberadaan Bukti</label>
                                    <select name="status" class="form-select">
                                        <option value="Ada" {{ $item->status === 'Ada' ? 'selected' : '' }}>Ada (Sesuai & Ada Bukti)</option>
                                        <option value="Tidak Ada" {{ $item->status === 'Tidak Ada' ? 'selected' : '' }}>Tidak Ada (Belum Ada Bukti)</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Analisis Kesesuaian Point & Bukti</label>
                                    <textarea name="notes" class="form-control" rows="3">{{ $item->notes }}</textarea>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Rekomendasi Bukti Lain (Ahli)</label>
                                    <textarea name="recommendation" class="form-control" rows="3">{{ $item->recommendation }}</textarea>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Catatan Bank Sumut / Tindak Lanjut</label>
                                    <textarea name="follow_up" class="form-control" rows="2">{{ $item->follow_up }}</textarea>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                                <button type="submit" class="btn btn-primary btn-sm">Simpan Perubahan</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            @endforeach
        </div>

        <!-- Pagination -->
        <div class="d-flex justify-content-center mt-3">
            {{ $items->links() }}
        </div>
    @endif
</div>
@endsection
