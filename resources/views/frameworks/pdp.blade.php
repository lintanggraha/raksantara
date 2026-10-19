@extends('layouts.tabler')

@section('title', 'UU No. 27/2022 Pelindungan Data Pribadi (UU PDP) - Raksantara')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/framework-reader.css') }}">
@endpush

@section('content')
@php
    $reqCollection = collect($requirements);
    $totalReqs = $reqCollection->count();
    $chapters = $reqCollection->pluck('chapter_title', 'chapter')->unique();
    $firstReq = $reqCollection->first();
@endphp

<div class="fw-page">
    {{-- COMPACT MODERN HERO --}}
    <div class="fw-hero">
        <div class="fw-hero-header">
            <div class="fw-kicker">
                <i class="fas fa-scale-balanced"></i> Undang-Undang Republik Indonesia
            </div>
            <span class="badge bg-purple-lt text-purple fw-bold px-3 py-1 rounded-pill">Status: Aktif Berlaku</span>
        </div>
        <h1 class="fw-title">UU No. 27 Tahun 2022 (UU PDP)</h1>
        <p class="fw-lede">
            Undang-Undang Pelindungan Data Pribadi. Payung hukum komprehensif atas tata kelola privasi data pribadi di Indonesia. Mengatur hak mutlak subjek data, kewajiban Pengendali & Prosesor data, legal basis pemrosesan, notifikasi breach 3x24 jam, transfer data internasional, serta sanksi denda administratif hingga 2% omset tahunan dan pidana penjara.
        </p>
        <div class="fw-metric-row">
            <div class="fw-metric-pill">
                <span>Total Ketentuan:</span>
                <strong>{{ $totalReqs }} Klausul Inti</strong>
            </div>
            <div class="fw-metric-pill">
                <span>Asas Pemrosesan:</span>
                <strong>8 Asas Utama</strong>
            </div>
            <div class="fw-metric-pill">
                <span>Hak Subjek Data:</span>
                <strong>8 Hak Mutlak</strong>
            </div>
            <div class="fw-metric-pill">
                <span>Notifikasi Breach:</span>
                <strong>Maksimal 3x24 Jam</strong>
            </div>
        </div>
    </div>

    {{-- NAVIGATION TABS --}}
    <div class="fw-nav-container">
        <ul class="fw-nav">
            <li>
                <button class="fw-tab active" data-tab="tab-explorer">
                    <i class="fas fa-list-check"></i>
                    <span>Eksplorasi Pasal UU PDP ({{ $totalReqs }})</span>
                </button>
            </li>
            <li>
                <button class="fw-tab" data-tab="tab-overview">
                    <i class="fas fa-cubes"></i>
                    <span>8 Asas & Klasifikasi Data</span>
                </button>
            </li>
            <li>
                <button class="fw-tab" data-tab="tab-rights">
                    <i class="fas fa-user-shield"></i>
                    <span>8 Hak Subjek Data</span>
                </button>
            </li>
            <li>
                <button class="fw-tab" data-tab="tab-dpo">
                    <i class="fas fa-user-tie"></i>
                    <span>DPO, DPIA & Notifikasi Breach</span>
                </button>
            </li>
        </ul>
    </div>

    {{-- TAB 1: CLAUSE EXPLORER & MASTER-DETAIL (ACTIVE DEFAULT) --}}
    <div class="fw-tab-content" id="tab-explorer">
        <div class="fw-workspace">
            {{-- LEFT COLUMN: LIST PANEL --}}
            <div class="fw-list-panel">
                <div class="fw-search-box">
                    <div class="fw-search-input-wrap">
                        <i class="fas fa-search"></i>
                        <input
                            type="text"
                            id="fw-search-input"
                            class="fw-search-input"
                            placeholder="Cari pasal, kata kunci, hak subjek, dpo, breach..."
                            autocomplete="off"
                        >
                        <span class="fw-search-kbd">/</span>
                    </div>
                    <select id="fw-filter-select" class="fw-filter-select">
                        <option value="">Semua Bab ({{ count($chapters) }} Bab)</option>
                        @foreach($chapters as $chKey => $chTitle)
                            <option value="{{ $chKey }}">Bab {{ $chKey }}: {{ Str::limit($chTitle ?: 'Ketentuan', 30) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="fw-counter-bar">
                    <span id="fw-counter">{{ $totalReqs }} dari {{ $totalReqs }} klausul ditampilkan</span>
                    <span class="text-muted small"><i class="fas fa-arrows-alt-v me-1"></i> Gunakan ↑/↓ keyboard</span>
                </div>

                <div class="fw-list" id="fw-items-container">
                    @foreach($requirements as $index => $req)
                        @php
                            $searchIndex = strtolower(($req['id'] ?? '') . ' ' . ($req['title'] ?? '') . ' ' . ($req['summary'] ?? '') . ' ' . ($req['pillar'] ?? '') . ' ' . ($req['chapter'] ?? ''));
                            $bod = $req['roleTranslations']['bod'] ?? ($req['roleTranslations']['board'] ?? '');
                            $sysadmin = $req['roleTranslations']['sysadmin'] ?? '';
                            $legal = $req['roleTranslations']['legal'] ?? '';
                        @endphp
                        <button
                            type="button"
                            class="fw-item {{ $index === 0 ? 'active' : '' }}"
                            data-group="{{ $req['chapter'] ?? '' }}"
                            data-search-index="{{ $searchIndex }}"
                            data-id="{{ $req['id'] ?? '' }}"
                            data-title="{{ $req['title'] ?? '' }}"
                            data-chapter="Bab {{ $req['chapter'] ?? '-' }}"
                            data-pillar="{{ $req['pillar'] ?? 'PDP' }}"
                            data-summary="{{ $req['summary'] ?? '' }}"
                            data-owner="{{ $req['owner'] ?? 'Semua Pihak' }}"
                            data-cadence="{{ $req['cadence'] ?? '-' }}"
                            data-analogy="{{ $req['analogy'] ?? '' }}"
                            data-focus='@json($req['focus'] ?? [])'
                            data-evidence='@json($req['evidence'] ?? [])'
                            data-bod="{{ $bod }}"
                            data-sysadmin="{{ $sysadmin }}"
                            data-legal="{{ $legal }}"
                        >
                            <div class="fw-item-top">
                                <span class="fw-item-code">{{ $req['id'] }}</span>
                                <span class="fw-item-tag">Bab {{ $req['chapter'] ?? '-' }}</span>
                            </div>
                            <div class="fw-item-title">{{ $req['title'] ?? '' }}</div>
                            <div class="fw-item-desc">{{ Str::limit($req['summary'] ?? '', 95) }}</div>
                        </button>
                    @endforeach
                </div>
            </div>

            {{-- RIGHT COLUMN: INSPECTOR PANEL --}}
            <div class="fw-inspector">
                <div class="fw-inspector-head">
                    <div class="fw-inspector-actions">
                        <span class="fw-badge-legal" id="insp-id">{{ $firstReq['id'] ?? '-' }}</span>
                        <div class="d-flex align-items-center gap-2">
                            <button type="button" class="btn btn-sm btn-outline-primary" data-copy-target="insp-summary" title="Salin ringkasan pasal">
                                <i class="fas fa-copy me-1"></i> Salin Teks
                            </button>
                            <a href="{{ route('analysis.cross-mapping') }}" class="btn btn-sm btn-outline-secondary" title="Buka pemetaan regulasi silang">
                                <i class="fas fa-network-wired me-1"></i> Cross-Mapping
                            </a>
                        </div>
                    </div>
                    <h2 class="fw-inspector-title" id="insp-title">{{ $firstReq['title'] ?? 'Pilih Pasal' }}</h2>
                    <div class="fw-meta-chips">
                        <span class="fw-meta-chip"><i class="fas fa-book text-muted"></i> <span id="insp-chapter">-</span></span>
                        <span class="fw-meta-chip"><i class="fas fa-shield-halved text-purple"></i> <span id="insp-pillar">-</span></span>
                        <span class="fw-meta-chip"><i class="fas fa-user text-primary"></i> <span id="insp-owner">-</span></span>
                    </div>
                </div>

                {{-- SUMMARY --}}
                <div class="fw-section">
                    <div class="fw-section-title"><i class="fas fa-align-left text-primary"></i> Isi & Uraian Ketentuan PDP</div>
                    <div class="fw-body-text" id="insp-summary">{{ $firstReq['summary'] ?? '-' }}</div>
                </div>

                {{-- ANALOGY --}}
                <div class="fw-section">
                    <div class="fw-section-title"><i class="fas fa-lightbulb text-warning"></i> Prinsip Pemahaman & Analogi</div>
                    <div class="p-3 bg-light rounded-3 text-secondary" style="font-size: 0.88rem; line-height: 1.55;" id="insp-analogy">-</div>
                </div>

                {{-- KEY FOCUS --}}
                <div class="fw-section">
                    <div class="fw-section-title"><i class="fas fa-bullseye text-danger"></i> Fokus Utama Kepatuhan</div>
                    <ul class="fw-bullets-list" id="insp-focus"></ul>
                </div>

                {{-- EVIDENCE NEEDED --}}
                <div class="fw-section">
                    <div class="fw-section-title"><i class="fas fa-folder-check text-success"></i> Dokumen Bukti & Evidencing Audit</div>
                    <ul class="fw-bullets-list" id="insp-evidence"></ul>
                </div>

                {{-- ROLE IMPACT CARDS --}}
                <div class="fw-section">
                    <div class="fw-section-title"><i class="fas fa-users-cog text-indigo"></i> Pembagian Tanggung Jawab Organisasi</div>
                    <div class="fw-role-grid">
                        <div class="fw-role-card bod">
                            <strong><i class="fas fa-crown text-purple"></i> BoD & Pimpinan</strong>
                            <p id="insp-bod">-</p>
                        </div>
                        <div class="fw-role-card sysadmin">
                            <strong><i class="fas fa-terminal text-blue"></i> Tim IT & Enkripsi Data</strong>
                            <p id="insp-sysadmin">-</p>
                        </div>
                        <div class="fw-role-card legal">
                            <strong><i class="fas fa-user-tie text-yellow"></i> Tim Legal & DPO</strong>
                            <p id="insp-legal">-</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- TAB 2: 8 ASAS & KLASIFIKASI DATA --}}
    <div class="fw-tab-content d-none" id="tab-overview">
        <div class="row g-3">
            <div class="col-lg-7">
                <div class="card p-3 h-100 shadow-sm border-0">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <h3 class="h5 text-dark mb-0"><i class="fas fa-landmark text-purple me-2"></i> 8 Asas Pemrosesan Data Pribadi (Pasal 16)</h3>
                        <span class="badge bg-purple-lt">Prinsip Wajib</span>
                    </div>
                    <p class="text-muted small mb-3">Seluruh pengendali dan prosesor data wajib menerapkan ke-8 asas ini pada setiap siklus pemrosesan data:</p>

                    <div class="d-flex flex-column gap-2">
                        <div class="p-2 px-3 rounded-3 bg-light border">
                            <strong class="text-purple small"><i class="fas fa-check-circle me-1"></i> 1. Pengumpulan Terbatas & Spesifik</strong>
                            <p class="text-muted small mb-0 mt-1">Data dikumpulkan secara terbatas, spesifik, sah secara hukum, dan transparan.</p>
                        </div>
                        <div class="p-2 px-3 rounded-3 bg-light border">
                            <strong class="text-purple small"><i class="fas fa-check-circle me-1"></i> 2. Kesesuaian Tujuan</strong>
                            <p class="text-muted small mb-0 mt-1">Pemrosesan dilakukan sesuai dengan tujuan yang disampaikan secara eksplisit kepada subjek data.</p>
                        </div>
                        <div class="p-2 px-3 rounded-3 bg-light border">
                            <strong class="text-purple small"><i class="fas fa-check-circle me-1"></i> 3. Penjaminan Hak Subjek Data</strong>
                            <p class="text-muted small mb-0 mt-1">Menjamin pemenuhan hak subjek data termasuk hak akses, perbaikan, penarikan persetujuan, dan penghapusan.</p>
                        </div>
                        <div class="p-2 px-3 rounded-3 bg-light border">
                            <strong class="text-purple small"><i class="fas fa-check-circle me-1"></i> 4. Akurasi & Pemutakhiran</strong>
                            <p class="text-muted small mb-0 mt-1">Pemrosesan dilakukan secara akurat, lengkap, tidak menyesatkan, dan selalu terbarui.</p>
                        </div>
                        <div class="p-2 px-3 rounded-3 bg-light border">
                            <strong class="text-purple small"><i class="fas fa-check-circle me-1"></i> 5. Pelindungan Keamanan Data</strong>
                            <p class="text-muted small mb-0 mt-1">Melindungi keamanan data pribadi dari pengaksesan yang tidak sah, pengubahan, atau kebocoran.</p>
                        </div>
                        <div class="p-2 px-3 rounded-3 bg-light border">
                            <strong class="text-purple small"><i class="fas fa-check-circle me-1"></i> 6. Keterbukaan Informasi</strong>
                            <p class="text-muted small mb-0 mt-1">Menyampaikan informasi tentang tujuan pemrosesan dan kegagalan pelindungan bila terjadi.</p>
                        </div>
                        <div class="p-2 px-3 rounded-3 bg-light border">
                            <strong class="text-purple small"><i class="fas fa-check-circle me-1"></i> 7. Pembatasan Retensi (Pemusnahan)</strong>
                            <p class="text-muted small mb-0 mt-1">Memusnahkan dan/atau menghapus data setelah masa retensi berakhir atau tujuan telah tercapai.</p>
                        </div>
                        <div class="p-2 px-3 rounded-3 bg-light border">
                            <strong class="text-purple small"><i class="fas fa-check-circle me-1"></i> 8. Akuntabilitas Penuh</strong>
                            <p class="text-muted small mb-0 mt-1">Bertanggung jawab atas pemrosesan data pribadi dan membuktikan kepatuhan dalam pelaksanaan kewajiban.</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-5">
                <div class="card p-3 h-100 shadow-sm border-0">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <h3 class="h5 text-dark mb-0"><i class="fas fa-tags text-indigo me-2"></i> Klasifikasi Data Pribadi</h3>
                        <span class="badge bg-indigo-lt">Pasal 4</span>
                    </div>

                    <div class="p-3 rounded-3 bg-light border mb-3">
                        <strong class="text-dark small d-block mb-1"><i class="fas fa-id-card text-primary me-1"></i> 1. Data Pribadi Bersifat Umum</strong>
                        <p class="text-muted small mb-2">Nama lengkap, jenis kelamin, kewarganegaraan, agama, status perkawinan, dan/atau data pribadi yang dikombinasikan untuk mengidentifikasi seseorang.</p>
                        <span class="badge bg-secondary-lt">Standar Pengamanan Wajar</span>
                    </div>

                    <div class="p-3 rounded-3 bg-purple-lt border border-purple-subtle mb-3">
                        <strong class="text-purple small d-block mb-1"><i class="fas fa-shield-virus me-1"></i> 2. Data Pribadi Bersifat Spesifik (Sensitif)</strong>
                        <p class="text-muted small mb-2">Data kesehatan, data biometrik, data genetika, catatan kejahatan, data anak, data keuangan pribadi, dan data lain sesuai regulasi perundang-undangan.</p>
                        <span class="badge bg-danger text-white">Wajib Enkripsi Kuat & DPO Mandatori</span>
                    </div>

                    <div class="p-3 bg-danger-lt rounded-3 border border-danger-subtle">
                        <strong class="text-danger small d-block mb-1"><i class="fas fa-gavel me-1"></i> Sanksi Pelanggaran Administratif & Pidana</strong>
                        <span class="text-muted small">Denda administratif maksimal hingga 2% dari total pendapatan/omset tahunan. Sanksi pidana penjara hingga 6 tahun bagi pihak yang sengaja melawan hukum memperoleh atau memalsukan data pribadi.</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- TAB 3: 8 HAK SUBJEK DATA --}}
    <div class="fw-tab-content d-none" id="tab-rights">
        <div class="card p-3 shadow-sm border-0">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <div>
                    <h3 class="h5 text-dark mb-1"><i class="fas fa-user-shield text-purple me-2"></i> 8 Hak Mutlak Subjek Data Pribadi (Bab IV UU PDP)</h3>
                    <p class="text-muted small mb-0">Setiap individu berhak menuntut hak-hak ini, dan Pengendali Data wajib merespons dalam waktu yang ditentukan.</p>
                </div>
                <span class="badge bg-purple-lt fs-6">Bab IV (Pasal 5 - 15)</span>
            </div>

            <div class="row g-3">
                <div class="col-md-6 col-lg-3">
                    <div class="p-3 rounded-3 bg-light border h-100">
                        <strong class="text-purple small d-block mb-1"><i class="fas fa-info-circle me-1"></i> 1. Hak Kejelasan Informasi</strong>
                        <p class="text-muted small mb-0">Mendapatkan kejelasan identitas pihak peminta, tujuan pemrosesan, dan akuntabilitas pengendali data.</p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3">
                    <div class="p-3 rounded-3 bg-light border h-100">
                        <strong class="text-purple small d-block mb-1"><i class="fas fa-pen-to-square me-1"></i> 2. Hak Melengkapi & Mengoreksi</strong>
                        <p class="text-muted small mb-0">Melengkapi dan mengoreksi data yang tidak akurat. Pengendali wajib merespons maksimal 3x24 jam.</p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3">
                    <div class="p-3 rounded-3 bg-light border h-100">
                        <strong class="text-purple small d-block mb-1"><i class="fas fa-eye me-1"></i> 3. Hak Akses & Riwayat Data</strong>
                        <p class="text-muted small mb-0">Mendapatkan akses dan salinan data serta rekam jejak pemrosesan data miliknya secara transparan.</p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3">
                    <div class="p-3 rounded-3 bg-light border h-100">
                        <strong class="text-purple small d-block mb-1"><i class="fas fa-trash me-1"></i> 4. Hak Menghapus & Memusnahkan</strong>
                        <p class="text-muted small mb-0">Mengakhiri pemrosesan, menghapus, atau memusnahkan data pribadi sesuai ketentuan perundang-undangan.</p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3">
                    <div class="p-3 rounded-3 bg-light border h-100">
                        <strong class="text-purple small d-block mb-1"><i class="fas fa-ban me-1"></i> 5. Hak Menarik Kembali Persetujuan</strong>
                        <p class="text-muted small mb-0">Menarik kembali persetujuan (consent withdrawal) pemrosesan data yang sebelumnya telah diberikan.</p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3">
                    <div class="p-3 rounded-3 bg-light border h-100">
                        <strong class="text-purple small d-block mb-1"><i class="fas fa-hand me-1"></i> 6. Hak Mengajukan Keberatan</strong>
                        <p class="text-muted small mb-0">Menolak tindakan pengambilan keputusan yang semata-mata didasarkan pada pemrosesan otomatis (profiling).</p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3">
                    <div class="p-3 rounded-3 bg-light border h-100">
                        <strong class="text-purple small d-block mb-1"><i class="fas fa-pause me-1"></i> 7. Hak Menunda Pemrosesan</strong>
                        <p class="text-muted small mb-0">Menunda atau membatasi pemrosesan data secara proporsional sesuai tujuan penundaan.</p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3">
                    <div class="p-3 rounded-3 bg-light border h-100">
                        <strong class="text-purple small d-block mb-1"><i class="fas fa-file-export me-1"></i> 8. Hak Portabilitas Data</strong>
                        <p class="text-muted small mb-0">Mendapatkan dan/atau mentransfer data pribadi ke Pengendali Data lain dalam format yang interoperabel.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- TAB 4: DPO, DPIA & BREACH --}}
    <div class="fw-tab-content d-none" id="tab-dpo">
        <div class="row g-3">
            <div class="col-lg-4">
                <div class="card p-3 h-100 shadow-sm border-0">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <h4 class="h5 text-dark mb-0"><i class="fas fa-user-tie text-purple me-2"></i> Pejabat DPO (Pasal 53)</h4>
                        <span class="badge bg-purple-lt">Mandatori</span>
                    </div>
                    <p class="text-muted small mb-3">Wajib ditunjuk jika pemrosesan memenuhi kriteria berikut:</p>
                    <ul class="list-unstyled d-flex flex-column gap-2 small text-secondary">
                        <li class="p-2 rounded bg-light border"><i class="fas fa-check text-success me-2"></i> Pemrosesan untuk kepentingan pelayanan publik.</li>
                        <li class="p-2 rounded bg-light border"><i class="fas fa-check text-success me-2"></i> Pemrosesan berskala besar dan pemantauan sistematis.</li>
                        <li class="p-2 rounded bg-light border"><i class="fas fa-check text-success me-2"></i> Pemrosesan data spesifik/keuangan secara masif.</li>
                    </ul>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card p-3 h-100 shadow-sm border-0">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <h4 class="h5 text-dark mb-0"><i class="fas fa-clipboard-check text-primary me-2"></i> DPIA & RoPA</h4>
                        <span class="badge bg-primary-lt">Penilaian Dampak</span>
                    </div>
                    <p class="text-muted small mb-3">Kewajiban dokumentasi pemrosesan risiko tinggi:</p>
                    <ul class="list-unstyled d-flex flex-column gap-2 small text-secondary">
                        <li class="p-2 rounded bg-light border"><strong class="d-block text-dark">DPIA (Data Protection Impact Assessment):</strong> Dilakukan sebelum memulai pemrosesan yang berpotensi memiliki risiko tinggi terhadap subjek data.</li>
                        <li class="p-2 rounded bg-light border"><strong class="d-block text-dark">RoPA (Record of Processing Activities):</strong> Buku catatan seluruh aktivitas pemrosesan, pihak ketiga, retensi, dan langkah pengamanan.</li>
                    </ul>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card p-3 h-100 shadow-sm border-0">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <h4 class="h5 text-dark mb-0"><i class="fas fa-triangle-exclamation text-danger me-2"></i> Notifikasi Kebocoran 3x24 Jam</h4>
                        <span class="badge bg-danger-lt">Pasal 46</span>
                    </div>
                    <p class="text-muted small mb-3">Prosedur mandatory saat terjadi kegagalan pelindungan data pribadi (data breach):</p>
                    <div class="p-3 bg-danger-lt rounded-3 border border-danger-subtle small text-danger">
                        <strong class="d-block mb-1">Maksimal 3x24 Jam:</strong>
                        Pengendali wajib menyampaikan pemberitahuan tertulis kepada Subjek Data dan Lembaga Pengawas resmi mengenai:
                        <ul class="mb-0 mt-2 ps-3">
                            <li>Data pribadi yang terungkap</li>
                            <li>Kapan dan bagaimana kebocoran terjadi</li>
                            <li>Upaya penanganan dan mitigasi dampak</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('js/framework-reader.js') }}"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const inspId = document.getElementById('insp-id');
    const inspTitle = document.getElementById('insp-title');
    const inspChapter = document.getElementById('insp-chapter');
    const inspPillar = document.getElementById('insp-pillar');
    const inspOwner = document.getElementById('insp-owner');
    const inspSummary = document.getElementById('insp-summary');
    const inspAnalogy = document.getElementById('insp-analogy');
    const inspFocus = document.getElementById('insp-focus');
    const inspEvidence = document.getElementById('insp-evidence');
    const inspBod = document.getElementById('insp-bod');
    const inspSysadmin = document.getElementById('insp-sysadmin');
    const inspLegal = document.getElementById('insp-legal');

    new FrameworkReader({
        onItemSelect: function(el) {
            if (inspId) inspId.textContent = el.dataset.id || '-';
            if (inspTitle) inspTitle.textContent = el.dataset.title || 'Pilih Ketentuan';
            if (inspChapter) inspChapter.textContent = el.dataset.chapter || '-';
            if (inspPillar) inspPillar.textContent = el.dataset.pillar || '-';
            if (inspOwner) inspOwner.textContent = el.dataset.owner || '-';
            if (inspSummary) inspSummary.textContent = el.dataset.summary || '-';
            if (inspAnalogy) inspAnalogy.textContent = el.dataset.analogy || 'Patuhi tata kelola privasi sesuai standar UU PDP.';

            // Focus
            if (inspFocus) {
                inspFocus.innerHTML = '';
                try {
                    const focusArr = JSON.parse(el.dataset.focus || '[]');
                    if (focusArr.length === 0) {
                        inspFocus.innerHTML = '<li class="text-muted">Tidak ada fokus spesifik dicatat.</li>';
                    } else {
                        focusArr.forEach(f => {
                            const li = document.createElement('li');
                            li.textContent = f;
                            inspFocus.appendChild(li);
                        });
                    }
                } catch(e) {}
            }

            // Evidence
            if (inspEvidence) {
                inspEvidence.innerHTML = '';
                try {
                    const evArr = JSON.parse(el.dataset.evidence || '[]');
                    if (evArr.length === 0) {
                        inspEvidence.innerHTML = '<li class="text-muted">SOP Privasi, Dokumen Persetujuan, atau Laporan Audit.</li>';
                    } else {
                        evArr.forEach(ev => {
                            const li = document.createElement('li');
                            li.textContent = ev;
                            inspEvidence.appendChild(li);
                        });
                    }
                } catch(e) {}
            }

            // Roles
            if (inspBod) inspBod.textContent = el.dataset.bod || 'Penetapan kebijakan privasi dan penunjukan DPO organisasi.';
            if (inspSysadmin) inspSysadmin.textContent = el.dataset.sysadmin || 'Implementasi enkripsi data at-rest & in-transit, masking, serta kontrol akses granular.';
            if (inspLegal) inspLegal.textContent = el.dataset.legal || 'Penyusunan Data Processing Agreement (DPA), penanganan DSR, dan notifikasi regulator.';
        }
    });
});
</script>
@endpush
