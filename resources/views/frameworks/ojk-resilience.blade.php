@extends('layouts.tabler')

@section('title', 'Panduan Resiliensi Digital OJK - Raksantara')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/framework-reader.css') }}">
@endpush

@section('content')
@php
    $guidanceCollection = collect($guidanceThemes);
    $totalThemes = $guidanceCollection->count();
    $totalSections = $guidanceCollection->pluck('section')->filter()->unique()->count();
    $totalFocusPoints = $guidanceCollection->sum(fn ($item) => count($item['focus'] ?? []));

    $sectionMeta = [
        'A. Latar Belakang' => ['label' => 'Latar Belakang', 'color' => '#2563eb', 'icon' => 'fa-compass', 'summary' => 'Menjelaskan konteks ketergantungan digital, eksposur gangguan, dan urgensi kesinambungan layanan.'],
        'B. Ketahanan Digital' => ['label' => 'Ketahanan Digital', 'color' => '#0f766e', 'icon' => 'fa-shield-alt', 'summary' => 'Menekankan kemampuan mencegah, mendeteksi, merespons, dan memulihkan gangguan pada layanan digital.'],
        'C. Kerangka Ketahanan Digital' => ['label' => 'Kerangka Ketahanan', 'color' => '#d97706', 'icon' => 'fa-sitemap', 'summary' => 'Menguatkan tata kelola, struktur pengelolaan, pengujian, dan peningkatan berkelanjutan.'],
    ];

    $sectionBreakdown = collect($sectionMeta)->map(function ($meta, $section) use ($guidanceCollection) {
        $items = $guidanceCollection->where('section', $section);
        return [
            'key' => $section,
            'label' => $meta['label'],
            'color' => $meta['color'],
            'icon' => $meta['icon'],
            'summary' => $meta['summary'],
            'count' => $items->count()
        ];
    })->filter(fn ($item) => $item['count'] > 0)->values();

    $maxSectionCount = max($sectionBreakdown->max('count') ?: 0, 1);
    $firstTheme = $guidanceCollection->first();
@endphp

