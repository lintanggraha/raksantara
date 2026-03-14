@extends('layouts.tabler')

@section('title', 'Threat Intelligence - Risk Management System')

@php
    $summary = $dashboard['summary'] ?? [];
    $analytics = $dashboard['analytics'] ?? [];
    $priorityQueue = $analytics['priority_queue'] ?? [];
    $severityBreakdown = $analytics['severity_breakdown'] ?? ['labels' => [], 'values' => []];
    $publicationTrend = $analytics['publication_trend'] ?? ['labels' => [], 'values' => []];
    $vendorExposure = $analytics['vendor_exposure'] ?? ['labels' => [], 'values' => []];
    $owaspBreakdown = $analytics['owasp_breakdown'] ?? ['labels' => [], 'values' => []];
    $owaspTaxonomy = data_get($dashboard, 'owasp.taxonomy', []);
    $sources = $dashboard['sources'] ?? [];
    $cves = $dashboard['cves'] ?? [];
    $kevItems = data_get($dashboard, 'kev.items', []);
    $sourceHealthy = collect($sources)->every(function ($source) {
        return ($source['status'] ?? 'error') === 'ok';
    });
@endphp

@push('styles')
<style>
    .thintel-hero {
        border: 0;
        border-radius: 24px;
        background:
            radial-gradient(circle at top right, rgba(34, 197, 94, 0.16), transparent 26%),
            radial-gradient(circle at bottom left, rgba(59, 130, 246, 0.22), transparent 32%),
            linear-gradient(135deg, #081120 0%, #0f2743 48%, #144b67 100%);
        color: #f8fafc;
        box-shadow: 0 24px 60px rgba(8, 17, 32, 0.26);
    }

    .thintel-hero .badge {
        background: rgba(255, 255, 255, 0.1);
        color: #f8fafc;
        border: 1px solid rgba(255, 255, 255, 0.12);
        font-weight: 600;
        padding: 0.45rem 0.75rem;
    }

    .thintel-card {
        border: 1px solid #d9e2ec;
        border-radius: 20px;
        box-shadow: 0 16px 36px rgba(15, 23, 42, 0.08);
    }

    .thintel-stat {
        border-radius: 20px;
        min-height: 150px;
        padding: 1rem;
        position: relative;
        overflow: hidden;
    }

    .thintel-stat.dark { background: linear-gradient(135deg, #12263a 0%, #22466b 100%); color: #f8fafc; }
    .thintel-stat.red { background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%); color: #7f1d1d; }
    .thintel-stat.amber { background: linear-gradient(135deg, #fff7d6 0%, #fef08a 100%); color: #713f12; }
    .thintel-stat.cyan { background: linear-gradient(135deg, #e0f2fe 0%, #bae6fd 100%); color: #0c4a6e; }

    .thintel-stat-label {
        font-size: 0.74rem;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        font-weight: 700;
        opacity: 0.84;
        margin-bottom: 0.75rem;
    }

    .thintel-stat-value {
        font-size: 2rem;
        font-weight: 800;
        line-height: 1;
    }

    .thintel-stat-copy {
        margin-top: 0.6rem;
        font-size: 0.84rem;
    }

    .thintel-panel-title {
        margin: 0;
        font-size: 1rem;
        font-weight: 700;
        color: #0f172a;
    }

    .thintel-panel-copy {
        color: #64748b;
        font-size: 0.84rem;
    }

    .thintel-priority-item,
    .thintel-feed-item,
    .thintel-source-item {
        border: 1px solid #dbe4ef;
        border-radius: 18px;
        background: linear-gradient(180deg, #ffffff 0%, #f8fbff 100%);
        padding: 1rem;
    }

    .thintel-priority-item + .thintel-priority-item,
    .thintel-feed-item + .thintel-feed-item,
    .thintel-source-item + .thintel-source-item {
        margin-top: 0.85rem;
    }

    .thintel-pill {
        display: inline-flex;
        align-items: center;
        border-radius: 999px;
        padding: 0.22rem 0.58rem;
        font-size: 0.72rem;
        font-weight: 700;
        line-height: 1.2;
        background: #eff6ff;
        color: #1d4ed8;
    }

    .thintel-pill.alert { background: #fee2e2; color: #991b1b; }
    .thintel-pill.warn { background: #fef3c7; color: #92400e; }
    .thintel-pill.good { background: #dcfce7; color: #166534; }
    .thintel-pill.dark { background: #e2e8f0; color: #334155; }

    .thintel-severity {
        display: inline-flex;
        align-items: center;
        border-radius: 999px;
        padding: 0.25rem 0.68rem;
        font-size: 0.72rem;
        font-weight: 800;
        letter-spacing: 0.02em;
    }

    .thintel-severity-CRITICAL { background: #7f1d1d; color: #fff; }
    .thintel-severity-HIGH { background: #dc2626; color: #fff; }
    .thintel-severity-MEDIUM { background: #f59e0b; color: #1f2937; }
    .thintel-severity-LOW { background: #d1fae5; color: #065f46; }
    .thintel-severity-UNKNOWN { background: #e2e8f0; color: #334155; }

    .thintel-chart {
        position: relative;
        height: 290px;
    }

    .thintel-chart.short {
        height: 240px;
    }

    .thintel-scroll {
        max-height: 720px;
        overflow-y: auto;
        padding-right: 0.25rem;
    }

    .thintel-empty {
        border: 1px dashed #cbd5e1;
        border-radius: 18px;
        padding: 1rem;
        background: #f8fafc;
        color: #64748b;
    }

    .thintel-tab-nav {
        display: flex;
        flex-wrap: wrap;
        gap: 0.55rem;
    }

    .thintel-tab-nav .nav-link {
        border: 1px solid #dbe4ef;
        border-radius: 999px;
        color: #0f3c59;
        font-weight: 700;
        background: #fff;
        padding: 0.45rem 0.85rem;
    }

    .thintel-tab-nav .nav-link.active {
        color: #fff;
        background: #0f4c81;
        border-color: #0f4c81;
        box-shadow: 0 10px 22px rgba(15, 76, 129, 0.2);
    }
</style>
@endpush

@section('content_header')
<div class="row align-items-center">
    <div class="col">
        <h2 class="page-title mb-1">Threat Intelligence</h2>
        <div class="text-muted">
            Triage dashboard untuk CVE terbaru, overlap dengan CISA KEV, dan source health dalam satu layar.
        </div>
    </div>
</div>
@endsection

@section('content')
<div class="card thintel-hero mb-4">
    <div class="card-body p-4 p-lg-5">
        <div class="row g-4 align-items-start">
            <div class="col-xl-7">
                <div class="text-uppercase small fw-bold opacity-75 mb-2">Mission Control</div>
                <h1 class="mb-3" style="font-size:clamp(2rem,4vw,3rem);line-height:1;font-weight:800;letter-spacing:-0.04em;">Threat Intel Triage Board</h1>
                <p class="mb-4 text-white-50">
                    Fokuskan analis ke item yang paling layak ditindak: KEV overlap, severity tinggi, CVSS tinggi, dan vendor exposure yang dominan.
                </p>
                <div class="d-flex flex-wrap gap-2">
                    <span class="badge">NVD CVE API</span>
                    <span class="badge">CISA KEV</span>
                    <span class="badge">Window {{ (int) ($dashboard['filters']['days'] ?? 30) }} days</span>
                    <span class="badge">Updated {{ $dashboard['generated_at'] }}</span>
                </div>
            </div>
            <div class="col-xl-5">
                <div class="row g-3">
                    <div class="col-6">
                        <div class="thintel-stat dark">
                            <div class="thintel-stat-label">Displayed CVEs</div>
                            <div class="thintel-stat-value">{{ number_format((int) ($summary['displayed_cves'] ?? 0)) }}</div>
                            <div class="thintel-stat-copy">Current working set.</div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="thintel-stat red">
                            <div class="thintel-stat-label">KEV Match Ratio</div>
                            <div class="thintel-stat-value">{{ number_format((int) ($summary['kev_match_ratio'] ?? 0)) }}%</div>
                            <div class="thintel-stat-copy">Share of visible CVEs that overlap with KEV.</div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="thintel-stat amber">
                            <div class="thintel-stat-label">High + Critical</div>
                            <div class="thintel-stat-value">{{ number_format((int) ($summary['high_or_critical'] ?? 0)) }}</div>
                            <div class="thintel-stat-copy">Immediate review band.</div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="thintel-stat cyan">
                            <div class="thintel-stat-label">Source Health</div>
                            <div class="thintel-stat-value">{{ $sourceHealthy ? 'OK' : 'WARN' }}</div>
                            <div class="thintel-stat-copy">{{ $sourceHealthy ? 'Both feeds are healthy.' : 'One or more feeds need attention.' }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card thintel-card mb-4">
    <div class="card-body">
        <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-3">
            <div>
                <h3 class="thintel-panel-title">Filter the working set</h3>
                <div class="thintel-panel-copy">Keep the page focused by narrowing keyword, severity, time window, and list depth.</div>
            </div>
            <span class="thintel-pill">{{ number_format((int) ($summary['nvd_total_results'] ?? 0)) }} total NVD matches</span>
        </div>
        <form method="GET" action="{{ route('threat-intelligence.index') }}">
            <div class="row g-3 align-items-end">
                <div class="col-lg-4">
                    <label for="keyword" class="form-label">Keyword</label>
                    <input type="text" class="form-control" id="keyword" name="keyword" value="{{ $dashboard['filters']['keyword'] }}" placeholder="Examples: Laravel, Fortinet, banking, OpenSSH">
                </div>
                <div class="col-md-3 col-lg-2">
                    <label for="severity" class="form-label">Severity</label>
                    <select class="form-select no-search-select" id="severity" name="severity">
                        <option value="">All</option>
                        @foreach (['CRITICAL', 'HIGH', 'MEDIUM', 'LOW'] as $severity)
                            <option value="{{ $severity }}" @selected($dashboard['filters']['severity'] === $severity)>{{ $severity }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 col-lg-2">
                    <label for="owasp" class="form-label">OWASP</label>
                    <select class="form-select no-search-select" id="owasp" name="owasp">
                        <option value="">All</option>
                        @foreach ($owaspTaxonomy as $owaspItem)
                            <option value="{{ $owaspItem['code'] }}" @selected(($dashboard['filters']['owasp'] ?? '') === $owaspItem['code'])>
                                {{ $owaspItem['code'] }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 col-lg-2">
                    <label for="days" class="form-label">Window</label>
                    <select class="form-select no-search-select" id="days" name="days">
                        @foreach ([7 => '7 days', 30 => '30 days', 90 => '90 days'] as $value => $label)
                            <option value="{{ $value }}" @selected((int) ($dashboard['filters']['days'] ?? 30) === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 col-lg-1">
                    <label for="limit" class="form-label">Limit</label>
                    <select class="form-select no-search-select" id="limit" name="limit">
                        @foreach ([10, 12, 20, 50] as $value)
                            <option value="{{ $value }}" @selected((int) ($dashboard['filters']['limit'] ?? 12) === $value)>{{ $value }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 col-lg-1">
                    <button type="submit" class="btn btn-primary w-100">Apply</button>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-6 col-xl-3">
        <div class="thintel-stat dark">
            <div class="thintel-stat-label">KEV Matches</div>
            <div class="thintel-stat-value">{{ number_format((int) ($summary['known_exploited_on_page'] ?? 0)) }}</div>
            <div class="thintel-stat-copy">Visible CVEs that also exist in the CISA KEV catalog.</div>
        </div>
    </div>
    <div class="col-md-6 col-xl-3">
        <div class="thintel-stat red">
            <div class="thintel-stat-label">Critical CVE</div>
            <div class="thintel-stat-value">{{ number_format((int) ($summary['critical_cves'] ?? 0)) }}</div>
            <div class="thintel-stat-copy">Highest severity items in the current view.</div>
        </div>
    </div>
    <div class="col-md-6 col-xl-3">
        <div class="thintel-stat amber">
            <div class="thintel-stat-label">OWASP Mapped</div>
            <div class="thintel-stat-value">{{ number_format((int) ($summary['owasp_mapped'] ?? 0)) }}</div>
            <div class="thintel-stat-copy">Visible CVEs that can be mapped to OWASP Top 10 categories.</div>
        </div>
    </div>
    <div class="col-md-6 col-xl-3">
        <div class="thintel-stat cyan">
            <div class="thintel-stat-label">Ransomware Flag</div>
            <div class="thintel-stat-value">{{ number_format((int) ($summary['ransomware_flagged'] ?? 0)) }}</div>
            <div class="thintel-stat-copy">KEV items with known ransomware use.</div>
        </div>
    </div>
</div>

<div class="card thintel-card mb-4">
    <div class="card-header bg-transparent border-0 pb-0">
        <h3 class="thintel-panel-title">Analytics</h3>
        <div class="thintel-panel-copy">Compact visual summary of severity, publication timing, and vendor concentration.</div>
    </div>
    <div class="card-body">
        <div class="row g-4">
            <div class="col-xl-4">
                <div class="thintel-chart short">
                    <canvas id="threatSeverityChart"></canvas>
                </div>
            </div>
            <div class="col-xl-5">
                <div class="thintel-chart">
                    <canvas id="threatPublicationChart"></canvas>
                </div>
            </div>
            <div class="col-xl-3">
                <div class="thintel-chart short">
                    <canvas id="threatVendorChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card thintel-card">
    <div class="card-header bg-transparent border-0 pb-0">
        <div class="d-flex flex-wrap justify-content-between align-items-start gap-3">
            <div>
                <h3 class="thintel-panel-title">Intel Explorer</h3>
                <div class="thintel-panel-copy">Switch between the current CVE feed, KEV queue, and source notes without adding more vertical clutter.</div>
            </div>
            <ul class="nav thintel-tab-nav" id="threatIntelTab" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="thintel-feed-tab" data-bs-toggle="tab" data-bs-target="#thintel-feed-pane" type="button" role="tab" aria-controls="thintel-feed-pane" aria-selected="true">Current CVE Feed</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="thintel-kev-tab" data-bs-toggle="tab" data-bs-target="#thintel-kev-pane" type="button" role="tab" aria-controls="thintel-kev-pane" aria-selected="false">Known Exploited Queue</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="thintel-source-tab" data-bs-toggle="tab" data-bs-target="#thintel-source-pane" type="button" role="tab" aria-controls="thintel-source-pane" aria-selected="false">Data Sources</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="thintel-priority-tab" data-bs-toggle="tab" data-bs-target="#thintel-priority-pane" type="button" role="tab" aria-controls="thintel-priority-pane" aria-selected="false">Priority Queue</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="thintel-analyst-tab" data-bs-toggle="tab" data-bs-target="#thintel-analyst-pane" type="button" role="tab" aria-controls="thintel-analyst-pane" aria-selected="false">Analyst Console</button>
                </li>
            </ul>
        </div>
    </div>
    <div class="card-body">
        <div class="tab-content" id="threatIntelTabContent">
            <div class="tab-pane fade show active" id="thintel-feed-pane" role="tabpanel" aria-labelledby="thintel-feed-tab">
                @if (empty($cves))
                    <div class="thintel-empty">No CVEs match the current filters, or the NVD feed is unavailable right now.</div>
                @else
                    <div class="thintel-scroll">
                        @foreach ($cves as $item)
                            <div class="thintel-feed-item">
                                <div class="d-flex flex-wrap justify-content-between gap-3 mb-2">
                                    <div>
                                        <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
                                            <a href="{{ $item['detail_url'] }}" target="_blank" rel="noopener" class="fw-bold text-decoration-none">{{ $item['cve_id'] }}</a>
                                            <span class="thintel-severity thintel-severity-{{ $item['severity'] }}">{{ $item['severity'] }}</span>
                                            @if (($item['is_known_exploited'] ?? false) === true)
                                                <span class="thintel-pill alert">KEV overlap</span>
                                            @endif
                                            @if (!empty($item['owasp_codes']))
                                                <span class="thintel-pill warn" title="{{ implode(', ', (array) ($item['owasp_top10'] ?? [])) }}">
                                                    OWASP {{ implode(', ', (array) $item['owasp_codes']) }}
                                                </span>
                                            @endif
                                        </div>
                                        <div class="small text-muted">Published {{ $item['published_at'] }} | Updated {{ $item['modified_at'] }} | Status {{ $item['vuln_status'] }}</div>
                                    </div>
                                    <div class="text-end">
                                        <div class="fw-bold text-dark">CVSS {{ $item['base_score'] ?? '-' }}</div>
                                    </div>
                                </div>
                                <div class="text-muted mb-2">{{ \Illuminate\Support\Str::limit($item['description'], 220) }}</div>
                                <div class="d-flex flex-wrap gap-2">
                                    @foreach (($item['cwes'] ?? []) as $cwe)
                                        <span class="thintel-pill">{{ $cwe }}</span>
                                    @endforeach
                                    @foreach (($item['owasp_top10'] ?? []) as $owaspLabel)
                                        <span class="thintel-pill warn">{{ $owaspLabel }}</span>
                                    @endforeach
                                    @if (!is_null($item['reference_url']))
                                        <a class="thintel-pill text-decoration-none" href="{{ $item['reference_url'] }}" target="_blank" rel="noopener">Reference</a>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            <div class="tab-pane fade" id="thintel-kev-pane" role="tabpanel" aria-labelledby="thintel-kev-tab">
                @if (empty($kevItems))
                    <div class="thintel-empty">No KEV entries match the current filter set, or the CISA feed is unavailable.</div>
                @else
                    <div class="thintel-scroll" style="max-height:540px;">
                        @foreach ($kevItems as $item)
                            <div class="thintel-feed-item">
                                <div class="d-flex justify-content-between gap-3 mb-2">
                                    <div>
                                        <a href="{{ $item['detail_url'] }}" target="_blank" rel="noopener" class="fw-bold text-decoration-none">{{ $item['cve_id'] }}</a>
                                        <div class="small text-muted mt-1">{{ $item['vendor_project'] }} | {{ $item['product'] }}</div>
                                    </div>
                                    <span class="thintel-pill {{ strtoupper($item['known_ransomware_campaign_use']) === 'KNOWN' ? 'alert' : 'dark' }}">{{ $item['known_ransomware_campaign_use'] }}</span>
                                </div>
                                <div class="fw-semibold mb-2">{{ \Illuminate\Support\Str::limit($item['vulnerability_name'], 90) }}</div>
                                <div class="small text-muted">Added {{ $item['date_added'] }} | Due {{ $item['due_date'] }}</div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            <div class="tab-pane fade" id="thintel-source-pane" role="tabpanel" aria-labelledby="thintel-source-tab">
                <div class="thintel-source-item">
                    <div class="fw-semibold mb-1">NVD CVE API</div>
                    <div class="small text-muted mb-2">Primary source for CVE records, CVSS scores, CWE tags, and publication metadata.</div>
                    <a href="https://nvd.nist.gov/developers/vulnerabilities" target="_blank" rel="noopener" class="small">nvd.nist.gov/developers/vulnerabilities</a>
                </div>
                <div class="thintel-source-item">
                    <div class="fw-semibold mb-1">CISA KEV Catalog</div>
                    <div class="small text-muted mb-2">Canonical source for CVEs known to be exploited in the wild.</div>
                    <a href="https://www.cisa.gov/known-exploited-vulnerabilities-catalog" target="_blank" rel="noopener" class="small">cisa.gov/known-exploited-vulnerabilities-catalog</a>
                </div>
                <div class="thintel-source-item">
                    <div class="fw-semibold mb-1">CISA KEV JSON Mirror</div>
                    <div class="small text-muted mb-2">Operational fallback when the primary CISA JSON feed is unavailable.</div>
                    <a href="https://raw.githubusercontent.com/cisagov/kev-data/develop/known_exploited_vulnerabilities.json" target="_blank" rel="noopener" class="small">github.com/cisagov/kev-data</a>
                </div>
                <div class="thintel-source-item">
                    <div class="fw-semibold mb-1">OWASP Top 10 (2021)</div>
                    <div class="small text-muted mb-2">Reference taxonomy used to enrich CVE entries from CWE tags into OWASP categories.</div>
                    <a href="{{ config('services.owasp.top10_url', 'https://owasp.org/Top10/') }}" target="_blank" rel="noopener" class="small">{{ config('services.owasp.top10_url', 'https://owasp.org/Top10/') }}</a>
                </div>
            </div>

            <div class="tab-pane fade" id="thintel-priority-pane" role="tabpanel" aria-labelledby="thintel-priority-tab">
                @if (empty($priorityQueue))
                    <div class="thintel-empty">No current priorities in the selected working set. Adjust the filters to widen the search.</div>
                @else
                    <div class="thintel-scroll" style="max-height:540px;">
                        @foreach ($priorityQueue as $item)
                            <div class="thintel-priority-item">
                                <div class="d-flex flex-wrap justify-content-between gap-3">
                                    <div>
                                        <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
                                            <a href="{{ $item['detail_url'] }}" target="_blank" rel="noopener" class="fw-bold text-decoration-none">{{ $item['cve_id'] }}</a>
                                            <span class="thintel-severity thintel-severity-{{ $item['severity'] }}">{{ $item['severity'] }}</span>
                                            <span class="thintel-pill {{ ($item['priority_label'] ?? 'Monitor') === 'Immediate' ? 'alert' : (($item['priority_label'] ?? 'Monitor') === 'Urgent' ? 'warn' : 'dark') }}">{{ $item['priority_label'] }}</span>
                                            @if (($item['is_known_exploited'] ?? false) === true)
                                                <span class="thintel-pill alert">Known exploited</span>
                                            @endif
                                        </div>
                                        <div class="text-muted mb-2">{{ \Illuminate\Support\Str::limit($item['description'], 220) }}</div>
                                        <div class="d-flex flex-wrap gap-2">
                                            @foreach (($item['priority_reasons'] ?? []) as $reason)
                                                <span class="thintel-pill">{{ $reason }}</span>
                                            @endforeach
                                            @if (!empty($item['kev_summary']['due_date']))
                                                <span class="thintel-pill warn">KEV due {{ $item['kev_summary']['due_date'] }}</span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="text-xl-end">
                                        <div class="fw-bold text-dark fs-3">{{ $item['base_score'] ?? '-' }}</div>
                                        <div class="small text-muted mb-2">CVSS base score</div>
                                        <a href="{{ $item['detail_url'] }}" target="_blank" rel="noopener" class="btn btn-primary btn-sm">Open NVD</a>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            <div class="tab-pane fade" id="thintel-analyst-pane" role="tabpanel" aria-labelledby="thintel-analyst-tab">
                <div class="thintel-source-item">
                    <div class="fw-semibold mb-3">Source Health</div>
                    @foreach (['nvd', 'kev', 'owasp'] as $sourceKey)
                        <div class="{{ $loop->first ? '' : 'mt-3' }}">
                            <div class="d-flex justify-content-between align-items-start gap-2 mb-2">
                                <div class="fw-semibold">{{ $sources[$sourceKey]['label'] ?? strtoupper($sourceKey) }}</div>
                                <span class="thintel-pill {{ ($sources[$sourceKey]['status'] ?? 'error') === 'ok' ? 'good' : 'alert' }}">{{ ($sources[$sourceKey]['status'] ?? 'error') === 'ok' ? 'Ready' : 'Degraded' }}</span>
                            </div>
                            <div class="small text-muted mb-2">{{ $sources[$sourceKey]['note'] ?? '-' }}</div>
                            <a href="{{ $sources[$sourceKey]['url'] ?? '#' }}" target="_blank" rel="noopener" class="small">{{ $sources[$sourceKey]['url'] ?? '-' }}</a>
                        </div>
                    @endforeach
                </div>
                <div class="thintel-source-item">
                    <div class="fw-semibold mb-2">How to Read</div>
                    <div class="small text-muted mb-2">KEV Matches are the overlap between the visible NVD CVEs and the CISA Known Exploited Vulnerabilities catalog.</div>
                    <div class="small text-muted mb-2">High + Critical tells you how much review-now workload exists in the current filter set.</div>
                    <div class="small text-muted">{{ $sourceHealthy ? 'Feed health is currently normal.' : 'One or more feeds are degraded, so visibility may be partial.' }}</div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
<script>
    (function () {
        if (typeof Chart === 'undefined') {
            return;
        }

        const severityLabels = @json($severityBreakdown['labels'] ?? []);
        const severityValues = @json($severityBreakdown['values'] ?? []);
        const publicationLabels = @json($publicationTrend['labels'] ?? []);
        const publicationValues = @json($publicationTrend['values'] ?? []);
        const vendorLabels = @json($vendorExposure['labels'] ?? []);
        const vendorValues = @json($vendorExposure['values'] ?? []);

        const tooltip = {
            backgroundColor: '#ffffff',
            titleColor: '#0f172a',
            bodyColor: '#334155',
            borderColor: '#dbe4ef',
            borderWidth: 1,
        };

        const severityEl = document.getElementById('threatSeverityChart');
        if (severityEl) {
            new Chart(severityEl, {
                type: 'doughnut',
                data: {
                    labels: severityLabels,
                    datasets: [{
                        data: severityValues,
                        backgroundColor: ['#7f1d1d', '#dc2626', '#f59e0b', '#16a34a', '#94a3b8'],
                        borderWidth: 0,
                        hoverOffset: 8,
                    }],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '66%',
                    plugins: {
                        legend: { position: 'bottom', labels: { usePointStyle: true, boxWidth: 10, color: '#475569' } },
                        tooltip: tooltip,
                    },
                },
            });
        }

        const publicationEl = document.getElementById('threatPublicationChart');
        if (publicationEl) {
            new Chart(publicationEl, {
                type: 'bar',
                data: {
                    labels: publicationLabels,
                    datasets: [{
                        label: 'Published CVEs',
                        data: publicationValues,
                        backgroundColor: '#0f4c81',
                        borderRadius: 10,
                        maxBarThickness: 34,
                    }],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false }, tooltip: tooltip },
                    scales: {
                        x: { grid: { display: false }, ticks: { color: '#64748b' } },
                        y: { beginAtZero: true, ticks: { precision: 0, color: '#64748b' }, grid: { color: 'rgba(148, 163, 184, 0.18)' } },
                    },
                },
            });
        }

        const vendorEl = document.getElementById('threatVendorChart');
        if (vendorEl) {
            new Chart(vendorEl, {
                type: 'bar',
                data: {
                    labels: vendorLabels,
                    datasets: [{
                        label: 'KEV count',
                        data: vendorValues,
                        backgroundColor: ['#1d4ed8', '#2563eb', '#0ea5e9', '#14b8a6', '#22c55e', '#84cc16'],
                        borderRadius: 10,
                        maxBarThickness: 28,
                    }],
                },
                options: {
                    indexAxis: 'y',
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false }, tooltip: tooltip },
                    scales: {
                        x: { beginAtZero: true, ticks: { precision: 0, color: '#64748b' }, grid: { color: 'rgba(148, 163, 184, 0.18)' } },
                        y: { ticks: { color: '#475569' }, grid: { display: false } },
                    },
                },
            });
        }
    }());
</script>
@endpush
