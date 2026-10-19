@extends('layouts.tabler')

@section('title', 'NIST CSF 2.0 & SP 800-53 - Raksantara')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/framework-reader.css') }}">
@endpush

@section('content')
@php
    $collection = collect($controls);
    $spCollection = collect($sp800Controls);
    $totalFuncs = $collection->pluck('function')->unique()->count();
    $totalCats = $collection->pluck('category')->unique()->count();
    $totalSubs = $collection->count();
    $totalSp800 = $collection->pluck('sp80053')->flatten()->unique()->count();

    $functionMeta = [
        'GOVERN' => ['label' => 'Tata Kelola (Govern)', 'color' => '#1d3557', 'icon' => 'fa-sitemap', 'summary' => 'Arah kebijakan, manajemen risiko organisasi, akuntabilitas, dan pengawasan.'],
        'IDENTIFY' => ['label' => 'Identifikasi (Identify)', 'color' => '#2563eb', 'icon' => 'fa-compass', 'summary' => 'Pemahaman konteks aset, rantai pasokan, kerentanan, dan paparan ancaman.'],
        'PROTECT' => ['label' => 'Pelindungan (Protect)', 'color' => '#0f766e', 'icon' => 'fa-shield-alt', 'summary' => 'Safeguard identitas, kontrol akses, kesadaran, keamanan data, dan ketahanan infrastruktur.'],
        'DETECT' => ['label' => 'Deteksi (Detect)', 'color' => '#d97706', 'icon' => 'fa-binoculars', 'summary' => 'Pemantauan anomali terus-menerus, investigasi sinyal ancaman, dan deteksi dini insiden.'],
        'RESPOND' => ['label' => 'Respons (Respond)', 'color' => '#dc2626', 'icon' => 'fa-bolt', 'summary' => 'Tindakan penanganan insiden, analisis dampak, mitigasi, dan komunikasi koordinasi.'],
        'RECOVER' => ['label' => 'Pemulihan (Recover)', 'color' => '#16a34a', 'icon' => 'fa-life-ring', 'summary' => 'Restorasi aset dan layanan penting, komunikasi publik, dan evaluasi berkelanjutan.'],
    ];

    $functionBreakdown = collect($functionMeta)->map(function ($meta, $function) use ($collection) {
        $subset = $collection->where('function', $function);
        return [
            'key' => $function,
            'label' => $meta['label'],
            'color' => $meta['color'],
            'icon' => $meta['icon'],
            'summary' => $meta['summary'],
            'count' => $subset->count(),
            'categories' => $subset->pluck('category')->unique()->count(),
        ];
    })->values();

    $familyBreakdown = $spCollection->groupBy('family')->map(function ($items, $family) {
        $first = $items->first();
        return [
            'code' => $family,
            'name' => $first['family_name'] ?? $family,
            'count' => $items->count(),
        ];
    })->sortByDesc('count')->values();

    $firstControl = $collection->first();
@endphp

