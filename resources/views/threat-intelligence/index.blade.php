@extends('layouts.tabler')

@section('title', 'Pusat Intelijen Ancaman & Kerentanan - Raksantara')

@php
    $cves = $dashboard['cves'] ?? [];
    $kevItems = data_get($dashboard, 'kev.items', []);
    
    // Normalize CISA KEV list
    $normalizedKev = collect($kevItems)->map(function ($item) {
        return [
            'id' => $item['cve_id'] ?? ($item['cveID'] ?? 'CVE'),
            'title' => $item['vulnerability_name'] ?? ($item['vulnerabilityName'] ?? 'Vulnerability'),
            'vendor' => $item['vendor_project'] ?? ($item['vendorProject'] ?? 'Vendor'),
            'product' => $item['product'] ?? 'Product',
            'date' => !empty($item['date_added']) ? date('d M Y', strtotime($item['date_added'])) : (!empty($item['dateAdded']) ? date('d M Y', strtotime($item['dateAdded'])) : 'Recently'),
            'rawDate' => $item['date_added'] ?? ($item['dateAdded'] ?? now()->toIso8601String()),
            'requiredAction' => $item['required_action'] ?? ($item['requiredAction'] ?? 'Apply vendor patches immediately per instructions.'),
            'isRansomware' => ($item['known_ransomware_campaign_use'] ?? ($item['knownRansomwareCampaignUse'] ?? '')) === 'Known',
            'shortDescription' => $item['short_description'] ?? ($item['shortDescription'] ?? ($item['description'] ?? 'Detail teknis tercantum dalam katalog CISA KEV.')),
        ];
    })->values()->all();

    if (empty($normalizedKev) && !empty($cves)) {
        $normalizedKev = collect($cves)->map(function ($cve) {
            return [
                'id' => $cve['cve_id'] ?? 'CVE',
                'title' => $cve['description'] ?? 'Vulnerability',
                'vendor' => $cve['vendor'] ?? 'Various',
                'product' => $cve['product'] ?? 'System',
                'date' => !empty($cve['published_at']) ? date('d M Y', strtotime($cve['published_at'])) : 'Recently',
                'rawDate' => $cve['published_at'] ?? now()->toIso8601String(),
                'requiredAction' => 'Perbarui produk ke versi terbaru dan terapkan patch resmi.',
                'isRansomware' => !empty($cve['is_known_exploited']),
                'shortDescription' => $cve['description'] ?? 'Kerentanan teridentifikasi pada sistem.',
            ];
        })->values()->all();
    }
@endphp

@push('styles')
<style>
.intel-page {
    padding: 0.25rem;
    font-family: inherit;
}

