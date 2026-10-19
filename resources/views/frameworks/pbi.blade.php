@extends('layouts.tabler')

@section('title', 'PBI 02/2024 Keamanan Sistem Informasi & Ketahanan Siber - Raksantara')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/framework-reader.css') }}">
@endpush

@section('content')
@php
    $reqCollection = collect($requirements);
    $referenceCollection = collect($references);
    $totalChapters = $reqCollection->pluck('chapter')->filter()->unique()->count();
    $totalRequirements = $reqCollection->count();
    $totalReferences = $referenceCollection->count();
    $incidentClock = '1j / 3h';

    $domainMeta = [
        'Kerangka' => ['label' => 'Kerangka', 'color' => '#2563eb', 'icon' => 'fa-landmark', 'summary' => 'Sasaran, prinsip dasar, objek pengaturan, dan ruang lingkup KKS.'],
        'Tata Kelola' => ['label' => 'Tata Kelola', 'color' => '#0f766e', 'icon' => 'fa-sitemap', 'summary' => 'Strategi, kebijakan, fungsi organisasi, audit, dan budaya KKS.'],
        'Pencegahan' => ['label' => 'Pencegahan', 'color' => '#d97706', 'icon' => 'fa-shield-alt', 'summary' => 'Profil risiko, proteksi, deteksi, dan pengujian kapabilitas preventif.'],
        'Penanganan' => ['label' => 'Penanganan', 'color' => '#dc2626', 'icon' => 'fa-bolt', 'summary' => 'Respons, pelaporan insiden, pemulihan, dan komunikasi regulator.'],
        'Pengawasan' => ['label' => 'Pengawasan', 'color' => '#7c3aed', 'icon' => 'fa-binoculars', 'summary' => 'Pengawasan langsung/tidak langsung serta tindak lanjut regulator.'],
        'Kolaborasi' => ['label' => 'Kolaborasi', 'color' => '#2563eb', 'icon' => 'fa-handshake', 'summary' => 'Pertukaran informasi, koordinasi lintas otoritas, dan peran SRO.'],
        'Penerapan' => ['label' => 'Penerapan', 'color' => '#0f766e', 'icon' => 'fa-layer-group', 'summary' => 'Penerapan KKS sesuai klasifikasi penyelenggara.'],
    ];

    $chapterMeta = [
        'II' => ['label' => 'Kerangka KKS', 'color' => '#2563eb', 'icon' => 'fa-landmark', 'summary' => 'Sasaran, prinsip dasar, objek pengaturan, dan ruang lingkup KKS.'],
        'III' => ['label' => 'Tata Kelola', 'color' => '#0f766e', 'icon' => 'fa-sitemap', 'summary' => 'Strategi, fungsi organisasi, audit, dan budaya KKS.'],
        'IV' => ['label' => 'Pencegahan', 'color' => '#d97706', 'icon' => 'fa-shield-alt', 'summary' => 'Identifikasi, proteksi, deteksi, dan pengujian preventif.'],
        'V' => ['label' => 'Penanganan', 'color' => '#dc2626', 'icon' => 'fa-bolt', 'summary' => 'Respons, pemulihan, dan komunikasi insiden siber.'],
        'VI' => ['label' => 'Pengawasan', 'color' => '#7c3aed', 'icon' => 'fa-binoculars', 'summary' => 'Mekanisme pengawasan dan penyampaian data/informasi.'],
        'VII' => ['label' => 'Kolaborasi', 'color' => '#2563eb', 'icon' => 'fa-handshake', 'summary' => 'Pertukaran informasi, koordinasi, dan kerja sama dengan SRO.'],
        'VIII' => ['label' => 'Penerapan', 'color' => '#0f766e', 'icon' => 'fa-layer-group', 'summary' => 'Penerapan KKS sesuai klasifikasi penyelenggara.'],
    ];

    $subjectScopes = [
        ['code' => 'PJP', 'name' => 'Penyedia Jasa Pembayaran'],
        ['code' => 'PIP', 'name' => 'Penyelenggara Infrastruktur Sistem Pembayaran'],
        ['code' => 'PUSK PUVA', 'name' => 'Pelaku usaha pasar uang & pasar valuta asing'],
        ['code' => 'LPPU', 'name' => 'Lembaga Pendukung Pasar Uang'],
        ['code' => 'LPPVA', 'name' => 'Lembaga Pendukung Pasar Valuta Asing'],
        ['code' => 'KUPVA BB', 'name' => 'Penyelenggara penukaran valas bukan bank'],
        ['code' => 'Lainnya', 'name' => 'Pihak lain yang diatur & diawasi Bank Indonesia'],
    ];

    $domainBreakdown = collect($domainMeta)->map(function ($meta, $domain) use ($reqCollection) {
        return [
            'key' => $domain,
            'label' => $meta['label'],
            'color' => $meta['color'],
            'icon' => $meta['icon'],
            'summary' => $meta['summary'],
            'count' => $reqCollection->where('domain', $domain)->count(),
        ];
    })->filter(fn ($item) => $item['count'] > 0)->values();

    $chapterBreakdown = collect($chapterMeta)->map(function ($meta, $chapter) use ($reqCollection) {
        return [
            'key' => $chapter,
            'label' => $meta['label'],
            'color' => $meta['color'],
            'icon' => $meta['icon'],
            'summary' => $meta['summary'],
            'count' => $reqCollection->where('chapter', $chapter)->count(),
        ];
    })->filter(fn ($item) => $item['count'] > 0)->values();

    $maxChapterCount = max($chapterBreakdown->max('count') ?: 0, 1);
    $firstRequirement = $reqCollection->first();
