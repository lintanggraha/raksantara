@extends('layouts.tabler')

@section('title', 'PBI 02/2024 - Raksantara')

@push('styles')
<style>
.pbi-page{--ink:#14263b;--muted:#5c6776;--line:rgba(20,38,59,.1);--shell:linear-gradient(180deg,#f6efe4 0%,#edf5f6 100%);color:var(--ink);padding:.25rem;border-radius:32px;background:var(--shell)}
.pbi-shell{display:grid;gap:1rem}.pbi-hero{display:grid;grid-template-columns:1.55fr .92fr;gap:1.2rem;align-items:stretch;min-height:368px;padding:1.45rem;border-radius:28px;overflow:hidden;position:relative;background:radial-gradient(circle at top right,rgba(248,214,161,.88),transparent 30%),radial-gradient(circle at bottom left,rgba(156,210,219,.7),transparent 28%),linear-gradient(135deg,#132a43 0%,#1d5671 46%,#f2debb 100%);box-shadow:0 20px 44px rgba(15,23,42,.09)}.pbi-hero>*{position:relative;z-index:1}
.pbi-kicker{display:inline-flex;align-items:center;gap:.45rem;padding:.35rem .7rem;border-radius:999px;background:rgba(255,250,242,.18);color:rgba(255,250,242,.92);font-size:.75rem;font-weight:700;text-transform:uppercase;letter-spacing:.08em}.pbi-title{margin:1rem 0 .7rem;color:#fffaf2;font-size:clamp(2rem,3.8vw,3rem);font-weight:800;line-height:1.04}.pbi-lede{margin:0;max-width:760px;color:rgba(255,250,242,.82);line-height:1.7}
.pbi-metrics{display:grid;grid-template-columns:repeat(4,1fr);gap:.65rem;margin-top:1.1rem}.pbi-metric,.pbi-side,.pbi-panel,.pbi-mini,.pbi-refcard{border:1px solid var(--line);background:linear-gradient(180deg,rgba(255,251,245,.98) 0%,rgba(246,251,252,.98) 100%);box-shadow:0 14px 28px rgba(15,23,42,.05)}.pbi-metric{padding:.68rem .74rem;border-radius:16px;background:rgba(255,250,242,.12);border-color:rgba(255,255,255,.18);min-height:96px;display:flex;flex-direction:column;justify-content:flex-start}.pbi-metric label,.pbi-side label,.pbi-mini label,.pbi-form label,.pbi-inspector small,.pbi-label{display:block;font-size:.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.08em}.pbi-metric label{color:rgba(255,250,242,.7);margin-bottom:.35rem}.pbi-metric strong{display:block;color:#fffaf2;font-size:1.5rem;font-weight:800;line-height:1}.pbi-metric span{display:block;margin-top:.34rem;color:rgba(255,250,242,.72);font-size:.76rem;line-height:1.45}
.pbi-side-stack{display:grid;gap:.85rem}.pbi-side{padding:.8rem .86rem;border-radius:18px;background:rgba(255,250,242,.78);border-color:rgba(255,255,255,.24);min-height:142px}.pbi-side label{color:var(--muted);margin-bottom:.4rem}.pbi-side h3{margin:0;font-size:1rem;font-weight:800}.pbi-side p{margin:.55rem 0 0;color:var(--muted);line-height:1.55;font-size:.84rem}
.pbi-nav{display:grid;grid-template-columns:repeat(3,1fr);gap:.75rem}.pbi-tab{display:grid;grid-template-columns:auto 1fr;gap:.72rem;align-items:center;padding:.82rem .88rem;border-radius:18px;border:1px solid var(--line);background:linear-gradient(180deg,rgba(255,250,242,.94) 0%,rgba(239,245,246,.94) 100%);text-align:left;color:var(--ink);box-shadow:0 12px 24px rgba(15,23,42,.04);cursor:pointer}.pbi-tab.active{border-color:rgba(20,78,114,.24);box-shadow:0 18px 30px rgba(20,78,114,.1)}.pbi-tab i{width:2.35rem;height:2.35rem;display:inline-flex;align-items:center;justify-content:center;border-radius:14px;background:rgba(20,38,59,.06)}.pbi-tab strong{display:block;font-size:.9rem;font-weight:800}.pbi-tab span{display:block;margin-top:.14rem;color:var(--muted);font-size:.76rem;line-height:1.4}
.pbi-grid{display:grid;gap:1rem}.pbi-grid.two{grid-template-columns:1.06fr .94fr}.pbi-panel{padding:1rem;border-radius:20px}.pbi-head{display:flex;align-items:center;justify-content:space-between;gap:.85rem;margin-bottom:.7rem}.pbi-head h3{margin:0;font-size:.98rem;font-weight:800}.pbi-chip,.pbi-pill,.pbi-meta span,.pbi-ref{display:inline-flex;align-items:center;gap:.3rem;padding:.24rem .52rem;border-radius:999px;font-size:.7rem;font-weight:700;line-height:1.2}.pbi-chip{background:rgba(20,38,59,.08);color:var(--ink)}.pbi-copy{margin:0 0 .85rem;color:var(--muted);line-height:1.6;font-size:.84rem}
.pbi-bars,.pbi-cards,.pbi-list,.pbi-board,.pbi-scope-grid,.pbi-domain-grid{display:grid;gap:.65rem}.pbi-bar{display:grid;grid-template-columns:minmax(0,1.25fr) minmax(180px,1fr) auto;gap:.68rem;align-items:center;padding:.62rem .72rem;border-radius:16px;border:1px solid transparent;background:rgba(255,255,255,.55);text-align:left;cursor:pointer;transition:border-color .15s ease,transform .15s ease,box-shadow .15s ease}.pbi-bar:hover,.pbi-bar.active,.pbi-card:hover,.pbi-card.active,.pbi-domain:hover,.pbi-domain.active,.pbi-mini:hover,.pbi-refcard:hover{transform:translateY(-1px);border-color:rgba(20,78,114,.22);box-shadow:0 12px 22px rgba(20,78,114,.08)}.pbi-bar strong,.pbi-card strong,.pbi-domain strong,.pbi-mini strong,.pbi-refcard strong{display:block;font-size:.86rem}.pbi-bar em,.pbi-card p,.pbi-domain span,.pbi-mini span,.pbi-refcard p{display:block;margin-top:.12rem;color:var(--muted);font-size:.74rem;font-style:normal;line-height:1.45}.pbi-track{height:.44rem;border-radius:999px;background:rgba(20,38,59,.08);overflow:hidden}.pbi-track b{display:block;height:100%;border-radius:inherit}.pbi-num{min-width:2.2rem;text-align:right;font-weight:800}
.pbi-cards{grid-template-columns:repeat(3,1fr)}.pbi-card{width:100%;padding:.76rem .8rem;border-radius:16px;border:1px solid var(--line);background:rgba(255,255,255,.75);text-align:left;cursor:pointer}.pbi-card-top{display:flex;align-items:center;justify-content:space-between;gap:.55rem;color:var(--muted);font-size:.64rem;text-transform:uppercase;letter-spacing:.06em;font-weight:700}.pbi-icon{width:1.9rem;height:1.9rem;display:inline-flex;align-items:center;justify-content:center;border-radius:12px;background:rgba(20,78,114,.12);color:var(--accent);font-size:.8rem}
.pbi-mini-row{display:grid;grid-template-columns:repeat(2,1fr);gap:.65rem}.pbi-mini{width:100%;padding:.78rem .82rem;border-radius:16px;background:rgba(255,255,255,.76);text-align:left;cursor:pointer}.pbi-mini label{color:var(--muted)}.pbi-mini strong{margin-top:.25rem;color:#144e72;font-size:1rem}.pbi-mini span{margin-top:.2rem}
.pbi-scope-grid{grid-template-columns:repeat(2,1fr)}.pbi-scope{padding:.78rem .82rem;border-radius:16px;border:1px solid var(--line);background:rgba(255,255,255,.78)}.pbi-scope strong{display:block;font-size:.86rem;font-weight:800}.pbi-scope span{display:block;margin-top:.14rem;color:var(--muted);font-size:.74rem;line-height:1.45}
.pbi-workspace{display:grid;grid-template-columns:.78fr 1.04fr .9fr;gap:1rem}.pbi-refspace{display:grid;grid-template-columns:.82fr 1.18fr;gap:1rem}.pbi-domain-grid{grid-template-columns:repeat(2,1fr);margin-bottom:.8rem}.pbi-domain{width:100%;padding:.62rem .68rem;border-radius:16px;border:1px solid var(--line);background:rgba(255,255,255,.75);text-align:left;cursor:pointer}.pbi-domain strong{font-size:.82rem;font-weight:800;color:var(--accent)}.pbi-domain span{font-size:.74rem}
.pbi-form{display:grid;gap:.75rem}.pbi-form label{margin-bottom:.3rem;color:var(--muted)}.pbi-summary{margin-top:.85rem;padding:.82rem .88rem;border-radius:18px;background:linear-gradient(180deg,rgba(25,61,87,.95) 0%,rgba(20,78,114,.95) 100%);color:#fffaf2}.pbi-summary small{display:block;color:rgba(255,250,242,.7);font-size:.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.08em}.pbi-summary strong{display:block;margin-top:.28rem;font-size:1.72rem;font-weight:800;line-height:1}.pbi-summary span{display:block;margin-top:.42rem;color:rgba(255,250,242,.78);font-size:.78rem;line-height:1.5}
.pbi-list{display:grid;gap:.55rem;max-height:720px;overflow-y:auto;padding-right:.12rem}.pbi-item{position:relative;width:100%;padding:.7rem .78rem .66rem .88rem;border-radius:14px;border:1px solid rgba(20,38,59,.08);background:#fff;text-align:left;cursor:pointer}.pbi-item.active{border-color:rgba(20,78,114,.22);box-shadow:0 10px 20px rgba(20,78,114,.05);background:rgba(255,255,255,.96)}.pbi-item::before{content:'';position:absolute;left:0;top:.68rem;bottom:.68rem;width:.18rem;border-radius:999px;background:var(--accent,#144e72)}.pbi-item-top{display:flex;flex-wrap:wrap;gap:.5rem;align-items:center;justify-content:space-between}.pbi-item-code,.pbi-code{font-family:ui-monospace,SFMono-Regular,Menlo,Monaco,Consolas,"Courier New",monospace;font-size:.76rem;font-weight:800;color:var(--accent,#144e72)}.pbi-item-name{margin:.28rem 0 .2rem;font-size:.88rem;font-weight:700;line-height:1.38;color:var(--ink)}.pbi-item-meta{color:var(--muted);font-size:.74rem;line-height:1.4}.pbi-item-meta span+span::before{content:'\2022';margin:0 .4rem;color:rgba(20,38,59,.35)}
.pbi-pill{padding:.2rem .45rem;font-size:.68rem;background:rgba(20,38,59,.08);color:var(--ink)}.pbi-inspector{position:sticky;top:1rem;min-height:720px;display:flex;flex-direction:column}.pbi-inspector-head{padding-bottom:.85rem;border-bottom:1px solid var(--line)}.pbi-inspector-head strong{display:block;margin-top:.35rem;font-size:1rem;font-weight:800;color:#144e72}.pbi-inspector-head span{display:block;margin-top:.28rem;font-size:.9rem;font-weight:800;line-height:1.4}.pbi-inspector-body{display:grid;gap:.75rem;padding-top:.85rem;flex:1;min-height:0;overflow:auto;align-content:start}.pbi-meta{display:flex;flex-wrap:wrap;gap:.45rem}.pbi-meta span{background:rgba(20,38,59,.06);color:var(--ink);font-size:.72rem}.pbi-callout,.pbi-note{padding:.76rem .84rem;border-radius:16px;border:1px solid var(--line);line-height:1.62}.pbi-callout{background:rgba(255,255,255,.75)}.pbi-note{background:rgba(238,245,245,.84)}.pbi-plain{margin:.15rem 0 0;padding-left:1rem;color:var(--muted);font-size:.78rem;line-height:1.6}.pbi-plain li{margin-bottom:.16rem}.pbi-empty{padding:.9rem;border-radius:16px;border:1px dashed rgba(20,38,59,.18);background:rgba(255,255,255,.6);color:var(--muted);text-align:center;line-height:1.55}
.pbi-board{grid-template-columns:repeat(2,1fr)}.pbi-refcard{position:relative;padding:.88rem .92rem;border-radius:18px;background:rgba(255,255,255,.88);border-color:rgba(20,38,59,.08)}.pbi-refcard::before{content:'';position:absolute;left:0;top:.82rem;bottom:.82rem;width:.2rem;border-radius:999px;background:var(--accent,#144e72)}.pbi-ref-top{display:flex;flex-wrap:wrap;align-items:center;justify-content:space-between;gap:.5rem;margin-bottom:.45rem}.pbi-ref-top .pbi-code{font-size:.72rem}.pbi-ref-meta{display:flex;flex-wrap:wrap;gap:.4rem;margin:.55rem 0}.pbi-ref-meta span{display:inline-flex;align-items:center;gap:.3rem;padding:.22rem .5rem;border-radius:999px;background:rgba(20,38,59,.06);font-size:.7rem;font-weight:700;color:var(--ink)}.pbi-refs{display:flex;flex-wrap:wrap;gap:.4rem;margin-top:.6rem}.pbi-ref{border:1px solid rgba(20,38,59,.12);background:rgba(255,255,255,.82);color:var(--ink);font-size:.7rem;cursor:pointer}
@media (max-width:1399.98px){.pbi-workspace,.pbi-refspace{grid-template-columns:1fr}.pbi-inspector{position:static;min-height:auto}}@media (max-width:1199.98px){.pbi-hero,.pbi-metric,.pbi-side{min-height:auto}.pbi-hero,.pbi-nav,.pbi-grid.two,.pbi-metrics,.pbi-cards,.pbi-scope-grid,.pbi-mini-row,.pbi-board{grid-template-columns:1fr}.pbi-bar{grid-template-columns:1fr}}@media (max-width:767.98px){.pbi-hero,.pbi-panel{padding:1.2rem;border-radius:22px}.pbi-domain-grid{grid-template-columns:1fr}}
</style>
@endpush

@section('content')
@php
    $reqCollection = collect($requirements);
    $referenceCollection = collect($references);
    $totalChapters = $reqCollection->pluck('chapter')->filter()->unique()->count();
    $totalRequirements = $reqCollection->count();
    $totalReferences = $referenceCollection->count();
    $incidentClock = '1j/3h';

    $domainMeta = [
        'Kerangka' => ['label' => 'Kerangka', 'color' => '#2563eb', 'icon' => 'fa-landmark', 'summary' => 'Sasaran, prinsip dasar, objek pengaturan, dan ruang lingkup KKS.'],
        'Tata Kelola' => ['label' => 'Tata Kelola', 'color' => '#0f766e', 'icon' => 'fa-sitemap', 'summary' => 'Strategi, kebijakan, fungsi organisasi, audit, dan budaya KKS.'],
        'Pencegahan' => ['label' => 'Pencegahan', 'color' => '#a16207', 'icon' => 'fa-shield-alt', 'summary' => 'Profil risiko, proteksi, deteksi, dan pengujian kapabilitas preventif.'],
        'Penanganan' => ['label' => 'Penanganan', 'color' => '#b91c1c', 'icon' => 'fa-bolt', 'summary' => 'Respons, pelaporan insiden, pemulihan, dan komunikasi regulator.'],
        'Pengawasan' => ['label' => 'Pengawasan', 'color' => '#7c3aed', 'icon' => 'fa-binoculars', 'summary' => 'Pengawasan langsung/tidak langsung serta tindak lanjut regulator.'],
        'Kolaborasi' => ['label' => 'Kolaborasi', 'color' => '#1d4ed8', 'icon' => 'fa-handshake', 'summary' => 'Pertukaran informasi, koordinasi lintas otoritas, dan peran SRO.'],
        'Penerapan' => ['label' => 'Penerapan', 'color' => '#144e72', 'icon' => 'fa-layer-group', 'summary' => 'Penerapan KKS sesuai klasifikasi penyelenggara.'],
    ];

    $chapterMeta = [
        'II' => ['label' => 'Kerangka KKS', 'color' => '#2563eb', 'icon' => 'fa-landmark', 'summary' => 'Sasaran, prinsip dasar, objek pengaturan, dan ruang lingkup KKS.'],
        'III' => ['label' => 'Tata Kelola', 'color' => '#0f766e', 'icon' => 'fa-sitemap', 'summary' => 'Strategi, fungsi organisasi, audit, dan budaya KKS.'],
        'IV' => ['label' => 'Pencegahan', 'color' => '#a16207', 'icon' => 'fa-shield-alt', 'summary' => 'Identifikasi, proteksi, deteksi, dan pengujian preventif.'],
        'V' => ['label' => 'Penanganan', 'color' => '#b91c1c', 'icon' => 'fa-bolt', 'summary' => 'Respons, pemulihan, dan komunikasi insiden siber.'],
        'VI' => ['label' => 'Pengawasan', 'color' => '#7c3aed', 'icon' => 'fa-binoculars', 'summary' => 'Mekanisme pengawasan dan penyampaian data/informasi.'],
        'VII' => ['label' => 'Kolaborasi', 'color' => '#1d4ed8', 'icon' => 'fa-handshake', 'summary' => 'Pertukaran informasi, koordinasi, dan kerja sama dengan SRO.'],
        'VIII' => ['label' => 'Penerapan', 'color' => '#144e72', 'icon' => 'fa-layer-group', 'summary' => 'Penerapan KKS sesuai klasifikasi penyelenggara.'],
    ];

    $referenceTypeMeta = [
        'Pelaporan Insiden' => ['label' => 'Pelaporan Insiden', 'color' => '#b91c1c', 'summary' => 'Tenggat regulator saat insiden siber terjadi.'],
        'Pelaporan Berkala' => ['label' => 'Pelaporan Berkala', 'color' => '#2563eb', 'summary' => 'Pelaporan tahunan tingkat kematangan dan infrastruktur vital.'],
        'Pengawasan' => ['label' => 'Pengawasan', 'color' => '#7c3aed', 'summary' => 'Kesiapan bukti, pengawasan, dan tindak lanjut regulator.'],
        'Kolaborasi' => ['label' => 'Kolaborasi', 'color' => '#0f766e', 'summary' => 'Pertukaran informasi dan koordinasi lintas pihak.'],
        'Penerapan' => ['label' => 'Penerapan', 'color' => '#144e72', 'summary' => 'Rujukan teknis dan penerapan berbasis klasifikasi.'],
    ];

    $subjectScopes = [
        ['code' => 'PJP', 'name' => 'Penyedia Jasa Pembayaran'],
        ['code' => 'PIP', 'name' => 'Penyelenggara Infrastruktur Sistem Pembayaran'],
        ['code' => 'PUSK PUVA', 'name' => 'Pelaku usaha pasar uang dan pasar valuta asing'],
        ['code' => 'LPPU', 'name' => 'Lembaga Pendukung Pasar Uang'],
        ['code' => 'LPPVA', 'name' => 'Lembaga Pendukung Pasar Valuta Asing'],
        ['code' => 'KUPVA BB', 'name' => 'Penyelenggara kegiatan usaha penukaran valuta asing bukan bank'],
        ['code' => 'Lainnya', 'name' => 'Pihak lain yang diatur dan diawasi Bank Indonesia'],
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

    $referenceTypeBreakdown = collect($referenceTypeMeta)->map(function ($meta, $type) use ($referenceCollection) {
        return [
            'type' => $type,
            'label' => $meta['label'],
            'summary' => $meta['summary'],
            'color' => $meta['color'],
            'count' => $referenceCollection->where('type', $type)->count(),
        ];
    })->filter(fn ($item) => $item['count'] > 0)->values();

    $reportingHighlights = $referenceCollection->whereIn('id', ['REF-01', 'REF-02', 'REF-03', 'REF-05'])->values();
    $maxChapterCount = max($chapterBreakdown->max('count') ?: 0, 1);
    $chapterOptions = $chapterBreakdown->pluck('key')->values();
    $firstRequirement = $reqCollection->first();
    $firstDomain = $firstRequirement ? ($domainMeta[$firstRequirement['domain']] ?? null) : null;
@endphp

<div class="pbi-page">
    <div class="pbi-shell">
        <section class="pbi-hero">
            <div>
                <span class="pbi-kicker"><i class="fas fa-landmark"></i>Framework Studio</span>
                <h1 class="pbi-title">PBI 02/2024 KKS Navigator</h1>
                <p class="pbi-lede">
                    Halaman ini menyajikan ringkasan Peraturan Bank Indonesia Nomor 2 Tahun 2024 mengenai keamanan
                    sistem informasi dan ketahanan siber bagi penyelenggara yang diatur dan diawasi Bank Indonesia,
                    termasuk peta kewajiban inti, analisa implementasi, serta rujukan pelaporan dan pengawasan.
                </p>

                <div class="pbi-metrics">
                    <div class="pbi-metric">
                        <label>Bab Substantif</label>
                        <strong>{{ $totalChapters }}</strong>
                        <span>Mulai dari kerangka KKS sampai penerapan berdasarkan klasifikasi penyelenggara.</span>
                    </div>
                    <div class="pbi-metric">
                        <label>Kewajiban Inti</label>
                        <strong>{{ $totalRequirements }}</strong>
                        <span>Requirement operasional yang dapat dianalisa satu per satu dalam explorer.</span>
                    </div>
                    <div class="pbi-metric">
                        <label>Rujukan Operasional</label>
                        <strong>{{ $totalReferences }}</strong>
                        <span>Board pelaporan, pengawasan, kolaborasi, dan penerapan yang perlu disiapkan.</span>
                    </div>
                    <div class="pbi-metric">
                        <label>Tenggat Insiden</label>
                        <strong>{{ $incidentClock }}</strong>
                        <span>Notifikasi awal 1 jam dan laporan insiden 3 hari kalender ke Bank Indonesia.</span>
                    </div>
                </div>
            </div>

            <div class="pbi-side-stack">
                <div class="pbi-side">
                    <label>Alur Analisa</label>
                    <h3>Pendekatan analisa regulasi yang terstruktur</h3>
                    <p>
                        Analisa dapat diawali dari peta bab dan domain kewajiban untuk memahami struktur regulasi,
                        dilanjutkan ke explorer kewajiban untuk meninjau requirement inti secara rinci, lalu ditutup
                        dengan board pelaporan dan pengawasan untuk menyiapkan artefak regulator yang relevan.
                    </p>
                </div>
                <div class="pbi-side">
                    <label>Catatan Pengawasan</label>
                    <p>
                        PBI ini menekankan pelaporan insiden yang sangat cepat, pelaporan tahunan atas maturitas KKS
                        dan infrastruktur informasi vital, serta kesiapan organisasi menghadapi pengawasan berbasis
                        risiko dan/atau kepatuhan oleh Bank Indonesia.
                    </p>
                </div>
            </div>
        </section>

        <div class="pbi-nav nav" role="tablist">
            <button class="pbi-tab active" data-bs-toggle="tab" data-bs-target="#pbi-overview-pane" type="button" role="tab">
                <i class="fas fa-chart-line"></i>
                <span>
                    <strong>Ringkasan Regulasi</strong>
                    <span>Struktur bab, domain kewajiban, dan sorotan operasional utama.</span>
                </span>
            </button>
            <button class="pbi-tab" data-bs-toggle="tab" data-bs-target="#pbi-explorer-pane" type="button" role="tab">
                <i class="fas fa-sliders-h"></i>
                <span>
                    <strong>Explorer Kewajiban</strong>
                    <span>Filter requirement, baca interpretasi, dan telusuri kebutuhan bukti.</span>
                </span>
            </button>
            <button class="pbi-tab" data-bs-toggle="tab" data-bs-target="#pbi-reference-pane" type="button" role="tab">
                <i class="fas fa-folder-open"></i>
                <span>
                    <strong>Pelaporan & Pengawasan</strong>
                    <span>Board tenggat, rujukan regulator, dan pengaitan ke kewajiban inti.</span>
                </span>
            </button>
        </div>

        <div class="tab-content pbi-grid">
            <div class="tab-pane fade show active" id="pbi-overview-pane" role="tabpanel">
                <div class="pbi-grid two">
                    <article class="pbi-panel">
                        <div class="pbi-head">
                            <h3>Peta bab pengaturan</h3>
                            <span class="pbi-chip">{{ $totalChapters }} bab</span>
                        </div>
                        <p class="pbi-copy">
                            Pembacaan dapat dimulai dari struktur bab berikut untuk memahami urutan pengaturan mulai
                            dari fondasi KKS, kontrol preventif, penanganan insiden, sampai kewajiban pelaporan.
                        </p>
                        <div class="pbi-bars">
                            @foreach ($chapterBreakdown as $item)
                                <button type="button" class="pbi-bar" data-explorer-chapter="{{ $item['key'] }}">
                                    <span>
                                        <strong>{{ $item['key'] }} - {{ $item['label'] }}</strong>
                                        <em>{{ $item['summary'] }}</em>
                                    </span>
                                    <span class="pbi-track">
                                        <b style="width: {{ round(($item['count'] / $maxChapterCount) * 100, 1) }}%; background: {{ $item['color'] }}"></b>
                                    </span>
                                    <span class="pbi-num">{{ $item['count'] }}</span>
                                </button>
                            @endforeach
                        </div>
                    </article>

                    <article class="pbi-panel">
                        <div class="pbi-head">
                            <h3>Domain kewajiban inti</h3>
                            <span class="pbi-chip">{{ $domainBreakdown->count() }} domain</span>
                        </div>
                        <p class="pbi-copy">
                            Domain berikut membantu memetakan requirement utama yang perlu dilihat sebagai satu rangkaian
                            penguatan KKS, bukan sebagai kontrol yang berdiri sendiri.
                        </p>
                        <div class="pbi-cards">
                            @foreach ($domainBreakdown as $item)
                                <button type="button" class="pbi-card" data-explorer-domain="{{ $item['key'] }}">
                                    <div class="pbi-card-top">
                                        <span class="pbi-icon" style="--accent: {{ $item['color'] }}; color: {{ $item['color'] }}">
                                            <i class="fas {{ $item['icon'] }}"></i>
                                        </span>
                                        <span>{{ $item['count'] }} entri</span>
                                    </div>
                                    <strong>{{ $item['label'] }}</strong>
                                    <p>{{ $item['summary'] }}</p>
                                </button>
                            @endforeach
                        </div>
                    </article>
                </div>

                <div class="pbi-grid two">
                    <article class="pbi-panel">
                        <div class="pbi-head">
                            <h3>Cakupan subjek pengaturan</h3>
                            <span class="pbi-chip">{{ count($subjectScopes) }} kelompok</span>
                        </div>
                        <p class="pbi-copy">
                            PBI 02/2024 berlaku pada kelompok penyelenggara berikut sebagai objek pengaturan dan
                            pengawasan KKS Bank Indonesia.
                        </p>
                        <div class="pbi-scope-grid">
                            @foreach ($subjectScopes as $scope)
                                <div class="pbi-scope">
                                    <strong>{{ $scope['code'] }}</strong>
                                    <span>{{ $scope['name'] }}</span>
                                </div>
                            @endforeach
                        </div>
                    </article>

                    <article class="pbi-panel">
                        <div class="pbi-head">
                            <h3>Sorotan pelaporan dan pengawasan</h3>
                            <span class="pbi-chip">Prioritas awal</span>
                        </div>
                        <p class="pbi-copy">
                            Area berikut biasanya menjadi perhatian awal dalam penerapan PBI karena langsung terkait
                            dengan respons insiden, kewajiban pelaporan, dan kesiapan saat pengawasan berlangsung.
                        </p>
                        <div class="pbi-mini-row">
                            @foreach ($reportingHighlights as $item)
                                <button type="button" class="pbi-mini" data-reference-jump="{{ $item['id'] }}">
                                    <label>{{ $item['article'] }}</label>
                                    <strong>{{ $item['title'] }}</strong>
                                    <span>{{ $item['timeline'] }}</span>
                                </button>
                            @endforeach
                        </div>
                        <div class="pbi-note">
                            <strong>Catatan kepatuhan</strong>
                            <div class="mt-2">
                                Pasal 55 menegaskan bahwa ketidakpatuhan atas kewajiban laporan dapat berujung pada
                                teguran, kewajiban membayar, penghentian kegiatan, hingga pencabutan izin dan/atau
                                persetujuan yang telah diberikan.
                            </div>
                        </div>
                    </article>
                </div>
            </div>

            <div class="tab-pane fade" id="pbi-explorer-pane" role="tabpanel">
                <div class="pbi-workspace">
                    <article class="pbi-panel">
                        <div class="pbi-head">
                            <h3>Filter Kewajiban</h3>
                            <span class="pbi-chip">{{ $totalRequirements }} entri</span>
                        </div>

                        <div class="pbi-domain-grid">
                            @foreach ($domainBreakdown as $item)
                                <button type="button" class="pbi-domain" data-domain-card="{{ $item['key'] }}" style="--accent: {{ $item['color'] }}">
                                    <strong>{{ $item['label'] }}</strong>
                                    <span>{{ $item['count'] }} kewajiban</span>
                                </button>
                            @endforeach
                        </div>

                        <div class="pbi-form">
                            <div>
                                <label for="chapterFilter">Bab Regulasi</label>
                                <select id="chapterFilter" class="form-select">
                                    <option value="">Semua bab</option>
                                    @foreach ($chapterOptions as $chapter)
                                        <option value="{{ $chapter }}">{{ $chapter }} - {{ $chapterMeta[$chapter]['label'] }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label for="requirementSearch">Cari kewajiban</label>
                                <input id="requirementSearch" type="search" class="form-control" placeholder="Cari ID, pasal, judul, ringkasan, fokus, atau bukti">
                            </div>
                            <button type="button" class="btn btn-outline-secondary" id="resetRequirementFilters">Atur ulang filter</button>
                        </div>

                        <div class="pbi-summary">
                            <small>Kewajiban Ditampilkan</small>
                            <strong id="requirementVisibleCount">{{ $totalRequirements }}</strong>
                            <span id="requirementVisibleSummary">Menampilkan seluruh requirement PBI 02/2024 yang dipetakan pada halaman ini.</span>
                        </div>
                    </article>

                    <article class="pbi-panel">
                        <div class="pbi-head">
                            <h3>Daftar kewajiban</h3>
                            <span class="pbi-chip" id="requirementCountChip">{{ $totalRequirements }} entri</span>
                        </div>
                        <div id="requirementList" class="pbi-list"></div>
                    </article>

                    <article class="pbi-panel pbi-inspector">
                        <div class="pbi-inspector-head">
                            <small>Requirement Inspector</small>
                            <strong id="requirement-id">{{ $firstRequirement['id'] ?? '-' }}</strong>
                            <span id="requirement-title">{{ $firstRequirement['title'] ?? 'Pilih kewajiban untuk membaca detail.' }}</span>
                        </div>
                        <div class="pbi-inspector-body">
                            <div class="pbi-meta">
                                <span id="requirement-article">{{ $firstRequirement['article'] ?? '-' }}</span>
                                <span id="requirement-chapter">{{ $firstRequirement['chapter'] ?? '-' }}</span>
                                <span id="requirement-domain">{{ $firstDomain['label'] ?? ($firstRequirement['domain'] ?? '-') }}</span>
                                <span id="requirement-evidence-count">{{ $firstRequirement['evidence_count'] ?? count($firstRequirement['evidence'] ?? []) }} bukti</span>
                            </div>
                            <div class="pbi-callout">
                                <strong>Ringkasan Kewajiban</strong>
                                <div id="requirement-summary" class="mt-2">{{ $firstRequirement['summary'] ?? 'Pilih requirement untuk membaca ringkasan.' }}</div>
                            </div>
                            <div class="pbi-note">
                                <strong>Interpretasi Implementasi</strong>
                                <div id="requirement-interpretation" class="mt-2">{{ $firstRequirement['interpretation'] ?? '-' }}</div>
                            </div>
                            <div class="pbi-callout">
                                <strong>Fokus Pelaksanaan</strong>
                                <ul id="requirement-focus" class="pbi-plain">
                                    @forelse (($firstRequirement['focus'] ?? []) as $item)
                                        <li>{{ $item }}</li>
                                    @empty
                                        <li>Tidak ada fokus tambahan yang dipetakan.</li>
                                    @endforelse
                                </ul>
                            </div>
                            <div class="pbi-callout">
                                <strong>Contoh Bukti</strong>
                                <ul id="requirement-evidence" class="pbi-plain">
                                    @forelse (($firstRequirement['evidence'] ?? []) as $item)
                                        <li>{{ $item }}</li>
                                    @empty
                                        <li>Tidak ada contoh bukti yang dipetakan.</li>
                                    @endforelse
                                </ul>
                            </div>
                            <div class="pbi-note">
                                <strong>Catatan Pelaporan/Pengawasan</strong>
                                <div id="requirement-reporting" class="mt-2">{{ $firstRequirement['reporting'] ?? 'Requirement ini tidak memiliki catatan pelaporan khusus, namun tetap perlu didukung bukti implementasi yang siap ditelusuri saat pengawasan.' }}</div>
                            </div>
                        </div>
                    </article>
                </div>
            </div>

            <div class="tab-pane fade" id="pbi-reference-pane" role="tabpanel">
                <div class="pbi-refspace">
                    <article class="pbi-panel">
                        <div class="pbi-head">
                            <h3>Filter Board Rujukan</h3>
                            <span class="pbi-chip">{{ $totalReferences }} entri</span>
                        </div>
                        <div class="pbi-bars">
                            @foreach ($referenceTypeBreakdown as $item)
                                <button type="button" class="pbi-bar" data-type-card="{{ $item['type'] }}">
                                    <span>
                                        <strong>{{ $item['label'] }}</strong>
                                        <em>{{ $item['summary'] }}</em>
                                    </span>
                                    <span class="pbi-track"><b style="width: 100%; background: {{ $item['color'] }}"></b></span>
                                    <span class="pbi-num">{{ $item['count'] }}</span>
                                </button>
                            @endforeach
                        </div>

                        <div class="pbi-form mt-3">
                            <div>
                                <label for="referenceTypeFilter">Jenis Rujukan</label>
                                <select id="referenceTypeFilter" class="form-select">
                                    <option value="">Semua jenis</option>
                                    @foreach ($referenceTypeBreakdown as $item)
                                        <option value="{{ $item['type'] }}">{{ $item['label'] }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label for="referenceSearch">Cari rujukan</label>
                                <input id="referenceSearch" type="search" class="form-control" placeholder="Cari ID, pasal, judul, ringkasan, atau deliverable">
                            </div>
                            <button type="button" class="btn btn-outline-secondary" id="resetReferenceFilters">Atur ulang filter</button>
                        </div>

                        <div class="pbi-summary">
                            <small>Rujukan Ditampilkan</small>
                            <strong id="referenceVisibleCount">{{ $totalReferences }}</strong>
                            <span id="referenceVisibleSummary">Menampilkan seluruh rujukan operasional PBI 02/2024 pada halaman ini.</span>
                        </div>
                    </article>

                    <article class="pbi-panel">
                        <div class="pbi-head">
                            <h3>Board pelaporan, pengawasan, dan kolaborasi</h3>
                            <span class="pbi-chip" id="referenceCountChip">{{ $totalReferences }} entri</span>
                        </div>
                        <p class="pbi-copy">
                            Board ini merangkum tenggat regulator, kebutuhan deliverable, dan rujukan kewajiban inti
                            yang perlu disiapkan saat organisasi menata kepatuhan terhadap PBI 02/2024.
                        </p>
                        <div id="referenceBoard" class="pbi-board"></div>
                    </article>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const requirements = @json($requirements);
    const references = @json($references);
    const domainMeta = @json($domainMeta);
    const referenceTypeMeta = @json($referenceTypeMeta);
    const requirementMap = Object.fromEntries(requirements.map(item => [item.id, item]));
    const firstRequirementId = @json($firstRequirement['id'] ?? null);
    const explorerTab = document.querySelector('.pbi-tab[data-bs-target="#pbi-explorer-pane"]');
    const referenceTab = document.querySelector('.pbi-tab[data-bs-target="#pbi-reference-pane"]');
    const chapterFilter = document.getElementById('chapterFilter');
    const requirementSearch = document.getElementById('requirementSearch');
    const requirementList = document.getElementById('requirementList');
    const referenceTypeFilter = document.getElementById('referenceTypeFilter');
    const referenceSearch = document.getElementById('referenceSearch');
    const referenceBoard = document.getElementById('referenceBoard');
    let activeDomain = '';
    let activeRequirementId = firstRequirementId;

    const showTab = trigger => trigger && bootstrap.Tab.getOrCreateInstance(trigger).show();
    const escapeHtml = value => String(value ?? '').replace(/[&<>"']/g, char => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[char]));

    function syncTabState(activeBtn) {
        document.querySelectorAll('.pbi-tab').forEach(tab => tab.classList.remove('active'));
        if (activeBtn) activeBtn.classList.add('active');
    }

    function syncDomainState() {
        document.querySelectorAll('[data-domain-card]').forEach(button => {
            button.classList.toggle('active', button.dataset.domainCard === activeDomain && activeDomain !== '');
        });
    }

    function syncReferenceTypeState() {
        document.querySelectorAll('[data-type-card]').forEach(button => {
            button.classList.toggle('active', button.dataset.typeCard === referenceTypeFilter.value && referenceTypeFilter.value !== '');
        });
    }

    function renderRequirementInspector(requirement) {
        if (!requirement) {
            document.getElementById('requirement-id').textContent = '-';
            document.getElementById('requirement-title').textContent = 'Tidak ada kewajiban yang sesuai dengan filter aktif.';
            document.getElementById('requirement-article').textContent = '-';
            document.getElementById('requirement-chapter').textContent = '-';
            document.getElementById('requirement-domain').textContent = '-';
            document.getElementById('requirement-evidence-count').textContent = '0 bukti';
            document.getElementById('requirement-summary').textContent = 'Sesuaikan filter untuk melihat requirement yang tersedia.';
            document.getElementById('requirement-interpretation').textContent = '-';
            document.getElementById('requirement-reporting').textContent = 'Tidak ada catatan tambahan.';
            document.getElementById('requirement-focus').innerHTML = '<li>Tidak ada requirement yang cocok.</li>';
            document.getElementById('requirement-evidence').innerHTML = '<li>Tidak ada bukti yang dipetakan.</li>';
            return;
        }

        const domain = domainMeta[requirement.domain] || { label: requirement.domain || '-' };
        document.getElementById('requirement-id').textContent = requirement.id || '-';
        document.getElementById('requirement-title').textContent = requirement.title || '-';
        document.getElementById('requirement-article').textContent = requirement.article || '-';
        document.getElementById('requirement-chapter').textContent = requirement.chapter || '-';
        document.getElementById('requirement-domain').textContent = domain.label || '-';
        document.getElementById('requirement-evidence-count').textContent = `${requirement.evidence_count || (requirement.evidence || []).length} bukti`;
        document.getElementById('requirement-summary').textContent = requirement.summary || '-';
        document.getElementById('requirement-interpretation').textContent = requirement.interpretation || '-';
        document.getElementById('requirement-reporting').textContent = requirement.reporting || 'Requirement ini tidak memiliki catatan pelaporan khusus, namun tetap perlu didukung bukti implementasi yang siap ditelusuri saat pengawasan.';

        const focus = document.getElementById('requirement-focus');
        focus.innerHTML = '';
        (requirement.focus && requirement.focus.length ? requirement.focus : ['Tidak ada fokus tambahan yang dipetakan.']).forEach(item => {
            const li = document.createElement('li');
            li.textContent = item;
            focus.appendChild(li);
        });

        const evidence = document.getElementById('requirement-evidence');
        evidence.innerHTML = '';
        (requirement.evidence && requirement.evidence.length ? requirement.evidence : ['Tidak ada contoh bukti yang dipetakan.']).forEach(item => {
            const li = document.createElement('li');
            li.textContent = item;
            evidence.appendChild(li);
        });
    }

    function buildRequirementItem(requirement, selected) {
        const domain = domainMeta[requirement.domain] || { label: requirement.domain || '-', color: '#144e72' };
        return `<button type="button" class="pbi-item${selected ? ' active' : ''}" data-requirement-id="${escapeHtml(requirement.id)}" style="--accent:${domain.color}"><div class="pbi-item-top"><span class="pbi-item-code">${escapeHtml(requirement.id)}</span><span class="pbi-pill">${escapeHtml(domain.label)}</span></div><div class="pbi-item-name">${escapeHtml(requirement.title || '-')}</div><div class="pbi-item-meta"><span>${escapeHtml(requirement.article || '-')}</span><span>Bab ${escapeHtml(requirement.chapter || '-')}</span><span>${escapeHtml(requirement.focus_count || (requirement.focus || []).length)} fokus</span></div></button>`;
    }

    function renderRequirementList(list) {
        if (!list.length) {
            requirementList.innerHTML = '<div class="pbi-empty">Tidak ada kewajiban yang cocok dengan filter saat ini.</div>';
            return;
        }

        requirementList.innerHTML = list.map(item => buildRequirementItem(item, item.id === activeRequirementId)).join('');
        requirementList.querySelectorAll('.pbi-item').forEach(node => {
            node.addEventListener('click', function () {
                const id = node.dataset.requirementId;
                if (!id) return;
                setActiveRequirement(id);
                renderRequirementList(list);
            });
        });
    }

    function setActiveRequirement(id) {
        activeRequirementId = id;
        renderRequirementInspector(requirementMap[id] || null);
        document.querySelectorAll('.pbi-item').forEach(item => item.classList.toggle('active', item.dataset.requirementId === id));
    }

    function updateRequirementSummary(count) {
        document.getElementById('requirementVisibleCount').textContent = count;
        document.getElementById('requirementCountChip').textContent = `${count} entri`;
        document.getElementById('requirementVisibleSummary').textContent = count
            ? `Menampilkan ${count} requirement PBI 02/2024 sesuai filter aktif.`
            : 'Tidak ada requirement yang cocok dengan filter saat ini.';
        syncDomainState();
    }

    function filteredRequirements() {
        const query = (requirementSearch.value || '').trim().toLowerCase();
        return requirements.filter(requirement => {
            if (activeDomain && requirement.domain !== activeDomain) return false;
            if (chapterFilter.value && requirement.chapter !== chapterFilter.value) return false;
            if (!query) return true;
            return [
                requirement.id,
                requirement.title,
                requirement.article,
                requirement.chapter,
                requirement.domain,
                requirement.summary,
                requirement.interpretation,
                ...(requirement.focus || []),
                ...(requirement.evidence || [])
            ].join(' ').toLowerCase().includes(query);
        });
    }

    function applyRequirementFilters(preferredId = null) {
        const list = filteredRequirements();
        updateRequirementSummary(list.length);

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

    function buildReferenceCard(reference) {
        const type = referenceTypeMeta[reference.type] || { label: reference.type || '-', color: '#144e72' };
        const deliverables = (reference.deliverables && reference.deliverables.length ? reference.deliverables : ['Tidak ada deliverable tambahan.'])
            .slice(0, 3)
            .map(item => `<li>${escapeHtml(item)}</li>`)
            .join('');
        const links = (reference.linked_requirements && reference.linked_requirements.length
            ? reference.linked_requirements.map(item => `<button type="button" class="pbi-ref" data-linked-requirement-id="${escapeHtml(item)}">${escapeHtml(item)}</button>`).join('')
            : '<span class="pbi-empty w-100">Belum ada requirement yang dipetakan.</span>');

        return `<article class="pbi-refcard" style="--accent:${type.color}"><div class="pbi-ref-top"><span class="pbi-pill">${escapeHtml(type.label)}</span><span class="pbi-code">${escapeHtml(reference.article || '-')}</span></div><strong>${escapeHtml(reference.title || '-')}</strong><p>${escapeHtml(reference.summary || '-')}</p><div class="pbi-ref-meta"><span>${escapeHtml(reference.timeline || '-')}</span><span>${escapeHtml(reference.owner || '-')}</span></div><ul class="pbi-plain">${deliverables}</ul><div class="pbi-refs">${links}</div></article>`;
    }

    function renderReferenceBoard(list) {
        if (!list.length) {
            referenceBoard.innerHTML = '<div class="pbi-empty">Tidak ada rujukan yang cocok dengan filter saat ini.</div>';
            return;
        }

        referenceBoard.innerHTML = list.map(buildReferenceCard).join('');
    }

    function updateReferenceSummary(count) {
        document.getElementById('referenceVisibleCount').textContent = count;
        document.getElementById('referenceCountChip').textContent = `${count} entri`;
        document.getElementById('referenceVisibleSummary').textContent = count
            ? `Menampilkan ${count} rujukan operasional PBI 02/2024 sesuai filter aktif.`
            : 'Tidak ada rujukan yang cocok dengan filter saat ini.';
        syncReferenceTypeState();
    }

    function filteredReferences() {
        const query = (referenceSearch.value || '').trim().toLowerCase();
        return references.filter(reference => {
            if (referenceTypeFilter.value && reference.type !== referenceTypeFilter.value) return false;
            if (!query) return true;
            return [
                reference.id,
                reference.type,
                reference.article,
                reference.title,
                reference.summary,
                reference.timeline,
                reference.owner,
                ...(reference.deliverables || []),
                ...(reference.linked_requirements || [])
            ].join(' ').toLowerCase().includes(query);
        });
    }

    function applyReferenceFilters() {
        const list = filteredReferences();
        updateReferenceSummary(list.length);
        renderReferenceBoard(list);
    }

    function jumpExplorer(domain = '', chapter = '', requirementId = null) {
        activeDomain = domain || '';
        chapterFilter.value = chapter || '';
        requirementSearch.value = '';
        applyRequirementFilters(requirementId);
        showTab(explorerTab);
    }

    function jumpReference(type = '', search = '') {
        referenceTypeFilter.value = type || '';
        referenceSearch.value = search || '';
        applyReferenceFilters();
        showTab(referenceTab);
    }

    document.querySelectorAll('.pbi-tab').forEach(button => {
        button.addEventListener('shown.bs.tab', () => syncTabState(button));
    });

    document.querySelectorAll('[data-domain-card]').forEach(button => {
        button.addEventListener('click', function () {
            activeDomain = button.dataset.domainCard === activeDomain ? '' : button.dataset.domainCard;
            applyRequirementFilters();
        });
    });

    document.querySelectorAll('[data-explorer-domain]').forEach(button => {
        button.addEventListener('click', function () {
            jumpExplorer(button.dataset.explorerDomain || '', '', null);
        });
    });

    document.querySelectorAll('[data-explorer-chapter]').forEach(button => {
        button.addEventListener('click', function () {
            jumpExplorer('', button.dataset.explorerChapter || '', null);
        });
    });

    document.querySelectorAll('[data-type-card]').forEach(button => {
        button.addEventListener('click', function () {
            const type = button.dataset.typeCard || '';
            jumpReference(referenceTypeFilter.value === type ? '' : type, '');
        });
    });

    chapterFilter.addEventListener('change', () => applyRequirementFilters());
    requirementSearch.addEventListener('input', () => applyRequirementFilters());
    document.getElementById('resetRequirementFilters').addEventListener('click', function () {
        activeDomain = '';
        chapterFilter.value = '';
        requirementSearch.value = '';
        applyRequirementFilters(firstRequirementId);
    });

    referenceTypeFilter.addEventListener('change', () => applyReferenceFilters());
    referenceSearch.addEventListener('input', () => applyReferenceFilters());
    document.getElementById('resetReferenceFilters').addEventListener('click', function () {
        referenceTypeFilter.value = '';
        referenceSearch.value = '';
        applyReferenceFilters();
    });

    document.addEventListener('click', function (event) {
        const linkedRequirementButton = event.target.closest('[data-linked-requirement-id]');
        if (linkedRequirementButton) {
            const id = linkedRequirementButton.dataset.linkedRequirementId;
            const requirement = requirementMap[id];
            jumpExplorer(requirement?.domain || '', requirement?.chapter || '', id);
            return;
        }

        const referenceButton = event.target.closest('[data-reference-jump]');
        if (referenceButton) {
            jumpReference('', referenceButton.dataset.referenceJump || '');
        }
    });

    syncTabState(document.querySelector('.pbi-tab.active'));
    applyRequirementFilters(firstRequirementId);
    applyReferenceFilters();
});
</script>
@endpush
