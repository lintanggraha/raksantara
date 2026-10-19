@extends('layouts.tabler')

@section('title', 'OWASP Top 10 2025 Application Security - Raksantara')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/framework-reader.css') }}">
@endpush

@section('content')
@php
    $items = collect($requirements);
    $apps = collect($appendices);
    $totalRisks = $items->count();
    $totalAppendices = $apps->count();
    $firstItem = $items->first();
@endphp

<div class="fw-page">
    {{-- COMPACT MODERN HERO --}}
    <div class="fw-hero">
        <div class="fw-hero-header">
            <div class="fw-kicker">
                <i class="fas fa-bug"></i> Open Worldwide Application Security Project
            </div>
            <span class="badge bg-danger-lt text-danger fw-bold px-3 py-1 rounded-pill">Edisi: OWASP Top 10 2025</span>
        </div>
        <h1 class="fw-title">OWASP Top 10: 2025 Application Security</h1>
        <p class="fw-lede">
            Standar konsensus global mengenai 10 risiko keamanan aplikasi web paling kritikal. Memetakan vektor serangan (Access Control, Cryptographic Failures, Injection, Insecure Design, hingga SSRF) disertai panduan mitigasi Secure SDLC, pengujian SAST/DAST, serta verifikasi ASVS.
        </p>
        <div class="fw-metric-row">
            <div class="fw-metric-pill">
                <span>Risiko Teratas:</span>
                <strong>{{ $totalRisks }} Kategori Ancaman</strong>
            </div>
            <div class="fw-metric-pill">
                <span>Cakupan Kode:</span>
                <strong>A01 s/d A10</strong>
            </div>
            <div class="fw-metric-pill">
                <span>Mitigasi & Rujukan:</span>
                <strong>{{ $totalAppendices }} Modul Remediasi</strong>
            </div>
            <div class="fw-metric-pill">
                <span>Prinsip Inti:</span>
                <strong>Secure by Design</strong>
            </div>
        </div>
    </div>

    {{-- NAVIGATION TABS --}}
    <div class="fw-nav-container">
        <ul class="fw-nav">
            <li>
                <button class="fw-tab active" data-tab="tab-explorer">
                    <i class="fas fa-list-check"></i>
                    <span>Eksplorasi 10 Risiko Teratas ({{ $totalRisks }})</span>
                </button>
            </li>
            <li>
                <button class="fw-tab" data-tab="tab-overview">
                    <i class="fas fa-cubes"></i>
                    <span>Matriks Ringkasan & Dampak</span>
                </button>
            </li>
            <li>
                <button class="fw-tab" data-tab="tab-mitigation">
                    <i class="fas fa-shield-alt"></i>
                    <span>Strategi Mitigasi Terpadu</span>
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
                            placeholder="Cari risiko (misal: Access Control, Injection, SSRF)..."
                            autocomplete="off"
                        >
                        <span class="fw-search-kbd">/</span>
                    </div>
                </div>
                <div class="fw-counter-bar">
                    <span id="fw-counter">{{ $totalRisks }} dari {{ $totalRisks }} risiko ditampilkan</span>
                    <span class="text-muted small"><i class="fas fa-arrows-alt-v me-1"></i> Gunakan ↑/↓ keyboard</span>
                </div>

                <div class="fw-list" id="fw-items-container">
                    @foreach($items as $index => $item)
                        @php
                            $searchIndex = strtolower(($item['id'] ?? '') . ' ' . ($item['title'] ?? '') . ' ' . ($item['summary'] ?? '') . ' ' . ($item['chapter_title'] ?? ''));
                        @endphp
                        <button
                            type="button"
                            class="fw-item {{ $index === 0 ? 'active' : '' }}"
                            data-search-index="{{ $searchIndex }}"
                            data-id="{{ $item['id'] ?? '' }}"
                            data-title="{{ $item['title'] ?? '' }}"
                            data-chapter="{{ $item['chapter_title'] ?? $item['id'] }}"
                            data-cadence="{{ $item['cadence'] ?? 'Audit Rutin' }}"
                            data-summary="{{ $item['summary'] ?? '' }}"
                            data-analogy="{{ $item['analogy'] ?? '' }}"
                            data-focus='@json($item['focus'] ?? [])'
                            data-evidence='@json($item['evidence'] ?? [])'
                            data-appendices='@json($item['appendices'] ?? [])'
                        >
                            <div class="fw-item-top">
                                <span class="fw-item-code text-danger">{{ $item['id'] }}</span>
                                <span class="fw-item-tag bg-danger-lt text-danger">Kritis</span>
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
                        <span class="fw-badge-legal bg-danger text-white" id="insp-id">{{ $firstItem['id'] ?? '-' }}</span>
                        <div class="d-flex align-items-center gap-2">
                            <button type="button" class="btn btn-sm btn-outline-primary" data-copy-target="insp-summary" title="Salin ringkasan risiko">
                                <i class="fas fa-copy me-1"></i> Salin Teks
                            </button>
                            <a href="{{ route('analysis.cross-mapping') }}" class="btn btn-sm btn-outline-secondary" title="Buka pemetaan regulasi silang">
                                <i class="fas fa-network-wired me-1"></i> Cross-Mapping
                            </a>
                        </div>
                    </div>
                    <h2 class="fw-inspector-title" id="insp-chapter">{{ $firstItem['chapter_title'] ?? $firstItem['title'] ?? 'Pilih Risiko' }}</h2>
                    <div class="text-secondary small mt-1 mb-2 fw-medium" id="insp-title">{{ $firstItem['title'] ?? '' }}</div>
                    <div class="fw-meta-chips">
                        <span class="fw-meta-chip"><i class="fas fa-clock text-warning"></i> <span id="insp-cadence">-</span></span>
                        <span class="fw-meta-chip"><i class="fas fa-shield-cat text-danger"></i> Prioritas Tertinggi</span>
                    </div>
                </div>

                {{-- SUMMARY --}}
                <div class="fw-section">
                    <div class="fw-section-title"><i class="fas fa-align-left text-primary"></i> Ringkasan Vektor Serangan & Dampak</div>
                    <div class="fw-body-text" id="insp-summary">{{ $firstItem['summary'] ?? '-' }}</div>
                </div>

                {{-- ANALOGY --}}
                <div class="fw-section">
                    <div class="fw-section-title"><i class="fas fa-lightbulb text-warning"></i> Analogi Operasional Sederhana</div>
                    <div class="p-3 bg-light rounded-3 text-secondary" style="font-size: 0.88rem; line-height: 1.55;" id="insp-analogy">-</div>
                </div>

                {{-- FOCUS MITIGATION --}}
                <div class="fw-section">
                    <div class="fw-section-title"><i class="fas fa-bullseye text-danger"></i> Fokus Mitigasi & Pengendalian Teknis</div>
                    <ul class="fw-bullets-list" id="insp-focus"></ul>
                </div>

                {{-- EVIDENCE NEEDED --}}
                <div class="fw-section">
                    <div class="fw-section-title"><i class="fas fa-folder-check text-success"></i> Dokumen Bukti & Verifikasi Audit</div>
                    <ul class="fw-bullets-list" id="insp-evidence"></ul>
                </div>

                {{-- LINKED APPENDICES --}}
                <div class="fw-section">
                    <div class="fw-section-title"><i class="fas fa-link text-indigo"></i> Referensi Standar & Verifikasi ASVS</div>
                    <div class="d-flex flex-wrap gap-2" id="insp-appendices"></div>
                </div>
            </div>
        </div>
    </div>

    {{-- TAB 2: OVERVIEW MATRIX --}}
    <div class="fw-tab-content d-none" id="tab-overview">
        <div class="row g-3">
            @foreach($items as $item)
                <div class="col-md-6">
                    <div class="card p-3 h-100 shadow-sm border-0">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="badge bg-danger text-white font-monospace">{{ $item['id'] }}</span>
                            <span class="badge bg-secondary-lt">{{ $item['owner'] ?? 'SecDevOps' }}</span>
                        </div>
                        <h4 class="h5 mb-1 text-dark fw-bold">{{ $item['chapter_title'] ?? $item['id'] }}</h4>
                        <div class="small text-muted mb-2">{{ $item['title'] }}</div>
                        <p class="small text-secondary mb-3">{{ Str::limit($item['summary'] ?? '', 140) }}</p>
                        <div class="mt-auto pt-2 border-top d-flex gap-1 flex-wrap">
                            @foreach($item['appendices'] ?? [] as $app)
                                <span class="badge bg-light border text-dark">{{ $app }}</span>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    {{-- TAB 3: MITIGATION STRATEGIES --}}
    <div class="fw-tab-content d-none" id="tab-mitigation">
        <div class="card p-4 shadow-sm border-0">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <h3 class="h5 text-dark mb-0"><i class="fas fa-shield-alt text-danger me-2"></i> Strategi Mitigasi Aplikasi Web Terpadu</h3>
                <span class="badge bg-danger-lt">Defense-in-Depth</span>
            </div>

            <div class="row g-3">
                <div class="col-md-4">
                    <div class="p-3 rounded-3 bg-light border h-100">
                        <h4 class="h6 text-danger fw-bold mb-2"><i class="fas fa-code me-1"></i> 1. Secure Coding & Shift-Left</h4>
                        <p class="small text-muted mb-0">
                            Integrasikan Static Application Security Testing (SAST) dan Software Composition Analysis (SCA) di dalam pipeline CI/CD sebelum kode masuk ke lingkungan staging atau production.
                        </p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="p-3 rounded-3 bg-light border h-100">
                        <h4 class="h6 text-danger fw-bold mb-2"><i class="fas fa-key me-1"></i> 2. Centralized Authentication</h4>
                        <p class="small text-muted mb-0">
                            Hindari membuat mekanisme autentikasi dan session management custom. Manfaatkan framework standar industri dengan otentikasi multi-faktor (MFA) wajib untuk akun berprivilese.
                        </p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="p-3 rounded-3 bg-light border h-100">
                        <h4 class="h6 text-danger fw-bold mb-2"><i class="fas fa-database me-1"></i> 3. Parameterized Queries & Zero Trust</h4>
                        <p class="small text-muted mb-0">
                            Terapkan ORM / parameterized queries secara konsisten untuk mencegah SQL Injection, validasi skema input (allowlist), dan isolasi network microservices via mTLS.
                        </p>
                    </div>
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
    const inspChapter = document.getElementById('insp-chapter');
    const inspTitle = document.getElementById('insp-title');
    const inspCadence = document.getElementById('insp-cadence');
    const inspSummary = document.getElementById('insp-summary');
    const inspAnalogy = document.getElementById('insp-analogy');
    const inspFocus = document.getElementById('insp-focus');
    const inspEvidence = document.getElementById('insp-evidence');
    const inspAppendices = document.getElementById('insp-appendices');

    new FrameworkReader({
        onItemSelect: function(el) {
            if (inspId) inspId.textContent = el.dataset.id || '-';
            if (inspChapter) inspChapter.textContent = el.dataset.chapter || '-';
            if (inspTitle) inspTitle.textContent = el.dataset.title || '';
            if (inspCadence) inspCadence.textContent = el.dataset.cadence || '-';
            if (inspSummary) inspSummary.textContent = el.dataset.summary || '-';
            if (inspAnalogy) inspAnalogy.textContent = el.dataset.analogy || 'Terapkan prinsip secure-by-design.';

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
                        inspEvidence.innerHTML = '<li class="text-muted">Hasil scan SAST/DAST, unit test keamanan, atau dokumen code review.</li>';
                    } else {
                        evArr.forEach(ev => {
                            const li = document.createElement('li');
                            li.textContent = ev;
                            inspEvidence.appendChild(li);
                        });
                    }
                } catch(e) {}
            }

            // Appendices
            if (inspAppendices) {
                inspAppendices.innerHTML = '';
                try {
                    const appsArr = JSON.parse(el.dataset.appendices || '[]');
                    if (appsArr.length === 0) {
                        inspAppendices.innerHTML = '<span class="text-muted small">Tidak ada referensi lampiran khusus.</span>';
                    } else {
                        appsArr.forEach(appId => {
                            const span = document.createElement('span');
                            span.className = 'badge bg-danger-lt border px-2 py-1';
                            span.textContent = appId;
                            inspAppendices.appendChild(span);
                        });
                    }
                } catch(e) {}
            }
        }
    });
});
</script>
@endpush
