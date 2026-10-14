{{--
    Main GRC dashboard ("Ringkasan"). Each [data-section] loads its own data
    from route('dashboard.sections') and renders a loading, empty or error state.
    Reading order follows exception-based reporting: status -> what needs
    attention -> where risk sits -> detail.
--}}
@php
    $skeletonLines = fn (int $n) => str_repeat('<span class="placeholder col-12 mb-2"></span>', $n);
@endphp

{{-- A: status tiles --}}
<div class="row row-cards mb-3" data-section="summary" aria-live="polite">
    @foreach(['Di luar appetite', 'KRI merah / kuning', 'Aksi terlambat', 'Loss event', 'Insiden terbuka'] as $label)
        <div class="col-12 col-sm-6 col-lg">
            <div class="grc-tile placeholder-glow" aria-hidden="true">
                <span class="grc-tile-label">{{ $label }}</span>
                <span class="placeholder col-5" style="height: 1.6rem;"></span>
                <span class="placeholder col-9"></span>
                <span class="placeholder col-7"></span>
            </div>
        </div>
    @endforeach
</div>

<div class="row row-cards mb-3">
    {{-- B: needs attention --}}
    <div class="col-lg-7">
        <div class="card grc-card" data-section="attention">
            <div class="card-header">
                <div>
                    <h3 class="card-title">Perlu perhatian</h3>
                    <div class="grc-card-sub">Pengecualian yang butuh tindak lanjut, paling kritis dan paling lama di atas.</div>
                </div>
                <div class="grc-chips ms-auto" data-attention-filters hidden></div>
            </div>
            <div class="card-body p-0" data-body>
                <div class="p-3 placeholder-glow">{!! $skeletonLines(6) !!}</div>
            </div>
        </div>
    </div>

    {{-- C: heatmaps --}}
    <div class="col-lg-5">
        <div class="card grc-card" data-section="heatmap">
            <div class="card-header">
                <div>
                    <h3 class="card-title">Peta risiko</h3>
                    <div class="grc-card-sub">Inheren vs residual. Klik sel untuk melihat risk di dalamnya.</div>
                </div>
            </div>
            <div class="card-body" data-body>
                <div class="placeholder-glow">{!! $skeletonLines(7) !!}</div>
            </div>
        </div>
    </div>
</div>

<div class="row row-cards mb-3">
    {{-- D: profile by category --}}
    <div class="col-lg-5">
        <div class="card grc-card" data-section="profile">
            <div class="card-header">
                <div>
                    <h3 class="card-title">Profil risiko per kategori</h3>
                    <div class="grc-card-sub">Tingkat risiko residual (5 tingkat).</div>
                </div>
            </div>
            <div class="card-body" data-body>
                <div class="placeholder-glow">{!! $skeletonLines(6) !!}</div>
            </div>
        </div>
    </div>

    {{-- E: top risks --}}
    <div class="col-lg-7">
        <div class="card grc-card" data-section="top-risks">
            <div class="card-header">
                <div>
                    <h3 class="card-title">10 risiko residual tertinggi</h3>
                    <div class="grc-card-sub">Skor inheren → residual, status appetite, progres treatment, dan KRI.</div>
                </div>
                <a href="{{ route('risks.index') }}" class="btn btn-sm btn-ghost-primary ms-auto">Register lengkap</a>
            </div>
            <div class="card-body p-0" data-body>
                <div class="p-3 placeholder-glow">{!! $skeletonLines(7) !!}</div>
            </div>
        </div>
    </div>
</div>

<div class="row row-cards mb-3">
    {{-- F: KRI watchlist --}}
    <div class="col-lg-6">
        <div class="card grc-card" data-section="kris">
            <div class="card-header">
                <div>
                    <h3 class="card-title">KRI yang perlu dipantau</h3>
                    <div class="grc-card-sub">Merah, kuning, atau datanya belum diperbarui sesuai jadwal.</div>
                </div>
                <a href="{{ route('kris.index') }}" class="btn btn-sm btn-ghost-primary ms-auto">Semua KRI</a>
            </div>
            <div class="card-body p-0" data-body>
                <div class="p-3 placeholder-glow">{!! $skeletonLines(6) !!}</div>
            </div>
        </div>
    </div>

    {{-- G: losses --}}
    <div class="col-lg-6">
        <div class="card grc-card" data-section="losses">
            <div class="card-header">
                <div>
                    <h3 class="card-title">Loss event 12 bulan</h3>
                    <div class="grc-card-sub">Nilai kerugian dan jumlah kejadian per bulan.</div>
                </div>
                <a href="{{ route('incidents.index') }}" class="btn btn-sm btn-ghost-primary ms-auto">Register insiden</a>
            </div>
            <div class="card-body" data-body>
                <div class="placeholder-glow">{!! $skeletonLines(6) !!}</div>
            </div>
        </div>
    </div>
</div>

<div class="row row-cards mb-3">
    {{-- H: follow-up progress --}}
    <div class="col-lg-4">
        <div class="card grc-card" data-section="actions">
            <div class="card-header">
                <div>
                    <h3 class="card-title">Progres tindak lanjut</h3>
                    <div class="grc-card-sub">Treatment per unit dan keputusan komite.</div>
                </div>
                <a href="{{ route('treatments.kanban') }}" class="btn btn-sm btn-ghost-primary ms-auto">Kanban</a>
            </div>
            <div class="card-body" data-body>
                <div class="placeholder-glow">{!! $skeletonLines(6) !!}</div>
            </div>
        </div>
    </div>

    {{-- I: assurance due --}}
    <div class="col-lg-4">
        <div class="card grc-card" data-section="assurance">
            <div class="card-header">
                <div>
                    <h3 class="card-title">Assurance jatuh tempo</h3>
                    <div class="grc-card-sub">Review vendor, review BIA, dan uji BCP per layanan dalam 30 hari.</div>
                </div>
            </div>
            <div class="card-body p-0" data-body>
                <div class="p-3 placeholder-glow">{!! $skeletonLines(6) !!}</div>
            </div>
        </div>
    </div>

    {{-- J: posture trend (month-end snapshots) --}}
    <div class="col-lg-4">
        <div class="card grc-card" data-section="trend">
            <div class="card-header">
                <div>
                    <h3 class="card-title">Tren postur risiko</h3>
                    <div class="grc-card-sub">Posisi akhir bulan dari snapshot skor.</div>
                </div>
            </div>
            <div class="card-body" data-body>
                <div class="placeholder-glow">{!! $skeletonLines(6) !!}</div>
            </div>
        </div>
    </div>
</div>