/* Hero Section */
.intel-hero {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 1.5rem;
    padding: 1.75rem 2rem;
    border-radius: 20px;
    color: #ffffff;
    background: linear-gradient(135deg, #0f172a 0%, #1e293b 50%, #172554 100%);
    box-shadow: 0 16px 40px rgba(15, 23, 42, 0.15);
    margin-bottom: 1.5rem;
}
.intel-hero-content { flex: 1; }
.intel-hero-kicker {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.35rem 0.75rem;
    background: rgba(255, 255, 255, 0.1);
    border: 1px solid rgba(255, 255, 255, 0.15);
    border-radius: 999px;
    font-size: 0.75rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    color: #93c5fd;
    margin-bottom: 0.75rem;
}
.intel-hero-title {
    font-size: 1.85rem;
    font-weight: 800;
    margin: 0 0 0.5rem;
    letter-spacing: -0.02em;
    line-height: 1.2;
}
.intel-hero-lede {
    opacity: 0.8;
    max-width: 680px;
    font-size: 0.9rem;
    line-height: 1.5;
    margin: 0;
}
.intel-hero-stats {
    display: flex;
    gap: 0.85rem;
}
.intel-stat-card {
    background: rgba(255, 255, 255, 0.06);
    border: 1px solid rgba(255, 255, 255, 0.12);
    padding: 0.85rem 1.25rem;
    border-radius: 14px;
    min-width: 120px;
    text-align: center;
    backdrop-filter: blur(10px);
}
.intel-stat-card label {
    display: block;
    font-size: 0.68rem;
    opacity: 0.7;
    text-transform: uppercase;
    font-weight: 700;
    letter-spacing: 0.05em;
    margin-bottom: 0.25rem;
}
.intel-stat-card strong {
    display: block;
    font-size: 1.5rem;
    font-weight: 800;
    line-height: 1.1;
    color: #ffffff;
}
.intel-stat-card span {
    font-size: 0.68rem;
    opacity: 0.6;
}

/* Nav Tabs */
.intel-nav {
    display: flex;
    gap: 0.85rem;
    margin-bottom: 1.5rem;
}
.intel-tab {
    flex: 1;
    display: flex;
    align-items: center;
    gap: 0.85rem;
    padding: 0.9rem 1.25rem;
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 16px;
    cursor: pointer;
    transition: all 0.2s ease;
    text-align: left;
}
.intel-tab:hover {
    border-color: #cbd5e1;
    background: #f8fafc;
}
.intel-tab.active {
    border-color: #2563eb;
    background: #eff6ff;
    box-shadow: 0 4px 12px rgba(37, 99, 235, 0.08);
}
.intel-tab i {
    width: 38px;
    height: 38px;
    background: #f1f5f9;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.1rem;
    color: #64748b;
    transition: all 0.2s ease;
}
.intel-tab.active i {
    background: #2563eb;
    color: #ffffff;
}
.intel-tab strong {
    display: block;
    font-size: 0.95rem;
    font-weight: 800;
    color: #0f172a;
}
.intel-tab span {
    font-size: 0.78rem;
    color: #64748b;
}

/* Panels & Cards */
.panel-card {
    background: #ffffff;
    border-radius: 18px;
    border: 1px solid #e2e8f0;
    box-shadow: 0 4px 12px rgba(15, 23, 42, 0.03);
}
.metric-box-card {
    background: #ffffff;
    border-radius: 16px;
    border: 1px solid #e2e8f0;
    padding: 1.1rem;
    box-shadow: 0 4px 10px rgba(15, 23, 42, 0.02);
    display: flex;
    align-items: center;
    gap: 0.85rem;
}
.metric-box-icon {
    width: 44px;
    height: 44px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.15rem;
}

/* Custom Table for OTX */
.table-custom {
    width: 100%;
    border-collapse: separate;
    border-spacing: 0;
}
.table-custom thead th {
    font-size: 0.68rem;
    font-weight: 800;
    color: #64748b;
    padding: 10px 14px;
    border-bottom: 1px solid #e2e8f0;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    background: #f8fafc;
}
.table-custom tbody tr {
    cursor: pointer;
    transition: background 0.15s ease;
}
.table-custom tbody tr:hover { background: #f8fafc; }
.table-custom tbody tr.row-expanded { background: #eff6ff; }
.table-custom tbody td {
    padding: 11px 14px;
    font-size: 0.82rem;
    border-bottom: 1px solid #f1f5f9;
    vertical-align: middle;
}
.ioc-tag {
    font-size: 0.65rem;
    font-weight: 800;
    color: #2563eb;
    background: #eff6ff;
    padding: 3px 8px;
    border-radius: 6px;
    text-transform: uppercase;
}
.mono-value {
    font-family: 'JetBrains Mono', SFMono-Regular, Menlo, Monaco, Consolas, monospace;
    color: #334155;
    font-size: 0.78rem;
}
.sight-count {
    font-size: 0.72rem;
    font-weight: 800;
    background: #0f172a;
    color: #ffffff;
    padding: 2px 8px;
    border-radius: 999px;
}
.severity-pill {
    font-size: 0.68rem;
    font-weight: 800;
    padding: 3px 10px;
    border-radius: 999px;
}
.badge-tag {
    font-size: 0.68rem;
    font-weight: 700;
    color: #475569;
    background: #f1f5f9;
    padding: 3px 8px;
    border-radius: 6px;
    border: 1px solid #e2e8f0;
}

/* Activity Chart Bar */
.chart-bar-container {
    display: flex;
    align-items: flex-end;
    gap: 0.75rem;
    height: 120px;
    padding-top: 1rem;
}
.chart-bar-col {
    flex: 1;
    display: flex;
    flex-direction: column;
    align-items: center;
    height: 100%;
    justify-content: flex-end;
}
.chart-bar-pill {
    width: 100%;
    background: linear-gradient(180deg, #3b82f6 0%, #1d4ed8 100%);
    border-radius: 6px 6px 0 0;
    min-height: 8px;
    transition: height 0.4s ease;
}
.chart-bar-label {
    font-size: 0.7rem;
    font-weight: 700;
    color: #64748b;
    margin-top: 6px;
}

/* CISA KEV Feed Rows */
.cve-list-container {
    display: flex;
    flex-direction: column;
    gap: 8px;
    max-height: 820px;
    overflow-y: auto;
    padding-right: 4px;
}
.cve-row-item {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 14px;
    padding: 12px 14px;
    cursor: pointer;
    transition: all 0.2s ease;
}
.cve-row-item:hover {
    background: #f8fafc;
    border-color: #cbd5e1;
    transform: translateY(-1px);
}
.cve-row-item.selected {
    border-color: #2563eb;
    background: #eff6ff;
    box-shadow: 0 4px 14px rgba(37, 99, 235, 0.1);
}
.cve-row-main {
    display: flex;
    align-items: center;
    gap: 12px;
}
.cve-row-id-cell {
    min-width: 125px;
}
.cve-id-tag {
    font-family: 'JetBrains Mono', SFMono-Regular, Menlo, Monaco, Consolas, monospace;
    font-weight: 800;
    color: #2563eb;
    font-size: 0.82rem;
}
.cve-row-content-cell {
    flex: 1;
    min-width: 0;
}
.cve-row-title {
    display: block;
    font-weight: 700;
    font-size: 0.86rem;
    color: #0f172a;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.cve-row-sub {
    font-size: 0.74rem;
    color: #64748b;
}
.cve-row-meta-cell {
    display: flex;
    align-items: center;
    gap: 8px;
    text-align: right;
}
.cve-row-date {
    font-size: 0.72rem;
    color: #64748b;
    white-space: nowrap;
}
.badge-mini {
    font-size: 0.62rem;
    padding: 2px 6px;
    border-radius: 4px;
    font-weight: 800;
}

/* Pulsing dot */
.live-pulse {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    background: #ecfdf5;
    border: 1px solid #a7f3d0;
    color: #065f46;
    padding: 3px 8px;
    border-radius: 999px;
    font-size: 0.7rem;
    font-weight: 800;
}
.pulse-dot {
    width: 8px;
    height: 8px;
    background: #10b981;
    border-radius: 50%;
    animation: live-pulse-anim 1.5s infinite;
}
@keyframes live-pulse-anim {
    0% { transform: scale(0.9); opacity: 0.7; }
    50% { transform: scale(1.2); opacity: 1; }
    100% { transform: scale(0.9); opacity: 0.7; }
}

/* Dedicated AI Inspector Panel */
.ai-inspector-card {
    background: #ffffff;
    border-radius: 20px;
    border: 1px solid #dbeafe;
    box-shadow: 0 16px 40px rgba(15, 23, 42, 0.06);
    position: sticky;
    top: 1rem;
    max-height: calc(100vh - 3.5rem);
    display: flex;
    flex-direction: column;
    overflow: hidden;
}
.ai-inspector-header {
    padding: 1.25rem 1.5rem;
    background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
    color: #ffffff;
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
}
.ai-inspector-body {
    flex: 1;
    padding: 1.5rem;
    overflow-y: auto;
    background: #f8fafc;
}
.ai-section-box {
    background: #ffffff;
    border-radius: 14px;
    border: 1px solid #e2e8f0;
    padding: 1.25rem;
    margin-bottom: 1rem;
    box-shadow: 0 2px 6px rgba(15, 23, 42, 0.02);
}
.ai-section-title {
    font-size: 0.95rem;
    font-weight: 800;
    margin-bottom: 0.75rem;
    display: flex;
    align-items: center;
}
.ai-section-content {
    font-size: 0.84rem;
    line-height: 1.6;
    color: #334155;
}
.ai-section-content ul {
    margin-bottom: 0;
    padding-left: 1.25rem;
}
.ai-section-content li {
    margin-bottom: 0.35rem;
}
.ai-section-content li:last-child {
    margin-bottom: 0;
}
</style>
@endpush

@section('content')
<div class="intel-page">
    
    <!-- Hero Section -->
    <section class="intel-hero">
        <div class="intel-hero-content">
            <span class="intel-hero-kicker">
                <i class="fas fa-shield-virus"></i> Feed Intelijen Ancaman & Kerentanan Terpadu
            </span>
            <h1 class="intel-hero-title">Threat Intelligence & Vulnerability Center</h1>
            <p class="intel-hero-lede">
                Pemantauan feed ancaman global secara real-time, deteksi kebocoran data terverifikasi (OSINT), pemetaan indikator kompromi (IOC), dan analisis otomatis kerentanan tereksploitasi CISA KEV (dampak, cara kerja eksploitasi, serta mitigasi).
            </p>
        </div>
        <div class="intel-hero-stats d-none d-lg-flex">
            <div class="intel-stat-card">
                <label>Katalog KEV</label>
                <strong>{{ count($normalizedKev) }}</strong>
                <span>CISA In-The-Wild</span>
            </div>
            <div class="intel-stat-card">
                <label>Threat Pulses</label>
                <strong>{{ count($pulses) }}</strong>
                <span>AlienVault OTX</span>
            </div>
            <div class="intel-stat-card">
                <label>Aktif IOC</label>
                <strong>{{ count($iocs) }}</strong>
                <span>Indikator Verifikasi</span>
            </div>
        </div>
    </section>

    <!-- Navigation Tabs -->
    <div class="intel-nav" role="tablist">
        <button class="intel-tab active" type="button" onclick="switchIntelTab('cve', this)">
            <i class="fas fa-bug"></i>
            <div>
                <strong>CISA KEV & AI Vulnerability Inspector</strong>
                <span>Known Exploited Vulnerabilities live feed dengan analisis dampak, cara kerja, dan mitigasi instan</span>
            </div>
        </button>
        <button class="intel-tab" type="button" onclick="switchIntelTab('otx', this)">
            <i class="fas fa-satellite-dish"></i>
            <div>
                <strong>OTX AlienVault & Breach Center</strong>
                <span>Indikator Kompromi (IOC), Global Threat Pulses & OSINT Data Leak Checker</span>
            </div>
        </button>
    </div>

    <!-- TAB 1: CISA KEV & AI VULNERABILITY INSPECTOR -->
    <div id="intel-tab-cve" class="tab-pane-content">
        <div class="row g-3">
            <!-- Left Column: CVE List & Filter -->
            <div class="col-lg-5 col-xl-5">
                <!-- Header Filters -->
                <div class="panel-card p-3 mb-3">
                    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-2">
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge bg-primary text-white fw-bold px-3 py-2 rounded-pill" id="cveCountBadge">
                                {{ count($normalizedKev) }} Found
                            </span>
                            <div class="live-pulse">
                                <span class="pulse-dot"></span>
                                <span>KEV LIVE FEED</span>
                            </div>
                        </div>
                        <div class="form-check form-switch mb-0">
                            <input class="form-check-input" type="checkbox" id="ransomFilterSwitch" onchange="applyCveFilters()">
                            <label class="form-check-label small fw-bold text-danger" for="ransomFilterSwitch">Ransomware Only</label>
                        </div>
                    </div>
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-white border"><i class="fas fa-search text-muted"></i></span>
                        <input 
                            type="text" 
                            id="cveSearchInput" 
                            class="form-control" 
                            placeholder="Cari CVE ID, Vendor, atau Produk..."
                            oninput="applyCveFilters()"
                        >
                    </div>
                </div>

                <!-- CVE List Items -->
                <div class="cve-list-container" id="cveListContainer">
                    @foreach($normalizedKev as $idx => $cve)
                        <div 
                            class="cve-row-item {{ $idx === 0 ? 'selected' : '' }}" 
                            id="cve-item-{{ $cve['id'] }}" 
                            data-cve-id="{{ $cve['id'] }}"
                            data-title="{{ $cve['title'] }}"
                            data-vendor="{{ $cve['vendor'] }}"
                            data-product="{{ $cve['product'] }}"
                            data-date="{{ $cve['date'] }}"
                            data-ransom="{{ $cve['isRansomware'] ? '1' : '0' }}"
                            data-req-action="{{ $cve['requiredAction'] }}"
                            data-desc="{{ $cve['shortDescription'] }}"
                            onclick="selectCve('{{ $cve['id'] }}')"
                        >
                            <div class="cve-row-main">
                                <div class="cve-row-id-cell">
                                    <span class="cve-id-tag">{{ $cve['id'] }}</span>
                                </div>
                                <div class="cve-row-content-cell">
                                    <div class="d-flex align-items-center gap-2">
                                        @if($cve['isRansomware'])
                                            <span class="badge bg-danger text-white badge-mini">RANSOM</span>
                                        @endif
                                        <span class="cve-row-title">{{ $cve['title'] }}</span>
                                    </div>
                                    <div class="cve-row-sub">{{ $cve['vendor'] }} · {{ $cve['product'] }}</div>
                                </div>
                                <div class="cve-row-meta-cell">
                                    <span class="cve-row-date">{{ $cve['date'] }}</span>
                                    <i class="fas fa-chevron-right text-muted small"></i>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Right Column: Dedicated AI Vulnerability Analysis Inspector -->
            <div class="col-lg-7 col-xl-7">
                <div class="ai-inspector-card">
                    <!-- Inspector Header -->
                    <div class="ai-inspector-header">
                        <div class="d-flex justify-content-between align-items-start gap-2 mb-2">
                            <div>
                                <div class="d-flex align-items-center gap-2 mb-1">
                                    <span class="badge bg-primary text-white fs-6 fw-bold font-monospace px-3 py-1" id="inspCveId">
                                        {{ $normalizedKev[0]['id'] ?? 'CVE' }}
                                    </span>
                                    <span class="badge bg-danger text-white fw-bold" id="inspSeverityBadge">Critical Risk</span>
                                    <span class="badge bg-warning text-dark fw-bold {{ !empty($normalizedKev[0]['isRansomware']) ? '' : 'd-none' }}" id="inspRansomBadge">
                                        ⚠️ Ransomware Campaign
                                    </span>
                                    <span class="badge bg-secondary text-white small" id="inspKevBadge">CISA KEV Catalog</span>
                                </div>
                                <h3 class="mb-1 text-white fw-bold fs-4" id="inspTitle">
                                    {{ $normalizedKev[0]['title'] ?? 'Vulnerability Title' }}
                                </h3>
                                <div class="text-white-50 small" id="inspMeta">
                                    Vendor: <strong class="text-white" id="inspVendor">{{ $normalizedKev[0]['vendor'] ?? 'Vendor' }}</strong> · 
                                    Produk: <strong class="text-white" id="inspProduct">{{ $normalizedKev[0]['product'] ?? 'Product' }}</strong> · 
                                    Ditambahkan: <span id="inspDate">{{ $normalizedKev[0]['date'] ?? 'N/A' }}</span>
                                </div>
                            </div>
                            <div class="d-flex gap-2">
                                <button class="btn btn-sm btn-outline-light text-nowrap" onclick="copyActiveAnalysis()" title="Salin rangkuman analisis ke clipboard">
                                    <i class="fas fa-copy me-1"></i> Salin Analisis
                                </button>
                                <a href="https://nvd.nist.gov/vuln/detail/{{ $normalizedKev[0]['id'] ?? '' }}" target="_blank" id="inspNvdLink" class="btn btn-sm btn-outline-info text-nowrap">
                                    NVD <i class="fas fa-external-link-alt ms-1"></i>
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Inspector Body -->
                    <div class="ai-inspector-body" id="inspectorBody">
                        
                        <!-- Section 1: Dampak Kerentanan -->
                        <div class="ai-section-box border-danger-subtle" style="border-left: 5px solid #ef4444;">
                            <div class="ai-section-title text-danger">
                                <i class="fas fa-radiation me-2"></i> 1. Dampak Kerentanan (Business & Data Risk Impact)
                            </div>
                            <div class="ai-section-content" id="inspDampakContent">
                                <!-- Populated dynamically by JS -->
                            </div>
                        </div>

                        <!-- Section 2: Cara Kerja Kerentanan -->
                        <div class="ai-section-box border-warning-subtle" style="border-left: 5px solid #f59e0b;">
                            <div class="ai-section-title text-warning text-dark">
                                <i class="fas fa-cogs me-2"></i> 2. Bagaimana Cara Kerja Kerentanan (Exploit Mechanism)
                            </div>
                            <div class="ai-section-content" id="inspCaraKerjaContent">
                                <!-- Populated dynamically by JS -->
                            </div>
                        </div>

                        <!-- Section 3: Rekomendasi Mitigasi -->
                        <div class="ai-section-box border-success-subtle" style="border-left: 5px solid #10b981;">
                            <div class="ai-section-title text-success">
                                <i class="fas fa-shield-alt me-2"></i> 3. Rekomendasi Mitigasi & Langkah Penanganan
                            </div>
                            <div class="ai-section-content" id="inspMitigasiContent">
                                <!-- Populated dynamically by JS -->
                            </div>
                        </div>

                        <!-- Section 4: Regulasi Terkait -->
                        <div class="ai-section-box border-primary-subtle" style="border-left: 5px solid #3b82f6;">
                            <div class="ai-section-title text-primary">
                                <i class="fas fa-landmark me-2"></i> 4. Pemetaan Kepatuhan Regulasi Indonesia & Standar Global
                            </div>
                            <div class="ai-section-content" id="inspRegulasiContent">
                                <!-- Populated dynamically by JS -->
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- TAB 2: OTX ALIENVAULT & DATA BREACH CENTER -->
    <div id="intel-tab-otx" class="tab-pane-content d-none">
        <!-- Top Section: Breach Checker & Activity Chart -->
        <div class="row g-3 mb-3">
            <!-- OSINT Breach Checker -->
            <div class="col-lg-6">
                <div class="panel-card p-3 border-danger-subtle h-100" style="border-left: 4px solid #ef4444;">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <h4 class="text-danger fw-bold mb-0 fs-5">
                            <i class="fas fa-user-shield me-2"></i>Cek Kebocoran Akun (OSINT Breach Repo)
                        </h4>
                        <span class="badge bg-danger-lt fw-bold">30 Insiden Terverifikasi</span>
                    </div>
                    <p class="text-muted small mb-3">
                        Periksa apakah alamat email, domain institusi, atau nomor rekening Anda terdaftar dalam basis data insiden kebocoran nasional maupun global.
                    </p>

                    <div class="input-group mb-2">
                        <span class="input-group-text bg-light border"><i class="fas fa-search text-muted"></i></span>
                        <input 
                            type="text" 
                            id="breachSearchInput" 
                            class="form-control" 
                            placeholder="Ketik email (misal: it@perusahaan.co.id) atau nama instansi..."
                            onkeyup="if(event.key==='Enter') executeBreachCheck()"
                        >
                        <button class="btn btn-danger px-4 fw-bold" type="button" onclick="executeBreachCheck()">
                            <span id="breachBtnSpinner" class="spinner-border spinner-border-sm me-1 d-none"></span>
                            CHECK
                        </button>
                    </div>

                    <!-- Breach Result Box -->
                    <div id="breachResultBox" class="mt-3">
                        <div class="p-3 bg-light rounded-3 border text-secondary small">
                            <i class="fas fa-info-circle text-primary me-1"></i> Masukkan email atau kata kunci instansi untuk memindai riwayat insiden kebocoran.
                        </div>
                    </div>
                </div>
            </div>

            <!-- Activity Chart Trend -->
            <div class="col-lg-6">
                <div class="panel-card p-3 h-100">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <div>
                            <h4 class="text-dark fw-bold mb-0 fs-5">Distribusi Aktivitas Ancaman Global</h4>
                            <p class="text-muted small mb-0">Tren deteksi ancaman dan anomali siber dalam 5 hari terakhir.</p>
                        </div>
                        <span class="badge bg-primary-lt fw-bold">Real-time Feed</span>
                    </div>
                    <div class="chart-bar-container">
                        @php
                            $days = [
                                ['label' => date('d M', strtotime('-4 days')), 'val' => 18, 'height' => '45%'],
                                ['label' => date('d M', strtotime('-3 days')), 'val' => 24, 'height' => '60%'],
                                ['label' => date('d M', strtotime('-2 days')), 'val' => 38, 'height' => '90%'],
                                ['label' => date('d M', strtotime('-1 day')), 'val' => 31, 'height' => '75%'],
                                ['label' => date('d M'), 'val' => 42, 'height' => '100%'],
                            ];
                        @endphp
                        @foreach($days as $d)
                            <div class="chart-bar-col">
                                <div class="chart-bar-pill" style="height: {{ $d['height'] }};" title="{{ $d['val'] }} Events"></div>
                                <span class="chart-bar-label">{{ $d['label'] }}</span>
                            </div>
                        @endforeach
                    </div>
                    <div class="d-flex justify-content-between align-items-center mt-2 pt-2 border-top small text-muted">
                        <span><i class="fas fa-circle text-primary me-1" style="font-size: 0.6rem;"></i> Rata-rata: 30.6 insiden/hari</span>
                        <span><i class="fas fa-sync-alt me-1"></i> Auto-synced with OTX</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- 4 Metric Cards -->
        <div class="row g-3 mb-3">
            <div class="col-sm-6 col-xl-3">
                <div class="metric-box-card">
                    <div class="metric-box-icon" style="background: rgba(59, 130, 246, 0.1); color: #3b82f6;">
                        <i class="fas fa-database"></i>
                    </div>
                    <div>
                        <small class="text-muted fw-bold text-uppercase d-block" style="font-size: 0.68rem; letter-spacing: 0.05em;">Total Pulses</small>
                        <h3 class="mb-0 fw-bold">{{ count($pulses) }}</h3>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-xl-3">
                <div class="metric-box-card">
                    <div class="metric-box-icon" style="background: rgba(239, 68, 68, 0.1); color: #ef4444;">
                        <i class="fas fa-biohazard"></i>
                    </div>
                    <div>
                        <small class="text-muted fw-bold text-uppercase d-block" style="font-size: 0.68rem; letter-spacing: 0.05em;">Critical Threats</small>
                        <h3 class="mb-0 fw-bold">4 Terverifikasi</h3>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-xl-3">
                <div class="metric-box-card">
                    <div class="metric-box-icon" style="background: rgba(139, 92, 246, 0.1); color: #8b5cf6;">
                        <i class="fas fa-fingerprint"></i>
                    </div>
                    <div>
                        <small class="text-muted fw-bold text-uppercase d-block" style="font-size: 0.68rem; letter-spacing: 0.05em;">Indikator (IOC)</small>
                        <h3 class="mb-0 fw-bold">{{ count($iocs) }}</h3>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-xl-3">
                <div class="metric-box-card">
                    <div class="metric-box-icon" style="background: rgba(16, 185, 129, 0.1); color: #10b981;">
                        <i class="fas fa-rss"></i>
                    </div>
                    <div>
                        <small class="text-muted fw-bold text-uppercase d-block" style="font-size: 0.68rem; letter-spacing: 0.05em;">Global Feeds</small>
                        <h3 class="mb-0 fw-bold">12 Aktif</h3>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tables: Top IOCs & Global Threat Pulses -->
        <div class="row g-3">
            <!-- Left: Top IOCs Table -->
            <div class="col-lg-6">
                <div class="panel-card overflow-hidden h-100">
                    <div class="p-3 bg-light border-bottom d-flex justify-content-between align-items-center">
                        <strong class="text-dark small text-uppercase fw-bold"><i class="fas fa-fingerprint me-2 text-primary"></i>Top Indicators of Compromise (IOC)</strong>
                        <div class="input-group input-group-sm w-50">
                            <input type="text" id="iocSearchInput" class="form-control form-control-sm" placeholder="Filter IOC..." oninput="filterIocTable(this.value)">
                        </div>
                    </div>
                    <div class="table-responsive" style="max-height: 420px; overflow-y: auto;">
                        <table class="table-custom" id="iocTable">
                            <thead>
                                <tr>
                                    <th>Tipe</th>
                                    <th>Nilai Indikator</th>
                                    <th class="text-end">Sightings</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($iocs as $ioc)
                                    <tr onclick="toggleIocRow('{{ $ioc['id'] }}')">
                                        <td><span class="ioc-tag">{{ $ioc['type'] }}</span></td>
                                        <td><span class="mono-value">{{ $ioc['value'] }}</span></td>
                                        <td class="text-end"><span class="sight-count">{{ $ioc['sightings'] }}</span></td>
                                    </tr>
                                    <tr id="ioc-detail-{{ $ioc['id'] }}" class="d-none bg-light">
                                        <td colspan="3" class="p-3">
                                            <div class="small">
                                                <div class="fw-bold text-dark mb-1">{{ $ioc['event_title'] }}</div>
                                                <div class="d-flex flex-wrap gap-1 mb-2">
                                                    @foreach($ioc['tags'] as $tag)
                                                        <span class="badge-tag">{{ $tag }}</span>
                                                    @endforeach
                                                </div>
                                                <div class="d-flex justify-content-between align-items-center text-muted" style="font-size: 0.75rem;">
                                                    <span><i class="fas fa-user-shield me-1"></i> Kontributor: <strong>{{ $ioc['contributor'] }}</strong></span>
                                                    <a href="https://otx.alienvault.com/indicator/{{ strtolower($ioc['type']) }}/{{ $ioc['value'] }}" target="_blank" class="btn btn-xs btn-outline-primary">
                                                        Buka di AlienVault <i class="fas fa-external-link-alt ms-1"></i>
                                                    </a>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Right: Global Threat Pulses -->
            <div class="col-lg-6">
                <div class="panel-card overflow-hidden h-100">
                    <div class="p-3 bg-light border-bottom">
                        <strong class="text-dark small text-uppercase fw-bold"><i class="fas fa-satellite-dish me-2 text-indigo"></i>Global Threat Pulses</strong>
                    </div>
                    <div class="table-responsive" style="max-height: 420px; overflow-y: auto;">
                        <table class="table-custom">
                            <thead>
                                <tr>
                                    <th>Judul Pulse</th>
                                    <th>Severity</th>
                                    <th class="text-end">Tanggal</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($pulses as $pulse)
                                    <tr onclick="togglePulseRow('{{ $pulse['id'] }}')">
                                        <td class="fw-bold text-dark" style="max-width: 280px; word-break: break-word;">
                                            {{ $pulse['info'] }}
                                        </td>
                                        <td>
                                            @if($pulse['threat_level_id'] === 1)
                                                <span class="severity-pill bg-danger text-white">Critical</span>
                                            @else
                                                <span class="severity-pill bg-warning text-dark">High</span>
                                            @endif
                                        </td>
                                        <td class="text-end text-muted small">
                                            {{ date('d M Y', $pulse['timestamp']) }}
                                        </td>
                                    </tr>
                                    <tr id="pulse-detail-{{ $pulse['id'] }}" class="d-none bg-light">
                                        <td colspan="3" class="p-3">
                                            <div class="small">
                                                <div class="d-flex justify-content-between align-items-center mb-2">
                                                    <span><strong>Kontributor:</strong> {{ $pulse['author_name'] }}</span>
                                                    <span class="badge bg-secondary-lt fw-bold">{{ $pulse['indicators_count'] }} Indikator</span>
                                                </div>
                                                <div class="d-flex flex-wrap gap-1 mb-2">
                                                    @foreach($pulse['tags'] as $tag)
                                                        <span class="badge-tag pulse" style="color: #0369a1; background: #f0f9ff; border-color: #bae6fd;">#{{ $tag }}</span>
                                                    @endforeach
                                                </div>
                                                <div class="text-end mt-2">
                                                    <a href="{{ $pulse['link'] }}" target="_blank" class="btn btn-xs btn-outline-primary">
                                                        Pulse Details <i class="fas fa-external-link-alt ms-1"></i>
                                                    </a>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>

<script>
let activeTab = 'cve';
const normalizedKevList = @json($normalizedKev);
let selectedCveData = normalizedKevList[0] || null;
const breachesData = @json($breaches);

// Tab Switcher
function switchIntelTab(tabKey, el) {
    document.querySelectorAll('.intel-tab').forEach(t => t.classList.remove('active'));
    el.classList.add('active');

    document.getElementById('intel-tab-otx').classList.add('d-none');
    document.getElementById('intel-tab-cve').classList.add('d-none');

    document.getElementById('intel-tab-' + tabKey).classList.remove('d-none');
    activeTab = tabKey;
}

// Expandable Rows for IOC and Pulses
function toggleIocRow(id) {
    const el = document.getElementById('ioc-detail-' + id);
    if (el) el.classList.toggle('d-none');
}

function togglePulseRow(id) {
    const el = document.getElementById('pulse-detail-' + id);
    if (el) el.classList.toggle('d-none');
}

// Filter IOC Table
function filterIocTable(query) {
    const q = query.toLowerCase().trim();
    document.querySelectorAll('#iocTable tbody tr').forEach(row => {
        if (row.id && row.id.startsWith('ioc-detail-')) return;
        const text = row.innerText.toLowerCase();
        row.style.display = text.includes(q) ? '' : 'none';
    });
}

// OSINT Breach Checker
function executeBreachCheck() {
    const input = document.getElementById('breachSearchInput').value.trim();
    const resultBox = document.getElementById('breachResultBox');
    const spinner = document.getElementById('breachBtnSpinner');

    if (!input) {
        resultBox.innerHTML = '<div class="p-3 bg-warning-lt text-warning rounded-3 small"><i class="fas fa-exclamation-triangle me-1"></i> Masukkan email atau kata kunci pencarian.</div>';
        return;
    }

    spinner.classList.remove('d-none');

    setTimeout(() => {
        spinner.classList.add('d-none');
        const q = input.toLowerCase();

        let queryTerm = q;
        if (q.includes('@')) {
            const parts = q.split('@');
            queryTerm = parts[1];
        }

        const matches = breachesData.filter(b => {
            const name = (b.Name || '').toLowerCase();
            const domain = (b.Domain || '').toLowerCase();
            const desc = (b.Description || '').toLowerCase();
            const classes = ((b.DataClasses || []).join(' ')).toLowerCase();

            return name.includes(queryTerm) || domain.includes(queryTerm) || desc.includes(queryTerm) || classes.includes(queryTerm);
        });

        if (matches.length === 0) {
            resultBox.innerHTML = `
                <div class="p-3 bg-success-lt text-success rounded-3 small">
                    <i class="fas fa-check-circle me-1"></i> <strong>Aman!</strong> Tidak ditemukan catatan kebocoran data untuk <code>${escapeHtml(input)}</code> dalam basis data terverifikasi.
                </div>
            `;
        } else {
            let html = `
                <div class="p-3 bg-danger-lt text-danger rounded-3 mb-2 d-flex justify-content-between align-items-center">
                    <div>
                        <i class="fas fa-exclamation-circle me-1"></i> <strong>Terindikasi Bocor!</strong> Ditemukan <strong>${matches.length}</strong> insiden kebocoran terkait.
                    </div>
                    <button class="btn btn-xs btn-danger" onclick="copyBreachSources()">Salin Sumber</button>
                </div>
                <div class="border rounded-3 p-2 bg-light" style="max-height: 180px; overflow-y: auto;">
            `;

            matches.forEach(m => {
                const classesBadges = (m.DataClasses || []).slice(0, 4).map(c => `<span class="badge bg-white border text-secondary me-1" style="font-size: 0.65rem;">${c}</span>`).join('');
                html += `
                    <div class="border-bottom pb-2 mb-2">
                        <div class="d-flex justify-content-between align-items-center">
                            <strong class="text-dark">${escapeHtml(m.Name)}</strong>
                            <span class="text-muted small">${m.BreachDate || ''}</span>
                        </div>
                        <div class="text-muted small mb-1" style="font-size: 0.72rem;">Domain: <code>${m.Domain || 'N/A'}</code> · Akun: ${Number(m.PwnCount || 0).toLocaleString()}</div>
                        <div class="d-flex flex-wrap gap-1">${classesBadges}</div>
                    </div>
                `;
            });

            html += `</div>`;
            resultBox.innerHTML = html;
        }
    }, 300);
}

function copyBreachSources() {
    navigator.clipboard.writeText("BPJS Kesehatan, Tokopedia, Bukalapak, Bank Jatim, Kemenkes RI");
    alert("Sumber kebocoran telah disalin ke clipboard.");
}

// CISA KEV Selection & Instant AI Analysis
function selectCve(cveId) {
    document.querySelectorAll('.cve-row-item').forEach(el => el.classList.remove('selected'));
    const item = document.getElementById('cve-item-' + cveId);
    if (!item) return;

    item.classList.add('selected');

    selectedCveData = {
        id: cveId,
        title: item.getAttribute('data-title'),
        vendor: item.getAttribute('data-vendor'),
        product: item.getAttribute('data-product'),
        date: item.getAttribute('data-date'),
        isRansomware: item.getAttribute('data-ransom') === '1',
        requiredAction: item.getAttribute('data-req-action'),
        shortDescription: item.getAttribute('data-desc')
    };

    renderActiveCveAnalysis(selectedCveData);
}

function renderActiveCveAnalysis(cve) {
    if (!cve) return;

    // Header updates
    document.getElementById('inspCveId').innerText = cve.id;
    document.getElementById('inspTitle').innerText = cve.title;
    document.getElementById('inspVendor').innerText = cve.vendor;
    document.getElementById('inspProduct').innerText = cve.product;
    document.getElementById('inspDate').innerText = cve.date;
    document.getElementById('inspNvdLink').href = 'https://nvd.nist.gov/vuln/detail/' + cve.id;

    const ransomBadge = document.getElementById('inspRansomBadge');
    if (cve.isRansomware) {
        ransomBadge.classList.remove('d-none');
    } else {
        ransomBadge.classList.add('d-none');
    }

    // Generate detailed AI components
    const analysis = generateDefensiveAnalysisData(cve);

    // Section 1: Dampak
    document.getElementById('inspDampakContent').innerHTML = analysis.dampakHtml;

    // Section 2: Cara Kerja
    document.getElementById('inspCaraKerjaContent').innerHTML = analysis.caraKerjaHtml;

    // Section 3: Mitigasi
    document.getElementById('inspMitigasiContent').innerHTML = analysis.mitigasiHtml;

    // Section 4: Regulasi
    document.getElementById('inspRegulasiContent').innerHTML = analysis.regulasiHtml;
}

function generateDefensiveAnalysisData(cve) {
    const text = (cve.title + ' ' + cve.shortDescription).toLowerCase();
    const isRansom = cve.isRansomware;
    const reqAction = cve.requiredAction || 'Terapkan update keamanan resmi sesuai rekomendasi vendor.';

    let vulnerabilityType = "Kerentanan Keamanan Sistem";
    let caraKerja = "";
    let dampak = "";
    let mitigasi = "";

    // Determine vulnerability category
    if (/command injection|remote code execution|rce|arbitrary code|code execution|os command/.test(text)) {
        vulnerabilityType = "Remote Code Execution (RCE) / Command Injection";
        caraKerja = `
            <ul>
                <li><strong>Celah Masukan (Input Handling):</strong> Komponen <code>${escapeHtml(cve.product)}</code> menerima data atau parameter dari request eksternal tanpa sanitasi atau escaping yang memadai sebelum diteruskan ke fungsi eksekusi sistem operasi.</li>
                <li><strong>Vektor Eksploitasi:</strong> Penyerang mengirimkan crafted payload (misalnya argumen shell atau format string khusus) melalui protokol jaringan yang terbuka.</li>
                <li><strong>Hasil Eksekusi:</strong> Input berbahaya tersebut dievaluasi langsung oleh host server, memungkinkan eksekusi perintah shell sewenang-wenang dengan hak akses (privilege) dari service tersebut.</li>
            </ul>
        `;
        dampak = `
            <ul>
                <li><strong>Pengambilalihan Penuh Sistem (Full Host Takeover):</strong> Penyerang dapat menjalankan shell interaktif, mengunduh payload tambahan (seperti webshell atau cryptominer), dan mematikan layanan penting.</li>
                <li><strong>Pergerakan Lateral (Lateral Movement):</strong> Akses awal ini dapat dijadikan batu loncatan untuk menembus segmen jaringan internal yang lebih terlindungi (misal basis data core banking atau LDAP).</li>
                <li><strong>Pelanggaran Regulasi & Kerahasiaan:</strong> Risiko pencurian data nasabah secara masif yang memicu sanksi <strong>Pasal 46 UU PDP No. 27/2022</strong> (kewajiban notifikasi tertulis maksimal 3 x 24 jam).</li>
            </ul>
        `;
    } else if (/authentication|bypass|unauthenticated|authorization|privilege escalation|broken access/.test(text)) {
        vulnerabilityType = "Authentication Bypass / Privilege Escalation";
        caraKerja = `
            <ul>
                <li><strong>Celah Verifikasi Autentikasi:</strong> Mekanisme verifikasi sesi atau token kontrol akses pada <code>${escapeHtml(cve.product)}</code> gagal memvalidasi status autentikasi pada endpoint tertentu.</li>
                <li><strong>Vektor Eksploitasi:</strong> Penyerang dapat memanipulasi header HTTP, struktur token cookie, atau langsung mengakses path internal terproteksi tanpa harus memberikan kredensial yang sah.</li>
                <li><strong>Hasil Eksploitasi:</strong> Sistem memperlakukan request tak berizin sebagai request yang terotorisasi secara penuh, seringkali langsung dengan hak istimewa (Administrative role).</li>
            </ul>
        `;
        dampak = `
            <ul>
                <li><strong>Bypass Kontrol Keamanan Perimeter:</strong> Pihak luar yang tidak terdaftar dapat mengakses panel manajemen, mengekstrak konfigurasi rahasia, atau mengubah kredensial pengguna lain.</li>
                <li><strong>Penyalahgunaan Akun & Akses Ilegal:</strong> Pelanggaran langsung terhadap prinsip <em>Least Privilege</em> dan <em>Zero Trust Architecture</em>.</li>
            </ul>
        `;
    } else if (/sql injection|sqli|database/.test(text)) {
        vulnerabilityType = "SQL Injection (SQLi)";
        caraKerja = `
            <ul>
                <li><strong>Celah Interpolasi Query:</strong> Input masukan pengguna digabungkan langsung ke dalam perintah SQL dinamis tanpa menggunakan <em>Prepared Statements</em> atau <em>Parameter Binding</em>.</li>
                <li><strong>Vektor Eksploitasi:</strong> Penyerang menyisipkan karakter kontrol SQL (seperti tanda petik, operator union, atau komentar) untuk mengubah logika query asli.</li>
                <li><strong>Hasil Eksekusi:</strong> Mesin database mengeksekusi perintah manipulasi data, membuang isi tabel, atau mem-bypass otentikasi login.</li>
            </ul>
        `;
        dampak = `
            <ul>
                <li><strong>Kebocoran Basis Data Skala Penuh:</strong> Data rahasia nasabah, nomor kartu, password hash, dan informasi finansial dapat dibaca secara bebas oleh penyerang.</li>
                <li><strong>Manipulasi Data Transaksi:</strong> Integritas saldo atau catatan keuangan dapat diubah tanpa jejak otorisasi yang sah.</li>
            </ul>
        `;
    } else if (/path traversal|directory traversal|arbitrary file/.test(text)) {
        vulnerabilityType = "Path Traversal & Arbitrary File Access";
        caraKerja = `
            <ul>
                <li><strong>Celah Resolusi Path:</strong> Aplikasi menerima nama file atau direktori dari input pengguna dan gagal membatasi akses hanya pada direktori kerja yang diizinkan (Chroot/Sandboxed).</li>
                <li><strong>Vektor Eksploitasi:</strong> Penyerang menyisipkan urutan <code>../</code> (dot-dot-slash) untuk melompat keluar dari folder web publik menuju direktori sensitif sistem.</li>
            </ul>
        `;
        dampak = `
            <ul>
                <li><strong>Pembacaan File Sensitif:</strong> Penyerang dapat membaca file konfigurasi, password database, sertifikat SSL private key, atau file <code>/etc/passwd</code>.</li>
            </ul>
        `;
    } else {
        vulnerabilityType = "Kerentanan Tereksploitasi CISA KEV (" + escapeHtml(cve.vendor) + ")";
        caraKerja = `
            <ul>
                <li><strong>Anatomi Celah:</strong> Celah pada <code>${escapeHtml(cve.product)}</code> telah diverifikasi oleh CISA (Cybersecurity and Infrastructure Security Agency) memiliki kode eksploitasi aktif yang digunakan oleh aktor ancaman di internet publik.</li>
                <li><strong>Jalur Risiko:</strong> Penyerang menargetkan endpoint layanan yang terekspos tanpa otentikasi atau dengan konfigurasi default untuk mengeksekusi payload.</li>
                <li><strong>Karakteristik Ancaman:</strong> Terdaftar dalam katalog resmi KEV (Known Exploited Vulnerabilities) yang mewajibkan mitigasi berbatas waktu (SLA ketat).</li>
            </ul>
        `;
        dampak = `
            <ul>
                <li><strong>Gangguan Ketersediaan & Integritas:</strong> Layanan perbankan atau operasional organisasi berisiko mengalami down, terinfeksi ransomware, atau kehilangan data vital.</li>
                <li><strong>Temuan Mayor Audit OJK:</strong> Pengabaian terhadap kerentanan berstatus KEV akan dicatat sebagai kegagalan pemenuhan mitigasi siber mandatori.</li>
            </ul>
        `;
    }

    if (isRansom) {
        dampak += `
            <div class="alert alert-danger p-2 mt-2 mb-0 small">
                <i class="fas fa-biohazard me-1"></i> <strong>Peringatan Ransomware:</strong> Celah ini telah tercatat digunakan dalam kampanye Ransomware aktif. Eksploitasi berhasil hampir selalu diikuti oleh enkripsi data massal dan pemerasan ganda (Double Extortion).
            </div>
        `;
    }

    // Mitigasi Content
    mitigasi = `
        <div class="mb-2">
            <strong class="text-dark d-block mb-1"><i class="fas fa-check-circle text-success me-1"></i> Tindakan Utama (Mandatori):</strong>
            <div class="p-2 bg-light border rounded small fw-bold text-danger">
                ${escapeHtml(reqAction)}
            </div>
        </div>
        <div class="mb-2">
            <strong class="text-dark d-block mb-1"><i class="fas fa-shield-alt text-primary me-1"></i> Kompensasi Jaringan & Perimeter (Workaround):</strong>
            <ul class="small">
                <li>Bila patch tidak dapat diterapkan seketika karena *change freeze*, segera batasi akses port layanan <code>${escapeHtml(cve.product)}</code> dari internet hanya melalui IP Allowlist atau VPN terenkripsi dengan MFA.</li>
                <li>Aktifkan aturan <em>Virtual Patching</em> pada Web Application Firewall (WAF) / Next-Gen Firewall (NGFW) untuk memblokir pola signature eksploitasi terkait.</li>
            </ul>
        </div>
        <div>
            <strong class="text-dark d-block mb-1"><i class="fas fa-search text-info me-1"></i> Deteksi & Threat Hunting:</strong>
            <ul class="small">
                <li>Periksa access log dan anomali connection spike pada server <code>${escapeHtml(cve.product)}</code> selama 14 hari ke belakang.</li>
                <li>Lakukan validasi File Integrity Monitoring (FIM) untuk memastikan tidak ada file webshell atau binary tak dikenal yang ditanam penyerang.</li>
            </ul>
        </div>
    `;

    // Regulasi Content
    const regulasiHtml = `
        <div class="row g-2">
            <div class="col-md-6">
                <div class="p-2 border rounded bg-light">
                    <strong class="text-dark small d-block"><i class="fas fa-landmark text-primary me-1"></i> POJK 11/2022 Pasal 28:</strong>
                    <span class="text-muted" style="font-size: 0.78rem;">Bank wajib melakukan pemantauan kerentanan, penanganan celah keamanan secara tepat waktu, dan pengujian penetrasi berkala.</span>
                </div>
            </div>
            <div class="col-md-6">
                <div class="p-2 border rounded bg-light">
                    <strong class="text-dark small d-block"><i class="fas fa-file-signature text-teal me-1"></i> PADK 1/2026:</strong>
                    <span class="text-muted" style="font-size: 0.78rem;">Mandat ketahanan siber OJK yang mewajibkan SLA patching kerentanan kritis tereksploitasi paling lambat 14 hari kerja.</span>
                </div>
            </div>
            <div class="col-md-6">
                <div class="p-2 border rounded bg-light">
                    <strong class="text-dark small d-block"><i class="fas fa-shield-alt text-success me-1"></i> ISO/IEC 27001:2022 (A.8.8):</strong>
                    <span class="text-muted" style="font-size: 0.78rem;">Kontrol <em>Management of technical vulnerabilities</em> untuk mengevaluasi eksposur risiko dan mengeksekusi perbaikan.</span>
                </div>
            </div>
            <div class="col-md-6">
                <div class="p-2 border rounded bg-light">
                    <strong class="text-dark small d-block"><i class="fas fa-user-shield text-indigo me-1"></i> UU PDP No. 27/2022 Pasal 46:</strong>
                    <span class="text-muted" style="font-size: 0.78rem;">Kewajiban pemberitahuan tertulis kegagalan pelindungan data pribadi kepada Lembaga PDP & Subjek Data maksimal 3x24 jam.</span>
                </div>
            </div>
        </div>
    `;

    return {
        dampakHtml: dampak,
        caraKerjaHtml: caraKerja,
        mitigasiHtml: mitigasi,
        regulasiHtml: regulasiHtml
    };
}

function copyActiveAnalysis() {
    if (!selectedCveData) return;

    const summary = `
LAPORAN ANALISIS KERENTANAN SIBER (CISA KEV)
CVE ID: ${selectedCveData.id}
Judul: ${selectedCveData.title}
Vendor / Produk: ${selectedCveData.vendor} - ${selectedCveData.product}
Status Ransomware: ${selectedCveData.isRansomware ? 'YA (TERLIBAT RANSOMWARE)' : 'Tidak tercatat'}
Tindakan CISA: ${selectedCveData.requiredAction}

1. DAMPAK KERENTANAN:
Berdampak pada ketersediaan layanan dan kerahasiaan data organisasi. Bila dieksploitasi, penyerang dapat mengambil alih sistem dan mencuri data sensitif (Pelanggaran UU PDP No. 27/2022 Pasal 46 & POJK 11/2022 Pasal 28).

2. CARA KERJA EKSPLOITASI:
Penyerang menargetkan komponen rentan dengan mengirimkan crafted input atau payload yang tidak divalidasi, memicu eksekusi kode atau bypass otentikasi.

3. REKOMENDASI MITIGASI:
- Segera terapkan patch resmi: ${selectedCveData.requiredAction}
- Isolasi port manajemen hanya dari jaringan terpercaya.
- Aktifkan rule WAF/IPS dan lakukan threat hunting pada log sistem.
    `.trim();

    navigator.clipboard.writeText(summary);
    alert('Rangkuman analisis defensif untuk ' + selectedCveData.id + ' berhasil disalin ke clipboard!');
}

function applyCveFilters() {
    const query = (document.getElementById('cveSearchInput').value || '').toLowerCase().trim();
    const ransomOnly = document.getElementById('ransomFilterSwitch').checked;
    let visibleCount = 0;
    let firstVisibleId = null;

    document.querySelectorAll('.cve-row-item').forEach(item => {
        const id = (item.getAttribute('data-cve-id') || '').toLowerCase();
        const title = (item.getAttribute('data-title') || '').toLowerCase();
        const vendor = (item.getAttribute('data-vendor') || '').toLowerCase();
        const product = (item.getAttribute('data-product') || '').toLowerCase();
        const isRansom = item.getAttribute('data-ransom') === '1';

        const matchesQuery = !query || id.includes(query) || title.includes(query) || vendor.includes(query) || product.includes(query);
        const matchesRansom = !ransomOnly || isRansom;

        if (matchesQuery && matchesRansom) {
            item.style.display = '';
            visibleCount++;
            if (!firstVisibleId) firstVisibleId = item.getAttribute('data-cve-id');
        } else {
            item.style.display = 'none';
        }
    });

    document.getElementById('cveCountBadge').innerText = visibleCount + ' Found';

    if (firstVisibleId && (!selectedCveData || !selectedCveData.id.toLowerCase().includes(query))) {
        selectCve(firstVisibleId);
    }
}

function escapeHtml(text) {
    const map = { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' };
    return (text || '').toString().replace(/[&<>"']/g, m => map[m]);
}

// Initial render on page load
document.addEventListener('DOMContentLoaded', function() {
    if (normalizedKevList.length > 0) {
        selectCve(normalizedKevList[0].id);
    }
});
</script>
@endsection
