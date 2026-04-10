@extends('layouts.tabler')

@section('title', 'ISO 27001:2022 Framework - Risk Management System')

@push('styles')
<style>
.iso-page {
    --ink: #132238;
    --muted: #5c6776;
    --line: rgba(19, 34, 56, 0.1);
    --soft: rgba(255, 250, 242, 0.95);
    --shell: linear-gradient(180deg, #f7f2e8 0%, #edf5f5 100%);
    color: var(--ink);
    padding: .25rem;
    border-radius: 32px;
    background: var(--shell);
}
.iso-shell { display: grid; gap: 1rem; }
.iso-hero {
    display: grid; grid-template-columns: 1.6fr .9fr; gap: 1.25rem;
    align-items: stretch; min-height: 368px;
    padding: 1.45rem; border-radius: 28px; overflow: hidden; position: relative;
    background:
        radial-gradient(circle at top right, rgba(255, 226, 189, .9), transparent 32%),
        radial-gradient(circle at bottom left, rgba(173, 223, 217, .7), transparent 28%),
        linear-gradient(135deg, #17324d 0%, #215a56 50%, #f4e4c5 100%);
    box-shadow: 0 20px 44px rgba(15, 23, 42, .09);
}
.iso-hero > * { position: relative; z-index: 1; }
.iso-kicker {
    display: inline-flex; align-items: center; gap: .45rem; padding: .35rem .7rem;
    border-radius: 999px; background: rgba(255, 250, 242, .18); color: rgba(255, 250, 242, .92);
    font-size: .75rem; font-weight: 700; text-transform: uppercase; letter-spacing: .08em;
}
.iso-title { margin: 1rem 0 .7rem; color: #fffaf2; font-size: clamp(2rem, 3.8vw, 3rem); font-weight: 800; line-height: 1.04; }
.iso-lede { margin: 0; max-width: 680px; color: rgba(255, 250, 242, .82); line-height: 1.7; }
.iso-metric-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: .65rem; margin-top: 1.15rem; }
.iso-metric {
    padding: .68rem .74rem; border-radius: 16px; background: rgba(255, 250, 242, .12);
    border: 1px solid rgba(255, 255, 255, .18); backdrop-filter: blur(6px);
    min-height: 96px; display: flex; flex-direction: column; justify-content: flex-start;
}
.iso-metric label, .iso-side-card label, .iso-panel-copy strong {
    display: block; font-size: .72rem; font-weight: 700; text-transform: uppercase; letter-spacing: .08em;
}
.iso-metric label { color: rgba(255, 250, 242, .7); margin-bottom: .35rem; }
.iso-metric strong { display: block; color: #fffaf2; font-size: 1.5rem; font-weight: 800; line-height: 1; }
.iso-metric span { display: block; margin-top: .35rem; color: rgba(255, 250, 242, .72); font-size: .76rem; }
.iso-side-stack { display: grid; gap: .95rem; }
.iso-side-card {
    padding: .8rem .86rem; border-radius: 18px; background: rgba(255, 250, 242, .78);
    border: 1px solid rgba(255, 255, 255, .24); backdrop-filter: blur(12px);
    min-height: 142px;
}
.iso-side-card label { color: var(--muted); margin-bottom: .45rem; }
.iso-side-card h3 { margin: 0; font-size: 1rem; font-weight: 800; }
.iso-side-card p { margin: .55rem 0 0; color: var(--muted); line-height: 1.55; font-size: .84rem; }
.iso-bullets { display: grid; gap: .7rem; margin-top: .95rem; }
.iso-bullets div { display: grid; grid-template-columns: auto 1fr; gap: .7rem; align-items: start; }
.iso-bullets i { color: #c2410c; margin-top: .25rem; }
.iso-nav { display: grid; grid-template-columns: repeat(3, 1fr); gap: .85rem; }
.iso-tab {
    display: grid; grid-template-columns: auto 1fr; gap: .85rem; align-items: center;
    padding: .82rem .88rem; border-radius: 18px; border: 1px solid var(--line);
    background: linear-gradient(180deg, rgba(255, 250, 242, .94) 0%, rgba(239, 245, 246, .94) 100%);
    text-align: left; color: var(--ink); box-shadow: 0 12px 24px rgba(15, 23, 42, .04); cursor: pointer;
}
.iso-tab.active { border-color: rgba(15, 118, 110, .24); box-shadow: 0 18px 30px rgba(15, 118, 110, .1); }
.iso-tab i {
    width: 2.35rem; height: 2.35rem; display: inline-flex; align-items: center; justify-content: center;
    border-radius: 14px; background: rgba(19, 34, 56, .06);
}
.iso-tab strong { display: block; font-size: .9rem; font-weight: 800; }
.iso-tab span { display: block; margin-top: .14rem; color: var(--muted); font-size: .76rem; line-height: 1.4; }
.iso-grid { display: grid; gap: 1rem; }
.iso-grid.two { grid-template-columns: 1.15fr .85fr; }
.iso-panel {
    padding: 1rem; border-radius: 20px; border: 1px solid var(--line);
    background: linear-gradient(180deg, rgba(255, 251, 245, .98) 0%, rgba(246, 251, 252, .98) 100%);
    box-shadow: 0 14px 28px rgba(15, 23, 42, .05);
}
.iso-panel-head { display: flex; align-items: center; justify-content: space-between; gap: .85rem; margin-bottom: .7rem; }
.iso-panel-head h3 { margin: 0; font-size: .98rem; font-weight: 800; }
.iso-chip, .iso-pill {
    display: inline-flex; align-items: center; gap: .3rem; padding: .24rem .52rem; border-radius: 999px;
    font-size: .7rem; font-weight: 700; line-height: 1.2;
}
.iso-chip { background: rgba(19, 34, 56, .08); color: var(--ink); }
.iso-panel-copy { margin: 0 0 .85rem; color: var(--muted); line-height: 1.62; font-size: .84rem; }
.iso-bars, .iso-priority-stack, .iso-list, .iso-concept-list { display: grid; gap: .65rem; }
.iso-bar {
    display: grid; grid-template-columns: minmax(0, 1.35fr) minmax(240px, 1fr) auto; gap: .9rem; align-items: center;
}
.iso-bar.jump {
    padding: .62rem .72rem;
    border-radius: 16px;
    border: 1px solid transparent;
    background: rgba(255, 255, 255, .5);
    cursor: pointer;
    transition: border-color .15s ease, transform .15s ease, box-shadow .15s ease;
}
.iso-bar.jump:hover {
    transform: translateY(-1px);
    border-color: rgba(15, 118, 110, .22);
    box-shadow: 0 12px 22px rgba(15, 118, 110, .08);
}
.iso-bar-copy { display: block; text-align: left; }
.iso-bar-copy strong { display: block; font-size: .88rem; text-align: left; }
.iso-bar-copy span { display: block; margin-top: .12rem; color: var(--muted); font-size: .75rem; line-height: 1.45; text-align: left; }
.iso-track { height: .5rem; border-radius: 999px; background: rgba(19, 34, 56, .08); overflow: hidden; }
.iso-track b { display: block; height: 100%; border-radius: inherit; }
.iso-number { min-width: 2.4rem; text-align: right; font-weight: 800; }
.iso-type-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: .8rem; }
.iso-concept-board { display: grid; grid-template-columns: repeat(6, minmax(0, 1fr)); gap: .65rem; }
.iso-quick-themes { display: grid; grid-template-columns: repeat(4, 1fr); gap: .55rem; }
.iso-quick-theme {
    width: 100%;
    padding: .62rem .68rem;
    border-radius: 16px;
    border: 1px solid var(--line);
    background: rgba(255, 255, 255, .72);
    text-align: left;
    cursor: pointer;
}
.iso-quick-theme strong { display: block; font-size: .82rem; font-weight: 800; color: var(--accent); }
.iso-quick-theme span { display: block; margin-top: .12rem; color: var(--muted); font-size: .74rem; white-space: nowrap; }
.iso-mini {
    padding: .78rem .82rem; border-radius: 16px; border: 1px solid var(--line); background: rgba(255, 255, 255, .72);
}
.iso-mini label { display: block; color: var(--muted); font-size: .72rem; font-weight: 700; text-transform: uppercase; letter-spacing: .08em; }
.iso-mini strong { display: block; margin-top: .26rem; color: var(--accent); font-size: 1.25rem; font-weight: 800; }
.iso-mini span, .iso-priority p, .iso-tile p { display: block; margin-top: .24rem; color: var(--muted); font-size: .75rem; line-height: 1.48; }
.iso-mini.compact { padding: .68rem .74rem; border-radius: 14px; }
.iso-mini.compact label { font-size: .68rem; }
.iso-mini.compact strong { margin-top: .18rem; font-size: 1.06rem; }
.iso-mini.compact span { margin-top: .18rem; font-size: .74rem; }
.iso-priority {
    padding: .78rem .84rem; border-radius: 16px; border: 1px solid var(--line); background: rgba(255, 255, 255, .72);
}
.iso-priority-top { display: flex; align-items: center; justify-content: space-between; gap: .8rem; }
.iso-priority-top strong { display: inline-flex; align-items: center; gap: .45rem; font-size: .9rem; }
.iso-priority-top strong::before { content: ''; width: .55rem; height: .55rem; border-radius: 999px; background: var(--accent); }
.iso-priority-bars { display: grid; gap: .55rem; margin-top: .75rem; }
.iso-priority-bar {
    display: grid;
    grid-template-columns: minmax(88px, .55fr) minmax(120px, 1fr) auto;
    gap: .65rem;
    align-items: center;
    padding: .58rem .68rem;
    border-radius: 14px;
    border: 1px solid var(--line);
    background: rgba(255, 255, 255, .72);
}
.iso-priority-name {
    display: inline-flex;
    align-items: center;
    gap: .45rem;
    font-size: .78rem;
    font-weight: 800;
}
.iso-priority-name::before {
    content: '';
    width: .55rem;
    height: .55rem;
    border-radius: 999px;
    background: var(--accent);
}
.iso-priority-track {
    height: .45rem;
    border-radius: 999px;
    background: rgba(19, 34, 56, .08);
    overflow: hidden;
}
.iso-priority-track b {
    display: block;
    height: 100%;
    border-radius: inherit;
    background: var(--accent);
}
.iso-workspace { display: grid; grid-template-columns: .78fr 1fr .95fr; gap: 1rem; }
.iso-form-grid { display: grid; gap: .85rem; }
.iso-form-grid label { display: block; margin-bottom: .35rem; color: var(--muted); font-size: .76rem; font-weight: 700; text-transform: uppercase; letter-spacing: .08em; }
.iso-summary {
    padding: .82rem .88rem; border-radius: 18px; background: linear-gradient(180deg, rgba(25, 61, 87, .95) 0%, rgba(23, 70, 67, .95) 100%);
    color: #fffaf2;
}
.iso-summary small { display: block; color: rgba(255, 250, 242, .7); font-size: .72rem; text-transform: uppercase; letter-spacing: .08em; }
.iso-summary strong { display: block; margin-top: .28rem; font-size: 1.72rem; font-weight: 800; line-height: 1; }
.iso-summary span { display: block; margin-top: .42rem; color: rgba(255, 250, 242, .78); font-size: .78rem; line-height: 1.5; }
.iso-list { max-height: 760px; overflow-y: auto; padding-right: .2rem; }
.iso-item {
    position: relative; width: 100%; padding: .7rem .8rem .68rem .88rem; border-radius: 14px; border: 1px solid rgba(19, 34, 56, .08);
    background: #fff; text-align: left; cursor: pointer;
}
.iso-item.active { border-color: rgba(15, 118, 110, .22); box-shadow: 0 10px 20px rgba(15, 118, 110, .05); background: rgba(255, 255, 255, .96); }
.iso-item::before {
    content: ''; position: absolute; left: 0; top: .68rem; bottom: .68rem; width: .18rem; border-radius: 999px; background: var(--accent);
}
.iso-item-top { display: flex; flex-wrap: wrap; gap: .52rem; align-items: center; justify-content: space-between; }
.iso-item-code {
    font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Courier New", monospace;
    font-size: .78rem; font-weight: 800; color: var(--accent);
}
.iso-item-name {
    margin: .28rem 0 .2rem;
    font-size: .88rem;
    font-weight: 700;
    line-height: 1.4;
    color: var(--ink);
}
.iso-item-meta-line {
    color: var(--muted);
    font-size: .74rem;
    line-height: 1.4;
}
.iso-item-meta-line span + span::before {
    content: '•';
    margin: 0 .42rem;
    color: rgba(19, 34, 56, .35);
}
.iso-pill.compact {
    padding: .22rem .48rem;
    font-size: .7rem;
}
.iso-pill.neutral { background: rgba(19, 34, 56, .08); color: var(--ink); }
.iso-pill.teal { background: rgba(15, 118, 110, .12); color: #0f766e; }
.iso-pill.gold { background: rgba(161, 98, 7, .14); color: #a16207; }
.iso-pill.clay, .iso-pill.warning { background: rgba(194, 65, 12, .12); color: #c2410c; }
.iso-pill.danger { background: rgba(185, 28, 28, .12); color: #b91c1c; }
.iso-pill.sky { background: rgba(37, 99, 235, .12); color: #2563eb; }
.iso-pill.success { background: rgba(21, 128, 61, .12); color: #15803d; }
.iso-empty {
    padding: 1.3rem; border-radius: 20px; border: 1px dashed rgba(19, 34, 56, .18); background: rgba(255, 255, 255, .6);
    color: var(--muted); text-align: center; line-height: 1.65;
}
.iso-inspector-panel {
    position: sticky;
    top: 1rem;
    min-height: 760px;
    display: flex;
    flex-direction: column;
}
.iso-detail-head { padding-bottom: 1rem; border-bottom: 1px solid var(--line); }
.iso-detail-head small, .iso-detail-label { display: block; color: var(--muted); font-size: .74rem; font-weight: 700; text-transform: uppercase; letter-spacing: .08em; }
.iso-detail-head strong { display: block; margin-top: .5rem; font-size: 1.08rem; font-weight: 800; color: #0f766e; }
.iso-detail-head span { display: block; margin-top: .35rem; font-size: .98rem; font-weight: 800; line-height: 1.45; }
.iso-detail-body {
    display: grid;
    gap: .9rem;
    padding-top: 1rem;
    flex: 1;
    min-height: 0;
    overflow: auto;
    align-content: start;
}
.iso-detail-meta {
    display: flex;
    flex-wrap: wrap;
    gap: .55rem;
    padding-bottom: .15rem;
}
.iso-detail-meta-item {
    display: inline-flex;
    align-items: center;
    gap: .38rem;
    padding: .38rem .65rem;
    border-radius: 999px;
    background: rgba(19, 34, 56, .06);
    color: var(--ink);
    font-size: .76rem;
    line-height: 1.2;
}
.iso-detail-meta-item small {
    color: var(--muted);
    font-size: .68rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .06em;
}
.iso-detail-meta-item strong {
    font-size: .78rem;
    font-weight: 700;
}
.iso-note, .iso-callout { padding: .9rem 1rem; border-radius: 18px; border: 1px solid var(--line); }
.iso-note { background: rgba(238, 245, 245, .84); line-height: 1.7; }
.iso-callout { background: rgba(255, 255, 255, .72); line-height: 1.7; }
.iso-evidence-list {
    margin: .15rem 0 0;
    padding-left: 1rem;
    color: var(--muted);
    font-size: .84rem;
    line-height: 1.7;
}
.iso-evidence-list li { margin-bottom: .22rem; }
.iso-tile {
    width: 100%; padding: .72rem .78rem; border-radius: 16px; border: 1px solid var(--line); background: #fff; text-align: left;
    cursor: pointer;
}
.iso-tile.active { border-color: rgba(15, 118, 110, .24); box-shadow: 0 16px 28px rgba(15, 118, 110, .08); background: rgba(238, 245, 245, .72); }
.iso-tile-top { display: flex; align-items: center; justify-content: space-between; gap: .45rem; color: var(--muted); font-size: .64rem; text-transform: uppercase; letter-spacing: .06em; font-weight: 700; }
.iso-tile-top > span:last-child { white-space: nowrap; }
.iso-tile-icon {
    width: 1.9rem; height: 1.9rem; display: inline-flex; align-items: center; justify-content: center;
    border-radius: 12px; background: rgba(15, 118, 110, .12); color: var(--accent); font-size: .8rem;
}
.iso-tile strong { display: block; margin-top: .42rem; font-size: .86rem; font-weight: 800; line-height: 1.2; }
.iso-tile p {
    display: -webkit-box;
    margin-top: .28rem;
    color: var(--muted);
    font-size: .74rem;
    line-height: 1.45;
    overflow: hidden;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
}
.iso-selected {
    display: grid; grid-template-columns: auto 1fr auto; gap: .85rem; align-items: center; padding: .92rem 1rem;
    border-radius: 20px; background: linear-gradient(135deg, rgba(24, 59, 86, .96) 0%, rgba(15, 118, 110, .9) 100%); color: #fffaf2;
}
.iso-selected-mark {
    width: 2.55rem; height: 2.55rem; display: inline-flex; align-items: center; justify-content: center;
    border-radius: 16px; background: rgba(255, 250, 242, .14); font-size: .98rem;
}
.iso-selected small { color: rgba(255, 250, 242, .68); }
.iso-selected h3 { margin: .16rem 0 0; font-size: 1.02rem; font-weight: 800; }
.iso-selected p { margin: .28rem 0 0; color: rgba(255, 250, 242, .8); line-height: 1.52; font-size: .8rem; }
.iso-selected-count { text-align: right; }
.iso-selected-count strong { display: block; font-size: 1.7rem; font-weight: 800; line-height: 1; }
.iso-selected-count span { display: block; margin-top: .15rem; color: rgba(255, 250, 242, .72); font-size: .72rem; text-transform: uppercase; letter-spacing: .08em; font-weight: 700; }
.iso-concept-workspace { display: grid; grid-template-columns: .9fr 1.1fr; gap: 1rem; }
@media (max-width: 1199.98px) {
    .iso-hero, .iso-metric, .iso-side-card { min-height: auto; }
    .iso-hero, .iso-nav, .iso-grid.two, .iso-workspace, .iso-concept-workspace, .iso-metric-grid, .iso-type-grid, .iso-quick-themes { grid-template-columns: 1fr; }
    .iso-bar { grid-template-columns: 1fr; }
}
@media (max-width: 1599.98px) {
    .iso-concept-board { grid-template-columns: repeat(3, minmax(0, 1fr)); }
}
@media (max-width: 991.98px) {
    .iso-concept-board { grid-template-columns: repeat(2, minmax(0, 1fr)); }
}
@media (max-width: 767.98px) {
    .iso-hero, .iso-panel { padding: 1.2rem; border-radius: 22px; }
    .iso-selected { grid-template-columns: 1fr; }
    .iso-selected-count { text-align: left; }
    .iso-concept-board { grid-template-columns: 1fr; }
    .iso-inspector-panel { min-height: auto; position: static; }
}
</style>
@endpush

@section('content')
@php
    $controlCollection = collect($controls);
    $totalControls = $controlCollection->count();

    $domainMeta = [
        'Organisasional' => ['id' => 'ORG', 'color' => '#0f766e', 'summary' => 'Kebijakan, tata kelola, kontrak, dan keputusan manajemen.'],
        'Orang' => ['id' => 'PPL', 'color' => '#2563eb', 'summary' => 'Kesadaran, perilaku, dan akuntabilitas personel.'],
        'Fisik' => ['id' => 'PHY', 'color' => '#c2410c', 'summary' => 'Lokasi, perangkat, dan perlindungan lingkungan operasional.'],
        'Teknologi' => ['id' => 'TEC', 'color' => '#1d3557', 'summary' => 'Identitas, monitoring, hardening, dan operasional teknis.'],
    ];

    $typeMeta = [
        'Pencegahan' => ['color' => '#0f766e'],
        'Deteksi' => ['color' => '#a16207'],
        'Koreksi' => ['color' => '#c2410c'],
    ];

    $priorityMeta = [
        'Kritis' => ['color' => '#b91c1c'],
        'Tinggi' => ['color' => '#c2410c'],
        'Sedang' => ['color' => '#2563eb'],
        'Rendah' => ['color' => '#15803d'],
    ];

    $conceptMeta = [
        'Mengidentifikasi' => ['key' => 'Identify', 'color' => '#2563eb', 'icon' => 'fa-compass', 'summary' => 'Memahami aset, eksposur, dan konteks bisnis yang harus dijaga.'],
        'Melindungi' => ['key' => 'Protect', 'color' => '#0f766e', 'icon' => 'fa-shield-alt', 'summary' => 'Menjaga layanan, data, dan akses dengan pengamanan yang konsisten.'],
        'Mendeteksi' => ['key' => 'Detect', 'color' => '#a16207', 'icon' => 'fa-binoculars', 'summary' => 'Menangkap indikasi insiden dan penyimpangan secepat mungkin.'],
        'Merespons' => ['key' => 'Respond', 'color' => '#b91c1c', 'icon' => 'fa-bolt', 'summary' => 'Mengatur langkah taktis ketika kejadian sudah terkonfirmasi.'],
        'Memulihkan' => ['key' => 'Recover', 'color' => '#15803d', 'icon' => 'fa-life-ring', 'summary' => 'Mengembalikan layanan dan kapabilitas setelah gangguan.'],
        'Mengatur' => ['key' => 'Govern', 'color' => '#1d3557', 'icon' => 'fa-sitemap', 'summary' => 'Menetapkan arah, akuntabilitas, dan keputusan tingkat organisasi.'],
    ];

    $domainBreakdown = collect($domainStats)->map(function ($item) use ($domainMeta) {
        $meta = $domainMeta[$item['name']];
        return ['name' => $item['name'], 'id' => $meta['id'], 'count' => $item['count'], 'color' => $meta['color'], 'summary' => $meta['summary']];
    })->values();

    $typeBreakdown = collect($typeMeta)->map(function ($meta, $type) use ($controlCollection, $totalControls) {
        $count = $controlCollection->where('type', $type)->count();
        return ['name' => $type, 'count' => $count, 'share' => $totalControls > 0 ? round(($count / $totalControls) * 100) : 0, 'color' => $meta['color']];
    })->values();

    $priorityBreakdown = collect($priorityMeta)->map(function ($meta, $priority) use ($controlCollection) {
        return ['name' => $priority, 'count' => $controlCollection->where('priority', $priority)->count(), 'color' => $meta['color']];
    })->values();

    $conceptBreakdown = collect($conceptMeta)->map(function ($meta, $concept) use ($controlCollection) {
        return ['name' => $concept, 'key' => $meta['key'], 'count' => $controlCollection->where('concept', $concept)->count(), 'color' => $meta['color'], 'icon' => $meta['icon'], 'summary' => $meta['summary']];
    })->values();

    $maxDomainCount = max($domainBreakdown->max('count') ?: 0, 1);
    $maxPriorityCount = max($priorityBreakdown->max('count') ?: 0, 1);
    $criticalControls = $controlCollection->where('priority', 'Kritis')->count();
    $evidenceCount = $controlCollection->pluck('exampleEvidence')->flatten(1)->filter()->count();
@endphp

<div class="iso-page">
    <div class="iso-shell">
        <section class="iso-hero">
            <div>
                <span class="iso-kicker"><i class="fas fa-compass"></i>Framework Studio</span>
                <h1 class="iso-title">ISO 27001:2022 Control Studio</h1>
                <p class="iso-lede">
                    Halaman ini menyajikan kontrol Annex A dalam tampilan yang mendukung analisa kebijakan,
                    penentuan prioritas implementasi, dan identifikasi kebutuhan bukti audit.
                </p>

                <div class="iso-metric-grid">
                    <div class="iso-metric">
                        <label>Annex A Controls</label>
                        <strong>{{ $totalControls }}</strong>
                        <span>Kontrol inti yang bisa ditelusuri end-to-end.</span>
                    </div>
                    <div class="iso-metric">
                        <label>Categories</label>
                        <strong>{{ $domainBreakdown->count() }}</strong>
                        <span>Organisasional, Orang, Fisik, dan Teknologi.</span>
                    </div>
                    <div class="iso-metric">
                        <label>Critical Priorities</label>
                        <strong>{{ $criticalControls }}</strong>
                        <span>Area yang paling butuh sequencing implementasi.</span>
                    </div>
                    <div class="iso-metric">
                        <label>Evidence Cues</label>
                        <strong>{{ $evidenceCount }}</strong>
                        <span>Contoh bukti audit yang bisa jadi titik mulai.</span>
                    </div>
                </div>
            </div>

            <div class="iso-side-stack">
                <div class="iso-side-card">
                    <label>Recommended Review Flow</label>
                    <div class="iso-bullets">
                        <div><i class="fas fa-circle"></i><span>Tinjau ringkasan kontrol untuk mengidentifikasi area fokus berdasarkan kategori dan prioritas.</span></div>
                        <div><i class="fas fa-circle"></i><span>Gunakan Explorer untuk menyaring kontrol dan menganalisa rincian implementasi, bukti audit, serta tingkat kesulitan.</span></div>
                        <div><i class="fas fa-circle"></i><span>Gunakan Concept Board untuk melihat keterkaitan kontrol dengan kapabilitas keamanan siber.</span></div>
                    </div>
                </div>
            </div>
        </section>

        <div class="nav iso-nav" id="isoTab" role="tablist">
            <button class="iso-tab active" data-bs-toggle="tab" data-bs-target="#iso-overview-pane" type="button" role="tab">
                <i class="fas fa-chart-line"></i>
                <div>
                    <strong>Ringkasan Program</strong>
                    <span>Pulse, distribusi kategori, dan profil prioritas.</span>
                </div>
            </button>
            <button class="iso-tab" data-bs-toggle="tab" data-bs-target="#iso-explorer-pane" type="button" role="tab">
                <i class="fas fa-sliders-h"></i>
                <div>
                    <strong>Explorer Kontrol</strong>
                    <span>Filter panel, daftar kontrol, dan inspector detail.</span>
                </div>
            </button>
            <button class="iso-tab" data-bs-toggle="tab" data-bs-target="#iso-concept-pane" type="button" role="tab">
                <i class="fas fa-project-diagram"></i>
                <div>
                    <strong>Concept Board</strong>
                    <span>Enam konsep keamanan sebagai perspektif pembacaan alternatif.</span>
                </div>
            </button>
        </div>

        <div class="tab-content">
            <div class="tab-pane fade show active" id="iso-overview-pane" role="tabpanel">
                <div class="iso-grid two">
                    <article class="iso-panel">
                        <div class="iso-panel-head">
                            <h3>Distribusi per kategori</h3>
                            <span class="iso-chip">Klik kategori untuk filter</span>
                        </div>
                        <p class="iso-panel-copy">Empat kategori ini tetap menjadi struktur utama pengelompokan kontrol sehingga area tata kelola, personel, fisik, dan teknologi dapat dianalisa secara cepat dan terarah.</p>
                        <div class="iso-bars">
                            @foreach($domainBreakdown as $domain)
                                <button type="button" class="iso-bar jump" data-theme-filter="{{ $domain['name'] }}">
                                    <div class="iso-bar-copy">
                                        <strong>{{ $domain['name'] }}</strong>
                                        <span>{{ $domain['summary'] }}</span>
                                    </div>
                                    <div class="iso-track">
                                        <b style="width: {{ round(($domain['count'] / $maxDomainCount) * 100) }}%; background: {{ $domain['color'] }};"></b>
                                    </div>
                                    <div class="iso-number">{{ $domain['count'] }}</div>
                                </button>
                            @endforeach
                        </div>
                    </article>

                    <article class="iso-panel">
                        <div class="iso-panel-head">
                            <h3>Profil kontrol</h3>
                            <span class="iso-chip">Ringkasan tipe dan prioritas</span>
                        </div>
                        <p class="iso-panel-copy">Panel ini merangkum komposisi jenis kontrol serta sebaran prioritas implementasi untuk membantu penetapan fokus pelaksanaan.</p>
                        <div class="iso-type-grid">
                            @foreach($typeBreakdown as $type)
                                <div class="iso-mini compact" style="--accent: {{ $type['color'] }};">
                                    <label>{{ $type['name'] }}</label>
                                    <strong>{{ $type['count'] }}</strong>
                                    <span>{{ $type['share'] }}% dari total kontrol</span>
                                </div>
                            @endforeach
                        </div>
                        <div class="iso-priority-bars">
                            @foreach($priorityBreakdown as $priority)
                                <div class="iso-priority-bar" style="--accent: {{ $priority['color'] }};">
                                    <div class="iso-priority-name">{{ $priority['name'] }}</div>
                                    <div class="iso-priority-track">
                                        <b style="width: {{ round(($priority['count'] / $maxPriorityCount) * 100) }}%;"></b>
                                    </div>
                                    <div class="iso-number">{{ $priority['count'] }}</div>
                                </div>
                            @endforeach
                        </div>
                    </article>

                    <article class="iso-panel">
                        <div class="iso-panel-head">
                            <h3>Landscape konsep keamanan</h3>
                            <span class="iso-chip">Snapshot</span>
                        </div>
                        <p class="iso-panel-copy">Pemetaan konsep keamanan disajikan sebagai board terpisah agar dapat digunakan sebagai perspektif analisis tambahan tanpa mengganggu pembacaan struktur utama Annex A.</p>
                        <div class="iso-concept-board">
                            @foreach($conceptBreakdown as $concept)
                                <div class="iso-mini" style="--accent: {{ $concept['color'] }};">
                                    <label><i class="fas {{ $concept['icon'] }} me-1"></i>{{ $concept['key'] }}</label>
                                    <strong>{{ $concept['count'] }}</strong>
                                    <span>{{ $concept['name'] }}</span>
                                </div>
                            @endforeach
                        </div>
                    </article>

                    <article class="iso-panel">
                        <div class="iso-panel-head">
                            <h3>Cara pakai halaman ini</h3>
                            <span class="iso-chip">Workflow</span>
                        </div>
                        <p class="iso-panel-copy"><strong>Mulai dari overview.</strong> Identifikasi kategori dengan kepadatan kontrol tertinggi dan prioritas implementasi yang paling menonjol.</p>
                        <p class="iso-panel-copy"><strong>Pindah ke explorer.</strong> Filter kontrol yang relevan, lalu baca detail untuk bukti audit dan tips implementasi.</p>
                        <p class="iso-panel-copy mb-0"><strong>Validasi dengan concept board.</strong> Saat butuh narasi yang lebih dekat ke bahasa cyber security, gunakan lens Identify, Protect, Detect, Respond, Recover, atau Govern.</p>
                    </article>
                </div>
            </div>

            <div class="tab-pane fade" id="iso-explorer-pane" role="tabpanel">
                <div class="iso-workspace">
                    <aside class="iso-panel" style="position: sticky; top: 1rem;">
                        <div class="iso-panel-head">
                            <h3>Filter Workspace</h3>
                        </div>
                        <div class="iso-quick-themes mb-3">
                            @foreach($domainBreakdown as $domain)
                                <button type="button" class="iso-quick-theme" data-theme-filter="{{ $domain['name'] }}" style="--accent: {{ $domain['color'] }};">
                                    <strong>{{ $domain['name'] === 'Organisasional' ? 'Organisasi' : $domain['name'] }}</strong>
                                    <span>{{ $domain['count'] }} kontrol</span>
                                </button>
                            @endforeach
                        </div>
                        <div class="iso-form-grid">
                            <div>
                                <label for="filter-theme">Kategori</label>
                                <select class="form-select" id="filter-theme" onchange="window.__isoApplyFilters && window.__isoApplyFilters()">
                                    <option value="All">Semua kategori</option>
                                    @foreach($domainBreakdown as $domain)
                                        <option value="{{ $domain['name'] }}">{{ $domain['name'] }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label for="filter-type">Jenis kontrol</label>
                                <select class="form-select" id="filter-type" onchange="window.__isoApplyFilters && window.__isoApplyFilters()">
                                    <option value="All">Semua jenis</option>
                                    @foreach($typeBreakdown as $type)
                                        <option value="{{ $type['name'] }}">{{ $type['name'] }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label for="filter-prio">Prioritas</label>
                                <select class="form-select" id="filter-prio" onchange="window.__isoApplyFilters && window.__isoApplyFilters()">
                                    <option value="All">Semua prioritas</option>
                                    @foreach($priorityBreakdown as $priority)
                                        <option value="{{ $priority['name'] }}">{{ $priority['name'] }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label for="filter-diff">Kesulitan</label>
                                <select class="form-select" id="filter-diff" onchange="window.__isoApplyFilters && window.__isoApplyFilters()">
                                    <option value="All">Semua tingkat</option>
                                    <option value="Rendah">Rendah</option>
                                    <option value="Sedang">Sedang</option>
                                    <option value="Tinggi">Tinggi</option>
                                </select>
                            </div>
                            <div>
                                <label for="filter-search">Cari kontrol</label>
                                <input type="text" class="form-control" id="filter-search" placeholder="Cari ID, nama, atau capability" oninput="window.__isoApplyFilters && window.__isoApplyFilters()">
                            </div>
                            <div class="iso-summary">
                                <small>Visible controls</small>
                                <strong id="table-record-count">{{ $totalControls }}</strong>
                                <span id="active-filter-copy">Semua kontrol sedang ditampilkan.</span>
                            </div>
                            <button class="btn btn-outline-dark" id="btn-clear-filters" type="button">
                                <i class="fas fa-rotate-left me-2"></i>Reset filter
                            </button>
                        </div>
                    </aside>

                    <section class="iso-panel">
                        <div class="iso-panel-head">
                            <h3>Daftar kontrol</h3>
                            <span class="iso-chip">Klik untuk buka inspector</span>
                        </div>
                        <div class="iso-list" id="controls-list"></div>
                    </section>

                    <section class="iso-panel iso-inspector-panel">
                        <div class="iso-detail-head">
                            <small>Control Inspector</small>
                            <strong id="detail-title">Pilih sebuah kontrol</strong>
                            <span id="detail-subtitle">Klik kontrol di daftar untuk membuka deskripsi, interpretasi, bukti audit, dan tips implementasi.</span>
                        </div>
                        <div class="iso-detail-body" id="detail-body">
                            <div class="iso-empty">Belum ada kontrol yang dipilih.</div>
                        </div>
                    </section>
                </div>
            </div>

            <div class="tab-pane fade" id="iso-concept-pane" role="tabpanel">
                <div class="iso-grid">
                    <section class="iso-panel">
                        <div class="iso-panel-head">
                            <h3>Concept Board</h3>
                            <span class="iso-chip">Pick one lens</span>
                        </div>
                        <p class="iso-panel-copy">Enam konsep keamanan ini disediakan sebagai lensa pembacaan alternatif untuk menghubungkan kontrol ISO 27001 dengan kapabilitas keamanan siber secara lebih jelas dan sistematis.</p>
                        <div class="iso-concept-board">
                            @foreach($conceptBreakdown as $concept)
                                <button type="button" class="iso-tile" data-concept="{{ $concept['name'] }}" style="--accent: {{ $concept['color'] }};">
                                    <div class="iso-tile-top">
                                        <span class="iso-tile-icon"><i class="fas {{ $concept['icon'] }}"></i></span>
                                        <span>{{ $concept['count'] }} controls</span>
                                    </div>
                                    <strong>{{ $concept['name'] }}</strong>
                                    <p>{{ $concept['summary'] }}</p>
                                </button>
                            @endforeach
                        </div>
                    </section>

                    <div id="concept-empty-state" class="iso-empty">
                        Pilih salah satu concept lane di atas untuk menampilkan kontrol terkait dan inspector detailnya.
                    </div>

                    <div id="concept-active-view" class="d-none">
                        <div class="iso-grid">
                            <div class="iso-selected" id="concept-summary-card"></div>
                            <div class="iso-concept-workspace">
                                <section class="iso-panel iso-inspector-panel">
                                    <div class="iso-panel-head">
                                        <h3>Kontrol terkait</h3>
                                        <span class="iso-chip">Auto-filtered by concept</span>
                                    </div>
                                    <div class="iso-list" id="concept-controls-list"></div>
                                </section>
                                <section class="iso-panel">
                                    <div class="iso-detail-head">
                                        <small>Concept Inspector</small>
                                        <strong id="concept-detail-title">Pilih sebuah kontrol</strong>
                                        <span id="concept-detail-subtitle">Klik kontrol dari concept lane yang dipilih untuk membaca detail lengkap.</span>
                                    </div>
                                    <div class="iso-detail-body" id="concept-detail-body">
                                        <div class="iso-empty">Belum ada kontrol yang dipilih.</div>
                                    </div>
                                </section>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
const isoControls = @json($controls);
const domainMeta = @json($domainMeta);
const conceptMeta = @json($conceptMeta);

function escapeHtml(value) {
    return String(value ?? '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}

function normalizeValue(value) {
    return String(value ?? '')
        .trim()
        .toLowerCase()
        .normalize('NFKC');
}

function toneForType(type) {
    return { 'Pencegahan': 'teal', 'Deteksi': 'gold', 'Koreksi': 'clay' }[type] || 'neutral';
}

function toneForPriority(priority) {
    return { 'Kritis': 'danger', 'Tinggi': 'warning', 'Sedang': 'sky', 'Rendah': 'success' }[priority] || 'neutral';
}

function domainColor(domain) {
    return (domainMeta[domain] || {}).color || '#64748b';
}

function buildPill(label, className) {
    return `<span class="iso-pill ${className}">${escapeHtml(label)}</span>`;
}

function buildDetail(control) {
    const evidence = Array.isArray(control.exampleEvidence) && control.exampleEvidence.length
        ? `<ul class="iso-evidence-list">${control.exampleEvidence.map((item) => `<li>${escapeHtml(item)}</li>`).join('')}</ul>`
        : '<div class="iso-empty">Belum ada contoh bukti audit untuk kontrol ini.</div>';

    const chips = [
        control.concept ? `<span class="iso-chip">${escapeHtml(control.concept)}</span>` : '',
        control.capability ? `<span class="iso-chip">${escapeHtml(control.capability)}</span>` : '',
    ].filter(Boolean).join('');

    return `
        <div class="iso-detail-meta">
            <span class="iso-detail-meta-item"><small>Kategori</small><strong>${escapeHtml(control.domain || '-')}</strong></span>
            <span class="iso-detail-meta-item"><small>Jenis</small><strong>${escapeHtml(control.type || '-')}</strong></span>
            <span class="iso-detail-meta-item"><small>Prioritas</small><strong>${escapeHtml(control.priority || '-')}</strong></span>
            <span class="iso-detail-meta-item"><small>Kesulitan</small><strong>${escapeHtml(control.difficulty || '-')}</strong></span>
        </div>
        <div>
            <span class="iso-detail-label">Deskripsi kontrol</span>
            <div class="iso-callout">${escapeHtml(control.description || '-')}</div>
        </div>
        <div>
            <span class="iso-detail-label">Interpretasi</span>
            <div class="iso-note">${escapeHtml(control.interpretation || '-')}</div>
        </div>
        <div>
            <span class="iso-detail-label">Contoh bukti audit</span>
            ${evidence}
        </div>
        <div>
            <span class="iso-detail-label">Tips implementasi</span>
            <div class="iso-callout">${escapeHtml(control.implementationTips || '-')}</div>
        </div>
        <div>
            <span class="iso-detail-label">Konsep dan capability</span>
            <div class="mt-2">${chips || '<span class="iso-chip">Tidak ada data tambahan</span>'}</div>
        </div>
    `;
}

function buildItem(control, selected) {
    const accent = domainColor(control.domain);
    return `
        <button type="button" class="iso-item${selected ? ' active' : ''}" data-id="${escapeHtml(control.id)}" style="--accent:${accent};">
            <div class="iso-item-top">
                <span class="iso-item-code">${escapeHtml(control.id)}</span>
                ${buildPill(control.priority || '-', `${toneForPriority(control.priority)} compact`)}
            </div>
            <div class="iso-item-name">${escapeHtml(control.name || '-')}</div>
            <div class="iso-item-meta-line">
                <span>${escapeHtml(control.domain || '-')}</span>
                <span>${escapeHtml(control.type || '-')}</span>
                <span>${escapeHtml(control.difficulty || '-')}</span>
            </div>
        </button>
    `;
}

document.addEventListener('DOMContentLoaded', function () {
    const explorerState = { selectedId: isoControls[0]?.id || null };
    const conceptState = { active: null, selectedId: null };

    const explorerEls = {
        list: document.getElementById('controls-list'),
        count: document.getElementById('table-record-count'),
        copy: document.getElementById('active-filter-copy'),
        title: document.getElementById('detail-title'),
        subtitle: document.getElementById('detail-subtitle'),
        body: document.getElementById('detail-body'),
        themeSelect: document.getElementById('filter-theme'),
    };

    const conceptEls = {
        tiles: Array.from(document.querySelectorAll('.iso-tile')),
        empty: document.getElementById('concept-empty-state'),
        active: document.getElementById('concept-active-view'),
        summary: document.getElementById('concept-summary-card'),
        list: document.getElementById('concept-controls-list'),
        title: document.getElementById('concept-detail-title'),
        subtitle: document.getElementById('concept-detail-subtitle'),
        body: document.getElementById('concept-detail-body'),
    };

    function setDetail(panel, control, emptyText) {
        if (!control) {
            panel.title.textContent = 'Pilih sebuah kontrol';
            panel.subtitle.textContent = emptyText;
            panel.body.innerHTML = '<div class="iso-empty">Belum ada kontrol yang dipilih.</div>';
            return;
        }

        panel.title.textContent = control.id || '-';
        panel.subtitle.textContent = control.name || '-';
        panel.body.innerHTML = buildDetail(control);
    }

    function filteredControls() {
        const theme = explorerEls.themeSelect.value;
        const type = document.getElementById('filter-type').value;
        const priority = document.getElementById('filter-prio').value;
        const difficulty = document.getElementById('filter-diff').value;
        const query = document.getElementById('filter-search').value.trim().toLowerCase();

        return isoControls.filter((control) => {
            if (theme !== 'All' && normalizeValue(control.domain) !== normalizeValue(theme)) return false;
            if (type !== 'All' && normalizeValue(control.type) !== normalizeValue(type)) return false;
            if (priority !== 'All' && normalizeValue(control.priority) !== normalizeValue(priority)) return false;
            if (difficulty !== 'All' && normalizeValue(control.difficulty) !== normalizeValue(difficulty)) return false;

            if (!query) return true;

            return [control.id, control.name, control.capability, control.concept, control.domain]
                .join(' ')
                .toLowerCase()
                .includes(query);
        });
    }

    function activeFilterCopy() {
        const parts = [];
        const filterMap = [
            ['filter-theme', 'kategori'],
            ['filter-type', 'jenis'],
            ['filter-prio', 'prioritas'],
            ['filter-diff', 'kesulitan'],
        ];

        filterMap.forEach(([id, label]) => {
            const value = document.getElementById(id).value;
            if (value !== 'All') parts.push(`${label} ${value}`);
        });

        const query = document.getElementById('filter-search').value.trim();
        if (query) parts.push(`cari "${query}"`);

        return parts.length ? `Filter aktif: ${parts.join(', ')}.` : 'Semua kontrol sedang ditampilkan.';
    }

    function renderList(container, list, selectedId, emptyCopy, onSelect) {
        if (!list.length) {
            container.innerHTML = `<div class="iso-empty">${escapeHtml(emptyCopy)}</div>`;
            return;
        }

        container.innerHTML = list.map((control) => buildItem(control, control.id === selectedId)).join('');
        container.querySelectorAll('.iso-item').forEach((node) => {
            node.addEventListener('click', function () {
                const control = list.find((item) => item.id === node.dataset.id);
                if (control) onSelect(control);
            });
        });
    }

    function renderExplorer() {
        const list = filteredControls();
        if (!list.some((control) => control.id === explorerState.selectedId)) {
            explorerState.selectedId = list[0]?.id || null;
        }

        explorerEls.count.textContent = list.length;
        explorerEls.copy.textContent = activeFilterCopy();

        renderList(
            explorerEls.list,
            list,
            explorerState.selectedId,
            'Tidak ada kontrol yang cocok dengan filter saat ini.',
            (control) => {
                explorerState.selectedId = control.id;
                renderExplorer();
            }
        );

        setDetail(
            explorerEls,
            list.find((control) => control.id === explorerState.selectedId) || null,
            list.length ? 'Klik kontrol di daftar untuk membuka detail lengkap.' : 'Ubah filter terlebih dulu untuk menampilkan kontrol.'
        );
    }

    function renderConcept() {
        conceptEls.tiles.forEach((tile) => tile.classList.toggle('active', tile.dataset.concept === conceptState.active));

        if (!conceptState.active) {
            conceptEls.empty.classList.remove('d-none');
            conceptEls.active.classList.add('d-none');
            return;
        }

        const meta = conceptMeta[conceptState.active];
        const list = isoControls.filter((control) => control.concept === conceptState.active);

        if (!list.some((control) => control.id === conceptState.selectedId)) {
            conceptState.selectedId = list[0]?.id || null;
        }

        conceptEls.empty.classList.add('d-none');
        conceptEls.active.classList.remove('d-none');
        conceptEls.summary.innerHTML = `
            <div class="iso-selected-mark"><i class="fas ${escapeHtml(meta.icon)}"></i></div>
            <div>
                <small>${escapeHtml(meta.key)}</small>
                <h3>${escapeHtml(conceptState.active)}</h3>
                <p>${escapeHtml(meta.summary)}</p>
            </div>
            <div class="iso-selected-count">
                <strong>${list.length}</strong>
                <span>mapped controls</span>
            </div>
        `;

        renderList(
            conceptEls.list,
            list,
            conceptState.selectedId,
            'Belum ada kontrol yang dipetakan ke concept lane ini.',
            (control) => {
                conceptState.selectedId = control.id;
                renderConcept();
            }
        );

        setDetail(
            conceptEls,
            list.find((control) => control.id === conceptState.selectedId) || null,
            list.length ? 'Klik kontrol dari concept lane yang dipilih untuk membaca detail lengkap.' : 'Concept lane ini belum memiliki kontrol terkait.'
        );
    }

    function jumpToTheme(theme) {
        explorerEls.themeSelect.value = theme;
        renderExplorer();

        const explorerTab = document.querySelector('.iso-tab[data-bs-target="#iso-explorer-pane"]');
        if (explorerTab) {
            explorerTab.click();
        }
    }

    ['filter-theme', 'filter-type', 'filter-prio', 'filter-diff'].forEach((id) => {
        document.getElementById(id).addEventListener('change', renderExplorer);
    });
    document.getElementById('filter-search').addEventListener('input', renderExplorer);
    document.getElementById('btn-clear-filters').addEventListener('click', function () {
        document.getElementById('filter-theme').value = 'All';
        document.getElementById('filter-type').value = 'All';
        document.getElementById('filter-prio').value = 'All';
        document.getElementById('filter-diff').value = 'All';
        document.getElementById('filter-search').value = '';
        renderExplorer();
    });

    conceptEls.tiles.forEach((tile) => {
        tile.addEventListener('click', function () {
            conceptState.active = tile.dataset.concept;
            conceptState.selectedId = null;
            renderConcept();
        });
    });

    document.querySelectorAll('[data-theme-filter]').forEach((node) => {
        node.addEventListener('click', function () {
            jumpToTheme(node.dataset.themeFilter);
        });
    });

    window.__isoApplyFilters = renderExplorer;
    renderExplorer();
    renderConcept();
});
</script>
@endpush
