{{--
    "Analisis" tab. Server-rendered from RiskMetricService's dashboard summary
    and DashboardMetricsService::levelMigration(). Everything is drawn with
    plain HTML/CSS marks; tooltips come from risks.partials.dashboard-tooltip.
--}}
@php
    $summary = is_array($dashboardSummary ?? null) ? $dashboardSummary : [];
    $varianceRows = collect($summary['variance']['rows'] ?? [])
        ->sortByDesc(fn ($row) => (float) ($row['avg_inherent'] ?? 0) - (float) ($row['avg_residual'] ?? 0))
        ->values();
    $rootCause = is_array($summary['root_cause_analysis'] ?? null) ? $summary['root_cause_analysis'] : [];
    $rootCategories = collect($rootCause['top_categories'] ?? [])->take(8);
    $rootKeywords = collect($rootCause['top_keywords'] ?? [])->take(8);
    $rootPatterns = collect($rootCause['patterns'] ?? [])->take(3);
    $benchmark = is_array($summary['benchmark_scoring'] ?? null) ? $summary['benchmark_scoring'] : [];
    $migration = $levelMigration ?? ['levels' => [], 'matrix' => [], 'total' => 0, 'down' => 0, 'same' => 0, 'up' => 0];

    $levelLabels = [
        'High' => 'Tinggi',
        'Moderate to High' => 'Menengah–tinggi',
        'Moderate' => 'Menengah',
        'Low to Moderate' => 'Rendah–menengah',
        'Low' => 'Rendah',
    ];
    $scoreMax = 25;
    $pct = fn ($value) => max(0, min(100, (float) $value / $scoreMax * 100));
    $fmt = fn ($value, $decimals = 1) => number_format((float) $value, $decimals, ',', '.');
    $tip = fn (string $html) => 'data-tip="' . e($html) . '"';

    $internal = (float) ($benchmark['internal_average_score'] ?? 0);
    $target = (float) ($benchmark['target_score'] ?? 9);
    $baseline = (float) ($benchmark['industry_baseline_score'] ?? 12);
    $benchMax = max(20, ceil(max($internal, $baseline) * 1.25 / 5) * 5);
    $benchPct = fn ($value) => max(0, min(100, (float) $value / $benchMax * 100));
    [$positionLabel, $positionClass] = match ($benchmark['position'] ?? null) {
        'ahead_of_target' => ['Dalam target', 'is-ok'],
        'better_than_industry' => ['Di atas target, di bawah baseline', 'is-warning'],
        'above_industry_risk' => ['Di atas baseline: eskalasi', 'is-critical'],
        default => ['Belum dinilai', 'is-muted'],
    };

    $migrationMax = max(1, collect($migration['matrix'])->flatten()->max() ?? 1);
    $categoryMax = max(1, (int) $rootCategories->max('count'));
    $keywordMax = max(1, (int) $rootKeywords->max('frequency'));
    $info = fn (string $text) => '<button type="button" class="grc-info" data-tip="' . e($text) . '" aria-label="' . e($text) . '">i</button>';
@endphp

<div class="d-flex flex-wrap justify-content-end gap-2 mb-3">
    <a href="{{ route('risks.heatmap') }}" class="btn btn-outline-primary btn-sm">Halaman heatmap</a>
    <a href="{{ route('risks.recap') }}" class="btn btn-outline-secondary btn-sm">Rekap tahunan</a>
</div>

