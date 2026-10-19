@extends('layouts.tabler')

@section('title', 'ISO/IEC 27001:2022 Control Studio - Raksantara')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/framework-reader.css') }}">
@endpush

@section('content')
@php
    $controlCollection = collect($controls);
    $totalControls = $controlCollection->count();

    $domainMeta = [
        'Organisasional' => ['id' => 'ORG', 'color' => '#0f766e', 'icon' => 'fa-sitemap', 'summary' => 'Kebijakan, tata kelola, kontrak, dan keputusan manajemen.'],
        'Orang' => ['id' => 'PPL', 'color' => '#2563eb', 'icon' => 'fa-users', 'summary' => 'Kesadaran, perilaku, dan akuntabilitas personel.'],
        'Fisik' => ['id' => 'PHY', 'color' => '#d97706', 'icon' => 'fa-building-shield', 'summary' => 'Lokasi, perangkat, perimeter, dan perlindungan fisik operasional.'],
        'Teknologi' => ['id' => 'TEC', 'color' => '#4338ca', 'icon' => 'fa-microchip', 'summary' => 'Identitas, monitoring, hardening, konfigurasi, dan operasional teknis.'],
    ];

    $conceptMeta = [
        'Mengidentifikasi' => ['key' => 'Identify', 'color' => '#2563eb', 'icon' => 'fa-compass', 'summary' => 'Memahami aset, eksposur, dan konteks bisnis yang harus dijaga.'],
        'Melindungi' => ['key' => 'Protect', 'color' => '#0f766e', 'icon' => 'fa-shield-alt', 'summary' => 'Menjaga layanan, data, dan akses dengan pengamanan yang konsisten.'],
        'Mendeteksi' => ['key' => 'Detect', 'color' => '#d97706', 'icon' => 'fa-binoculars', 'summary' => 'Menangkap indikasi insiden dan anomali secepat mungkin.'],
        'Merespons' => ['key' => 'Respond', 'color' => '#dc2626', 'icon' => 'fa-bolt', 'summary' => 'Mengatur langkah taktis ketika kejadian ancaman terkonfirmasi.'],
        'Memulihkan' => ['key' => 'Recover', 'color' => '#16a34a', 'icon' => 'fa-life-ring', 'summary' => 'Mengembalikan layanan dan kapabilitas pasca gangguan.'],
        'Mengatur' => ['key' => 'Govern', 'color' => '#4338ca', 'icon' => 'fa-sitemap', 'summary' => 'Menetapkan arah, akuntabilitas, dan keputusan tingkat organisasi.'],
    ];

    $domainBreakdown = collect($domainStats ?? [])->map(function ($item) use ($domainMeta) {
        $meta = $domainMeta[$item['name']] ?? ['id' => 'GEN', 'color' => '#2563eb', 'icon' => 'fa-layer-group', 'summary' => 'Kontrol SMKI'];
        return [
            'name' => $item['name'],
            'id' => $meta['id'],
            'count' => $item['count'],
            'color' => $meta['color'],
            'icon' => $meta['icon'],
            'summary' => $meta['summary']
        ];
    })->values();

    $criticalControls = $controlCollection->where('priority', 'Kritis')->count();
    $evidenceCount = $controlCollection->pluck('exampleEvidence')->flatten(1)->filter()->count();
    $domains = $controlCollection->pluck('domain')->filter()->unique();
    $firstControl = $controlCollection->first();
@endphp

