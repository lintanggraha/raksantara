@extends('layouts.tabler')

@section('title', $edition . ' Regulasi Sistem Pembayaran Bank Indonesia - Raksantara')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/framework-reader.css') }}">
@endpush

@section('content')
@php
    $reqCollection = collect($requirements);
    $totalReqs = $reqCollection->count();
    $chapters = $reqCollection->pluck('chapter_title', 'chapter')->unique();
    $domains = $reqCollection->pluck('implementation_domain')->filter()->unique();
    $appendixCollection = collect($appendices ?? []);
    $totalAppendices = $appendixCollection->count();
    $firstReq = $reqCollection->first();
@endphp

<div class="fw-page">
    {{-- EDITION SWITCHER BAR --}}
    <div class="d-flex flex-wrap align-items-center justify-content-between p-3 mb-3 bg-white rounded-4 border shadow-sm gap-2">
        <div class="d-flex align-items-center gap-2">
            <span class="badge bg-warning-lt text-warning p-2 rounded-circle"><i class="fas fa-landmark"></i></span>
            <div>
                <strong class="text-dark small d-block">Pilih Regulasi PADG Bank Indonesia</strong>
                <span class="text-muted" style="font-size: 0.78rem;">Penyelenggara Jasa Pembayaran (PJP) & Infrastruktur Sistem Pembayaran (PIP)</span>
            </div>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('frameworks.padg') }}" class="btn btn-sm {{ $edition === 'PADG 32/2025' ? 'btn-primary fw-bold' : 'btn-outline-secondary' }}">
                <i class="fas fa-credit-card me-1"></i> PADG 32/2025 (Industri SP)
            </a>
            <a href="{{ route('frameworks.padg24') }}" class="btn btn-sm {{ $edition === 'PADG 24/2024' ? 'btn-primary fw-bold' : 'btn-outline-secondary' }}">
                <i class="fas fa-shield-halved me-1"></i> PADG 24/2024 (Ketahanan Siber BI)
            </a>
        </div>
    </div>

    {{-- COMPACT MODERN HERO --}}
    <div class="fw-hero">
        <div class="fw-hero-header">
            <div class="fw-kicker">
                <i class="fas fa-building-columns"></i> Peraturan Anggota Dewan Gubernur BI
            </div>
            <span class="badge bg-warning-lt text-warning fw-bold px-3 py-1 rounded-pill">Edisi: {{ $edition }}</span>
        </div>
        <h1 class="fw-title">{{ $edition }}</h1>
        <p class="fw-lede">
            Peraturan Anggota Dewan Gubernur Bank Indonesia yang mengatur standarisasi operasional industri sistem pembayaran, ketahanan dan keamanan siber (KKS), pengelolaan risiko fraud, tata kelola pemrosesan transaksi, serta kewajiban pelaporan insiden kepada Bank Indonesia.
        </p>
        <div class="fw-metric-row">
            <div class="fw-metric-pill">
                <span>Total Klausul:</span>
                <strong>{{ $totalReqs }} Ketentuan</strong>
            </div>
            <div class="fw-metric-pill">
                <span>Bab Pengaturan:</span>
                <strong>{{ count($chapters) }} Bab Regulasi</strong>
            </div>
            <div class="fw-metric-pill">
                <span>Entitas Sasaran:</span>
                <strong>PJP / PIP Bank & Non-Bank</strong>
            </div>
            <div class="fw-metric-pill">
                <span>Otoritas Pengawas:</span>
                <strong>Bank Indonesia (SP)</strong>
            </div>
        </div>
    </div>

    {{-- NAVIGATION TABS --}}
    <div class="fw-nav-container">
        <ul class="fw-nav">
            <li>
                <button class="fw-tab active" data-tab="tab-explorer">
                    <i class="fas fa-list-check"></i>
                    <span>Eksplorasi Klausul & Ketentuan ({{ $totalReqs }})</span>
                </button>
            </li>
            <li>
                <button class="fw-tab" data-tab="tab-overview">
                    <i class="fas fa-cubes"></i>
                    <span>Struktur Bab & Pilar Kepatuhan</span>
                </button>
            </li>
            @if($totalAppendices > 0)
            <li>
                <button class="fw-tab" data-tab="tab-references">
                    <i class="fas fa-book-open"></i>
                    <span>Lampiran & Format Rujukan ({{ $totalAppendices }})</span>
                </button>
            </li>
            @endif
        </ul>
    </div>

    {{-- TAB 1: CLAUSE EXPLORER & MASTER-DETAIL (ACTIVE DEFAULT) --}}
    <div class="fw-tab-content" id="tab-explorer">
        <div class="fw-workspace">
            {{-- LEFT COLUMN: LIST PANEL --}}
            <div class="fw-list-panel">
                <div class="fw-search-box">
                    <div class="fw-search-input-wrap">
                        <i class="fas fa-search"></i>
                        <input
                            type="text"
                            id="fw-search-input"
                            class="fw-search-input"
                            placeholder="Cari klausul, topik, fraud, QRIS..."
                            autocomplete="off"
                        >
                        <span class="fw-search-kbd">/</span>
                    </div>
                    <select id="fw-filter-select" class="fw-filter-select">
                        <option value="">Semua Bab ({{ count($chapters) }} Bab)</option>
                        @foreach($chapters as $chKey => $chTitle)
                            <option value="{{ $chKey }}">Bab {{ $chKey }}: {{ Str::limit($chTitle ?: 'Ketentuan', 30) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="fw-counter-bar">
                    <span id="fw-counter">{{ $totalReqs }} dari {{ $totalReqs }} ketentuan ditampilkan</span>
                    <span class="text-muted small"><i class="fas fa-arrows-alt-v me-1"></i> Gunakan ↑/↓ keyboard</span>
                </div>

                <div class="fw-list" id="fw-items-container">
                    @foreach($requirements as $index => $req)
                        @php
                            $searchIndex = strtolower(($req['id'] ?? '') . ' ' . ($req['title'] ?? '') . ' ' . ($req['summary'] ?? '') . ' ' . ($req['implementation_domain'] ?? '') . ' ' . ($req['chapter'] ?? ''));
                            $bod = $req['roleTranslations']['bod'] ?? ($req['roleTranslations']['board'] ?? '');
                            $sysadmin = $req['roleTranslations']['sysadmin'] ?? '';
                            $legal = $req['roleTranslations']['legal'] ?? '';
                        @endphp
                        <button
                            type="button"
                            class="fw-item {{ $index === 0 ? 'active' : '' }}"
                            data-group="{{ $req['chapter'] ?? '' }}"
                            data-search-index="{{ $searchIndex }}"
                            data-id="{{ $req['id'] ?? '' }}"
                            data-title="{{ $req['title'] ?? '' }}"
                            data-chapter="Bab {{ $req['chapter'] ?? '-' }}"
                            data-chapter-title="{{ $req['chapter_title'] ?? '' }}"
                            data-domain="{{ $req['implementation_domain'] ?? ($req['owner'] ?? 'Penyelenggara') }}"
                            data-summary="{{ $req['summary'] ?? '' }}"
                            data-analogy="{{ $req['analogy'] ?? '' }}"
                            data-focus='@json($req['focus'] ?? [])'
                            data-evidence='@json($req['evidence'] ?? [])'
                            data-bod="{{ $bod }}"
                            data-sysadmin="{{ $sysadmin }}"
                            data-legal="{{ $legal }}"
                        >
                            <div class="fw-item-top">
                                <span class="fw-item-code">{{ $req['id'] }}</span>
                                <span class="fw-item-tag">Bab {{ $req['chapter'] ?? '-' }}</span>
                            </div>
                            <div class="fw-item-title">{{ $req['title'] ?? '' }}</div>
                            <div class="fw-item-desc">{{ Str::limit($req['summary'] ?? '', 95) }}</div>
                        </button>
                    @endforeach
                </div>
            </div>

            {{-- RIGHT COLUMN: INSPECTOR PANEL --}}
            <div class="fw-inspector">
                <div class="fw-inspector-head">
                    <div class="fw-inspector-actions">
                        <span class="fw-badge-legal" id="insp-id">{{ $firstReq['id'] ?? '-' }}</span>
                        <div class="d-flex align-items-center gap-2">
                            <button type="button" class="btn btn-sm btn-outline-primary" data-copy-target="insp-summary" title="Salin ringkasan ketentuan">
                                <i class="fas fa-copy me-1"></i> Salin Teks
                            </button>
                            <a href="{{ route('analysis.cross-mapping') }}" class="btn btn-sm btn-outline-secondary" title="Buka pemetaan regulasi silang">
                                <i class="fas fa-network-wired me-1"></i> Cross-Mapping
                            </a>
                        </div>
                    </div>
                    <h2 class="fw-inspector-title" id="insp-title">{{ $firstReq['title'] ?? 'Pilih Ketentuan' }}</h2>
                    <div class="fw-meta-chips">
                        <span class="fw-meta-chip"><i class="fas fa-book text-muted"></i> <span id="insp-chapter">-</span></span>
                        <span class="fw-meta-chip"><i class="fas fa-layer-group text-primary"></i> <span id="insp-domain">-</span></span>
                    </div>
                </div>

                {{-- SUMMARY --}}
                <div class="fw-section">
                    <div class="fw-section-title"><i class="fas fa-align-left text-primary"></i> Ringkasan Ketentuan</div>
                    <div class="fw-body-text" id="insp-summary">{{ $firstReq['summary'] ?? '-' }}</div>
                </div>

                {{-- ANALOGY / PENJELASAN --}}
                <div class="fw-section">
                    <div class="fw-section-title"><i class="fas fa-lightbulb text-warning"></i> Pemahaman Prinsip Operasional BI</div>
                    <div class="p-3 bg-light rounded-3 text-secondary" style="font-size: 0.88rem; line-height: 1.55;" id="insp-analogy">-</div>
                </div>

                {{-- KEY FOCUS --}}
                <div class="fw-section">
                    <div class="fw-section-title"><i class="fas fa-bullseye text-danger"></i> Fokus Utama Kepatuhan</div>
                    <ul class="fw-bullets-list" id="insp-focus"></ul>
                </div>

                {{-- EVIDENCE NEEDED --}}
                <div class="fw-section">
                    <div class="fw-section-title"><i class="fas fa-folder-check text-success"></i> Dokumen Bukti & Evidencing Audit</div>
                    <ul class="fw-bullets-list" id="insp-evidence"></ul>
                </div>

                {{-- ROLE IMPACT CARDS --}}
                <div class="fw-section">
                    <div class="fw-section-title"><i class="fas fa-users-cog text-indigo"></i> Pembagian Tanggung Jawab Peran</div>
                    <div class="fw-role-grid">
                        <div class="fw-role-card bod">
                            <strong><i class="fas fa-crown text-purple"></i> Direksi & Manajemen</strong>
                            <p id="insp-bod">-</p>
                        </div>
                        <div class="fw-role-card sysadmin">
                            <strong><i class="fas fa-terminal text-blue"></i> Tim Teknis & Operasional SP</strong>
                            <p id="insp-sysadmin">-</p>
                        </div>
                        <div class="fw-role-card legal">
                            <strong><i class="fas fa-scale-balanced text-yellow"></i> Legal & Compliance</strong>
                            <p id="insp-legal">-</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- TAB 2: OVERVIEW & PILLARS --}}
    <div class="fw-tab-content d-none" id="tab-overview">
        <div class="row g-3">
            <div class="col-lg-6">
                <div class="card p-3 h-100 shadow-sm border-0">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <h3 class="h5 text-dark mb-0"><i class="fas fa-book text-warning me-2"></i> Struktur Bab {{ $edition }}</h3>
                        <span class="badge bg-warning-lt">{{ count($chapters) }} Bab</span>
                    </div>
                    <p class="text-muted small mb-3">Penyelenggara wajib mengintegrasikan seluruh bab ke dalam kebijakan dan SOP operasional:</p>

                    <div class="d-flex flex-column gap-2" style="max-height: 480px; overflow-y: auto;">
                        @foreach($chapters as $chKey => $chTitle)
                            @php
                                $cnt = $reqCollection->where('chapter', $chKey)->count();
                            @endphp
                            <div class="p-3 rounded-3 bg-light border d-flex align-items-center justify-content-between">
                                <div>
                                    <strong class="text-dark small d-block">Bab {{ $chKey }}: {{ $chTitle ?: 'Ketentuan Operasional' }}</strong>
                                    <span class="text-muted small">Standar kepatuhan penyelenggaraan BI</span>
                                </div>
                                <span class="badge bg-primary-lt">{{ $cnt }} Ketentuan</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="card p-3 h-100 shadow-sm border-0">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <h3 class="h5 text-dark mb-0"><i class="fas fa-shield-halved text-primary me-2"></i> Pilar Utama Kepatuhan Sistem Pembayaran</h3>
                        <span class="badge bg-primary-lt">Prinsip BI</span>
                    </div>

                    <div class="d-flex flex-column gap-3">
                        <div class="p-3 rounded-3 bg-light border">
                            <strong class="text-primary small d-block mb-1"><i class="fas fa-lock me-1"></i> Keamanan & Enkripsi Data Transaksi</strong>
                            <p class="text-muted small mb-0">Standar enkripsi end-to-end pada kanal komunikasi sistem pembayaran, perlindungan PIN nasabah (HSM), dan tokenisasi kartu.</p>
                        </div>
                        <div class="p-3 rounded-3 bg-light border">
                            <strong class="text-primary small d-block mb-1"><i class="fas fa-triangle-exclamation me-1"></i> Fraud Management & Real-Time Monitoring</strong>
                            <p class="text-muted small mb-0">Kewajiban mengoperasikan Fraud Detection System (FDS) secara real-time dengan threshold adaptif dan koordinasi intelijen ancaman industri.</p>
                        </div>
                        <div class="p-3 rounded-3 bg-light border">
                            <strong class="text-primary small d-block mb-1"><i class="fas fa-bolt me-1"></i> Ketersediaan Layanan (Uptime & SLA 99.9%)</strong>
                            <p class="text-muted small mb-0">Target ketersediaan sistem pembayaran (SLA minimal 99.9%), redundansi sistem, dan kesiapan pemulihan bencana (RTO ≤ 2 jam).</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- TAB 3: REFERENCES --}}
    @if($totalAppendices > 0)
    <div class="fw-tab-content d-none" id="tab-references">
        <div class="card p-3 shadow-sm border-0">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <h3 class="h5 text-dark mb-0"><i class="fas fa-book-open text-primary me-2"></i> Dokumen & Lampiran Rujukan</h3>
                <span class="badge bg-primary-lt">{{ $totalAppendices }} Dokumen</span>
            </div>

            <div class="table-responsive">
                <table class="table table-vcenter table-hover border">
                    <thead class="bg-light">
                        <tr>
                            <th style="width: 120px;">Kode</th>
                            <th>Judul & Deskripsi</th>
                            <th style="width: 150px;">Kategori</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($appendices as $app)
                            <tr>
                                <td><span class="badge bg-primary-lt font-monospace">{{ $app['id'] ?? '-' }}</span></td>
                                <td>
                                    <div class="fw-bold text-dark">{{ $app['title'] ?? '-' }}</div>
                                    <div class="text-muted small mt-1">{{ $app['summary'] ?? ($app['scope'] ?? '') }}</div>
                                </td>
                                <td><span class="badge bg-secondary-lt">{{ $app['type'] ?? 'Lampiran' }}</span></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif
