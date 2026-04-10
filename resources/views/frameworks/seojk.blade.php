@extends('layouts.tabler')

@section('title', 'SEOJK 29/SEOJK.03/2022 - Risk Management System')

@push('styles')
<link rel="stylesheet" href="{{ asset('vendor/datatables/css/dataTables.bootstrap5.min.css') }}">
<style>
.sej-page{--ink:#14263b;--muted:#5c6776;--line:rgba(20,38,59,.1);--shell:linear-gradient(180deg,#f6efe3 0%,#edf5f6 100%);color:var(--ink);padding:.25rem;border-radius:32px;background:var(--shell)}
.sej-shell{display:grid;gap:1rem}.sej-hero{display:grid;grid-template-columns:1.55fr .92fr;gap:1.2rem;align-items:stretch;min-height:368px;padding:1.45rem;border-radius:28px;overflow:hidden;position:relative;background:radial-gradient(circle at top right,rgba(248,214,161,.88),transparent 30%),radial-gradient(circle at bottom left,rgba(156,210,219,.7),transparent 28%),linear-gradient(135deg,#132a43 0%,#1f5f78 46%,#f2debb 100%);box-shadow:0 20px 44px rgba(15,23,42,.09)}.sej-hero>*{position:relative;z-index:1}
.sej-kicker{display:inline-flex;align-items:center;gap:.45rem;padding:.35rem .7rem;border-radius:999px;background:rgba(255,250,242,.18);color:rgba(255,250,242,.92);font-size:.75rem;font-weight:700;text-transform:uppercase;letter-spacing:.08em}.sej-title{margin:1rem 0 .7rem;color:#fffaf2;font-size:clamp(2rem,3.8vw,3rem);font-weight:800;line-height:1.04}.sej-lede{margin:0;max-width:720px;color:rgba(255,250,242,.82);line-height:1.7}
.sej-metrics{display:grid;grid-template-columns:repeat(4,1fr);gap:.65rem;margin-top:1.1rem}.sej-metric,.sej-side,.sej-panel,.sej-mini{border:1px solid var(--line);background:linear-gradient(180deg,rgba(255,251,245,.98) 0%,rgba(246,251,252,.98) 100%);box-shadow:0 14px 28px rgba(15,23,42,.05)}.sej-metric{padding:.68rem .74rem;border-radius:16px;background:rgba(255,250,242,.12);border-color:rgba(255,255,255,.18);min-height:96px;display:flex;flex-direction:column;justify-content:flex-start}.sej-metric label,.sej-side label,.sej-mini label,.sej-form label,.sej-inspector small,.sej-label{display:block;font-size:.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.08em}.sej-metric label{color:rgba(255,250,242,.7);margin-bottom:.35rem}.sej-metric strong{display:block;color:#fffaf2;font-size:1.5rem;font-weight:800;line-height:1}.sej-metric span{display:block;margin-top:.34rem;color:rgba(255,250,242,.72);font-size:.76rem}
.sej-side-stack{display:grid;gap:.85rem}.sej-side{padding:.8rem .86rem;border-radius:18px;background:rgba(255,250,242,.78);border-color:rgba(255,255,255,.24);min-height:142px}.sej-side label{color:var(--muted);margin-bottom:.4rem}.sej-side h3{margin:0;font-size:1rem;font-weight:800}.sej-side p{margin:.55rem 0 0;color:var(--muted);line-height:1.55;font-size:.84rem}
.sej-nav{display:grid;grid-template-columns:repeat(3,1fr);gap:.75rem}.sej-tab{display:grid;grid-template-columns:auto 1fr;gap:.72rem;align-items:center;padding:.82rem .88rem;border-radius:18px;border:1px solid var(--line);background:linear-gradient(180deg,rgba(255,250,242,.94) 0%,rgba(239,245,246,.94) 100%);text-align:left;color:var(--ink);box-shadow:0 12px 24px rgba(15,23,42,.04);cursor:pointer}.sej-tab.active{border-color:rgba(20,78,114,.24);box-shadow:0 18px 30px rgba(20,78,114,.1)}.sej-tab i{width:2.35rem;height:2.35rem;display:inline-flex;align-items:center;justify-content:center;border-radius:14px;background:rgba(20,38,59,.06)}.sej-tab strong{display:block;font-size:.9rem;font-weight:800}.sej-tab span{display:block;margin-top:.14rem;color:var(--muted);font-size:.76rem;line-height:1.4}
.sej-grid{display:grid;gap:1rem}.sej-grid.two{grid-template-columns:1.06fr .94fr}.sej-panel{padding:1rem;border-radius:20px}.sej-head{display:flex;align-items:center;justify-content:space-between;gap:.85rem;margin-bottom:.7rem}.sej-head h3{margin:0;font-size:.98rem;font-weight:800}.sej-chip,.sej-pill,.sej-meta span,.sej-ref{display:inline-flex;align-items:center;gap:.3rem;padding:.24rem .52rem;border-radius:999px;font-size:.7rem;font-weight:700;line-height:1.2}.sej-chip{background:rgba(20,38,59,.08);color:var(--ink)}.sej-copy{margin:0 0 .85rem;color:var(--muted);line-height:1.6;font-size:.84rem}
.sej-bars,.sej-cards,.sej-hotspots,.sej-families{display:grid;gap:.65rem}.sej-bar,.sej-hotspot,.sej-family{display:grid;grid-template-columns:minmax(0,1.25fr) minmax(180px,1fr) auto;gap:.68rem;align-items:center;padding:.62rem .72rem;border-radius:16px;border:1px solid transparent;background:rgba(255,255,255,.55);text-align:left;cursor:pointer;transition:border-color .15s ease,transform .15s ease,box-shadow .15s ease}.sej-family{grid-template-columns:1fr auto}.sej-bar:hover,.sej-hotspot:hover,.sej-family:hover,.sej-bar.active,.sej-hotspot.active,.sej-family.active{transform:translateY(-1px);border-color:rgba(20,78,114,.22);box-shadow:0 12px 22px rgba(20,78,114,.08)}.sej-bar strong,.sej-hotspot strong,.sej-family strong{display:block;font-size:.86rem}.sej-bar em,.sej-hotspot em,.sej-family em{display:block;margin-top:.12rem;color:var(--muted);font-size:.74rem;font-style:normal}.sej-track{height:.44rem;border-radius:999px;background:rgba(20,38,59,.08);overflow:hidden}.sej-track b{display:block;height:100%;border-radius:inherit}.sej-num{min-width:2.2rem;text-align:right;font-weight:800}
.sej-cards{grid-template-columns:repeat(3,1fr)}.sej-card{width:100%;padding:.76rem .8rem;border-radius:16px;border:1px solid var(--line);background:rgba(255,255,255,.75);text-align:left;cursor:pointer}.sej-card.active{border-color:rgba(20,78,114,.24);box-shadow:0 16px 28px rgba(20,78,114,.08);background:rgba(238,245,245,.72)}.sej-card-top{display:flex;align-items:center;justify-content:space-between;gap:.55rem;color:var(--muted);font-size:.64rem;text-transform:uppercase;letter-spacing:.06em;font-weight:700}.sej-icon{width:1.9rem;height:1.9rem;display:inline-flex;align-items:center;justify-content:center;border-radius:12px;background:rgba(20,78,114,.12);color:var(--accent);font-size:.8rem}.sej-card strong{display:block;margin-top:.42rem;font-size:.86rem;font-weight:800}.sej-card p{margin:.22rem 0 0;color:var(--muted);font-size:.72rem;line-height:1.4}
.sej-mini-row{display:grid;grid-template-columns:repeat(3,1fr);gap:.65rem;margin-bottom:.8rem}.sej-mini{padding:.72rem .78rem;border-radius:16px}.sej-mini label{color:var(--muted)}.sej-mini strong{display:block;margin-top:.2rem;font-size:1.08rem;font-weight:800;color:#144e72}.sej-mini span{display:block;margin-top:.14rem;color:var(--muted);font-size:.72rem;line-height:1.4}
.sej-workspace{display:grid;grid-template-columns:.76fr 1.03fr .91fr;gap:1rem}.sej-refspace{display:grid;grid-template-columns:.82fr 1.18fr;gap:1rem}.sej-pillar-grid{display:grid;grid-template-columns:repeat(2,1fr);gap:.55rem;margin-bottom:.8rem}.sej-pillar{width:100%;padding:.62rem .68rem;border-radius:16px;border:1px solid var(--line);background:rgba(255,255,255,.75);text-align:left;cursor:pointer}.sej-pillar.active{border-color:rgba(20,78,114,.24);background:rgba(238,245,245,.72);box-shadow:0 12px 24px rgba(20,78,114,.08)}.sej-pillar strong{display:block;font-size:.82rem;font-weight:800;color:var(--accent)}.sej-pillar span{display:block;margin-top:.12rem;color:var(--muted);font-size:.74rem}
.sej-form{display:grid;gap:.75rem}.sej-form label{margin-bottom:.3rem;color:var(--muted)}.sej-summary{margin-top:.85rem;padding:.82rem .88rem;border-radius:18px;background:linear-gradient(180deg,rgba(25,61,87,.95) 0%,rgba(20,78,114,.95) 100%);color:#fffaf2}.sej-summary small{display:block;color:rgba(255,250,242,.7);font-size:.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.08em}.sej-summary strong{display:block;margin-top:.28rem;font-size:1.72rem;font-weight:800;line-height:1}.sej-summary span{display:block;margin-top:.42rem;color:rgba(255,250,242,.78);font-size:.78rem;line-height:1.5}
.sej-list{display:grid;gap:.55rem;max-height:720px;overflow-y:auto;padding-right:.12rem}.sej-item{position:relative;width:100%;padding:.7rem .78rem .66rem .88rem;border-radius:14px;border:1px solid rgba(20,38,59,.08);background:#fff;text-align:left;cursor:pointer}.sej-item.active{border-color:rgba(20,78,114,.22);box-shadow:0 10px 20px rgba(20,78,114,.05);background:rgba(255,255,255,.96)}.sej-item::before{content:'';position:absolute;left:0;top:.68rem;bottom:.68rem;width:.18rem;border-radius:999px;background:var(--accent,#144e72)}.sej-item-top{display:flex;flex-wrap:wrap;gap:.5rem;align-items:center;justify-content:space-between}.sej-item-code,.sej-code{font-family:ui-monospace,SFMono-Regular,Menlo,Monaco,Consolas,"Courier New",monospace;font-size:.76rem;font-weight:800;color:var(--accent,#144e72)}.sej-item-name{margin:.28rem 0 .2rem;font-size:.88rem;font-weight:700;line-height:1.38;color:var(--ink)}.sej-item-meta{color:var(--muted);font-size:.74rem;line-height:1.4}.sej-item-meta span+span::before{content:'\2022';margin:0 .4rem;color:rgba(20,38,59,.35)}
.sej-table{margin:0!important;width:100%!important;border-collapse:separate!important;border-spacing:0 .48rem!important}.sej-table thead th{padding:0 .65rem .16rem!important;border:0!important;color:var(--muted);font-size:.7rem;font-weight:700;text-transform:uppercase;letter-spacing:.08em}.sej-table tbody td{padding:.66rem .65rem!important;border-top:1px solid rgba(20,38,59,.08)!important;border-bottom:1px solid rgba(20,38,59,.08)!important;background:rgba(255,255,255,.93);vertical-align:top}.sej-table tbody td:first-child{border-left:1px solid rgba(20,38,59,.08)!important;border-radius:14px 0 0 14px}.sej-table tbody td:last-child{border-right:1px solid rgba(20,38,59,.08)!important;border-radius:0 14px 14px 0}.sej-table tbody tr{cursor:pointer}.sej-table tbody tr.is-active td{border-color:rgba(20,78,114,.22)!important;background:rgba(240,247,248,.94)}.sej-title-sm{font-size:.86rem;font-weight:700;line-height:1.34;color:var(--ink)}.sej-copy-sm{margin-top:.14rem;color:var(--muted);font-size:.72rem;line-height:1.44}
.sej-pill{padding:.2rem .45rem;font-size:.68rem;background:rgba(20,38,59,.08);color:var(--ink)}.sej-inspector{position:sticky;top:1rem;min-height:720px;display:flex;flex-direction:column}.sej-inspector-head{padding-bottom:.85rem;border-bottom:1px solid var(--line)}.sej-inspector-head strong{display:block;margin-top:.35rem;font-size:1rem;font-weight:800;color:#144e72}.sej-inspector-head span{display:block;margin-top:.28rem;font-size:.9rem;font-weight:800;line-height:1.4}.sej-inspector-body{display:grid;gap:.75rem;padding-top:.85rem;flex:1;min-height:0;overflow:auto;align-content:start}.sej-meta{display:flex;flex-wrap:wrap;gap:.45rem}.sej-meta span{background:rgba(20,38,59,.06);color:var(--ink);font-size:.72rem}.sej-callout,.sej-note{padding:.76rem .84rem;border-radius:16px;border:1px solid var(--line);line-height:1.62}.sej-callout{background:rgba(255,255,255,.75)}.sej-note{background:rgba(238,245,245,.84)}.sej-plain{margin:.15rem 0 0;padding-left:1rem;color:var(--muted);font-size:.78rem;line-height:1.6}.sej-plain li{margin-bottom:.16rem}.sej-refs{display:flex;flex-wrap:wrap;gap:.4rem}.sej-ref{border:1px solid rgba(20,38,59,.12);background:rgba(255,255,255,.82);color:var(--ink);font-size:.7rem;cursor:pointer}.sej-empty{padding:.9rem;border-radius:16px;border:1px dashed rgba(20,38,59,.18);background:rgba(255,255,255,.6);color:var(--muted);text-align:center;line-height:1.55}
.dataTables_wrapper .dataTables_paginate .paginate_button{padding:.18rem .55rem!important;border-radius:999px;border:0!important;background:transparent!important}.dataTables_wrapper .dataTables_paginate .paginate_button.current,.dataTables_wrapper .dataTables_paginate .paginate_button.current:hover{color:#fff!important;background:#144e72!important}.dataTables_wrapper .dataTables_paginate .paginate_button:hover{color:#144e72!important;background:rgba(20,78,114,.08)!important}.dataTables_wrapper .dataTables_info{color:var(--muted)!important;font-size:.8rem}
@media (max-width:1399.98px){.sej-workspace{grid-template-columns:1fr}.sej-inspector{position:static;min-height:auto}}@media (max-width:1199.98px){.sej-hero,.sej-metric,.sej-side{min-height:auto}.sej-hero,.sej-nav,.sej-grid.two,.sej-refspace,.sej-metrics,.sej-mini-row,.sej-cards{grid-template-columns:1fr}.sej-bar,.sej-hotspot,.sej-family{grid-template-columns:1fr}}@media (max-width:767.98px){.sej-hero,.sej-panel{padding:1.2rem;border-radius:22px}.sej-pillar-grid{grid-template-columns:1fr}}
</style>
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
        'Assessment' => ['label' => 'Penilaian', 'color' => '#1d4ed8', 'icon' => 'fa-chart-line', 'summary' => 'Siklus penilaian, scoring, dan justifikasi hasil.'],
        'Governance' => ['label' => 'Governance', 'color' => '#0f766e', 'icon' => 'fa-sitemap', 'summary' => 'Arah kebijakan, akuntabilitas pengurus, dan kerangka risiko.'],
        'Resilience' => ['label' => 'Ketahanan', 'color' => '#a16207', 'icon' => 'fa-shield-alt', 'summary' => 'Identifikasi, pelindungan, deteksi, respons, dan pemulihan.'],
        'Testing' => ['label' => 'Pengujian', 'color' => '#c2410c', 'icon' => 'fa-vials', 'summary' => 'Vulnerability assessment dan scenario exercise.'],
        'Organization' => ['label' => 'Fungsi', 'color' => '#7c3aed', 'icon' => 'fa-users', 'summary' => 'Unit independen dan koordinasi tim tanggap insiden.'],
        'Reporting' => ['label' => 'Pelaporan', 'color' => '#b91c1c', 'icon' => 'fa-file-alt', 'summary' => 'Konsolidasi tingkat risiko dan pelaporan resmi ke OJK.'],
    ];

    $chapterMeta = [
        'II' => ['label' => 'Risiko Inheren', 'color' => '#2563eb', 'icon' => 'fa-chart-line', 'summary' => 'Menilai paparan awal berdasarkan empat faktor risiko.'],
        'III' => ['label' => 'Manajemen Risiko', 'color' => '#0f766e', 'icon' => 'fa-sitemap', 'summary' => 'Pengawasan aktif, kerangka, proses, dan pengendalian.'],
        'IV' => ['label' => 'Ketahanan Siber', 'color' => '#a16207', 'icon' => 'fa-shield-alt', 'summary' => 'Identifikasi, pelindungan, deteksi, penanggulangan, dan pemulihan.'],
        'V' => ['label' => 'Maturitas', 'color' => '#7c3aed', 'icon' => 'fa-th-large', 'summary' => 'Mengukur kualitas penerapan dan level kematangan.'],
        'VI' => ['label' => 'Tingkat Risiko', 'color' => '#b91c1c', 'icon' => 'fa-balance-scale', 'summary' => 'Menggabungkan inherent risk dan maturity menjadi risk rating final.'],
        'VII' => ['label' => 'Pengujian', 'color' => '#c2410c', 'icon' => 'fa-vials', 'summary' => 'Analisis kerentanan, penetration test, dan exercise berbasis skenario.'],
        'VIII' => ['label' => 'Fungsi Siber', 'color' => '#1d4ed8', 'icon' => 'fa-users', 'summary' => 'Unit independen dan kesiapan tim tanggap insiden siber.'],
    ];

    $appendixTypePalette = [
        'Parameter Minimum' => '#2563eb',
        'Format Penilaian' => '#0f766e',
        'Definisi Peringkat' => '#a16207',
        'Definisi Tingkat' => '#7c3aed',
        'Kontrol Minimum' => '#b91c1c',
        'Format Laporan' => '#144e72',
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

    $appendixTypeBreakdown = $appendixCollection->groupBy('type')->map(function ($items, $type) use ($appendixTypePalette) {
        return [
            'type' => $type,
            'count' => $items->count(),
            'color' => $appendixTypePalette[$type] ?? '#144e72',
        ];
    })->sortByDesc('count')->values();

    $maxPillarCount = max($pillarBreakdown->max('count') ?: 0, 1);
    $maxChapterCount = max($chapterBreakdown->max('count') ?: 0, 1);
    $chapterOptions = $chapterBreakdown->pluck('key')->values();
    $firstRequirement = $reqCollection->first();
    $firstPillar = $firstRequirement ? ($pillarMeta[$firstRequirement['pillar']] ?? null) : null;
@endphp

<div class="sej-page">
    <div class="sej-shell">
        <section class="sej-hero">
            <div>
                <span class="sej-kicker"><i class="fas fa-landmark"></i>Framework Studio</span>
                <h1 class="sej-title">SEOJK 29/SEOJK.03/2022 Cyber Resilience Map</h1>
                <p class="sej-lede">
                    Halaman ini menyajikan rangkuman kewajiban inti SEOJK 29/SEOJK.03/2022 mengenai ketahanan dan
                    keamanan siber bagi bank umum, termasuk peta kewajiban, analisa implementasi, lampiran resmi,
                    dan format pelaporan yang relevan untuk kebutuhan penilaian dan pelaporan.
                </p>

                <div class="sej-metrics">
                    <div class="sej-metric">
                        <label>Bab Operasional</label>
                        <strong>{{ $totalChapters }}</strong>
                        <span>Mulai dari risiko inheren sampai unit/fungsi siber.</span>
                    </div>
                    <div class="sej-metric">
                        <label>Kewajiban Inti</label>
                        <strong>{{ $totalRequirements }}</strong>
                        <span>Requirement yang dapat ditinjau satu per satu dalam explorer.</span>
                    </div>
                    <div class="sej-metric">
                        <label>Lampiran</label>
                        <strong>{{ $totalAppendices }}</strong>
                        <span>Parameter, format penilaian, definisi rating, dan format laporan.</span>
                    </div>
                    <div class="sej-metric">
                        <label>SLA Pelaporan</label>
                        <strong>{{ $reportingSla }}</strong>
                        <span>Hari kerja setelah akhir tahun pelaporan untuk penyampaian ke OJK.</span>
                    </div>
                </div>
            </div>

            <div class="sej-side-stack">
                <div class="sej-side">
                    <label>Alur Analisa</label>
                    <h3>Pendekatan analisa yang terstruktur dan konsisten</h3>
                    <p>
                        Analisa dapat diawali dari peta pilar dan bab untuk memahami cakupan regulasi, kemudian
                        dilanjutkan ke explorer requirement untuk meninjau kewajiban secara rinci, serta ditutup
                        dengan board lampiran untuk melihat format penilaian, artefak pelaporan, dan rujukan
                        dokumentasi yang relevan.
                    </p>
                </div>
                <div class="sej-side">
                    <label>Catatan Regulasi</label>
                    <p>
                        SEOJK ini menekankan penilaian tahunan pada posisi akhir Desember, pelaporan paling lama
                        15 hari kerja setelah akhir tahun pelaporan, dan penggunaan lampiran resmi untuk scoring serta
                        dokumentasi self-assessment.
                    </p>
                </div>
            </div>
        </section>

        <div class="sej-nav nav" role="tablist">
            <button class="sej-tab active" data-bs-toggle="tab" data-bs-target="#seojk-overview-pane" type="button" role="tab">
                <i class="fas fa-chart-line"></i>
                <span>
                    <strong>Ringkasan Program</strong>
                    <span>Peta pilar, bab regulasi, dan elemen penilaian inti.</span>
                </span>
            </button>
            <button class="sej-tab" data-bs-toggle="tab" data-bs-target="#seojk-explorer-pane" type="button" role="tab">
                <i class="fas fa-sliders-h"></i>
                <span>
                    <strong>Explorer Kewajiban</strong>
                    <span>Filter requirement, baca interpretasi, dan telusuri lampiran terkait.</span>
                </span>
            </button>
            <button class="sej-tab" data-bs-toggle="tab" data-bs-target="#seojk-reference-pane" type="button" role="tab">
                <i class="fas fa-folder-open"></i>
                <span>
                    <strong>Lampiran & Laporan</strong>
                    <span>Board referensi lampiran resmi dan format pelaporan ke OJK.</span>
                </span>
            </button>
        </div>

        <div class="tab-content sej-grid">
            <div class="tab-pane fade show active" id="seojk-overview-pane" role="tabpanel">
                <div class="sej-grid two">
                    <article class="sej-panel">
                        <div class="sej-head">
                            <h3>Lanskap pilar regulasi</h3>
                            <span class="sej-chip">{{ $pillarBreakdown->count() }} pilar</span>
                        </div>
                        <p class="sej-copy">
                            Pilar ini dipakai sebagai lensa kerja utama untuk membaca SEOJK: assessment, governance,
                            resilience, testing, organization, dan reporting. Klik salah satu bar untuk langsung lompat
                            ke explorer dengan filter yang relevan.
                        </p>
                        <div class="sej-bars">
                            @foreach ($pillarBreakdown as $item)
                                <button type="button" class="sej-bar" data-explorer-pillar="{{ $item['key'] }}">
                                    <span>
                                        <strong>{{ $item['label'] }}</strong>
                                        <em>{{ $item['summary'] }}</em>
                                    </span>
                                    <span class="sej-track"><b style="width: {{ round(($item['count'] / $maxPillarCount) * 100, 1) }}%; background: {{ $item['color'] }}"></b></span>
                                    <span class="sej-num">{{ $item['count'] }}</span>
                                </button>
                            @endforeach
                        </div>
                    </article>

                    <article class="sej-panel">
                        <div class="sej-head">
                            <h3>Hotspot per bab</h3>
                            <span class="sej-chip">{{ $totalChapters }} bab aktif</span>
                        </div>
                        <p class="sej-copy">
                            Distribusi bab membantu melihat di mana regulasi paling banyak memberi arahan operasional:
                            penilaian, proses ketahanan, pengujian, atau struktur organisasi siber.
                        </p>
                        <div class="sej-hotspots">
                            @foreach ($chapterBreakdown as $item)
                                <button type="button" class="sej-hotspot" data-explorer-chapter="{{ $item['key'] }}">
                                    <span>
                                        <strong>{{ $item['key'] }}. {{ $item['label'] }}</strong>
                                        <em>{{ $item['summary'] }}</em>
                                    </span>
                                    <span class="sej-track"><b style="width: {{ round(($item['count'] / $maxChapterCount) * 100, 1) }}%; background: {{ $item['color'] }}"></b></span>
                                    <span class="sej-num">{{ $item['count'] }}</span>
                                </button>
                            @endforeach
                        </div>
                    </article>
                </div>

                <div class="sej-grid two">
                    <article class="sej-panel">
                        <div class="sej-head">
                            <h3>Elemen penilaian inti</h3>
                            <span class="sej-chip">Assessment model</span>
                        </div>
                        <div class="sej-mini-row">
                            <div class="sej-mini">
                                <label>4 Faktor Risiko Inheren</label>
                                <strong>Teknologi</strong>
                                <span>Produk bank, karakteristik organisasi, dan rekam jejak insiden siber.</span>
                            </div>
                            <div class="sej-mini">
                                <label>4 Proses Ketahanan</label>
                                <strong>Identify to Recover</strong>
                                <span>Identifikasi, pelindungan, deteksi, penanggulangan, dan pemulihan.</span>
                            </div>
                            <div class="sej-mini">
                                <label>2 Metode Uji</label>
                                <strong>Technical + Scenario</strong>
                                <span>Analisis kerentanan serta exercise berbasis skenario lintas fungsi.</span>
                            </div>
                        </div>
                        <div class="sej-note">
                            Penilaian maturitas memakai dua dimensi besar: kualitas penerapan manajemen risiko siber dan
                            kualitas penerapan proses ketahanan siber. Hasil akhirnya dipakai bersama risiko inheren
                            untuk menetapkan tingkat risiko keamanan siber.
                        </div>
                    </article>

                    <article class="sej-panel">
                        <div class="sej-head">
                            <h3>Peta lampiran</h3>
                            <span class="sej-chip">{{ $totalAppendices }} referensi</span>
                        </div>
                        <p class="sej-copy">
                            Lampiran di SEOJK ini bukan sekadar pelengkap. Ia menjadi sumber format resmi, kontrol
                            minimum, dan definisi rating yang dipakai saat Bank menyusun self-assessment dan laporan.
                        </p>
                        <div class="sej-cards">
                            @foreach ($appendixTypeBreakdown as $item)
                                <button type="button" class="sej-card" data-type-card="{{ $item['type'] }}" style="--accent: {{ $item['color'] }}">
                                    <div class="sej-card-top">
                                        <span class="sej-icon"><i class="fas fa-layer-group"></i></span>
                                        <span>{{ $item['count'] }} lampiran</span>
                                    </div>
                                    <strong>{{ $item['type'] }}</strong>
                                    <p>Lompat ke board lampiran dengan filter jenis ini.</p>
                                </button>
                            @endforeach
                        </div>
                    </article>
                </div>

            </div>

            <div class="tab-pane fade" id="seojk-explorer-pane" role="tabpanel">
                <div class="sej-workspace">
                    <article class="sej-panel">
                        <div class="sej-head">
                            <h3>Filter Workspace</h3>
                            <span class="sej-chip">{{ $totalRequirements }} requirement</span>
                        </div>

                        <div class="sej-pillar-grid">
                            @foreach ($pillarBreakdown as $item)
                                <button type="button" class="sej-pillar" data-pillar-card="{{ $item['key'] }}" style="--accent: {{ $item['color'] }}">
                                    <strong>{{ $item['label'] }}</strong>
                                    <span>{{ $item['count'] }} requirement</span>
                                </button>
                            @endforeach
                        </div>

                        <div class="sej-form">
                            <div>
                                <label for="chapterFilter">Bab</label>
                                <select id="chapterFilter" class="form-select">
                                    <option value="">Semua bab</option>
                                    @foreach ($chapterOptions as $chapter)
                                        <option value="{{ $chapter }}">{{ $chapter }}. {{ $chapterMeta[$chapter]['label'] }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label for="requirementSearch">Cari requirement</label>
                                <input id="requirementSearch" type="search" class="form-control" placeholder="Cari ID, judul, ringkasan, atau lampiran">
                            </div>
                            <button type="button" class="btn btn-outline-secondary" id="resetRequirementFilters">Atur ulang filter</button>
                        </div>

                        <div class="sej-summary">
                            <small>Requirement Ditampilkan</small>
                            <strong id="requirementVisibleCount">{{ $totalRequirements }}</strong>
                            <span id="requirementVisibleSummary">Menampilkan seluruh requirement SEOJK yang dipetakan pada halaman ini.</span>
                        </div>
                    </article>

                    <article class="sej-panel">
                        <div class="sej-head">
                            <h3>Daftar requirement</h3>
                            <span class="sej-chip" id="requirementCountChip">{{ $totalRequirements }} entri</span>
                        </div>
                        <div id="requirementList" class="sej-list"></div>
                    </article>

                    <article class="sej-panel sej-inspector">
                        <div class="sej-inspector-head">
                            <small>Requirement Inspector</small>
                            <strong id="req-id">{{ $firstRequirement['id'] ?? '-' }}</strong>
                            <span id="req-title">{{ $firstRequirement['title'] ?? 'Pilih requirement untuk membaca detail.' }}</span>
                        </div>

                        <div class="sej-inspector-body">
                            <div class="sej-meta">
                                <span id="req-pillar">{{ $firstPillar['label'] ?? '-' }}</span>
                                <span id="req-chapter">{{ isset($firstRequirement['chapter']) ? $firstRequirement['chapter'] . '. ' . ($chapterMeta[$firstRequirement['chapter']]['label'] ?? '-') : '-' }}</span>
                                <span id="req-cadence">{{ $firstRequirement['cadence'] ?? '-' }}</span>
                                <span id="req-appendix-count">{{ count($firstRequirement['appendices'] ?? []) }} lampiran</span>
                            </div>

                            <div class="sej-callout">
                                <span class="sej-label">Ringkasan Requirement</span>
                                <div id="req-summary" class="mt-2">{{ $firstRequirement['summary'] ?? 'Pilih requirement untuk membaca ringkasan.' }}</div>
                            </div>

                            <div class="sej-note">
                                <span class="sej-label">Interpretasi Implementasi</span>
                                <div id="req-interpretation" class="mt-2">{{ $firstRequirement['interpretation'] ?? '-' }}</div>
                            </div>

                            <div class="sej-callout">
                                <span class="sej-label">Fokus Implementasi</span>
                                <ul id="req-focus" class="sej-plain">
                                    @forelse (($firstRequirement['focus'] ?? []) as $item)
                                        <li>{{ $item }}</li>
                                    @empty
                                        <li>Tidak ada fokus implementasi tambahan.</li>
                                    @endforelse
                                </ul>
                            </div>

                            <div class="sej-callout">
                                <span class="sej-label">Contoh Evidence</span>
                                <ul id="req-evidence" class="sej-plain">
                                    @forelse (($firstRequirement['evidence'] ?? []) as $item)
                                        <li>{{ $item }}</li>
                                    @empty
                                        <li>Tidak ada evidence cue.</li>
                                    @endforelse
                                </ul>
                            </div>

                            <div class="sej-callout">
                                <span class="sej-label">Lampiran Terkait</span>
                                <div id="req-appendices" class="sej-refs">
                                    @forelse (($firstRequirement['appendices'] ?? []) as $appendix)
                                        <button type="button" class="sej-ref" data-appendix-id="{{ $appendix }}">{{ $appendix }}</button>
                                    @empty
                                        <span class="sej-empty w-100">Requirement ini tidak menunjuk lampiran spesifik.</span>
                                    @endforelse
                                </div>
                            </div>

                            <div class="sej-note">
                                <span class="sej-label">Pelaporan / Output</span>
                                <div id="req-reporting" class="mt-2">{{ $firstRequirement['reporting'] ?? '-' }}</div>
                            </div>
                        </div>
                    </article>
                </div>
            </div>

            <div class="tab-pane fade" id="seojk-reference-pane" role="tabpanel">
                <div class="sej-refspace">
                    <article class="sej-panel">
                        <div class="sej-head">
                            <h3>Filter lampiran</h3>
                            <span class="sej-chip">{{ $totalAppendices }} lampiran</span>
                        </div>

                        <div class="sej-form">
                            <div>
                                <label for="appendixTypeFilter">Jenis Lampiran</label>
                                <select id="appendixTypeFilter" class="form-select">
                                    <option value="">Semua jenis</option>
                                    @foreach ($appendixTypeBreakdown as $item)
                                        <option value="{{ $item['type'] }}">{{ $item['type'] }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label for="appendixSearch">Cari lampiran</label>
                                <input id="appendixSearch" type="search" class="form-control" placeholder="Cari ID, judul, jenis, atau scope">
                            </div>
                            <button type="button" class="btn btn-outline-secondary" id="resetAppendixFilters">Atur ulang filter</button>
                        </div>

                        <div class="sej-summary">
                            <small>Lampiran Ditampilkan</small>
                            <strong id="appendixVisibleCount">{{ $totalAppendices }}</strong>
                            <span id="appendixVisibleSummary">Menampilkan seluruh lampiran SEOJK yang dipetakan pada halaman ini.</span>
                        </div>

                        <div class="sej-families mt-3">
                            @foreach ($appendixTypeBreakdown as $item)
                                <button type="button" class="sej-family" data-type-card="{{ $item['type'] }}" style="--accent: {{ $item['color'] }}">
                                    <span>
                                        <strong>{{ $item['type'] }}</strong>
                                        <em>{{ $item['count'] }} lampiran</em>
                                    </span>
                                    <span class="sej-num">{{ $item['count'] }}</span>
                                </button>
                            @endforeach
                        </div>

                        <div class="sej-note mt-3">
                            <span class="sej-label">Keluaran Pelaporan</span>
                            Hasil risiko inheren, penilaian maturitas, dan tingkat risiko keamanan siber seluruhnya
                            bermuara ke laporan kondisi terkini penyelenggaraan TI Bank menggunakan Lampiran V.
                        </div>
                    </article>

                    <article class="sej-panel">
                        <div class="sej-head">
                            <h3>Board lampiran</h3>
                            <span class="sej-chip" id="appendixCountChip">{{ $totalAppendices }} entri</span>
                        </div>
                        <table id="appendixTable" class="table sej-table align-middle">
                            <thead>
                                <tr>
                                    <th>Lampiran</th>
                                    <th>Jenis</th>
                                    <th>Cakupan</th>
                                    <th>Dipakai Pada</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($appendices as $appendix)
                                    <tr data-appendix-id="{{ $appendix['id'] }}">
                                        <td><span class="sej-code">{{ $appendix['id'] }}</span></td>
                                        <td>{{ $appendix['type'] }}</td>
                                        <td>
                                            <div class="sej-title-sm">{{ $appendix['title'] }}</div>
                                            <div class="sej-copy-sm">{{ $appendix['scope'] }}</div>
                                        </td>
                                        <td>{{ count($appendix['used_by'] ?? []) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </article>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="appendixModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <div>
                    <div class="text-muted text-uppercase fw-semibold small" id="appmod-id">-</div>
                    <h5 class="modal-title mt-1" id="appmod-title">Detail Lampiran</h5>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <div class="text-muted text-uppercase fw-semibold small mb-2">Ringkasan</div>
                    <div id="appmod-summary">-</div>
                </div>
                <div class="mb-3">
                    <div class="text-muted text-uppercase fw-semibold small mb-2">Artefak / Isi Utama</div>
                    <ul id="appmod-contains" class="sej-plain"></ul>
                </div>
                <div>
                    <div class="text-muted text-uppercase fw-semibold small mb-2">Requirement yang Menggunakannya</div>
                    <div id="appmod-links" class="d-flex flex-wrap gap-2"></div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('vendor/datatables/js/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('vendor/datatables/js/dataTables.bootstrap5.min.js') }}"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const requirements = @json($requirements);
    const appendices = @json($appendices);
    const pillarMeta = @json($pillarMeta);
    const chapterMeta = @json($chapterMeta);
    const requirementMap = Object.fromEntries(requirements.map(item => [item.id, item]));
    const appendixMap = Object.fromEntries(appendices.map(item => [item.id, item]));
    const firstRequirementId = @json($firstRequirement['id'] ?? null);
    const explorerTab = document.querySelector('.sej-tab[data-bs-target="#seojk-explorer-pane"]');
    const referenceTab = document.querySelector('.sej-tab[data-bs-target="#seojk-reference-pane"]');
    const chapterFilter = document.getElementById('chapterFilter');
    const requirementSearch = document.getElementById('requirementSearch');
    const appendixTypeFilter = document.getElementById('appendixTypeFilter');
    const appendixSearch = document.getElementById('appendixSearch');
    const requirementList = document.getElementById('requirementList');
    const appendixModal = new bootstrap.Modal(document.getElementById('appendixModal'));

    let activeRequirementId = firstRequirementId;
    let activePillar = '';

    const escapeRegex = value => value.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
    const escapeHtml = value => String(value ?? '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
    const showTab = trigger => trigger && bootstrap.Tab.getOrCreateInstance(trigger).show();

    const appendixTable = $('#appendixTable').DataTable({
        pageLength: 10,
        lengthChange: false,
        autoWidth: false,
        dom: 'rtip',
        order: [[0, 'asc']],
        language: {
            paginate: { previous: 'Sebelumnya', next: 'Berikutnya' },
            zeroRecords: 'Tidak ada lampiran yang sesuai.',
            info: 'Menampilkan _START_ sampai _END_ dari _TOTAL_ entri',
            infoEmpty: 'Tidak ada entri untuk ditampilkan',
            infoFiltered: '',
        },
    });

    function syncTabState(activeBtn) {
        document.querySelectorAll('.sej-tab').forEach(tab => tab.classList.remove('active'));
        if (activeBtn) activeBtn.classList.add('active');
    }

    function syncPillarState() {
        document.querySelectorAll('[data-pillar-card]').forEach(button => {
            button.classList.toggle('active', button.dataset.pillarCard === activePillar && activePillar !== '');
        });
        document.querySelectorAll('[data-explorer-pillar]').forEach(button => {
            button.classList.toggle('active', button.dataset.explorerPillar === activePillar && activePillar !== '');
        });
    }

    function syncChapterState() {
        document.querySelectorAll('[data-explorer-chapter]').forEach(button => {
            button.classList.toggle('active', button.dataset.explorerChapter === chapterFilter.value && chapterFilter.value !== '');
        });
    }

    function syncAppendixTypeState() {
        document.querySelectorAll('[data-type-card]').forEach(button => {
            button.classList.toggle('active', button.dataset.typeCard === appendixTypeFilter.value && appendixTypeFilter.value !== '');
        });
    }

    function renderRequirementInspector(requirement) {
        if (!requirement) {
            document.getElementById('req-id').textContent = '-';
            document.getElementById('req-title').textContent = 'Tidak ada requirement yang sesuai dengan filter aktif.';
            document.getElementById('req-pillar').textContent = '-';
            document.getElementById('req-chapter').textContent = '-';
            document.getElementById('req-cadence').textContent = '-';
            document.getElementById('req-appendix-count').textContent = '0 lampiran';
            document.getElementById('req-summary').textContent = 'Sesuaikan filter untuk melihat requirement yang tersedia.';
            document.getElementById('req-interpretation').textContent = '-';
            document.getElementById('req-focus').innerHTML = '<li>Tidak ada requirement yang cocok.</li>';
            document.getElementById('req-evidence').innerHTML = '<li>Tidak ada evidence cue.</li>';
            document.getElementById('req-appendices').innerHTML = '<span class="sej-empty w-100">Tidak ada lampiran yang ditautkan.</span>';
            document.getElementById('req-reporting').textContent = '-';
            return;
        }

        const pillar = pillarMeta[requirement.pillar] || { label: requirement.pillar || '-' };
        const chapterLabel = chapterMeta[requirement.chapter]?.label || requirement.chapter || '-';

        document.getElementById('req-id').textContent = requirement.id || '-';
        document.getElementById('req-title').textContent = requirement.title || '-';
        document.getElementById('req-pillar').textContent = pillar.label || '-';
        document.getElementById('req-chapter').textContent = `${requirement.chapter || '-'}. ${chapterLabel}`;
        document.getElementById('req-cadence').textContent = requirement.cadence || '-';
        document.getElementById('req-appendix-count').textContent = `${(requirement.appendices || []).length} lampiran`;
        document.getElementById('req-summary').textContent = requirement.summary || '-';
        document.getElementById('req-interpretation').textContent = requirement.interpretation || '-';
        document.getElementById('req-reporting').textContent = requirement.reporting || '-';

        const focus = document.getElementById('req-focus');
        focus.innerHTML = '';
        (requirement.focus && requirement.focus.length ? requirement.focus : ['Tidak ada fokus implementasi tambahan.']).forEach(item => {
            const li = document.createElement('li');
            li.textContent = item;
            focus.appendChild(li);
        });

        const evidence = document.getElementById('req-evidence');
        evidence.innerHTML = '';
        (requirement.evidence && requirement.evidence.length ? requirement.evidence : ['Tidak ada evidence cue.']).forEach(item => {
            const li = document.createElement('li');
            li.textContent = item;
            evidence.appendChild(li);
        });

        const refs = document.getElementById('req-appendices');
        refs.innerHTML = '';
        if (requirement.appendices && requirement.appendices.length) {
            requirement.appendices.forEach(ref => {
                const button = document.createElement('button');
                button.type = 'button';
                button.className = 'sej-ref';
                button.dataset.appendixId = ref;
                button.textContent = ref;
                refs.appendChild(button);
            });
        } else {
            refs.innerHTML = '<span class="sej-empty w-100">Requirement ini tidak menunjuk lampiran spesifik.</span>';
        }
    }

    function buildRequirementItem(requirement, selected) {
        const pillar = pillarMeta[requirement.pillar] || { label: requirement.pillar || '-', color: '#144e72' };
        const appendixCount = Array.isArray(requirement.appendices) ? requirement.appendices.length : 0;

        return `
            <button type="button" class="sej-item${selected ? ' active' : ''}" data-requirement-id="${escapeHtml(requirement.id)}" style="--accent:${escapeHtml(pillar.color)}">
                <div class="sej-item-top">
                    <span class="sej-item-code">${escapeHtml(requirement.id)}</span>
                    <span class="sej-pill">${escapeHtml(pillar.label)}</span>
                </div>
                <div class="sej-item-name">${escapeHtml(requirement.title || '-')}</div>
                <div class="sej-item-meta">
                    <span>${escapeHtml((requirement.chapter || '-') + '. ' + (chapterMeta[requirement.chapter]?.label || requirement.chapter_title || '-'))}</span>
                    <span>${appendixCount} lampiran</span>
                </div>
            </button>
        `;
    }

    function renderRequirementList(list) {
        if (!list.length) {
            requirementList.innerHTML = '<div class="sej-empty">Tidak ada requirement yang cocok dengan filter saat ini.</div>';
            return;
        }

        requirementList.innerHTML = list.map(item => buildRequirementItem(item, item.id === activeRequirementId)).join('');
        requirementList.querySelectorAll('.sej-item').forEach(node => {
            node.addEventListener('click', function () {
                const id = node.dataset.requirementId;
                if (id) {
                    setActiveRequirement(id);
                    renderRequirementList(list);
                }
            });
        });
    }

    function setActiveRequirement(id) {
        activeRequirementId = id;
        renderRequirementInspector(requirementMap[id] || null);
        document.querySelectorAll('.sej-item').forEach(item => {
            item.classList.toggle('active', item.dataset.requirementId === id);
        });
    }

    function updateRequirementSummary(count) {
        document.getElementById('requirementVisibleCount').textContent = count;
        document.getElementById('requirementCountChip').textContent = `${count} entri`;
        document.getElementById('requirementVisibleSummary').textContent = count
            ? `Menampilkan ${count} requirement SEOJK sesuai filter aktif.`
            : 'Tidak ada requirement yang cocok dengan filter saat ini.';
    }

    function filteredRequirements() {
        const query = (requirementSearch.value || '').trim().toLowerCase();

        return requirements.filter(requirement => {
            if (activePillar && requirement.pillar !== activePillar) return false;
            if (chapterFilter.value && requirement.chapter !== chapterFilter.value) return false;

            if (!query) return true;

            return [
                requirement.id,
                requirement.title,
                requirement.summary,
                requirement.chapter_title,
                pillarMeta[requirement.pillar]?.label,
                ...(requirement.appendices || []),
                ...(requirement.focus || []),
            ]
                .join(' ')
                .toLowerCase()
                .includes(query);
        });
    }

    function applyRequirementFilters(preferredId = null) {
        const list = filteredRequirements();
        updateRequirementSummary(list.length);
        syncPillarState();
        syncChapterState();

        if (!list.length) {
            activeRequirementId = null;
            renderRequirementInspector(null);
            renderRequirementList([]);
            return;
        }

        const visibleIds = list.map(item => item.id);
        const target = preferredId && visibleIds.includes(preferredId)
            ? preferredId
            : (activeRequirementId && visibleIds.includes(activeRequirementId) ? activeRequirementId : visibleIds[0]);

        setActiveRequirement(target);
        renderRequirementList(list);
    }

    function openAppendix(id) {
        const appendix = appendixMap[id];
        if (!appendix) return;

        document.getElementById('appmod-id').textContent = `${appendix.id} | ${appendix.type}`;
        document.getElementById('appmod-title').textContent = appendix.title || 'Detail Lampiran';
        document.getElementById('appmod-summary').textContent = `${appendix.scope || '-'} - ${appendix.summary || '-'}`;

        const contains = document.getElementById('appmod-contains');
        contains.innerHTML = '';
        (appendix.contains && appendix.contains.length ? appendix.contains : ['Tidak ada artefak yang dipetakan.']).forEach(item => {
            const li = document.createElement('li');
            li.textContent = item;
            contains.appendChild(li);
        });

        const links = document.getElementById('appmod-links');
        links.innerHTML = '';
        (appendix.used_by && appendix.used_by.length ? appendix.used_by : []).forEach(item => {
            const button = document.createElement('button');
            button.type = 'button';
            button.className = 'sej-ref';
            button.dataset.linkedRequirementId = item;
            button.textContent = item;
            links.appendChild(button);
        });

        if (!links.children.length) {
            links.innerHTML = '<span class="sej-empty w-100">Belum ada requirement yang dipetakan ke lampiran ini.</span>';
        }

        appendixModal.show();
    }

    function updateAppendixSummary(count) {
        document.getElementById('appendixVisibleCount').textContent = count;
        document.getElementById('appendixCountChip').textContent = `${count} entri`;
        document.getElementById('appendixVisibleSummary').textContent = count
            ? `Menampilkan ${count} lampiran SEOJK sesuai filter aktif.`
            : 'Tidak ada lampiran yang cocok dengan filter saat ini.';
        syncAppendixTypeState();
    }

    function applyAppendixFilters(preferredId = null) {
        appendixTable.column(1).search(appendixTypeFilter.value ? escapeRegex(appendixTypeFilter.value) : '', true, false);
        appendixTable.search(appendixSearch.value || '').draw();
        const visibleIds = appendixTable.rows({ filter: 'applied' }).nodes().to$().map((_, row) => row.dataset.appendixId).get();
        updateAppendixSummary(visibleIds.length);

        if (preferredId && visibleIds.includes(preferredId)) {
            openAppendix(preferredId);
            $('#appendixTable tbody tr').removeClass('is-active');
            $(`#appendixTable tbody tr[data-appendix-id="${preferredId}"]`).addClass('is-active');
        }
    }

    function jumpExplorer(pillar = '', chapter = '') {
        activePillar = pillar;
        chapterFilter.value = chapter;
        requirementSearch.value = '';
        applyRequirementFilters();
        showTab(explorerTab);
    }

    function jumpAppendix(id = '', type = '') {
        appendixTypeFilter.value = type || '';
        appendixSearch.value = id || '';
        applyAppendixFilters(id || null);
        showTab(referenceTab);
    }

    document.querySelectorAll('.sej-tab').forEach(button => {
        button.addEventListener('shown.bs.tab', () => syncTabState(button));
    });

    document.querySelectorAll('[data-pillar-card]').forEach(button => {
        button.addEventListener('click', function () {
            activePillar = button.dataset.pillarCard === activePillar ? '' : button.dataset.pillarCard;
            applyRequirementFilters();
        });
    });

    document.querySelectorAll('[data-explorer-pillar]').forEach(button => {
        button.addEventListener('click', function () {
            jumpExplorer(button.dataset.explorerPillar || '', '');
        });
    });

    document.querySelectorAll('[data-explorer-chapter]').forEach(button => {
        button.addEventListener('click', function () {
            jumpExplorer('', button.dataset.explorerChapter || '');
        });
    });

    document.querySelectorAll('[data-type-card]').forEach(button => {
        button.addEventListener('click', function () {
            const type = button.dataset.typeCard || '';
            appendixTypeFilter.value = appendixTypeFilter.value === type ? '' : type;
            appendixSearch.value = '';
            applyAppendixFilters();
            showTab(referenceTab);
        });
    });

    chapterFilter.addEventListener('change', () => applyRequirementFilters());
    requirementSearch.addEventListener('input', () => applyRequirementFilters());
    document.getElementById('resetRequirementFilters').addEventListener('click', function () {
        activePillar = '';
        chapterFilter.value = '';
        requirementSearch.value = '';
        applyRequirementFilters(firstRequirementId);
    });

    appendixTypeFilter.addEventListener('change', () => applyAppendixFilters());
    appendixSearch.addEventListener('input', () => applyAppendixFilters());
    document.getElementById('resetAppendixFilters').addEventListener('click', function () {
        appendixTypeFilter.value = '';
        appendixSearch.value = '';
        applyAppendixFilters();
    });

    $('#appendixTable tbody').on('click', 'tr', function () {
        const id = this.dataset.appendixId;
        if (!id) return;
        $('#appendixTable tbody tr').removeClass('is-active');
        this.classList.add('is-active');
        openAppendix(id);
    });

    document.addEventListener('click', function (event) {
        const appendixButton = event.target.closest('.sej-ref[data-appendix-id]');
        if (appendixButton) {
            jumpAppendix(appendixButton.dataset.appendixId || '', '');
            return;
        }

        const linkedRequirementButton = event.target.closest('[data-linked-requirement-id]');
        if (linkedRequirementButton) {
            const id = linkedRequirementButton.dataset.linkedRequirementId;
            const requirement = requirementMap[id];
            activePillar = requirement?.pillar || '';
            chapterFilter.value = requirement?.chapter || '';
            requirementSearch.value = '';
            applyRequirementFilters(id);
            showTab(explorerTab);
            appendixModal.hide();
        }
    });

    syncTabState(document.querySelector('.sej-tab.active'));
    applyRequirementFilters(firstRequirementId);
    applyAppendixFilters();
});
</script>
@endpush