<div class="fw-page">
    {{-- COMPACT MODERN HERO --}}
    <div class="fw-hero">
        <div class="fw-hero-header">
            <div class="fw-kicker">
                <i class="fas fa-globe"></i> Standar Internasional ISMS
            </div>
            <span class="badge bg-success-lt text-success fw-bold px-3 py-1 rounded-pill">Edisi: ISO/IEC 27001:2022</span>
        </div>
        <h1 class="fw-title">ISO/IEC 27001:2022 Control Studio</h1>
        <p class="fw-lede">
            Standar global Sistem Manajemen Keamanan Informasi (SMKI / ISMS). Mengintegrasikan 93 kontrol Annex A dan klausul manajemen ke dalam 4 tema inti (Organisasional, Orang, Fisik, Teknologi) dengan panduan bukti audit, pembagian peran tanggung jawab, serta taksonomi atribut atributif.
        </p>
        <div class="fw-metric-row">
            <div class="fw-metric-pill">
                <span>Total Kontrol:</span>
                <strong>{{ $totalControls }} Kontrol & Klausa</strong>
            </div>
            <div class="fw-metric-pill">
                <span>Kategori Tema:</span>
                <strong>4 Tema Annex A</strong>
            </div>
            <div class="fw-metric-pill">
                <span>Prioritas Kritis:</span>
                <strong>{{ $criticalControls }} Kontrol Utama</strong>
            </div>
            <div class="fw-metric-pill">
                <span>Panduan Bukti Audit:</span>
                <strong>{{ $evidenceCount }} Item Evidence</strong>
            </div>
        </div>
    </div>

    {{-- NAVIGATION TABS --}}
    <div class="fw-nav-container">
        <ul class="fw-nav">
            <li>
                <button class="fw-tab active" data-tab="tab-explorer">
                    <i class="fas fa-list-check"></i>
                    <span>Eksplorasi Kontrol & Annex A ({{ $totalControls }})</span>
                </button>
            </li>
            <li>
                <button class="fw-tab" data-tab="tab-overview">
                    <i class="fas fa-cubes"></i>
                    <span>4 Kategori Tema & Distribusi</span>
                </button>
            </li>
            <li>
                <button class="fw-tab" data-tab="tab-concepts">
                    <i class="fas fa-compass"></i>
                    <span>Taksonomi Cybersecurity Concepts</span>
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
                            placeholder="Cari ID (mis. 5.1, 8.1), nama kontrol, bukti..."
                            autocomplete="off"
                        >
                        <span class="fw-search-kbd">/</span>
                    </div>
                    <select id="fw-filter-select" class="fw-filter-select">
                        <option value="">Semua Kategori ({{ $domains->count() }} Domain)</option>
                        @foreach($domains as $dom)
                            <option value="{{ $dom }}">{{ $dom }}</option>
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
                            data-type="{{ $ctrl['type'] ?? 'Kontrol' }}"
                            data-priority="{{ $ctrl['priority'] ?? 'Sedang' }}"
                            data-difficulty="{{ $ctrl['difficulty'] ?? '-' }}"
                            data-concept="{{ $ctrl['concept'] ?? '-' }}"
                            data-capability="{{ $ctrl['capability'] ?? '-' }}"
                            data-description="{{ $ctrl['description'] ?? '' }}"
                            data-interpretation="{{ $ctrl['interpretation'] ?? ($ctrl['implementationTips'] ?? '') }}"
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
                        <span class="fw-meta-chip"><i class="fas fa-shield-alt text-info"></i> <span id="insp-type">-</span></span>
                        <span class="fw-meta-chip"><i class="fas fa-flag text-danger"></i> <span id="insp-priority">-</span></span>
                        <span class="fw-meta-chip"><i class="fas fa-compass text-warning"></i> <span id="insp-concept">-</span></span>
                    </div>
                </div>

                {{-- DESCRIPTION --}}
                <div class="fw-section">
                    <div class="fw-section-title"><i class="fas fa-align-left text-primary"></i> Deskripsi Standar Kontrol</div>
                    <div class="fw-body-text" id="insp-description">{{ $firstControl['description'] ?? '-' }}</div>
                </div>

                {{-- INTERPRETATION & TIPS --}}
                <div class="fw-section">
                    <div class="fw-section-title"><i class="fas fa-lightbulb text-warning"></i> Panduan & Tips Praktis Implementasi</div>
                    <div class="p-3 bg-light rounded-3 text-secondary" style="font-size: 0.88rem; line-height: 1.55;" id="insp-interpretation">-</div>
                </div>

                {{-- ANALOGY --}}
                <div class="fw-section">
                    <div class="fw-section-title"><i class="fas fa-comment-dots text-info"></i> Analogi Operasional</div>
                    <div class="p-3 bg-light rounded-3 text-secondary" style="font-size: 0.88rem; line-height: 1.55;" id="insp-analogy">-</div>
                </div>

                {{-- EVIDENCE NEEDED --}}
                <div class="fw-section">
                    <div class="fw-section-title"><i class="fas fa-folder-check text-success"></i> Contoh Bukti Audit (Example Evidence)</div>
                    <ul class="fw-bullets-list" id="insp-evidence"></ul>
                </div>

                {{-- ROLE IMPACT CARDS --}}
                <div class="fw-section">
                    <div class="fw-section-title"><i class="fas fa-users-cog text-indigo"></i> Pembagian Tanggung Jawab Peran</div>
                    <div class="fw-role-grid">
                        <div class="fw-role-card bod">
                            <strong><i class="fas fa-crown text-purple"></i> Direksi & Pimpinan</strong>
                            <p id="insp-bod">-</p>
                        </div>
                        <div class="fw-role-card sysadmin">
                            <strong><i class="fas fa-terminal text-blue"></i> Tim IT & Keamanan Siber</strong>
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

    {{-- TAB 2: OVERVIEW & 4 DOMAINS --}}
    <div class="fw-tab-content d-none" id="tab-overview">
        <div class="row g-3">
            @foreach($domainBreakdown as $d)
                <div class="col-md-6 col-lg-3">
                    <div class="card p-3 h-100 shadow-sm border-0">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <span class="badge" style="background-color: {{ $d['color'] }}20; color: {{ $d['color'] }}; font-weight: 700;">
                                <i class="fas {{ $d['icon'] }} me-1"></i> {{ $d['name'] }}
                            </span>
                            <span class="badge bg-light text-dark border">{{ $d['count'] }} Kontrol</span>
                        </div>
                        <h4 class="h5 text-dark mb-1">{{ $d['name'] }}</h4>
                        <p class="text-muted small mb-0">{{ $d['summary'] }}</p>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    {{-- TAB 3: CYBERSECURITY CONCEPTS --}}
    <div class="fw-tab-content d-none" id="tab-concepts">
        <div class="card p-3 shadow-sm border-0">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <div>
                    <h3 class="h5 text-dark mb-1"><i class="fas fa-compass text-primary me-2"></i> Taksonomi Cybersecurity Concepts ISO 27001:2022</h3>
                    <p class="text-muted small mb-0">Klasifikasi kontrol berdasarkan keselarasan dengan konsep keamanan siber global (CSF):</p>
                </div>
                <span class="badge bg-primary-lt fs-6">6 Konsep</span>
            </div>

            <div class="row g-3">
                @foreach($conceptMeta as $cName => $cMeta)
                    @php
                        $cCount = $controlCollection->where('concept', $cName)->count();
                    @endphp
                    <div class="col-md-6 col-lg-4">
                        <div class="p-3 rounded-3 bg-light border h-100">
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <strong style="color: {{ $cMeta['color'] }};"><i class="fas {{ $cMeta['icon'] }} me-1"></i> {{ $cName }} ({{ $cMeta['key'] }})</strong>
                                <span class="badge bg-white text-dark border">{{ $cCount }} Kontrol</span>
                            </div>
                            <p class="text-muted small mb-0">{{ $cMeta['summary'] }}</p>
                        </div>
                    </div>
                @endforeach
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
    const inspType = document.getElementById('insp-type');
    const inspPriority = document.getElementById('insp-priority');
    const inspConcept = document.getElementById('insp-concept');
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
            if (inspType) inspType.textContent = el.dataset.type || '-';
            if (inspPriority) inspPriority.textContent = `Prioritas ${el.dataset.priority || '-'}`;
            if (inspConcept) inspConcept.textContent = el.dataset.concept || '-';
            if (inspDescription) inspDescription.textContent = el.dataset.description || '-';
            if (inspInterpretation) inspInterpretation.textContent = el.dataset.interpretation || 'Lakukan evaluasi berkala atas kontrol ini.';
            if (inspAnalogy) inspAnalogy.textContent = el.dataset.analogy || 'Terapkan tata kelola keamanan informasi sesuai best practice.';

            // Evidence
            if (inspEvidence) {
                inspEvidence.innerHTML = '';
                try {
                    const evArr = JSON.parse(el.dataset.evidence || '[]');
                    if (evArr.length === 0) {
                        inspEvidence.innerHTML = '<li class="text-muted">Dokumentasikan kebijakan, SOP, atau log audit sistem.</li>';
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
            if (inspBod) inspBod.textContent = el.dataset.bod || 'Pengesahan kebijakan dan pemantauan efektivitas SMKI.';
            if (inspSysadmin) inspSysadmin.textContent = el.dataset.sysadmin || 'Konfigurasi teknis, hardening sistem, dan pemantauan log.';
            if (inspLegal) inspLegal.textContent = el.dataset.legal || 'Tinjauan kontrak pihak ketiga dan kepatuhan regulasi eksternal.';
        }
    });
});
</script>
@endpush
