@extends('layouts.tabler')

@section('title', 'Upload Evidence - Bank Sulteng')

@section('content_header')
<div class="row align-items-center">
    <div class="col">
        <div class="page-pretitle text-muted text-uppercase fw-bold">
            <i class="fas fa-file-upload me-1 text-primary"></i> Data &amp; Log Assessment
        </div>
        <h2 class="page-title d-flex align-items-center gap-2">
            <span>Upload Evidence</span>
            <span class="badge bg-primary-lt fs-6 fw-semibold">Bank Sulteng</span>
        </h2>
        <div class="text-muted mt-1 small">
            Upload file evidence per point data/log dengan penamaan otomatis sesuai judul point (suffix -1, -2, -3 jika multiple).
        </div>
    </div>
    <div class="col-auto ms-auto d-print-none">
        <div class="btn-list">
            <a href="{{ route('sulteng.dashboard') }}" class="btn btn-outline-primary d-inline-flex align-items-center gap-1 shadow-sm">
                <i class="fas fa-clipboard-check"></i>
                <span>Monitoring Assessment</span>
            </a>
            <a href="{{ route('sulteng.export-excel') }}" class="btn btn-outline-success d-inline-flex align-items-center gap-1">
                <i class="fas fa-file-excel text-success"></i>
                <span>Export Excel</span>
            </a>
        </div>
    </div>
</div>
@endsection

