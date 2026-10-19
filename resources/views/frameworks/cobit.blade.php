@extends('layouts.tabler')

@section('title', 'COBIT 2019 Governance Navigator - Raksantara')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/framework-reader.css') }}">
@endpush

@section('content')
@php
    $data = $cobitData;
    $chapters = collect($data['chapters'] ?? []);
    $domains = collect($data['domains'] ?? []);
    $systemPrinciples = collect($data['principles_system'] ?? []);
    $frameworkPrinciples = collect($data['principles_framework'] ?? []);
    $designFactors = collect($data['design_factors'] ?? []);
    $components = collect($data['components'] ?? []);
    $implPhases = collect($data['implementation_phases'] ?? []);
    $totalObjectives = $domains->sum('count');
@endphp

<div class="fw-page">
    {{-- COMPACT MODERN HERO --}}
    <div class="fw-hero">
        <div class="fw-hero-header">
            <div class="fw-kicker">
                <i class="fas fa-sitemap"></i> Standar Global ISACA
            </div>
            <span class="badge bg-warning-lt text-warning fw-bold px-3 py-1 rounded-pill">Edisi: COBIT 2019</span>
        </div>
        <h1 class="fw-title">COBIT 2019 Governance Navigator</h1>
        <p class="fw-lede">
            Enterprise Governance of Information and Technology (EGIT). Menghubungkan sasaran strategis bisnis dengan tata kelola dan manajemen teknologi informasi. Menelusuri 40 objektif Core Model (EDM, APO, BAI, DSS, MEA), 11 design factors penyesuaian konteks, hingga 7 fase continuous improvement.
        </p>
        <div class="fw-metric-row">
            <div class="fw-metric-pill">
                <span>Objektif Core Model:</span>
                <strong>{{ $totalObjectives }} Objektif</strong>
            </div>
            <div class="fw-metric-pill">
                <span>Domain Tata Kelola:</span>
                <strong>5 Domain (EDM-MEA)</strong>
            </div>
            <div class="fw-metric-pill">
                <span>Design Factors:</span>
                <strong>11 Faktor Tailoring</strong>
            </div>
            <div class="fw-metric-pill">
                <span>Fase Implementasi:</span>
                <strong>7 Langkah Roadmap</strong>
            </div>
        </div>
    </div>

    {{-- NAVIGATION TABS --}}
    <div class="fw-nav-container">
        <ul class="fw-nav">
            <li>
                <button class="fw-tab active" data-tab="tab-domains">
                    <i class="fas fa-sitemap"></i>
                    <span>Core Model (5 Domain & 40 Objektif)</span>
                </button>
            </li>
            <li>
                <button class="fw-tab" data-tab="tab-principles">
                    <i class="fas fa-compass"></i>
                    <span>Prinsip Tata Kelola & 7 Komponen</span>
                </button>
            </li>
            <li>
                <button class="fw-tab" data-tab="tab-tailoring">
                    <i class="fas fa-sliders-h"></i>
                    <span>11 Design Factors (Tailoring)</span>
                </button>
            </li>
            <li>
                <button class="fw-tab" data-tab="tab-lifecycle">
                    <i class="fas fa-rotate"></i>
                    <span>Siklus 7 Fase Implementasi</span>
                </button>
            </li>
        </ul>
    </div>

    {{-- TAB 1: DOMAINS (CORE MODEL) --}}
    <div class="fw-tab-content" id="tab-domains">
        <div class="d-flex flex-column gap-3">
            @foreach($domains as $dom)
                @php
                    $domColor = $dom['color'] ?? '#ea580c';
                @endphp
                <div class="card p-4 shadow-sm border-0 rounded-4">
                    <div class="d-flex flex-wrap align-items-center justify-content-between mb-3 gap-2">
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge fs-6 font-monospace px-3 py-2 text-white" style="background-color: {{ $domColor }};">
                                {{ $dom['id'] }}
                            </span>
                            <div>
                                <h3 class="h4 text-dark mb-0 fw-bold">{{ $dom['title'] ?? $dom['id'] }}</h3>
                                <span class="text-muted small">Domain {{ str_starts_with($dom['id'], 'EDM') ? 'Governance' : 'Management' }}</span>
                            </div>
                        </div>
                        <span class="badge bg-light text-dark border px-3 py-2 fw-bold fs-6">
                            {{ $dom['count'] ?? 0 }} Objektif
                        </span>
                    </div>

                    <p class="text-secondary mb-3" style="font-size: 0.92rem; line-height: 1.6;">{{ $dom['summary'] ?? '' }}</p>

                    <div class="p-3 bg-light rounded-3 border mb-3">
                        <strong class="text-dark small d-block mb-1"><i class="fas fa-star text-warning me-1"></i> Nilai Strategis Domain:</strong>
                        <span class="text-secondary small">{{ $dom['importance'] ?? '-' }}</span>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-7">
                            <label class="form-label text-muted small fw-bold text-uppercase mb-2">Fokus Utama Objektif</label>
                            <ul class="list-unstyled d-flex flex-column gap-2 mb-0">
                                @foreach($dom['focus'] ?? [] as $f)
                                    <li class="small text-secondary p-2 rounded bg-white border">
                                        <i class="fas fa-check-circle text-success me-2"></i> {{ $f }}
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                        <div class="col-md-5">
                            <label class="form-label text-muted small fw-bold text-uppercase mb-2">Contoh Bukti / Artefak</label>
                            <div class="d-flex flex-wrap gap-2 mb-3">
                                @foreach($dom['evidence'] ?? [] as $ev)
                                    <span class="badge bg-white text-dark border p-2">
                                        <i class="fas fa-folder-check text-primary me-1"></i> {{ $ev }}
                                    </span>
                                @endforeach
                            </div>
                            @if(!empty($dom['analogy']))
                                <div class="p-3 bg-warning-lt rounded-3 border border-warning-subtle text-secondary small">
                                    <i class="fas fa-lightbulb text-warning me-1"></i> <strong>Analogi Operasional:</strong>
                                    <div class="mt-1">{{ $dom['analogy'] }}</div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    {{-- TAB 2: PRINCIPLES & COMPONENTS --}}
    <div class="fw-tab-content d-none" id="tab-principles">
        <div class="row g-3">
            <div class="col-lg-6">
                <div class="card p-3 h-100 shadow-sm border-0">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <h3 class="h5 text-dark mb-0"><i class="fas fa-compass text-primary me-2"></i> 6 Prinsip Sistem Tata Kelola</h3>
                        <span class="badge bg-primary-lt">Governance System</span>
                    </div>
                    <p class="text-muted small mb-3">Prinsip fundamental agar sistem tata kelola TI perusahaan berjalan efektif dan adaptif:</p>

                    <div class="d-flex flex-column gap-2">
                        @foreach($systemPrinciples as $idx => $p)
                            <div class="p-3 rounded-3 bg-light border">
                                <div class="fw-bold text-primary mb-1">
                                    <span class="badge bg-primary text-white me-1">{{ $p['id'] ?? ($idx + 1) }}</span> {{ $p['title'] ?? '' }}
                                </div>
                                <p class="mb-0 text-muted small">{{ $p['summary'] ?? ($p['desc'] ?? '') }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="card p-3 h-100 shadow-sm border-0">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <h3 class="h5 text-dark mb-0"><i class="fas fa-layer-group text-warning me-2"></i> 3 Prinsip Framework & 7 Komponen</h3>
                        <span class="badge bg-warning-lt">Framework Principles</span>
                    </div>

                    <div class="d-flex flex-column gap-2 mb-4">
                        @foreach($frameworkPrinciples as $idx => $fp)
                            <div class="p-3 rounded-3 bg-light border">
                                <div class="fw-bold text-warning mb-1">
                                    <span class="badge bg-warning text-dark me-1">{{ $fp['id'] ?? ($idx + 1) }}</span> {{ $fp['title'] ?? '' }}
                                </div>
                                <p class="mb-0 text-muted small">{{ $fp['summary'] ?? ($fp['desc'] ?? '') }}</p>
                            </div>
                        @endforeach
                    </div>

                    <h4 class="h6 text-dark fw-bold mb-2"><i class="fas fa-cubes text-success me-1"></i> 7 Komponen Tata Kelola (Enablers):</h4>
                    <div class="d-flex flex-column gap-2">
                        @foreach($components as $comp)
                            <div class="p-2 px-3 rounded-3 bg-light border d-flex align-items-center justify-content-between">
                                <div>
                                    <span class="badge bg-success-lt font-monospace me-2">{{ is_array($comp) ? ($comp['id'] ?? 'CMP') : 'CMP' }}</span>
                                    <strong class="text-dark small">{{ is_array($comp) ? ($comp['title'] ?? ($comp['name'] ?? '')) : $comp }}</strong>
                                    @if(is_array($comp) && !empty($comp['summary']))
                                        <div class="text-muted small mt-1 ps-4">{{ $comp['summary'] }}</div>
                                    @endif
                                </div>
                                @if(is_array($comp) && !empty($comp['group']))
                                    <span class="badge bg-white border text-secondary small">{{ $comp['group'] }}</span>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- TAB 3: TAILORING (11 DESIGN FACTORS) --}}
    <div class="fw-tab-content d-none" id="tab-tailoring">
        <div class="card p-3 shadow-sm border-0">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <div>
                    <h3 class="h5 text-dark mb-1"><i class="fas fa-sliders-h text-primary me-2"></i> 11 Design Factors untuk Tailoring EGIT</h3>
                    <p class="text-muted small mb-0">COBIT tidak bersifat one-size-fits-all. Gunakan 11 variabel ini untuk menyesuaikan prioritas objektif tata kelola.</p>
                </div>
                <span class="badge bg-primary-lt fs-6">11 Variabel</span>
            </div>

            <div class="row g-3">
                @foreach($designFactors as $df)
                    <div class="col-md-6 col-lg-4">
                        <div class="p-3 rounded-3 bg-light border h-100 d-flex flex-column">
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <span class="badge bg-primary text-white font-monospace">{{ $df['id'] ?? 'DF' }}</span>
                                @if(!empty($df['group']))
                                    <span class="badge bg-white border text-muted small">{{ $df['group'] }}</span>
                                @endif
                            </div>
                            <h4 class="text-dark fw-bold mb-1 fs-6">{{ $df['title'] ?? ($df['name'] ?? '') }}</h4>
                            <p class="mb-2 text-muted small" style="line-height: 1.5;">
                                {{ $df['summary'] ?? ($df['desc'] ?? '') }}
                            </p>
                            @if(!empty($df['importance']))
                                <div class="mt-auto pt-2 border-top">
                                    <span class="text-secondary small"><strong>Dampak:</strong> {{ $df['importance'] }}</span>
                                </div>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- TAB 4: IMPLEMENTATION LIFECYCLE --}}
    <div class="fw-tab-content d-none" id="tab-lifecycle">
        <div class="card p-3 shadow-sm border-0">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <div>
                    <h3 class="h5 text-dark mb-1"><i class="fas fa-rotate text-teal me-2"></i> Siklus Implementasi 7 Langkah COBIT 2019</h3>
                    <p class="text-muted small mb-0">Panduan continuous improvement terstruktur untuk memastikan penerapan tata kelola menghasilkan nilai bisnis berkelanjutan.</p>
                </div>
                <span class="badge bg-teal-lt fs-6">7 Fase Roadmap</span>
            </div>

            <div class="row g-3">
                @foreach($implPhases as $idx => $phase)
                    <div class="col-md-6 col-lg-4">
                        <div class="p-3 rounded-3 bg-light border h-100 d-flex flex-column">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="badge bg-primary text-white">Fase {{ $idx + 1 }}</span>
                                @if(!empty($phase['group']))
                                    <span class="badge bg-white border text-muted small">{{ $phase['group'] }}</span>
                                @endif
                            </div>
                            <h4 class="text-dark mb-2 fs-6 fw-bold">{{ $phase['title'] ?? '' }}</h4>
                            <p class="text-muted small mb-2">{{ $phase['summary'] ?? ($phase['desc'] ?? '') }}</p>
                            @if(!empty($phase['educational_tips']))
                                <div class="mt-auto pt-2 border-top">
                                    <div class="small text-info fw-bold mb-1"><i class="fas fa-lightbulb me-1"></i> Best Practice Tip:</div>
                                    <ul class="ps-3 mb-0 small text-secondary">
                                        @foreach($phase['educational_tips'] as $tip)
                                            <li>{{ $tip }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif
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
    new FrameworkReader();
});
</script>
@endpush