<div class="fw-page">
    {{-- COMPACT MODERN HERO --}}
    <div class="fw-hero">
        <div class="fw-hero-header">
            <div class="fw-kicker">
                <i class="fas fa-shield-halved"></i> Panduan Resmi OJK
            </div>
            <span class="badge bg-success-lt text-success fw-bold px-3 py-1 rounded-pill">Status: Panduan Berlaku</span>
        </div>
        <h1 class="fw-title">Panduan Resiliensi Digital OJK</h1>
        <p class="fw-lede">
            Panduan Operasional Resiliensi Digital bagi Sektor Perbankan dan Lembaga Jasa Keuangan. Menetapkan prinsip pencegahan, deteksi dini, respons terkoordinasi, pemulihan layanan penting, serta pengujian ketahanan berbasis skenario krisis.
        </p>
        <div class="fw-metric-row">
            <div class="fw-metric-pill">
                <span>Bagian Dokumen:</span>
                <strong>{{ $totalSections }} Bagian Utama</strong>
            </div>
            <div class="fw-metric-pill">
                <span>Tema Panduan:</span>
                <strong>{{ $totalThemes }} Tema Prioritas</strong>
            </div>
            <div class="fw-metric-pill">
                <span>Fokus Implementasi:</span>
                <strong>{{ $totalFocusPoints }} Arahan Aksi</strong>
            </div>
            <div class="fw-metric-pill">
                <span>Fokus Utama:</span>
                <strong>Disrupsi & Kelangsungan Bisnis</strong>
            </div>
        </div>
    </div>

    {{-- NAVIGATION TABS --}}
    <div class="fw-nav-container">
        <ul class="fw-nav">
            <li>
                <button class="fw-tab active" data-tab="tab-explorer">
                    <i class="fas fa-list-check"></i>
                    <span>Eksplorasi Tema Panduan ({{ $totalThemes }})</span>
                </button>
            </li>
            <li>
                <button class="fw-tab" data-tab="tab-overview">
                    <i class="fas fa-cubes"></i>
                    <span>Struktur 3 Bagian Panduan</span>
                </button>
            </li>
            <li>
                <button class="fw-tab" data-tab="tab-focus">
                    <i class="fas fa-layer-group"></i>
                    <span>Board Tema Per Bagian</span>
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
                            placeholder="Cari tema, fokus implementasi, kata kunci..."
                            autocomplete="off"
                        >
                        <span class="fw-search-kbd">/</span>
                    </div>
                    <select id="fw-filter-select" class="fw-filter-select">
                        <option value="">Semua Bagian ({{ $totalSections }} Bagian)</option>
                        @foreach($sectionBreakdown as $sec)
                            <option value="{{ $sec['key'] }}">{{ $sec['key'] }} ({{ $sec['count'] }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="fw-counter-bar">
                    <span id="fw-counter">{{ $totalThemes }} dari {{ $totalThemes }} tema ditampilkan</span>
                    <span class="text-muted small"><i class="fas fa-arrows-alt-v me-1"></i> Gunakan ↑/↓ keyboard</span>
                </div>

                <div class="fw-list" id="fw-items-container">
                    @foreach($guidanceThemes as $index => $theme)
                        @php
                            $searchIndex = strtolower(($theme['id'] ?? '') . ' ' . ($theme['title'] ?? '') . ' ' . ($theme['section'] ?? '') . ' ' . ($theme['summary'] ?? '') . ' ' . ($theme['interpretation'] ?? ''));
                            $secMeta = $sectionMeta[$theme['section']] ?? ['label' => $theme['section'], 'color' => '#2563eb'];
                        @endphp
                        <button
                            type="button"
                            class="fw-item {{ $index === 0 ? 'active' : '' }}"
                            data-group="{{ $theme['section'] ?? '' }}"
                            data-search-index="{{ $searchIndex }}"
                            data-id="{{ $theme['id'] ?? '' }}"
                            data-title="{{ $theme['title'] ?? '' }}"
                            data-section="{{ $theme['section'] ?? '' }}"
                            data-summary="{{ $theme['summary'] ?? '' }}"
                            data-interpretation="{{ $theme['interpretation'] ?? '' }}"
                            data-focus='@json($theme['focus'] ?? [])'
                        >
                            <div class="fw-item-top">
                                <span class="fw-item-code">{{ $theme['id'] }}</span>
                                <span class="fw-item-tag">{{ $secMeta['label'] }}</span>
                            </div>
                            <div class="fw-item-title">{{ $theme['title'] ?? '' }}</div>
                            <div class="fw-item-desc">{{ Str::limit($theme['summary'] ?? '', 95) }}</div>
                        </button>
                    @endforeach
                </div>
            </div>

            {{-- RIGHT COLUMN: INSPECTOR PANEL --}}
            <div class="fw-inspector">
                <div class="fw-inspector-head">
                    <div class="fw-inspector-actions">
                        <span class="fw-badge-legal" id="insp-id">{{ $firstTheme['id'] ?? '-' }}</span>
                        <div class="d-flex align-items-center gap-2">
                            <button type="button" class="btn btn-sm btn-outline-primary" data-copy-target="insp-summary" title="Salin ringkasan tema">
                                <i class="fas fa-copy me-1"></i> Salin Teks
                            </button>
                            <a href="{{ route('analysis.cross-mapping') }}" class="btn btn-sm btn-outline-secondary" title="Buka pemetaan regulasi silang">
                                <i class="fas fa-network-wired me-1"></i> Cross-Mapping
                            </a>
                        </div>
                    </div>
                    <h2 class="fw-inspector-title" id="insp-title">{{ $firstTheme['title'] ?? 'Pilih Tema' }}</h2>
                    <div class="fw-meta-chips">
                        <span class="fw-meta-chip"><i class="fas fa-compass text-primary"></i> <span id="insp-section">-</span></span>
                        <span class="fw-meta-chip"><i class="fas fa-bullseye text-warning"></i> <span id="insp-focus-count">-</span></span>
                    </div>
                </div>

                {{-- SUMMARY --}}
                <div class="fw-section">
                    <div class="fw-section-title"><i class="fas fa-align-left text-primary"></i> Ringkasan Tema Panduan</div>
                    <div class="fw-body-text" id="insp-summary">{{ $firstTheme['summary'] ?? '-' }}</div>
                </div>

                {{-- INTERPRETATION --}}
                <div class="fw-section">
                    <div class="fw-section-title"><i class="fas fa-lightbulb text-warning"></i> Interpretasi & Implikasi Operasional</div>
                    <div class="p-3 bg-light rounded-3 text-secondary" style="font-size: 0.88rem; line-height: 1.55;" id="insp-interpretation">-</div>
                </div>

                {{-- FOCUS POINTS --}}
                <div class="fw-section">
                    <div class="fw-section-title"><i class="fas fa-list-check text-success"></i> Fokus Arahan Implementasi</div>
                    <ul class="fw-bullets-list" id="insp-focus"></ul>
                </div>
            </div>
        </div>
    </div>

    {{-- TAB 2: OVERVIEW & 3 BAGIAN --}}
    <div class="fw-tab-content d-none" id="tab-overview">
        <div class="row g-3">
            @foreach($sectionBreakdown as $sec)
                <div class="col-lg-4">
                    <div class="card p-3 h-100 shadow-sm border-0">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <span class="badge" style="background-color: {{ $sec['color'] }}20; color: {{ $sec['color'] }}; font-weight: 700;">
                                <i class="fas {{ $sec['icon'] }} me-1"></i> {{ $sec['label'] }}
                            </span>
                            <span class="badge bg-light text-dark border">{{ $sec['count'] }} Tema</span>
                        </div>
                        <h4 class="h5 text-dark mb-2">{{ $sec['key'] }}</h4>
                        <p class="text-muted small mb-3">{{ $sec['summary'] }}</p>
                        
                        <div class="mt-auto pt-2 border-top">
                            <span class="text-muted small">Mencakup {{ $guidanceCollection->where('section', $sec['key'])->sum(fn($t) => count($t['focus'] ?? [])) }} arahan aksi operasional.</span>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    {{-- TAB 3: THEMES PER SECTION BOARD --}}
    <div class="fw-tab-content d-none" id="tab-focus">
        <div class="row g-3">
            @foreach($sectionBreakdown as $sec)
                @php
                    $themesInSection = $guidanceCollection->where('section', $sec['key']);
                @endphp
                <div class="col-lg-4">
                    <div class="card p-3 h-100 shadow-sm border-0">
                        <div class="d-flex align-items-center justify-content-between mb-3 pb-2 border-bottom">
                            <h5 class="fw-bold mb-0 text-dark">{{ $sec['label'] }}</h5>
                            <span class="badge bg-primary-lt">{{ $themesInSection->count() }} Tema</span>
                        </div>
                        <div class="d-flex flex-column gap-2">
                            @foreach($themesInSection as $t)
                                <div class="p-3 rounded-3 bg-light border">
                                    <div class="d-flex align-items-center justify-content-between mb-1">
                                        <span class="font-monospace fw-bold text-primary small">{{ $t['id'] }}</span>
                                        <span class="badge bg-secondary-lt small">{{ count($t['focus'] ?? []) }} aksi</span>
                                    </div>
                                    <div class="fw-bold text-dark small mb-1">{{ $t['title'] }}</div>
                                    <p class="text-muted small mb-0" style="font-size: 0.78rem;">{{ Str::limit($t['summary'], 90) }}</p>
                                </div>
                            @endforeach
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
    const inspTitle = document.getElementById('insp-title');
    const inspSection = document.getElementById('insp-section');
    const inspFocusCount = document.getElementById('insp-focus-count');
    const inspSummary = document.getElementById('insp-summary');
    const inspInterpretation = document.getElementById('insp-interpretation');
    const inspFocus = document.getElementById('insp-focus');

    new FrameworkReader({
        onItemSelect: function(el) {
            if (inspId) inspId.textContent = el.dataset.id || '-';
            if (inspTitle) inspTitle.textContent = el.dataset.title || 'Pilih Tema';
            if (inspSection) inspSection.textContent = el.dataset.section || '-';
            if (inspSummary) inspSummary.textContent = el.dataset.summary || '-';
            if (inspInterpretation) inspInterpretation.textContent = el.dataset.interpretation || '-';

            // Focus items
            if (inspFocus) {
                inspFocus.innerHTML = '';
                try {
                    const focusArr = JSON.parse(el.dataset.focus || '[]');
                    if (inspFocusCount) inspFocusCount.textContent = `${focusArr.length} Poin Aksi`;
                    if (focusArr.length === 0) {
                        inspFocus.innerHTML = '<li class="text-muted">Tidak ada poin fokus khusus.</li>';
                    } else {
                        focusArr.forEach(f => {
                            const li = document.createElement('li');
                            li.textContent = f;
                            inspFocus.appendChild(li);
                        });
                    }
                } catch(e) {}
            }
        }
    });
});
</script>
@endpush
