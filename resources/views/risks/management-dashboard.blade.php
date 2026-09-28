@extends('layouts.tabler')

@section('title', 'Dashboard Monitoring')

@section('css')
<style>
    .management-dashboard {
        --ink: #12243a;
        --muted: #64748b;
        --line: #dbe5ef;
        --surface: #ffffff;
        --navy: #12385b;
        --blue: #1877b9;
        --teal: #168c83;
        --amber: #d39127;
        --red: #c84b4b;
        color: var(--ink);
    }

    .management-hero {
        position: relative;
        overflow: hidden;
        border-radius: 1rem;
        padding: 1.4rem 1.5rem;
        color: #fff;
        background: linear-gradient(115deg, #102f4f 0%, #155b82 56%, #168c83 100%);
        box-shadow: 0 12px 28px rgba(18, 56, 91, 0.18);
    }

    .management-hero::after {
        content: "";
        position: absolute;
        width: 260px;
        height: 260px;
        right: -70px;
        top: -120px;
        border: 1px solid rgba(255, 255, 255, .18);
        border-radius: 50%;
        box-shadow: 0 0 0 22px rgba(255, 255, 255, .04), 0 0 0 44px rgba(255, 255, 255, .03);
    }

    .management-eyebrow {
        text-transform: uppercase;
        letter-spacing: .13em;
        font-size: .68rem;
        font-weight: 700;
        opacity: .72;
    }

    .management-hero h1 {
        margin: .25rem 0 .45rem;
        font-size: clamp(1.45rem, 2.7vw, 2.2rem);
        font-weight: 700;
        letter-spacing: -.025em;
    }

    .management-hero p { max-width: 650px; margin: 0; color: rgba(255, 255, 255, .82); }
    .management-hero-meta { position: relative; z-index: 1; min-width: 205px; }
    .management-hero-score { position: relative; z-index: 1; text-align: right; }
    .management-hero-score .score { font-size: 2.9rem; line-height: 1; font-weight: 800; letter-spacing: -.05em; }
    .management-hero-score .label { margin-top: .35rem; font-size: .76rem; color: rgba(255, 255, 255, .75); }

    .management-kpi {
        height: 100%;
        border: 1px solid var(--line);
        border-radius: .8rem;
        background: var(--surface);
        padding: .95rem 1rem;
        box-shadow: 0 5px 16px rgba(18, 36, 58, .045);
    }
    .management-kpi .kpi-label { color: var(--muted); font-size: .73rem; font-weight: 700; text-transform: uppercase; letter-spacing: .055em; }
    .management-kpi .kpi-value { margin-top: .22rem; font-size: 1.8rem; line-height: 1.1; font-weight: 800; color: var(--ink); }
    .management-kpi .kpi-note { margin-top: .35rem; color: var(--muted); font-size: .74rem; }
    .management-kpi.accent-blue { border-top: 3px solid var(--blue); }
    .management-kpi.accent-red { border-top: 3px solid var(--red); }
    .management-kpi.accent-amber { border-top: 3px solid var(--amber); }
    .management-kpi.accent-teal { border-top: 3px solid var(--teal); }

    .management-card {
        height: 100%;
        border: 1px solid var(--line);
        border-radius: .85rem;
        background: var(--surface);
        box-shadow: 0 5px 16px rgba(18, 36, 58, .045);
    }
    .management-card-header { padding: 1rem 1.05rem .7rem; border-bottom: 1px solid #edf2f7; }
    .management-card-header h2 { margin: 0; font-size: .94rem; font-weight: 750; }
    .management-card-header p { margin: .2rem 0 0; color: var(--muted); font-size: .75rem; }
    .management-card-body { padding: 1rem 1.05rem; }

    .posture-meter { display: flex; align-items: center; gap: 1rem; }
    .posture-ring { width: 108px; height: 108px; flex: 0 0 108px; border-radius: 50%; display: grid; place-items: center; background: conic-gradient(var(--teal) 0 42%, #e8eef4 42% 100%); }
    .posture-ring-inner { width: 78px; height: 78px; border-radius: 50%; display: grid; place-items: center; background: #fff; text-align: center; }
    .posture-ring-inner strong { display: block; font-size: 1.45rem; line-height: 1; }
    .posture-ring-inner span { color: var(--muted); font-size: .62rem; text-transform: uppercase; }
    .posture-copy strong { display: block; font-size: 1.05rem; }
    .posture-copy span { display: block; margin-top: .32rem; color: var(--muted); font-size: .76rem; line-height: 1.45; }

    .priority-row { margin-bottom: .75rem; }
    .priority-row:last-child { margin-bottom: 0; }
    .priority-head { display: flex; justify-content: space-between; gap: .75rem; margin-bottom: .28rem; font-size: .75rem; }
    .priority-head .name { font-weight: 650; }
    .priority-head .number { color: var(--muted); font-weight: 700; }
    .priority-track { height: 8px; overflow: hidden; border-radius: 999px; background: #edf2f7; }
    .priority-fill { height: 100%; border-radius: inherit; }
    .priority-fill.low { background: #56a86b; }
    .priority-fill.moderate { background: #e0ae42; }
    .priority-fill.high { background: #e47d34; }
    .priority-fill.extreme { background: #c84b4b; }

    .attention-list { display: grid; gap: .6rem; }
    .attention-item { display: flex; align-items: flex-start; gap: .65rem; padding: .62rem .65rem; border: 1px solid #e7edf3; border-radius: .6rem; background: #fbfdff; }
    .attention-dot { width: 8px; height: 8px; flex: 0 0 8px; margin-top: .35rem; border-radius: 50%; background: var(--red); }
    .attention-item.high .attention-dot { background: var(--amber); }
    .attention-item .title { font-size: .78rem; font-weight: 700; line-height: 1.3; }
    .attention-item .meta { margin-top: .18rem; color: var(--muted); font-size: .7rem; }
    .attention-empty { padding: 1rem; text-align: center; color: var(--muted); font-size: .78rem; background: #f8fbfd; border-radius: .55rem; }

    .trend-table { width: 100%; }
    .trend-row { display: grid; grid-template-columns: 70px 1fr 42px; align-items: center; gap: .6rem; margin-bottom: .7rem; }
    .trend-row:last-child { margin-bottom: 0; }
    .trend-label, .trend-value { color: var(--muted); font-size: .72rem; }
    .trend-value { text-align: right; font-weight: 700; color: var(--ink); }
    .trend-track { position: relative; height: 10px; overflow: hidden; border-radius: 999px; background: #edf2f7; }
    .trend-bar { height: 100%; border-radius: inherit; background: linear-gradient(90deg, #4ab8ae, #1877b9); }

    .management-footer-note { color: var(--muted); font-size: .72rem; }
    @media (max-width: 767.98px) {
        .management-hero-score { margin-top: 1.1rem; text-align: left; }
        .management-hero-score .score { font-size: 2.3rem; }
        .posture-meter { align-items: flex-start; }
    }
</style>
@endsection

@section('content')
@php
    $summary = is_array($dashboardSummary ?? null) ? $dashboardSummary : [];
    $portfolio = is_array($summary['portfolio_overview'] ?? null) ? $summary['portfolio_overview'] : [];
    $priorityRows = collect($portfolio['by_priority'] ?? []);
    $topRiskRows = collect($portfolio['top_risks'] ?? [])->filter(function ($row) {
        return in_array(strtolower((string) ($row['priority_bucket'] ?? '')), ['high', 'extreme'], true);
    })->take(4);
    $trendRows = collect($summary['trend'] ?? []);
    $maxTrend = max(1, (float) $trendRows->max('overall'));
    $totalRisks = (int) ($summary['total_risks'] ?? 0);
    $highRiskCount = (int) $priorityRows->whereIn('label', ['High', 'Extreme'])->sum('count');
    $overdueCount = (int) ($summary['sla']['overdue_count'] ?? 0);
    $slaActual = (float) ($summary['sla']['actual_percent'] ?? 0);
    $residualAverage = (float) ($summary['average_scores']['residual'] ?? 0);
    $postureLabel = $highRiskCount > 0 ? 'Perlu perhatian' : 'Terkendali';
    $postureCopy = $highRiskCount > 0
        ? 'Terdapat risiko prioritas tinggi yang perlu dipantau dan diekskalasi oleh pemilik risiko.'
        : 'Belum ada risiko pada prioritas tinggi atau ekstrem dalam cakupan yang terlihat.';
    $posturePercent = $totalRisks > 0 ? min(100, round(($highRiskCount / $totalRisks) * 100)) : 0;
@endphp

<div class="management-dashboard">
    <div class="management-hero mb-3">
        <div class="d-flex flex-wrap justify-content-between align-items-end gap-3">
            <div class="management-hero-meta">
                <div class="management-eyebrow">Management view · Risk governance</div>
                <h1>Dashboard Monitoring</h1>
                <p>Ringkasan posisi risiko perusahaan untuk membantu manajemen menentukan fokus, eskalasi, dan tindak lanjut.</p>
                <div class="small mt-3" style="color: rgba(255,255,255,.68)">
                    Pembaruan terakhir: {{ $summary['generated_at'] ?? '-' }} · Cakupan: {{ $totalRisks }} risiko
                </div>
            </div>
            <div class="management-hero-score">
                <div class="score">{{ number_format($residualAverage, 2) }}</div>
                <div class="label">Rata-rata skor residual</div>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-3">
        <div class="col-sm-6 col-xl-3"><div class="management-kpi accent-blue"><div class="kpi-label">Total risiko</div><div class="kpi-value">{{ number_format($totalRisks) }}</div><div class="kpi-note">Seluruh risiko dalam cakupan akses Anda</div></div></div>
        <div class="col-sm-6 col-xl-3"><div class="management-kpi accent-red"><div class="kpi-label">Prioritas tinggi</div><div class="kpi-value">{{ number_format($highRiskCount) }}</div><div class="kpi-note">High dan Extreme · perlu perhatian</div></div></div>
        <div class="col-sm-6 col-xl-3"><div class="management-kpi accent-amber"><div class="kpi-label">Mitigasi terlambat</div><div class="kpi-value">{{ number_format($overdueCount) }}</div><div class="kpi-note">Tindakan melewati tenggat</div></div></div>
        <div class="col-sm-6 col-xl-3"><div class="management-kpi accent-teal"><div class="kpi-label">Kepatuhan SLA</div><div class="kpi-value">{{ number_format($slaActual, 1) }}%</div><div class="kpi-note">Target operasional: {{ number_format((float) ($summary['sla']['target_percent'] ?? 95), 0) }}%</div></div></div>
    </div>

    <div class="row g-3 mb-3">
        <div class="col-lg-5">
            <div class="management-card">
                <div class="management-card-header"><h2>Postur risiko saat ini</h2><p>Sinyal utama berdasarkan skor residual</p></div>
                <div class="management-card-body">
                    <div class="posture-meter">
                        <div class="posture-ring" style="background: conic-gradient({{ $highRiskCount > 0 ? '#d39127' : '#168c83' }} 0 {{ $posturePercent }}%, #e8eef4 {{ $posturePercent }}% 100%);"><div class="posture-ring-inner"><div><strong>{{ $posturePercent }}%</strong><span>prioritas</span></div></div></div>
                        <div class="posture-copy"><strong>{{ $postureLabel }}</strong><span>{{ $postureCopy }}</span></div>
                    </div>
                    <div class="management-footer-note mt-3">Interpretasi ini adalah ringkasan manajemen; detail scoring dan basis data tersedia pada Risk Monitoring.</div>
                </div>
            </div>
        </div>
        <div class="col-lg-7">
            <div class="management-card">
                <div class="management-card-header"><h2>Distribusi prioritas</h2><p>Komposisi risiko berdasarkan skor yang ditampilkan</p></div>
                <div class="management-card-body">
                    @foreach($priorityRows as $row)
                        @php
                            $label = (string) ($row['label'] ?? '-');
                            $bucket = strtolower($label);
                            $count = (int) ($row['count'] ?? 0);
                            $width = $totalRisks > 0 ? min(100, round(($count / $totalRisks) * 100, 1)) : 0;
                        @endphp
                        <div class="priority-row"><div class="priority-head"><span class="name">{{ $label }}</span><span class="number">{{ number_format($count) }} <small>({{ number_format($width, 1) }}%)</small></span></div><div class="priority-track"><div class="priority-fill {{ $bucket }}" style="width: {{ $width }}%"></div></div></div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-3">
        <div class="col-lg-7">
            <div class="management-card">
                <div class="management-card-header d-flex justify-content-between align-items-start gap-2"><div><h2>Area perhatian manajemen</h2><p>Risiko dengan skor residual tertinggi</p></div><a href="{{ route('risks.monitoring') }}" class="btn btn-outline-primary btn-sm">Lihat detail</a></div>
                <div class="management-card-body"><div class="attention-list">
                    @forelse($topRiskRows as $row)
                        @php $priority = strtolower((string) ($row['priority_bucket'] ?? 'high')); @endphp
                        <div class="attention-item {{ $priority === 'high' ? 'high' : '' }}"><span class="attention-dot"></span><div><div class="title">{{ $row['risk_id'] ?? '-' }} · {{ $row['risk_title'] ?? '-' }}</div><div class="meta">{{ $row['division'] ?? '-' }} · {{ $row['priority_label'] ?? '-' }} · Skor {{ number_format((float) ($row['risk_value'] ?? 0), 2) }}</div></div></div>
                    @empty
                        <div class="attention-empty">Tidak ada risiko prioritas tinggi atau ekstrem pada cakupan saat ini.</div>
                    @endforelse
                </div></div>
            </div>
        </div>
        <div class="col-lg-5">
            <div class="management-card">
                <div class="management-card-header"><h2>Tren skor residual</h2><p>{{ ucfirst((string) ($summary['trend_granularity'] ?? 'quarterly')) }} · enam periode terakhir</p></div>
                <div class="management-card-body"><div class="trend-table">
                    @forelse($trendRows as $row)
                        @php $value = (float) ($row['overall'] ?? 0); $width = min(100, round(($value / $maxTrend) * 100, 1)); @endphp
                        <div class="trend-row"><span class="trend-label">{{ $row['label'] ?? '-' }}</span><div class="trend-track"><div class="trend-bar" style="width: {{ $width }}%"></div></div><span class="trend-value">{{ number_format($value, 1) }}</span></div>
                    @empty
                        <div class="attention-empty">Data tren belum tersedia.</div>
                    @endforelse
                </div></div>
            </div>
        </div>
    </div>

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 px-1 mb-2">
        <span class="management-footer-note">Gunakan dashboard ini untuk orientasi keputusan; investigasi dan tindakan tetap dilakukan pada modul terkait.</span>
        <a href="{{ route('risks.monitoring') }}" class="btn btn-primary btn-sm">Buka Risk Monitoring</a>
    </div>
</div>
@endsection
