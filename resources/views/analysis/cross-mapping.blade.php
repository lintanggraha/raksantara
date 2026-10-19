@extends('layouts.tabler')

@section('title', 'Cross-Mapping Regulasi & Standar Keamanan - Raksantara')

@push('styles')
<style>
.cmap-page {
    --ink: #0f172a;
    --muted: #475569;
    --line: rgba(15, 23, 42, 0.1);
    --shell: linear-gradient(180deg, #f8fafc 0%, #f1f5f9 100%);
    color: var(--ink);
    padding: .25rem;
}
.cmap-shell { display: grid; gap: 1.25rem; }

/* Modern Gradient Hero */
.cmap-hero {
    display: grid; grid-template-columns: 1.6fr .9fr; gap: 1.25rem;
    align-items: stretch; min-height: 250px;
    padding: 1.5rem 1.75rem; border-radius: 24px; overflow: hidden; position: relative;
    background:
        radial-gradient(circle at top right, rgba(56, 189, 248, .22), transparent 40%),
        radial-gradient(circle at bottom left, rgba(99, 102, 241, .18), transparent 35%),
        linear-gradient(135deg, #0f172a 0%, #1e293b 55%, #0f2744 100%);
    box-shadow: 0 16px 36px rgba(15, 23, 42, .25);
    color: #ffffff;
}
.cmap-hero > * { position: relative; z-index: 1; }
.cmap-kicker {
    display: inline-flex; align-items: center; gap: .45rem; padding: .3rem .75rem;
    border-radius: 999px; background: rgba(255, 255, 255, .12); color: #93c5fd;
    font-size: .74rem; font-weight: 700; text-transform: uppercase; letter-spacing: .06em;
    border: 1px solid rgba(255, 255, 255, .15);
}
.cmap-title { margin: .75rem 0 .4rem; color: #ffffff; font-size: clamp(1.6rem, 2.8vw, 2.3rem); font-weight: 800; line-height: 1.15; }
.cmap-lede { margin: 0; max-width: 720px; color: #cbd5e1; line-height: 1.55; font-size: .88rem; }
.cmap-metric-grid { display: flex; flex-wrap: wrap; gap: .65rem; margin-top: 1.15rem; }
.cmap-metric {
    padding: .5rem .85rem; border-radius: 12px; background: rgba(255, 255, 255, .08);
    border: 1px solid rgba(255, 255, 255, .15); backdrop-filter: blur(8px);
    display: inline-flex; align-items: center; gap: .6rem;
}
.cmap-metric label { display: block; font-size: .72rem; font-weight: 600; color: #94a3b8; margin: 0; }
.cmap-metric strong { display: block; color: #38bdf8; font-size: 1.15rem; font-weight: 800; line-height: 1; }

/* Submenu View Tabs */
.cmap-nav-container {
    background: #ffffff; border: 1px solid var(--line); border-radius: 16px;
    padding: .45rem .65rem; box-shadow: 0 1px 3px rgba(15, 23, 42, .05);
}
.cmap-nav {
    display: flex; flex-wrap: wrap; gap: .35rem; margin: 0; padding: 0; list-style: none;
}
.cmap-tab {
    display: inline-flex; align-items: center; gap: .5rem; padding: .5rem 1rem;
    border-radius: 10px; font-size: .84rem; font-weight: 600; color: #64748b;
    background: transparent; border: none; cursor: pointer; transition: all .15s ease;
}
.cmap-tab:hover { background: #f8fafc; color: #2563eb; }
.cmap-tab.active { background: #eff6ff; color: #2563eb; font-weight: 700; box-shadow: inset 0 0 0 1px rgba(37, 99, 235, .2); }
.cmap-tab.active i { color: #2563eb; }

/* Panel Card */
.cmap-panel {
    padding: 1.25rem; border-radius: 20px; border: 1px solid var(--line);
    background: #ffffff; box-shadow: 0 4px 14px -2px rgba(15, 23, 42, .06);
}
.cmap-panel-head { display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: .75rem; margin-bottom: 1rem; }
.cmap-panel-head h3 { margin: 0; font-size: 1.05rem; font-weight: 800; color: #0f172a; }

/* Framework Filter Chips */
.cmap-fw-filter { display: flex; flex-wrap: wrap; gap: .45rem; }
.cmap-fw-chip {
    display: inline-flex; align-items: center; gap: .4rem; padding: .35rem .75rem;
    border-radius: 10px; font-size: .78rem; font-weight: 700; cursor: pointer;
    border: 1px solid var(--line); background: #f8fafc; color: #475569; transition: all .15s ease;
    user-select: none;
}
.cmap-fw-chip:hover { background: #f1f5f9; }
.cmap-fw-chip.active { background: #0f172a; color: #ffffff; border-color: #0f172a; }

/* D3 Graph Container */
.cmap-graph-wrapper {
    position: relative; border-radius: 16px; border: 1px solid #e2e8f0;
    background: radial-gradient(#f1f5f9 1.5px, #ffffff 1.5px);
    background-size: 24px 24px;
    overflow: hidden;
}
.cmap-graph-toolbar {
    position: absolute; top: 12px; right: 12px; z-index: 10;
    display: flex; gap: .35rem; background: rgba(255, 255, 255, .92);
    backdrop-filter: blur(8px); padding: .3rem; border-radius: 10px;
    border: 1px solid #cbd5e1; box-shadow: 0 4px 12px rgba(15, 23, 42, .08);
}
.cmap-tool-btn {
    width: 32px; height: 32px; display: inline-flex; align-items: center; justify-content: center;
    border-radius: 6px; border: 1px solid #e2e8f0; background: #ffffff; color: #334155;
    font-size: .82rem; cursor: pointer; transition: all .15s ease;
}
.cmap-tool-btn:hover { background: #f1f5f9; color: #2563eb; border-color: #cbd5e1; }

.cmap-graph-legend {
    position: absolute; bottom: 12px; left: 12px; z-index: 10;
    background: rgba(255, 255, 255, .92); backdrop-filter: blur(8px);
    padding: .5rem .75rem; border-radius: 10px; border: 1px solid #cbd5e1;
    font-size: .75rem; color: #475569; display: flex; flex-direction: column; gap: .25rem;
    box-shadow: 0 4px 12px rgba(15, 23, 42, .06);
}
.cmap-legend-item { display: inline-flex; align-items: center; gap: .45rem; }
.cmap-legend-line { width: 20px; height: 3px; display: inline-block; border-radius: 2px; }

#cmap-graph-svg {
    width: 100%; height: 700px; display: block; cursor: grab;
}
#cmap-graph-svg:active { cursor: grabbing; }

/* Custom Floating Tooltip */
.cmap-tooltip {
    position: absolute; z-index: 100; pointer-events: none;
    background: #ffffff; border-radius: 12px; width: 300px;
    box-shadow: 0 16px 36px rgba(15, 23, 42, .18); border: 1px solid #cbd5e1;
    overflow: hidden; opacity: 0; transition: opacity .15s ease;
    font-size: .8rem;
}
.cmap-tooltip.show { opacity: 1; }
.cmap-tooltip-head {
    padding: .65rem .85rem; color: #ffffff; font-weight: 700;
    display: flex; align-items: center; justify-content: space-between;
}
.cmap-tooltip-body { padding: .75rem .85rem; }

/* Inspector Drawer / Bottom Card */
.cmap-inspector-card {
    border-radius: 16px; border: 1px solid #cbd5e1; background: #ffffff;
    box-shadow: 0 10px 25px rgba(15, 23, 42, .08); padding: 1.25rem;
    margin-top: 1rem; display: none; animation: fadeIn .2s ease;
}
@keyframes fadeIn { from { opacity: 0; transform: translateY(6px); } to { opacity: 1; transform: translateY(0); } }

/* Equivalency Cluster Cards */
.cmap-cluster-card {
    border: 1px solid var(--line); border-radius: 16px; padding: 1.15rem; background: #ffffff;
    transition: all .18s ease; height: 100%; display: flex; flex-direction: column;
}
.cmap-cluster-card:hover { transform: translateY(-2px); box-shadow: 0 12px 24px rgba(15, 23, 42, .06); border-color: #2563eb; }
.cmap-tag {
    display: inline-block; padding: .22rem .55rem; border-radius: 6px; font-size: .74rem;
    font-weight: 700; margin: .15rem; font-family: ui-monospace, monospace; cursor: pointer;
    transition: all .15s ease;
}
.cmap-tag:hover { background: #e0f2fe; color: #0369a1; }
</style>
@endpush

@section('content')
@php
    $frameworks = $mappingData['frameworks'] ?? [];
    $controls = $mappingData['controls'] ?? [];
    $groups = $mappingData['groups'] ?? [];
    $links = $mappingData['links'] ?? [];

    $controlsById = collect($controls)->keyBy('id');
    $controlsByFramework = collect($controls)->groupBy('framework');
    $frameworksById = collect($frameworks)->keyBy('id');

    $frameworkRouteMap = [
        'iso27001' => route('frameworks.iso27001'),
        'nist' => route('frameworks.nist'),
        'cobit' => route('frameworks.cobit'),
        'seojk' => route('frameworks.seojk'),
        'pbi' => route('frameworks.pbi'),
        'padg' => route('frameworks.padg'),
        'owasp_top10' => route('frameworks.owasp-top10'),
        'owasp_asvs' => route('frameworks.owasp-asvs'),
        'pdp' => route('frameworks.pdp'),
        'padk' => route('frameworks.padk')
    ];
@endphp

<div class="cmap-page">
    <div class="cmap-shell">
        {{-- HERO HEADER --}}
        <div class="cmap-hero">
            <div>
                <span class="cmap-kicker">
                    <i class="fas fa-network-wired me-1"></i> Analisis Kepatuhan • Peta Topologi Relasi & Matriks Kesetaraan
                </span>
                <h1 class="cmap-title">Cross-Mapping Regulasi & Standar</h1>
                <p class="cmap-lede">
                    Visualisasi relasi kesetaraan kontrol antar standar global (ISO 27001, NIST CSF, COBIT, OWASP) dan regulasi nasional (POJK 11/2022, PADK 1/2026, SEOJK 29/2022, PBI 02/2024, PADG 32/2025, UU PDP 27/2022). Terapkan satu kontrol untuk memenuhi banyak kepatuhan sekaligus (Prinsip <em>Test Once, Comply Many</em>).
                </p>
                <div class="cmap-metric-grid">
                    <div class="cmap-metric">
                        <label>Framework:</label>
                        <strong>{{ count($frameworks) }} Standar</strong>
                    </div>
                    <div class="cmap-metric">
                        <label>Titik Kontrol:</label>
                        <strong>{{ count($controls) }} Titik</strong>
                    </div>
                    <div class="cmap-metric">
                        <label>Klaster Harmonisasi:</label>
                        <strong>{{ count($groups) }} Domain</strong>
                    </div>
                    <div class="cmap-metric">
                        <label>Tautan Relasi Silang:</label>
                        <strong>{{ count($links) }} Tautan</strong>
                    </div>
                </div>
            </div>
            <div style="background: rgba(255, 255, 255, .1); border: 1px solid rgba(255, 255, 255, .2); border-radius: 18px; padding: 1.25rem; backdrop-filter: blur(8px); display: flex; flex-direction: column; justify-content: center;">
                <label style="font-size: .72rem; font-weight: 700; color: #93c5fd; text-transform: uppercase;">Prinsip "Test Once, Comply Many"</label>
                <h3 style="color: #ffffff; font-size: 1.05rem; margin: .35rem 0 .5rem;">Efisiensi Audit Hingga 60%</h3>
                <p style="color: #cbd5e1; font-size: .82rem; line-height: 1.5; margin: 0;">
                    Dengan memetakan kontrol yang setara, organisasi menghindari duplikasi implementasi SOP, mempersingkat siklus pembuktian audit, serta mencegah silo kepatuhan antar divisi.
                </p>
            </div>
        </div>

        {{-- NAVIGATION PERSPECTIVE TABS --}}
        <div class="cmap-nav-container">
            <ul class="cmap-nav">
                <li>
                    <button class="cmap-tab active" data-tab="tab-map">
                        <i class="fas fa-network-wired"></i>
                        <span>Peta Relasi Graf (Network Topology Map)</span>
                    </button>
                </li>
                <li>
                    <button class="cmap-tab" data-tab="tab-matrix">
                        <i class="fas fa-table-cells"></i>
                        <span>Matriks Kesetaraan Kontrol (Matrix Table)</span>
                    </button>
                </li>
                <li>
                    <button class="cmap-tab" data-tab="tab-clusters">
                        <i class="fas fa-layer-group"></i>
                        <span>5 Klaster Harmonisasi Utama (Equivalency Clusters)</span>
                    </button>
                </li>
            </ul>
        </div>

        {{-- TAB 1: INTERACTIVE D3 NETWORK TOPOLOGY MAP --}}
        <div class="cmap-tab-content" id="tab-map">
            <div class="cmap-panel">
                <div class="cmap-panel-head">
                    <div>
                        <h3><i class="fas fa-project-diagram me-2 text-primary"></i> Peta Graf Hubungan Silang Antar Standar & Regulasi</h3>
                        <p class="text-muted small mb-0">Klik pada node kontrol untuk mengisolasi jalur pemetaan kesetaraan di seluruh standar terkait. Seret node untuk mengatur tata letak.</p>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge bg-primary-lt" id="graph-stat-badge">{{ count($controls) }} Kontrol Aktif</span>
                        <button class="btn btn-sm btn-outline-secondary" id="cmap-reset-graph-btn">
                            <i class="fas fa-rotate me-1"></i> Reset Tampilan
                        </button>
                    </div>
                </div>

                {{-- Interactive Framework Filter Switcher --}}
                <div class="mb-3 p-3 bg-light rounded-3 border">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <strong class="small text-dark text-uppercase"><i class="fas fa-sliders me-1"></i> Filter Standar & Regulasi yang Ditampilkan:</strong>
                        <div class="d-flex gap-2">
                            <button class="btn btn-xs btn-outline-primary" id="btn-select-all-fw">Pilih Semua</button>
                            <button class="btn btn-xs btn-outline-secondary" id="btn-select-none-fw">Pilih Minimum</button>
                        </div>
                    </div>
                    <div class="cmap-fw-filter" id="cmap-fw-filter-pills">
                        @foreach($frameworks as $fw)
                            <div class="cmap-fw-chip active" data-fw="{{ $fw['id'] }}" style="border-left: 4px solid {{ $fw['color'] ?? '#0284c7' }};">
                                <span class="badge rounded-circle p-1" style="background: {{ $fw['color'] ?? '#0284c7' }}; width: 8px; height: 8px;"></span>
                                <span>{{ $fw['name'] }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- Quick Filter By Cluster --}}
                <div class="d-flex flex-wrap align-items-center gap-2 mb-3">
                    <span class="small text-muted fw-bold">Sorot Klaster:</span>
                    <button class="btn btn-xs btn-outline-dark cluster-filter-btn active" data-cluster="all">Semua Domain</button>
                    @foreach($groups as $grp)
                        <button class="btn btn-xs btn-outline-primary cluster-filter-btn" data-cluster="{{ $grp['id'] }}">
                            {{ $grp['name'] }}
                        </button>
                    @endforeach
                </div>

                {{-- Visual Graph Canvas Container --}}
                <div class="cmap-graph-wrapper" id="cmap-graph-wrapper">
                    {{-- Zoom / Pan Toolbar --}}
                    <div class="cmap-graph-toolbar">
                        <button class="cmap-tool-btn" id="tool-zoom-in" title="Perbesar (Zoom In)"><i class="fas fa-plus"></i></button>
                        <button class="cmap-tool-btn" id="tool-zoom-out" title="Perkecil (Zoom Out)"><i class="fas fa-minus"></i></button>
                        <button class="cmap-tool-btn" id="tool-zoom-fit" title="Paskan Layar"><i class="fas fa-expand"></i></button>
                        <button class="cmap-tool-btn" id="tool-zoom-reset" title="Reset Posisi"><i class="fas fa-crosshairs"></i></button>
                    </div>

                    {{-- Legend Info Bar --}}
                    <div class="cmap-graph-legend">
                        <div class="cmap-legend-item">
                            <span class="cmap-legend-line" style="background: #cbd5e1;"></span>
                            <span>Relasi Hierarki (Framework &rarr; Kontrol)</span>
                        </div>
                        <div class="cmap-legend-item">
                            <span class="cmap-legend-line" style="background: #0284c7; border-top: 2px dashed #0284c7; height: 0;"></span>
                            <span class="fw-bold text-primary">Relasi Kesetaraan Antar Standar</span>
                        </div>
                        <div class="cmap-legend-item">
                            <span class="badge rounded-circle bg-white border border-2 border-dark" style="width: 12px; height: 12px; padding: 0;"></span>
                            <span>Node Pusat Framework (Hub)</span>
                        </div>
                        <div class="cmap-legend-item">
                            <span class="badge rounded-circle bg-primary" style="width: 10px; height: 10px; padding: 0;"></span>
                            <span>Titik Kontrol Spesifik</span>
                        </div>
                    </div>

                    {{-- SVG Element for D3.js --}}
                    <svg id="cmap-graph-svg"></svg>

                    {{-- Floating Tooltip --}}
                    <div class="cmap-tooltip" id="cmap-tooltip">
                        <div class="cmap-tooltip-head" id="cmap-tooltip-head">
                            <span id="cmap-tt-title">Detail Node</span>
                        </div>
                        <div class="cmap-tooltip-body">
                            <span class="badge bg-secondary-lt mb-2 font-monospace" id="cmap-tt-badge">-</span>
                            <p class="mb-2 text-dark small" id="cmap-tt-desc" style="line-height: 1.45;">-</p>
                            <div id="cmap-tt-equivalents-wrap" style="display: none;">
                                <hr class="my-2">
                                <strong class="d-block text-muted small text-uppercase mb-1" style="font-size: .7rem;">Setara dengan:</strong>
                                <div id="cmap-tt-equivalents" class="d-flex flex-wrap gap-1"></div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Interactive Inspector Drawer for Selected Node --}}
                <div class="cmap-inspector-card" id="cmap-inspector-drawer">
                    <div class="d-flex flex-wrap justify-content-between align-items-start gap-2 mb-3">
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge fs-6 font-monospace px-3 py-2 text-white" id="insp-fw-badge">ISO 27001</span>
                            <div>
                                <h4 class="text-dark mb-0 fw-bold fs-5" id="insp-ctrl-name">Nama Kontrol</h4>
                                <span class="text-muted small font-monospace" id="insp-ctrl-id">ctrl-id</span>
                            </div>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <a href="#" class="btn btn-sm btn-outline-primary" id="insp-open-framework-link" target="_blank">
                                <i class="fas fa-arrow-up-right-from-square me-1"></i> Buka di Framework Reader
                            </a>
                            <button type="button" class="btn-close" id="insp-close-btn"></button>
                        </div>
                    </div>

                    <div class="row g-3">
                        <div class="col-lg-5">
                            <div class="p-3 bg-light rounded-3 border h-100">
                                <strong class="small text-muted text-uppercase d-block mb-1">Deskripsi Persyaratan Kontrol:</strong>
                                <p class="text-dark small mb-3" id="insp-ctrl-desc" style="line-height: 1.6;">-</p>
                                <strong class="small text-muted text-uppercase d-block mb-1">Klaster Harmonisasi:</strong>
                                <span class="badge bg-primary-lt" id="insp-ctrl-cluster">-</span>
                            </div>
                        </div>
                        <div class="col-lg-7">
                            <div class="p-3 bg-light rounded-3 border h-100">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <strong class="small text-dark text-uppercase"><i class="fas fa-link me-1 text-primary"></i> Kontrol yang Setara di Standar & Regulasi Lain:</strong>
                                    <span class="badge bg-secondary-lt" id="insp-equiv-count">0 Standar</span>
                                </div>
                                <p class="text-muted small mb-2">Menerapkan kontrol ini sekaligus membuktikan kepatuhan terhadap klausul-klausul berikut:</p>
                                <div class="row g-2" id="insp-equiv-grid"></div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        {{-- TAB 2: CROSS-FRAMEWORK MATRIX TABLE --}}
        <div class="cmap-tab-content d-none" id="tab-matrix">
            <div class="cmap-panel">
                <div class="cmap-panel-head">
                    <div>
                        <h3><i class="fas fa-table-cells me-2 text-primary"></i> Matriks Kesetaraan Kontrol Silang (Cross-Framework Matrix)</h3>
                        <p class="text-muted small mb-0">Tabel pemetaan matriks komparasi langsung antara 5 klaster utama dan 10 regulasi/standar.</p>
                    </div>
                    <span class="badge bg-green-lt">Harmonisasi 10 Standar</span>
                </div>

                <div class="table-responsive">
                    <table class="table table-bordered table-vcenter table-hover" id="cmap-table">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 20%; min-width: 200px;">Klaster Kesetaraan</th>
                                @foreach($frameworks as $fw)
                                    <th class="text-center" style="font-size: .78rem; font-weight: 700; color: {{ $fw['color'] ?? '#0284c7' }}; min-width: 140px;">
                                        {{ $fw['name'] }}
                                    </th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($groups as $grp)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center gap-1 mb-1">
                                            <span class="badge bg-primary-lt font-monospace">{{ $grp['id'] }}</span>
                                        </div>
                                        <strong style="color: #0f172a; font-size: .88rem; display: block;">{{ $grp['name'] }}</strong>
                                        <span style="display: block; font-size: .76rem; color: #64748b; line-height: 1.4;">{{ $grp['desc'] }}</span>
                                    </td>
                                    @foreach($frameworks as $fw)
                                        @php
                                            $fwCtrlList = $controlsByFramework->get($fw['id']) ?? collect();
                                            $fwControlIds = $fwCtrlList->pluck('id')->toArray();
                                            $intersect = array_values(array_intersect($grp['controls'] ?? [], $fwControlIds));
                                        @endphp
                                        <td class="text-center" style="vertical-align: middle;">
                                            @if(!empty($intersect))
                                                @foreach($intersect as $ctrlId)
                                                    @php
                                                        $ctrlObj = $controlsById->get($ctrlId);
                                                    @endphp
                                                    <div class="badge p-2 mb-1" style="background: {{ $fw['color'] ?? '#0284c7' }}; font-size: .72rem; cursor: pointer; white-space: normal; line-height: 1.3; display: block;"
                                                         onclick="focusAndInspectControl('{{ $ctrlId }}')"
                                                         title="{{ $ctrlObj['name'] ?? '' }}: {{ $ctrlObj['desc'] ?? '' }}">
                                                        <strong>{{ $ctrlObj['name'] ?? $ctrlId }}</strong>
                                                    </div>
                                                @endforeach
                                            @else
                                                <span class="text-muted" style="font-size: .75rem;">—</span>
                                            @endif
                                        </td>
                                    @endforeach
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- TAB 3: 5 EQUIVALENCY CLUSTERS DETAIL CARDS --}}
        <div class="cmap-tab-content d-none" id="tab-clusters">
            <div class="cmap-panel">
                <div class="cmap-panel-head">
                    <div>
                        <h3><i class="fas fa-layer-group me-2 text-primary"></i> 5 Klaster Kesetaraan Kontrol Utama (Equivalency Clusters)</h3>
                        <p class="text-muted small mb-0">Fondasi harmonisasi arsitektur tata kelola dan keamanan siber terpadu.</p>
                    </div>
                    <span class="badge bg-blue-lt">5 Domain Harmonisasi</span>
                </div>

                <div class="row g-3">
                    @foreach($groups as $grp)
                        <div class="col-md-6 col-lg-4">
                            <div class="cmap-cluster-card">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="badge bg-primary text-white font-monospace">{{ $grp['id'] }}</span>
                                    <span class="badge bg-secondary-lt">{{ count($grp['controls'] ?? []) }} Standar Terhubung</span>
                                </div>
                                <h4 class="text-dark fw-bold mb-2 fs-6">{{ $grp['name'] }}</h4>
                                <p class="text-muted small mb-3" style="line-height: 1.5; flex: 1;">{{ $grp['desc'] }}</p>
                                <div class="pt-2 border-top">
                                    <small class="text-muted fw-bold d-block mb-1 text-uppercase" style="font-size: .7rem;">Titik Kontrol Terhubung (Klik untuk Sorot):</small>
                                    <div class="d-flex flex-wrap">
                                        @foreach($grp['controls'] as $cId)
                                            @php
                                                $cObj = $controlsById->get($cId);
                                                $cFw = $frameworksById->get($cObj['framework'] ?? '');
                                            @endphp
                                            <span class="cmap-tag bg-light border text-dark"
                                                  style="border-left: 3px solid {{ $cFw['color'] ?? '#0284c7' }} !important;"
                                                  onclick="focusAndInspectControl('{{ $cId }}')"
                                                  title="{{ $cObj['name'] ?? '' }}">
                                                {{ $cId }}
                                            </span>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

    </div>
</div>
@endsection

@push('scripts')
{{-- Load D3.js from local vendor with fallback --}}
<script src="{{ asset('vendor/d3/d3.min.js') }}"></script>
<script>
if (typeof d3 === 'undefined') {
    document.write('<script src="https://cdn.jsdelivr.net/npm/d3@7"><\/script>');
}
</script>

<script>
document.addEventListener('DOMContentLoaded', function () {
    // 1. Data Structures
    const frameworksData = @json($frameworks);
    const controlsData = @json($controls);
    const groupsData = @json($groups);
    const linksData = @json($links);
    const frameworkRouteMap = @json($frameworkRouteMap);

    // 2. View Tab Navigation
    const tabs = document.querySelectorAll('.cmap-tab');
    const contents = document.querySelectorAll('.cmap-tab-content');

    tabs.forEach(tab => {
        tab.addEventListener('click', function () {
            tabs.forEach(t => t.classList.remove('active'));
            contents.forEach(c => c.classList.add('d-none'));

            this.classList.add('active');
            const targetId = this.getAttribute('data-tab');
            const targetEl = document.getElementById(targetId);
            if (targetEl) {
                targetEl.classList.remove('d-none');
                if (targetId === 'tab-map') {
                    if (typeof updateGraphLayout === 'function') {
                        setTimeout(updateGraphLayout, 50);
                    }
                } else {
                    if (simulation) simulation.stop();
                }
            }
        });
    });

    // 3. Framework Filter State
    let selectedFrameworks = frameworksData.map(f => f.id);
    let activeClusterFilter = 'all';
    let activeIsolatedNodeId = null;

    const filterPills = document.querySelectorAll('.cmap-fw-chip');
    filterPills.forEach(pill => {
        pill.addEventListener('click', function () {
            const fwId = this.getAttribute('data-fw');
            if (selectedFrameworks.includes(fwId)) {
                // Minimum 2 frameworks
                if (selectedFrameworks.length <= 2) {
                    alert('Minimal 2 framework harus aktif untuk memvisualisasikan cross-mapping.');
                    return;
                }
                selectedFrameworks = selectedFrameworks.filter(id => id !== fwId);
                this.classList.remove('active');
            } else {
                selectedFrameworks.push(fwId);
                this.classList.add('active');
            }
            renderGraph();
        });
    });

    document.getElementById('btn-select-all-fw')?.addEventListener('click', () => {
        selectedFrameworks = frameworksData.map(f => f.id);
        filterPills.forEach(p => p.classList.add('active'));
        renderGraph();
    });

    document.getElementById('btn-select-none-fw')?.addEventListener('click', () => {
        // Keep primary two
        selectedFrameworks = ['iso27001', 'nist'];
        filterPills.forEach(p => {
            const id = p.getAttribute('data-fw');
            if (selectedFrameworks.includes(id)) {
                p.classList.add('active');
            } else {
                p.classList.remove('active');
            }
        });
        renderGraph();
    });

    // Cluster Quick Filters
    document.querySelectorAll('.cluster-filter-btn').forEach(btn => {
        btn.addEventListener('click', function () {
            document.querySelectorAll('.cluster-filter-btn').forEach(b => b.classList.remove('active', 'btn-dark'));
            document.querySelectorAll('.cluster-filter-btn').forEach(b => b.classList.add('btn-outline-primary'));
            this.classList.remove('btn-outline-primary');
            this.classList.add('active', 'btn-dark');

            activeClusterFilter = this.getAttribute('data-cluster');
            highlightCluster(activeClusterFilter);
        });
    });

    // 4. D3.js Force Directed Graph Engine
    const svgElement = document.getElementById('cmap-graph-svg');
    const container = document.getElementById('cmap-graph-wrapper');
    const tooltip = document.getElementById('cmap-tooltip');
    const inspectorDrawer = document.getElementById('cmap-inspector-drawer');

    let simulation = null;
    let svgSelection = null;
    let gSelection = null;
    let linkSelection = null;
    let nodeSelection = null;
    let labelSelection = null;
    let zoomBehavior = null;

    function getFrameworkColor(fwId) {
        const fw = frameworksData.find(f => f.id === fwId);
        return fw ? fw.color : '#0284c7';
    }

    function getFrameworkName(fwId) {
        const fw = frameworksData.find(f => f.id === fwId);
        return fw ? fw.name : fwId;
    }

    function getFilteredGraphData() {
        const activeFwSet = new Set(selectedFrameworks);

        // 1. Framework Nodes (Hubs)
        const fwNodes = frameworksData
            .filter(fw => activeFwSet.has(fw.id))
            .map(fw => ({
                id: fw.id,
                name: fw.name,
                isFramework: true,
                color: fw.color || '#0284c7',
                radius: 34
            }));

        // 2. Control Nodes
        const controlNodes = controlsData
            .filter(ctrl => activeFwSet.has(ctrl.framework))
            .map(ctrl => {
                const group = groupsData.find(g => g.controls && g.controls.includes(ctrl.id));
                return {
                    id: ctrl.id,
                    framework: ctrl.framework,
                    name: ctrl.name,
                    desc: ctrl.desc,
                    groupId: group ? group.id : null,
                    groupName: group ? group.name : null,
                    isFramework: false,
                    color: getFrameworkColor(ctrl.framework),
                    radius: 13
                };
            });

        const nodes = [...fwNodes, ...controlNodes];
        const nodeIds = new Set(nodes.map(n => n.id));
        const links = [];

        // 3. Hierarchy links (solid lines to parent framework hub)
        controlNodes.forEach(ctrl => {
            links.push({
                source: ctrl.framework,
                target: ctrl.id,
                type: 'hierarchy',
                value: 1
            });
        });

        // 4. Equivalency links (dashed lines between equivalent controls)
        linksData.forEach(link => {
            if (nodeIds.has(link.source) && nodeIds.has(link.target)) {
                links.push({
                    source: link.source,
                    target: link.target,
                    type: 'equivalent',
                    value: 2
                });
            }
        });

        return { nodes, links };
    }

    function renderGraph() {
        if (!svgElement || !container) return;

        const width = container.clientWidth || 980;
        const height = 700;
        const cx = width / 2;
        const cy = height / 2;

        if (simulation) {
            simulation.stop();
            simulation = null;
        }

        svgSelection = d3.select(svgElement)
            .attr('viewBox', [0, 0, width, height]);

        svgSelection.selectAll('*').remove();

        gSelection = svgSelection.append('g');

        // Zoom Behavior
        zoomBehavior = d3.zoom()
            .scaleExtent([0.15, 3.5])
            .on('zoom', (event) => {
                gSelection.attr('transform', event.transform);
            });

        svgSelection.call(zoomBehavior);

        const { nodes, links } = getFilteredGraphData();

        document.getElementById('graph-stat-badge').textContent = `${nodes.filter(n => !n.isFramework).length} Kontrol Aktif (${nodes.filter(n => n.isFramework).length} Standar)`;

        // 1. RADIAL PRE-POSITIONING (Eliminates explosive force computations from (0,0))
        const fwNodes = nodes.filter(n => n.isFramework);
        const numFw = fwNodes.length || 1;
        const angleStep = (2 * Math.PI) / numFw;
        const radiusFw = Math.min(width, height) * 0.36;

        fwNodes.forEach((fw, i) => {
            fw.x = cx + Math.cos(i * angleStep) * radiusFw;
            fw.y = cy + Math.sin(i * angleStep) * radiusFw;
        });

        const ctrlNodes = nodes.filter(n => !n.isFramework);
        ctrlNodes.forEach((ctrl, i) => {
            const parent = fwNodes.find(f => f.id === ctrl.framework);
            if (parent) {
                const subAngle = (i * 1.1) % (2 * Math.PI);
                ctrl.x = parent.x + Math.cos(subAngle) * 50;
                ctrl.y = parent.y + Math.sin(subAngle) * 50;
            } else {
                ctrl.x = cx;
                ctrl.y = cy;
            }
        });

        // 2. OPTIMIZED PHYSICS: Fast cooling, bounded charge, collision damping
        simulation = d3.forceSimulation(nodes)
            .force('link', d3.forceLink(links).id(d => d.id).distance(d => d.type === 'hierarchy' ? 70 : 160))
            .force('charge', d3.forceManyBody().strength(d => d.isFramework ? -450 : -110).distanceMax(380))
            .force('center', d3.forceCenter(cx, cy))
            .force('collide', d3.forceCollide().radius(d => d.radius + 4).iterations(2))
            .alphaDecay(0.065)    // Rapid cooldown in ~25-30 frames
            .velocityDecay(0.45)  // Damps oscillation to prevent endless jitter
            .alphaMin(0.005);     // Hard stop when stable

        // 3. SYNCHRONOUS IN-MEMORY WARM-UP (35 ticks in ~8ms, zero DOM operations)
        simulation.stop();
        for (let i = 0; i < 35; ++i) {
            simulation.tick();
        }

        // Links
        linkSelection = gSelection.append('g')
            .selectAll('line')
            .data(links)
            .join('line')
            .attr('x1', d => d.source.x)
            .attr('y1', d => d.source.y)
            .attr('x2', d => d.target.x)
            .attr('y2', d => d.target.y)
            .attr('stroke-width', d => d.type === 'hierarchy' ? 1.5 : 2.2)
            .attr('stroke', d => d.type === 'hierarchy' ? '#cbd5e1' : '#0284c7')
            .attr('stroke-dasharray', d => d.type === 'equivalent' ? '5,4' : 'none')
            .attr('opacity', 0.85);

        // Nodes
        nodeSelection = gSelection.append('g')
            .selectAll('circle')
            .data(nodes)
            .join('circle')
            .attr('cx', d => d.x)
            .attr('cy', d => d.y)
            .attr('r', d => d.radius)
            .attr('fill', d => d.isFramework ? '#ffffff' : d.color)
            .attr('stroke', d => d.color)
            .attr('stroke-width', d => d.isFramework ? 4 : 2)
            .attr('cursor', 'pointer')
            .attr('filter', d => d.isFramework ? 'drop-shadow(0 4px 6px rgba(0,0,0,0.1))' : 'none')
            .call(d3.drag()
                .on('start', (event, d) => {
                    if (!event.active) simulation.alphaTarget(0.15).restart();
                    d.fx = d.x;
                    d.fy = d.y;
                })
                .on('drag', (event, d) => {
                    d.fx = event.x;
                    d.fy = event.y;
                })
                .on('end', (event, d) => {
                    if (!event.active) simulation.alphaTarget(0);
                    d.fx = null;
                    d.fy = null;
                })
            );

        // Labels
        labelSelection = gSelection.append('g')
            .selectAll('text')
            .data(nodes)
            .join('text')
            .attr('x', d => d.x)
            .attr('y', d => d.y)
            .attr('text-anchor', 'middle')
            .attr('dy', d => d.isFramework ? 4 : 22)
            .attr('font-size', d => d.isFramework ? '12px' : '10.5px')
            .attr('font-weight', d => d.isFramework ? '800' : '600')
            .attr('fill', d => d.isFramework ? d.color : '#1e293b')
            .attr('pointer-events', 'none')
            .text(d => d.isFramework ? d.name : d.id);

        // Node Tooltip & Click Handlers
        nodeSelection
            .on('mouseover', (event, d) => {
                const rect = container.getBoundingClientRect();
                const tt = tooltip;
                const ttHead = document.getElementById('cmap-tooltip-head');
                const ttTitle = document.getElementById('cmap-tt-title');
                const ttBadge = document.getElementById('cmap-tt-badge');
                const ttDesc = document.getElementById('cmap-tt-desc');
                const ttEquivWrap = document.getElementById('cmap-tt-equivalents-wrap');
                const ttEquivList = document.getElementById('cmap-tt-equivalents');

                ttHead.style.backgroundColor = d.color;
                ttTitle.textContent = d.name;

                if (d.isFramework) {
                    ttBadge.textContent = 'Standar / Regulasi Hub';
                    ttDesc.textContent = `Pusat kendali standar ${d.name}. Hubungkan titik kontrol dan temukan ekuivalensinya di standar lain.`;
                    ttEquivWrap.style.display = 'none';
                } else {
                    ttBadge.textContent = `${getFrameworkName(d.framework)} • ${d.id}`;
                    ttDesc.textContent = d.desc || 'Persyaratan kepatuhan.';

                    // Find equivalent nodes
                    const relatedLinks = links.filter(l => l.type === 'equivalent' && (l.source.id === d.id || l.target.id === d.id));
                    const relatedNodes = relatedLinks.map(l => l.source.id === d.id ? l.target : l.source);

                    if (relatedNodes.length > 0) {
                        ttEquivWrap.style.display = 'block';
                        ttEquivList.innerHTML = relatedNodes.map(rn => {
                            const col = getFrameworkColor(rn.framework);
                            return `<span class="badge" style="background: ${col}; font-size: .68rem;">${rn.id}</span>`;
                        }).join('');
                    } else {
                        ttEquivWrap.style.display = 'none';
                    }
                }

                tt.style.left = Math.min(event.clientX - rect.left + 15, rect.width - 320) + 'px';
                tt.style.top = Math.max(10, event.clientY - rect.top - 20) + 'px';
                tt.classList.add('show');
            })
            .on('mouseout', () => {
                tooltip.classList.remove('show');
            })
            .on('click', (event, d) => {
                if (d.isFramework) {
                    isolateFramework(d.id);
                } else {
                    isolateControl(d.id);
                }
            });

        // 4. REQUESTANIMATIONFRAME THROTTLED TICK HANDLER
        let isTicking = false;
        function updateSvgPositions() {
            linkSelection
                .attr('x1', d => d.source.x)
                .attr('y1', d => d.source.y)
                .attr('x2', d => d.target.x)
                .attr('y2', d => d.target.y);

            nodeSelection
                .attr('cx', d => d.x)
                .attr('cy', d => d.y);

            labelSelection
                .attr('x', d => d.x)
                .attr('y', d => d.y);

            isTicking = false;
        }

        simulation.on('tick', () => {
            if (!isTicking) {
                isTicking = true;
                requestAnimationFrame(updateSvgPositions);
            }
        });

        // When simulation ends, ensure 0% CPU consumption
        simulation.on('end', () => {
            simulation.stop();
        });

        // Click on background svg clears isolation
        svgSelection.on('click', (event) => {
            if (event.target.tagName === 'svg') {
                resetGraphIsolation();
            }
        });

        // Smoothly settle the remaining ~20 frames, then stops permanently
        simulation.restart();
    }

    // 5. Isolation & Highlighting Functions
    function isolateControl(ctrlId) {
        if (!nodeSelection) return;

        if (activeIsolatedNodeId === ctrlId) {
            resetGraphIsolation();
            return;
        }

        activeIsolatedNodeId = ctrlId;
        const ctrl = controlsData.find(c => c.id === ctrlId);
        if (!ctrl) return;

        const { nodes, links } = getFilteredGraphData();

        // Find equivalent controls
        const relatedLinks = links.filter(l => l.type === 'equivalent' && (l.source === ctrlId || l.target === ctrlId || l.source.id === ctrlId || l.target.id === ctrlId));
        const relatedNodeIds = relatedLinks.map(l => {
            const sid = l.source.id || l.source;
            const tid = l.target.id || l.target;
            return sid === ctrlId ? tid : sid;
        });

        // Visible IDs: Target control, its framework, all equivalent controls, and their frameworks
        const visibleIds = new Set([ctrl.id, ctrl.framework, ...relatedNodeIds]);
        relatedNodeIds.forEach(id => {
            const rc = controlsData.find(c => c.id === id);
            if (rc && rc.framework) visibleIds.add(rc.framework);
        });

        // Apply dimming / highlight
        nodeSelection.transition().duration(250)
            .attr('opacity', n => visibleIds.has(n.id) ? 1 : 0.08)
            .attr('r', n => n.id === ctrlId ? n.radius + 6 : n.radius);

        labelSelection.transition().duration(250)
            .attr('opacity', n => visibleIds.has(n.id) ? 1 : 0.08)
            .attr('font-weight', n => n.id === ctrlId ? '800' : (n.isFramework ? '800' : '600'));

        linkSelection.transition().duration(250)
            .attr('opacity', l => {
                const sid = l.source.id || l.source;
                const tid = l.target.id || l.target;
                if (l.type === 'hierarchy' && visibleIds.has(sid) && visibleIds.has(tid)) return 0.9;
                if (l.type === 'equivalent' && (sid === ctrlId || tid === ctrlId)) return 1;
                return 0.04;
            })
            .attr('stroke-width', l => {
                const sid = l.source.id || l.source;
                const tid = l.target.id || l.target;
                if (l.type === 'equivalent' && (sid === ctrlId || tid === ctrlId)) return 3.5;
                return l.type === 'hierarchy' ? 1.6 : 2.4;
            });

        // Open Inspector Drawer
        openInspector(ctrl, relatedNodeIds);
    }

    function isolateFramework(fwId) {
        if (!nodeSelection) return;

        if (activeIsolatedNodeId === fwId) {
            resetGraphIsolation();
            return;
        }

        activeIsolatedNodeId = fwId;
        const visibleIds = new Set([fwId]);
        controlsData.filter(c => c.framework === fwId).forEach(c => visibleIds.add(c.id));

        nodeSelection.transition().duration(250)
            .attr('opacity', n => visibleIds.has(n.id) ? 1 : 0.08);

        labelSelection.transition().duration(250)
            .attr('opacity', n => visibleIds.has(n.id) ? 1 : 0.08);

        linkSelection.transition().duration(250)
            .attr('opacity', l => {
                const sid = l.source.id || l.source;
                const tid = l.target.id || l.target;
                return (sid === fwId || tid === fwId) ? 1 : 0.04;
            });

        inspectorDrawer.style.display = 'none';
    }

    function highlightCluster(clusterId) {
        if (!nodeSelection) return;

        if (clusterId === 'all') {
            resetGraphIsolation();
            return;
        }

        const grp = groupsData.find(g => g.id === clusterId);
        if (!grp) return;

        const visibleIds = new Set(grp.controls || []);
        grp.controls.forEach(cId => {
            const ctrl = controlsData.find(c => c.id === cId);
            if (ctrl && ctrl.framework) visibleIds.add(ctrl.framework);
        });

        nodeSelection.transition().duration(250)
            .attr('opacity', n => visibleIds.has(n.id) ? 1 : 0.1);

        labelSelection.transition().duration(250)
            .attr('opacity', n => visibleIds.has(n.id) ? 1 : 0.1);

        linkSelection.transition().duration(250)
            .attr('opacity', l => {
                const sid = l.source.id || l.source;
                const tid = l.target.id || l.target;
                if (visibleIds.has(sid) && visibleIds.has(tid)) return 0.95;
                return 0.05;
            });
    }

    function resetGraphIsolation() {
        activeIsolatedNodeId = null;
        if (!nodeSelection) return;

        nodeSelection.transition().duration(250)
            .attr('opacity', 1)
            .attr('r', d => d.radius);

        labelSelection.transition().duration(250)
            .attr('opacity', 1)
            .attr('font-weight', d => d.isFramework ? '800' : '600');

        linkSelection.transition().duration(250)
            .attr('opacity', 0.85)
            .attr('stroke-width', d => d.type === 'hierarchy' ? 1.6 : 2.4);

        inspectorDrawer.style.display = 'none';
    }

    // 6. Inspector Drawer Population
    function openInspector(ctrl, equivalentIds) {
        const fw = frameworksData.find(f => f.id === ctrl.framework) || {};
        const group = groupsData.find(g => g.controls && g.controls.includes(ctrl.id));
        const equivControls = controlsData.filter(c => equivalentIds.includes(c.id));

        document.getElementById('insp-fw-badge').textContent = fw.name || ctrl.framework;
        document.getElementById('insp-fw-badge').style.backgroundColor = fw.color || '#0284c7';
        document.getElementById('insp-ctrl-name').textContent = ctrl.name;
        document.getElementById('insp-ctrl-id').textContent = `ID: ${ctrl.id} • Standar: ${fw.name || ctrl.framework}`;
        document.getElementById('insp-ctrl-desc').textContent = ctrl.desc || 'Persyaratan kepatuhan standar.';
        document.getElementById('insp-ctrl-cluster').textContent = group ? `${group.id} - ${group.name}` : 'Umum';
        document.getElementById('insp-equiv-count').textContent = `${equivControls.length} Standar Lain`;

        // Direct link to framework reader
        const fwUrl = frameworkRouteMap[ctrl.framework] || '#';
        const fwLink = document.getElementById('insp-open-framework-link');
        fwLink.href = fwUrl;
        fwLink.title = `Buka katalog ${fw.name || ctrl.framework}`;

        // Render Equivalent Cards
        const grid = document.getElementById('insp-equiv-grid');
        grid.innerHTML = '';

        if (equivControls.length === 0) {
            grid.innerHTML = '<div class="col-12 text-muted small">Tidak ada kontrol ekuivalen terpetakan di filter aktif saat ini.</div>';
        } else {
            equivControls.forEach(ec => {
                const efw = frameworksData.find(f => f.id === ec.framework) || {};
                const col = efw.color || '#64748b';
                const efwUrl = frameworkRouteMap[ec.framework] || '#';

                const cardCol = document.createElement('div');
                cardCol.className = 'col-md-6';
                cardCol.innerHTML = `
                    <div class="p-2 rounded-3 border bg-white h-100 d-flex flex-column" style="border-left: 3px solid ${col} !important;">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="badge text-white" style="background: ${col}; font-size: .68rem;">${efw.name || ec.framework}</span>
                            <span class="badge bg-light text-muted font-monospace" style="font-size: .68rem;">${ec.id}</span>
                        </div>
                        <strong class="text-dark small d-block mb-1" style="font-size: .82rem;">${ec.name}</strong>
                        <p class="text-muted small mb-2" style="font-size: .75rem; line-height: 1.4; flex: 1;">${ec.desc}</p>
                        <div class="d-flex justify-content-between align-items-center pt-1 border-top">
                            <span class="badge bg-green-lt" style="font-size: .65rem;"><i class="fas fa-check-circle me-1"></i> Ekuivalen Penuh</span>
                            <a href="${efwUrl}" class="btn btn-xs btn-outline-secondary" target="_blank" style="font-size: .7rem;">
                                Buka <i class="fas fa-arrow-right ms-1"></i>
                            </a>
                        </div>
                    </div>
                `;
                grid.appendChild(cardCol);
            });
        }

        inspectorDrawer.style.display = 'block';
        inspectorDrawer.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }

    document.getElementById('insp-close-btn')?.addEventListener('click', resetGraphIsolation);
    document.getElementById('cmap-reset-graph-btn')?.addEventListener('click', () => {
        resetGraphIsolation();
        if (svgSelection && zoomBehavior) {
            svgSelection.transition().duration(600).call(zoomBehavior.transform, d3.zoomIdentity);
        }
    });

    // 7. Zoom Toolbar Handlers
    document.getElementById('tool-zoom-in')?.addEventListener('click', () => {
        if (svgSelection && zoomBehavior) svgSelection.transition().duration(250).call(zoomBehavior.scaleBy, 1.25);
    });
    document.getElementById('tool-zoom-out')?.addEventListener('click', () => {
        if (svgSelection && zoomBehavior) svgSelection.transition().duration(250).call(zoomBehavior.scaleBy, 0.8);
    });
    document.getElementById('tool-zoom-reset')?.addEventListener('click', () => {
        if (svgSelection && zoomBehavior) svgSelection.transition().duration(500).call(zoomBehavior.transform, d3.zoomIdentity);
    });
    document.getElementById('tool-zoom-fit')?.addEventListener('click', () => {
        if (svgSelection && zoomBehavior) {
            const width = container.clientWidth || 980;
            const height = 700;
            svgSelection.transition().duration(500).call(zoomBehavior.transform, d3.zoomIdentity.translate(width*0.05, height*0.05).scale(0.9));
        }
    });

    // Global helper to switch to map tab and focus control from matrix or cluster cards
    window.focusAndInspectControl = function (ctrlId) {
        const mapTabBtn = document.querySelector('.cmap-tab[data-tab="tab-map"]');
        if (mapTabBtn) mapTabBtn.click();

        setTimeout(() => {
            isolateControl(ctrlId);
        }, 150);
    };

    window.updateGraphLayout = function () {
        if (simulation) {
            simulation.alpha(0.08).restart();
        }
    };

    // 8. Initial Render & Lifecycle Management
    renderGraph();

    let resizeTimer = null;
    window.addEventListener('resize', () => {
        if (document.getElementById('tab-map')?.classList.contains('d-none')) return;
        clearTimeout(resizeTimer);
        resizeTimer = setTimeout(() => {
            renderGraph();
        }, 200);
    });

    // Automatically stop physics computation when tab is hidden to preserve 100% CPU
    document.addEventListener('visibilitychange', () => {
        if (document.hidden && simulation) {
            simulation.stop();
        }
    });

    window.addEventListener('beforeunload', () => {
        if (simulation) simulation.stop();
    });
});
</script>
@endpush