</div>
@endsection

@push('scripts')
<script src="{{ asset('js/framework-reader.js') }}"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const inspId = document.getElementById('insp-id');
    const inspTitle = document.getElementById('insp-title');
    const inspChapter = document.getElementById('insp-chapter');
    const inspDomain = document.getElementById('insp-domain');
    const inspSummary = document.getElementById('insp-summary');
    const inspAnalogy = document.getElementById('insp-analogy');
    const inspFocus = document.getElementById('insp-focus');
    const inspEvidence = document.getElementById('insp-evidence');
    const inspBod = document.getElementById('insp-bod');
    const inspSysadmin = document.getElementById('insp-sysadmin');
    const inspLegal = document.getElementById('insp-legal');

    new FrameworkReader({
        onItemSelect: function(el) {
            if (inspId) inspId.textContent = el.dataset.id || '-';
            if (inspTitle) inspTitle.textContent = el.dataset.title || 'Pilih Ketentuan';
            if (inspChapter) inspChapter.textContent = el.dataset.chapter || '-';
            if (inspDomain) inspDomain.textContent = el.dataset.domain || '-';
            if (inspSummary) inspSummary.textContent = el.dataset.summary || '-';
            if (inspAnalogy) inspAnalogy.textContent = el.dataset.analogy || 'Terapkan kepatuhan operasional sesuai standar Bank Indonesia.';

            // Focus
            if (inspFocus) {
                inspFocus.innerHTML = '';
                try {
                    const focusArr = JSON.parse(el.dataset.focus || '[]');
                    if (focusArr.length === 0) {
                        inspFocus.innerHTML = '<li class="text-muted">Tidak ada fokus spesifik dicatat.</li>';
                    } else {
                        focusArr.forEach(f => {
                            const li = document.createElement('li');
                            li.textContent = f;
                            inspFocus.appendChild(li);
                        });
                    }
                } catch(e) {}
            }

            // Evidence
            if (inspEvidence) {
                inspEvidence.innerHTML = '';
                try {
                    const evArr = JSON.parse(el.dataset.evidence || '[]');
                    if (evArr.length === 0) {
                        inspEvidence.innerHTML = '<li class="text-muted">Dokumentasikan SOP dan log audit transaksi.</li>';
                    } else {
                        evArr.forEach(ev => {
                            const li = document.createElement('li');
                            li.textContent = ev;
                            inspEvidence.appendChild(li);
                        });
                    }
                } catch(e) {}
            }

            // Roles
            if (inspBod) inspBod.textContent = el.dataset.bod || 'Pengawasan kebijakan dan alokasi sumber daya sistem pembayaran.';
            if (inspSysadmin) inspSysadmin.textContent = el.dataset.sysadmin || 'Konfigurasi teknis, patching, enkripsi, dan monitoring FDS.';
            if (inspLegal) inspLegal.textContent = el.dataset.legal || 'Verifikasi kontrak kerja sama merchant/penyelenggara dan kepatuhan BI.';
        }
    });
});
</script>
@endpush