@endphp

<div class="fw-page">
    {{-- COMPACT MODERN HERO --}}
    <div class="fw-hero">
        <div class="fw-hero-header">
            <div class="fw-kicker">
                <i class="fas fa-building-columns"></i> Peraturan Bank Indonesia
            </div>
            <span class="badge bg-success-lt text-success fw-bold px-3 py-1 rounded-pill">Status: Aktif Berlaku</span>
        </div>
        <h1 class="fw-title">PBI 02/2024 KKS Navigator</h1>
        <p class="fw-lede">
            Peraturan Bank Indonesia Nomor 2 Tahun 2024 tentang Keamanan Sistem Informasi dan Ketahanan Siber bagi Penyelenggara yang Diatur dan Diawasi Bank Indonesia. Menetapkan tata kelola, pencegahan protektif, SLA eskalasi insiden ketat, dan pelaporan kepatuhan.
        </p>
        <div class="fw-metric-row">
            <div class="fw-metric-pill">
                <span>Total Kewajiban:</span>
                <strong>{{ $totalRequirements }} Requirement</strong>
            </div>
            <div class="fw-metric-pill">
                <span>Rujukan Regulasi:</span>
                <strong>{{ $totalReferences }} Ketentuan Laporan</strong>
            </div>
            <div class="fw-metric-pill">
                <span>Bab Pengaturan:</span>
                <strong>{{ $totalChapters }} Bab Substantif</strong>
            </div>
            <div class="fw-metric-pill">
                <span>SLA Insiden Kritis:</span>
                <strong>1 Jam Awal / 3 Hari Laporan</strong>
            </div>
        </div>
    </div>

    {{-- NAVIGATION TABS --}}
    <div class="fw-nav-container">
        <ul class="fw-nav">
            <li>
                <button class="fw-tab active" data-tab="tab-explorer">
                    <i class="fas fa-list-check"></i>
                    <span>Eksplorasi Kewajiban ({{ $totalRequirements }})</span>
                </button>
            </li>
            <li>
                <button class="fw-tab" data-tab="tab-overview">
                    <i class="fas fa-cubes"></i>
                    <span>Peta Domain & Subjek Penyelenggara</span>
                </button>
            </li>
            <li>
                <button class="fw-tab" data-tab="tab-references">
                    <i class="fas fa-clock-rotate-left"></i>
                    <span>Tenggat Pelaporan & Pengawasan ({{ $totalReferences }})</span>
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
                            placeholder="Cari pasal, kata kunci, bukti audit..."
                            autocomplete="off"
                        >
                        <span class="fw-search-kbd">/</span>
                    </div>
                    <select id="fw-filter-select" class="fw-filter-select">
                        <option value="">Semua Bab ({{ $totalChapters }} Bab)</option>
                        @foreach($chapterBreakdown as $c)
                            <option value="{{ $c['key'] }}">Bab {{ $c['key'] }}: {{ $c['label'] }} ({{ $c['count'] }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="fw-counter-bar">
                    <span id="fw-counter">{{ $totalRequirements }} dari {{ $totalRequirements }} kewajiban</span>
                    <span class="text-muted small"><i class="fas fa-arrows-alt-v me-1"></i> Gunakan ↑/↓ keyboard</span>
                </div>

                <div class="fw-list" id="fw-items-container">
                    @foreach($requirements as $index => $req)
                        @php
                            $searchIndex = strtolower(($req['id'] ?? '') . ' ' . ($req['article'] ?? '') . ' ' . ($req['title'] ?? '') . ' ' . ($req['summary'] ?? '') . ' ' . ($req['domain'] ?? '') . ' ' . ($req['chapter'] ?? ''));
                            $dMeta = $domainMeta[$req['domain']] ?? ['label' => $req['domain'], 'color' => '#2563eb'];
                        @endphp
                        <button
                            type="button"
                            class="fw-item {{ $index === 0 ? 'active' : '' }}"
                            data-group="{{ $req['chapter'] ?? '' }}"
                            data-search-index="{{ $searchIndex }}"
                            data-id="{{ $req['id'] ?? '' }}"
                            data-article="{{ $req['article'] ?? '' }}"
                            data-chapter="Bab {{ $req['chapter'] ?? '-' }}"
                            data-domain="{{ $dMeta['label'] }}"
                            data-title="{{ $req['title'] ?? '' }}"
                            data-summary="{{ $req['summary'] ?? '' }}"
                            data-interpretation="{{ $req['interpretation'] ?? '' }}"
                            data-focus='@json($req['focus'] ?? [])'
                            data-evidence='@json($req['evidence'] ?? [])'
                            data-references='@json($req['references'] ?? [])'
                        >
                            <div class="fw-item-top">
                                <span class="fw-item-code">{{ $req['article'] ?: $req['id'] }}</span>
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
                        <span class="fw-badge-legal" id="insp-article">{{ $firstRequirement['article'] ?? $firstRequirement['id'] ?? '-' }}</span>
                        <div class="d-flex align-items-center gap-2">
                            <button type="button" class="btn btn-sm btn-outline-primary" data-copy-target="insp-summary" title="Salin ringkasan pasal">
                                <i class="fas fa-copy me-1"></i> Salin Teks
                            </button>
                            <a href="{{ route('analysis.cross-mapping') }}" class="btn btn-sm btn-outline-secondary" title="Buka pemetaan regulasi silang">
                                <i class="fas fa-network-wired me-1"></i> Cross-Mapping
                            </a>
                        </div>
                    </div>
                    <h2 class="fw-inspector-title" id="insp-title">{{ $firstRequirement['title'] ?? 'Pilih Kewajiban' }}</h2>
                    <div class="fw-meta-chips">
                        <span class="fw-meta-chip"><i class="fas fa-landmark text-primary"></i> <span id="insp-domain">-</span></span>
                        <span class="fw-meta-chip"><i class="fas fa-book text-muted"></i> <span id="insp-chapter">-</span></span>
                        <span class="fw-meta-chip"><i class="fas fa-hashtag text-muted"></i> <span id="insp-id">-</span></span>
                    </div>
                </div>

                {{-- SUMMARY --}}
                <div class="fw-section">
                    <div class="fw-section-title"><i class="fas fa-align-left text-primary"></i> Isi & Ringkasan Ketentuan</div>
                    <div class="fw-body-text" id="insp-summary">{{ $firstRequirement['summary'] ?? '-' }}</div>
                </div>

                {{-- INTERPRETATION --}}
                <div class="fw-section">
                    <div class="fw-section-title"><i class="fas fa-lightbulb text-warning"></i> Interpretasi Implementasi BI</div>
                    <div class="p-3 bg-light rounded-3 text-secondary" style="font-size: 0.88rem; line-height: 1.55;" id="insp-interpretation">-</div>
                </div>

                {{-- KEY FOCUS --}}
                <div class="fw-section">
                    <div class="fw-section-title"><i class="fas fa-bullseye text-danger"></i> Fokus Utama Kepatuhan</div>
                    <ul class="fw-bullets-list" id="insp-focus"></ul>
                </div>

                {{-- EVIDENCE NEEDED --}}
                <div class="fw-section">
                    <div class="fw-section-title"><i class="fas fa-folder-check text-success"></i> Dokumen Bukti & Evidencing Pengawasan</div>
                    <ul class="fw-bullets-list" id="insp-evidence"></ul>
                </div>

                {{-- LINKED REFERENCES --}}
                <div class="fw-section">
                    <div class="fw-section-title"><i class="fas fa-link text-indigo"></i> Rujukan Pelaporan / Kewajiban Terkait</div>
                    <div class="d-flex flex-wrap gap-2" id="insp-references"></div>
                </div>
            </div>
        </div>
    </div>

    {{-- TAB 2: OVERVIEW & DOMAINS --}}
    <div class="fw-tab-content d-none" id="tab-overview">
        <div class="row g-3">
            <div class="col-lg-6">
                <div class="card p-3 h-100 shadow-sm border-0">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <h3 class="h5 text-dark mb-0"><i class="fas fa-cubes text-primary me-2"></i> 7 Domain Ketahanan Siber BI</h3>
                        <span class="badge bg-primary-lt">{{ $domainBreakdown->count() }} Domain</span>
                    </div>
                    <p class="text-muted small mb-3">Struktur domain pilar dalam PBI 02/2024 yang wajib dipenuhi oleh seluruh penyelenggara:</p>
                    
                    <div class="d-flex flex-column gap-2">
                        @foreach($domainBreakdown as $d)
                            <div class="p-3 rounded-3 bg-light border">
                                <div class="d-flex align-items-center justify-content-between mb-1">
                                    <strong style="color: {{ $d['color'] }}"><i class="fas {{ $d['icon'] }} me-1"></i> {{ $d['label'] }}</strong>
                                    <span class="badge bg-white text-dark border">{{ $d['count'] }} Kewajiban</span>
                                </div>
                                <p class="text-muted small mb-0">{{ $d['summary'] }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="card p-3 h-100 shadow-sm border-0">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <h3 class="h5 text-dark mb-0"><i class="fas fa-users-gear text-info me-2"></i> Subjek Penyelenggara yang Diatur</h3>
                        <span class="badge bg-info-lt">{{ count($subjectScopes) }} Kategori</span>
                    </div>
                    <p class="text-muted small mb-3">Kategori entitas sistem pembayaran dan pasar uang yang berada di bawah pengawasan Bank Indonesia:</p>

                    <div class="d-flex flex-column gap-2">
                        @foreach($subjectScopes as $sc)
                            <div class="p-2 px-3 rounded-3 bg-light border d-flex align-items-center justify-content-between">
                                <span class="badge bg-primary-lt font-monospace fw-bold">{{ $sc['code'] }}</span>
                                <span class="text-secondary small fw-medium text-end">{{ $sc['name'] }}</span>
                            </div>
                        @endforeach
                    </div>

                    <div class="p-3 bg-danger-lt rounded-3 border border-danger-subtle mt-3">
                        <div class="d-flex align-items-start gap-2">
                            <i class="fas fa-triangle-exclamation text-danger mt-1"></i>
                            <div>
                                <strong class="text-danger small d-block">Sanksi Kepatuhan (Pasal 55)</strong>
                                <span class="text-muted small">Pelanggaran ketentuan pelaporan dan tata kelola berkonsekuensi sanksi administratif, denda kewajiban pembayaran, pembatasan operasional, hingga pencabutan izin penyelenggaraan.</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- TAB 3: REFERENCES & REPORTING DEADLINES --}}
    <div class="fw-tab-content d-none" id="tab-references">
        <div class="card p-3 shadow-sm border-0">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <div>
                    <h3 class="h5 text-dark mb-1"><i class="fas fa-clock-rotate-left text-danger me-2"></i> Matriks Pelaporan, Pengawasan, & Tenggat BI</h3>
                    <p class="text-muted small mb-0">Rujukan regulasi mengenai waktu penyampaian laporan insiden, pelaporan berkala, dan kesiapan bukti audit.</p>
                </div>
                <span class="badge bg-danger-lt fs-6">{{ $totalReferences }} Ketentuan Laporan</span>
            </div>

            <div class="table-responsive">
                <table class="table table-vcenter table-hover border">
                    <thead class="bg-light">
                        <tr>
                            <th style="width: 100px;">Kode</th>
                            <th style="width: 130px;">Dasar Pasal</th>
                            <th>Judul & Uraian Kewajiban</th>
                            <th style="width: 200px;">Tenggat Waktu (SLA)</th>
                            <th style="width: 150px;">Jenis</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($references as $ref)
                            <tr>
                                <td><span class="badge bg-danger-lt font-monospace">{{ $ref['id'] }}</span></td>
                                <td><span class="fw-bold text-dark">{{ $ref['article'] ?? '-' }}</span></td>
                                <td>
                                    <div class="fw-bold text-dark">{{ $ref['title'] }}</div>
                                    <div class="text-muted small mt-1">{{ $ref['summary'] ?? '' }}</div>
                                    @if(!empty($ref['deliverables']))
                                        <div class="d-flex flex-wrap gap-1 mt-2">
                                            @foreach($ref['deliverables'] as $deliv)
                                                <span class="badge bg-light text-secondary border font-monospace" style="font-size: 0.72rem;">
                                                    <i class="fas fa-check-circle text-success me-1"></i> {{ $deliv }}
                                                </span>
                                            @endforeach
                                        </div>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-warning-lt text-dark fw-bold">
                                        <i class="fas fa-clock me-1 text-warning"></i> {{ $ref['timeline'] ?? '-' }}
                                    </span>
                                </td>
                                <td><span class="badge bg-secondary-lt">{{ $ref['type'] ?? 'Pelaporan' }}</span></td>
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
    const inspArticle = document.getElementById('insp-article');
    const inspId = document.getElementById('insp-id');
    const inspTitle = document.getElementById('insp-title');
    const inspDomain = document.getElementById('insp-domain');
    const inspChapter = document.getElementById('insp-chapter');
    const inspSummary = document.getElementById('insp-summary');
    const inspInterpretation = document.getElementById('insp-interpretation');
    const inspFocus = document.getElementById('insp-focus');
    const inspEvidence = document.getElementById('insp-evidence');
    const inspReferences = document.getElementById('insp-references');

    new FrameworkReader({
        onItemSelect: function(el) {
            if (inspArticle) inspArticle.textContent = el.dataset.article || el.dataset.id || '-';
            if (inspId) inspId.textContent = el.dataset.id || '-';
            if (inspTitle) inspTitle.textContent = el.dataset.title || 'Pilih Kewajiban';
            if (inspDomain) inspDomain.textContent = el.dataset.domain || '-';
            if (inspChapter) inspChapter.textContent = el.dataset.chapter || '-';
            if (inspSummary) inspSummary.textContent = el.dataset.summary || '-';
            if (inspInterpretation) inspInterpretation.textContent = el.dataset.interpretation || '-';

            // Focus items
            if (inspFocus) {
                inspFocus.innerHTML = '';
                try {
                    const focusArr = JSON.parse(el.dataset.focus || '[]');
                    if (focusArr.length === 0) {
                        inspFocus.innerHTML = '<li class="text-muted">Tidak ada poin fokus spesifik.</li>';
                    } else {
                        focusArr.forEach(f => {
                            const li = document.createElement('li');
                            li.textContent = f;
                            inspFocus.appendChild(li);
                        });
                    }
                } catch(e) {}
            }

            // Evidence items
            if (inspEvidence) {
                inspEvidence.innerHTML = '';
                try {
                    const evArr = JSON.parse(el.dataset.evidence || '[]');
                    if (evArr.length === 0) {
                        inspEvidence.innerHTML = '<li class="text-muted">Gunakan dokumentasi SOP dan audit trail.</li>';
                    } else {
                        evArr.forEach(ev => {
                            const li = document.createElement('li');
                            li.textContent = ev;
                            inspEvidence.appendChild(li);
                        });
                    }
                } catch(e) {}
            }

            // References
            if (inspReferences) {
                inspReferences.innerHTML = '';
                try {
                    const refArr = JSON.parse(el.dataset.references || '[]');
                    if (refArr.length === 0) {
                        inspReferences.innerHTML = '<span class="text-muted small">Tidak ada tautan pelaporan khusus.</span>';
                    } else {
                        refArr.forEach(refId => {
                            const span = document.createElement('span');
                            span.className = 'badge bg-danger-lt border px-2 py-1';
                            span.innerHTML = `<i class="fas fa-file-invoice me-1"></i> ${refId}`;
                            inspReferences.appendChild(span);
                        });
                    }
                } catch(e) {}
            }
        }
    });
});
</script>
@endpush
