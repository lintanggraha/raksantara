@extends('layouts.tabler')

@section('title', 'SEOJK 29/SEOJK.03/2022 Ketahanan & Keamanan Siber - Raksantara')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/framework-reader.css') }}">
@endpush

@section('content')
@php
    $reqCollection = collect($requirements);
    $appendixCollection = collect($appendices);
    $totalChapters = $reqCollection->pluck('chapter')->unique()->count();
    $totalRequirements = $reqCollection->count();
    $totalAppendices = $appendixCollection->count();
    $reportingSla = 15;

    $pillarMeta = [
        'Assessment' => ['label' => 'Penilaian', 'color' => '#2563eb', 'icon' => 'fa-chart-line', 'summary' => 'Siklus penilaian, scoring, dan justifikasi hasil.'],
        'Governance' => ['label' => 'Governance', 'color' => '#0f766e', 'icon' => 'fa-sitemap', 'summary' => 'Arah kebijakan, akuntabilitas pengurus, dan kerangka risiko.'],
        'Resilience' => ['label' => 'Ketahanan', 'color' => '#d97706', 'icon' => 'fa-shield-alt', 'summary' => 'Identifikasi, pelindungan, deteksi, respons, dan pemulihan.'],
        'Testing' => ['label' => 'Pengujian', 'color' => '#ea580c', 'icon' => 'fa-vials', 'summary' => 'Vulnerability assessment dan scenario exercise.'],
        'Organization' => ['label' => 'Fungsi Organisasi', 'color' => '#7c3aed', 'icon' => 'fa-users', 'summary' => 'Unit independen dan koordinasi tim tanggap insiden.'],
        'Reporting' => ['label' => 'Pelaporan', 'color' => '#dc2626', 'icon' => 'fa-file-alt', 'summary' => 'Konsolidasi tingkat risiko dan pelaporan resmi ke OJK.'],
    ];

    $chapterMeta = [
        'II' => ['label' => 'Risiko Inheren', 'color' => '#2563eb', 'icon' => 'fa-chart-line', 'summary' => 'Menilai paparan awal berdasarkan empat faktor risiko.'],
        'III' => ['label' => 'Manajemen Risiko', 'color' => '#0f766e', 'icon' => 'fa-sitemap', 'summary' => 'Pengawasan aktif, kerangka, proses, dan pengendalian.'],
        'IV' => ['label' => 'Ketahanan Siber', 'color' => '#d97706', 'icon' => 'fa-shield-alt', 'summary' => 'Identifikasi, pelindungan, deteksi, penanggulangan, dan pemulihan.'],
        'V' => ['label' => 'Maturitas', 'color' => '#7c3aed', 'icon' => 'fa-th-large', 'summary' => 'Mengukur kualitas penerapan dan level kematangan.'],
        'VI' => ['label' => 'Tingkat Risiko', 'color' => '#dc2626', 'icon' => 'fa-balance-scale', 'summary' => 'Menggabungkan inherent risk dan maturity menjadi risk rating final.'],
        'VII' => ['label' => 'Pengujian', 'color' => '#ea580c', 'icon' => 'fa-vials', 'summary' => 'Analisis kerentanan, penetration test, dan exercise berbasis skenario.'],
        'VIII' => ['label' => 'Fungsi Siber', 'color' => '#2563eb', 'icon' => 'fa-users', 'summary' => 'Unit independen dan kesiapan tim tanggap insiden siber.'],
    ];

    $pillarBreakdown = collect($pillarMeta)->map(function ($meta, $pillar) use ($reqCollection) {
        return [
            'key' => $pillar,
            'label' => $meta['label'],
            'color' => $meta['color'],
            'icon' => $meta['icon'],
            'summary' => $meta['summary'],
            'count' => $reqCollection->where('pillar', $pillar)->count(),
        ];
    })->values();

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

    $maxPillarCount = max($pillarBreakdown->max('count') ?: 0, 1);
    $maxChapterCount = max($chapterBreakdown->max('count') ?: 0, 1);
    $firstRequirement = $reqCollection->first();
@endphp

