{{--
    Main GRC dashboard ("Ringkasan"). Each [data-section] loads its own data
    from route('dashboard.sections') and renders a loading, empty or error state.
    Reading order answers three questions: are we within appetite (tiles,
    heatmap) -> getting better or worse (trend) -> where to act (KRI, top risks,
    actions). Item lists live in the follow-up drawer at the bottom.
--}}
@php
    $skeletonLines = fn (int $n) => str_repeat('<span class="placeholder col-12 mb-2"></span>', $n);
    $info = fn (string $text) => '<button type="button" class="grc-info" data-tip="' . e($text) . '" aria-label="' . e($text) . '">i</button>';
@endphp

{{-- A: status tiles --}}
<div class="grc-tiles mb-3" data-section="summary" aria-live="polite">
    @foreach(['Di luar appetite', 'KRI merah / kuning', 'Aksi terlambat', 'Loss event', 'Insiden terbuka'] as $label)
        <div class="grc-tile placeholder-glow" aria-hidden="true">
            <span class="grc-tile-label">{{ $label }}</span>
            <span class="placeholder col-5" style="height: 1.6rem;"></span>
            <span class="placeholder col-8"></span>
        </div>
    @endforeach
</div>

{{-- A2: portfolio at a glance (detail on the register and incident pages) --}}
<div class="row row-cards mb-3" data-section="portfolio">
    <div class="col-lg-6">
        <div class="card grc-card" data-portfolio="risks">
            <div class="card-header">
                <h3 class="card-title">Risk register per divisi</h3>
                {!! $info('Semua risk di register yang boleh Anda lihat, dikelompokkan menurut divisi pemilik risk.') !!}
                <a href="{{ route('risks.index') }}" class="btn btn-sm btn-ghost-primary ms-auto">Register</a>
            </div>
            <div class="card-body" data-body>
                <div class="placeholder-glow">{!! $skeletonLines(5) !!}</div>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card grc-card" data-portfolio="incidents">
            <div class="card-header">
                <h3 class="card-title">Insiden per client</h3>
                {!! $info('Semua insiden yang boleh Anda lihat, dikelompokkan menurut client atau pihak yang terdampak.') !!}
                <a href="{{ route('incidents.index') }}" class="btn btn-sm btn-ghost-primary ms-auto">Insiden</a>
            </div>
            <div class="card-body" data-body>
                <div class="placeholder-glow">{!! $skeletonLines(5) !!}</div>
            </div>
        </div>
    </div>
</div>

<div class="row row-cards mb-3">
    {{-- C: heatmap --}}
    <div class="col-lg-5">
        <div class="card grc-card" data-section="heatmap">
            <div class="card-header">
                <h3 class="card-title">Peta risiko</h3>
                {!! $info('Jumlah risk per sel kemungkinan × dampak. Klik sel untuk melihat risk di dalamnya.') !!}
                <div class="grc-seg ms-auto" role="group" aria-label="Jenis skor" data-heat-toggle hidden>
                    <button type="button" data-heat-kind="residual" aria-pressed="true">Residual</button>
                    <button type="button" data-heat-kind="inherent" aria-pressed="false">Inheren</button>
                </div>
            </div>
            <div class="card-body" data-body>
                <div class="placeholder-glow">{!! $skeletonLines(7) !!}</div>
            </div>
        </div>
    </div>

    {{-- J: posture trend (month-end snapshots) --}}
    <div class="col-lg-7">
        <div class="card grc-card" data-section="trend">
            <div class="card-header">
                <h3 class="card-title">Tren postur risiko, 12 bulan</h3>
                {!! $info('Posisi akhir bulan dari snapshot skor: jumlah risk yang melewati toleransi appetite dan risk dengan level residual tinggi.') !!}
            </div>
            <div class="card-body" data-body>
                <div class="placeholder-glow">{!! $skeletonLines(7) !!}</div>
            </div>
        </div>
    </div>
</div>

