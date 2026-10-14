@extends('layouts.tabler')

@use('App\Support\BusinessContinuity', 'BC')
@use('App\Support\RiskOwnerAccess')
@php
    $bia = $service->bia;
    $bcp = $service->bcp;
    [$statusLabel, $statusClass] = $statusLabels[$status];
    $matrix = $bia ? $bia->impact_matrix : [];
    $biaErrors = $errors->getBag('bia');
    $levelClass = [1 => 'bg-green-lt', 2 => 'bg-yellow-lt', 3 => 'bg-orange-lt', 4 => 'bg-red-lt'];
    $jsHorizons = [];
    foreach (BC::HORIZONS as $horizonMinutes => $horizonLabel) {
        $jsHorizons[] = ['minutes' => $horizonMinutes, 'label' => $horizonLabel];
    }
    $jsTierBounds = [];
    foreach (BC::TIERS as $tierName => $tierInfo) {
        $jsTierBounds[$tierName] = $tierInfo[0];
    }
@endphp

@section('title', $service->name . ' · Business Continuity')

@section('css')
<style>
    .bcm-steps { display: flex; flex-wrap: wrap; gap: .4rem; }
    .bcm-steps a { font-size: .8rem; padding: .25rem .7rem; border: 1px solid #e6e8ec; border-radius: 999px; color: #667382; text-decoration: none; background: #fff; }
    .bcm-steps a.done { border-color: #2fb344; color: #1d7a30; }
    .bcm-steps a:hover { color: #182433; }
    .bcm-matrix select { min-width: 7rem; }
    .bcm-matrix td, .bcm-matrix th { vertical-align: middle; }
    .bcm-section { scroll-margin-top: 1rem; }
    .bcm-readonly dt { font-weight: 500; color: #667382; font-size: .8rem; }
    .bcm-readonly dd { margin-bottom: .75rem; white-space: pre-line; }
</style>
@endsection

@section('content_header')
<div class="d-flex justify-content-between align-items-start flex-wrap gap-2">
    <div>
        <div class="text-muted small"><a href="{{ route('bcm.index') }}">Business Continuity</a> / {{ $service->code }}</div>
        <h2 class="page-title">{{ $service->name }}</h2>
        <div class="d-flex flex-wrap gap-2 mt-1 align-items-center">
            <span class="text-muted small">{{ RiskOwnerAccess::displayLabel($service->owner_unit) }}</span>
            @if($bia)<span class="badge {{ $bia->tier === 'Tier 1' ? 'bg-red-lt' : ($bia->tier === 'Tier 2' ? 'bg-orange-lt' : 'bg-secondary-lt') }}">{{ $bia->tier }}</span>@endif
            <span class="badge {{ $statusClass }}">{{ $statusLabel }}</span>
            @if($service->status !== 'active')<span class="badge bg-secondary-lt">{{ \App\BusinessService::STATUSES[$service->status] }}</span>@endif
        </div>
    </div>
    <nav class="bcm-steps" aria-label="Langkah business continuity">
        <a href="#profil" class="done">1 · Profil</a>
        <a href="#bia" class="{{ $bia ? 'done' : '' }}">2 · BIA</a>
        <a href="#bia" class="{{ $bia && $bia->approved_at ? 'done' : '' }}">3 · Persetujuan</a>
        <a href="#risiko" class="{{ $service->risks->isNotEmpty() ? 'done' : '' }}">4 · Risk</a>
        <a href="#bcp" class="{{ $bcp ? 'done' : '' }}">5 · BCP</a>
        <a href="#uji" class="{{ $service->tests->isNotEmpty() ? 'done' : '' }}">6 · Uji</a>
    </nav>
</div>
@endsection

@section('content')
@if($service->status === 'draft')
    <div class="alert alert-warning">
        Layanan ini masih <strong>draf</strong>. Periksa nama, unit, dan risk tertaut, lalu ubah status menjadi <strong>Aktif</strong> di bagian Profil.
    </div>
@endif

{{-- 1. Profile & dependencies --}}
<div class="card mb-3 bcm-section" id="profil">
    <div class="card-header"><h3 class="card-title">1. Profil & dependensi</h3></div>
    <div class="card-body">
        @if($canManage)
            <form method="POST" action="{{ route('bcm.services.update', $service) }}">
                @csrf @method('PUT')
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label required" for="name">Nama layanan</label>
                        <input type="text" name="name" id="name" class="form-control" value="{{ old('name', $service->name) }}" required maxlength="255">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label required" for="owner_unit">Unit pemilik</label>
                        <select name="owner_unit" id="owner_unit" class="form-select">
                            @foreach($ownerUnits as $unit)
                                <option value="{{ $unit }}" @selected(old('owner_unit', $service->owner_unit) === $unit)>{{ RiskOwnerAccess::displayLabel($unit) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label required" for="status">Status</label>
                        <select name="status" id="status" class="form-select no-search-select">
                            @foreach(\App\BusinessService::STATUSES as $key => $label)
                                <option value="{{ $key }}" @selected(old('status', $service->status) === $key)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12">
                        <label class="form-label" for="description">Deskripsi</label>
                        <textarea name="description" id="description" rows="2" class="form-control">{{ old('description', $service->description) }}</textarea>
                    </div>
                    @foreach([
                        'dependency_systems' => ['Sistem & aplikasi', 'Contoh: Core banking, database Oracle, ESB, HSM'],
                        'dependency_vendors' => ['Vendor & pihak ketiga', 'Contoh: penyedia jaringan, vendor HSM, prinsipal kartu'],
                        'dependency_sites' => ['Lokasi', 'Contoh: DC utama Jakarta, DRC Surabaya'],
                        'key_personnel' => ['SDM kunci', 'Peran (bukan nama) yang wajib tersedia saat pemulihan'],
                    ] as $field => [$label, $placeholder])
                        <div class="col-md-6">
                            <label class="form-label" for="{{ $field }}">{{ $label }}</label>
                            <textarea name="{{ $field }}" id="{{ $field }}" rows="2" class="form-control" placeholder="{{ $placeholder }}">{{ old($field, $service->{$field}) }}</textarea>
                        </div>
                    @endforeach
                </div>
                <button type="submit" class="btn btn-primary btn-sm mt-3">Simpan profil</button>
            </form>
        @else
            <dl class="row bcm-readonly mb-0">
                <dt class="col-sm-3">Deskripsi</dt><dd class="col-sm-9">{{ $service->description ?: '-' }}</dd>
                <dt class="col-sm-3">Sistem & aplikasi</dt><dd class="col-sm-9">{{ $service->dependency_systems ?: '-' }}</dd>
                <dt class="col-sm-3">Vendor & pihak ketiga</dt><dd class="col-sm-9">{{ $service->dependency_vendors ?: '-' }}</dd>
                <dt class="col-sm-3">Lokasi</dt><dd class="col-sm-9">{{ $service->dependency_sites ?: '-' }}</dd>
                <dt class="col-sm-3">SDM kunci</dt><dd class="col-sm-9">{{ $service->key_personnel ?: '-' }}</dd>
            </dl>
        @endif
    </div>
</div>

{{-- 2–3. BIA & approval --}}
<div class="card mb-3 bcm-section" id="bia">
    <div class="card-header">
        <div>
            <h3 class="card-title">2. Business Impact Analysis</h3>
            <div class="text-muted small">Nilai dampak bila layanan ini berhenti total selama durasi berikut, apa pun penyebabnya.</div>
        </div>
    </div>
    <div class="card-body">
        @if($bia)
            <div class="row g-3 mb-3">
                <div class="col-6 col-md-3"><div class="subheader">MTPD</div><div class="h3 mb-0">{{ BC::durationLabel($bia->mtpd_minutes) }}</div><div class="small text-muted">Batas gangguan yang masih dapat ditoleransi</div></div>
                <div class="col-6 col-md-3"><div class="subheader">Tier</div><div class="h3 mb-0">{{ $bia->tier }}</div><div class="small text-muted">{{ BC::TIERS[$bia->tier][1] ?? '' }}</div></div>
                <div class="col-6 col-md-3"><div class="subheader">RTO</div><div class="h3 mb-0">{{ BC::durationLabel($bia->rto_minutes) }}</div><div class="small text-muted">Target waktu pulih</div></div>
                <div class="col-6 col-md-3"><div class="subheader">RPO</div><div class="h3 mb-0">{{ BC::RPO_OPTIONS[$bia->rpo_minutes] ?? $bia->rpo_minutes . ' menit' }}</div><div class="small text-muted">Batas data yang boleh hilang</div></div>
            </div>
            <div class="alert {{ $bia->approved_at ? 'alert-success' : 'alert-warning' }} d-flex flex-wrap justify-content-between align-items-center gap-2">
                <div>
                    Dinilai oleh {{ optional($bia->assessor)->name ?? '-' }} pada {{ optional($bia->assessed_at)->format('d M Y H:i') ?? '-' }}.
                    @if($bia->approved_at)
                        <strong>Disetujui</strong> oleh {{ optional($bia->approver)->name ?? '-' }} pada {{ $bia->approved_at->format('d M Y H:i') }}.
                    @else
                        <strong>Belum disetujui.</strong> Persetujuan harus oleh pengelola unit selain pengisi BIA.
                    @endif
                    Review berikutnya: {{ optional($bia->next_review_at)->format('d M Y') ?? '-' }}{{ $bia->next_review_at && $bia->next_review_at->isPast() ? ' (terlambat)' : '' }}.
                </div>
                @if($canApprove && !$bia->approved_at)
                    <form method="POST" action="{{ route('bcm.bia.approve', $service) }}">
                        @csrf
                        <button type="submit" class="btn btn-success btn-sm">Setujui BIA</button>
                    </form>
                @endif
            </div>
        @endif

        @if($biaErrors->any())
            <div class="alert alert-danger"><ul class="mb-0">@foreach($biaErrors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
        @endif

        <form method="POST" action="{{ route('bcm.bia.update', $service) }}" id="biaForm">
            @csrf @method('PUT')
            <div class="table-responsive">
                <table class="table table-sm table-bordered bcm-matrix mb-2" data-no-sort>
                    <thead>
                        <tr>
                            <th>Kategori dampak</th>
                            @foreach(BC::HORIZONS as $minutes => $label)
                                <th class="text-center">{{ $label }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @foreach(BC::CATEGORIES as $category => $categoryLabel)
                            <tr>
                                <th class="fw-medium">{{ $categoryLabel }}</th>
                                @foreach(BC::HORIZONS as $minutes => $label)
                                    @php
                                        $value = (int) old("impact.$minutes.$category", $matrix[$minutes][$category] ?? 0);
                                    @endphp
                                    <td class="{{ $value ? $levelClass[$value] : '' }}" data-cell>
                                        @if($canManage)
                                            <select name="impact[{{ $minutes }}][{{ $category }}]" class="form-select form-select-sm no-search-select" data-impact data-minutes="{{ $minutes }}" aria-label="{{ $categoryLabel }}, {{ $label }}" required>
                                                <option value="">Pilih</option>
                                                @foreach(BC::IMPACT_LEVELS as $level => $levelLabel)
                                                    <option value="{{ $level }}" @selected($value === $level)>{{ $level }} · {{ $levelLabel }}</option>
                                                @endforeach
                                            </select>
                                        @else
                                            {{ $value ? $value . ' · ' . BC::IMPACT_LEVELS[$value] : '-' }}
                                        @endif
                                    </td>
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="small text-muted mb-3">
                Dampak <strong>Tinggi</strong> atau <strong>Kritis</strong> dianggap tidak dapat diterima. Titik waktu pertama dampak itu tercapai menjadi MTPD.
                Dampak tidak boleh turun seiring lamanya gangguan.
            </div>

            @if($canManage)
                <div class="alert alert-info py-2" data-bia-preview>Isi matriks untuk melihat MTPD dan tier.</div>
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label required" for="rto_minutes">RTO</label>
                        <select name="rto_minutes" id="rto_minutes" class="form-select no-search-select" required>
                            <option value="">Pilih</option>
                            @foreach(BC::RTO_OPTIONS as $minutes => $label)
                                <option value="{{ $minutes }}" @selected((int) old('rto_minutes', optional($bia)->rto_minutes) === $minutes && old('rto_minutes', optional($bia)->rto_minutes) !== null)>{{ $label }}</option>
                            @endforeach
                        </select>
                        <div class="form-hint">Harus ≤ MTPD.</div>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label required" for="rpo_minutes">RPO</label>
                        <select name="rpo_minutes" id="rpo_minutes" class="form-select no-search-select" required>
                            <option value="">Pilih</option>
                            @foreach(BC::RPO_OPTIONS as $minutes => $label)
                                <option value="{{ $minutes }}" @selected(old('rpo_minutes', optional($bia)->rpo_minutes) !== null && (int) old('rpo_minutes', optional($bia)->rpo_minutes) === $minutes)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label required" for="next_review_at">Review berikutnya</label>
                        <input type="date" name="next_review_at" id="next_review_at" class="form-control" required
                               value="{{ old('next_review_at', optional(optional($bia)->next_review_at)->format('Y-m-d') ?? now()->addYear()->format('Y-m-d')) }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="minimum_resources">Sumber daya minimum untuk pulih</label>
                        <textarea name="minimum_resources" id="minimum_resources" rows="2" class="form-control" placeholder="Contoh: 2 node DB di DRC, 3 operator, akses VPN darurat">{{ old('minimum_resources', optional($bia)->minimum_resources) }}</textarea>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="notes">Catatan & asumsi</label>
                        <textarea name="notes" id="notes" rows="2" class="form-control">{{ old('notes', optional($bia)->notes) }}</textarea>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary btn-sm mt-3">{{ $bia ? 'Simpan perubahan BIA' : 'Simpan BIA' }}</button>
                @if($bia && $bia->approved_at)
                    <span class="small text-muted ms-2">Perubahan akan membatalkan persetujuan saat ini.</span>
                @endif
            @elseif($bia)
                <dl class="row bcm-readonly mb-0">
                    <dt class="col-sm-3">Sumber daya minimum</dt><dd class="col-sm-9">{{ $bia->minimum_resources ?: '-' }}</dd>
                    <dt class="col-sm-3">Catatan</dt><dd class="col-sm-9">{{ $bia->notes ?: '-' }}</dd>
                </dl>
            @else
                <div class="text-muted">BIA belum diisi oleh unit pemilik.</div>
            @endif
        </form>
    </div>
</div>

{{-- 4. Linked risks --}}
<div class="card mb-3 bcm-section" id="risiko">
    <div class="card-header">
        <div>
            <h3 class="card-title">4. Risk yang dapat mengganggu layanan ini</h3>
            <div class="text-muted small">Penilaian kemungkinan dan dampak tetap di risk register; di sini hanya tautannya.</div>
        </div>
    </div>
    <div class="table-responsive">
        <table class="table table-vcenter card-table">
            <thead><tr><th>Risk</th><th>Unit</th><th>Skor inheren → residual</th>@if($canManage)<th class="w-1"></th>@endif</tr></thead>
            <tbody>
                @forelse($service->risks as $risk)
                    <tr>
                        <td><a href="{{ route('risks.show', $risk->id) }}">{{ $risk->risk_id }}</a><div class="small text-muted">{{ \Illuminate\Support\Str::limit($risk->risk_title, 90) }}</div></td>
                        <td class="small text-muted">{{ RiskOwnerAccess::displayLabel($risk->risk_owner) }}</td>
                        <td class="text-nowrap">{{ optional($risk->inherentAnalysis)->inherent_risk_score ?? '-' }} → <strong>{{ optional($risk->residualAnalysis)->residual_risk_score ?? '-' }}</strong></td>
                        @if($canManage)
                            <td>
                                <form method="POST" action="{{ route('bcm.risks.detach', [$service, $risk->id]) }}">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-ghost-danger" aria-label="Lepas tautan {{ $risk->risk_id }}">Lepas</button>
                                </form>
                            </td>
                        @endif
                    </tr>
                @empty
                    <tr><td colspan="4" class="text-muted text-center py-3">Belum ada risk tertaut.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($canManage && $linkableRisks->isNotEmpty())
        <div class="card-footer">
            <form method="POST" action="{{ route('bcm.risks.attach', $service) }}" class="d-flex flex-wrap gap-2 align-items-end">
                @csrf
                <div class="flex-grow-1" style="min-width: 260px;">
                    <label class="form-label" for="risk_id">Tautkan risk</label>
                    <select name="risk_id" id="risk_id" class="form-select" required>
                        <option value="">Cari risk...</option>
                        @foreach($linkableRisks as $risk)
                            <option value="{{ $risk->id }}">{{ $risk->risk_id }} · {{ \Illuminate\Support\Str::limit($risk->risk_title, 80) }}</option>
                        @endforeach
                    </select>
                </div>
                <button type="submit" class="btn btn-outline-primary">Tautkan</button>
            </form>
        </div>
    @endif
</div>

{{-- 5. BCP --}}
<div class="card mb-3 bcm-section" id="bcp">
    <div class="card-header">
        <div>
            <h3 class="card-title">5. Business Continuity Plan</h3>
            <div class="text-muted small">Strategi untuk memulihkan layanan dalam RTO{{ $bia ? ' (' . BC::durationLabel($bia->rto_minutes) . ')' : '' }}.</div>
        </div>
    </div>
    <div class="card-body">
        @if(!$bia)
            <div class="text-muted">Isi BIA lebih dulu. BCP disusun untuk memenuhi RTO yang ditetapkan di BIA.</div>
        @elseif($canManage)
            <form method="POST" action="{{ route('bcm.bcp.update', $service) }}">
                @csrf @method('PUT')
                <div class="row g-3">
                    @foreach([
                        'activation_criteria' => ['Kriteria aktivasi', true, 'Kapan BCP dijalankan dan siapa yang memutuskan'],
                        'strategy_summary' => ['Strategi pemulihan', true, 'Contoh: failover ke DRC, mode degradasi, proses manual'],
                        'recovery_procedures' => ['Prosedur pemulihan', true, 'Langkah berurutan, atau rujukan ke runbook'],
                        'primary_workaround' => ['Workaround sementara', false, 'Cara melayani nasabah selama pemulihan'],
                        'contacts' => ['Kontak & eskalasi', true, 'Peran, nomor darurat, vendor yang dihubungi'],
                    ] as $field => [$label, $required, $placeholder])
                        <div class="col-md-6">
                            <label class="form-label {{ $required ? 'required' : '' }}" for="{{ $field }}">{{ $label }}</label>
                            <textarea name="{{ $field }}" id="{{ $field }}" rows="3" class="form-control" placeholder="{{ $placeholder }}" @if($required) required @endif>{{ old($field, optional($bcp)->{$field}) }}</textarea>
                        </div>
                    @endforeach
                    <div class="col-md-6">
                        <label class="form-label required" for="testing_frequency">Frekuensi uji</label>
                        <select name="testing_frequency" id="testing_frequency" class="form-select no-search-select" required>
                            @foreach(BC::TESTING_FREQUENCIES as $key => [$label])
                                <option value="{{ $key }}" @selected(old('testing_frequency', optional($bcp)->testing_frequency ?? ($bia->tier === 'Tier 1' ? 'Semi-Annual' : 'Annual')) === $key)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary btn-sm mt-3">{{ $bcp ? 'Simpan perubahan BCP' : 'Simpan BCP' }}</button>
            </form>
        @elseif($bcp)
            <dl class="row bcm-readonly mb-0">
                <dt class="col-sm-3">Kriteria aktivasi</dt><dd class="col-sm-9">{{ $bcp->activation_criteria }}</dd>
                <dt class="col-sm-3">Strategi</dt><dd class="col-sm-9">{{ $bcp->strategy_summary }}</dd>
                <dt class="col-sm-3">Prosedur</dt><dd class="col-sm-9">{{ $bcp->recovery_procedures }}</dd>
                <dt class="col-sm-3">Workaround</dt><dd class="col-sm-9">{{ $bcp->primary_workaround ?: '-' }}</dd>
                <dt class="col-sm-3">Kontak</dt><dd class="col-sm-9">{{ $bcp->contacts }}</dd>
                <dt class="col-sm-3">Frekuensi uji</dt><dd class="col-sm-9">{{ BC::TESTING_FREQUENCIES[$bcp->testing_frequency][0] ?? $bcp->testing_frequency }}</dd>
            </dl>
        @else
            <div class="text-muted">BCP belum disusun oleh unit pemilik.</div>
        @endif
    </div>
</div>

{{-- 6. Tests --}}
<div class="card mb-3 bcm-section" id="uji">
    <div class="card-header">
        <div>
            <h3 class="card-title">6. Riwayat pengujian</h3>
            <div class="text-muted small">
                @if($bcp)
                    Frekuensi {{ strtolower(BC::TESTING_FREQUENCIES[$bcp->testing_frequency][0] ?? $bcp->testing_frequency) }}.
                    @if($nextTestDue)
                        Uji berikutnya paling lambat {{ $nextTestDue->format('d M Y') }}{{ $nextTestDue->isPast() ? ' (terlambat)' : '' }}.
                    @else
                        Belum pernah diuji.
                    @endif
                @else
                    Tersedia setelah BCP disusun.
                @endif
            </div>
        </div>
    </div>
    <div class="table-responsive">
        <table class="table table-vcenter card-table">
            <thead><tr><th>Tanggal</th><th>Jenis</th><th>Hasil</th><th>Waktu pulih vs RTO</th><th>Temuan & tindak lanjut</th></tr></thead>
            <tbody>
                @forelse($service->tests as $test)
                    <tr>
                        <td class="text-nowrap">{{ $test->tested_at->format('d M Y') }}</td>
                        <td>{{ BC::TEST_TYPES[$test->test_type] ?? $test->test_type }}</td>
                        <td><span class="badge {{ ['pass' => 'bg-green-lt', 'partial' => 'bg-yellow-lt', 'fail' => 'bg-red-lt'][$test->result] ?? 'bg-secondary-lt' }}">{{ BC::TEST_RESULTS[$test->result] ?? $test->result }}</span></td>
                        <td class="text-nowrap">
                            @if($test->actual_recovery_minutes !== null)
                                {{ $test->actual_recovery_minutes }} menit
                                @if($bia)
                                    @if($test->actual_recovery_minutes <= $bia->rto_minutes)
                                        <span class="badge bg-green-lt">Dalam RTO</span>
                                    @else
                                        <span class="badge bg-red-lt">Melebihi RTO</span>
                                    @endif
                                @endif
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td class="small">{{ $test->findings ?: '-' }}@if($test->follow_up)<div class="text-muted">Tindak lanjut: {{ $test->follow_up }}</div>@endif</td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-muted text-center py-3">Belum ada pengujian tercatat.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($canManage && $bcp)
        <div class="card-footer">
            <form method="POST" action="{{ route('bcm.tests.store', $service) }}">
                @csrf
                <div class="row g-2 align-items-end">
                    <div class="col-md-2">
                        <label class="form-label required" for="tested_at">Tanggal uji</label>
                        <input type="date" name="tested_at" id="tested_at" class="form-control" max="{{ now()->format('Y-m-d') }}" required value="{{ old('tested_at') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label required" for="test_type">Jenis</label>
                        <select name="test_type" id="test_type" class="form-select no-search-select" required>
                            @foreach(BC::TEST_TYPES as $key => $label)<option value="{{ $key }}" @selected(old('test_type') === $key)>{{ $label }}</option>@endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label required" for="result">Hasil</label>
                        <select name="result" id="result" class="form-select no-search-select" required>
                            @foreach(BC::TEST_RESULTS as $key => $label)<option value="{{ $key }}" @selected(old('result') === $key)>{{ $label }}</option>@endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label" for="actual_recovery_minutes">Waktu pulih (menit)</label>
                        <input type="number" min="0" name="actual_recovery_minutes" id="actual_recovery_minutes" class="form-control" value="{{ old('actual_recovery_minutes') }}">
                    </div>
                    <div class="col-md-3">
                        <button type="submit" class="btn btn-outline-primary w-100">Catat hasil uji</button>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="findings">Temuan</label>
                        <textarea name="findings" id="findings" rows="2" class="form-control">{{ old('findings') }}</textarea>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="follow_up">Tindak lanjut</label>
                        <textarea name="follow_up" id="follow_up" rows="2" class="form-control">{{ old('follow_up') }}</textarea>
                    </div>
                </div>
            </form>
        </div>
    @endif
</div>
@endsection

@if($canManage)
@section('js')
<script>
(function () {
    // Live MTPD / tier preview and RTO options that respect MTPD (the server re-validates).
    const horizons = @json($jsHorizons);
    const tiers = @json($jsTierBounds);
    const unacceptable = {{ BC::UNACCEPTABLE_LEVEL }};
    const cellClass = {1: 'bg-green-lt', 2: 'bg-yellow-lt', 3: 'bg-orange-lt', 4: 'bg-red-lt'};
    const form = document.getElementById('biaForm');
    const preview = form && form.querySelector('[data-bia-preview]');
    const rto = document.getElementById('rto_minutes');
    if (!form || !preview || !rto) return;

    function refresh() {
        const selects = Array.from(form.querySelectorAll('[data-impact]'));
        selects.forEach((select) => {
            const cell = select.closest('[data-cell]');
            Object.values(cellClass).forEach((cls) => cell.classList.remove(cls));
            if (select.value) cell.classList.add(cellClass[select.value]);
        });
        const complete = selects.every((select) => select.value);
        let mtpd = null;
        for (const horizon of horizons) {
            if (selects.some((select) => Number(select.dataset.minutes) === horizon.minutes && Number(select.value) >= unacceptable)) {
                mtpd = horizon;
                break;
            }
        }
        let tier = 'Tier 4';
        if (mtpd) {
            for (const [name, bound] of Object.entries(tiers)) {
                if (bound !== null && mtpd.minutes <= bound) { tier = name; break; }
            }
        }
        Array.from(rto.options).forEach((option) => {
            option.disabled = option.value !== '' && mtpd !== null && Number(option.value) > mtpd.minutes;
        });
        if (rto.selectedOptions[0] && rto.selectedOptions[0].disabled) rto.value = '';
        preview.textContent = complete
            ? 'MTPD: ' + (mtpd ? mtpd.label : 'lebih dari 1 minggu') + ' · ' + tier + '. Pilih RTO yang tidak melebihi MTPD.'
            : 'Isi semua sel matriks untuk melihat MTPD dan tier.';
    }

    form.addEventListener('change', refresh);
    refresh();
})();
</script>
@endsection
@endif