<div class="fw-page">
    {{-- COMPACT MODERN HERO --}}
    <div class="fw-hero">
        <div class="fw-hero-header">
            <div class="fw-kicker">
                <i class="fas fa-landmark"></i> Surat Edaran OJK
            </div>
            <span class="badge bg-success-lt text-success fw-bold px-3 py-1 rounded-pill">Status: Aktif Berlaku</span>
        </div>
        <h1 class="fw-title">SEOJK 29/SEOJK.03/2022</h1>
        <p class="fw-lede">
            Surat Edaran Otoritas Jasa Keuangan tentang Ketahanan dan Keamanan Siber bagi Bank Umum. Menetapkan kerangka penilaian risiko inheren, evaluasi maturitas siber berkala, pengujian penetrasi berkala, serta format pelaporan resmi kondisi siber ke OJK.
        </p>
        <div class="fw-metric-row">
            <div class="fw-metric-pill">
                <span>Total Ketentuan:</span>
                <strong>{{ $totalRequirements }} Requirement</strong>
            </div>
            <div class="fw-metric-pill">
                <span>Dokumen Lampiran:</span>
                <strong>{{ $totalAppendices }} Lampiran</strong>
            </div>
            <div class="fw-metric-pill">
                <span>Bab Regulasi:</span>
                <strong>{{ $totalChapters }} Bab Operasional</strong>
            </div>
            <div class="fw-metric-pill">
                <span>SLA Pelaporan:</span>
                <strong>{{ $reportingSla }} Hari Kerja</strong>
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
                    <i class="fas fa-chart-pie"></i>
                    <span>Pilar & Hotspot Bab Regulasi</span>
                </button>
            </li>
            <li>
                <button class="fw-tab" data-tab="tab-references">
                    <i class="fas fa-file-invoice"></i>
                    <span>Lampiran & Format Resmi ({{ $totalAppendices }})</span>
                </button>
            </li>
        </ul>
    </div>

    {{-- TAB 1: MASTER-DETAIL EXPLORER (ACTIVE DEFAULT) --}}
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
                            placeholder="Cari ID, kata kunci, topik ketentuan..."
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
                    <span id="fw-counter">{{ $totalRequirements }} dari {{ $totalRequirements }} requirement</span>
                    <span class="text-muted small"><i class="fas fa-arrows-alt-v me-1"></i> Gunakan ↑/↓ keyboard</span>
                </div>

                <div class="fw-list" id="fw-items-container">
                    @foreach($requirements as $index => $req)
                        @php
                            $searchIndex = strtolower(($req['id'] ?? '') . ' ' . ($req['title'] ?? '') . ' ' . ($req['summary'] ?? '') . ' ' . ($req['pillar'] ?? '') . ' ' . ($req['chapter'] ?? ''));
                            $pillarName = $pillarMeta[$req['pillar']]['label'] ?? ($req['pillar'] ?? '-');
                            $chapName = $chapterMeta[$req['chapter']]['label'] ?? ($req['chapter'] ?? '-');
                        @endphp
                        <button
                            type="button"
                            class="fw-item {{ $index === 0 ? 'active' : '' }}"
                            data-group="{{ $req['chapter'] ?? '' }}"
                            data-search-index="{{ $searchIndex }}"
                            data-id="{{ $req['id'] ?? '' }}"
                            data-title="{{ $req['title'] ?? '' }}"
                            data-summary="{{ $req['summary'] ?? '' }}"
                            data-pillar="{{ $pillarName }}"
                            data-chapter="Bab {{ $req['chapter'] ?? '-' }}: {{ $chapName }}"
                            data-cadence="{{ $req['cadence'] ?? '-' }}"
                            data-interpretation="{{ $req['interpretation'] ?? '-' }}"
                            data-focus='@json($req['focus'] ?? [])'
                            data-evidence='@json($req['evidence'] ?? [])'
                            data-appendices='@json($req['appendices'] ?? [])'
                            data-reporting="{{ $req['reporting'] ?? '-' }}"
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
                        <span class="fw-badge-legal" id="insp-id">{{ $firstRequirement['id'] ?? '-' }}</span>
                        <div class="d-flex align-items-center gap-2">
                            <button type="button" class="btn btn-sm btn-outline-primary" data-copy-target="insp-summary" title="Salin ringkasan ketentuan">
                                <i class="fas fa-copy me-1"></i> Salin Teks
                            </button>
                            <a href="{{ route('analysis.cross-mapping') }}" class="btn btn-sm btn-outline-secondary" title="Buka pemetaan regulasi silang">
                                <i class="fas fa-network-wired me-1"></i> Cross-Mapping
                            </a>
                        </div>
                    </div>
                    <h2 class="fw-inspector-title" id="insp-title">{{ $firstRequirement['title'] ?? 'Pilih Requirement' }}</h2>
                    <div class="fw-meta-chips">
                        <span class="fw-meta-chip"><i class="fas fa-cubes text-primary"></i> <span id="insp-pillar">-</span></span>
                        <span class="fw-meta-chip"><i class="fas fa-book text-muted"></i> <span id="insp-chapter">-</span></span>
                        <span class="fw-meta-chip"><i class="fas fa-clock text-warning"></i> <span id="insp-cadence">-</span></span>
                    </div>
                </div>

                {{-- SUMMARY --}}
                <div class="fw-section">
                    <div class="fw-section-title"><i class="fas fa-align-left text-primary"></i> Ringkasan Ketentuan</div>
                    <div class="fw-body-text" id="insp-summary">{{ $firstRequirement['summary'] ?? '-' }}</div>
                </div>

                {{-- INTERPRETATION --}}
                <div class="fw-section">
                    <div class="fw-section-title"><i class="fas fa-lightbulb text-warning"></i> Interpretasi Implementasi & Panduan Praktis</div>
                    <div class="p-3 bg-light rounded-3 text-secondary" style="font-size: 0.88rem; line-height: 1.55;" id="insp-interpretation">-</div>
                </div>

                {{-- FOCUS --}}
                <div class="fw-section">
                    <div class="fw-section-title"><i class="fas fa-bullseye text-danger"></i> Fokus Utama Kepatuhan</div>
                    <ul class="fw-bullets-list" id="insp-focus"></ul>
                </div>

                {{-- EVIDENCE --}}
                <div class="fw-section">
                    <div class="fw-section-title"><i class="fas fa-folder-check text-success"></i> Contoh Evidence & Bukti Audit</div>
                    <ul class="fw-bullets-list" id="insp-evidence"></ul>
                </div>

                {{-- APPENDICES --}}
                <div class="fw-section">
                    <div class="fw-section-title"><i class="fas fa-file-invoice text-indigo"></i> Lampiran Resmi Terkait</div>
                    <div class="d-flex flex-wrap gap-2" id="insp-appendices"></div>
                </div>

                {{-- REPORTING / OUTPUT --}}
                <div class="fw-section">
                    <div class="fw-section-title"><i class="fas fa-paper-plane text-info"></i> Output & Pelaporan Mandatori</div>
                    <div class="p-3 bg-light rounded-3 text-secondary" style="font-size: 0.88rem; line-height: 1.55;" id="insp-reporting">-</div>
                </div>
            </div>
        </div>
    </div>

    {{-- TAB 2: OVERVIEW & BREAKDOWN --}}
    <div class="fw-tab-content d-none" id="tab-overview">
        <div class="row g-3">
            <div class="col-lg-6">
                <div class="card p-3 h-100 shadow-sm border-0">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <h3 class="h5 text-dark mb-0"><i class="fas fa-cubes text-primary me-2"></i> 6 Pilar Ketahanan Siber</h3>
                        <span class="badge bg-primary-lt">{{ $pillarBreakdown->count() }} Pilar</span>
                    </div>
                    <p class="text-muted small mb-3">Distribusi ketentuan berdasarkan lensa operasional keamanan dan ketahanan siber Bank Umum:</p>
                    
                    <div class="d-flex flex-column gap-2">
                        @foreach($pillarBreakdown as $p)
                            <div class="p-3 rounded-3 bg-light border">
                                <div class="d-flex align-items-center justify-content-between mb-1">
                                    <strong style="color: {{ $p['color'] }}"><i class="fas {{ $p['icon'] }} me-1"></i> {{ $p['label'] }}</strong>
                                    <span class="badge bg-white text-dark border">{{ $p['count'] }} Ketentuan</span>
                                </div>
                                <p class="text-muted small mb-2">{{ $p['summary'] }}</p>
                                <div class="progress" style="height: 6px;">
                                    <div class="progress-bar" style="width: {{ round(($p['count'] / $maxPillarCount) * 100) }}%; background-color: {{ $p['color'] }};"></div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="card p-3 h-100 shadow-sm border-0">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <h3 class="h5 text-dark mb-0"><i class="fas fa-layer-group text-info me-2"></i> Distribusi Bab SEOJK 29</h3>
                        <span class="badge bg-info-lt">{{ $totalChapters }} Bab Operasional</span>
                    </div>
                    <p class="text-muted small mb-3">Pemetaan jumlah ketentuan pada setiap bab dalam Surat Edaran:</p>

                    <div class="d-flex flex-column gap-2">
                        @foreach($chapterBreakdown as $c)
                            <div class="p-3 rounded-3 bg-light border">
                                <div class="d-flex align-items-center justify-content-between mb-1">
                                    <strong style="color: {{ $c['color'] }}"><i class="fas {{ $c['icon'] }} me-1"></i> Bab {{ $c['key'] }}: {{ $c['label'] }}</strong>
                                    <span class="badge bg-white text-dark border">{{ $c['count'] }} Ketentuan</span>
                                </div>
                                <p class="text-muted small mb-2">{{ $c['summary'] }}</p>
                                <div class="progress" style="height: 6px;">
                                    <div class="progress-bar" style="width: {{ round(($c['count'] / $maxChapterCount) * 100) }}%; background-color: {{ $c['color'] }};"></div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- TAB 3: APPENDICES & REFERENCES --}}
    <div class="fw-tab-content d-none" id="tab-references">
        <div class="card p-3 shadow-sm border-0">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <div>
                    <h3 class="h5 text-dark mb-1"><i class="fas fa-file-invoice text-indigo me-2"></i> Daftar Lampiran Resmi SEOJK 29/2022</h3>
                    <p class="text-muted small mb-0">Format penilaian mandiri, parameter risiko, kontrol minimum, dan template pelaporan ke OJK.</p>
                </div>
                <span class="badge bg-indigo-lt fs-6">{{ $totalAppendices }} Dokumen Lampiran</span>
            </div>

            <div class="table-responsive">
                <table class="table table-vcenter table-hover border">
                    <thead class="bg-light">
                        <tr>
                            <th style="width: 130px;">Kode</th>
                            <th>Judul & Cakupan Dokumen</th>
                            <th style="width: 180px;">Jenis</th>
                            <th style="width: 120px;">Terkait</th>
                            <th style="width: 100px;" class="text-end">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($appendices as $app)
                            <tr>
                                <td><span class="badge bg-primary-lt font-monospace">{{ $app['id'] }}</span></td>
                                <td>
                                    <div class="fw-bold text-dark">{{ $app['title'] }}</div>
                                    <div class="text-muted small mt-1">{{ $app['scope'] ?? $app['summary'] ?? '' }}</div>
                                </td>
                                <td><span class="badge bg-secondary-lt">{{ $app['type'] ?? 'Lampiran' }}</span></td>
                                <td>
                                    <span class="text-muted small">{{ count($app['used_by'] ?? []) }} pasal</span>
                                </td>
                                <td class="text-end">
                                    <button 
                                        type="button" 
                                        class="btn btn-sm btn-outline-primary btn-view-appendix"
                                        data-id="{{ $app['id'] }}"
                                        data-title="{{ $app['title'] }}"
                                        data-summary="{{ $app['summary'] ?? '' }}"
                                        data-contains='@json($app['contains'] ?? [])'
                                        data-usedby='@json($app['used_by'] ?? [])'
                                    >
                                        <i class="fas fa-eye me-1"></i> Detail
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

