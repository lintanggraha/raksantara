@extends('layouts.tabler')

@section('title', 'NIST CSF 2.0 - Risk Management System')

@push('styles')
<link rel="stylesheet" href="{{ asset('vendor/datatables/css/dataTables.bootstrap5.min.css') }}">
<style>
.nst-page{--ink:#14263b;--muted:#5c6776;--line:rgba(20,38,59,.1);--shell:linear-gradient(180deg,#f6f1e8 0%,#edf5f4 100%);color:var(--ink);padding:.25rem;border-radius:32px;background:var(--shell)}
.nst-shell{display:grid;gap:1rem}.nst-hero{display:grid;grid-template-columns:1.55fr .9fr;gap:1.2rem;align-items:stretch;min-height:368px;padding:1.45rem;border-radius:28px;overflow:hidden;position:relative;background:radial-gradient(circle at top right,rgba(247,208,156,.86),transparent 30%),radial-gradient(circle at bottom left,rgba(155,214,205,.68),transparent 28%),linear-gradient(135deg,#17324d 0%,#1f4d6f 48%,#f2e0bf 100%);box-shadow:0 20px 44px rgba(15,23,42,.09)}.nst-hero>*{position:relative;z-index:1}
.nst-kicker{display:inline-flex;align-items:center;gap:.45rem;padding:.35rem .7rem;border-radius:999px;background:rgba(255,250,242,.18);color:rgba(255,250,242,.92);font-size:.75rem;font-weight:700;text-transform:uppercase;letter-spacing:.08em}.nst-title{margin:1rem 0 .7rem;color:#fffaf2;font-size:clamp(2rem,3.8vw,3rem);font-weight:800;line-height:1.04}.nst-lede{margin:0;max-width:700px;color:rgba(255,250,242,.82);line-height:1.7}
.nst-metrics{display:grid;grid-template-columns:repeat(4,1fr);gap:.65rem;margin-top:1.1rem}.nst-metric,.nst-side,.nst-panel,.nst-mini{border:1px solid var(--line);background:linear-gradient(180deg,rgba(255,251,245,.98) 0%,rgba(246,251,252,.98) 100%);box-shadow:0 14px 28px rgba(15,23,42,.05)}.nst-metric{padding:.68rem .74rem;border-radius:16px;background:rgba(255,250,242,.12);border-color:rgba(255,255,255,.18);min-height:96px;display:flex;flex-direction:column;justify-content:flex-start}.nst-metric label,.nst-side label,.nst-mini label,.nst-form label,.nst-inspector small,.nst-label{display:block;font-size:.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.08em}.nst-metric label{color:rgba(255,250,242,.7);margin-bottom:.35rem}.nst-metric strong{display:block;color:#fffaf2;font-size:1.5rem;font-weight:800;line-height:1}.nst-metric span{display:block;margin-top:.34rem;color:rgba(255,250,242,.72);font-size:.76rem}
.nst-side-stack{display:grid;gap:.85rem}.nst-side{padding:.8rem .86rem;border-radius:18px;background:rgba(255,250,242,.78);border-color:rgba(255,255,255,.24);min-height:142px}.nst-side label{color:var(--muted);margin-bottom:.4rem}.nst-side h3{margin:0;font-size:1rem;font-weight:800}.nst-side p{margin:.55rem 0 0;color:var(--muted);line-height:1.55;font-size:.84rem}
.nst-nav{display:grid;grid-template-columns:repeat(3,1fr);gap:.75rem}.nst-tab{display:grid;grid-template-columns:auto 1fr;gap:.72rem;align-items:center;padding:.82rem .88rem;border-radius:18px;border:1px solid var(--line);background:linear-gradient(180deg,rgba(255,250,242,.94) 0%,rgba(239,245,246,.94) 100%);text-align:left;color:var(--ink);box-shadow:0 12px 24px rgba(15,23,42,.04);cursor:pointer}.nst-tab.active{border-color:rgba(20,78,114,.24);box-shadow:0 18px 30px rgba(20,78,114,.1)}.nst-tab i{width:2.35rem;height:2.35rem;display:inline-flex;align-items:center;justify-content:center;border-radius:14px;background:rgba(20,38,59,.06)}.nst-tab strong{display:block;font-size:.9rem;font-weight:800}.nst-tab span{display:block;margin-top:.14rem;color:var(--muted);font-size:.76rem;line-height:1.4}
.nst-grid{display:grid;gap:1rem}.nst-grid.two{grid-template-columns:1.06fr .94fr}.nst-panel{padding:1rem;border-radius:20px}.nst-head{display:flex;align-items:center;justify-content:space-between;gap:.85rem;margin-bottom:.7rem}.nst-head h3{margin:0;font-size:.98rem;font-weight:800}.nst-chip,.nst-pill,.nst-meta span,.nst-ref{display:inline-flex;align-items:center;gap:.3rem;padding:.24rem .52rem;border-radius:999px;font-size:.7rem;font-weight:700;line-height:1.2}.nst-chip{background:rgba(20,38,59,.08);color:var(--ink)}.nst-copy{margin:0 0 .85rem;color:var(--muted);line-height:1.6;font-size:.84rem}
.nst-bars,.nst-cards,.nst-hotspots,.nst-families{display:grid;gap:.65rem}.nst-bar,.nst-hotspot,.nst-family{display:grid;grid-template-columns:minmax(0,1.25fr) minmax(180px,1fr) auto;gap:.68rem;align-items:center;padding:.62rem .72rem;border-radius:16px;border:1px solid transparent;background:rgba(255,255,255,.55);text-align:left;cursor:pointer;transition:border-color .15s ease,transform .15s ease,box-shadow .15s ease}.nst-family{grid-template-columns:1fr auto}.nst-bar:hover,.nst-hotspot:hover,.nst-family:hover,.nst-bar.active,.nst-hotspot.active,.nst-family.active{transform:translateY(-1px);border-color:rgba(20,78,114,.22);box-shadow:0 12px 22px rgba(20,78,114,.08)}.nst-bar strong,.nst-hotspot strong,.nst-family strong{display:block;font-size:.86rem}.nst-bar em,.nst-hotspot em,.nst-family em{display:block;margin-top:.12rem;color:var(--muted);font-size:.74rem;font-style:normal}.nst-track{height:.44rem;border-radius:999px;background:rgba(20,38,59,.08);overflow:hidden}.nst-track b{display:block;height:100%;border-radius:inherit}.nst-num{min-width:2.2rem;text-align:right;font-weight:800}
.nst-cards{grid-template-columns:repeat(3,1fr)}.nst-card{width:100%;padding:.76rem .8rem;border-radius:16px;border:1px solid var(--line);background:rgba(255,255,255,.75);text-align:left;cursor:pointer}.nst-card.active{border-color:rgba(20,78,114,.24);box-shadow:0 16px 28px rgba(20,78,114,.08);background:rgba(238,245,245,.72)}.nst-card-top{display:flex;align-items:center;justify-content:space-between;gap:.55rem;color:var(--muted);font-size:.64rem;text-transform:uppercase;letter-spacing:.06em;font-weight:700}.nst-icon{width:1.9rem;height:1.9rem;display:inline-flex;align-items:center;justify-content:center;border-radius:12px;background:rgba(20,78,114,.12);color:var(--accent);font-size:.8rem}.nst-card strong{display:block;margin-top:.42rem;font-size:.86rem;font-weight:800}.nst-card p{margin:.22rem 0 0;color:var(--muted);font-size:.72rem;line-height:1.4}
.nst-mini-row{display:grid;grid-template-columns:repeat(3,1fr);gap:.65rem;margin-bottom:.8rem}.nst-mini{padding:.72rem .78rem;border-radius:16px}.nst-mini label{color:var(--muted)}.nst-mini strong{display:block;margin-top:.2rem;font-size:1.08rem;font-weight:800;color:#144e72}.nst-mini span{display:block;margin-top:.14rem;color:var(--muted);font-size:.72rem;line-height:1.4}
.nst-workspace{display:grid;grid-template-columns:.76fr 1.03fr .91fr;gap:1rem}.nst-refspace{display:grid;grid-template-columns:.82fr 1.18fr;gap:1rem}.nst-function-grid{display:grid;grid-template-columns:repeat(2,1fr);gap:.55rem;margin-bottom:.8rem}.nst-fn{width:100%;padding:.62rem .68rem;border-radius:16px;border:1px solid var(--line);background:rgba(255,255,255,.75);text-align:left;cursor:pointer}.nst-fn.active{border-color:rgba(20,78,114,.24);background:rgba(238,245,245,.72);box-shadow:0 12px 24px rgba(20,78,114,.08)}.nst-fn strong{display:block;font-size:.82rem;font-weight:800;color:var(--accent)}.nst-fn span{display:block;margin-top:.12rem;color:var(--muted);font-size:.74rem}
.nst-form{display:grid;gap:.75rem}.nst-form label{margin-bottom:.3rem;color:var(--muted)}.nst-summary{margin-top:.85rem;padding:.82rem .88rem;border-radius:18px;background:linear-gradient(180deg,rgba(25,61,87,.95) 0%,rgba(20,78,114,.95) 100%);color:#fffaf2}.nst-summary small{display:block;color:rgba(255,250,242,.7);font-size:.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.08em}.nst-summary strong{display:block;margin-top:.28rem;font-size:1.72rem;font-weight:800;line-height:1}.nst-summary span{display:block;margin-top:.42rem;color:rgba(255,250,242,.78);font-size:.78rem;line-height:1.5}
.nst-list{display:grid;gap:.55rem;max-height:720px;overflow-y:auto;padding-right:.12rem}.nst-item{position:relative;width:100%;padding:.7rem .78rem .66rem .88rem;border-radius:14px;border:1px solid rgba(20,38,59,.08);background:#fff;text-align:left;cursor:pointer}.nst-item.active{border-color:rgba(20,78,114,.22);box-shadow:0 10px 20px rgba(20,78,114,.05);background:rgba(255,255,255,.96)}.nst-item::before{content:'';position:absolute;left:0;top:.68rem;bottom:.68rem;width:.18rem;border-radius:999px;background:var(--accent,#144e72)}.nst-item-top{display:flex;flex-wrap:wrap;gap:.5rem;align-items:center;justify-content:space-between}.nst-item-code{font-family:ui-monospace,SFMono-Regular,Menlo,Monaco,Consolas,"Courier New",monospace;font-size:.76rem;font-weight:800;color:var(--accent,#144e72)}.nst-item-name{margin:.28rem 0 .2rem;font-size:.88rem;font-weight:700;line-height:1.38;color:var(--ink)}.nst-item-meta{color:var(--muted);font-size:.74rem;line-height:1.4}.nst-item-meta span+span::before{content:'\2022';margin:0 .4rem;color:rgba(20,38,59,.35)}
.nst-table{margin:0!important;width:100%!important;border-collapse:separate!important;border-spacing:0 .48rem!important}.nst-table thead th{padding:0 .65rem .16rem!important;border:0!important;color:var(--muted);font-size:.7rem;font-weight:700;text-transform:uppercase;letter-spacing:.08em}.nst-table tbody td{padding:.66rem .65rem!important;border-top:1px solid rgba(20,38,59,.08)!important;border-bottom:1px solid rgba(20,38,59,.08)!important;background:rgba(255,255,255,.93);vertical-align:top}.nst-table tbody td:first-child{border-left:1px solid rgba(20,38,59,.08)!important;border-radius:14px 0 0 14px}.nst-table tbody td:last-child{border-right:1px solid rgba(20,38,59,.08)!important;border-radius:0 14px 14px 0}.nst-table tbody tr{cursor:pointer}.nst-table tbody tr.is-active td{border-color:rgba(20,78,114,.22)!important;background:rgba(240,247,248,.94)}.nst-code{font-family:ui-monospace,SFMono-Regular,Menlo,Monaco,Consolas,"Courier New",monospace;font-size:.76rem;font-weight:800;color:var(--accent,#144e72)}.nst-title-sm{font-size:.86rem;font-weight:700;line-height:1.34;color:var(--ink)}.nst-copy-sm{margin-top:.14rem;color:var(--muted);font-size:.72rem;line-height:1.44}
.nst-pill{padding:.2rem .45rem;font-size:.68rem;background:rgba(20,38,59,.08);color:var(--ink)}.nst-inspector{position:sticky;top:1rem;min-height:720px;display:flex;flex-direction:column}.nst-inspector-head{padding-bottom:.85rem;border-bottom:1px solid var(--line)}.nst-inspector-head strong{display:block;margin-top:.35rem;font-size:1rem;font-weight:800;color:#144e72}.nst-inspector-head span{display:block;margin-top:.28rem;font-size:.9rem;font-weight:800;line-height:1.4}.nst-inspector-body{display:grid;gap:.75rem;padding-top:.85rem;flex:1;min-height:0;overflow:auto;align-content:start}.nst-meta{display:flex;flex-wrap:wrap;gap:.45rem}.nst-meta span{background:rgba(20,38,59,.06);color:var(--ink);font-size:.72rem}.nst-callout,.nst-note{padding:.76rem .84rem;border-radius:16px;border:1px solid var(--line);line-height:1.62}.nst-callout{background:rgba(255,255,255,.75)}.nst-note{background:rgba(238,245,245,.84)}.nst-plain{margin:.15rem 0 0;padding-left:1rem;color:var(--muted);font-size:.78rem;line-height:1.6}.nst-plain li{margin-bottom:.16rem}.nst-refs{display:flex;flex-wrap:wrap;gap:.4rem}.nst-ref{border:1px solid rgba(20,38,59,.12);background:rgba(255,255,255,.82);color:var(--ink);font-size:.7rem;cursor:pointer}.nst-empty{padding:.9rem;border-radius:16px;border:1px dashed rgba(20,38,59,.18);background:rgba(255,255,255,.6);color:var(--muted);text-align:center;line-height:1.55}
.dataTables_wrapper .dataTables_paginate .paginate_button{padding:.18rem .55rem!important;border-radius:999px;border:0!important;background:transparent!important}.dataTables_wrapper .dataTables_paginate .paginate_button.current,.dataTables_wrapper .dataTables_paginate .paginate_button.current:hover{color:#fff!important;background:#144e72!important}.dataTables_wrapper .dataTables_paginate .paginate_button:hover{color:#144e72!important;background:rgba(20,78,114,.08)!important}.dataTables_wrapper .dataTables_info{color:var(--muted)!important;font-size:.8rem}
@media (max-width:1399.98px){.nst-workspace{grid-template-columns:1fr}.nst-inspector{position:static;min-height:auto}}@media (max-width:1199.98px){.nst-hero,.nst-metric,.nst-side{min-height:auto}.nst-hero,.nst-nav,.nst-grid.two,.nst-refspace,.nst-metrics,.nst-mini-row,.nst-cards{grid-template-columns:1fr}.nst-bar,.nst-hotspot,.nst-family{grid-template-columns:1fr}}@media (max-width:767.98px){.nst-hero,.nst-panel{padding:1.2rem;border-radius:22px}.nst-function-grid{grid-template-columns:1fr}}
</style>
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
        'GOVERN' => ['label' => 'Tata Kelola', 'color' => '#1d3557', 'icon' => 'fa-sitemap', 'summary' => 'Arah kebijakan, akuntabilitas, dan keputusan manajemen.'],
        'IDENTIFY' => ['label' => 'Identifikasi', 'color' => '#2563eb', 'icon' => 'fa-compass', 'summary' => 'Aset, konteks bisnis, dan eksposur risiko.'],
        'PROTECT' => ['label' => 'Perlindungan', 'color' => '#0f766e', 'icon' => 'fa-shield-alt', 'summary' => 'Safeguard operasional dan teknis yang berjalan konsisten.'],
        'DETECT' => ['label' => 'Deteksi', 'color' => '#a16207', 'icon' => 'fa-binoculars', 'summary' => 'Pemantauan, deteksi anomali, dan eskalasi cepat.'],
        'RESPOND' => ['label' => 'Respons', 'color' => '#b91c1c', 'icon' => 'fa-bolt', 'summary' => 'Koordinasi tindakan dan pengendalian dampak insiden.'],
        'RECOVER' => ['label' => 'Pemulihan', 'color' => '#15803d', 'icon' => 'fa-life-ring', 'summary' => 'Pemulihan layanan dan kesinambungan operasi.'],
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

    $categoryBreakdown = $collection->groupBy('category')->map(function ($items, $category) {
        return [
            'name' => $category,
            'count' => $items->count(),
            'function' => $items->first()['function'] ?? null,
        ];
    })->sortByDesc('count')->values();

    $familyBreakdown = $spCollection->groupBy('family')->map(function ($items, $family) {
        $first = $items->first();

        return [
            'code' => $family,
            'name' => $first['family_name'] ?? $family,
            'count' => $items->count(),
        ];
    })->sortByDesc('count')->values();

    $maxFunctionCount = max($functionBreakdown->max('count') ?: 0, 1);
    $maxCategoryCount = max($categoryBreakdown->max('count') ?: 0, 1);
    $categoryOptions = $categoryBreakdown->pluck('name')->sort()->values();
    $firstControl = $collection->first();
@endphp

<div class="nst-page">
    <div class="nst-shell">
        <section class="nst-hero">
            <div>
                <span class="nst-kicker"><i class="fas fa-compass"></i>Kerangka Kerja</span>
                <h1 class="nst-title">NIST CSF 2.0 Tinjauan Fungsi Inti</h1>
                <p class="nst-lede">
                    Halaman ini menyajikan subkategori NIST CSF 2.0 dalam tampilan terstruktur yang mendukung
                    analisa fungsi inti, penetapan fokus implementasi, dan penelusuran referensi SP 800-53.
                </p>

                <div class="nst-metrics">
                    <div class="nst-metric">
                        <label>Fungsi Inti</label>
                        <strong>{{ $totalFuncs }}</strong>
                        <span>Tata Kelola, Identifikasi, Perlindungan, Deteksi, Respons, dan Pemulihan.</span>
                    </div>
                    <div class="nst-metric">
                        <label>Kategori</label>
                        <strong>{{ $totalCats }}</strong>
                        <span>Kelompok topik yang merangkum area pengendalian.</span>
                    </div>
                    <div class="nst-metric">
                        <label>Subkategori</label>
                        <strong>{{ $totalSubs }}</strong>
                        <span>Subkategori yang dapat dianalisa per fungsi dan kategori.</span>
                    </div>
                    <div class="nst-metric">
                        <label>Referensi SP 800-53</label>
                        <strong>{{ $totalSp800 }}</strong>
                        <span>Referensi lintas kontrol untuk pendalaman implementasi.</span>
                    </div>
                </div>
            </div>

            <div class="nst-side-stack">
                <div class="nst-side">
                    <label>Ruang Lingkup</label>
                    <h3>Analisa fungsi inti dalam format yang lebih operasional</h3>
                    <p>
                        Setiap subkategori dapat dianalisa berdasarkan fungsi, kategori, kebutuhan bukti,
                        serta referensi SP 800-53 yang mendukung penguatan implementasi.
                    </p>
                </div>
                <div class="nst-side">
                    <label>Alur Analisa</label>
                    <p>
                        Gunakan ringkasan untuk melihat distribusi fungsi, lanjutkan ke area eksplorasi
                        untuk menganalisa subkategori, lalu buka referensi SP 800-53 untuk pendalaman kontrol.
                    </p>
                </div>
            </div>
        </section>

        <div class="nst-nav nav" role="tablist">
            <button class="nst-tab active" data-bs-toggle="tab" data-bs-target="#nist-overview-pane" type="button" role="tab">
                <i class="fas fa-chart-line"></i>
                <span>
                    <strong>Ringkasan</strong>
                    <span>Distribusi fungsi dan kategori terpadat.</span>
                </span>
            </button>
            <button class="nst-tab" data-bs-toggle="tab" data-bs-target="#nist-explorer-pane" type="button" role="tab">
                <i class="fas fa-table"></i>
                <span>
                    <strong>Eksplorasi</strong>
                    <span>Filter subkategori dan analisa detail pengendalian.</span>
                </span>
            </button>
            <button class="nst-tab" data-bs-toggle="tab" data-bs-target="#nist-reference-pane" type="button" role="tab">
                <i class="fas fa-link"></i>
                <span>
                    <strong>Referensi</strong>
                    <span>Telusuri kontrol SP 800-53 yang terkait.</span>
                </span>
            </button>
        </div>

        <div class="tab-content nst-grid">
            <div class="tab-pane fade show active" id="nist-overview-pane" role="tabpanel">
                <div class="nst-grid two">
                    <article class="nst-panel">
                        <div class="nst-head">
                            <h3>Lanskap fungsi</h3>
                            <span class="nst-chip">{{ $totalFuncs }} area fungsi</span>
                        </div>
                        <p class="nst-copy">
                            Distribusi ini membantu melihat porsi perhatian tiap fungsi inti sekaligus
                            memudahkan penyaringan subkategori berdasarkan domain kerja NIST CSF.
                        </p>
                        <div class="nst-bars">
                            @foreach ($functionBreakdown as $item)
                                <button type="button" class="nst-bar" data-explorer-function="{{ $item['key'] }}">
                                    <span>
                                        <strong>{{ $item['label'] }}</strong>
                                        <em>{{ $item['categories'] }} kategori</em>
                                    </span>
                                    <span class="nst-track"><b style="width: {{ round(($item['count'] / $maxFunctionCount) * 100, 1) }}%; background: {{ $item['color'] }}"></b></span>
                                    <span class="nst-num">{{ $item['count'] }}</span>
                                </button>
                            @endforeach
                        </div>
                    </article>

                    <article class="nst-panel">
                        <div class="nst-head">
                            <h3>Kategori terpadat</h3>
                            <span class="nst-chip">6 kategori utama</span>
                        </div>

                        <div class="nst-mini-row">
                            <div class="nst-mini">
                                <label>Bukti Audit</label>
                                <strong>{{ $collection->pluck('evidence')->flatten(1)->filter()->count() }}</strong>
                                <span>Total contoh bukti audit yang tercatat pada subkategori NIST.</span>
                            </div>
                            <div class="nst-mini">
                                <label>Kepadatan Referensi</label>
                                <strong>{{ $totalSubs > 0 ? round($collection->avg(fn ($control) => count($control['sp80053'] ?? [])), 1) : 0 }}</strong>
                                <span>Rata-rata tautan SP 800-53 per subkategori.</span>
                            </div>
                            <div class="nst-mini">
                                <label>Keluarga Kontrol</label>
                                <strong>{{ $familyBreakdown->count() }}</strong>
                                <span>Keluarga SP 800-53 yang muncul di halaman ini.</span>
                            </div>
                        </div>

                        <div class="nst-hotspots">
                            @foreach ($categoryBreakdown->take(6) as $item)
                                @php $meta = $functionMeta[$item['function']] ?? null; @endphp
                                <button type="button" class="nst-hotspot" data-explorer-function="{{ $item['function'] }}" data-explorer-category="{{ $item['name'] }}">
                                    <span>
                                        <strong>{{ $item['name'] }}</strong>
                                        <em>{{ $meta['label'] ?? $item['function'] }}</em>
                                    </span>
                                    <span class="nst-track"><b style="width: {{ round(($item['count'] / $maxCategoryCount) * 100, 1) }}%; background: {{ $meta['color'] ?? '#144e72' }}"></b></span>
                                    <span class="nst-num">{{ $item['count'] }}</span>
                                </button>
                            @endforeach
                        </div>
                    </article>
                </div>

                <article class="nst-panel">
                    <div class="nst-head">
                        <h3>Profil fungsi</h3>
                        <span class="nst-chip">6 perspektif fungsi</span>
                    </div>
                    <div class="nst-cards">
                        @foreach ($functionBreakdown as $item)
                            <button type="button" class="nst-card" data-explorer-function="{{ $item['key'] }}" style="--accent: {{ $item['color'] }}">
                                <div class="nst-card-top">
                                    <span class="nst-icon"><i class="fas {{ $item['icon'] }}"></i></span>
                                    <span>{{ $item['count'] }} subkategori</span>
                                </div>
                                <strong>{{ $item['label'] }}</strong>
                                <p>{{ $item['summary'] }}</p>
                            </button>
                        @endforeach
                    </div>
                </article>
            </div>

            <div class="tab-pane fade" id="nist-explorer-pane" role="tabpanel">
                <div class="nst-workspace">
                    <article class="nst-panel">
                        <div class="nst-head">
                            <h3>Filter Tinjauan</h3>
                            <span class="nst-chip">{{ $totalSubs }} subkategori</span>
                        </div>

                        <div class="nst-function-grid">
                            @foreach ($functionBreakdown as $item)
                                <button type="button" class="nst-fn" data-function-card="{{ $item['key'] }}" style="--accent: {{ $item['color'] }}">
                                    <strong>{{ $item['label'] }}</strong>
                                    <span>{{ $item['count'] }} subkategori</span>
                                </button>
                            @endforeach
                        </div>

                        <div class="nst-form">
                            <div>
                                <label for="functionFilter">Fungsi</label>
                                <select id="functionFilter" class="form-select">
                                    <option value="">Semua fungsi</option>
                                    @foreach ($functionBreakdown as $item)
                                        <option value="{{ $item['key'] }}">{{ $item['label'] }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label for="categoryFilter">Kategori</label>
                                <select id="categoryFilter" class="form-select">
                                    <option value="">Semua kategori</option>
                                    @foreach ($categoryOptions as $category)
                                        <option value="{{ $category }}">{{ $category }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label for="controlSearch">Cari subkategori</label>
                                <input id="controlSearch" type="search" class="form-control" placeholder="Cari ID, judul, atau kategori">
                            </div>
                            <button type="button" class="btn btn-outline-secondary" id="resetExplorerFilters">Atur ulang filter</button>
                        </div>

                        <div class="nst-summary">
                            <small>Subkategori Ditampilkan</small>
                            <strong id="visibleCount">{{ $totalSubs }}</strong>
                            <span id="visibleSummary">Menampilkan seluruh subkategori NIST CSF 2.0 yang tersedia.</span>
                        </div>
                    </article>

                    <article class="nst-panel">
                        <div class="nst-head">
                            <h3>Daftar subkategori</h3>
                            <span class="nst-chip" id="tableCountChip">{{ $totalSubs }} entri</span>
                        </div>
                        <div id="explorerList" class="nst-list"></div>
                    </article>

                    <article class="nst-panel nst-inspector">
                        <div class="nst-inspector-head">
                            <small>Detail Subkategori</small>
                            <strong id="nctrl-id">{{ $firstControl['id'] ?? '-' }}</strong>
                            <span id="nctrl-title">{{ $firstControl['title'] ?? 'Pilih subkategori untuk membaca detail.' }}</span>
                        </div>

                        <div class="nst-inspector-body">
                            <div class="nst-meta">
                                <span id="nctrl-function">{{ $functionMeta[$firstControl['function']]['label'] ?? '-' }}</span>
                                <span id="nctrl-category">{{ $firstControl['category'] ?? '-' }}</span>
                                <span id="nctrl-ref-count">{{ count($firstControl['sp80053'] ?? []) }} referensi</span>
                            </div>

                            <div class="nst-callout">
                                <span class="nst-label">Deskripsi</span>
                                <div id="nctrl-description" class="mt-2">{{ $firstControl['description'] ?? 'Pilih subkategori untuk membaca deskripsi.' }}</div>
                            </div>

                            <div class="nst-note">
                                <span class="nst-label">Interpretasi Kontrol</span>
                                <div id="nctrl-interpretation" class="mt-2">{{ $firstControl['interpretation'] ?? '-' }}</div>
                            </div>

                            <div class="nst-callout">
                                <span class="nst-label">Contoh Bukti Audit</span>
                                <ul id="nctrl-evidence" class="nst-plain">
                                    @forelse (($firstControl['evidence'] ?? []) as $item)
                                        <li>{{ $item }}</li>
                                    @empty
                                        <li>Tidak ada contoh bukti audit.</li>
                                    @endforelse
                                </ul>
                            </div>

                            <div class="nst-callout">
                                <span class="nst-label">Referensi SP 800-53</span>
                                <div id="nctrl-refs" class="nst-refs">
                                    @forelse (($firstControl['sp80053'] ?? []) as $ref)
                                        <button type="button" class="nst-ref" data-ref-id="{{ $ref }}">{{ $ref }}</button>
                                    @empty
                                        <span class="nst-empty w-100">Belum ada referensi SP 800-53 yang ditautkan.</span>
                                    @endforelse
                                </div>
                            </div>
                        </div>
                    </article>
                </div>
            </div>

            <div class="tab-pane fade" id="nist-reference-pane" role="tabpanel">
                <div class="nst-refspace">
                    <article class="nst-panel">
                        <div class="nst-head">
                            <h3>Filter referensi</h3>
                            <span class="nst-chip">{{ $spCollection->count() }} kontrol</span>
                        </div>

                        <div class="nst-form">
                            <div>
                                <label for="familyFilter">Keluarga Kontrol</label>
                                <select id="familyFilter" class="form-select">
                                    <option value="">Semua keluarga</option>
                                    @foreach ($familyBreakdown as $family)
                                        <option value="{{ $family['code'] }}">{{ $family['code'] }} - {{ $family['name'] }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label for="sp800Search">Cari referensi</label>
                                <input id="sp800Search" type="search" class="form-control" placeholder="Cari ID, keluarga, atau deskripsi">
                            </div>
                            <button type="button" class="btn btn-outline-secondary" id="resetSp800Filters">Atur ulang filter</button>
                        </div>

                        <div class="nst-summary">
                            <small>Referensi Ditampilkan</small>
                            <strong id="sp800VisibleCount">{{ $spCollection->count() }}</strong>
                            <span id="sp800VisibleSummary">Menampilkan seluruh kontrol SP 800-53 yang dipetakan ke NIST CSF.</span>
                        </div>

                        <div class="nst-families mt-3">
                            @foreach ($familyBreakdown->take(8) as $family)
                                <button type="button" class="nst-family" data-family-card="{{ $family['code'] }}">
                                    <span>
                                        <strong>{{ $family['code'] }} - {{ $family['name'] }}</strong>
                                        <em>{{ $family['count'] }} kontrol terkait</em>
                                    </span>
                                    <span class="nst-num">{{ $family['count'] }}</span>
                                </button>
                            @endforeach
                        </div>
                    </article>

                    <article class="nst-panel">
                        <div class="nst-head">
                            <h3>Referensi SP 800-53</h3>
                            <span class="nst-chip" id="sp800CountChip">{{ $spCollection->count() }} entri</span>
                        </div>
                        <table id="sp800Table" class="table nst-table align-middle">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Keluarga</th>
                                    <th>Kontrol</th>
                                    <th>Referensi NIST</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($sp800Controls as $control)
                                    <tr data-sp800-id="{{ $control['id'] }}">
                                        <td><span class="nst-code">{{ $control['id'] }}</span></td>
                                        <td>{{ $control['family'] }}</td>
                                        <td>
                                            <div class="nst-title-sm">{{ $control['family_name'] }}</div>
                                            <div class="nst-copy-sm">{{ \Illuminate\Support\Str::limit($control['description'], 110) }}</div>
                                        </td>
                                        <td>{{ count($control['nist_references'] ?? []) }}</td>
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

<div class="modal fade" id="sp800Modal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <div>
                    <div class="text-muted text-uppercase fw-semibold small" id="spmod-id">-</div>
                    <h5 class="modal-title mt-1" id="spmod-family">Detail SP 800-53</h5>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <div class="text-muted text-uppercase fw-semibold small mb-2">Deskripsi Kontrol</div>
                    <div id="spmod-description">-</div>
                </div>
                <div class="mb-3">
                    <div class="text-muted text-uppercase fw-semibold small mb-2">Contoh Bukti Audit</div>
                    <ul id="spmod-evidence" class="nst-plain"></ul>
                </div>
                <div>
                    <div class="text-muted text-uppercase fw-semibold small mb-2">Referensi NIST CSF</div>
                    <div id="spmod-links" class="d-flex flex-wrap gap-2"></div>
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
    const controls = @json($controls);
    const sp800Controls = @json($sp800Controls);
    const functionMeta = @json($functionMeta);
    const controlMap = Object.fromEntries(controls.map(item => [item.id, item]));
    const sp800Map = Object.fromEntries(sp800Controls.map(item => [item.id, item]));
    const firstControlId = @json($firstControl['id'] ?? null);
    const explorerTab = document.querySelector('.nst-tab[data-bs-target="#nist-explorer-pane"]');
    const referenceTab = document.querySelector('.nst-tab[data-bs-target="#nist-reference-pane"]');
    const functionFilter = document.getElementById('functionFilter');
    const categoryFilter = document.getElementById('categoryFilter');
    const controlSearch = document.getElementById('controlSearch');
    const familyFilter = document.getElementById('familyFilter');
    const sp800Search = document.getElementById('sp800Search');
    const sp800Modal = new bootstrap.Modal(document.getElementById('sp800Modal'));
    const explorerList = document.getElementById('explorerList');
    let activeControlId = firstControlId;

    const escapeRegex = value => value.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
    const escapeHtml = value => String(value ?? '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
    const showTab = trigger => trigger && bootstrap.Tab.getOrCreateInstance(trigger).show();

    // Daftar subkategori dirender manual agar tampil lebih ringkas.
    const sp800Table = $('#sp800Table').DataTable({
        pageLength: 10, lengthChange: false, autoWidth: false, dom: 'rtip', order: [[0, 'asc']],
        language: { paginate: { previous: 'Sebelumnya', next: 'Berikutnya' }, zeroRecords: 'Tidak ada kontrol SP 800-53 yang sesuai.', info: 'Menampilkan _START_ sampai _END_ dari _TOTAL_ entri', infoEmpty: 'Tidak ada entri untuk ditampilkan', infoFiltered: '' }
    });

    function syncTabState(activeBtn) {
        document.querySelectorAll('.nst-tab').forEach(tab => tab.classList.remove('active'));
        if (activeBtn) activeBtn.classList.add('active');
    }

    function syncFunctionState() {
        document.querySelectorAll('[data-function-card]').forEach(button => {
            button.classList.toggle('active', button.dataset.functionCard === functionFilter.value && functionFilter.value !== '');
        });
        document.querySelectorAll('[data-explorer-function]').forEach(button => {
            button.classList.toggle('active', button.dataset.explorerFunction === functionFilter.value && functionFilter.value !== '');
        });
    }

    function syncFamilyState() {
        document.querySelectorAll('[data-family-card]').forEach(button => {
            button.classList.toggle('active', button.dataset.familyCard === familyFilter.value && familyFilter.value !== '');
        });
    }

    function renderInspector(control) {
        if (!control) {
            document.getElementById('nctrl-id').textContent = '-';
            document.getElementById('nctrl-title').textContent = 'Tidak ada subkategori yang sesuai dengan filter.';
            document.getElementById('nctrl-function').textContent = '-';
            document.getElementById('nctrl-category').textContent = '-';
            document.getElementById('nctrl-ref-count').textContent = '0 referensi';
            document.getElementById('nctrl-description').textContent = 'Sesuaikan filter untuk melihat detail subkategori.';
            document.getElementById('nctrl-interpretation').textContent = '-';
            document.getElementById('nctrl-evidence').innerHTML = '<li>Tidak ada data.</li>';
            document.getElementById('nctrl-refs').innerHTML = '<span class="nst-empty w-100">Tidak ada referensi.</span>';
            return;
        }

        document.getElementById('nctrl-id').textContent = control.id || '-';
        document.getElementById('nctrl-title').textContent = control.title || '-';
        document.getElementById('nctrl-function').textContent = functionMeta[control.function]?.label || control.function || '-';
        document.getElementById('nctrl-category').textContent = control.category || '-';
        document.getElementById('nctrl-ref-count').textContent = `${(control.sp80053 || []).length} referensi`;
        document.getElementById('nctrl-description').textContent = control.description || '-';
        document.getElementById('nctrl-interpretation').textContent = control.interpretation || '-';

        const evidence = document.getElementById('nctrl-evidence');
        evidence.innerHTML = '';
        (control.evidence && control.evidence.length ? control.evidence : ['Tidak ada contoh bukti audit.']).forEach(item => {
            const li = document.createElement('li');
            li.textContent = item;
            evidence.appendChild(li);
        });

        const refs = document.getElementById('nctrl-refs');
        refs.innerHTML = '';
        if (control.sp80053 && control.sp80053.length) {
            control.sp80053.forEach(ref => {
                const button = document.createElement('button');
                button.type = 'button';
                button.className = 'nst-ref';
                button.dataset.refId = ref;
                button.textContent = ref;
                refs.appendChild(button);
            });
        } else {
            refs.innerHTML = '<span class="nst-empty w-100">Belum ada referensi SP 800-53 yang ditautkan.</span>';
        }
    }

    function buildExplorerItem(control, selected) {
        const meta = functionMeta[control.function] || { label: control.function || '-', color: '#144e72' };
        const refCount = Array.isArray(control.sp80053) ? control.sp80053.length : 0;

        return `
            <button type="button" class="nst-item${selected ? ' active' : ''}" data-control-id="${escapeHtml(control.id)}" style="--accent:${escapeHtml(meta.color)}">
                <div class="nst-item-top">
                    <span class="nst-item-code">${escapeHtml(control.id)}</span>
                    <span class="nst-pill">${escapeHtml(meta.label)}</span>
                </div>
                <div class="nst-item-name">${escapeHtml(control.title || '-')}</div>
                <div class="nst-item-meta">
                    <span>${escapeHtml(control.category || '-')}</span>
                    <span>${refCount} referensi</span>
                </div>
            </button>
        `;
    }

    function renderExplorerList(list) {
        if (!list.length) {
            explorerList.innerHTML = '<div class="nst-empty">Tidak ada subkategori yang cocok dengan filter saat ini.</div>';
            return;
        }

        explorerList.innerHTML = list.map(control => buildExplorerItem(control, control.id === activeControlId)).join('');
        explorerList.querySelectorAll('.nst-item').forEach(node => {
            node.addEventListener('click', function () {
                const id = node.dataset.controlId;
                if (id) {
                    setActiveControl(id);
                    renderExplorerList(list);
                }
            });
        });
    }

    function setActiveControl(id) {
        activeControlId = id;
        renderInspector(controlMap[id] || null);
        document.querySelectorAll('.nst-item').forEach(item => {
            item.classList.toggle('active', item.dataset.controlId === id);
        });
    }

    function updateExplorerSummary(count) {
        document.getElementById('visibleCount').textContent = count;
        document.getElementById('tableCountChip').textContent = `${count} entri`;
        document.getElementById('visibleSummary').textContent = count
            ? `Menampilkan ${count} subkategori sesuai filter aktif.`
            : 'Tidak ada subkategori yang cocok dengan filter saat ini.';
    }

    function filteredControls() {
        const query = (controlSearch.value || '').trim().toLowerCase();

        return controls.filter(control => {
            if (functionFilter.value && control.function !== functionFilter.value) return false;
            if (categoryFilter.value && control.category !== categoryFilter.value) return false;

            if (!query) return true;

            return [
                control.id,
                control.title,
                control.description,
                control.category,
                functionMeta[control.function]?.label,
                ...(control.sp80053 || []),
            ]
                .join(' ')
                .toLowerCase()
                .includes(query);
        });
    }

    function applyExplorer(preferredId = null) {
        const list = filteredControls();
        updateExplorerSummary(list.length);
        syncFunctionState();

        if (!list.length) {
            activeControlId = null;
            renderInspector(null);
            renderExplorerList([]);
            return;
        }

        const visibleIds = list.map(control => control.id);
        const target = preferredId && visibleIds.includes(preferredId)
            ? preferredId
            : (activeControlId && visibleIds.includes(activeControlId) ? activeControlId : visibleIds[0]);

        setActiveControl(target);
        renderExplorerList(list);
    }

    function openSp800(id) {
        const control = sp800Map[id];
        if (!control) return;
        document.getElementById('spmod-id').textContent = control.id || '-';
        document.getElementById('spmod-family').textContent = `${control.family || '-'} - ${control.family_name || '-'}`;
        document.getElementById('spmod-description').textContent = control.description || '-';
        const evidence = document.getElementById('spmod-evidence');
        evidence.innerHTML = '';
        (control.bukti_audit && control.bukti_audit.length ? control.bukti_audit : ['Tidak ada contoh bukti audit.']).forEach(item => {
            const li = document.createElement('li');
            li.textContent = item;
            evidence.appendChild(li);
        });
        const links = document.getElementById('spmod-links');
        links.innerHTML = '';
        (control.nist_references && control.nist_references.length ? control.nist_references : ['Tidak ada referensi NIST CSF.']).forEach(item => {
            const span = document.createElement('span');
            span.className = 'badge bg-light text-dark border';
            span.textContent = item;
            links.appendChild(span);
        });
        sp800Modal.show();
    }

    function updateSp800Summary(count) {
        document.getElementById('sp800VisibleCount').textContent = count;
        document.getElementById('sp800CountChip').textContent = `${count} entri`;
        document.getElementById('sp800VisibleSummary').textContent = count
            ? `Menampilkan ${count} kontrol SP 800-53 sesuai filter aktif.`
            : 'Tidak ada kontrol SP 800-53 yang cocok dengan filter saat ini.';
        syncFamilyState();
    }

    function applySp800(preferredId = null) {
        sp800Table.column(1).search(familyFilter.value ? escapeRegex(familyFilter.value) : '', true, false);
        sp800Table.search(sp800Search.value || '').draw();
        const visibleIds = sp800Table.rows({ filter: 'applied' }).nodes().to$().map((_, row) => row.dataset.sp800Id).get();
        updateSp800Summary(visibleIds.length);
        if (preferredId && visibleIds.includes(preferredId)) openSp800(preferredId);
    }

    function jumpExplorer(fn = '', category = '') {
        functionFilter.value = fn;
        categoryFilter.value = category;
        applyExplorer();
        showTab(explorerTab);
    }

    function jumpReference(id = '') {
        familyFilter.value = '';
        sp800Search.value = id;
        applySp800(id || null);
        showTab(referenceTab);
    }

    document.querySelectorAll('.nst-tab').forEach(button => button.addEventListener('shown.bs.tab', () => syncTabState(button)));
    document.querySelectorAll('[data-function-card]').forEach(button => button.addEventListener('click', () => {
        functionFilter.value = button.dataset.functionCard === functionFilter.value ? '' : button.dataset.functionCard;
        applyExplorer();
    }));
    document.querySelectorAll('[data-explorer-function]').forEach(button => button.addEventListener('click', () => jumpExplorer(button.dataset.explorerFunction || '', button.dataset.explorerCategory || '')));
    document.querySelectorAll('[data-family-card]').forEach(button => button.addEventListener('click', () => {
        familyFilter.value = button.dataset.familyCard === familyFilter.value ? '' : button.dataset.familyCard;
        applySp800();
    }));

    functionFilter.addEventListener('change', () => applyExplorer());
    categoryFilter.addEventListener('change', () => applyExplorer());
    controlSearch.addEventListener('input', () => applyExplorer());
    document.getElementById('resetExplorerFilters').addEventListener('click', () => {
        functionFilter.value = '';
        categoryFilter.value = '';
        controlSearch.value = '';
        applyExplorer(firstControlId);
    });

    familyFilter.addEventListener('change', () => applySp800());
    sp800Search.addEventListener('input', () => applySp800());
    document.getElementById('resetSp800Filters').addEventListener('click', () => {
        familyFilter.value = '';
        sp800Search.value = '';
        applySp800();
    });

    document.getElementById('nctrl-refs').addEventListener('click', function (event) {
        const button = event.target.closest('[data-ref-id]');
        if (button) jumpReference(button.dataset.refId);
    });

    $('#sp800Table tbody').on('click', 'tr', function () {
        if (this.dataset.sp800Id) openSp800(this.dataset.sp800Id);
    });

    applyExplorer(firstControlId);
    applySp800();
});
</script>
@endpush