@section('content')
<div class="container-xl px-0">

    {{-- Sub Navigation Tabs --}}
    <ul class="nav nav-tabs border-bottom mb-4">
        <li class="nav-item">
            <a class="nav-link text-muted" href="{{ route('sulteng.dashboard') }}">
                <i class="fas fa-chart-pie me-1"></i> Dashboard Monitoring
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link active fw-bold" href="{{ route('sulteng.items') }}">
                <i class="fas fa-clipboard-list me-1 text-primary"></i> Checklist &amp; Upload Evidence
                <span class="badge bg-primary-lt ms-1">{{ $totalCount }}</span>
            </a>
        </li>
    </ul>


    {{-- Filter Bar --}}
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body p-3">
            <form method="GET" action="{{ route('sulteng.items') }}" class="row g-2 align-items-center">
                {{-- Search --}}
                <div class="col-md-3">
                    <div class="input-icon">
                        <span class="input-icon-addon">
                            <i class="fas fa-search text-muted"></i>
                        </span>
                        <input type="text" name="search" class="form-control form-control-sm" placeholder="Cari point data / log..." value="{{ request('search') }}">
                    </div>
                </div>

                {{-- Filter Category --}}
                <div class="col-md-2">
                    <select name="category" class="form-select form-select-sm">
                        <option value="">Semua Kategori</option>
                        @foreach($allCategories as $cat)
                            <option value="{{ $cat->category_code }}" {{ request('category') === $cat->category_code ? 'selected' : '' }}>
                                {{ $cat->category_code }}. {{ $cat->category_name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Filter Status (Binary: Hijau vs Merah) --}}
                <div class="col-md-2">
                    <select name="status" class="form-select form-select-sm">
                        <option value="">Semua Status</option>
                        <option value="Diterima" {{ request('status') === 'Diterima' ? 'selected' : '' }}>🟢 Diterima (Hijau)</option>
                        <option value="Belum Diterima" {{ request('status') === 'Belum Diterima' ? 'selected' : '' }}>🔴 Belum Diterima (Merah)</option>
                    </select>
                </div>

                {{-- Filter PIC --}}
                <div class="col-md-2">
                    <select name="pic" class="form-select form-select-sm">
                        <option value="">Semua PIC</option>
                        @foreach($allPics as $p)
                            <option value="{{ $p }}" {{ request('pic') === $p ? 'selected' : '' }}>{{ $p }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Filter Evidence Status --}}
                <div class="col-md-2">
                    <select name="has_evidence" class="form-select form-select-sm">
                        <option value="">Semua Evidence</option>
                        <option value="yes" {{ request('has_evidence') === 'yes' ? 'selected' : '' }}>Sudah Ada Evidence</option>
                        <option value="no" {{ request('has_evidence') === 'no' ? 'selected' : '' }}>Belum Ada Evidence</option>
                    </select>
                </div>

                {{-- Buttons --}}
                <div class="col-md-1 d-flex gap-1">
                    <button type="submit" class="btn btn-sm btn-primary w-100" title="Terapkan Filter">
                        <i class="fas fa-filter"></i>
                    </button>
                    <a href="{{ route('sulteng.items') }}" class="btn btn-sm btn-outline-secondary" title="Reset Filter">
                        <i class="fas fa-redo"></i>
                    </a>
                </div>
            </form>
        </div>
    </div>

    {{-- Checklist Items Grouped by Category --}}
    @if($groupedItems->isEmpty())
        <div class="card shadow-sm border-0 p-5 text-center text-muted">
            <i class="fas fa-clipboard-list fa-3x mb-3 text-secondary opacity-50"></i>
            <h4 class="fw-bold">Tidak ada item yang sesuai dengan filter</h4>
            <p class="small mb-3">Silakan reset filter pencarian Anda untuk melihat seluruh data checklist.</p>
            <div>
                <a href="{{ route('sulteng.items') }}" class="btn btn-primary btn-sm">Reset Filter</a>
            </div>
        </div>
    @else
        @foreach($groupedItems as $catCode => $items)
            @php($catName = $items->first()->category_name)
            @php($catTotal = $items->count())
            @php($catDone = $items->where('status', 'Diterima')->count())
            @php($catPct = $catTotal > 0 ? round(($catDone / $catTotal) * 100) : 0)

            <div class="card shadow-sm border-0 mb-4">
                {{-- Category Header --}}
                <div class="card-header bg-light py-3 d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge bg-primary text-white fw-bold fs-6">{{ $catCode }}</span>
                        <h3 class="card-title fw-bold text-dark mb-0 fs-4">{{ $catName }}</h3>
                        <span class="badge bg-secondary-lt ms-2">{{ $catTotal }} Point Data/Log</span>
                    </div>
                    <div class="d-flex align-items-center gap-3">
                        <div class="d-none d-sm-flex align-items-center gap-2">
                            <span class="small text-muted fw-bold">{{ $catDone }}/{{ $catTotal }} Selesai</span>
                            <div class="progress" style="width: 100px; height: 8px;">
                                <div class="progress-bar bg-success" style="width: {{ $catPct }}%"></div>
                            </div>
                            <span class="small fw-bold text-success">{{ $catPct }}%</span>
                        </div>
                    </div>
                </div>

                {{-- Table Items in this Category --}}
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-vcenter table-hover card-table">
                            <thead class="bg-white text-muted small">
                                <tr>
                                    <th style="width: 60px;">No</th>
                                    <th style="width: 380px;">Data / Log</th>
                                    <th style="width: 150px;">Periode</th>
                                    <th style="width: 150px;">PIC Divisi</th>
                                    <th style="width: 150px;">Status</th>
                                    <th>File Evidence</th>
                                    <th class="text-end" style="width: 150px;">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($items as $item)
                                <tr>
                                    {{-- Code / Item Number --}}
                                    <td>
                                        <span class="badge bg-primary-lt fw-bold fs-6">{{ $item->code }}</span>
                                    </td>

                                    {{-- Title / Content --}}
                                    <td>
                                        <div class="fw-bold text-dark mb-1" style="white-space: pre-line;">{{ $item->title }}</div>
                                        <div class="text-secondary small">
                                            <span class="text-muted">Target nama file:</span>
                                            <code class="text-primary">{{ $item->clean_title }}.ext</code>
                                        </div>
                                        @if(!empty($item->notes))
                                            @if($item->status === 'Belum Diterima')
                                            <div class="mt-2 p-2 rounded bg-danger-lt border border-danger small text-dark">
                                                <i class="fas fa-exclamation-triangle text-danger me-1"></i>
                                                <strong class="text-danger">Kekurangan Evidence / Catatan:</strong> {{ $item->notes }}
                                            </div>
                                            @elseif(stripos($item->notes, 'catatan') !== false || stripos($item->notes, 'rekomendasi') !== false)
                                            <div class="mt-2 p-2 rounded bg-info-lt border border-info small text-dark">
                                                <i class="fas fa-sticky-note text-info me-1"></i>
                                                <strong class="text-info">Catatan Forensik:</strong> {{ $item->notes }}
                                            </div>
                                            @else
                                            <div class="mt-1 small text-muted">
                                                <i class="fas fa-info-circle me-1"></i> {{ $item->notes }}
                                            </div>
                                            @endif
                                        @endif
                                    </td>

                                    {{-- Periode --}}
                                    <td>
                                        <span class="badge bg-purple-lt text-purple">
                                            <i class="far fa-calendar-alt me-1"></i> {{ $item->period ?: '-' }}
                                        </span>
                                    </td>

                                    {{-- PIC Divisi --}}
                                    <td>
                                        <span class="badge bg-blue-lt text-blue">
                                            <i class="fas fa-user-shield me-1"></i> {{ $item->pic_division ?: '-' }}
                                        </span>
                                    </td>

                                    {{-- Status with Quick Toggle (Binary: Diterima / Belum Diterima) --}}
                                    <td>
                                        <div class="dropdown">
                                            <button class="btn btn-sm {{ $item->status_badge_class }} dropdown-toggle shadow-none" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                                <i class="{{ $item->status_icon }} me-1"></i> {{ $item->status }}
                                            </button>
                                            <ul class="dropdown-menu">
                                                <li>
                                                    <form action="{{ route('sulteng.items.update', $item->id) }}" method="POST">
                                                        @csrf
                                                        <input type="hidden" name="status" value="Belum Diterima">
                                                        <button type="submit" class="dropdown-item text-danger {{ $item->status === 'Belum Diterima' ? 'active' : '' }}">
                                                            <i class="fas fa-times-circle me-2"></i> Belum Diterima (Merah)
                                                        </button>
                                                    </form>
                                                </li>
                                                <li>
                                                    <form action="{{ route('sulteng.items.update', $item->id) }}" method="POST">
                                                        @csrf
                                                        <input type="hidden" name="status" value="Diterima">
                                                        <button type="submit" class="dropdown-item text-success {{ $item->status === 'Diterima' ? 'active' : '' }}">
                                                            <i class="fas fa-check-circle me-2"></i> Diterima (Hijau)
                                                        </button>
                                                    </form>
                                                </li>
                                            </ul>
                                        </div>
                                    </td>

                                    {{-- Evidence Files List --}}
                                    <td>
                                        @if($item->evidences->isEmpty())
                                            <span class="text-muted small fst-italic">
                                                <i class="fas fa-exclamation-circle text-muted me-1"></i> Belum ada evidence
                                            </span>
                                        @else
                                            <div class="d-flex flex-column gap-1">
                                                @foreach($item->evidences as $ev)
                                                <div class="d-flex align-items-center justify-content-between p-1 bg-light rounded border border-light">
                                                    <div class="d-flex align-items-center gap-2 text-truncate" style="max-width: 320px;">
                                                        <i class="{{ $ev->icon_class }} fs-4"></i>
                                                        <div class="text-truncate">
                                                            <a href="{{ route('sulteng.evidence.preview', $ev->id) }}" target="_blank" class="fw-bold text-dark text-truncate text-decoration-none d-block small" title="{{ $ev->file_name }}">
                                                                {{ $ev->file_name }}
                                                            </a>
                                                            <span class="text-muted" style="font-size: 0.72rem;">{{ $ev->formatted_size }} &bull; {{ $ev->created_at->format('d M Y H:i') }}</span>
                                                        </div>
                                                    </div>
                                                    <div class="btn-list flex-nowrap ms-2">
                                                        {{-- Preview --}}
                                                        <a href="{{ route('sulteng.evidence.preview', $ev->id) }}" target="_blank" class="btn btn-xs btn-ghost-secondary" title="Buka / Preview File">
                                                            <i class="fas fa-external-link-alt"></i>
                                                        </a>
                                                        {{-- Download --}}
                                                        <a href="{{ route('sulteng.evidence.download', $ev->id) }}" class="btn btn-xs btn-ghost-primary" title="Download File">
                                                            <i class="fas fa-download"></i>
                                                        </a>
                                                        {{-- Delete --}}
                                                        <form action="{{ route('sulteng.evidence.destroy', $ev->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Hapus evidence file {{ $ev->file_name }}?');">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="btn btn-xs btn-ghost-danger" title="Hapus File Evidence">
                                                                <i class="fas fa-trash-alt"></i>
                                                            </button>
                                                        </form>
                                                    </div>
                                                </div>
                                                @endforeach
                                            </div>
                                        @endif
                                    </td>

                                    {{-- Actions (Upload & Edit) --}}
                                    <td class="text-end">
                                        <div class="btn-list justify-content-end flex-nowrap">
                                            {{-- Upload Button --}}
                                            <button type="button" class="btn btn-sm btn-primary d-inline-flex align-items-center gap-1" data-bs-toggle="modal" data-bs-target="#uploadModal-{{ $item->id }}">
                                                <i class="fas fa-upload"></i>
                                                <span>Upload</span>
                                            </button>

                                            {{-- Edit Item Modal Button --}}
                                            <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#editModal-{{ $item->id }}" title="Edit Detail Point">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>

                                {{-- Upload Modal for this Item --}}
                                <div class="modal fade" id="uploadModal-{{ $item->id }}" tabindex="-1" aria-labelledby="uploadModalLabel-{{ $item->id }}" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered">
                                        <div class="modal-content shadow-lg border-0">
                                            <form action="{{ route('sulteng.items.upload', $item->id) }}" method="POST" enctype="multipart/form-data">
                                                @csrf
                                                <div class="modal-header bg-light">
                                                    <h5 class="modal-title fw-bold" id="uploadModalLabel-{{ $item->id }}">
                                                        <i class="fas fa-file-upload text-primary me-2"></i> Upload Evidence [{{ $item->code }}]
                                                    </h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body">
                                                    {{-- Point Information --}}
                                                    <div class="mb-3 p-3 bg-light rounded border">
                                                        <div class="small text-muted text-uppercase fw-bold">Judul Point:</div>
                                                        <div class="fw-bold text-dark fs-5 mt-1" style="white-space: pre-line;">{{ $item->title }}</div>
                                                        <div class="mt-2 small text-secondary">
                                                            <i class="fas fa-folder text-warning me-1"></i> Folder evidence: <code>{{ $catCode }} - {{ $catName }}</code>
                                                        </div>
                                                    </div>

                                                    {{-- File Naming Notice --}}
                                                    <div class="alert alert-info py-2 px-3 small mb-3">
                                                        <i class="fas fa-info-circle me-1"></i> File otomatis diberi nama:
                                                        <br>
                                                        <strong>{{ $item->clean_title }}.ext</strong>
                                                        @if($item->evidences->count() > 0)
                                                            (Evidence berikutnya akan diberi nama <strong>{{ $item->clean_title }}-{{ $item->evidences->count() + 1 }}.ext</strong>)
                                                        @else
                                                            (Jika upload multiple file, akan otomatis diberi akhiran <strong>-1, -2, -3</strong>)
                                                        @endif
                                                    </div>

                                                    {{-- File Selector --}}
                                                    <div class="mb-3">
                                                        <label class="form-label required">Pilih File Evidence (Bisa Multiple):</label>
                                                        <input type="file" name="files[]" class="form-control" multiple required>
                                                        <div class="form-hint">Mendukung file dokumen (PDF, Word, Excel), log/teks (.log, .txt, .json), gambar (PNG, JPG), arsip (.zip, .rar, .7z) hingga 100MB per file.</div>
                                                    </div>

                                                    {{-- Notes --}}
                                                    <div class="mb-3">
                                                        <label class="form-label">Catatan / Keterangan (Opsional):</label>
                                                        <textarea name="notes" class="form-control" rows="2" placeholder="Contoh: Log firewall diekstrak dari server utama per 15 September..."></textarea>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                                    <button type="submit" class="btn btn-primary d-inline-flex align-items-center gap-1">
                                                        <i class="fas fa-cloud-upload-alt"></i>
                                                        <span>Upload File Sekarang</span>
                                                    </button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>

                                {{-- Edit Point Modal --}}
                                <div class="modal fade" id="editModal-{{ $item->id }}" tabindex="-1" aria-labelledby="editModalLabel-{{ $item->id }}" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered">
                                        <div class="modal-content shadow-lg border-0">
                                            <form action="{{ route('sulteng.items.update', $item->id) }}" method="POST">
                                                @csrf
                                                <div class="modal-header bg-light">
                                                    <h5 class="modal-title fw-bold" id="editModalLabel-{{ $item->id }}">
                                                        <i class="fas fa-edit text-secondary me-2"></i> Edit Point [{{ $item->code }}]
                                                    </h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <div class="mb-3">
                                                        <label class="form-label">Data / Log:</label>
                                                        <div class="fw-bold text-dark p-2 bg-light rounded" style="white-space: pre-line;">{{ $item->title }}</div>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label required">Status Pemenuhan:</label>
                                                        <select name="status" class="form-select" required>
                                                            <option value="Belum Diterima" {{ $item->status === 'Belum Diterima' ? 'selected' : '' }}>🔴 Belum Diterima (Merah)</option>
                                                            <option value="Diterima" {{ $item->status === 'Diterima' ? 'selected' : '' }}>🟢 Diterima (Hijau)</option>
                                                        </select>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label">PIC Divisi:</label>
                                                        <input type="text" name="pic_division" class="form-control" value="{{ $item->pic_division }}" placeholder="Contoh: Infra, ITOM, Cyber Security...">
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label">Catatan Tambahan:</label>
                                                        <textarea name="notes" class="form-control" rows="3" placeholder="Tambahkan catatan tindak lanjut...">{{ $item->notes }}</textarea>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                                    <button type="submit" class="btn btn-primary">
                                                        <i class="fas fa-save me-1"></i> Simpan Perubahan
                                                    </button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>

                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @endforeach
    @endif

</div>
@endsection
