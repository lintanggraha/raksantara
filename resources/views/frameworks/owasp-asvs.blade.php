@extends('layouts.tabler')

@section('title', 'OWASP ASVS 5.0.0 Verification Standard - Raksantara')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/framework-reader.css') }}">
@endpush

@section('content')
@php
    $items = collect($requirements);
    $apps = collect($appendices);
    $totalChapters = $items->count();
    $pillars = $items->pluck('pillar')->unique();
    $firstItem = $items->first();
@endphp

<div class="fw-page">
    {{-- COMPACT MODERN HERO --}}
    <div class="fw-hero">
        <div class="fw-hero-header">
            <div class="fw-kicker">
                <i class="fas fa-check-double"></i> Standar Verifikasi Aplikasi Web Global
            </div>
            <span class="badge bg-success-lt text-success fw-bold px-3 py-1 rounded-pill">Edisi: OWASP ASVS 5.0.0</span>
        </div>
        <h1 class="fw-title">OWASP ASVS 5.0.0 Verification Standard</h1>
        <p class="fw-lede">
            Application Security Verification Standard. Standar pengujian kontrol keamanan aplikasi web enterprise paling komprehensif. Menetapkan tolok ukur pengujian ketat untuk 17 domain teknis (V1 s/d V17) di 3 level verifikasi (L1 Opportunistic, L2 Standard Enterprise, L3 Critical Advanced).
        </p>
        <div class="fw-metric-row">
            <div class="fw-metric-pill">
                <span>Bab Verifikasi:</span>
                <strong>{{ $totalChapters }} Bab Kontrol (V1-V17)</strong>
            </div>
            <div class="fw-metric-pill">
                <span>Level Pengujian:</span>
                <strong>L1, L2, L3 Bertingkat</strong>
            </div>
            <div class="fw-metric-pill">
                <span>Pilar Teknis:</span>
                <strong>{{ $pillars->count() }} Pilar Pengamanan</strong>
            </div>
            <div class="fw-metric-pill">
                <span>Sasaran Pengguna:</span>
                <strong>Pentester, QA, Architect</strong>
            </div>
        </div>
    </div>

    {{-- NAVIGATION TABS --}}
    <div class="fw-nav-container">
        <ul class="fw-nav">
            <li>
                <button class="fw-tab active" data-tab="tab-explorer">
                    <i class="fas fa-list-check"></i>
                    <span>Eksplorasi 17 Bab Verifikasi ({{ $totalChapters }})</span>
                </button>
            </li>
            <li>
                <button class="fw-tab" data-tab="tab-levels">
                    <i class="fas fa-layer-group"></i>
                    <span>3 Tingkat Level Verifikasi (L1, L2, L3)</span>
                </button>
            </li>
            <li>
                <button class="fw-tab" data-tab="tab-overview">
                    <i class="fas fa-cubes"></i>
                    <span>Pilar Teknis & Matriks Tanggung Jawab</span>
                </button>
            </li>
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
                            placeholder="Cari bab verifikasi (misal: Encoding, API, Auth, Crypto)..."
                            autocomplete="off"
                        >
                        <span class="fw-search-kbd">/</span>
                    </div>
                    <select id="fw-filter-select" class="fw-filter-select">
                        <option value="">Semua Pilar ({{ $pillars->count() }} Pilar)</option>
                        @foreach($pillars as $pil)
                            <option value="{{ $pil }}">{{ $pil }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="fw-counter-bar">
                    <span id="fw-counter">{{ $totalChapters }} dari {{ $totalChapters }} bab ditampilkan</span>
                    <span class="text-muted small"><i class="fas fa-arrows-alt-v me-1"></i> Gunakan ↑/↓ keyboard</span>
                </div>

                <div class="fw-list" id="fw-items-container">
                    @foreach($items as $index => $item)
                        @php
                            $searchIndex = strtolower(($item['id'] ?? '') . ' ' . ($item['title'] ?? '') . ' ' . ($item['summary'] ?? '') . ' ' . ($item['chapter_title'] ?? '') . ' ' . ($item['pillar'] ?? ''));
                            $bod = $item['roleTranslations']['bod'] ?? ($item['roleTranslations']['board'] ?? '');
                            $sysadmin = $item['roleTranslations']['sysadmin'] ?? '';
                            $legal = $item['roleTranslations']['legal'] ?? '';
                        @endphp
                        <button
                            type="button"
                            class="fw-item {{ $index === 0 ? 'active' : '' }}"
                            data-group="{{ $item['pillar'] ?? '' }}"
                            data-search-index="{{ $searchIndex }}"
                            data-id="{{ $item['id'] ?? '' }}"
                            data-title="{{ $item['title'] ?? '' }}"
                            data-chapter="{{ $item['chapter_title'] ?? $item['id'] }}"
                            data-pillar="{{ $item['pillar'] ?? 'Security' }}"
                            data-scoring="{{ $item['scoring'] ?? '-' }}"
                            data-cadence="{{ $item['cadence'] ?? '-' }}"
                            data-summary="{{ $item['summary'] ?? '' }}"
                            data-analogy="{{ $item['analogy'] ?? '' }}"
                            data-focus='@json($item['focus'] ?? [])'
                            data-evidence='@json($item['evidence'] ?? [])'
                            data-reporting="{{ $item['reporting'] ?? '-' }}"
                            data-appendices='@json($item['appendices'] ?? [])'
                            data-bod="{{ $bod }}"
                            data-sysadmin="{{ $sysadmin }}"
                            data-legal="{{ $legal }}"
                        >
                            <div class="fw-item-top">
                                <span class="fw-item-code">{{ $item['id'] }}</span>
                                <span class="fw-item-tag">{{ $item['pillar'] ?? '-' }}</span>
                            </div>
                            <div class="fw-item-title">{{ $item['chapter_title'] ?? $item['title'] }}</div>
                            <div class="fw-item-desc">{{ Str::limit($item['summary'] ?? '', 95) }}</div>
                        </button>
                    @endforeach
                </div>
            </div>

            {{-- RIGHT COLUMN: INSPECTOR PANEL --}}
            <div class="fw-inspector">
                <div class="fw-inspector-head">
                    <div class="fw-inspector-actions">
                        <span class="fw-badge-legal" id="insp-id">{{ $firstItem['id'] ?? '-' }}</span>
                        <div class="d-flex align-items-center gap-2">
                            <button type="button" class="btn btn-sm btn-outline-primary" data-copy-target="insp-summary" title="Salin ringkasan bab">
                                <i class="fas fa-copy me-1"></i> Salin Teks
                            </button>
                            <a href="{{ route('analysis.cross-mapping') }}" class="btn btn-sm btn-outline-secondary" title="Buka pemetaan regulasi silang">
                                <i class="fas fa-network-wired me-1"></i> Cross-Mapping
                            </a>
                        </div>
                    </div>
                    <h2 class="fw-inspector-title" id="insp-chapter">{{ $firstItem['chapter_title'] ?? $firstItem['title'] ?? 'Pilih Bab' }}</h2>
                    <div class="text-secondary small mt-1 mb-2 fw-medium" id="insp-title">{{ $firstItem['title'] ?? '' }}</div>
                    <div class="fw-meta-chips">
                        <span class="fw-meta-chip"><i class="fas fa-layer-group text-primary"></i> <span id="insp-pillar">-</span></span>
                        <span class="fw-meta-chip"><i class="fas fa-flag text-danger"></i> <span id="insp-scoring">-</span></span>
                        <span class="fw-meta-chip"><i class="fas fa-clock text-warning"></i> <span id="insp-cadence">-</span></span>
                    </div>
                </div>

                {{-- SUMMARY --}}
                <div class="fw-section">
                    <div class="fw-section-title"><i class="fas fa-align-left text-primary"></i> Deskripsi Persyaratan Verifikasi</div>
                    <div class="fw-body-text" id="insp-summary">{{ $firstItem['summary'] ?? '-' }}</div>
                </div>

                {{-- ANALOGY --}}
                <div class="fw-section">
                    <div class="fw-section-title"><i class="fas fa-lightbulb text-warning"></i> Analogi Operasional</div>
                    <div class="p-3 bg-light rounded-3 text-secondary" style="font-size: 0.88rem; line-height: 1.55;" id="insp-analogy">-</div>
                </div>

                {{-- FOCUS --}}
                <div class="fw-section">
                    <div class="fw-section-title"><i class="fas fa-bullseye text-danger"></i> Fokus Tolok Ukur Pengujian</div>
                    <ul class="fw-bullets-list" id="insp-focus"></ul>
                </div>

                {{-- EVIDENCE --}}
                <div class="fw-section">
                    <div class="fw-section-title"><i class="fas fa-folder-check text-success"></i> Dokumen Bukti & Evidencing Pengujian</div>
                    <ul class="fw-bullets-list" id="insp-evidence"></ul>
                </div>

                {{-- APPLICABLE LEVELS --}}
                <div class="fw-section">
                    <div class="fw-section-title"><i class="fas fa-layer-group text-info"></i> Level Verifikasi yang Berlaku</div>
                    <div class="d-flex flex-wrap gap-2" id="insp-levels"></div>
                </div>

                {{-- ROLE IMPACT CARDS --}}
                <div class="fw-section">
                    <div class="fw-section-title"><i class="fas fa-users-cog text-indigo"></i> Pembagian Tanggung Jawab Peran</div>
                    <div class="fw-role-grid">
                        <div class="fw-role-card bod">
                            <strong><i class="fas fa-crown text-purple"></i> Manajemen & Product Owner</strong>
                            <p id="insp-bod">-</p>
                        </div>
                        <div class="fw-role-card sysadmin">
                            <strong><i class="fas fa-terminal text-blue"></i> Tim Developer & SysAdmin</strong>
                            <p id="insp-sysadmin">-</p>
                        </div>
                        <div class="fw-role-card legal">
                            <strong><i class="fas fa-scale-balanced text-yellow"></i> QA, Pentester & Legal</strong>
                            <p id="insp-legal">-</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- TAB 2: 3 VERIFICATION LEVELS --}}
    <div class="fw-tab-content d-none" id="tab-levels">
        <div class="row g-3">
            <div class="col-lg-4">
                <div class="card p-3 h-100 shadow-sm border-0">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <span class="badge bg-primary text-white font-monospace fs-6">Level 1 (L1)</span>
                        <span class="badge bg-primary-lt">Opportunistic</span>
                    </div>
                    <h4 class="h5 text-dark fw-bold mb-2">Penetration Test & Black Box</h4>
                    <p class="text-muted small mb-3">Tingkat pengujian minimum untuk seluruh aplikasi web. Dapat diuji tanpa akses ke kode sumber (black-box) untuk memvalidasi pertahanan terhadap ancaman otomatis dan kerentanan dasar.</p>
                    <div class="mt-auto pt-2 border-top">
                        <strong class="text-dark small d-block">Cocok untuk:</strong>
                        <span class="text-secondary small">Aplikasi informasi publik, landing page, atau sistem tanpa transaksi data sensitif.</span>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card p-3 h-100 shadow-sm border-0">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <span class="badge bg-success text-white font-monospace fs-6">Level 2 (L2)</span>
                        <span class="badge bg-success-lt">Standard Enterprise</span>
                    </div>
                    <h4 class="h5 text-dark fw-bold mb-2">Code Review & Architecture Analysis</h4>
                    <p class="text-muted small mb-3">Standar yang direkomendasikan untuk sebagian besar aplikasi bisnis dan B2B/B2C. Membutuhkan verifikasi berbasis akses ke kode sumber (white-box), arsitektur, dan konfigurasi.</p>
                    <div class="mt-auto pt-2 border-top">
                        <strong class="text-dark small d-block">Cocok untuk:</strong>
                        <span class="text-secondary small">Aplikasi perbankan, e-commerce, portal healthcare, atau sistem yang memproses data PII penting.</span>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card p-3 h-100 shadow-sm border-0">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <span class="badge bg-danger text-white font-monospace fs-6">Level 3 (L3)</span>
                        <span class="badge bg-danger-lt">Advanced Critical</span>
                    </div>
                    <h4 class="h5 text-dark fw-bold mb-2">High Assurance & Critical Infrastructure</h4>
                    <p class="text-muted small mb-3">Tingkat verifikasi tertinggi untuk aplikasi sistemik dan infrastruktur informasi vital. Memerlukan pembuktian mendalam atas ketahanan terhadap penyerang berketerampilan tinggi.</p>
                    <div class="mt-auto pt-2 border-top">
                        <strong class="text-dark small d-block">Cocok untuk:</strong>
                        <span class="text-secondary small">Sistem pembayaran inti (core banking), sistem pertahanan, telekomunikasi nasional, atau rekam medis krusial.</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- TAB 3: TECHNICAL PILLARS --}}
    <div class="fw-tab-content d-none" id="tab-overview">
        <div class="row g-3">
            @foreach($pillars as $p)
                @php
                    $pItems = $items->where('pillar', $p);
                @endphp
                <div class="col-md-6 col-lg-3">
                    <div class="card p-3 h-100 shadow-sm border-0">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <span class="badge bg-primary-lt font-monospace fw-bold">{{ $p }}</span>
                            <span class="badge bg-light text-dark border">{{ $pItems->count() }} Bab</span>
                        </div>
                        <h4 class="h6 text-dark fw-bold mb-1">{{ $p }}</h4>
                        <div class="text-muted small mt-auto pt-2">
                            Mencakup bab: {{ $pItems->pluck('id')->join(', ') }}
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('js/framework-reader.js') }}"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const inspId = document.getElementById('insp-id');
    const inspChapter = document.getElementById('insp-chapter');
    const inspTitle = document.getElementById('insp-title');
    const inspPillar = document.getElementById('insp-pillar');
    const inspScoring = document.getElementById('insp-scoring');
    const inspCadence = document.getElementById('insp-cadence');
    const inspSummary = document.getElementById('insp-summary');
    const inspAnalogy = document.getElementById('insp-analogy');
    const inspFocus = document.getElementById('insp-focus');
    const inspEvidence = document.getElementById('insp-evidence');
    const inspLevels = document.getElementById('insp-levels');
    const inspBod = document.getElementById('insp-bod');
    const inspSysadmin = document.getElementById('insp-sysadmin');
    const inspLegal = document.getElementById('insp-legal');

    new FrameworkReader({
        onItemSelect: function(el) {
            if (inspId) inspId.textContent = el.dataset.id || '-';
            if (inspChapter) inspChapter.textContent = el.dataset.chapter || '-';
            if (inspTitle) inspTitle.textContent = el.dataset.title || '';
            if (inspPillar) inspPillar.textContent = el.dataset.pillar || '-';
            if (inspScoring) inspScoring.textContent = el.dataset.scoring || '-';
            if (inspCadence) inspCadence.textContent = el.dataset.cadence || '-';
            if (inspSummary) inspSummary.textContent = el.dataset.summary || '-';
            if (inspAnalogy) inspAnalogy.textContent = el.dataset.analogy || 'Terapkan verifikasi keamanan berlapis.';

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
                        inspEvidence.innerHTML = '<li class="text-muted">Laporan pengujian SAST/DAST, audit code review, atau checklist verifikasi.</li>';
                    } else {
                        evArr.forEach(ev => {
                            const li = document.createElement('li');
                            li.textContent = ev;
                            inspEvidence.appendChild(li);
                        });
                    }
                } catch(e) {}
            }

            // Levels
            if (inspLevels) {
                inspLevels.innerHTML = '';
                try {
                    const appsArr = JSON.parse(el.dataset.appendices || '[]');
                    if (appsArr.length === 0) {
                        inspLevels.innerHTML = '<span class="text-muted small">Berlaku untuk semua level verifikasi.</span>';
                    } else {
                        appsArr.forEach(lvl => {
                            const span = document.createElement('span');
                            span.className = 'badge bg-success-lt border px-3 py-1 font-monospace fw-bold';
                            span.textContent = `Level ${lvl}`;
                            inspLevels.appendChild(span);
                        });
                    }
                } catch(e) {}
            }

            // Roles
            if (inspBod) inspBod.textContent = el.dataset.bod || 'Pengawasan alokasi waktu dan pengujian kualitas rilis software.';
            if (inspSysadmin) inspSysadmin.textContent = el.dataset.sysadmin || 'Implementasi kontrol teknis, hardening server, dan integrasi pipeline pengujian.';
            if (inspLegal) inspLegal.textContent = el.dataset.legal || 'Verifikasi kepatuhan terhadap standar industri dan audit pemenuhan kontrak.';
        }
    });
});
</script>
@endpush
