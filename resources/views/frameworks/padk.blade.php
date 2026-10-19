@extends('layouts.tabler')

@section('title', 'PADK 1 Tahun 2026 Ketentuan Pelaksana TI Bank Umum - Raksantara')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/framework-reader.css') }}">
@endpush

@section('content')
@php
    $reqCollection = collect($requirements);
    $appCollection = collect($appendices);
    $totalReqs = $reqCollection->count();
    $totalApps = $appCollection->count();
    $pillars = $reqCollection->pluck('pillar')->unique()->filter();
@endphp

<div class="fw-page">
    {{-- COMPACT MODERN HERO --}}
    <div class="fw-hero">
        <div class="fw-hero-header">
            <div class="fw-kicker">
                <i class="fas fa-landmark"></i> Standar Pelaksana OJK 2026
            </div>
            <span class="badge bg-teal-lt text-teal fw-bold px-3 py-1 rounded-pill">Status: Aktif Berlaku</span>
        </div>
        <h1 class="fw-title">PADK 1 Tahun 2026</h1>
        <p class="fw-lede">
            Pedoman Standar Operasional Ketahanan & Siber Bank Umum. Mengatur mandat kritis SLA perbaikan celah KEV (maksimal 14 hari), perimeter Zero Trust, pembatasan remote access, dan pengawasan ketat Penempatan Sistem Luar Negeri (Format 4.1/4.2).
        </p>
        <div class="fw-metric-row">
            <div class="fw-metric-pill">
                <span>Total Ketentuan:</span>
                <strong>{{ $totalReqs }} Ketentuan</strong>
            </div>
            <div class="fw-metric-pill">
                <span>Format Lampiran:</span>
                <strong>{{ $totalApps }} Template</strong>
            </div>
            <div class="fw-metric-pill">
                <span>SLA Patching Kritis:</span>
                <strong>≤ 14 Hari Kalender</strong>
            </div>
            <div class="fw-metric-pill">
                <span>Pilar Utama:</span>
                <strong>Zero Trust & DRP</strong>
            </div>
        </div>
    </div>

    {{-- NAVIGATION TABS --}}
    <div class="fw-nav-container">
        <ul class="fw-nav">
            <li>
                <button class="fw-tab active" data-tab="tab-explorer">
                    <i class="fas fa-list-check"></i>
                    <span>Eksplorasi Ketentuan Teknis ({{ $totalReqs }})</span>
                </button>
            </li>
            <li>
                <button class="fw-tab" data-tab="tab-appendices">
                    <i class="fas fa-folder-open"></i>
                    <span>Daftar Format Lampiran ({{ $totalApps }})</span>
                </button>
            </li>
            <li>
                <button class="fw-tab" data-tab="tab-perspectives">
                    <i class="fas fa-users-cog"></i>
                    <span>Panduan Peran (BoD, SysAdmin, Legal)</span>
                </button>
            </li>
        </ul>
    </div>

    {{-- TAB 1: EXPLORER --}}
    <div class="fw-tab-content" id="tab-explorer">
        <div class="fw-workspace">
            {{-- LEFT COLUMN: CLAUSE NAVIGATOR --}}
            <div class="fw-list-panel">
                <div class="fw-search-box">
                    <div class="fw-search-input-wrap">
                        <i class="fas fa-search"></i>
                        <input
                            type="text"
                            id="fw-search-input"
                            class="fw-search-input"
                            placeholder="Cari ketentuan, SLA, kata kunci..."
                            autocomplete="off"
                        >
                        <span class="fw-search-kbd">/</span>
                    </div>
                    <select id="fw-filter-select" class="fw-filter-select">
                        <option value="">Semua Pilar ({{ $pillars->count() }} Pilar)</option>
                        @foreach($pillars as $p)
                            <option value="{{ $p }}">{{ Str::limit($p, 36) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="fw-counter-bar">
                    <span id="fw-counter">{{ $totalReqs }} dari {{ $totalReqs }} ditampilkan</span>
                    <span class="text-muted small"><i class="fas fa-arrows-alt-v me-1"></i> Gunakan ↑/↓ keyboard</span>
                </div>

                <div class="fw-list" id="fw-items-container">
                    @foreach($requirements as $index => $req)
                        @php
                            $searchIndex = strtolower(($req['id'] ?? '') . ' ' . ($req['pillar'] ?? '') . ' ' . ($req['title'] ?? '') . ' ' . ($req['summary'] ?? ''));
                        @endphp
                        <button
                            type="button"
                            class="fw-item {{ $index === 0 ? 'active' : '' }}"
                            data-group="{{ $req['pillar'] ?? '' }}"
                            data-search-index="{{ $searchIndex }}"
                            data-id="{{ $req['id'] ?? '' }}"
                            data-pillar="{{ $req['pillar'] ?? 'Teknis' }}"
                            data-title="{{ $req['title'] ?? '' }}"
                            data-summary="{{ $req['summary'] ?? '' }}"
                            data-cadence="{{ $req['cadence'] ?? 'Berlaku 2026' }}"
                            data-reporting="{{ $req['reporting'] ?? '' }}"
                            data-focus='@json($req['focus'] ?? [])'
                            data-evidence='@json($req['evidence'] ?? [])'
                            data-bod="{{ $req['roleTranslations']['bod'] ?? '' }}"
                            data-sysadmin="{{ $req['roleTranslations']['sysadmin'] ?? '' }}"
                            data-legal="{{ $req['roleTranslations']['legal'] ?? '' }}"
                        >
                            <div class="fw-item-top">
                                <span class="fw-item-code">{{ $req['id'] }}</span>
                                <span class="fw-item-tag">{{ Str::limit($req['pillar'] ?? 'Teknis', 16) }}</span>
                            </div>
                            <div class="fw-item-title">{{ $req['title'] }}</div>
                            <div class="fw-item-desc">{{ Str::limit($req['summary'], 95) }}</div>
                        </button>
                    @endforeach
                </div>
            </div>

            {{-- RIGHT COLUMN: INSPECTOR & READER --}}
            <div class="fw-inspector">
                <div class="fw-inspector-head">
                    <div class="fw-inspector-actions">
                        <span class="fw-badge-legal" id="insp-id">PADK</span>
                        <div class="d-flex align-items-center gap-2">
                            <button type="button" class="btn btn-sm btn-outline-primary" data-copy-target="insp-summary" title="Salin ketentuan">
                                <i class="fas fa-copy me-1"></i> Salin Teks
                            </button>
                            <a href="{{ route('analysis.cross-mapping') }}" class="btn btn-sm btn-outline-secondary" title="Buka pemetaan regulasi silang">
                                <i class="fas fa-network-wired me-1"></i> Cross-Mapping
                            </a>
                        </div>
                    </div>
                    <h2 class="fw-inspector-title" id="insp-title">Pilih Ketentuan</h2>
                    <div class="fw-meta-chips">
                        <span class="fw-meta-chip"><i class="fas fa-layer-group text-muted"></i> <span id="insp-pillar">Pilar</span></span>
                        <span class="fw-meta-chip"><i class="fas fa-clock text-muted"></i> <span id="insp-cadence">Tenggat</span></span>
                    </div>
                </div>

                {{-- SUMMARY --}}
                <div class="fw-section">
                    <div class="fw-section-title"><i class="fas fa-align-left text-primary"></i> Ringkasan Ketentuan Operasional</div>
                    <div class="fw-body-text" id="insp-summary">-</div>
                </div>

                {{-- REPORTING OBLIGATION --}}
                <div class="fw-section">
                    <div class="fw-section-title"><i class="fas fa-bullhorn text-warning"></i> Mandat Pelaporan & Notifikasi OJK</div>
                    <div class="p-3 bg-light rounded-3 text-secondary" style="font-size: 0.88rem; line-height: 1.55;" id="insp-reporting">-</div>
                </div>

                {{-- FOCUS --}}
                <div class="fw-section">
                    <div class="fw-section-title"><i class="fas fa-bullseye text-danger"></i> Fokus Implementasi Teknis</div>
                    <ul class="fw-bullets-list" id="insp-focus"></ul>
                </div>

                {{-- EVIDENCE --}}
                <div class="fw-section">
                    <div class="fw-section-title"><i class="fas fa-folder-check text-success"></i> Berkas & Bukti Kepatuhan Wajib</div>
                    <ul class="fw-bullets-list" id="insp-evidence"></ul>
                </div>

                {{-- ROLE IMPACTS --}}
                <div class="fw-section">
                    <div class="fw-section-title"><i class="fas fa-users-cog text-indigo"></i> Pembagian Tanggung Jawab</div>
                    <div class="fw-role-grid">
                        <div class="fw-role-card bod">
                            <strong><i class="fas fa-crown text-purple"></i> Direksi & Dewan</strong>
                            <p id="insp-bod">-</p>
                        </div>
                        <div class="fw-role-card sysadmin">
                            <strong><i class="fas fa-terminal text-blue"></i> Tim IT & Keamanan</strong>
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

    {{-- TAB 2: APPENDICES & FORMATS --}}
    <div class="fw-tab-content d-none" id="tab-appendices">
        <div class="row g-3">
            @foreach($appendices as $app)
                <div class="col-md-6">
                    <div class="card p-3 shadow-sm border-0 h-100">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <span class="badge bg-teal-lt fw-bold font-monospace">{{ $app['id'] ?? 'Format' }}</span>
                            <span class="badge bg-secondary-lt">{{ $app['category'] ?? 'Format Resmi' }}</span>
                        </div>
                        <h4 class="h5 text-dark mb-1">{{ $app['title'] ?? '' }}</h4>
                        <p class="text-muted small mb-2">{{ $app['summary'] ?? '' }}</p>
                        @if(!empty($app['key_fields']))
                            <div class="mt-2 pt-2 border-top">
                                <span class="small fw-bold text-secondary d-block mb-1">Komponen Kunci:</span>
                                <ul class="small ps-3 text-muted mb-0">
                                    @foreach($app['key_fields'] as $kf)
                                        <li>{{ $kf }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    {{-- TAB 3: PERSPECTIVES --}}
    <div class="fw-tab-content d-none" id="tab-perspectives">
        <div class="row g-3">
            <div class="col-md-4">
                <div class="card p-3 shadow-sm border-0 border-top border-4 border-purple h-100">
                    <h4 class="h5 text-purple mb-2"><i class="fas fa-crown me-2"></i> Dewan Pengurus (BoD & BoC)</h4>
                    <p class="text-muted small">Mandat strategis Direksi pada implementasi PADK 1/2026:</p>
                    <ul class="small text-muted ps-3 mb-0">
                        <li class="mb-2">Memastikan alokasi anggaran mencukupi untuk pemenuhan batas SLA patch 14 hari.</li>
                        <li class="mb-2">Mengesahkan penunjukan CISO / Kepala Satuan Kerja Keamanan Informasi independen.</li>
                        <li class="mb-2">Menyetujui evaluasi risiko penempatan sistem/data center lintas batas negara.</li>
                        <li>Memantau ketahanan simulasi ransomware skenario terburuk secara tahunan.</li>
                    </ul>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card p-3 shadow-sm border-0 border-top border-4 border-primary h-100">
                    <h4 class="h5 text-primary mb-2"><i class="fas fa-terminal me-2"></i> Tim Operasional & SysAdmin</h4>
                    <p class="text-muted small">Tugas teknis administrator jaringan, sistem, dan pertahanan siber:</p>
                    <ul class="small text-muted ps-3 mb-0">
                        <li class="mb-2">Mengisolasi server kritis dan menerapkan Zero Trust Network Architecture (ZTNA).</li>
                        <li class="mb-2">Mengeksekusi patching CVE berstatus Known Exploited Vulnerability dalam batas 14 hari.</li>
                        <li class="mb-2">Mengaktifkan MFA di seluruh akses SSH, RDP, VPN, dan portal web perbankan.</li>
                        <li>Memastikan air-gapped / immutable backup terlindungi dari enkripsi ransomware.</li>
                    </ul>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card p-3 shadow-sm border-0 border-top border-4 border-warning h-100">
                    <h4 class="h5 text-warning text-dark mb-2"><i class="fas fa-scale-balanced me-2"></i> Legal & Compliance</h4>
                    <p class="text-muted small">Fungsi hukum dan pemenuhan dokumen audit:</p>
                    <ul class="small text-muted ps-3 mb-0">
                        <li class="mb-2">Menyusun klausul addendum SLA PPJTI pihak ketiga selaras dengan PADK 1/2026.</li>
                        <li class="mb-2">Mengisi dan menyampaikan 17 subformat LKTPTI tepat waktu (tenggat 21 Januari).</li>
                        <li class="mb-2">Memastikan notifikasi insiden nonsiber tersampaikan ke OJK dalam waktu 24 jam.</li>
                        <li>Menyiapkan izin penempatan data luar negeri menggunakan berkas Format 4.1/4.2.</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('js/framework-reader.js') }}"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    // Inspector
    const inspId = document.getElementById('insp-id');
    const inspPillar = document.getElementById('insp-pillar');
    const inspTitle = document.getElementById('insp-title');
    const inspSummary = document.getElementById('insp-summary');
    const inspCadence = document.getElementById('insp-cadence');
    const inspReporting = document.getElementById('insp-reporting');
    const inspFocus = document.getElementById('insp-focus');
    const inspEvidence = document.getElementById('insp-evidence');
    const inspBod = document.getElementById('insp-bod');
    const inspSysadmin = document.getElementById('insp-sysadmin');
    const inspLegal = document.getElementById('insp-legal');

    new FrameworkReader({
        itemSelector: '.fw-item',
        searchInputId: 'fw-search-input',
        filterSelectId: 'fw-filter-select',
        counterId: 'fw-counter',
        onSelect: function (el) {
            if (inspId) inspId.textContent = el.dataset.id || '-';
            if (inspPillar) inspPillar.textContent = el.dataset.pillar || 'Teknis';
            if (inspTitle) inspTitle.textContent = el.dataset.title || '';
            if (inspSummary) inspSummary.textContent = el.dataset.summary || '-';
            if (inspCadence) inspCadence.textContent = el.dataset.cadence || 'Berlaku 2026';
            if (inspReporting) inspReporting.textContent = el.dataset.reporting || 'Dokumentasikan kepatuhan sebagai bagian dari laporan kondisi TI ke OJK.';

            if (inspFocus) {
                inspFocus.innerHTML = '';
                try {
                    const focusArr = JSON.parse(el.dataset.focus || '[]');
                    focusArr.forEach(f => {
                        const li = document.createElement('li');
                        li.textContent = f;
                        inspFocus.appendChild(li);
                    });
                } catch(e) {}
            }

            if (inspEvidence) {
                inspEvidence.innerHTML = '';
                try {
                    const evArr = JSON.parse(el.dataset.evidence || '[]');
                    evArr.forEach(ev => {
                        const li = document.createElement('li');
                        li.textContent = ev;
                        inspEvidence.appendChild(li);
                    });
                } catch(e) {}
            }

            if (inspBod) inspBod.textContent = el.dataset.bod || '-';
            if (inspSysadmin) inspSysadmin.textContent = el.dataset.sysadmin || '-';
            if (inspLegal) inspLegal.textContent = el.dataset.legal || '-';
        }
    });
});
</script>
@endpush