<div class="fw-page">
    {{-- COMPACT MODERN HERO --}}
    <div class="fw-hero">
        <div class="fw-hero-header">
            <div class="fw-kicker">
                <i class="fas fa-shield-halved"></i> Standar Global Keamanan Siber
            </div>
            <span class="badge bg-primary-lt text-primary fw-bold px-3 py-1 rounded-pill">Edisi: NIST CSF 2.0</span>
        </div>
        <h1 class="fw-title">NIST Cybersecurity Framework (CSF) 2.0</h1>
        <p class="fw-lede">
            Kerangka kerja komprehensif National Institute of Standards and Technology. Memetakan 6 fungsi inti (Govern, Identify, Protect, Detect, Respond, Recover) dengan subkategori aksi taktis serta rujukan silang kontrol teknis NIST SP 800-53 Rev 5.
        </p>
        <div class="fw-metric-row">
            <div class="fw-metric-pill">
                <span>Fungsi Utama:</span>
                <strong>6 Fungsi Inti CSF</strong>
            </div>
            <div class="fw-metric-pill">
                <span>Subkategori:</span>
                <strong>{{ $totalSubs }} Subkategori Aksi</strong>
            </div>
            <div class="fw-metric-pill">
                <span>Kategori Topik:</span>
                <strong>{{ $totalCats }} Kategori Teknis</strong>
            </div>
            <div class="fw-metric-pill">
                <span>Pemetaan Silang:</span>
                <strong>{{ $totalSp800 }} Kontrol SP 800-53</strong>
            </div>
        </div>
    </div>

    {{-- NAVIGATION TABS --}}
    <div class="fw-nav-container">
        <ul class="fw-nav">
            <li>
                <button class="fw-tab active" data-tab="tab-explorer">
                    <i class="fas fa-list-check"></i>
                    <span>Eksplorasi Subkategori CSF ({{ $totalSubs }})</span>
                </button>
            </li>
            <li>
                <button class="fw-tab" data-tab="tab-overview">
                    <i class="fas fa-cubes"></i>
                    <span>6 Fungsi Inti & Peta Kategori</span>
                </button>
            </li>
            <li>
                <button class="fw-tab" data-tab="tab-sp800">
                    <i class="fas fa-network-wired"></i>
                    <span>Katalog Referensi SP 800-53 ({{ $spCollection->count() }})</span>
                </button>
            </li>
        </ul>
    </div>

    {{-- TAB 1: SUB-CATEGORY EXPLORER (ACTIVE DEFAULT) --}}
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
                            placeholder="Cari ID (mis. GV.OC-01, PR.AA-01), topik, bukti..."
                            autocomplete="off"
                        >
                        <span class="fw-search-kbd">/</span>
                    </div>
                    <select id="fw-filter-select" class="fw-filter-select">
                        <option value="">Semua Fungsi (6 Fungsi)</option>
                        @foreach($functionBreakdown as $fn)
                            <option value="{{ $fn['key'] }}">{{ $fn['label'] }} ({{ $fn['count'] }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="fw-counter-bar">
                    <span id="fw-counter">{{ $totalSubs }} dari {{ $totalSubs }} subkategori ditampilkan</span>
                    <span class="text-muted small"><i class="fas fa-arrows-alt-v me-1"></i> Gunakan ↑/↓ keyboard</span>
                </div>

                <div class="fw-list" id="fw-items-container">
                    @foreach($controls as $index => $ctrl)
                        @php
                            $searchIndex = strtolower(($ctrl['id'] ?? '') . ' ' . ($ctrl['title'] ?? '') . ' ' . ($ctrl['function'] ?? '') . ' ' . ($ctrl['category'] ?? '') . ' ' . ($ctrl['description'] ?? ''));
                            $fnMeta = $functionMeta[$ctrl['function']] ?? ['label' => $ctrl['function'], 'color' => '#2563eb'];
                        @endphp
                        <button
                            type="button"
                            class="fw-item {{ $index === 0 ? 'active' : '' }}"
                            data-group="{{ $ctrl['function'] ?? '' }}"
                            data-search-index="{{ $searchIndex }}"
                            data-id="{{ $ctrl['id'] ?? '' }}"
                            data-title="{{ $ctrl['title'] ?? '' }}"
                            data-function="{{ $fnMeta['label'] }}"
                            data-category="{{ $ctrl['category'] ?? '-' }}"
                            data-description="{{ $ctrl['description'] ?? '' }}"
                            data-interpretation="{{ $ctrl['interpretation'] ?? '-' }}"
                            data-evidence='@json($ctrl['evidence'] ?? [])'
                            data-sp800='@json($ctrl['sp80053'] ?? [])'
                        >
                            <div class="fw-item-top">
                                <span class="fw-item-code">{{ $ctrl['id'] }}</span>
                                <span class="fw-item-tag">{{ $ctrl['category'] ?? '-' }}</span>
                            </div>
                            <div class="fw-item-title">{{ $ctrl['title'] ?? '' }}</div>
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
                            <button type="button" class="btn btn-sm btn-outline-primary" data-copy-target="insp-description" title="Salin uraian subkategori">
                                <i class="fas fa-copy me-1"></i> Salin Teks
                            </button>
                            <a href="{{ route('analysis.cross-mapping') }}" class="btn btn-sm btn-outline-secondary" title="Buka pemetaan regulasi silang">
                                <i class="fas fa-network-wired me-1"></i> Cross-Mapping
                            </a>
                        </div>
                    </div>
                    <h2 class="fw-inspector-title" id="insp-title">{{ $firstControl['title'] ?? 'Pilih Subkategori' }}</h2>
                    <div class="fw-meta-chips">
                        <span class="fw-meta-chip"><i class="fas fa-sitemap text-primary"></i> <span id="insp-function">-</span></span>
                        <span class="fw-meta-chip"><i class="fas fa-folder text-muted"></i> <span id="insp-category">-</span></span>
                    </div>
                </div>

                {{-- DESCRIPTION --}}
                <div class="fw-section">
                    <div class="fw-section-title"><i class="fas fa-align-left text-primary"></i> Uraian Subkategori NIST CSF</div>
                    <div class="fw-body-text" id="insp-description">{{ $firstControl['description'] ?? '-' }}</div>
                </div>

                {{-- INTERPRETATION --}}
                <div class="fw-section">
                    <div class="fw-section-title"><i class="fas fa-lightbulb text-warning"></i> Interpretasi & Arahan Implementasi</div>
                    <div class="p-3 bg-light rounded-3 text-secondary" style="font-size: 0.88rem; line-height: 1.55;" id="insp-interpretation">-</div>
                </div>

                {{-- EVIDENCE NEEDED --}}
                <div class="fw-section">
                    <div class="fw-section-title"><i class="fas fa-folder-check text-success"></i> Contoh Bukti Kepatuhan (Audit Evidence)</div>
                    <ul class="fw-bullets-list" id="insp-evidence"></ul>
                </div>

                {{-- SP 800-53 REFERENCES --}}
                <div class="fw-section">
                    <div class="fw-section-title"><i class="fas fa-link text-indigo"></i> Pemetaan Kontrol NIST SP 800-53 Rev 5</div>
                    <div class="d-flex flex-wrap gap-2" id="insp-sp800"></div>
                </div>
            </div>
        </div>
    </div>

    {{-- TAB 2: OVERVIEW & 6 FUNCTIONS --}}
    <div class="fw-tab-content d-none" id="tab-overview">
        <div class="row g-3">
            @foreach($functionBreakdown as $fn)
                <div class="col-md-6 col-lg-4">
                    <div class="card p-3 h-100 shadow-sm border-0">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <span class="badge" style="background-color: {{ $fn['color'] }}20; color: {{ $fn['color'] }}; font-weight: 700;">
                                <i class="fas {{ $fn['icon'] }} me-1"></i> {{ $fn['key'] }}
                            </span>
                            <span class="badge bg-light text-dark border">{{ $fn['count'] }} Subkategori</span>
                        </div>
                        <h4 class="h5 text-dark mb-1">{{ $fn['label'] }}</h4>
                        <p class="text-muted small mb-0">{{ $fn['summary'] }}</p>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    {{-- TAB 3: NIST SP 800-53 CATALOG --}}
    <div class="fw-tab-content d-none" id="tab-sp800">
        <div class="card p-3 shadow-sm border-0">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <div>
                    <h3 class="h5 text-dark mb-1"><i class="fas fa-network-wired text-primary me-2"></i> Katalog Kontrol NIST SP 800-53 Rev 5</h3>
                    <p class="text-muted small mb-0">Rujukan kontrol keamanan dan privasi federal Amerika Serikat yang dipetakan langsung ke NIST CSF 2.0.</p>
                </div>
                <span class="badge bg-primary-lt fs-6">{{ $spCollection->count() }} Kontrol</span>
            </div>

            <div class="table-responsive">
                <table class="table table-vcenter table-hover border">
                    <thead class="bg-light">
                        <tr>
                            <th style="width: 110px;">ID Kontrol</th>
                            <th style="width: 130px;">Keluarga</th>
                            <th>Uraian Kontrol</th>
                            <th style="width: 140px;">Terkait CSF</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($sp800Controls as $sp)
                            <tr>
                                <td><span class="badge bg-primary-lt font-monospace">{{ $sp['id'] }}</span></td>
                                <td>
                                    <span class="fw-bold text-dark small d-block">{{ $sp['family'] }}</span>
                                    <span class="text-muted" style="font-size: 0.72rem;">{{ $sp['family_name'] ?? '' }}</span>
                                </td>
                                <td>
                                    <div class="text-secondary small">{{ Str::limit($sp['description'] ?? '', 160) }}</div>
                                </td>
                                <td>
                                    <span class="badge bg-light text-secondary border">{{ count($sp['nist_references'] ?? []) }} subkategori</span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
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
    const inspTitle = document.getElementById('insp-title');
    const inspFunction = document.getElementById('insp-function');
    const inspCategory = document.getElementById('insp-category');
    const inspDescription = document.getElementById('insp-description');
    const inspInterpretation = document.getElementById('insp-interpretation');
    const inspEvidence = document.getElementById('insp-evidence');
    const inspSp800 = document.getElementById('insp-sp800');

    new FrameworkReader({
        onItemSelect: function(el) {
            if (inspId) inspId.textContent = el.dataset.id || '-';
            if (inspTitle) inspTitle.textContent = el.dataset.title || 'Pilih Subkategori';
            if (inspFunction) inspFunction.textContent = el.dataset.function || '-';
            if (inspCategory) inspCategory.textContent = el.dataset.category || '-';
            if (inspDescription) inspDescription.textContent = el.dataset.description || '-';
            if (inspInterpretation) inspInterpretation.textContent = el.dataset.interpretation || '-';

            // Evidence
            if (inspEvidence) {
                inspEvidence.innerHTML = '';
                try {
                    const evArr = JSON.parse(el.dataset.evidence || '[]');
                    if (evArr.length === 0) {
                        inspEvidence.innerHTML = '<li class="text-muted">Gunakan kebijakan, SOP, atau log audit standar.</li>';
                    } else {
                        evArr.forEach(ev => {
                            const li = document.createElement('li');
                            li.textContent = ev;
                            inspEvidence.appendChild(li);
                        });
                    }
                } catch(e) {}
            }

            // SP 800-53
            if (inspSp800) {
                inspSp800.innerHTML = '';
                try {
                    const spArr = JSON.parse(el.dataset.sp800 || '[]');
                    if (spArr.length === 0) {
                        inspSp800.innerHTML = '<span class="text-muted small">Tidak ada kontrol SP 800-53 yang ditunjuk.</span>';
                    } else {
                        spArr.forEach(spId => {
                            const span = document.createElement('span');
                            span.className = 'badge bg-indigo-lt border px-2 py-1 font-monospace';
                            span.textContent = spId;
                            inspSp800.appendChild(span);
                        });
                    }
                } catch(e) {}
            }
        }
    });
});
</script>
@endpush