{{-- MODAL DETAIL LAMPIRAN --}}
<div class="modal fade" id="appendixModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content shadow border-0">
            <div class="modal-header bg-light">
                <div>
                    <span class="badge bg-primary-lt font-monospace me-2" id="modal-app-id">-</span>
                    <h5 class="modal-title d-inline-block fw-bold" id="modal-app-title">Detail Lampiran</h5>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div class="mb-4">
                    <label class="form-label text-muted small fw-bold text-uppercase">Ringkasan Dokumen</label>
                    <div class="p-3 bg-light rounded-3 text-secondary" id="modal-app-summary">-</div>
                </div>

                <div class="mb-4">
                    <label class="form-label text-muted small fw-bold text-uppercase">Artefak / Muatan Utama</label>
                    <ul class="list-group list-group-flush border rounded-3" id="modal-app-contains"></ul>
                </div>

                <div>
                    <label class="form-label text-muted small fw-bold text-uppercase">Ketentuan yang Merujuk</label>
                    <div class="d-flex flex-wrap gap-2" id="modal-app-links"></div>
                </div>
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
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
    const inspPillar = document.getElementById('insp-pillar');
    const inspChapter = document.getElementById('insp-chapter');
    const inspCadence = document.getElementById('insp-cadence');
    const inspSummary = document.getElementById('insp-summary');
    const inspInterpretation = document.getElementById('insp-interpretation');
    const inspFocus = document.getElementById('insp-focus');
    const inspEvidence = document.getElementById('insp-evidence');
    const inspAppendices = document.getElementById('insp-appendices');
    const inspReporting = document.getElementById('insp-reporting');

    const appModal = new bootstrap.Modal(document.getElementById('appendixModal'));
    const modalAppId = document.getElementById('modal-app-id');
    const modalAppTitle = document.getElementById('modal-app-title');
    const modalAppSummary = document.getElementById('modal-app-summary');
    const modalAppContains = document.getElementById('modal-app-contains');
    const modalAppLinks = document.getElementById('modal-app-links');

    new FrameworkReader({
        onItemSelect: function(el) {
            if (inspId) inspId.textContent = el.dataset.id || '-';
            if (inspTitle) inspTitle.textContent = el.dataset.title || 'Pilih Ketentuan';
            if (inspPillar) inspPillar.textContent = el.dataset.pillar || '-';
            if (inspChapter) inspChapter.textContent = el.dataset.chapter || '-';
            if (inspCadence) inspCadence.textContent = el.dataset.cadence || '-';
            if (inspSummary) inspSummary.textContent = el.dataset.summary || '-';
            if (inspInterpretation) inspInterpretation.textContent = el.dataset.interpretation || '-';
            if (inspReporting) inspReporting.textContent = el.dataset.reporting || '-';

            // Focus items
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

            // Evidence items
            if (inspEvidence) {
                inspEvidence.innerHTML = '';
                try {
                    const evArr = JSON.parse(el.dataset.evidence || '[]');
                    if (evArr.length === 0) {
                        inspEvidence.innerHTML = '<li class="text-muted">Gunakan SOP dan log audit standar.</li>';
                    } else {
                        evArr.forEach(ev => {
                            const li = document.createElement('li');
                            li.textContent = ev;
                            inspEvidence.appendChild(li);
                        });
                    }
                } catch(e) {}
            }

            // Appendices pills
            if (inspAppendices) {
                inspAppendices.innerHTML = '';
                try {
                    const appsArr = JSON.parse(el.dataset.appendices || '[]');
                    if (appsArr.length === 0) {
                        inspAppendices.innerHTML = '<span class="text-muted small">Tidak ada lampiran khusus yang ditunjuk.</span>';
                    } else {
                        appsArr.forEach(appId => {
                            const span = document.createElement('span');
                            span.className = 'badge bg-indigo-lt border px-2 py-1';
                            span.innerHTML = `<i class="fas fa-file-invoice me-1"></i> ${appId}`;
                            inspAppendices.appendChild(span);
                        });
                    }
                } catch(e) {}
            }
        }
    });

    // Appendix modal triggers
    document.querySelectorAll('.btn-view-appendix').forEach(btn => {
        btn.addEventListener('click', function() {
            modalAppId.textContent = this.dataset.id;
            modalAppTitle.textContent = this.dataset.title;
            modalAppSummary.textContent = this.dataset.summary || 'Tidak ada ringkasan.';

            modalAppContains.innerHTML = '';
            try {
                const items = JSON.parse(this.dataset.contains || '[]');
                items.forEach(item => {
                    const li = document.createElement('li');
                    li.className = 'list-group-item small text-secondary';
                    li.innerHTML = `<i class="fas fa-check-circle text-success me-2"></i> ${item}`;
                    modalAppContains.appendChild(li);
                });
            } catch(e) {}

            modalAppLinks.innerHTML = '';
            try {
                const links = JSON.parse(this.dataset.usedby || '[]');
                if (links.length === 0) {
                    modalAppLinks.innerHTML = '<span class="text-muted small">Tidak ditunjuk oleh requirement spesifik.</span>';
                } else {
                    links.forEach(link => {
                        const span = document.createElement('span');
                        span.className = 'badge bg-primary-lt';
                        span.textContent = link;
                        modalAppLinks.appendChild(span);
                    });
                }
            } catch(e) {}

            appModal.show();
        });
    });
});
</script>
@endpush
