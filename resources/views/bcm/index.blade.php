@extends('layouts.tabler')

@section('title', 'Business Continuity')

@section('content_header')
<div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
    <div>
        <h2 class="page-title">Business Continuity</h2>
        <div class="text-muted mt-1">BIA, BCP, dan pengujian dikelola per layanan, lalu ditautkan ke risk yang dapat mengganggunya.</div>
    </div>
    @if(!empty($manageableUnits))
        <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modal-create-service">
            <i class="fas fa-plus me-1"></i> Tambah layanan
        </button>
    @endif
</div>
@endsection

@section('content')
<div class="card mb-3">
    <div class="card-body py-3">
        <div class="row g-3 small">
            @foreach([
                ['1', 'Daftarkan layanan', 'Layanan atau proses yang harus tetap berjalan, beserta dependensinya.'],
                ['2', 'Isi BIA', 'Dampak bila layanan berhenti 1 jam s.d. 1 minggu. MTPD, tier, dan batas RTO dihitung otomatis.'],
                ['3', 'Setujui BIA', 'Pengelola unit lain menyetujui (maker-checker).'],
                ['4', 'Tautkan risk', 'Risk dari register yang dapat menghentikan layanan ini.'],
                ['5', 'Susun & uji BCP', 'Strategi pemulihan, lalu catat setiap uji dan waktu pulih aktual.'],
            ] as [$step, $title, $text])
                <div class="col-12 col-md">
                    <div class="d-flex gap-2">
                        <span class="badge bg-blue-lt align-self-start">{{ $step }}</span>
                        <div><strong>{{ $title }}</strong><div class="text-muted">{{ $text }}</div></div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>

<div class="row row-cards mb-3">
    @foreach([
        ['Layanan terdaftar', $stats['services'], 'Tidak termasuk yang tidak aktif', ''],
        ['Tier 1', $stats['tier1'], 'Harus pulih dalam 4 jam', ''],
        ['BIA belum siap', $stats['pending'], 'Belum diisi atau belum disetujui', $stats['pending'] > 0 ? 'text-warning' : ''],
        ['BCP perlu tindakan', $stats['attention'], 'Belum disusun, belum/terlambat diuji, atau uji gagal', $stats['attention'] > 0 ? 'text-danger' : ''],
    ] as [$label, $value, $hint, $class])
        <div class="col-sm-6 col-lg-3">
            <div class="card card-sm">
                <div class="card-body">
                    <div class="subheader">{{ $label }}</div>
                    <div class="h1 mb-0 {{ $class }}">{{ $value }}</div>
                    <div class="text-muted small">{{ $hint }}</div>
                </div>
            </div>
        </div>
    @endforeach
</div>

<div class="card">
    <div class="card-header">
        <h3 class="card-title">Katalog layanan</h3>
        <div class="ms-auto">
            <form method="GET" action="{{ route('bcm.index') }}" class="d-flex gap-2">
                <select name="tier" class="form-select form-select-sm no-search-select" onchange="this.form.submit()">
                    <option value="">Semua tier</option>
                    @foreach(\App\Support\BusinessContinuity::TIERS as $tier => [$bound, $description])
                        <option value="{{ $tier }}" @selected(request('tier') === $tier)>{{ $tier }}</option>
                    @endforeach
                </select>
                <select name="state" class="form-select form-select-sm no-search-select" onchange="this.form.submit()">
                    <option value="">Semua status</option>
                    @foreach($statusLabels as $key => [$label])
                        <option value="{{ $key }}" @selected(request('state') === $key)>{{ $label }}</option>
                    @endforeach
                </select>
                @if(request('tier') || request('state'))
                    <a href="{{ route('bcm.index') }}" class="btn btn-sm btn-outline-secondary">Hapus filter</a>
                @endif
            </form>
        </div>
    </div>
    <div class="table-responsive">
        <table class="table table-vcenter card-table">
            <thead>
                <tr>
                    <th>Layanan</th>
                    <th>Unit pemilik</th>
                    <th>Tier</th>
                    <th>MTPD / RTO / RPO</th>
                    <th>Risk tertaut</th>
                    <th>Uji terakhir</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($services as $service)
                    @php([$statusLabel, $statusClass] = $statusLabels[$service->continuityStatus()])
                    <tr>
                        <td>
                            <a href="{{ route('bcm.services.show', $service) }}" class="fw-medium">{{ $service->name }}</a>
                            <div class="text-muted small">
                                {{ $service->code }}
                                @if($service->status !== 'active')
                                    · <span class="badge bg-secondary-lt">{{ \App\BusinessService::STATUSES[$service->status] ?? $service->status }}</span>
                                @endif
                            </div>
                        </td>
                        <td class="text-muted small">{{ \App\Support\RiskOwnerAccess::displayLabel($service->owner_unit) }}</td>
                        <td>
                            @if($service->bia)
                                <span class="badge {{ $service->bia->tier === 'Tier 1' ? 'bg-red-lt' : ($service->bia->tier === 'Tier 2' ? 'bg-orange-lt' : 'bg-secondary-lt') }}">{{ $service->bia->tier }}</span>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td class="small text-nowrap">
                            @if($service->bia)
                                {{ \App\Support\BusinessContinuity::durationLabel($service->bia->mtpd_minutes) }}
                                / {{ \App\Support\BusinessContinuity::durationLabel($service->bia->rto_minutes) }}
                                / {{ \App\Support\BusinessContinuity::RPO_OPTIONS[$service->bia->rpo_minutes] ?? $service->bia->rpo_minutes . ' menit' }}
                            @else
                                <span class="text-muted">Belum ada BIA</span>
                            @endif
                        </td>
                        <td>{{ $service->risks_count }}</td>
                        <td class="small">
                            @if($service->latestTest)
                                {{ $service->latestTest->tested_at->format('d M Y') }}
                                · {{ \App\Support\BusinessContinuity::TEST_RESULTS[$service->latestTest->result] ?? $service->latestTest->result }}
                            @else
                                <span class="text-muted">Belum pernah</span>
                            @endif
                        </td>
                        <td><span class="badge {{ $statusClass }}">{{ $statusLabel }}</span></td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center py-4 text-muted">
                            Belum ada layanan terdaftar.
                            @if(!empty($manageableUnits))
                                Mulai dengan <strong>Tambah layanan</strong> untuk layanan yang paling kritikal.
                            @endif
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@if(!empty($manageableUnits))
<div class="modal modal-blur fade" id="modal-create-service" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form class="modal-content" method="POST" action="{{ route('bcm.services.store') }}">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title">Tambah layanan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label required" for="serviceName">Nama layanan atau proses</label>
                    <input type="text" name="name" id="serviceName" class="form-control" maxlength="255" required placeholder="Contoh: Layanan transfer BI-FAST">
                </div>
                <div class="mb-3">
                    <label class="form-label required" for="serviceUnit">Unit pemilik</label>
                    <select name="owner_unit" id="serviceUnit" class="form-select" required>
                        @foreach($manageableUnits as $unit)
                            <option value="{{ $unit }}">{{ \App\Support\RiskOwnerAccess::displayLabel($unit) }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="form-label" for="serviceDescription">Deskripsi</label>
                    <textarea name="description" id="serviceDescription" rows="3" class="form-control" placeholder="Apa yang dilayani, untuk siapa, dan kapan layanan berjalan."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-link link-secondary me-auto" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-primary">Simpan & isi BIA</button>
            </div>
        </form>
    </div>
</div>
@endif
@endsection
