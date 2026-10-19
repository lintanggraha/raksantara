@extends('layouts.tabler')

@section('title', 'ISO 37001:2016 Anti-Bribery Management System - Raksantara')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/framework-reader.css') }}">
@endpush

@section('content')
@php
    $items = collect($controls);
    $totalControls = $items->count();
    $domains = $items->groupBy('domain');
    $priorities = $items->groupBy('priority');
    $criticalCount = ($priorities->get('Tinggi') ?? collect())->count();
    $evidenceCount = $items->flatMap(fn($i) => $i['exampleEvidence'] ?? [])->count();
    $firstControl = $items->first();

    $domainPalette = [
        'Konteks & Risiko' => '#0f766e',
        'Kepemimpinan & Komitmen' => '#2563eb',
        'Dukungan & Sumber Daya' => '#7c3aed',
        'Kontrol Operasional' => '#d97706',
        'Evaluasi & Peningkatan' => '#dc2626',
    ];
@endphp

<div class="fw-page">
    {{-- COMPACT MODERN HERO --}}
    <div class="fw-hero">
        <div class="fw-hero-header">
            <div class="fw-kicker">
                <i class="fas fa-handshake-simple"></i> Standar Internasional SMAP
            </div>
            <span class="badge bg-success-lt text-success fw-bold px-3 py-1 rounded-pill">Edisi: ISO 37001:2016</span>
        </div>
        <h1 class="fw-title">ISO 37001:2016 Anti-Bribery Management System</h1>
        <p class="fw-lede">
            Sistem Manajemen Anti-Penyuapan (SMAP). Memetakan tata kelola integritas terpadu: penilaian risiko suap, komitmen kepemimpinan puncak, uji kelayakan (due diligence) pihak ketiga, kontrol keuangan & non-keuangan, kanal whistleblowing terproteksi, investigasi independen, serta audit kepatuhan.
        </p>
        <div class="fw-metric-row">
            <div class="fw-metric-pill">
                <span>Total Klausul:</span>
                <strong>{{ $totalControls }} Persyaratan</strong>
            </div>
            <div class="fw-metric-pill">
                <span>Domain SMAP:</span>
                <strong>{{ $domains->count() }} Kategori Utama</strong>
            </div>
            <div class="fw-metric-pill">
                <span>Prioritas Tinggi:</span>
                <strong>{{ $criticalCount }} Kontrol Esensial</strong>
            </div>
            <div class="fw-metric-pill">
                <span>Katalog Bukti:</span>
                <strong>{{ $evidenceCount }} Indikator Evidence</strong>
            </div>
        </div>
    </div>

    {{-- NAVIGATION TABS --}}
    <div class="fw-nav-container">
        <ul class="fw-nav">
            <li>
                <button class="fw-tab active" data-tab="tab-explorer">
                    <i class="fas fa-list-check"></i>
                    <span>Eksplorasi Kontrol SMAP ({{ $totalControls }})</span>
                </button>
            </li>
            <li>
                <button class="fw-tab" data-tab="tab-overview">
                    <i class="fas fa-cubes"></i>
                    <span>5 Domain Tata Kelola & Distribusi</span>
                </button>
            </li>
            <li>
                <button class="fw-tab" data-tab="tab-governance">
                    <i class="fas fa-users-gear"></i>
                    <span>Panduan Peran (Board, IT, Legal)</span>
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
                            placeholder="Cari ID klausa, due diligence, whistleblowing..."
                            autocomplete="off"
                        >
                        <span class="fw-search-kbd">/</span>
                    </div>
                    <select id="fw-filter-select" class="fw-filter-select">
                        <option value="">Semua Kategori ({{ $domains->count() }} Domain)</option>
                        @foreach($domains as $domKey => $domItems)
                            <option value="{{ $domKey }}">{{ $domKey }} ({{ $domItems->count() }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="fw-counter-bar">
                    <span id="fw-counter">{{ $totalControls }} dari {{ $totalControls }} kontrol ditampilkan</span>
                    <span class="text-muted small"><i class="fas fa-arrows-alt-v me-1"></i> Gunakan ↑/↓ keyboard</span>
                </div>

                <div class="fw-list" id="fw-items-container">
                    @foreach($controls as $index => $ctrl)
                        @php
                            $searchIndex = strtolower(($ctrl['id'] ?? '') . ' ' . ($ctrl['name'] ?? '') . ' ' . ($ctrl['domain'] ?? '') . ' ' . ($ctrl['description'] ?? '') . ' ' . ($ctrl['capability'] ?? ''));
                            $bod = $ctrl['roleTranslations']['bod'] ?? ($ctrl['roleTranslations']['board'] ?? '');
                            $sysadmin = $ctrl['roleTranslations']['sysadmin'] ?? '';
                            $legal = $ctrl['roleTranslations']['legal'] ?? '';
                        @endphp
                        <button
                            type="button"
                            class="fw-item {{ $index === 0 ? 'active' : '' }}"
                            data-group="{{ $ctrl['domain'] ?? '' }}"
                            data-search-index="{{ $searchIndex }}"
                            data-id="{{ $ctrl['id'] ?? '' }}"
                            data-name="{{ $ctrl['name'] ?? '' }}"
                            data-domain="{{ $ctrl['domain'] ?? '-' }}"
                            data-priority="{{ $ctrl['priority'] ?? 'Sedang' }}"
                            data-type="{{ $ctrl['type'] ?? 'Preventif' }}"
                            data-capability="{{ $ctrl['capability'] ?? '-' }}"
                            data-description="{{ $ctrl['description'] ?? '' }}"
                            data-interpretation="{{ $ctrl['implementationTips'] ?? '' }}"
                            data-analogy="{{ $ctrl['analogy'] ?? '' }}"
                            data-evidence='@json($ctrl['exampleEvidence'] ?? [])'
                            data-bod="{{ $bod }}"
                            data-sysadmin="{{ $sysadmin }}"
                            data-legal="{{ $legal }}"
                        >
                            <div class="fw-item-top">
                                <span class="fw-item-code">{{ $ctrl['id'] }}</span>
                                <span class="fw-item-tag">{{ $ctrl['domain'] ?? '-' }}</span>
                            </div>
                            <div class="fw-item-title">{{ $ctrl['name'] ?? '' }}</div>
                            <div class="fw-item-desc">{{ Str::limit($ctrl['description'] ?? '', 95) }}</div>
                        </button>
                    @endforeach
                </div>
            </div>

            {{-- RIGHT COLUMN: INSPECTOR PANEL --}}
            <div class="fw-inspector">
                <div class="fw-inspector-head">
                    <div class="fw-inspector-actions">
                        <span class="fw-badge-legal" id="insp-id">{{ $firstControl['id'] ?? '-' }}</span>
                        <div class="d-flex align-items-center gap-2">
                            <button type="button" class="btn btn-sm btn-outline-primary" data-copy-target="insp-description" title="Salin deskripsi kontrol">
                                <i class="fas fa-copy me-1"></i> Salin Teks
                            </button>
                            <a href="{{ route('analysis.cross-mapping') }}" class="btn btn-sm btn-outline-secondary" title="Buka pemetaan regulasi silang">
                                <i class="fas fa-network-wired me-1"></i> Cross-Mapping
                            </a>
                        </div>
                    </div>
                    <h2 class="fw-inspector-title" id="insp-name">{{ $firstControl['name'] ?? 'Pilih Kontrol' }}</h2>
                    <div class="fw-meta-chips">
                        <span class="fw-meta-chip"><i class="fas fa-layer-group text-primary"></i> <span id="insp-domain">-</span></span>
                        <span class="fw-meta-chip"><i class="fas fa-flag text-danger"></i> <span id="insp-priority">-</span></span>
                        <span class="fw-meta-chip"><i class="fas fa-shield-alt text-info"></i> <span id="insp-type">-</span></span>
                    </div>
                </div>

                {{-- DESCRIPTION --}}
                <div class="fw-section">
                    <div class="fw-section-title"><i class="fas fa-align-left text-primary"></i> Deskripsi Persyaratan SMAP</div>
                    <div class="fw-body-text" id="insp-description">{{ $firstControl['description'] ?? '-' }}</div>
                </div>

                {{-- IMPLEMENTATION TIPS --}}
                <div class="fw-section">
                    <div class="fw-section-title"><i class="fas fa-lightbulb text-warning"></i> Panduan & Tips Implementasi</div>
                    <div class="p-3 bg-light rounded-3 text-secondary" style="font-size: 0.88rem; line-height: 1.55;" id="insp-interpretation">-</div>
                </div>

                {{-- ANALOGY --}}
                <div class="fw-section">
                    <div class="fw-section-title"><i class="fas fa-comment-dots text-info"></i> Analogi Operasional</div>
                    <div class="p-3 bg-light rounded-3 text-secondary" style="font-size: 0.88rem; line-height: 1.55;" id="insp-analogy">-</div>
                </div>

                {{-- EVIDENCE NEEDED --}}
                <div class="fw-section">
                    <div class="fw-section-title"><i class="fas fa-folder-check text-success"></i> Indikator Bukti Audit (Example Evidence)</div>
                    <ul class="fw-bullets-list" id="insp-evidence"></ul>
                </div>

                {{-- ROLE IMPACT CARDS --}}
                <div class="fw-section">
                    <div class="fw-section-title"><i class="fas fa-users-cog text-indigo"></i> Pembagian Tanggung Jawab Peran</div>
                    <div class="fw-role-grid">
                        <div class="fw-role-card bod">
                            <strong><i class="fas fa-crown text-purple"></i> Dewan Direksi & Pengawas</strong>
                            <p id="insp-bod">-</p>
                        </div>
                        <div class="fw-role-card sysadmin">
                            <strong><i class="fas fa-terminal text-blue"></i> Tim IT & Operasional</strong>
                            <p id="insp-sysadmin">-</p>
                        </div>
                        <div class="fw-role-card legal">
                            <strong><i class="fas fa-scale-balanced text-yellow"></i> Legal & Fungsi Kepatuhan SMAP</strong>
                            <p id="insp-legal">-</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- TAB 2: OVERVIEW & 5 DOMAINS --}}
    <div class="fw-tab-content d-none" id="tab-overview">
        <div class="row g-3">
            @foreach($domains as $domName => $domCtrls)
                @php
                    $dCount = $domCtrls->count();
                    $color = $domainPalette[$domName] ?? '#059669';
                @endphp
                <div class="col-md-6 col-lg-4">
                    <div class="card p-3 h-100 shadow-sm border-0">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <span class="badge" style="background-color: {{ $color }}20; color: {{ $color }}; font-weight: 700;">
                                <i class="fas fa-folder-open me-1"></i> {{ $domName }}
                            </span>
                            <span class="badge bg-light text-dark border">{{ $dCount }} Kontrol</span>
                        </div>
                        <h4 class="h5 text-dark mb-1">{{ $domName }}</h4>
                        <p class="text-muted small mb-0">{{ $domCtrls->first()['capability'] ?? 'Persyaratan klausul SMAP' }}</p>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    {{-- TAB 3: GOVERNANCE & ROLES --}}
    <div class="fw-tab-content d-none" id="tab-governance">
        <div class="row g-3">
            <div class="col-lg-4">
                <div class="card p-3 h-100 shadow-sm border-0">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <h4 class="h5 text-dark mb-0"><i class="fas fa-crown text-purple me-2"></i> Dewan Pengurus & Manajemen Puncak</h4>
                        <span class="badge bg-purple-lt">Governance</span>
                    </div>
                    <p class="text-muted small mb-3">Tanggung jawab utama kepemimpinan puncak:</p>
                    <ul class="list-unstyled d-flex flex-column gap-2 small text-secondary">
                        <li class="p-2 rounded bg-light border"><i class="fas fa-check text-success me-2"></i> Menetapkan kebijakan anti-penyuapan dan budaya no-compromise.</li>
                        <li class="p-2 rounded bg-light border"><i class="fas fa-check text-success me-2"></i> Menunjuk Fungsi Kepatuhan Anti-Penyuapan yang independen dan berwenang.</li>
                        <li class="p-2 rounded bg-light border"><i class="fas fa-check text-success me-2"></i> Meninjau hasil audit internal SMAP dan investigasi dugaan suap berkala.</li>
                    </ul>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card p-3 h-100 shadow-sm border-0">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <h4 class="h5 text-dark mb-0"><i class="fas fa-terminal text-primary me-2"></i> Tim IT & Operasional Keuangan</h4>
                        <span class="badge bg-primary-lt">Controls</span>
                    </div>
                    <p class="text-muted small mb-3">Pengendalian teknis dan operasional:</p>
                    <ul class="list-unstyled d-flex flex-column gap-2 small text-secondary">
                        <li class="p-2 rounded bg-light border"><i class="fas fa-check text-success me-2"></i> Pengendalian keuangan bertingkat (dual authorization / four-eyes principle).</li>
                        <li class="p-2 rounded bg-light border"><i class="fas fa-check text-success me-2"></i> Pengoperasian kanal whistleblowing sistem yang anonim, aman, dan terenkripsi.</li>
                        <li class="p-2 rounded bg-light border"><i class="fas fa-check text-success me-2"></i> Audit trail sistem procurement dan pencatatan transaksi tanpa pengecualian.</li>
                    </ul>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card p-3 h-100 shadow-sm border-0">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <h4 class="h5 text-dark mb-0"><i class="fas fa-scale-balanced text-warning me-2"></i> Tim Legal & Fungsi Kepatuhan</h4>
                        <span class="badge bg-warning-lt">Compliance</span>
                    </div>
                    <p class="text-muted small mb-3">Uji tuntas dan legalitas integritas:</p>
                    <ul class="list-unstyled d-flex flex-column gap-2 small text-secondary">
                        <li class="p-2 rounded bg-light border"><i class="fas fa-check text-success me-2"></i> Pelaksanaan due diligence atas mitra bisnis, vendor, dan kandidat personel.</li>
                        <li class="p-2 rounded bg-light border"><i class="fas fa-check text-success me-2"></i> Pencantuman klausul anti-bribery dan hak audit pada setiap kontrak kerja sama.</li>
                        <li class="p-2 rounded bg-light border"><i class="fas fa-check text-success me-2"></i> Investigasi formal atas laporan whistleblowing tanpa intervensi operasional.</li>
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
    const inspId = document.getElementById('insp-id');
    const inspName = document.getElementById('insp-name');
    const inspDomain = document.getElementById('insp-domain');
    const inspPriority = document.getElementById('insp-priority');
    const inspType = document.getElementById('insp-type');
    const inspDescription = document.getElementById('insp-description');
    const inspInterpretation = document.getElementById('insp-interpretation');
    const inspAnalogy = document.getElementById('insp-analogy');
    const inspEvidence = document.getElementById('insp-evidence');
    const inspBod = document.getElementById('insp-bod');
    const inspSysadmin = document.getElementById('insp-sysadmin');
    const inspLegal = document.getElementById('insp-legal');

    new FrameworkReader({
        onItemSelect: function(el) {
            if (inspId) inspId.textContent = el.dataset.id || '-';
            if (inspName) inspName.textContent = el.dataset.name || 'Pilih Kontrol';
            if (inspDomain) inspDomain.textContent = el.dataset.domain || '-';
            if (inspPriority) inspPriority.textContent = `Prioritas ${el.dataset.priority || '-'}`;
            if (inspType) inspType.textContent = el.dataset.type || '-';
            if (inspDescription) inspDescription.textContent = el.dataset.description || '-';
            if (inspInterpretation) inspInterpretation.textContent = el.dataset.interpretation || 'Lakukan audit dan verifikasi berkala atas klausul ini.';
            if (inspAnalogy) inspAnalogy.textContent = el.dataset.analogy || 'Patuhi prinsip integritas dan pencegahan suap di seluruh tingkatan organisasi.';

            // Evidence
            if (inspEvidence) {
                inspEvidence.innerHTML = '';
                try {
                    const evArr = JSON.parse(el.dataset.evidence || '[]');
                    if (evArr.length === 0) {
                        inspEvidence.innerHTML = '<li class="text-muted">Dokumentasikan kebijakan SMAP, formulir due diligence, atau catatan audit.</li>';
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
            if (inspBod) inspBod.textContent = el.dataset.bod || 'Dukungan kepemimpinan dan alokasi sumber daya fungsi kepatuhan SMAP.';
            if (inspSysadmin) inspSysadmin.textContent = el.dataset.sysadmin || 'Pengendalian teknis approval transaksi dan pengamanan kanal pelaporan.';
            if (inspLegal) inspLegal.textContent = el.dataset.legal || 'Due diligence pihak ketiga, klausul kontrak, dan tindak lanjut laporan dugaan suap.';
        }
    });
});
</script>
@endpush
