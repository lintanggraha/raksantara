@extends('layouts.tabler')

@section('title', 'User Guide & Pusat Otoritas - Raksantara')

@push('styles')
<style>
    /* UI/UX Pro Max Design System Tokens for User Guide */
    :root {
        --guide-primary: #0b57d0;
        --guide-primary-dark: #0842a0;
        --guide-primary-light: #e8f0fe;
        --guide-surface: #ffffff;
        --guide-surface-muted: #f8fafc;
        --guide-border: #e2e8f0;
        --guide-border-subtle: #f1f5f9;
        --guide-text-main: #0f172a;
        --guide-text-muted: #64748b;
        --guide-radius-lg: 16px;
        --guide-radius-md: 12px;
        --guide-radius-sm: 8px;
        --guide-shadow-sm: 0 1px 3px rgba(15, 23, 42, 0.05);
        --guide-shadow-md: 0 4px 14px -2px rgba(15, 23, 42, 0.06), 0 2px 6px -1px rgba(15, 23, 42, 0.04);
        --guide-shadow-hover: 0 12px 28px -4px rgba(15, 23, 42, 0.1), 0 4px 10px -2px rgba(15, 23, 42, 0.04);
        --guide-transition: all 0.22s cubic-bezier(0.16, 1, 0.3, 1);
    }

    /* Hero Banner */
    .guide-hero {
        background: linear-gradient(135deg, #0f172a 0%, #1e293b 60%, #0f2744 100%);
        border-radius: var(--guide-radius-lg);
        color: #ffffff;
        padding: 2.25rem 2.25rem;
        position: relative;
        overflow: hidden;
        box-shadow: 0 10px 30px -5px rgba(15, 23, 42, 0.35);
        margin-bottom: 1.75rem;
    }
    .guide-hero::before {
        content: "";
        position: absolute;
        top: -40%;
        right: -10%;
        width: 480px;
        height: 480px;
        background: radial-gradient(circle, rgba(14, 165, 233, 0.18) 0%, rgba(99, 102, 241, 0.08) 50%, transparent 70%);
        border-radius: 50%;
        pointer-events: none;
    }
    .guide-hero-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        background: rgba(255, 255, 255, 0.1);
        backdrop-filter: blur(8px);
        border: 1px solid rgba(255, 255, 255, 0.15);
        padding: 0.35rem 0.85rem;
        border-radius: 999px;
        font-size: 0.76rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: #93c5fd;
        margin-bottom: 0.85rem;
    }
    .guide-hero-title {
        font-size: 1.85rem;
        font-weight: 800;
        letter-spacing: -0.02em;
        color: #ffffff;
        margin-bottom: 0.5rem;
        line-height: 1.25;
    }
    .guide-hero-desc {
        color: #cbd5e1;
        font-size: 0.95rem;
        max-width: 720px;
        line-height: 1.6;
        margin-bottom: 1.5rem;
    }

    /* Live Search & Quick Filter Bar */
    .guide-search-wrapper {
        position: relative;
        max-width: 680px;
    }
    .guide-search-input {
        width: 100%;
        background: #ffffff;
        border: 2px solid transparent;
        border-radius: var(--guide-radius-md);
        padding: 0.85rem 3.5rem 0.85rem 3rem;
        font-size: 0.95rem;
        color: #0f172a;
        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.25);
        transition: var(--guide-transition);
    }
    .guide-search-input:focus {
        outline: none;
        border-color: #38bdf8;
        box-shadow: 0 0 0 4px rgba(56, 189, 248, 0.35);
    }
    .guide-search-icon {
        position: absolute;
        left: 1.15rem;
        top: 50%;
        transform: translateY(-50%);
        color: #64748b;
        font-size: 1.1rem;
        pointer-events: none;
    }
    .guide-search-kbd {
        position: absolute;
        right: 1.15rem;
        top: 50%;
        transform: translateY(-50%);
        background: #f1f5f9;
        border: 1px solid #cbd5e1;
        color: #475569;
        padding: 0.15rem 0.45rem;
        border-radius: 6px;
        font-size: 0.72rem;
        font-weight: 700;
        font-family: monospace;
        pointer-events: none;
    }
    .guide-search-clear {
        position: absolute;
        right: 3rem;
        top: 50%;
        transform: translateY(-50%);
        background: none;
        border: none;
        color: #94a3b8;
        cursor: pointer;
        padding: 0.25rem;
        display: none;
    }
    .guide-search-clear:hover {
        color: #0f172a;
    }

    /* Persona Mini Widget in Hero */
    .guide-persona-box {
        background: rgba(255, 255, 255, 0.07);
        backdrop-filter: blur(10px);
        border: 1px solid rgba(255, 255, 255, 0.12);
        border-radius: var(--guide-radius-md);
        padding: 1.1rem 1.25rem;
        color: #ffffff;
        display: flex;
        align-items: center;
        gap: 1rem;
    }
    .guide-persona-avatar {
        width: 48px;
        height: 48px;
        border-radius: 12px;
        background: linear-gradient(135deg, #38bdf8 0%, #3b82f6 100%);
        color: #ffffff;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 800;
        font-size: 1.25rem;
        box-shadow: 0 4px 12px rgba(56, 189, 248, 0.4);
        flex-shrink: 0;
    }
    .guide-persona-name {
        font-size: 1rem;
        font-weight: 700;
        color: #ffffff;
        line-height: 1.2;
    }
    .guide-persona-role {
        font-size: 0.8rem;
        color: #93c5fd;
        display: flex;
        align-items: center;
        gap: 0.4rem;
        margin-top: 0.2rem;
    }

    /* Tabs Styling - Pill Style */
    .guide-tabs-container {
        background: var(--guide-surface);
        border: 1px solid var(--guide-border);
        border-radius: var(--guide-radius-lg);
        padding: 0.75rem 0.75rem 0;
        margin-bottom: 1.5rem;
        box-shadow: var(--guide-shadow-sm);
    }
    .guide-nav-pills {
        display: flex;
        flex-wrap: wrap;
        gap: 0.4rem;
        border: none;
        margin-bottom: 0.75rem;
    }
    .guide-nav-pills .nav-item {
        margin: 0;
    }
    .guide-nav-pills .nav-link {
        border-radius: 999px;
        padding: 0.55rem 1rem;
        color: #475569;
        font-weight: 600;
        font-size: 0.84rem;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        background: transparent;
        border: 1px solid transparent;
        transition: var(--guide-transition);
        white-space: nowrap;
        cursor: pointer;
    }
    .guide-nav-pills .nav-link:hover {
        background: #f1f5f9;
        color: var(--guide-primary);
    }
    .guide-nav-pills .nav-link.active {
        background: var(--guide-primary);
        color: #ffffff;
        box-shadow: 0 4px 12px rgba(11, 87, 208, 0.25);
    }
    .guide-nav-pills .nav-link.active .badge {
        background: rgba(255, 255, 255, 0.25) !important;
        color: #ffffff !important;
    }

    /* Grid & Cards Layout */
    .guide-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(360px, 1fr));
        gap: 1.25rem;
        margin-bottom: 1.5rem;
    }
    @media (max-width: 768px) {
        .guide-grid {
            grid-template-columns: 1fr;
        }
    }

    /* Pro Max Feature Card */
    .guide-card-pro {
        background: var(--guide-surface);
        border: 1px solid var(--guide-border);
        border-radius: var(--guide-radius-md);
        padding: 1.35rem;
        box-shadow: var(--guide-shadow-sm);
        transition: var(--guide-transition);
        position: relative;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
    }
    .guide-card-pro:hover {
        transform: translateY(-3px);
        box-shadow: var(--guide-shadow-hover);
        border-color: #cbd5e1;
    }
    .guide-card-top {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 0.75rem;
        margin-bottom: 0.85rem;
    }
    .guide-card-iconbox {
        width: 44px;
        height: 44px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.15rem;
        flex-shrink: 0;
    }
    .guide-card-iconbox.primary { background: #eff6ff; color: #2563eb; }
    .guide-card-iconbox.indigo { background: #eef2ff; color: #4f46e5; }
    .guide-card-iconbox.emerald { background: #ecfdf5; color: #059669; }
    .guide-card-iconbox.amber { background: #fffbeb; color: #d97706; }
    .guide-card-iconbox.rose { background: #fff1f2; color: #e11d48; }
    .guide-card-iconbox.purple { background: #faf5ff; color: #9333ea; }
    .guide-card-iconbox.cyan { background: #ecfeff; color: #0891b2; }

    .guide-card-kicker {
        font-size: 0.72rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        color: #64748b;
        margin-bottom: 0.15rem;
    }
    .guide-card-title {
        font-size: 1.05rem;
        font-weight: 700;
        color: #0f172a;
        margin-bottom: 0.35rem;
        line-height: 1.3;
    }
    .guide-card-route {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        padding: 0.2rem 0.55rem;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 6px;
        font-size: 0.72rem;
        font-weight: 600;
        color: #475569;
        margin-bottom: 0.75rem;
    }
    .guide-card-desc {
        font-size: 0.88rem;
        color: #475569;
        line-height: 1.55;
        margin-bottom: 0.95rem;
    }
    .guide-card-badges {
        display: flex;
        flex-wrap: wrap;
        gap: 0.35rem;
        margin-bottom: 1rem;
    }

    /* Workflow Stepper in Cards */
    .guide-steps-list {
        background: #f8fafc;
        border: 1px solid #edf2f7;
        border-radius: var(--guide-radius-sm);
        padding: 0.75rem 0.85rem;
        margin-bottom: 0.95rem;
    }
    .guide-steps-title {
        font-size: 0.74rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.03em;
        color: #475569;
        margin-bottom: 0.45rem;
    }
    .guide-step-item {
        display: flex;
        align-items: flex-start;
        gap: 0.5rem;
        font-size: 0.82rem;
        color: #334155;
        margin-bottom: 0.35rem;
        line-height: 1.4;
    }
    .guide-step-item:last-child {
        margin-bottom: 0;
    }
    .guide-step-num {
        width: 18px;
        height: 18px;
        border-radius: 50%;
        background: #e2e8f0;
        color: #475569;
        font-size: 0.68rem;
        font-weight: 700;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        margin-top: 1px;
    }

    /* Progressive Disclosure Accordion (Details tag) */
    .guide-details {
        border-top: 1px dashed #e2e8f0;
        padding-top: 0.75rem;
        margin-top: 0.75rem;
    }
    .guide-details-summary {
        list-style: none;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: space-between;
        font-size: 0.8rem;
        font-weight: 700;
        color: var(--guide-primary);
        padding: 0.25rem 0;
        user-select: none;
    }
    .guide-details-summary::-webkit-details-marker {
        display: none;
    }
    .guide-details-summary .arrow-icon {
        transition: transform 0.2s ease;
        font-size: 0.75rem;
    }
    .guide-details[open] .guide-details-summary .arrow-icon {
        transform: rotate(180deg);
    }
    .guide-details-content {
        padding-top: 0.65rem;
    }

    /* Clean Tables inside Accordions */
    .guide-subtable {
        width: 100%;
        font-size: 0.8rem;
        border-collapse: collapse;
        margin-bottom: 0.5rem;
    }
    .guide-subtable th {
        background: #f1f5f9;
        color: #334155;
        font-weight: 700;
        padding: 0.45rem 0.65rem;
        border: 1px solid #e2e8f0;
        white-space: nowrap;
    }
    .guide-subtable td {
        padding: 0.45rem 0.65rem;
        border: 1px solid #e2e8f0;
        color: #475569;
        vertical-align: top;
        line-height: 1.45;
    }
    .guide-subtable tr:hover td {
        background: #f8fafc;
    }

    /* Action Buttons */
    .btn-guide-action {
        display: inline-flex;
        align-items: center;
        gap: 0.45rem;
        font-size: 0.82rem;
        font-weight: 600;
        padding: 0.45rem 0.85rem;
        border-radius: var(--guide-radius-sm);
        transition: var(--guide-transition);
        text-decoration: none !important;
    }

    /* No search results empty state */
    .guide-empty-state {
        display: none;
        text-align: center;
        padding: 3.5rem 1.5rem;
        background: var(--guide-surface);
        border: 1px dashed var(--guide-border);
        border-radius: var(--guide-radius-lg);
        margin: 1.5rem 0;
    }
    .guide-empty-icon {
        width: 64px;
        height: 64px;
        border-radius: 50%;
        background: #f1f5f9;
        color: #94a3b8;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.75rem;
        margin: 0 auto 1rem;
    }

    /* Section Headers inside Panes */
    .guide-pane-header {
        margin-bottom: 1.25rem;
    }
    .guide-pane-heading {
        font-size: 1.25rem;
        font-weight: 800;
        color: #0f172a;
        margin-bottom: 0.25rem;
    }
    .guide-pane-sub {
        font-size: 0.9rem;
        color: #64748b;
    }
</style>
@endpush

@section('content')
@php
    $viewer = auth()->user();
    $viewerName = $viewer->username ?: $viewer->name;
    $isSuperadmin = $viewer->isSuperadmin();
    $isCompliance = $viewer->isComplianceDivision();
    $isOperational = $viewer->isDivisionVerified() && !$isSuperadmin;
    $showThreatIntel = $isOperational || $isSuperadmin;
    $showComplianceAudit = $isCompliance && !$isSuperadmin;
    $showComplianceAnalysis = $isSuperadmin || $isCompliance;
    $showRegionalAssessment = ($isSuperadmin || $isCompliance) && Route::has('sulteng.dashboard');
    $showFrameworks = $isSuperadmin || $viewer->isDivisionVerified();
    $showOjkMitigation = $showComplianceAudit && config('features.show_ojk_mitigation', false);
    $roleLabel = $isSuperadmin ? 'Superadmin' : ($isCompliance ? 'Divisi Compliance' : ($viewer->isDivisionVerified() ? 'Verified User (' . ($viewer->division ?: 'Operasional') . ')' : 'Pending Approval'));

    $guideTabs = array_values(array_filter([
        ['key' => 'start', 'icon' => 'fas fa-compass', 'label' => 'Mulai & Jalur Cepat', 'visible' => true, 'count' => 'Utama'],
        ['key' => 'role', 'icon' => 'fas fa-users-cog', 'label' => 'Role & Matriks Hak Akses', 'visible' => true, 'count' => 'RBAC'],
        ['key' => 'frameworks', 'icon' => 'fas fa-landmark', 'label' => 'Regulasi & Standar Global', 'visible' => $showFrameworks, 'count' => '14 Standar'],
        ['key' => 'risk', 'icon' => 'fas fa-shield-alt', 'label' => 'Risk Workspace', 'visible' => $isOperational, 'count' => '12 Modul'],
        ['key' => 'intel', 'icon' => 'fas fa-satellite-dish', 'label' => 'Threat Intel & AI', 'visible' => $showThreatIntel, 'count' => 'Live Feed'],
        ['key' => 'analysis', 'icon' => 'fas fa-chart-line', 'label' => 'Analisis Kepatuhan', 'visible' => $showComplianceAnalysis, 'count' => '4 Fitur'],
        ['key' => 'assessment', 'icon' => 'fas fa-clipboard-check', 'label' => 'Monitoring Assessment', 'visible' => $showRegionalAssessment, 'count' => 'Sulteng & Sumut'],
        ['key' => 'compliance', 'icon' => 'fas fa-briefcase', 'label' => 'Audit & Compliance Program', 'visible' => $showComplianceAudit, 'count' => 'Khusus'],
        ['key' => 'superadmin', 'icon' => 'fas fa-cogs', 'label' => 'Superadmin Governance', 'visible' => $isSuperadmin, 'count' => 'Admin'],
        ['key' => 'account', 'icon' => 'fas fa-user-lock', 'label' => 'Akun & Keamanan', 'visible' => true, 'count' => 'MFA'],
    ], fn ($tab) => $tab['visible']));
@endphp

{{-- HERO SECTION --}}
<div class="guide-hero">
    <div class="row align-items-center">
        <div class="col-lg-8">
            <div class="guide-hero-badge">
                <i class="fas fa-shield-alt"></i> Enterprise Cyber Resilience Documentation
            </div>
            <h1 class="guide-hero-title">Pusat Panduan & Otoritas Sistem Raksantara</h1>
            <p class="guide-hero-desc">
                Dokumentasi terpadu alur kerja operasional risiko, 8 regulasi sektor keuangan Indonesia, 6 standar siber global, CISA KEV Live Intelijen, serta instrumen analisis kepatuhan perbankan.
            </p>

            {{-- LIVE SEARCH BAR --}}
            <div class="guide-search-wrapper">
                <i class="fas fa-search guide-search-icon"></i>
                <input
                    type="text"
                    id="guideSearchInput"
                    class="guide-search-input"
                    placeholder="Cari fitur, pasal regulasi, mitigasi, CVE, atau menu... (Tekan '/' untuk fokus)"
                    autocomplete="off"
                >
                <button type="button" id="guideSearchClear" class="guide-search-clear" title="Hapus pencarian">
                    <i class="fas fa-times"></i>
                </button>
                <span class="guide-search-kbd">/</span>
            </div>
        </div>

        <div class="col-lg-4 mt-3 mt-lg-0">
            <div class="guide-persona-box">
                <div class="guide-persona-avatar">
                    {{ strtoupper(substr($viewerName, 0, 1)) }}
                </div>
                <div>
                    <div class="text-white-50 small mb-1">Akun Sesi Aktif</div>
                    <div class="guide-persona-name">{{ $viewerName }}</div>
                    <div class="guide-persona-role">
                        @if($isSuperadmin)
                            <span class="badge bg-purple text-white"><i class="fas fa-crown me-1"></i> Superadmin</span>
                        @elseif($isCompliance)
                            <span class="badge bg-green text-white"><i class="fas fa-balance-scale me-1"></i> Compliance</span>
                        @elseif($viewer->isDivisionVerified())
                            <span class="badge bg-blue text-white"><i class="fas fa-user-check me-1"></i> {{ $viewer->division ?: 'Operasional' }}</span>
                        @else
                            <span class="badge bg-warning text-dark"><i class="fas fa-clock me-1"></i> Pending Verification</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- SEARCH MATCH COUNTER BANNER (Appears during search) --}}
<div id="guideSearchMatchBanner" class="alert alert-info d-none mb-3 align-items-center justify-content-between">
    <div>
        <i class="fas fa-filter me-2"></i>
        Hasil pencarian untuk: <strong id="searchQueryText"></strong> &mdash; Ditemukan <span id="searchResultCount" class="badge bg-primary text-white">0</span> topik
    </div>
    <button type="button" class="btn btn-sm btn-outline-primary" id="btnResetSearch">
        <i class="fas fa-redo me-1"></i> Tampilkan Semua
    </button>
</div>

{{-- TABS NAVIGATION --}}
<div class="guide-tabs-container">
    <ul class="nav nav-pills guide-nav-pills" id="userGuideTab" role="tablist">
        @foreach($guideTabs as $index => $tab)
            <li class="nav-item" role="presentation">
                <button
                    class="nav-link {{ $index === 0 ? 'active' : '' }}"
                    id="guide-{{ $tab['key'] }}-tab"
                    data-bs-toggle="pill"
                    data-bs-target="#guide-{{ $tab['key'] }}-pane"
                    type="button"
                    role="tab"
                    aria-controls="guide-{{ $tab['key'] }}-pane"
                    aria-selected="{{ $index === 0 ? 'true' : 'false' }}"
                >
                    <i class="{{ $tab['icon'] }}"></i>
                    <span>{{ $tab['label'] }}</span>
                    @if(isset($tab['count']))
                        <span class="badge bg-secondary-lt ms-1">{{ $tab['count'] }}</span>
                    @endif
                </button>
            </li>
        @endforeach
    </ul>
</div>

{{-- SEARCH EMPTY STATE --}}
<div id="guideEmptyState" class="guide-empty-state">
    <div class="guide-empty-icon">
        <i class="fas fa-search-minus"></i>
    </div>
    <h3 class="h4 text-dark mb-1">Topik Tidak Ditemukan</h3>
    <p class="text-muted mb-3">Tidak ada panduan atau fitur yang cocok dengan kata kunci pencarian Anda.</p>
    <button type="button" class="btn btn-primary" id="btnResetEmptySearch">
        <i class="fas fa-redo me-1"></i> Reset Pencarian
    </button>
</div>

{{-- TAB CONTENT PANES --}}
<div class="tab-content" id="userGuideTabContent">

    {{-- TAB 1: MULAI & JALUR CEPAT --}}
    <div class="tab-pane fade show active" id="guide-start-pane" role="tabpanel" aria-labelledby="guide-start-tab">
        <div class="guide-pane-header">
            <div class="guide-pane-heading">Jalur Kerja Cepat Berdasarkan Otoritas Anda</div>
            <div class="guide-pane-sub">Pilih jalur kerja utama yang sesuai dengan tanggung jawab dan mandat harian akun Anda.</div>
        </div>

        <div class="guide-grid">
            @if($isSuperadmin)
                {{-- Superadmin Pathways --}}
                <div class="guide-card-pro" data-searchable>
                    <div>
                        <div class="guide-card-top">
                            <div class="guide-card-iconbox purple"><i class="fas fa-users-cog"></i></div>
                            <span class="badge bg-purple-lt fw-bold">Governance</span>
                        </div>
                        <div class="guide-card-kicker">App Management</div>
                        <div class="guide-card-title">Verifikasi Akun & Parameter</div>
                        <div class="guide-card-route">App Management > User Management</div>
                        <div class="guide-card-desc">Sahkan penempatan divisi pengguna baru (Maker-Checker tata kelola), kelola master parameter risiko (10 tab), dan tetapkan Risk Appetite Direksi.</div>
                        <div class="guide-card-badges">
                            <span class="badge bg-blue-lt">Aktivasi User</span>
                            <span class="badge bg-purple-lt">Master Parameter</span>
                            <span class="badge bg-emerald-lt">Corporate Appetite</span>
                        </div>
                    </div>
                    <div class="pt-2 border-top">
                        <a href="{{ route('users.index') }}" class="btn btn-sm btn-primary w-100 btn-guide-action justify-content-center">
                            Buka User Management <i class="fas fa-arrow-right ms-1"></i>
                        </a>
                    </div>
                </div>

                <div class="guide-card-pro" data-searchable>
                    <div>
                        <div class="guide-card-top">
                            <div class="guide-card-iconbox amber"><i class="fas fa-tasks"></i></div>
                            <span class="badge bg-amber-lt fw-bold">Otorisasi</span>
                        </div>
                        <div class="guide-card-kicker">Workflow Approval</div>
                        <div class="guide-card-title">Status Approvals (Maker-Checker)</div>
                        <div class="guide-card-route">Sidebar > Status Approvals</div>
                        <div class="guide-card-desc">Evaluasi pengajuan eskalasi atau penutupan risiko dari unit kerja operasional. Berikan review note dan putuskan Approve atau Reject.</div>
                        <div class="guide-card-badges">
                            <span class="badge bg-amber-lt">Pending Queue</span>
                            <span class="badge bg-indigo-lt">Review Notes</span>
                            <span class="badge bg-teal-lt">POJK 11 Mandate</span>
                        </div>
                    </div>
                    <div class="pt-2 border-top">
                        <a href="{{ route('risk-status-approvals.index') }}" class="btn btn-sm btn-primary w-100 btn-guide-action justify-content-center">
                            Buka Status Approvals <i class="fas fa-arrow-right ms-1"></i>
                        </a>
                    </div>
                </div>

                <div class="guide-card-pro" data-searchable>
                    <div>
                        <div class="guide-card-top">
                            <div class="guide-card-iconbox emerald"><i class="fas fa-chart-line"></i></div>
                            <span class="badge bg-emerald-lt fw-bold">Kepatuhan</span>
                        </div>
                        <div class="guide-card-kicker">Compliance Analytics</div>
                        <div class="guide-card-title">Analisis Kepatuhan & Gap Matrix</div>
                        <div class="guide-card-route">Sidebar > Analisis Kepatuhan</div>
                        <div class="guide-card-desc">Lakukan Cross-Mapping pasal regulasi nasional ke standar global, evaluasi Gap Analysis berdampingan, dan jalankan "What-If" Simulator audit.</div>
                        <div class="guide-card-badges">
                            <span class="badge bg-emerald-lt">Cross-Mapping</span>
                            <span class="badge bg-blue-lt">Gap Analysis</span>
                            <span class="badge bg-indigo-lt">What-If Simulator</span>
                        </div>
                    </div>
                    <div class="pt-2 border-top">
                        <a href="{{ route('analysis.cross-mapping') }}" class="btn btn-sm btn-primary w-100 btn-guide-action justify-content-center">
                            Buka Analisis Kepatuhan <i class="fas fa-arrow-right ms-1"></i>
                        </a>
                    </div>
                </div>

                <div class="guide-card-pro" data-searchable>
                    <div>
                        <div class="guide-card-top">
                            <div class="guide-card-iconbox rose"><i class="fas fa-satellite-dish"></i></div>
                            <span class="badge bg-rose-lt fw-bold">Siber & Intel</span>
                        </div>
                        <div class="guide-card-kicker">Cyber Threat Intel</div>
                        <div class="guide-card-title">CISA KEV Live & AI Inspector</div>
                        <div class="guide-card-route">Sidebar > Threat Intel</div>
                        <div class="guide-card-desc">Pantau kerentanan yang aktif dieksploitasi, filter kampanye ransomware, dan peroleh analisis AI otomatis (Dampak, Cara Kerja, Mitigasi, Regulasi).</div>
                        <div class="guide-card-badges">
                            <span class="badge bg-rose-lt">CISA KEV</span>
                            <span class="badge bg-purple-lt">Instant AI</span>
                            <span class="badge bg-dark-lt">Breach Center</span>
                        </div>
                    </div>
                    <div class="pt-2 border-top">
                        <a href="{{ route('threat-intelligence.index') }}" class="btn btn-sm btn-primary w-100 btn-guide-action justify-content-center">
                            Buka Threat Intel <i class="fas fa-arrow-right ms-1"></i>
                        </a>
                    </div>
                </div>

            @elseif($isCompliance)
                {{-- Compliance Pathways --}}
                <div class="guide-card-pro" data-searchable>
                    <div>
                        <div class="guide-card-top">
                            <div class="guide-card-iconbox primary"><i class="fas fa-chart-pie"></i></div>
                            <span class="badge bg-primary-lt fw-bold">Dashboard Korporat</span>
                        </div>
                        <div class="guide-card-kicker">Portfolio Oversight</div>
                        <div class="guide-card-title">Ringkasan & Analisis Risiko Penuh</div>
                        <div class="guide-card-route">Dashboard > Ringkasan / Analisis</div>
                        <div class="guide-card-desc">Tinjau profil risiko residual lintas seluruh unit, status kepatuhan appetite direksi, KRI bermasalah, kerugian insiden (loss event), dan heatmap korporasi.</div>
                        <div class="guide-card-badges">
                            <span class="badge bg-blue-lt">Heatmap Inheren & Residual</span>
                            <span class="badge bg-amber-lt">Loss Event</span>
                            <span class="badge bg-emerald-lt">Appetite Check</span>
                        </div>
                    </div>
                    <div class="pt-2 border-top">
                        <a href="{{ route('dashboard') }}" class="btn btn-sm btn-primary w-100 btn-guide-action justify-content-center">
                            Buka Dashboard <i class="fas fa-arrow-right ms-1"></i>
                        </a>
                    </div>
                </div>

                <div class="guide-card-pro" data-searchable>
                    <div>
                        <div class="guide-card-top">
                            <div class="guide-card-iconbox emerald"><i class="fas fa-clipboard-check"></i></div>
                            <span class="badge bg-emerald-lt fw-bold">Audit Mandiri</span>
                        </div>
                        <div class="guide-card-kicker">Compliance Instruments</div>
                        <div class="guide-card-title">Checklist Evaluasi Mandiri Excel</div>
                        <div class="guide-card-route">Analisis Kepatuhan > Checklist Mandiri</div>
                        <div class="guide-card-desc">Lakukan evaluasi kepatuhan per pasal untuk POJK 11, PADK 1, PBI, dan ISO. Hitung skor real-time dan unduh lembar kerja resmi Excel (.xlsx).</div>
                        <div class="guide-card-badges">
                            <span class="badge bg-emerald-lt">Skor Kepatuhan</span>
                            <span class="badge bg-teal-lt">Ekspor Excel</span>
                            <span class="badge bg-blue-lt">Klausul POJK / PADK</span>
                        </div>
                    </div>
                    <div class="pt-2 border-top">
                        <a href="{{ route('analysis.checklist') }}" class="btn btn-sm btn-primary w-100 btn-guide-action justify-content-center">
                            Buka Checklist Evaluasi <i class="fas fa-arrow-right ms-1"></i>
                        </a>
                    </div>
                </div>

                <div class="guide-card-pro" data-searchable>
                    <div>
                        <div class="guide-card-top">
                            <div class="guide-card-iconbox cyan"><i class="fas fa-folder-open"></i></div>
                            <span class="badge bg-cyan-lt fw-bold">Regional Audit</span>
                        </div>
                        <div class="guide-card-kicker">Regional Assessment</div>
                        <div class="guide-card-title">Monitoring Evidence Sulteng & Sumut</div>
                        <div class="guide-card-route">Sidebar > Monitoring Assessment</div>
                        <div class="guide-card-desc">Kawal kelengkapan evidence forensik Bank Sulteng (Aditasi & Internal), serta pantau penyelesaian 27 Point rekomendasi penguatan Bank Sumut.</div>
                        <div class="guide-card-badges">
                            <span class="badge bg-cyan-lt">Sulteng Evidence</span>
                            <span class="badge bg-indigo-lt">Sumut 27 Point</span>
                            <span class="badge bg-blue-lt">List Temuan</span>
                        </div>
                    </div>
                    <div class="pt-2 border-top">
                        <a href="{{ route('sulteng.dashboard') }}" class="btn btn-sm btn-primary w-100 btn-guide-action justify-content-center">
                            Buka Monitoring Assessment <i class="fas fa-arrow-right ms-1"></i>
                        </a>
                    </div>
                </div>

                <div class="guide-card-pro" data-searchable>
                    <div>
                        <div class="guide-card-top">
                            <div class="guide-card-iconbox indigo"><i class="fas fa-clipboard-list"></i></div>
                            <span class="badge bg-indigo-lt fw-bold">Master Risk</span>
                        </div>
                        <div class="guide-card-kicker">Risk Register</div>
                        <div class="guide-card-title">Risk Register & Mass Import XLS</div>
                        <div class="guide-card-route">Risk > Risk Register</div>
                        <div class="guide-card-desc">Hak akses penuh lintas unit untuk mengimpor berkas register Excel, menambahkan risiko baru, mengevaluasi kontrol mitigasi, dan memvalidasi KRI.</div>
                        <div class="guide-card-badges">
                            <span class="badge bg-indigo-lt">Import XLS</span>
                            <span class="badge bg-primary-lt">Full Scope</span>
                            <span class="badge bg-amber-lt">Residual Score</span>
                        </div>
                    </div>
                    <div class="pt-2 border-top">
                        <a href="{{ route('risks.index') }}" class="btn btn-sm btn-primary w-100 btn-guide-action justify-content-center">
                            Buka Risk Register <i class="fas fa-arrow-right ms-1"></i>
                        </a>
                    </div>
                </div>

            @elseif($isOperational)
                {{-- Operational User Pathways --}}
                <div class="guide-card-pro" data-searchable>
                    <div>
                        <div class="guide-card-top">
                            <div class="guide-card-iconbox primary"><i class="fas fa-plus-circle"></i></div>
                            <span class="badge bg-primary-lt fw-bold">Registrasi</span>
                        </div>
                        <div class="guide-card-kicker">Unit Register</div>
                        <div class="guide-card-title">Kelola & Catat Risiko Divisi</div>
                        <div class="guide-card-route">Risk > Risk Register</div>
                        <div class="guide-card-desc">Daftarkan risiko spesifik unit Anda (Identifikasi, Penyebab, Dampak), tautkan kontrol eksisting, dan tentukan rencana penanganan mitigasi.</div>
                        <div class="guide-card-badges">
                            <span class="badge bg-blue-lt">Inherent Score</span>
                            <span class="badge bg-indigo-lt">Action Plan</span>
                            <span class="badge bg-teal-lt">Unit Scope</span>
                        </div>
                    </div>
                    <div class="pt-2 border-top">
                        <a href="{{ route('risks.index') }}" class="btn btn-sm btn-primary w-100 btn-guide-action justify-content-center">
                            Buka Risk Register <i class="fas fa-arrow-right ms-1"></i>
                        </a>
                    </div>
                </div>

                <div class="guide-card-pro" data-searchable>
                    <div>
                        <div class="guide-card-top">
                            <div class="guide-card-iconbox emerald"><i class="fas fa-columns"></i></div>
                            <span class="badge bg-emerald-lt fw-bold">Tindakan</span>
                        </div>
                        <div class="guide-card-kicker">Treatment Execution</div>
                        <div class="guide-card-title">Mitigation Kanban Board</div>
                        <div class="guide-card-route">Risk > Mitigation Kanban</div>
                        <div class="guide-card-desc">Pindahkan kartu mitigasi (To Do, In Progress, Blocked, Done), unggah bukti penanganan (evidence), dan patuhi SLA penyelesaian perbaikan.</div>
                        <div class="guide-card-badges">
                            <span class="badge bg-emerald-lt">Drag & Drop</span>
                            <span class="badge bg-amber-lt">Blocked Reason</span>
                            <span class="badge bg-purple-lt">Upload Evidence</span>
                        </div>
                    </div>
                    <div class="pt-2 border-top">
                        <a href="{{ route('treatments.kanban') }}" class="btn btn-sm btn-primary w-100 btn-guide-action justify-content-center">
                            Buka Mitigation Kanban <i class="fas fa-arrow-right ms-1"></i>
                        </a>
                    </div>
                </div>

                <div class="guide-card-pro" data-searchable>
                    <div>
                        <div class="guide-card-top">
                            <div class="guide-card-iconbox amber"><i class="fas fa-tachometer-alt"></i></div>
                            <span class="badge bg-amber-lt fw-bold">Peringatan</span>
                        </div>
                        <div class="guide-card-kicker">Early Warning</div>
                        <div class="guide-card-title">Key Risk Indicators (KRI)</div>
                        <div class="guide-card-route">Risk > Key Risk Indicators</div>
                        <div class="guide-card-desc">Pantau indikator batas peringatan dini divisi Anda. Input pembacaan metrik berkala untuk mencegah eskalasi status menjadi Merah (Critical).</div>
                        <div class="guide-card-badges">
                            <span class="badge bg-amber-lt">Batas Amber</span>
                            <span class="badge bg-rose-lt">Batas Red</span>
                            <span class="badge bg-blue-lt">Log Pembacaan</span>
                        </div>
                    </div>
                    <div class="pt-2 border-top">
                        <a href="{{ route('kris.index') }}" class="btn btn-sm btn-primary w-100 btn-guide-action justify-content-center">
                            Buka KRI Management <i class="fas fa-arrow-right ms-1"></i>
                        </a>
                    </div>
                </div>

                <div class="guide-card-pro" data-searchable>
                    <div>
                        <div class="guide-card-top">
                            <div class="guide-card-iconbox rose"><i class="fas fa-exclamation-triangle"></i></div>
                            <span class="badge bg-rose-lt fw-bold">Insiden Nyata</span>
                        </div>
                        <div class="guide-card-kicker">Incident Tracking</div>
                        <div class="guide-card-title">Pencatatan Insiden Operasional</div>
                        <div class="guide-card-route">Risk > Incident Management</div>
                        <div class="guide-card-desc">Laporkan insiden nyata yang terjadi, waktu deteksi, tingkat keparahan, tautkan ke risiko terkait, serta evaluasi keefektifan mitigasi darurat.</div>
                        <div class="guide-card-badges">
                            <span class="badge bg-rose-lt">Loss Event</span>
                            <span class="badge bg-indigo-lt">Link to Risk</span>
                            <span class="badge bg-teal-lt">Mitigation Verdict</span>
                        </div>
                    </div>
                    <div class="pt-2 border-top">
                        <a href="{{ route('incidents.index') }}" class="btn btn-sm btn-primary w-100 btn-guide-action justify-content-center">
                            Buka Incident Management <i class="fas fa-arrow-right ms-1"></i>
                        </a>
                    </div>
                </div>

            @else
                {{-- Pending User Pathway --}}
                <div class="guide-card-pro w-100" data-searchable>
                    <div>
                        <div class="guide-card-top">
                            <div class="guide-card-iconbox amber"><i class="fas fa-user-clock"></i></div>
                            <span class="badge bg-warning text-dark fw-bold">Menunggu Otorisasi</span>
                        </div>
                        <div class="guide-card-kicker">Status Pendaftaran</div>
                        <div class="guide-card-title">Akun Anda Dalam Tahap Verifikasi Divisi</div>
                        <div class="guide-card-desc">
                            Akun Anda telah berhasil terdaftar di Raksantara. Sesuai prinsip segregasi tugas (Separation of Duties - POJK 11/2022), Superadmin sistem harus memvalidasi unit kerja Anda sebelum hak akses register dan analitik dibuka.
                        </div>
                        <div class="guide-steps-list">
                            <div class="guide-steps-title">Langkah yang Perlu Dilakukan:</div>
                            <div class="guide-step-item">
                                <div class="guide-step-num">1</div>
                                <div>Hubungi Administrator Sistem atau PIC Divisi Compliance institusi Anda.</div>
                            </div>
                            <div class="guide-step-item">
                                <div class="guide-step-num">2</div>
                                <div>Informasikan username Anda (<code>{{ $viewerName }}</code>) dan nama divisi yang Anda wakili.</div>
                            </div>
                            <div class="guide-step-item">
                                <div class="guide-step-num">3</div>
                                <div>Setelah Superadmin mengesahkan divisi, lakukan refresh halaman untuk mengakses menu penuh.</div>
                            </div>
                        </div>
                    </div>
                    <div class="pt-2 border-top">
                        <a href="{{ route('change.password') }}" class="btn btn-sm btn-outline-primary btn-guide-action">
                            <i class="fas fa-key me-1"></i> Atur Password & Keamanan Akun
                        </a>
                    </div>
                </div>
            @endif
        </div>
    </div>

    {{-- TAB 2: ROLE & MATRIKS HAK AKSES --}}
    <div class="tab-pane fade" id="guide-role-pane" role="tabpanel" aria-labelledby="guide-role-tab">
        <div class="guide-pane-header">
            <div class="guide-pane-heading">Matriks Hak Akses & Pembagian Tanggung Jawab (RBAC)</div>
            <div class="guide-pane-sub">Segregasi tugas perbankan mengacu pada POJK 11/2022 dan ISO/IEC 27001:2022 Control A.5.3.</div>
        </div>

        <div class="guide-grid">
            {{-- Superadmin Role Card --}}
            <div class="guide-card-pro" data-searchable>
                <div>
                    <div class="guide-card-top">
                        <div class="guide-card-iconbox purple"><i class="fas fa-crown"></i></div>
                        <span class="badge bg-purple text-white">Superadmin</span>
                    </div>
                    <div class="guide-card-title">Superadmin (Tata Kelola Sistem)</div>
                    <div class="guide-card-desc">Pemegang otoritas tertinggi konfigurasi sistem, validasi pengguna, parameter governance, dan maker-checker persetujuan risiko.</div>
                    
                    <div class="guide-steps-list">
                        <div class="guide-steps-title">Cakupan Menu & Otoritas:</div>
                        <div class="guide-step-item"><i class="fas fa-check text-success me-2 mt-1"></i><span><strong>App Management:</strong> Verifikasi divisi user, reset password, audit trail, data purge.</span></div>
                        <div class="guide-step-item"><i class="fas fa-check text-success me-2 mt-1"></i><span><strong>Status Approvals:</strong> Maker-checker permohonan status risiko (Approve/Reject).</span></div>
                        <div class="guide-step-item"><i class="fas fa-check text-success me-2 mt-1"></i><span><strong>Analisis Kepatuhan:</strong> Akses penuh Cross-Mapping, Gap Analysis, Checklist, Simulator.</span></div>
                        <div class="guide-step-item"><i class="fas fa-check text-success me-2 mt-1"></i><span><strong>Monitoring Assessment:</strong> Pengawalan evidence forensik Sulteng & Sumut 27 Point.</span></div>
                    </div>
                </div>
                <div class="guide-card-badges">
                    <span class="badge bg-purple-lt">Scope Sistem Penuh</span>
                    <span class="badge bg-dark-lt">Governance Level</span>
                </div>
            </div>

            {{-- Compliance Role Card --}}
            <div class="guide-card-pro" data-searchable>
                <div>
                    <div class="guide-card-top">
                        <div class="guide-card-iconbox emerald"><i class="fas fa-balance-scale"></i></div>
                        <span class="badge bg-green text-white">Compliance</span>
                    </div>
                    <div class="guide-card-title">Divisi Compliance (Application Owner)</div>
                    <div class="guide-card-desc">Pengelola seluruh profil risiko korporasi, pengawas kesesuaian regulasi, dan eksekutor evaluasi kepatuhan mandiri.</div>
                    
                    <div class="guide-steps-list">
                        <div class="guide-steps-title">Cakupan Menu & Otoritas:</div>
                        <div class="guide-step-item"><i class="fas fa-check text-success me-2 mt-1"></i><span><strong>Full Risk Register:</strong> Hak baca dan kelola risiko seluruh unit, mass import XLS.</span></div>
                        <div class="guide-step-item"><i class="fas fa-check text-success me-2 mt-1"></i><span><strong>Analisis Kepatuhan:</strong> Ekspor lembar kerja Checklist mandiri Excel, simulasi audit.</span></div>
                        <div class="guide-step-item"><i class="fas fa-check text-success me-2 mt-1"></i><span><strong>Monitoring Regional:</strong> Update tindak lanjut temuan audit Sulteng & Sumut.</span></div>
                        <div class="guide-step-item"><i class="fas fa-check text-success me-2 mt-1"></i><span><strong>Program Kerja:</strong> Pengelolaan inisiatif Internal Audit, Risk, dan Tata Kelola.</span></div>
                    </div>
                </div>
                <div class="guide-card-badges">
                    <span class="badge bg-emerald-lt">Lintas Seluruh Divisi</span>
                    <span class="badge bg-blue-lt">Application Owner</span>
                </div>
            </div>

            {{-- Operational Role Card --}}
            <div class="guide-card-pro" data-searchable>
                <div>
                    <div class="guide-card-top">
                        <div class="guide-card-iconbox primary"><i class="fas fa-user-check"></i></div>
                        <span class="badge bg-blue text-white">Verified User</span>
                    </div>
                    <div class="guide-card-title">Divisi Operasional (Risk Owner)</div>
                    <div class="guide-card-desc">Unit kerja pemilik risiko operasional (IT, Operasional, Bisnis, Keuangan) yang bertanggung jawab atas mitigasi harian.</div>
                    
                    <div class="guide-steps-list">
                        <div class="guide-steps-title">Cakupan Menu & Otoritas:</div>
                        <div class="guide-step-item"><i class="fas fa-check text-success me-2 mt-1"></i><span><strong>Unit Workspace:</strong> Risk Register, Heatmap, KRI, dan Kanban mitigasi divisi sendiri.</span></div>
                        <div class="guide-step-item"><i class="fas fa-check text-success me-2 mt-1"></i><span><strong>Incident Management:</strong> Melaporkan insiden siber/operasional dan mencatat kronologi.</span></div>
                        <div class="guide-step-item"><i class="fas fa-check text-success me-2 mt-1"></i><span><strong>Regulasi & Threat Intel:</strong> Eksplorasi 8 Regulasi, 6 Standar Global, dan live CVE CISA.</span></div>
                        <div class="guide-step-item"><i class="fas fa-lock text-danger me-2 mt-1"></i><span><em>Analisis Kepatuhan & Monitoring Assessment dibatasi (khusus Compliance/Admin).</em></span></div>
                    </div>
                </div>
                <div class="guide-card-badges">
                    <span class="badge bg-blue-lt">Scope Unit Sendiri</span>
                    <span class="badge bg-slate-lt">First Line of Defense</span>
                </div>
            </div>

            {{-- Pending Role Card --}}
            <div class="guide-card-pro" data-searchable>
                <div>
                    <div class="guide-card-top">
                        <div class="guide-card-iconbox amber"><i class="fas fa-user-clock"></i></div>
                        <span class="badge bg-warning text-dark">Pending</span>
                    </div>
                    <div class="guide-card-title">Pending Approval (Baru Mendaftar)</div>
                    <div class="guide-card-desc">Akun baru yang belum disahkan divisi kerjanya oleh administrator sistem.</div>
                    
                    <div class="guide-steps-list">
                        <div class="guide-steps-title">Kondisi Akses:</div>
                        <div class="guide-step-item"><i class="fas fa-times text-danger me-2 mt-1"></i><span>Tidak memiliki akses data register risiko perbankan.</span></div>
                        <div class="guide-step-item"><i class="fas fa-times text-danger me-2 mt-1"></i><span>Tidak dapat membaca intelijen ancaman siber internal.</span></div>
                        <div class="guide-step-item"><i class="fas fa-check text-success me-2 mt-1"></i><span>Dapat mengakses form Change Password dan petunjuk aktivasi akun.</span></div>
                    </div>
                </div>
                <div class="guide-card-badges">
                    <span class="badge bg-amber-lt">Akses Dibatasi</span>
                    <span class="badge bg-secondary-lt">Menunggu Otorisasi</span>
                </div>
            </div>
        </div>
    </div>

    {{-- TAB 3: REGULASI NASIONAL & STANDAR GLOBAL --}}
    @if($showFrameworks)
    <div class="tab-pane fade" id="guide-frameworks-pane" role="tabpanel" aria-labelledby="guide-frameworks-tab">
        <div class="guide-pane-header">
            <div class="guide-pane-heading">Basis Regulasi Nasional (8) & Standar Keamanan Global (6)</div>
            <div class="guide-pane-sub">Klik tombol "Buka Regulasi" pada kartu untuk membaca teks pasal, lampiran, dan kontrol teknis.</div>
        </div>

        {{-- 8 REGULASI NASIONAL --}}
        <div class="d-flex align-items-center justify-content-between mb-3 mt-2">
            <h3 class="h4 text-dark mb-0"><i class="fas fa-landmark text-primary me-2"></i> Regulasi Nasional Sektor Keuangan</h3>
            <span class="badge bg-primary-lt">8 Regulasi Terintegrasi</span>
        </div>

        <div class="guide-grid mb-4">
            <div class="guide-card-pro" data-searchable>
                <div>
                    <div class="guide-card-top">
                        <div class="guide-card-iconbox primary"><i class="fas fa-university"></i></div>
                        <span class="badge bg-primary-lt fw-bold">OJK Bank Umum</span>
                    </div>
                    <div class="guide-card-kicker">POJK 11/POJK.03/2022</div>
                    <div class="guide-card-title">Penyelenggaraan Teknologi Informasi Bank Umum</div>
                    <div class="guide-card-desc">Regulasi payung tata kelola TI perbankan: arsitektur TI, ketahanan operasional, audit berkala, dan manajemen risiko pihak ketiga.</div>
                    <div class="guide-card-badges">
                        <span class="badge bg-blue-lt">Pasal 28 SLA KEV</span>
                        <span class="badge bg-indigo-lt">Tata Kelola TI</span>
                        <span class="badge bg-emerald-lt">Third-Party Risk</span>
                    </div>
                </div>
                <div class="pt-2 border-top">
                    <a href="{{ route('frameworks.pojk') }}" class="btn btn-sm btn-outline-primary w-100 btn-guide-action justify-content-center">
                        Buka POJK 11/2022 <i class="fas fa-external-link-alt ms-1"></i>
                    </a>
                </div>
            </div>

            <div class="guide-card-pro" data-searchable>
                <div>
                    <div class="guide-card-top">
                        <div class="guide-card-iconbox primary"><i class="fas fa-file-signature"></i></div>
                        <span class="badge bg-primary-lt fw-bold">Standar OJK 2026</span>
                    </div>
                    <div class="guide-card-kicker">PADK 1/PADK.03/2026</div>
                    <div class="guide-card-title">Standar Operasional Ketahanan & Siber</div>
                    <div class="guide-card-desc">Petunjuk teknis mendalam: kewajiban SLA patching kerentanan kritis KEV maksimal 14 hari, segmentasi jaringan, zero trust, dan MFA.</div>
                    <div class="guide-card-badges">
                        <span class="badge bg-rose-lt">SLA Patching 14 Hari</span>
                        <span class="badge bg-purple-lt">Zero Trust Architecture</span>
                        <span class="badge bg-cyan-lt">WAF & Perimeter</span>
                    </div>
                </div>
                <div class="pt-2 border-top">
                    <a href="{{ route('frameworks.padk') }}" class="btn btn-sm btn-outline-primary w-100 btn-guide-action justify-content-center">
                        Buka PADK 1/2026 <i class="fas fa-external-link-alt ms-1"></i>
                    </a>
                </div>
            </div>

            <div class="guide-card-pro" data-searchable>
                <div>
                    <div class="guide-card-top">
                        <div class="guide-card-iconbox primary"><i class="fas fa-file-contract"></i></div>
                        <span class="badge bg-primary-lt fw-bold">Surat Edaran OJK</span>
                    </div>
                    <div class="guide-card-kicker">SEOJK 29/SEOJK.03/2022</div>
                    <div class="guide-card-title">Ketahanan & Keamanan Siber Bank Umum</div>
                    <div class="guide-card-desc">Panduan pembentukan Cyber Incident Response Team (CSIRT), pemantauan Security Operations Center (SOC 24/7), dan skenario simulasi serangan siber.</div>
                    <div class="guide-card-badges">
                        <span class="badge bg-amber-lt">CSIRT Incident Flow</span>
                        <span class="badge bg-indigo-lt">SOC Monitoring</span>
                        <span class="badge bg-teal-lt">Simulasi Siber</span>
                    </div>
                </div>
                <div class="pt-2 border-top">
                    <a href="{{ route('frameworks.seojk') }}" class="btn btn-sm btn-outline-primary w-100 btn-guide-action justify-content-center">
                        Buka SEOJK 29/2022 <i class="fas fa-external-link-alt ms-1"></i>
                    </a>
                </div>
            </div>

            <div class="guide-card-pro" data-searchable>
                <div>
                    <div class="guide-card-top">
                        <div class="guide-card-iconbox cyan"><i class="fas fa-heartbeat"></i></div>
                        <span class="badge bg-cyan-lt fw-bold">Ketahanan Operasional</span>
                    </div>
                    <div class="guide-card-kicker">Panduan OJK</div>
                    <div class="guide-card-title">Panduan Resiliensi Digital Perbankan</div>
                    <div class="guide-card-desc">Kerangka pengujian ketahanan operasional terpadu: skenario pemadaman listrik total, ransomware skala luas, dan kegagalan data center.</div>
                    <div class="guide-card-badges">
                        <span class="badge bg-cyan-lt">Extreme Scenario Test</span>
                        <span class="badge bg-blue-lt">RTO & RPO Mandate</span>
                        <span class="badge bg-emerald-lt">DR Drill</span>
                    </div>
                </div>
                <div class="pt-2 border-top">
                    <a href="{{ route('frameworks.cis') }}" class="btn btn-sm btn-outline-primary w-100 btn-guide-action justify-content-center">
                        Buka Panduan Resiliensi <i class="fas fa-external-link-alt ms-1"></i>
                    </a>
                </div>
            </div>

            <div class="guide-card-pro" data-searchable>
                <div>
                    <div class="guide-card-top">
                        <div class="guide-card-iconbox emerald"><i class="fas fa-gavel"></i></div>
                        <span class="badge bg-emerald-lt fw-bold">Bank Indonesia</span>
                    </div>
                    <div class="guide-card-kicker">PBI 02/2024</div>
                    <div class="guide-card-title">Penyelenggaraan Sistem Pembayaran (SP)</div>
                    <div class="guide-card-desc">Standar pengawasan Bank Indonesia terhadap kelaikan sistem pemrosesan transaksi keuangan, mitigasi fraud, dan keamanan API perbankan.</div>
                    <div class="guide-card-badges">
                        <span class="badge bg-emerald-lt">Payment System</span>
                        <span class="badge bg-teal-lt">Anti-Fraud Controls</span>
                        <span class="badge bg-indigo-lt">Open API Governance</span>
                    </div>
                </div>
                <div class="pt-2 border-top">
                    <a href="{{ route('frameworks.pbi') }}" class="btn btn-sm btn-outline-primary w-100 btn-guide-action justify-content-center">
                        Buka PBI 02/2024 <i class="fas fa-external-link-alt ms-1"></i>
                    </a>
                </div>
            </div>

            <div class="guide-card-pro" data-searchable>
                <div>
                    <div class="guide-card-top">
                        <div class="guide-card-iconbox emerald"><i class="fas fa-credit-card"></i></div>
                        <span class="badge bg-emerald-lt fw-bold">Anggota Dewan Gubernur BI</span>
                    </div>
                    <div class="guide-card-kicker">PADG 32/2025</div>
                    <div class="guide-card-title">Ketentuan Pemrosesan Transaksi Pembayaran</div>
                    <div class="guide-card-desc">Spesifikasi enkripsi transmisi data kartu/rekening, standar verifikasi transaksi real-time, dan tata cara penanganan anomali transfer.</div>
                    <div class="guide-card-badges">
                        <span class="badge bg-emerald-lt">Transaction Encryption</span>
                        <span class="badge bg-blue-lt">Real-time Clearing</span>
                    </div>
                </div>
                <div class="pt-2 border-top">
                    <a href="{{ route('frameworks.padg') }}" class="btn btn-sm btn-outline-primary w-100 btn-guide-action justify-content-center">
                        Buka PADG 32/2025 <i class="fas fa-external-link-alt ms-1"></i>
                    </a>
                </div>
            </div>

            <div class="guide-card-pro" data-searchable>
                <div>
                    <div class="guide-card-top">
                        <div class="guide-card-iconbox emerald"><i class="fas fa-exchange-alt"></i></div>
                        <span class="badge bg-emerald-lt fw-bold">Penyelenggara Jasa Pembayaran</span>
                    </div>
                    <div class="guide-card-kicker">PADG 24/2024</div>
                    <div class="guide-card-title">Pengelolaan Risiko Siber PJP</div>
                    <div class="guide-card-desc">Pengendalian risiko siber spesifik institusi gerbang pembayaran (Payment Gateway) dan switching: autentikasi ganda & mitigasi skimming.</div>
                    <div class="guide-card-badges">
                        <span class="badge bg-emerald-lt">Gateway Protection</span>
                        <span class="badge bg-purple-lt">Switching Security</span>
                    </div>
                </div>
                <div class="pt-2 border-top">
                    <a href="{{ route('frameworks.padg24') }}" class="btn btn-sm btn-outline-primary w-100 btn-guide-action justify-content-center">
                        Buka PADG 24/2024 <i class="fas fa-external-link-alt ms-1"></i>
                    </a>
                </div>
            </div>

            <div class="guide-card-pro" data-searchable>
                <div>
                    <div class="guide-card-top">
                        <div class="guide-card-iconbox rose"><i class="fas fa-user-shield"></i></div>
                        <span class="badge bg-rose-lt fw-bold">Undang-Undang RI</span>
                    </div>
                    <div class="guide-card-kicker">UU No. 27 Tahun 2022</div>
                    <div class="guide-card-title">Pelindungan Data Pribadi (UU PDP)</div>
                    <div class="guide-card-desc">Dasar hukum pemrosesan data nasabah, hak subjek data, serta mandat krusial Pasal 46: <strong>notifikasi insiden kebocoran wajib 3x24 jam</strong>.</div>
                    <div class="guide-card-badges">
                        <span class="badge bg-rose-lt">Pasal 46 Notifikasi 3x24 Jam</span>
                        <span class="badge bg-indigo-lt">DPO Obligation</span>
                        <span class="badge bg-amber-lt">Denda Administratif</span>
                    </div>
                </div>
                <div class="pt-2 border-top">
                    <a href="{{ route('frameworks.pdp') }}" class="btn btn-sm btn-outline-primary w-100 btn-guide-action justify-content-center">
                        Buka UU PDP 27/2022 <i class="fas fa-external-link-alt ms-1"></i>
                    </a>
                </div>
            </div>
        </div>

        {{-- 6 STANDAR GLOBAL --}}
        <div class="d-flex align-items-center justify-content-between mb-3 mt-4">
            <h3 class="h4 text-dark mb-0"><i class="fas fa-globe text-indigo me-2"></i> Standar Keamanan & Tata Kelola Global</h3>
            <span class="badge bg-indigo-lt">6 Standar Terintegrasi</span>
        </div>

        <div class="guide-grid">
            <div class="guide-card-pro" data-searchable>
                <div>
                    <div class="guide-card-top">
                        <div class="guide-card-iconbox indigo"><i class="fas fa-shield-alt"></i></div>
                        <span class="badge bg-indigo-lt fw-bold">ISO / IEC</span>
                    </div>
                    <div class="guide-card-kicker">ISO/IEC 27001:2022</div>
                    <div class="guide-card-title">Information Security Management (ISMS)</div>
                    <div class="guide-card-desc">93 Kontrol Annex A dalam 4 Tema: Organisasi (37), Personel (8), Fisik (14), dan Teknologi (34). Termasuk Kontrol A.8.8 Manajemen Kerentanan.</div>
                    <div class="guide-card-badges">
                        <span class="badge bg-indigo-lt">93 Kontrol Annex A</span>
                        <span class="badge bg-blue-lt">Threat Intel A.5.7</span>
                        <span class="badge bg-purple-lt">Vulnerability A.8.8</span>
                    </div>
                </div>
                <div class="pt-2 border-top">
                    <a href="{{ route('frameworks.iso27001') }}" class="btn btn-sm btn-outline-primary w-100 btn-guide-action justify-content-center">
                        Buka ISO 27001:2022 <i class="fas fa-external-link-alt ms-1"></i>
                    </a>
                </div>
            </div>

            <div class="guide-card-pro" data-searchable>
                <div>
                    <div class="guide-card-top">
                        <div class="guide-card-iconbox emerald"><i class="fas fa-handshake"></i></div>
                        <span class="badge bg-emerald-lt fw-bold">Anti-Bribery</span>
                    </div>
                    <div class="guide-card-kicker">ISO 37001:2016</div>
                    <div class="guide-card-title">Anti-Bribery Management System (ABMS)</div>
                    <div class="guide-card-desc">Uji kelayakan (due diligence) pihak ketiga, kontrol transaksi keuangan, sistem whistleblower, dan pencegahan benturan kepentingan.</div>
                    <div class="guide-card-badges">
                        <span class="badge bg-emerald-lt">Vendor Due Diligence</span>
                        <span class="badge bg-teal-lt">Whistleblower</span>
                    </div>
                </div>
                <div class="pt-2 border-top">
                    <a href="{{ route('frameworks.iso37001') }}" class="btn btn-sm btn-outline-primary w-100 btn-guide-action justify-content-center">
                        Buka ISO 37001:2016 <i class="fas fa-external-link-alt ms-1"></i>
                    </a>
                </div>
            </div>

            <div class="guide-card-pro" data-searchable>
                <div>
                    <div class="guide-card-top">
                        <div class="guide-card-iconbox cyan"><i class="fas fa-sitemap"></i></div>
                        <span class="badge bg-cyan-lt fw-bold">NIST Cyber</span>
                    </div>
                    <div class="guide-card-kicker">NIST CSF 2.0</div>
                    <div class="guide-card-title">Cybersecurity Framework 2.0</div>
                    <div class="guide-card-desc">6 Fungsi Inti Modern: <em>Govern (GV)</em>, <em>Identify (ID)</em>, <em>Protect (PR)</em>, <em>Detect (DE)</em>, <em>Respond (RS)</em>, dan <em>Recover (RC)</em>.</div>
                    <div class="guide-card-badges">
                        <span class="badge bg-cyan-lt">Govern Core Function</span>
                        <span class="badge bg-blue-lt">Incident Response</span>
                    </div>
                </div>
                <div class="pt-2 border-top">
                    <a href="{{ route('frameworks.nist') }}" class="btn btn-sm btn-outline-primary w-100 btn-guide-action justify-content-center">
                        Buka NIST CSF 2.0 <i class="fas fa-external-link-alt ms-1"></i>
                    </a>
                </div>
            </div>

            <div class="guide-card-pro" data-searchable>
                <div>
                    <div class="guide-card-top">
                        <div class="guide-card-iconbox purple"><i class="fas fa-project-diagram"></i></div>
                        <span class="badge bg-purple-lt fw-bold">ISACA Governance</span>
                    </div>
                    <div class="guide-card-kicker">COBIT 2019</div>
                    <div class="guide-card-title">Governance & Management of Enterprise IT</div>
                    <div class="guide-card-desc">40 Governance and Management Objectives lintas domain EDM, APO, BAI, DSS, dan MEA untuk penyelarasan strategi bisnis TI.</div>
                    <div class="guide-card-badges">
                        <span class="badge bg-purple-lt">40 Objectives</span>
                        <span class="badge bg-indigo-lt">EDM & DSS Domains</span>
                    </div>
                </div>
                <div class="pt-2 border-top">
                    <a href="{{ route('frameworks.cobit') }}" class="btn btn-sm btn-outline-primary w-100 btn-guide-action justify-content-center">
                        Buka COBIT 2019 <i class="fas fa-external-link-alt ms-1"></i>
                    </a>
                </div>
            </div>

            <div class="guide-card-pro" data-searchable>
                <div>
                    <div class="guide-card-top">
                        <div class="guide-card-iconbox rose"><i class="fas fa-bug"></i></div>
                        <span class="badge bg-rose-lt fw-bold">App Security</span>
                    </div>
                    <div class="guide-card-kicker">OWASP Top 10 (2025)</div>
                    <div class="guide-card-title">Top 10 Web Application Security Risks</div>
                    <div class="guide-card-desc">10 celah keamanan web paling kritis: Broken Access Control, Cryptographic Failures, Injection, Insecure Design, dan Misconfiguration.</div>
                    <div class="guide-card-badges">
                        <span class="badge bg-rose-lt">A01 Broken Access</span>
                        <span class="badge bg-amber-lt">A03 Injection</span>
                        <span class="badge bg-teal-lt">Defensive Playbook</span>
                    </div>
                </div>
                <div class="pt-2 border-top">
                    <a href="{{ route('frameworks.owasp-top10') }}" class="btn btn-sm btn-outline-primary w-100 btn-guide-action justify-content-center">
                        Buka OWASP Top 10 <i class="fas fa-external-link-alt ms-1"></i>
                    </a>
                </div>
            </div>

            <div class="guide-card-pro" data-searchable>
                <div>
                    <div class="guide-card-top">
                        <div class="guide-card-iconbox amber"><i class="fas fa-check-double"></i></div>
                        <span class="badge bg-amber-lt fw-bold">Verifikasi Teknis</span>
                    </div>
                    <div class="guide-card-kicker">OWASP ASVS 5.0.0</div>
                    <div class="guide-card-title">Application Security Verification Standard</div>
                    <div class="guide-card-desc">Standar checklist audit aplikasi perbankan: Level 1 (Oportunistik), Level 2 (Standar Industri Finansial), dan Level 3 (Infrastruktur Kritis).</div>
                    <div class="guide-card-badges">
                        <span class="badge bg-amber-lt">Level 2 Perbankan</span>
                        <span class="badge bg-indigo-lt">Verification Checklist</span>
                    </div>
                </div>
                <div class="pt-2 border-top">
                    <a href="{{ route('frameworks.owasp-asvs') }}" class="btn btn-sm btn-outline-primary w-100 btn-guide-action justify-content-center">
                        Buka OWASP ASVS <i class="fas fa-external-link-alt ms-1"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- TAB 4: RISK WORKSPACE --}}
    @if($isOperational)
    <div class="tab-pane fade" id="guide-risk-pane" role="tabpanel" aria-labelledby="guide-risk-tab">
        <div class="guide-pane-header">
            <div class="guide-pane-heading">Risk Workspace & Modul Operasional</div>
            <div class="guide-pane-sub">Panduan langkah-demi-langkah pengisian, pengelolaan mitigasi harian, dan monitoring risiko divisi.</div>
        </div>

        <div class="guide-grid">
            {{-- Risk Register --}}
            <div class="guide-card-pro" data-searchable>
                <div>
                    <div class="guide-card-top">
                        <div class="guide-card-iconbox primary"><i class="fas fa-table"></i></div>
                        <span class="badge bg-primary-lt fw-bold">Register Utama</span>
                    </div>
                    <div class="guide-card-kicker">Feature 1</div>
                    <div class="guide-card-title">Risk Register Workspace</div>
                    <div class="guide-card-route">Risk > Risk Register</div>
                    <div class="guide-card-desc">Pusat pencatatan risiko unit Anda. Dilengkapi tab Dashboard 1 (Register), Dashboard 2 (Heat Map), dan Dashboard 3 (Rekapitulasi).</div>

                    <div class="guide-steps-list">
                        <div class="guide-steps-title">Alur Kerja 6 Bagian Form Risk:</div>
                        <div class="guide-step-item"><div class="guide-step-num">1</div><span><strong>Data Dasar:</strong> Judul risiko, kode, kategori, risk owner, deskripsi pemicu.</span></div>
                        <div class="guide-step-item"><div class="guide-step-num">2</div><span><strong>Inherent Risk:</strong> Probabilitas x Dampak (Kamus 1–5), Inherent Risk Score.</span></div>
                        <div class="guide-step-item"><div class="guide-step-num">3</div><span><strong>Existing Control:</strong> Efektivitas kontrol saat ini (Effective, Partial, Ineffective).</span></div>
                        <div class="guide-step-item"><div class="guide-step-num">4</div><span><strong>Mitigation Action:</strong> Tindakan perbaikan, PIC, deadline, offset follow-up.</span></div>
                        <div class="guide-step-item"><div class="guide-step-num">5</div><span><strong>Residual Analysis:</strong> Skor risiko setelah kontrol mitigasi dijalankan.</span></div>
                        <div class="guide-step-item"><div class="guide-step-num">6</div><span><strong>Kuantitatif (Rp):</strong> Potensi kerugian moneter inheren vs residual.</span></div>
                    </div>

                    <details class="guide-details">
                        <summary class="guide-details-summary">
                            <span><i class="fas fa-sliders-h me-1 text-primary"></i> Kamus Field & Tombol Registrasi</span>
                            <i class="fas fa-chevron-down arrow-icon"></i>
                        </summary>
                        <div class="guide-details-content">
                            <table class="guide-subtable">
                                <thead><tr><th>Tombol/Field</th><th>Fungsi</th></tr></thead>
                                <tbody>
                                    <tr><td><strong>Add Risk</strong></td><td>Membuka wizard pembuatan risiko 6 tab lengkap.</td></tr>
                                    <tr><td><strong>Filter Owner</strong></td><td>Menyaring register berdasarkan unit kerja/owner.</td></tr>
                                    @if($isCompliance)
                                    <tr><td><strong>Import XLS</strong></td><td>Mengunggah file excel register massal dengan validasi format.</td></tr>
                                    @endif
                                    <tr><td><strong>Heat Map</strong></td><td>Visualisasi matriks 5x5 pergeseran inherent ke residual.</td></tr>
                                    <tr><td><strong>Export Report</strong></td><td>Mengekspor tabel register atau rekapitulasi ke file Excel.</td></tr>
                                </tbody>
                            </table>
                        </div>
                    </details>
                </div>
                <div class="pt-2 border-top">
                    <a href="{{ route('risks.index') }}" class="btn btn-sm btn-primary w-100 btn-guide-action justify-content-center">
                        Buka Risk Register <i class="fas fa-arrow-right ms-1"></i>
                    </a>
                </div>
            </div>

            {{-- Mitigation Kanban --}}
            <div class="guide-card-pro" data-searchable>
                <div>
                    <div class="guide-card-top">
                        <div class="guide-card-iconbox emerald"><i class="fas fa-columns"></i></div>
                        <span class="badge bg-emerald-lt fw-bold">Eksekusi Harian</span>
                    </div>
                    <div class="guide-card-kicker">Feature 2</div>
                    <div class="guide-card-title">Mitigation Kanban Board</div>
                    <div class="guide-card-route">Risk > Mitigation Kanban</div>
                    <div class="guide-card-desc">Papan visual pengelolaan tindakan mitigasi harian. Menerapkan disiplin blocked reason, bukti penanganan (evidence), dan deadline SLA.</div>

                    <div class="guide-steps-list">
                        <div class="guide-steps-title">Kolom Status & Aturan Operasional:</div>
                        <div class="guide-step-item"><div class="guide-step-num">1</div><span><strong>Not Started:</strong> Rencana aksi mitigasi yang baru dibuat dan belum dieksekusi.</span></div>
                        <div class="guide-step-item"><div class="guide-step-num">2</div><span><strong>In Progress:</strong> Aksi yang sedang dikerjakan PIC terkait.</span></div>
                        <div class="guide-step-item"><div class="guide-step-num">3</div><span><strong>Blocked:</strong> Aksi tertahan (Wajib mencantumkan <em>Blocked Reason</em>).</span></div>
                        <div class="guide-step-item"><div class="guide-step-num">4</div><span><strong>Completed:</strong> Selesai (Wajib menyertakan bukti lampiran <em>Evidence</em>).</span></div>
                    </div>

                    <details class="guide-details">
                        <summary class="guide-details-summary">
                            <span><i class="fas fa-sliders-h me-1 text-primary"></i> Indikator Chip Pada Kartu Kanban</span>
                            <i class="fas fa-chevron-down arrow-icon"></i>
                        </summary>
                        <div class="guide-details-content">
                            <table class="guide-subtable">
                                <thead><tr><th>Chip Indikator</th><th>Arti & Ketentuan</th></tr></thead>
                                <tbody>
                                    <tr><td><strong>SLA Chip</strong></td><td>Status batas waktu: Overdue (Merah), Due Soon (Kuning), On Track (Biru).</td></tr>
                                    <tr><td><strong>Aging Chip</strong></td><td>Menunjukkan berapa hari kartu berada di status saat ini.</td></tr>
                                    <tr><td><strong>Evidence Chip</strong></td><td>Jumlah dokumen bukti pendukung yang terunggah.</td></tr>
                                    <tr><td><strong>Drag and Drop</strong></td><td>Tarik dan letakkan kartu antar kolom untuk mengubah status secara instan.</td></tr>
                                </tbody>
                            </table>
                        </div>
                    </details>
                </div>
                <div class="pt-2 border-top">
                    <a href="{{ route('treatments.kanban') }}" class="btn btn-sm btn-primary w-100 btn-guide-action justify-content-center">
                        Buka Mitigation Kanban <i class="fas fa-arrow-right ms-1"></i>
                    </a>
                </div>
            </div>

            {{-- KRI Management --}}
            <div class="guide-card-pro" data-searchable>
                <div>
                    <div class="guide-card-top">
                        <div class="guide-card-iconbox amber"><i class="fas fa-tachometer-alt"></i></div>
                        <span class="badge bg-amber-lt fw-bold">Early Warning</span>
                    </div>
                    <div class="guide-card-kicker">Feature 3</div>
                    <div class="guide-card-title">Key Risk Indicators (KRI)</div>
                    <div class="guide-card-route">Risk > Key Risk Indicators</div>
                    <div class="guide-card-desc">Sistem peringatan dini risiko. Setiap KRI memiliki batas Amber (Warning) dan Red (Critical) untuk mendeteksi potensi pembengkakan risiko sebelum terjadi.</div>

                    <div class="guide-steps-list">
                        <div class="guide-steps-title">Langkah Pengelolaan KRI:</div>
                        <div class="guide-step-item"><div class="guide-step-num">1</div><span>Pilih risiko yang ingin dipantau dari daftar KRI.</span></div>
                        <div class="guide-step-item"><div class="guide-step-num">2</div><span>Tetapkan metrik pengukuran, satuan (%, count, hari), dan frekuensi update.</span></div>
                        <div class="guide-step-item"><div class="guide-step-num">3</div><span>Tentukan nilai batas Warning (Amber) dan Kritis (Red).</span></div>
                        <div class="guide-step-item"><div class="guide-step-num">4</div><span>Lakukan input log nilai berkala untuk memicu alert governance bila melampaui batas.</span></div>
                    </div>
                </div>
                <div class="pt-2 border-top">
                    <a href="{{ route('kris.index') }}" class="btn btn-sm btn-primary w-100 btn-guide-action justify-content-center">
                        Buka KRI Management <i class="fas fa-arrow-right ms-1"></i>
                    </a>
                </div>
            </div>

            {{-- Business Continuity Management (BCM) --}}
            <div class="guide-card-pro" data-searchable>
                <div>
                    <div class="guide-card-top">
                        <div class="guide-card-iconbox cyan"><i class="fas fa-life-ring"></i></div>
                        <span class="badge bg-cyan-lt fw-bold">ISO 22301</span>
                    </div>
                    <div class="guide-card-kicker">Feature 4</div>
                    <div class="guide-card-title">Business Continuity (BCM)</div>
                    <div class="guide-card-route">Risk > Business Continuity (BCM)</div>
                    <div class="guide-card-desc">Dikelola <strong>per layanan bisnis</strong> (bukan per risiko). Meliputi Business Impact Analysis (BIA), penetapan MTPD/RTO, dokumen BCP, dan pencatatan uji DR.</div>

                    <div class="guide-steps-list">
                        <div class="guide-steps-title">Alur Siklus BCM:</div>
                        <div class="guide-step-item"><div class="guide-step-num">1</div><span><strong>Katalog Layanan:</strong> Daftarkan layanan inti, pemilik unit, dan dependensi IT.</span></div>
                        <div class="guide-step-item"><div class="guide-step-num">2</div><span><strong>Isi BIA:</strong> Nilai dampak berhenti 1 jam, 4 jam, 24 jam. Tentukan MTPD & RTO.</span></div>
                        <div class="guide-step-item"><div class="guide-step-num">3</div><span><strong>Persetujuan BIA:</strong> Disahkan pengelola unit selain pengisi (Maker-Checker).</span></div>
                        <div class="guide-step-item"><div class="guide-step-num">4</div><span><strong>Uji BCP:</strong> Catat hasil uji berkala (Tabletop, Simulasi, Full DR failover).</span></div>
                    </div>
                </div>
                <div class="pt-2 border-top">
                    <a href="{{ route('bcm.index') }}" class="btn btn-sm btn-primary w-100 btn-guide-action justify-content-center">
                        Buka Modul BCM <i class="fas fa-arrow-right ms-1"></i>
                    </a>
                </div>
            </div>

            {{-- Third-Party Vendors --}}
            <div class="guide-card-pro" data-searchable>
                <div>
                    <div class="guide-card-top">
                        <div class="guide-card-iconbox indigo"><i class="fas fa-handshake"></i></div>
                        <span class="badge bg-indigo-lt fw-bold">Pihak Ketiga</span>
                    </div>
                    <div class="guide-card-kicker">Feature 5</div>
                    <div class="guide-card-title">Third-Party Vendors</div>
                    <div class="guide-card-route">Sidebar > Third-Party Vendors</div>
                    <div class="guide-card-desc">Pendataan mitra vendor eksternal, level kritikalitas (Low, Medium, High), kontak PIC, dan penautan langsung ke risiko ketergantungan vendor.</div>
                    <div class="guide-card-badges">
                        <span class="badge bg-indigo-lt">Vendor Registry</span>
                        <span class="badge bg-rose-lt">Criticality Level</span>
                        <span class="badge bg-blue-lt">Associated Risks</span>
                    </div>
                </div>
                <div class="pt-2 border-top">
                    <a href="{{ route('vendors.index') }}" class="btn btn-sm btn-primary w-100 btn-guide-action justify-content-center">
                        Buka Third-Party Vendors <i class="fas fa-arrow-right ms-1"></i>
                    </a>
                </div>
            </div>

            {{-- Owner Workload & Playbooks --}}
            <div class="guide-card-pro" data-searchable>
                <div>
                    <div class="guide-card-top">
                        <div class="guide-card-iconbox purple"><i class="fas fa-users-cog"></i></div>
                        <span class="badge bg-purple-lt fw-bold">Optimasi Tim</span>
                    </div>
                    <div class="guide-card-kicker">Feature 6</div>
                    <div class="guide-card-title">Workload & Mitigation Playbooks</div>
                    <div class="guide-card-route">Risk > Owner Workload / Playbooks</div>
                    <div class="guide-card-desc">Matriks beban kerja risk owner (tindakan terlambat, blocked, KRI merah) serta pustaka template mitigasi (playbook) untuk standardisasi respon risiko berulang.</div>
                    <div class="guide-card-badges">
                        <span class="badge bg-purple-lt">Workload Score</span>
                        <span class="badge bg-teal-lt">Action Templates</span>
                    </div>
                </div>
                <div class="pt-2 border-top">
                    <a href="{{ route('owners.workload') }}" class="btn btn-sm btn-primary w-100 btn-guide-action justify-content-center">
                        Buka Owner Workload <i class="fas fa-arrow-right ms-1"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- TAB 5: THREAT INTEL & INSTANT AI INSPECTOR --}}
    @if($showThreatIntel)
    <div class="tab-pane fade" id="guide-intel-pane" role="tabpanel" aria-labelledby="guide-intel-tab">
        <div class="guide-pane-header">
            <div class="guide-pane-heading">Threat Intelligence, Live CISA KEV & AI Inspector</div>
            <div class="guide-pane-sub">Pusat pemantauan kerentanan aktif, kampanye ransomware, dan analisis AI instan tanpa latensi chatbot.</div>
        </div>

        <div class="guide-grid mb-4">
            {{-- CISA KEV Feed --}}
            <div class="guide-card-pro" data-searchable>
                <div>
                    <div class="guide-card-top">
                        <div class="guide-card-iconbox rose"><i class="fas fa-shield-virus"></i></div>
                        <span class="badge bg-rose-lt fw-bold">Live Feed</span>
                    </div>
                    <div class="guide-card-kicker">Threat Intel 1</div>
                    <div class="guide-card-title">CISA KEV (Known Exploited Vulnerabilities)</div>
                    <div class="guide-card-route">Sidebar > Threat Intel > Tab KEV</div>
                    <div class="guide-card-desc">Katalog kerentanan resmi Cybersecurity and Infrastructure Security Agency (CISA) yang telah terbukti aktif dieksploitasi penyerang di dunia nyata.</div>

                    <div class="guide-steps-list">
                        <div class="guide-steps-title">Fitur & Kontrol Layar:</div>
                        <div class="guide-step-item"><i class="fas fa-search text-primary me-2 mt-1"></i><span><strong>Pencarian Instan:</strong> Filter real-time berdasarkan CVE ID, Vendor (Microsoft, Cisco, Fortinet), atau nama produk.</span></div>
                        <div class="guide-step-item"><i class="fas fa-toggle-on text-danger me-2 mt-1"></i><span><strong>Switch "Ransomware Only":</strong> Menyaring hanya kerentanan yang terbukti dipakai sindikat ransomware pemerasan data.</span></div>
                        <div class="guide-step-item"><i class="fas fa-calendar-alt text-warning me-2 mt-1"></i><span><strong>Due Date CISA:</strong> Batas waktu mandatori perbaikan patch untuk institusi finansial.</span></div>
                    </div>
                </div>
                <div class="pt-2 border-top">
                    <a href="{{ route('threat-intelligence.index') }}" class="btn btn-sm btn-primary w-100 btn-guide-action justify-content-center">
                        Buka Threat Intel <i class="fas fa-arrow-right ms-1"></i>
                    </a>
                </div>
            </div>

            {{-- OSINT AlienVault Breach Center --}}
            <div class="guide-card-pro" data-searchable>
                <div>
                    <div class="guide-card-top">
                        <div class="guide-card-iconbox amber"><i class="fas fa-database"></i></div>
                        <span class="badge bg-amber-lt fw-bold">Breach OSINT</span>
                    </div>
                    <div class="guide-card-kicker">Threat Intel 2</div>
                    <div class="guide-card-title">AlienVault & Data Breach Center</div>
                    <div class="guide-card-route">Sidebar > Threat Intel > Tab Data Breach</div>
                    <div class="guide-card-desc">Basis data 30 insiden kebocoran data terverifikasi (nasional & global). Fitur pencarian domain/email untuk mendeteksi apakah kredensial staf telah bocor di dark web.</div>

                    <div class="guide-steps-list">
                        <div class="guide-steps-title">Indikator Kompromi (IoC):</div>
                        <div class="guide-step-item"><i class="fas fa-search text-info me-2 mt-1"></i><span><strong>Domain & Email Lookup:</strong> Pengecekan instan domain bank dan alamat email korporat.</span></div>
                        <div class="guide-step-item"><i class="fas fa-chart-bar text-success me-2 mt-1"></i><span><strong>Grafik Tren & IoC Log:</strong> Menampilkan IP berbahaya, domain phishing, dan file hash malware.</span></div>
                    </div>
                </div>
                <div class="pt-2 border-top">
                    <a href="{{ route('threat-intelligence.index') }}" class="btn btn-sm btn-primary w-100 btn-guide-action justify-content-center">
                        Buka Breach Center <i class="fas fa-arrow-right ms-1"></i>
                    </a>
                </div>
            </div>
        </div>

        {{-- 4 PILLARS OF INSTANT AI INSPECTOR --}}
        <div class="d-flex align-items-center justify-content-between mb-3">
            <h3 class="h4 text-dark mb-0"><i class="fas fa-robot text-purple me-2"></i> 4 Dimensi Analisis AI Vulnerability Inspector Instan</h3>
            <span class="badge bg-purple-lt">0ms Latensi (Klik CVE Langsung Tampil)</span>
        </div>

        <div class="guide-grid">
            <div class="guide-card-pro" data-searchable>
                <div>
                    <div class="guide-card-top">
                        <div class="guide-card-iconbox rose"><i class="fas fa-radiation"></i></div>
                        <span class="badge bg-rose-lt fw-bold">Dimensi 1</span>
                    </div>
                    <div class="guide-card-title text-danger">Dampak Kerentanan</div>
                    <div class="guide-card-desc">Menilai potensi ancaman terhadap ketersediaan transaksi operasional (DoS), pencurian kredensial, dan peringatan bahaya keterlibatan ransomware (double extortion).</div>
                    <div class="guide-card-badges">
                        <span class="badge bg-rose-lt">UU PDP Pasal 46</span>
                        <span class="badge bg-dark-lt">Data Breach Risk</span>
                    </div>
                </div>
            </div>

            <div class="guide-card-pro" data-searchable>
                <div>
                    <div class="guide-card-top">
                        <div class="guide-card-iconbox amber"><i class="fas fa-cogs"></i></div>
                        <span class="badge bg-amber-lt fw-bold">Dimensi 2</span>
                    </div>
                    <div class="guide-card-title text-warning text-dark">Cara Kerja Eksploitasi</div>
                    <div class="guide-card-desc">Menjelaskan anatomi teknis celah (Remote Code Execution, Auth Bypass, SQL Injection, Buffer Overflow) dan vektor serangan penyerang secara objektif.</div>
                    <div class="guide-card-badges">
                        <span class="badge bg-amber-lt">Attack Vector</span>
                        <span class="badge bg-indigo-lt">RCE & Auth Bypass</span>
                    </div>
                </div>
            </div>

            <div class="guide-card-pro" data-searchable>
                <div>
                    <div class="guide-card-top">
                        <div class="guide-card-iconbox emerald"><i class="fas fa-shield-alt"></i></div>
                        <span class="badge bg-emerald-lt fw-bold">Dimensi 3</span>
                    </div>
                    <div class="guide-card-title text-success">Rekomendasi Mitigasi</div>
                    <div class="guide-card-desc">Instruksi perbaikan mandatori dari CISA, solusi perimeter darurat (IP Allowlist & virtual patching WAF bila patch tertunda), serta threat hunting IOC.</div>
                    <div class="guide-card-badges">
                        <span class="badge bg-emerald-lt">Patch Priority</span>
                        <span class="badge bg-teal-lt">Virtual Patch WAF</span>
                    </div>
                </div>
            </div>

            <div class="guide-card-pro" data-searchable>
                <div>
                    <div class="guide-card-top">
                        <div class="guide-card-iconbox primary"><i class="fas fa-landmark"></i></div>
                        <span class="badge bg-primary-lt fw-bold">Dimensi 4</span>
                    </div>
                    <div class="guide-card-title text-primary">Pemetaan Regulasi</div>
                    <div class="guide-card-desc">Korelasi langsung dengan klausul POJK 11/2022 Pasal 28 (pemantauan kerentanan), PADK 1/2026 (SLA 14 hari), dan ISO/IEC 27001:2022 Kontrol A.8.8.</div>
                    <div class="guide-card-badges">
                        <span class="badge bg-blue-lt">POJK 11 Pasal 28</span>
                        <span class="badge bg-purple-lt">ISO 27001 A.8.8</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- TAB 6: ANALISIS KEPATUHAN (SUPERADMIN & COMPLIANCE ONLY) --}}
    @if($showComplianceAnalysis)
    <div class="tab-pane fade" id="guide-analysis-pane" role="tabpanel" aria-labelledby="guide-analysis-tab">
        <div class="guide-pane-header">
            <div class="guide-pane-heading">Instrumen Analisis Kepatuhan & Kesiapan Audit</div>
            <div class="guide-pane-sub">Khusus Superadmin dan Divisi Compliance untuk memetakan keselarasan regulasi dan gap standar.</div>
        </div>

        <div class="guide-grid">
            {{-- Cross-Mapping --}}
            <div class="guide-card-pro" data-searchable>
                <div>
                    <div class="guide-card-top">
                        <div class="guide-card-iconbox primary"><i class="fas fa-network-wired"></i></div>
                        <span class="badge bg-primary-lt fw-bold">Instrumen 1</span>
                    </div>
                    <div class="guide-card-kicker">Cross-Regulatory</div>
                    <div class="guide-card-title">Cross-Mapping Regulasi</div>
                    <div class="guide-card-route">Analisis Kepatuhan > Cross-Mapping Regulasi</div>
                    <div class="guide-card-desc">Memetakan satu pasal regulasi nasional ke berbagai standar global sekaligus. Menghindari duplikasi audit dengan prinsip <em>"Audit sekali, penuhi banyak standar"</em>.</div>

                    <div class="guide-steps-list">
                        <div class="guide-steps-title">Fitur & Manfaat:</div>
                        <div class="guide-step-item"><i class="fas fa-check text-success me-2 mt-1"></i><span>Pilih regulasi acuan (POJK 11/2022 atau PADK 1/2026).</span></div>
                        <div class="guide-step-item"><i class="fas fa-check text-success me-2 mt-1"></i><span>Lihat korelasi instan ke ISO 27001:2022, NIST CSF 2.0, COBIT 2019, dan PBI.</span></div>
                        <div class="guide-step-item"><i class="fas fa-check text-success me-2 mt-1"></i><span>Status pemenuhan: <em>Direct Equivalent</em>, <em>Partial Coverage</em>, atau <em>Unique Requirement</em>.</span></div>
                    </div>
                </div>
                <div class="pt-2 border-top">
                    <a href="{{ route('analysis.cross-mapping') }}" class="btn btn-sm btn-primary w-100 btn-guide-action justify-content-center">
                        Buka Cross-Mapping <i class="fas fa-arrow-right ms-1"></i>
                    </a>
                </div>
            </div>

            {{-- Comparison & Gap Analysis --}}
            <div class="guide-card-pro" data-searchable>
                <div>
                    <div class="guide-card-top">
                        <div class="guide-card-iconbox cyan"><i class="fas fa-columns"></i></div>
                        <span class="badge bg-cyan-lt fw-bold">Instrumen 2</span>
                    </div>
                    <div class="guide-card-kicker">Gap Identification</div>
                    <div class="guide-card-title">Komparasi & Gap Analysis</div>
                    <div class="guide-card-route">Analisis Kepatuhan > Komparasi & Gap Analysis</div>
                    <div class="guide-card-desc">Membandingkan dua regulasi berdampingan (side-by-side) untuk mendeteksi pasal baru atau persyaratan yang belum dipenuhi internal institusi.</div>

                    <div class="guide-steps-list">
                        <div class="guide-steps-title">Fitur & Manfaat:</div>
                        <div class="guide-step-item"><i class="fas fa-check text-success me-2 mt-1"></i><span>Pilih dua kerangka kerja (misal: POJK 11 vs PADK 1, atau ISO 27001 vs NIST).</span></div>
                        <div class="guide-step-item"><i class="fas fa-check text-success me-2 mt-1"></i><span>Sorotan visual terhadap pasal yang belum memiliki padanan kontrol mitigasi internal.</span></div>
                        <div class="guide-step-item"><i class="fas fa-check text-success me-2 mt-1"></i><span>Readiness Bar (%) kesiapan organisasi terhadap regulasi target.</span></div>
                    </div>
                </div>
                <div class="pt-2 border-top">
                    <a href="{{ route('analysis.comparison') }}" class="btn btn-sm btn-primary w-100 btn-guide-action justify-content-center">
                        Buka Komparasi & Gap <i class="fas fa-arrow-right ms-1"></i>
                    </a>
                </div>
            </div>

            {{-- Checklist Evaluasi Mandiri --}}
            <div class="guide-card-pro" data-searchable>
                <div>
                    <div class="guide-card-top">
                        <div class="guide-card-iconbox emerald"><i class="fas fa-clipboard-check"></i></div>
                        <span class="badge bg-emerald-lt fw-bold">Instrumen 3</span>
                    </div>
                    <div class="guide-card-kicker">Self-Assessment</div>
                    <div class="guide-card-title">Checklist Evaluasi Mandiri (Excel)</div>
                    <div class="guide-card-route">Analisis Kepatuhan > Checklist Mandiri</div>
                    <div class="guide-card-desc">Kuesioner evaluasi kepatuhan per pasal untuk menilai tingkat kepatuhan (Compliance Score), dilengkapi ekspor lembar kerja resmi Excel (.xlsx).</div>

                    <div class="guide-steps-list">
                        <div class="guide-steps-title">Fitur & Manfaat:</div>
                        <div class="guide-step-item"><i class="fas fa-check text-success me-2 mt-1"></i><span>Pilihan status per butir: Compliant, Partially Compliant, Non-Compliant.</span></div>
                        <div class="guide-step-item"><i class="fas fa-check text-success me-2 mt-1"></i><span>Kalkulasi otomatis skor kepatuhan real-time total dan per bab/domain.</span></div>
                        <div class="guide-step-item"><i class="fas fa-file-excel text-success me-2 mt-1"></i><span>Tombol <strong>Export Excel (.xlsx)</strong> resmi untuk bahan rapat audit dan regulator.</span></div>
                    </div>
                </div>
                <div class="pt-2 border-top">
                    <a href="{{ route('analysis.checklist') }}" class="btn btn-sm btn-primary w-100 btn-guide-action justify-content-center">
                        Buka Checklist Evaluasi <i class="fas fa-arrow-right ms-1"></i>
                    </a>
                </div>
            </div>

            {{-- What-If Simulator --}}
            <div class="guide-card-pro" data-searchable>
                <div>
                    <div class="guide-card-top">
                        <div class="guide-card-iconbox amber"><i class="fas fa-calculator"></i></div>
                        <span class="badge bg-amber-lt fw-bold">Instrumen 4</span>
                    </div>
                    <div class="guide-card-kicker">Interactive Simulation</div>
                    <div class="guide-card-title">"What-If" Simulator Kesiapan Audit</div>
                    <div class="guide-card-route">Analisis Kepatuhan > "What-If" Simulator</div>
                    <div class="guide-card-desc">Simulasi interaktif untuk memprediksi perubahan skor ketahanan siber dan dampak profil risiko bila institusi menerapkan atau menunda kontrol tertentu.</div>

                    <div class="guide-steps-list">
                        <div class="guide-steps-title">Fitur & Manfaat:</div>
                        <div class="guide-step-item"><i class="fas fa-check text-success me-2 mt-1"></i><span>Toggle switch untuk simulasi implementasi kontrol mitigasi baru.</span></div>
                        <div class="guide-step-item"><i class="fas fa-check text-success me-2 mt-1"></i><span>Bandingkan Skor Saat Ini vs Skor Proyeksi Setelah Kontrol Diaktifkan.</span></div>
                        <div class="guide-step-item"><i class="fas fa-check text-success me-2 mt-1"></i><span>Rekomendasi kontrol dengan kenaikan skor tertinggi dan biaya terendah.</span></div>
                    </div>
                </div>
                <div class="pt-2 border-top">
                    <a href="{{ route('analysis.simulator') }}" class="btn btn-sm btn-primary w-100 btn-guide-action justify-content-center">
                        Buka "What-If" Simulator <i class="fas fa-arrow-right ms-1"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- TAB 7: MONITORING ASSESSMENT REGIONAL (SUPERADMIN & COMPLIANCE ONLY) --}}
    @if($showRegionalAssessment)
    <div class="tab-pane fade" id="guide-assessment-pane" role="tabpanel" aria-labelledby="guide-assessment-tab">
        <div class="guide-pane-header">
            <div class="guide-pane-heading">Monitoring Assessment Investigasi & Penguatan Regional</div>
            <div class="guide-pane-sub">Khusus Superadmin dan Divisi Compliance untuk mengawal evidence forensik Bank Sulteng dan Bank Sumut.</div>
        </div>

        <div class="guide-grid">
            {{-- Sulteng Aditasi --}}
            <div class="guide-card-pro" data-searchable>
                <div>
                    <div class="guide-card-top">
                        <div class="guide-card-iconbox primary"><i class="fas fa-landmark"></i></div>
                        <span class="badge bg-primary-lt fw-bold">Bank Sulteng</span>
                    </div>
                    <div class="guide-card-kicker">Assessment 1</div>
                    <div class="guide-card-title">Sulteng - Assessment Aditasi</div>
                    <div class="guide-card-route">Monitoring Assessment > Sulteng - Assessment Aditasi</div>
                    <div class="guide-card-desc">Dashboard eksekutif monitoring pemenuhan berkas evidence investigasi, kelengkapan forensik, dan akuntabilitas penyerahan berkas PIC Bank Sulteng.</div>

                    <div class="guide-steps-list">
                        <div class="guide-steps-title">Kapabilitas Modul:</div>
                        <div class="guide-step-item"><i class="fas fa-chart-pie text-primary me-2 mt-1"></i><span><strong>KPI Ringkasan:</strong> Total Item, Diterima, Belum Diterima, % Capaian, Total Size File.</span></div>
                        <div class="guide-step-item"><i class="fas fa-upload text-success me-2 mt-1"></i><span><strong>Upload Evidence:</strong> Unggah berkas log, PDF, gambar per nomor checklist.</span></div>
                        <div class="guide-step-item"><i class="fas fa-eye text-info me-2 mt-1"></i><span><strong>Preview & Download:</strong> Pratinjau langsung file bukti di peramban.</span></div>
                        <div class="guide-step-item"><i class="fas fa-file-excel text-success me-2 mt-1"></i><span><strong>Export & Sync Excel:</strong> Ekspor status kelengkapan dan sinkronisasi master file.</span></div>
                    </div>
                </div>
                <div class="pt-2 border-top">
                    <a href="{{ route('sulteng.dashboard') }}" class="btn btn-sm btn-primary w-100 btn-guide-action justify-content-center">
                        Buka Assessment Aditasi <i class="fas fa-arrow-right ms-1"></i>
                    </a>
                </div>
            </div>

            {{-- Sulteng Internal --}}
            <div class="guide-card-pro" data-searchable>
                <div>
                    <div class="guide-card-top">
                        <div class="guide-card-iconbox cyan"><i class="fas fa-clipboard-list"></i></div>
                        <span class="badge bg-cyan-lt fw-bold">Bank Sulteng</span>
                    </div>
                    <div class="guide-card-kicker">Assessment 2</div>
                    <div class="guide-card-title">Sulteng - Assessment Internal</div>
                    <div class="guide-card-route">Monitoring Assessment > Sulteng - Assessment Internal</div>
                    <div class="guide-card-desc">Pemantauan dan pengelolaan tindak lanjut temuan audit internal (List Temuan) Bank Sulteng.</div>

                    <div class="guide-steps-list">
                        <div class="guide-steps-title">Kapabilitas Modul:</div>
                        <div class="guide-step-item"><i class="fas fa-tasks text-cyan me-2 mt-1"></i><span><strong>Metrik Status Temuan:</strong> Distribusi status Open, In Progress, Resolved, Closed.</span></div>
                        <div class="guide-step-item"><i class="fas fa-edit text-warning me-2 mt-1"></i><span><strong>Form Update Tindak Lanjut:</strong> Perbarui catatan perbaikan, PIC, due date, link dokumen.</span></div>
                        <div class="guide-step-item"><i class="fas fa-file-excel text-success me-2 mt-1"></i><span><strong>Ekspor Excel:</strong> Rekapitulasi temuan untuk laporan rapat direksi.</span></div>
                    </div>
                </div>
                <div class="pt-2 border-top">
                    <a href="{{ route('sulteng-internal.dashboard') }}" class="btn btn-sm btn-primary w-100 btn-guide-action justify-content-center">
                        Buka Assessment Internal <i class="fas fa-arrow-right ms-1"></i>
                    </a>
                </div>
            </div>

            {{-- Sumut 27 Point --}}
            <div class="guide-card-pro" data-searchable>
                <div>
                    <div class="guide-card-top">
                        <div class="guide-card-iconbox emerald"><i class="fas fa-shield-alt"></i></div>
                        <span class="badge bg-emerald-lt fw-bold">Bank Sumut</span>
                    </div>
                    <div class="guide-card-kicker">Assessment 3</div>
                    <div class="guide-card-title">Sumut - Langkah Penguatan (27 Point)</div>
                    <div class="guide-card-route">Monitoring Assessment > Sumut - Langkah Penguatan</div>
                    <div class="guide-card-desc">Dashboard pemantauan 27 action plan penguatan infrastruktur dan ketahanan siber rekomendasi regulator untuk Bank Sumut.</div>

                    <div class="guide-steps-list">
                        <div class="guide-steps-title">Kapabilitas Modul:</div>
                        <div class="guide-step-item"><i class="fas fa-server text-indigo me-2 mt-1"></i><span><strong>27 Point Checklist:</strong> Core Banking (CBS), proteksi EDR/XDR, PAM, Firewall/WAF.</span></div>
                        <div class="guide-step-item"><i class="fas fa-sticky-note text-amber me-2 mt-1"></i><span><strong>Catatan Lapangan:</strong> Pencatatan progres teknis dan hambatan per Point (#1 s.d. #27).</span></div>
                        <div class="guide-step-item"><i class="fas fa-file-excel text-success me-2 mt-1"></i><span><strong>Ekspor Spreadsheet:</strong> Matriks pemenuhan untuk laporan pengawasan regulator.</span></div>
                    </div>
                </div>
                <div class="pt-2 border-top">
                    <a href="{{ route('sumut.dashboard') }}" class="btn btn-sm btn-primary w-100 btn-guide-action justify-content-center">
                        Buka Sumut 27 Point <i class="fas fa-arrow-right ms-1"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- TAB 8: AUDIT & COMPLIANCE PROGRAM --}}
    @if($showComplianceAudit)
    <div class="tab-pane fade" id="guide-compliance-pane" role="tabpanel" aria-labelledby="guide-compliance-tab">
        <div class="guide-pane-header">
            <div class="guide-pane-heading">Audit Monitoring & Compliance Programs</div>
            <div class="guide-pane-sub">Pengawasan temuan audit independen dan pengelolaan program kerja kepatuhan tahunan.</div>
        </div>

        <div class="guide-grid">
            <div class="guide-card-pro" data-searchable>
                <div>
                    <div class="guide-card-top">
                        <div class="guide-card-iconbox primary"><i class="fas fa-clipboard-list"></i></div>
                        <span class="badge bg-primary-lt fw-bold">Monitoring</span>
                    </div>
                    <div class="guide-card-kicker">Program 1</div>
                    <div class="guide-card-title">Audit Monitoring Dashboard</div>
                    <div class="guide-card-route">Sidebar > Audit</div>
                    <div class="guide-card-desc">Dashboard komparasi temuan audit lintas bank, status temuan per kategori, filter tipe audit (Internal/External), serta ekspor laporan.</div>
                </div>
            </div>

            <div class="guide-card-pro" data-searchable>
                <div>
                    <div class="guide-card-top">
                        <div class="guide-card-iconbox emerald"><i class="fas fa-briefcase"></i></div>
                        <span class="badge bg-emerald-lt fw-bold">Program Kerja</span>
                    </div>
                    <div class="guide-card-kicker">Program 2</div>
                    <div class="guide-card-title">Compliance Work Programs</div>
                    <div class="guide-card-route">Compliance > Internal Audit / Risk / Governance</div>
                    <div class="guide-card-desc">Pendataan key initiative, deliverables, tolak ukur keberhasilan, alokasi budget, dan kebutuhan dukungan lintas unit.</div>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- TAB 9: SUPERADMIN GOVERNANCE --}}
    @if($isSuperadmin)
    <div class="tab-pane fade" id="guide-superadmin-pane" role="tabpanel" aria-labelledby="guide-superadmin-tab">
        <div class="guide-pane-header">
            <div class="guide-pane-heading">Tata Kelola & Administrasi Superadmin</div>
            <div class="guide-pane-sub">Modul administrasi inti, kontrol akses, parameter form, status approvals, dan audit trail sistem.</div>
        </div>

        <div class="guide-grid">
            <div class="guide-card-pro" data-searchable>
                <div>
                    <div class="guide-card-top">
                        <div class="guide-card-iconbox purple"><i class="fas fa-users-cog"></i></div>
                        <span class="badge bg-purple-lt fw-bold">User Admin</span>
                    </div>
                    <div class="guide-card-kicker">Admin 1</div>
                    <div class="guide-card-title">User Management & Verifikasi Divisi</div>
                    <div class="guide-card-route">App Management > User Management</div>
                    <div class="guide-card-desc">Mengesahkan penempatan unit kerja pengguna baru, promosi/pencabutan hak Superadmin, dan pembuatan password sementara (reset password).</div>
                </div>
                <div class="pt-2 border-top">
                    <a href="{{ route('users.index') }}" class="btn btn-sm btn-primary w-100 btn-guide-action justify-content-center">
                        Buka User Management <i class="fas fa-arrow-right ms-1"></i>
                    </a>
                </div>
            </div>

            <div class="guide-card-pro" data-searchable>
                <div>
                    <div class="guide-card-top">
                        <div class="guide-card-iconbox primary"><i class="fas fa-sliders-h"></i></div>
                        <span class="badge bg-primary-lt fw-bold">Master Data</span>
                    </div>
                    <div class="guide-card-kicker">Admin 2</div>
                    <div class="guide-card-title">Parameter Management (10 Tab)</div>
                    <div class="guide-card-route">App Management > Parameter</div>
                    <div class="guide-card-desc">Kelola opsi form risiko: Kategori Risiko, Risk Owner, Skala Probabilitas (1–5), Skala Dampak (1–5), Vendor, dan Radar Group.</div>
                </div>
                <div class="pt-2 border-top">
                    <a href="{{ route('parameters.index') }}" class="btn btn-sm btn-primary w-100 btn-guide-action justify-content-center">
                        Buka Parameter Management <i class="fas fa-arrow-right ms-1"></i>
                    </a>
                </div>
            </div>

            <div class="guide-card-pro" data-searchable>
                <div>
                    <div class="guide-card-top">
                        <div class="guide-card-iconbox amber"><i class="fas fa-bullseye"></i></div>
                        <span class="badge bg-amber-lt fw-bold">Kebijakan Direksi</span>
                    </div>
                    <div class="guide-card-kicker">Admin 3</div>
                    <div class="guide-card-title">Corporate Risk Appetite</div>
                    <div class="guide-card-route">App Management > Corporate Risk Appetite</div>
                    <div class="guide-card-desc">Menetapkan batas target appetite dan batas toleransi maksimum per kategori risiko sesuai surat keputusan pengesahan direksi.</div>
                </div>
                <div class="pt-2 border-top">
                    <a href="{{ route('risk-appetites.index') }}" class="btn btn-sm btn-primary w-100 btn-guide-action justify-content-center">
                        Buka Corporate Appetite <i class="fas fa-arrow-right ms-1"></i>
                    </a>
                </div>
            </div>

            <div class="guide-card-pro" data-searchable>
                <div>
                    <div class="guide-card-top">
                        <div class="guide-card-iconbox emerald"><i class="fas fa-check-double"></i></div>
                        <span class="badge bg-emerald-lt fw-bold">Maker-Checker</span>
                    </div>
                    <div class="guide-card-kicker">Admin 4</div>
                    <div class="guide-card-title">Status Approvals Queue</div>
                    <div class="guide-card-route">Sidebar > Status Approvals</div>
                    <div class="guide-card-desc">Antrean evaluasi permintaan perubahan status risiko unit operasional (Pending, Approved, Rejected) beserta catatan review.</div>
                </div>
                <div class="pt-2 border-top">
                    <a href="{{ route('risk-status-approvals.index') }}" class="btn btn-sm btn-primary w-100 btn-guide-action justify-content-center">
                        Buka Status Approvals <i class="fas fa-arrow-right ms-1"></i>
                    </a>
                </div>
            </div>

            <div class="guide-card-pro" data-searchable>
                <div>
                    <div class="guide-card-top">
                        <div class="guide-card-iconbox rose"><i class="fas fa-bell"></i></div>
                        <span class="badge bg-rose-lt fw-bold">Eskalasi</span>
                    </div>
                    <div class="guide-card-kicker">Admin 5</div>
                    <div class="guide-card-title">Governance Alerts</div>
                    <div class="guide-card-route">Sidebar > Alerts</div>
                    <div class="guide-card-desc">Memantau breach batas appetite, treatment mitigasi overdue, dan notifikasi tata kelola yang membutuhkan tindak lanjut eskalasi.</div>
                </div>
                <div class="pt-2 border-top">
                    <a href="{{ route('risk-alerts.index') }}" class="btn btn-sm btn-primary w-100 btn-guide-action justify-content-center">
                        Buka Alerts <i class="fas fa-arrow-right ms-1"></i>
                    </a>
                </div>
            </div>

            <div class="guide-card-pro" data-searchable>
                <div>
                    <div class="guide-card-top">
                        <div class="guide-card-iconbox cyan"><i class="fas fa-history"></i></div>
                        <span class="badge bg-cyan-lt fw-bold">Forensik</span>
                    </div>
                    <div class="guide-card-kicker">Admin 6</div>
                    <div class="guide-card-title">Audit Trail Sistem</div>
                    <div class="guide-card-route">Sidebar > Audit Trail</div>
                    <div class="guide-card-desc">Catatan log aktivitas forensik sistem yang tidak dapat diubah: aktor, waktu aktivitas, nama event, dan rincian perubahan data.</div>
                </div>
                <div class="pt-2 border-top">
                    <a href="{{ route('audit-logs.index') }}" class="btn btn-sm btn-primary w-100 btn-guide-action justify-content-center">
                        Buka Audit Trail <i class="fas fa-arrow-right ms-1"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- TAB 10: AKUN & KEAMANAN --}}
    <div class="tab-pane fade" id="guide-account-pane" role="tabpanel" aria-labelledby="guide-account-tab">
        <div class="guide-pane-header">
            <div class="guide-pane-heading">Keamanan Akun, Autentikasi MFA & Troubleshooting</div>
            <div class="guide-pane-sub">Standar perlindungan akun pengguna dan prosedur pemulihan kendala login.</div>
        </div>

        <div class="guide-grid">
            <div class="guide-card-pro" data-searchable>
                <div>
                    <div class="guide-card-top">
                        <div class="guide-card-iconbox primary"><i class="fas fa-key"></i></div>
                        <span class="badge bg-primary-lt fw-bold">Password</span>
                    </div>
                    <div class="guide-card-kicker">Keamanan 1</div>
                    <div class="guide-card-title">Standar Password Mandiri</div>
                    <div class="guide-card-route">Dropdown Akun > Change Password</div>
                    <div class="guide-card-desc">Ganti password akun Anda secara berkala sesuai ketentuan keamanan institusi perbankan.</div>

                    <div class="guide-steps-list">
                        <div class="guide-steps-title">Aturan Password Kuat:</div>
                        <div class="guide-step-item"><i class="fas fa-check text-success me-2 mt-1"></i><span>Panjang minimal 8 karakter (disarankan 12+ karakter).</span></div>
                        <div class="guide-step-item"><i class="fas fa-check text-success me-2 mt-1"></i><span>Kombinasi huruf besar, huruf kecil, angka, dan simbol unik.</span></div>
                        <div class="guide-step-item"><i class="fas fa-check text-success me-2 mt-1"></i><span>Tidak menggunakan password yang pernah dipakai sebelumnya.</span></div>
                    </div>
                </div>
                <div class="pt-2 border-top">
                    <a href="{{ route('change.password') }}" class="btn btn-sm btn-outline-primary w-100 btn-guide-action justify-content-center">
                        Buka Change Password <i class="fas fa-arrow-right ms-1"></i>
                    </a>
                </div>
            </div>

            <div class="guide-card-pro" data-searchable>
                <div>
                    <div class="guide-card-top">
                        <div class="guide-card-iconbox emerald"><i class="fas fa-mobile-alt"></i></div>
                        <span class="badge bg-emerald-lt fw-bold">MFA Protection</span>
                    </div>
                    <div class="guide-card-kicker">Keamanan 2</div>
                    <div class="guide-card-title">Multi-Factor Authentication (MFA)</div>
                    <div class="guide-card-route">Dropdown Akun > Profile > Manage MFA</div>
                    <div class="guide-card-desc">Lapisan perlindungan ganda dengan aplikasi Authenticator (Google Authenticator, Microsoft Authenticator) atau kode verifikasi email OTP.</div>

                    <div class="guide-steps-list">
                        <div class="guide-steps-title">Panduan Aktivasi:</div>
                        <div class="guide-step-item"><div class="guide-step-num">1</div><span>Buka Profile > Manage Two-Factor Authentication.</span></div>
                        <div class="guide-step-item"><div class="guide-step-num">2</div><span>Pindai QR code menggunakan aplikasi Authenticator ponsel Anda.</span></div>
                        <div class="guide-step-item"><div class="guide-step-num">3</div><span>Masukkan kode 6 digit dan simpan Recovery Code cadangan di tempat aman.</span></div>
                    </div>
                </div>
                <div class="pt-2 border-top">
                    <a href="{{ route('profile.index') }}" class="btn btn-sm btn-outline-primary w-100 btn-guide-action justify-content-center">
                        Buka Profil & MFA <i class="fas fa-arrow-right ms-1"></i>
                    </a>
                </div>
            </div>

            <div class="guide-card-pro" data-searchable>
                <div>
                    <div class="guide-card-top">
                        <div class="guide-card-iconbox amber"><i class="fas fa-tools"></i></div>
                        <span class="badge bg-amber-lt fw-bold">Bantuan</span>
                    </div>
                    <div class="guide-card-kicker">Keamanan 3</div>
                    <div class="guide-card-title">Troubleshooting Kendala Akses</div>
                    <div class="guide-card-route">Panduan Bantuan Masalah Umum</div>
                    <div class="guide-card-desc">Solusi cepat terhadap kendala yang paling sering dihadapi pengguna.</div>

                    <div class="guide-steps-list">
                        <div class="guide-steps-title">Solusi Masalah:</div>
                        <div class="guide-step-item"><i class="fas fa-question-circle text-warning me-2 mt-1"></i><span><strong>Lupa Password:</strong> Hubungi Superadmin untuk mendapatkan password sementara.</span></div>
                        <div class="guide-step-item"><i class="fas fa-question-circle text-warning me-2 mt-1"></i><span><strong>MFA Email Tak Masuk:</strong> Periksa folder Spam dan verifikasi alamat email terdaftar.</span></div>
                        <div class="guide-step-item"><i class="fas fa-question-circle text-warning me-2 mt-1"></i><span><strong>Menu Terkunci:</strong> Pastikan status penempatan divisi Anda sudah disahkan Superadmin.</span></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const searchInput = document.getElementById('guideSearchInput');
    const searchClear = document.getElementById('guideSearchClear');
    const matchBanner = document.getElementById('guideSearchMatchBanner');
    const queryText = document.getElementById('searchQueryText');
    const resultCount = document.getElementById('searchResultCount');
    const emptyState = document.getElementById('guideEmptyState');
    const tabNav = document.getElementById('userGuideTab');
    const resetButtons = [document.getElementById('btnResetSearch'), document.getElementById('btnResetEmptySearch')];
    const searchableCards = document.querySelectorAll('[data-searchable]');

    // Press '/' to focus search input
    document.addEventListener('keydown', function (e) {
        if (e.key === '/' && document.activeElement !== searchInput && !['INPUT', 'TEXTAREA'].includes(document.activeElement.tagName)) {
            e.preventDefault();
            searchInput.focus();
            searchInput.select();
        }
    });

    function performSearch() {
        const query = searchInput.value.trim().toLowerCase();

        if (query.length > 0) {
            searchClear.style.display = 'block';
        } else {
            searchClear.style.display = 'none';
        }

        if (query.length === 0) {
            matchBanner.classList.add('d-none');
            emptyState.style.display = 'none';
            // Restore cards in current tab
            searchableCards.forEach(card => {
                card.style.display = '';
            });
            return;
        }

        let totalMatches = 0;

        searchableCards.forEach(card => {
            const text = card.textContent.toLowerCase();
            const matches = text.includes(query);
            if (matches) {
                card.style.display = '';
                totalMatches++;
            } else {
                card.style.display = 'none';
            }
        });

        // Update match banner
        queryText.textContent = searchInput.value.trim();
        resultCount.textContent = totalMatches;
        matchBanner.classList.remove('d-none');

        // Show/hide empty state
        if (totalMatches === 0) {
            emptyState.style.display = 'block';
        } else {
            emptyState.style.display = 'none';
        }
    }

    searchInput.addEventListener('input', performSearch);

    searchClear.addEventListener('click', function () {
        searchInput.value = '';
        searchInput.focus();
        performSearch();
    });

    resetButtons.forEach(btn => {
        if (btn) {
            btn.addEventListener('click', function () {
                searchInput.value = '';
                searchInput.focus();
                performSearch();
            });
        }
    });

    // Reset search filter view when switching tabs so tabs don't show empty pane confusingly
    const tabButtons = tabNav ? tabNav.querySelectorAll('button[data-bs-toggle="pill"]') : [];
    tabButtons.forEach(button => {
        button.addEventListener('shown.bs.tab', function () {
            if (searchInput.value.trim().length > 0) {
                performSearch();
            }
        });
    });
});
</script>
@endpush