<div class="row row-cards mb-3">
    {{-- F: KRI watchlist --}}
    <div class="col-lg-7">
        <div class="card grc-card" data-section="kris">
            <div class="card-header">
                <h3 class="card-title">KRI yang perlu dipantau</h3>
                {!! $info('KRI merah, kuning, atau yang datanya belum diperbarui sesuai jadwal. Pita latar = zona hijau / kuning / merah, batang gelap = nilai terkini.') !!}
                <a href="{{ route('kris.index') }}" class="btn btn-sm btn-ghost-primary ms-auto">Semua KRI</a>
            </div>
            <div class="card-body" data-body>
                <div class="placeholder-glow">{!! $skeletonLines(6) !!}</div>
            </div>
        </div>
    </div>

    {{-- E: top risks --}}
    <div class="col-lg-5">
        <div class="card grc-card" data-section="top-risks">
            <div class="card-header">
                <h3 class="card-title">10 risiko residual tertinggi</h3>
                {!! $info('Titik kosong = skor inheren, titik berwarna = skor residual, garis tegak = batas appetite risk tersebut.') !!}
                <a href="{{ route('risks.index') }}" class="btn btn-sm btn-ghost-primary ms-auto">Register</a>
            </div>
            <div class="card-body" data-body>
                <div class="placeholder-glow">{!! $skeletonLines(7) !!}</div>
            </div>
        </div>
    </div>
</div>

<div class="row row-cards mb-3">
    {{-- D: profile by category --}}
    <div class="col-md-6 col-xl-4">
        <div class="card grc-card" data-section="profile">
            <div class="card-header">
                <h3 class="card-title">Profil risiko per kategori</h3>
                {!! $info('Jumlah risk aktif per kategori, dipecah menurut tingkat residual.') !!}
            </div>
            <div class="card-body" data-body>
                <div class="placeholder-glow">{!! $skeletonLines(6) !!}</div>
            </div>
        </div>
    </div>

    {{-- G: losses --}}
    <div class="col-md-6 col-xl-4">
        <div class="card grc-card" data-section="losses">
            <div class="card-header">
                <h3 class="card-title">Loss event, 12 bulan</h3>
                {!! $info('Nilai kerugian insiden per bulan kejadian. Jumlah kejadian ada di tooltip.') !!}
                <a href="{{ route('incidents.index') }}" class="btn btn-sm btn-ghost-primary ms-auto">Insiden</a>
            </div>
            <div class="card-body" data-body>
                <div class="placeholder-glow">{!! $skeletonLines(6) !!}</div>
            </div>
        </div>
    </div>

    {{-- H: overdue actions --}}
    <div class="col-xl-4">
        <div class="card grc-card" data-section="actions">
            <div class="card-header">
                <h3 class="card-title">Umur aksi terlambat per unit</h3>
                {!! $info('Treatment, CAPA insiden, dan keputusan komite yang lewat tenggat, dikelompokkan menurut lamanya terlambat.') !!}
                <a href="{{ route('treatments.kanban') }}" class="btn btn-sm btn-ghost-primary ms-auto">Kanban</a>
            </div>
            <div class="card-body" data-body>
                <div class="placeholder-glow">{!! $skeletonLines(6) !!}</div>
            </div>
        </div>
    </div>
</div>

{{-- I: assurance due --}}
<div class="card grc-card mb-3" data-section="assurance">
    <div class="card-header">
        <h3 class="card-title">Assurance jatuh tempo, 30 hari</h3>
        {!! $info('Jadwal review vendor, review BIA, dan uji BCP per layanan. Area merah muda = sudah lewat tenggat.') !!}
        <div class="ms-auto d-flex gap-1" data-assurance-counts></div>
    </div>
    <div class="card-body" data-body>
        <div class="placeholder-glow">{!! $skeletonLines(3) !!}</div>
    </div>
</div>

{{-- B: follow-up drawer --}}
<div class="card grc-card mb-3" data-section="attention" id="grc-followup">
    <div class="card-header">
        <h3 class="card-title">Daftar tindak lanjut</h3>
        {!! $info('Pengecualian yang butuh tindak lanjut, paling kritis dan paling lama di atas. Klik tile atau chip untuk memfilter.') !!}
        <div class="grc-chips" data-attention-filters hidden></div>
        <button type="button" class="btn btn-sm btn-ghost-primary ms-auto" data-attention-toggle aria-expanded="false" hidden>Tampilkan daftar</button>
    </div>
    <div class="card-body p-0" data-body>
        <div class="p-3 placeholder-glow">{!! $skeletonLines(2) !!}</div>
    </div>
</div>