<div class="row row-cards mb-3">
    {{-- Control effectiveness per category --}}
    <div class="col-lg-7">
        <div class="card grc-card">
            <div class="card-header">
                <h3 class="card-title">Efektivitas kontrol per kategori</h3>
                {!! $info('Rata-rata skor inheren dan residual per kategori. Angka kanan = penurunan skor berkat kontrol. Diurutkan dari efek terbesar.') !!}
                @if(isset($summary['variance']['global']))
                    <span class="grc-pill is-muted ms-auto" {!! $tip('Rata-rata penurunan skor inheren → residual di seluruh portofolio') !!}>Rata-rata −{{ $fmt($summary['variance']['global'], 2) }}</span>
                @endif
            </div>
            <div class="card-body">
                @forelse($varianceRows as $row)
                    @php
                        $inherent = (float) ($row['avg_inherent'] ?? 0);
                        $residual = (float) ($row['avg_residual'] ?? 0);
                        $reduction = $inherent - $residual;
                    @endphp
                    <div class="grc-db-row">
                        <div class="lbl" title="{{ $row['category'] }}">{{ $row['category'] }}</div>
                        <div class="grc-db-track" {!! $tip(e($row['category']) . '<br>' . (int) $row['risk_count'] . ' risk<br>Inheren ' . $fmt($inherent, 2) . ' → residual ' . $fmt($residual, 2)) !!}
                             role="img" aria-label="{{ $row['category'] }}: inheren {{ $fmt($inherent, 2) }}, residual {{ $fmt($residual, 2) }}">
                            <span class="grc-db-line" style="left: {{ min($pct($inherent), $pct($residual)) }}%; width: {{ abs($pct($inherent) - $pct($residual)) }}%"></span>
                            <span class="grc-db-dot inherent" style="left: {{ $pct($inherent) }}%"></span>
                            <span class="grc-db-dot" style="left: {{ $pct($residual) }}%; background: var(--grc-accent)"></span>
                        </div>
                        <span class="grc-db-delta {{ $reduction < 0 ? 'is-critical' : '' }}">{{ $reduction >= 0 ? '−' : '+' }}{{ $fmt(abs($reduction)) }}</span>
                    </div>
                @empty
                    <div class="grc-state"><strong>Belum ada data efektivitas kontrol.</strong>Terisi setelah risk punya analisis inheren dan residual.</div>
                @endforelse
                @if($varianceRows->isNotEmpty())
                    <div class="grc-db-axis"><span></span><div class="ticks">
                        @foreach([0, 5, 10, 15, 20, 25] as $t)<span style="left: {{ $pct($t) }}%">{{ $t }}</span>@endforeach
                    </div><span></span></div>
                    <div class="grc-heat-legend">
                        <span class="ring">Rata-rata inheren</span>
                        <span class="dot" style="--swatch: var(--grc-accent)">Rata-rata residual</span>
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Benchmark --}}
    <div class="col-lg-5">
        <div class="card grc-card">
            <div class="card-header">
                <h3 class="card-title">Posisi terhadap benchmark</h3>
                {!! $info('Rata-rata skor residual seluruh risk aktif dibanding target dan baseline. Acuan: ' . e($benchmark['source_name'] ?? '-') . '.') !!}
            </div>
            <div class="card-body">
                <div class="d-flex flex-wrap align-items-baseline gap-2 mb-3">
                    <span class="grc-big">{{ $fmt($internal) }}</span>
                    <span class="grc-pill {{ $positionClass }}">{{ $positionLabel }}</span>
                </div>
                <div class="grc-bullet lg" role="img" aria-label="Rata-rata residual {{ $fmt($internal) }}, target {{ $fmt($target) }}, baseline {{ $fmt($baseline) }}">
                    <span class="band band-ok" style="left: 0; width: {{ $benchPct($target) }}%"></span>
                    <span class="band band-warn" style="left: {{ $benchPct($target) }}%; width: {{ $benchPct($baseline) - $benchPct($target) }}%"></span>
                    <span class="band band-crit" style="left: {{ $benchPct($baseline) }}%; right: 0"></span>
                    <span class="val" style="width: {{ $benchPct($internal) }}%" {!! $tip('Rata-rata residual ' . $fmt($internal, 2) . '<br>Selisih ke target ' . $fmt($internal - $target, 2) . '<br>Selisih ke baseline ' . $fmt($internal - $baseline, 2)) !!}></span>
                </div>
                <div class="grc-bullet-axis">
                    @foreach(collect([0, $target, $baseline, $benchMax])->unique()->sort() as $t)
                        <span style="left: {{ $benchPct($t) }}%">{{ $fmt($t, $t == (int) $t ? 0 : 1) }}</span>
                    @endforeach
                </div>
                <div class="grc-heat-legend">
                    <span style="--swatch: #dff2e3">≤ {{ $fmt($target, 0) }} target</span>
                    <span style="--swatch: #fdecc8">{{ $fmt($target, 0) }}–{{ $fmt($baseline, 0) }} perhatian</span>
                    <span style="--swatch: #f7d6d6">&gt; {{ $fmt($baseline, 0) }} eskalasi</span>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row row-cards mb-3">
    {{-- Level migration --}}
    <div class="col-lg-5">
        <div class="card grc-card">
            <div class="card-header">
                <h3 class="card-title">Perpindahan level, inheren → residual</h3>
                {!! $info('Baris = level inheren, kolom = level residual. Sel bergaris = level tidak berubah; sel bergaris merah = residual lebih tinggi dari inheren, biasanya salah input.') !!}
            </div>
            <div class="card-body">
                @if($migration['total'] > 0)
                    <div class="d-flex flex-wrap align-items-center gap-1 mb-3">
                        <span class="grc-pill is-ok">{{ $migration['down'] }} turun level</span>
                        <span class="grc-pill is-muted">{{ $migration['same'] }} tetap</span>
                        @if($migration['up'] > 0)
                            <span class="grc-pill is-critical">{{ $migration['up'] }} naik, cek data</span>
                        @endif
                        <span class="small text-muted ms-1">dari {{ $migration['total'] }} risk</span>
                    </div>
                    <div class="table-responsive">
                        <div class="grc-mig" role="table" aria-label="Perpindahan level inheren ke residual">
                            <span class="hd rh">Inheren ↓ / Residual →</span>
                            @foreach($migration['levels'] as $level)
                                <span class="hd">{{ $levelLabels[$level] ?? $level }}</span>
                            @endforeach
                            @foreach($migration['levels'] as $r => $inherentLevel)
                                <span class="hd rh">{{ $levelLabels[$inherentLevel] ?? $inherentLevel }}</span>
                                @foreach($migration['levels'] as $c => $residualLevel)
                                    @php
                                        $count = (int) ($migration['matrix'][$inherentLevel][$residualLevel] ?? 0);
                                        $mix = $count ? (int) round(12 + $count / $migrationMax * 78) : 0;
                                        $class = $c === $r ? 'diag' : ($c < $r && $count ? 'worse' : '');
                                    @endphp
                                    <span class="c {{ $class }}"
                                          style="background: {{ $count ? 'color-mix(in srgb, var(--grc-accent) ' . $mix . '%, #fff)' : 'var(--grc-soft)' }}; color: {{ $mix > 50 ? '#fff' : ($count ? 'var(--grc-ink)' : 'var(--grc-faint)') }}"
                                          {!! $tip($count . ' risk: ' . ($levelLabels[$inherentLevel] ?? $inherentLevel) . ' → ' . ($levelLabels[$residualLevel] ?? $residualLevel)) !!}>{{ $count ?: '·' }}</span>
                                @endforeach
                            @endforeach
                        </div>
                    </div>
                @else
                    <div class="grc-state"><strong>Belum ada risk dengan analisis inheren dan residual.</strong></div>
                @endif
            </div>
        </div>
    </div>

    {{-- Dominant causes --}}
    <div class="col-lg-7">
        <div class="card grc-card">
            <div class="card-header">
                <h3 class="card-title">Penyebab dominan</h3>
                {!! $info('Kategori penyebab dan kata kunci yang paling sering muncul di narasi penyebab risk.') !!}
            </div>
            <div class="card-body">
                <div class="row g-4">
                    <div class="col-md-6">
                        <div class="grc-heat-title">Kategori penyebab · jumlah risk</div>
                        <div class="grc-bars">
                            @forelse($rootCategories as $cat)
                                <div class="grc-hbar-row">
                                    <span class="name" title="{{ $cat['category'] }}">{{ $cat['category'] }}</span>
                                    <span class="grc-hbar-cell">
                                        <span class="grc-hbar" style="width: {{ (int) $cat['count'] / $categoryMax * 80 }}%"
                                              {!! $tip(e($cat['category']) . ': ' . (int) $cat['count'] . ' risk (' . $fmt($cat['share_percent'] ?? 0) . '%)<br>Rata-rata residual ' . $fmt($cat['avg_residual_score'] ?? 0)) !!}></span>
                                        <b>{{ $cat['count'] }}</b>
                                    </span>
                                </div>
                            @empty
                                <div class="small text-muted">Belum ada kategori penyebab.</div>
                            @endforelse
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="grc-heat-title">Kata kunci tersering</div>
                        <div class="grc-bars">
                            @forelse($rootKeywords as $kw)
                                <div class="grc-hbar-row">
                                    <span class="name" title="{{ $kw['keyword'] }}">{{ $kw['keyword'] }}</span>
                                    <span class="grc-hbar-cell">
                                        <span class="grc-hbar" style="width: {{ (int) $kw['frequency'] / $keywordMax * 80 }}%"
                                              {!! $tip('“' . e($kw['keyword']) . '” muncul ' . (int) $kw['frequency'] . '× di ' . (int) $kw['risk_count'] . ' risk<br>Rata-rata residual ' . $fmt($kw['average_residual_score'] ?? 0)) !!}></span>
                                        <b>{{ $kw['frequency'] }}</b>
                                    </span>
                                </div>
                            @empty
                                <div class="small text-muted">Belum ada kata kunci penyebab.</div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Systemic recommendations: text is the content here, so it stays text, capped at three. --}}
<div class="card grc-card mb-3">
    <div class="card-header">
        <h3 class="card-title">Rekomendasi sistemik</h3>
        {!! $info('Pola penyebab yang berulang di beberapa risk sekaligus. Tiga teratas.') !!}
    </div>
    <div class="card-body">
        <div class="grc-recs">
            @forelse($rootPatterns as $pat)
                @php $critical = str_contains((string) ($pat['severity_class'] ?? ''), 'danger'); @endphp
                <div class="grc-rec">
                    <span class="sev" style="background: {{ $critical ? 'var(--grc-critical)' : 'var(--grc-faint)' }}"></span>
                    <div>
                        <b>{{ $pat['category'] }} <span class="grc-pill {{ $critical ? 'is-critical' : 'is-muted' }} ms-1">{{ $critical ? 'Dampak tinggi' : 'Standar' }}</span></b>
                        <p>{{ $pat['recommendation'] }}</p>
                    </div>
                </div>
            @empty
                <div class="small text-muted">Belum ada pola penyebab yang teridentifikasi.</div>
            @endforelse
        </div>
    </div>
</div>
