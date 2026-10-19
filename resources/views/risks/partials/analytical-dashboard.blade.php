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
                <div class="d-flex align-items-center gap-2">
                    <span class="avatar avatar-sm bg-success-lt text-success rounded-circle"><i class="fas fa-shield-alt"></i></span>
                    <div>
                        <h3 class="card-title mb-0">Efektivitas Kontrol per Kategori</h3>
                        <div class="text-muted small">Tingkat penurunan risiko dari inheren (awal) ke residual (setelah kontrol)</div>
                    </div>
                </div>
                {!! $info('Menampilkan perbandingan skor risiko inheren (sebelum mitigasi) vs residual (setelah mitigasi) per kategori, beserta persentase efektivitas kontrol mitigasi yang diterapkan.') !!}
                @if(isset($summary['variance']['global']))
                    <span class="badge bg-success-lt text-success fs-5 px-2 py-1 ms-auto" {!! $tip('Rata-rata penurunan skor inheren → residual di seluruh portofolio') !!}>
                        <i class="fas fa-arrow-down me-1"></i> Rata-rata Turun {{ $fmt($summary['variance']['global'], 1) }} Poin
                    </span>
                @endif
            </div>
            <div class="card-body">
                @if($varianceRows->isNotEmpty())
                    @php
                        $totalRisksInCategories = $varianceRows->sum('risk_count');
                        $avgReduction = $varianceRows->avg(fn ($r) => (float)($r['avg_inherent'] ?? 0) - (float)($r['avg_residual'] ?? 0));
                        $avgInherentTotal = $varianceRows->avg(fn ($r) => (float)($r['avg_inherent'] ?? 0));
                        $portfolioEffPct = $avgInherentTotal > 0 ? round(($avgReduction / $avgInherentTotal) * 100, 1) : 0;
                    @endphp
                    <!-- Executive KPI Summary Strip -->
                    <div class="row g-2 mb-3">
                        <div class="col-sm-4">
                            <div class="p-2 rounded bg-light border border-light text-center">
                                <div class="text-muted small">Rata-rata Efektivitas</div>
                                <div class="fs-2 fw-bold text-success">{{ $fmt($portfolioEffPct, 1) }}%</div>
                                <div class="text-muted" style="font-size: 0.72rem;">Penurunan risiko agregat</div>
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="p-2 rounded bg-light border border-light text-center">
                                <div class="text-muted small">Rata-rata Reduksi Skor</div>
                                <div class="fs-2 fw-bold text-primary">↓ {{ $fmt($avgReduction, 1) }} <span class="fs-6 fw-normal text-muted">/ 25</span></div>
                                <div class="text-muted" style="font-size: 0.72rem;">Efek mitigasi kontrol</div>
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="p-2 rounded bg-light border border-light text-center">
                                <div class="text-muted small">Kategori Teranalisis</div>
                                <div class="fs-2 fw-bold text-dark">{{ count($varianceRows) }} <span class="fs-6 fw-normal text-muted">kategori</span></div>
                                <div class="text-muted" style="font-size: 0.72rem;">{{ $totalRisksInCategories }} total risk</div>
                            </div>
                        </div>
                    </div>
                @endif

                @forelse($varianceRows as $index => $row)
                    @php
                        $inherent = (float) ($row['avg_inherent'] ?? 0);
                        $residual = (float) ($row['avg_residual'] ?? 0);
                        $reduction = $inherent - $residual;
                        $effPct = $inherent > 0 ? max(0, min(100, round(($reduction / $inherent) * 100, 1))) : 0;

                        [$statusBadge, $statusText, $statusIcon] = match (true) {
                            $effPct >= 75 => ['bg-success-lt text-success border-success-subtle', 'Sangat Efektif', 'fas fa-shield-alt'],
                            $effPct >= 50 => ['bg-primary-lt text-primary border-primary-subtle', 'Efektif', 'fas fa-check-circle'],
                            $effPct >= 30 => ['bg-warning-lt text-warning border-warning-subtle', 'Cukup Efektif', 'fas fa-exclamation-triangle'],
                            default       => ['bg-danger-lt text-danger border-danger-subtle', 'Perlu Evaluasi', 'fas fa-times-circle'],
                        };

                        $inherentLevel = match (true) {
                            $inherent >= 20 => 'Sangat Tinggi',
                            $inherent >= 15 => 'Tinggi',
                            $inherent >= 10 => 'Menengah',
                            $inherent >= 5  => 'Rendah-Menengah',
                            default         => 'Rendah',
                        };

                        $residualLevel = match (true) {
                            $residual >= 20 => 'Sangat Tinggi',
                            $residual >= 15 => 'Tinggi',
                            $residual >= 10 => 'Menengah',
                            $residual >= 5  => 'Rendah-Menengah',
                            default         => 'Rendah',
                        };
                    @endphp

                    <div class="grc-eff-item p-3 mb-2 rounded-3 border bg-white shadow-none" data-category-item @if($index >= 6) style="display: none;" @endif>
                        <div class="d-flex flex-wrap justify-content-between align-items-center mb-2 gap-2">
                            <div class="d-flex align-items-center gap-2">
                                <span class="avatar avatar-xs bg-blue-lt text-primary rounded"><i class="fas fa-layer-group"></i></span>
                                <span class="fw-bold text-dark fs-3">{{ $row['category'] }}</span>
                                <span class="badge bg-secondary-lt text-muted">{{ $row['risk_count'] }} Risk</span>
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                <span class="badge {{ $statusBadge }} border px-2 py-1 fs-5 fw-bold">
                                    <i class="{{ $statusIcon }} me-1"></i> {{ $fmt($effPct, 1) }}% {{ $statusText }}
                                </span>
                            </div>
                        </div>

                        <!-- Dual Progress Comparison Bars -->
                        <div class="grc-eff-bars bg-light p-2 rounded-2">
                            <!-- Inherent Row -->
                            <div class="d-flex align-items-center gap-2 mb-2">
                                <div class="small text-muted" style="width: 150px; min-width: 150px;">
                                    <span class="d-block fw-semibold text-secondary">Inheren (Sebelum)</span>
                                    <span class="text-muted" style="font-size: 0.72rem;">Level: {{ $inherentLevel }}</span>
                                </div>
                                <div class="flex-grow-1">
                                    <div class="progress" style="height: 10px; background-color: #e2e8f0; border-radius: 9999px;">
                                        <div class="progress-bar bg-secondary" style="width: {{ $pct($inherent) }}%; opacity: 0.7; border-radius: 9999px;" title="Skor Inheren: {{ $fmt($inherent, 2) }}"></div>
                                    </div>
                                </div>
                                <div class="text-end fw-bold text-secondary" style="width: 65px; min-width: 65px;">
                                    {{ $fmt($inherent, 1) }} <span class="small fw-normal text-muted">/25</span>
                                </div>
                            </div>

                            <!-- Residual Row -->
                            <div class="d-flex align-items-center gap-2">
                                <div class="small text-muted" style="width: 150px; min-width: 150px;">
                                    <span class="d-block fw-semibold text-primary">Residual (Setelah)</span>
                                    <span class="text-muted" style="font-size: 0.72rem;">Level: {{ $residualLevel }}</span>
                                </div>
                                <div class="flex-grow-1">
                                    <div class="progress" style="height: 10px; background-color: #e2e8f0; border-radius: 9999px;">
                                        <div class="progress-bar bg-primary" style="width: {{ $pct($residual) }}%; border-radius: 9999px;" title="Skor Residual: {{ $fmt($residual, 2) }}"></div>
                                    </div>
                                </div>
                                <div class="text-end fw-bold text-primary" style="width: 65px; min-width: 65px;">
                                    {{ $fmt($residual, 1) }} <span class="small fw-normal text-muted">/25</span>
                                </div>
                            </div>
                        </div>

                        <!-- Reduction Summary Footer -->
                        <div class="d-flex justify-content-between align-items-center mt-2 pt-2 border-top border-light text-muted small">
                            <span>
                                <i class="fas fa-arrow-circle-down text-success me-1"></i>
                                Kontrol berhasil menurunkan risiko sebesar <strong class="text-success">{{ $fmt($reduction, 1) }} poin</strong>
                            </span>
                            <span class="badge bg-success-lt text-success fw-bold">
                                <i class="fas fa-shield-alt me-1"></i> Efektivitas {{ $fmt($effPct, 1) }}%
                            </span>
                        </div>
                    </div>
                @empty
                    <div class="grc-state"><strong>Belum ada data efektivitas kontrol.</strong>Terisi setelah risk punya analisis inheren dan residual.</div>
                @endforelse

                @if($varianceRows->count() > 6)
                    <button type="button" class="btn btn-sm btn-ghost-primary w-100 mt-2 py-2" id="btnToggleAllCategories" onclick="toggleCategories(this)" data-expanded="false">
                        <i class="fas fa-chevron-down me-1"></i> Tampilkan Semua Kategori ({{ $varianceRows->count() }} Kategori)
                    </button>
                    <script>
                        function toggleCategories(btn) {
                            const items = document.querySelectorAll('[data-category-item]');
                            const isExpanded = btn.getAttribute('data-expanded') === 'true';
                            if (isExpanded) {
                                items.forEach((item, idx) => { if (idx >= 6) item.style.display = 'none'; });
                                btn.innerHTML = '<i class="fas fa-chevron-down me-1"></i> Tampilkan Semua Kategori ({{ $varianceRows->count() }} Kategori)';
                                btn.setAttribute('data-expanded', 'false');
                            } else {
                                items.forEach((item) => { item.style.display = 'block'; });
                                btn.innerHTML = '<i class="fas fa-chevron-up me-1"></i> Tampilkan Lebih Sedikit (Top 6)';
                                btn.setAttribute('data-expanded', 'true');
                            }
                        }
                    </script>
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
