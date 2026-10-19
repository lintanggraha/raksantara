@extends('layouts.tabler')

@section('title', 'POJK 11/POJK.03/2022 Penyelenggaraan TI Bank Umum - Raksantara')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/framework-reader.css') }}">
@endpush

@section('content')
@php
    $reqCollection = collect($requirements);
    $refCollection = collect($references);
    $totalReqs = $reqCollection->count();
    $totalRefs = $refCollection->count();
    $chapters = $reqCollection->pluck('chapter_title', 'chapter')->unique();
@endphp

<div class="fw-page">
    {{-- COMPACT MODERN HERO --}}
    <div class="fw-hero">
        <div class="fw-hero-header">
            <div class="fw-kicker">
                <i class="fas fa-landmark"></i> Regulasi Otoritas Jasa Keuangan
            </div>
            <span class="badge bg-success-lt text-success fw-bold px-3 py-1 rounded-pill">Status: Aktif Berlaku</span>
        </div>
        <h1 class="fw-title">POJK 11/POJK.03/2022</h1>
        <p class="fw-lede">
            Peraturan Otoritas Jasa Keuangan tentang Penyelenggaraan Teknologi Informasi oleh Bank Umum. Menetapkan 7 pilar tata kelola TI, manajemen risiko terintegrasi, ketahanan operasional, pelindungan data nasabah, dan kepatuhan penyedia jasa TI (PPJTI).
        </p>
        <div class="fw-metric-row">
            <div class="fw-metric-pill">
                <span>Total Ketentuan:</span>
                <strong>{{ $totalReqs }} Pasal</strong>
            </div>
            <div class="fw-metric-pill">
                <span>Dokumen Rujukan:</span>
                <strong>{{ $totalRefs }} Lampiran</strong>
            </div>
            <div class="fw-metric-pill">
                <span>Pilar Tata Kelola:</span>
                <strong>7 Aspek Wajib</strong>
            </div>
            <div class="fw-metric-pill">
                <span>SLA Patching Mandatori:</span>
                <strong>Pasal 28 & PADK 1</strong>
            </div>
        </div>
    </div>

    {{-- NAVIGATION TABS --}}
    <div class="fw-nav-container">
        <ul class="fw-nav">
            <li>
                <button class="fw-tab active" data-tab="tab-explorer">
                    <i class="fas fa-list-check"></i>
                    <span>Eksplorasi Pasal & Ketentuan ({{ $totalReqs }})</span>
                </button>
            </li>
            <li>
                <button class="fw-tab" data-tab="tab-overview">
                    <i class="fas fa-cubes"></i>
                    <span>7 Aspek Tata Kelola & Struktur Bab</span>
                </button>
            </li>
            <li>
                <button class="fw-tab" data-tab="tab-references">
                    <i class="fas fa-book-open"></i>
                    <span>Dokumen & Template Rujukan ({{ $totalRefs }})</span>
                </button>
            </li>
            <li>
                <button class="fw-tab" data-tab="tab-perspectives">
                    <i class="fas fa-users-cog"></i>
                    <span>Panduan Peran (BoD, IT, Compliance)</span>
                </button>
            </li>
        </ul>
    </div>

    {{-- TAB 1: CLAUSE EXPLORER & MASTER-DETAIL (ACTIVE DEFAULT) --}}
    <div class="fw-tab-content" id="tab-explorer">
        <div class="fw-workspace">
            {{-- LEFT COLUMN: CLAUSE NAVIGATOR --}}
            <div class="fw-list-panel">
                <div class="fw-search-box">
                    <div class="fw-search-input-wrap">
                        <i class="fas fa-search"></i>
                        <input
                            type="text"
                            id="fw-search-input"
                            class="fw-search-input"
                            placeholder="Cari nomor pasal, kata kunci, topik..."
                            autocomplete="off"
                        >
                        <span class="fw-search-kbd">/</span>
                    </div>
                    <select id="fw-filter-select" class="fw-filter-select">
                        <option value="">Semua Bab ({{ $chapters->count() }} Bab)</option>
                        @foreach($chapters as $chapKey => $chapTitle)
                            <option value="{{ $chapKey }}">Bab {{ $chapKey }}: {{ Str::limit($chapTitle, 32) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="fw-counter-bar">
                    <span id="fw-counter">{{ $totalReqs }} dari {{ $totalReqs }} pasal ditampilkan</span>
                    <span class="text-muted small"><i class="fas fa-arrows-alt-v me-1"></i> Gunakan ↑/↓ keyboard</span>
                </div>

                <div class="fw-list" id="fw-items-container">
                    @foreach($requirements as $index => $req)
                        @php
                            $legalRef = $req['legal_basis'] ?? $req['legal_ref'] ?? $req['id'] ?? '';
                            $owner = $req['owner'] ?? $req['risk_owner'] ?? 'Direksi';
                            $focus = $req['focus'] ?? $req['key_focus'] ?? [];
                            $evidence = $req['evidence'] ?? $req['evidence_needed'] ?? [];
                            $bod = $req['roleTranslations']['bod'] ?? $req['role_impact']['bod_boc'] ?? '';
                            $sysadmin = $req['roleTranslations']['sysadmin'] ?? $req['role_impact']['sysadmin_security'] ?? '';
                            $legalrole = $req['roleTranslations']['legal'] ?? $req['role_impact']['compliance_legal'] ?? '';
                            $searchIndex = strtolower(($req['id'] ?? '') . ' ' . $legalRef . ' ' . ($req['title'] ?? '') . ' ' . ($req['summary'] ?? '') . ' ' . ($req['chapter_title'] ?? ''));
                        @endphp
                        <button
                            type="button"
                            class="fw-item {{ $index === 0 ? 'active' : '' }}"
                            data-group="{{ $req['chapter'] ?? '' }}"
                            data-search-index="{{ $searchIndex }}"
                            data-id="{{ $req['id'] ?? '' }}"
                            data-legal="{{ $legalRef }}"
                            data-title="{{ $req['title'] ?? '' }}"
                            data-summary="{{ $req['summary'] ?? '' }}"
                            data-owner="{{ $owner }}"
                            data-cadence="{{ $req['cadence'] ?? 'Berkala' }}"
                            data-analogy="{{ $req['analogy'] ?? '' }}"
                            data-chapter="{{ $req['chapter'] ?? '' }}"
                            data-chapter-title="{{ $req['chapter_title'] ?? '' }}"
                            data-focus='@json($focus)'
                            data-evidence='@json($evidence)'
                            data-bod="{{ $bod }}"
                            data-sysadmin="{{ $sysadmin }}"
                            data-legalrole="{{ $legalrole }}"
                        >
                            <div class="fw-item-top">
                                <span class="fw-item-code">{{ $legalRef ?: $req['id'] }}</span>
                                <span class="fw-item-tag">Bab {{ $req['chapter'] ?? '-' }}</span>
                            </div>
                            <div class="fw-item-title">{{ $req['title'] ?? '' }}</div>
                            <div class="fw-item-desc">{{ Str::limit($req['summary'] ?? '', 95) }}</div>
                        </button>
                    @endforeach
                </div>
            </div>

            {{-- RIGHT COLUMN: INSPECTOR & DEEP READER --}}
            <div class="fw-inspector">
                <div class="fw-inspector-head">
                    <div class="fw-inspector-actions">
                        <span class="fw-badge-legal" id="insp-legal">Pasal</span>
                        <div class="d-flex align-items-center gap-2">
                            <button type="button" class="btn btn-sm btn-outline-primary" data-copy-target="insp-summary" title="Salin ringkasan pasal">
                                <i class="fas fa-copy me-1"></i> Salin Teks
                            </button>
                            <a href="{{ route('analysis.cross-mapping') }}" class="btn btn-sm btn-outline-secondary" title="Buka pemetaan regulasi silang">
                                <i class="fas fa-network-wired me-1"></i> Cross-Mapping
                            </a>
                        </div>
                    </div>
                    <h2 class="fw-inspector-title" id="insp-title">Pilih Pasal</h2>
                    <div class="fw-meta-chips">
                        <span class="fw-meta-chip"><i class="fas fa-book text-muted"></i> <span id="insp-chapter">Bab</span></span>
                        <span class="fw-meta-chip"><i class="fas fa-user-shield text-muted"></i> <span id="insp-owner">Owner</span></span>
                        <span class="fw-meta-chip"><i class="fas fa-clock text-muted"></i> <span id="insp-cadence">Frekuensi</span></span>
                        <span class="fw-meta-chip"><i class="fas fa-hashtag text-muted"></i> <span id="insp-id">ID</span></span>
                    </div>
                </div>

                {{-- CLAUSE SUMMARY --}}
                <div class="fw-section">
                    <div class="fw-section-title"><i class="fas fa-align-left text-primary"></i> Isi & Uraian Ketentuan</div>
                    <div class="fw-body-text" id="insp-summary">-</div>
                </div>

                {{-- ANALOGY / PRINSIP KERJA --}}
                <div class="fw-section">
                    <div class="fw-section-title"><i class="fas fa-lightbulb text-warning"></i> Prinsip Tata Kelola & Analogi Operasional</div>
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
                    <div class="fw-section-title"><i class="fas fa-users-cog text-indigo"></i> Pembagian Tanggung Jawab Peran</div>
                    <div class="fw-role-grid">
                        <div class="fw-role-card bod">
                            <strong><i class="fas fa-crown text-purple"></i> Direksi & Dewan</strong>
                            <p id="insp-bod">-</p>
                        </div>
                        <div class="fw-role-card sysadmin">
                            <strong><i class="fas fa-terminal text-blue"></i> Tim IT & Keamanan</strong>
                            <p id="insp-sysadmin">-</p>
                        </div>
                        <div class="fw-role-card legal">
                            <strong><i class="fas fa-scale-balanced text-yellow"></i> Legal & Compliance</strong>
                            <p id="insp-legalrole">-</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- TAB 2: OVERVIEW & 7 ASPEK --}}
    <div class="fw-tab-content d-none" id="tab-overview">
        <div class="row g-3">
            <div class="col-lg-7">
                <div class="card p-3 h-100 shadow-sm border-0">
                    <h3 class="h4 text-dark mb-2"><i class="fas fa-cubes text-primary me-2"></i> 7 Aspek Wajib Tata Kelola TI Bank (Pasal 3)</h3>
                    <p class="text-muted small mb-3">Setiap Bank Umum wajib memetakan, mengimplementasikan, dan menguji ketujuh pilar ini secara terintegrasi:</p>
                    
                    <div class="d-flex flex-column gap-2">
                        <div class="p-3 rounded-3 bg-light border">
                            <strong class="text-primary"><i class="fas fa-project-diagram me-1"></i> 1. Proses Bisnis</strong>
                            <p class="small text-muted mb-0 mt-1">Penyelarasan alur operasional bank dengan arsitektur TI dan manajemen risiko proses.</p>
                        </div>
                        <div class="p-3 rounded-3 bg-light border">
                            <strong class="text-primary"><i class="fas fa-sitemap me-1"></i> 2. Struktur Organisasi</strong>
                            <p class="small text-muted mb-0 mt-1">Segregation of duties antara pengembang, operasional, dan pengawas keamanan siber serta komite TI.</p>
                        </div>
                        <div class="p-3 rounded-3 bg-light border">
                            <strong class="text-primary"><i class="fas fa-file-contract me-1"></i> 3. Kebijakan, Standar, & Prosedur</strong>
                            <p class="small text-muted mb-0 mt-1">Siklus pengkinian berkala (Kebijakan ≤5 tahun, Standar ≤2 tahun, SOP ≤1 tahun).</p>
                        </div>
                        <div class="p-3 rounded-3 bg-light border">
                            <strong class="text-primary"><i class="fas fa-network-wired me-1"></i> 4. Kebutuhan & Alur Informasi (SIM)</strong>
                            <p class="small text-muted mb-0 mt-1">Sistem Informasi Manajemen yang menyuplai data akurat, tepat waktu, dan terlindungi.</p>
                        </div>
                        <div class="p-3 rounded-3 bg-light border">
                            <strong class="text-primary"><i class="fas fa-user-graduate me-1"></i> 5. Sumber Daya Manusia</strong>
                            <p class="small text-muted mb-0 mt-1">Kecukupan kuantitas dan sertifikasi kompetensi keahlian TI & keamanan siber.</p>
                        </div>
                        <div class="p-3 rounded-3 bg-light border">
                            <strong class="text-primary"><i class="fas fa-brain me-1"></i> 6. Budaya TI</strong>
                            <p class="small text-muted mb-0 mt-1">Peningkatan risk awareness, budaya inovasi, dan kepemimpinan berorientasi digital.</p>
                        </div>
                        <div class="p-3 rounded-3 bg-light border">
                            <strong class="text-primary"><i class="fas fa-server me-1"></i> 7. Infrastruktur & Aplikasi</strong>
                            <p class="small text-muted mb-0 mt-1">Ketahanan Pusat Data (DC), DRC redundan, hardening, patch management, dan SDLC terstruktur.</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-5">
                <div class="card p-3 h-100 shadow-sm border-0">
                    <h3 class="h4 text-dark mb-2"><i class="fas fa-book text-indigo me-2"></i> Struktur Bab Regulasi</h3>
                    <p class="text-muted small mb-3">Distribusi ketentuan berdasarkan bab hukum:</p>
                    <div class="d-flex flex-column gap-2">
                        @foreach($chapters as $chapKey => $chapTitle)
                            @php
                                $cnt = $reqCollection->where('chapter', $chapKey)->count();
                            @endphp
                            <div class="d-flex align-items-center justify-content-between p-2 rounded-2 bg-light border">
                                <div>
                                    <strong class="small d-block text-dark">Bab {{ $chapKey }}</strong>
                                    <span class="text-muted" style="font-size: 0.76rem;">{{ $chapTitle }}</span>
                                </div>
                                <span class="badge bg-primary-lt">{{ $cnt }} Ketentuan</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- TAB 3: REFERENCES & TEMPLATES --}}
    <div class="fw-tab-content d-none" id="tab-references">
        <div class="row g-3">
            @foreach($references as $ref)
                <div class="col-md-6">
                    <div class="card p-3 shadow-sm border-0 h-100">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <span class="badge bg-indigo-lt fw-bold font-monospace">{{ $ref['id'] }}</span>
                            <span class="text-muted small">{{ $ref['legal_basis'] ?? 'POJK 11/2022' }}</span>
                        </div>
                        <h4 class="h5 text-dark mb-1">{{ $ref['title'] }}</h4>
                        <p class="text-muted small mb-3">{{ $ref['summary'] ?? '' }}</p>

                        @if(!empty($ref['deliverables']))
                            <div class="mb-2">
                                <strong class="small d-block text-secondary mb-1">Output / Deliverables Wajib:</strong>
                                <ul class="small ps-3 mb-0 text-muted">
                                    @foreach($ref['deliverables'] as $deliv)
                                        <li>{{ $deliv }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    {{-- TAB 4: PERSPECTIVES --}}
    <div class="fw-tab-content d-none" id="tab-perspectives">
        <div class="row g-3">
            <div class="col-md-4">
                <div class="card p-3 shadow-sm border-0 border-top border-4 border-purple h-100">
                    <h4 class="h5 text-purple mb-2"><i class="fas fa-crown me-2"></i> Dewan Pengurus (BoD & BoC)</h4>
                    <p class="text-muted small">Fokus kepatuhan strategis bagi Direksi dan Dewan Komisaris Bank:</p>
                    <ul class="small text-muted ps-3 mb-0">
                        <li class="mb-2">Membentuk Komite Pengarah TI dan meninjau rekomendasi triwulanan.</li>
                        <li class="mb-2">Menetapkan dan mengesahkan Rencana Strategis TI (RSTI) selaras rencana korporasi.</li>
                        <li class="mb-2">Memastikan kecukupan alokasi capex/opex keamanan dan pemulihan bencana.</li>
                        <li class="mb-2">Menyetujui kerja sama strategis dengan Pihak Penyedia Jasa TI (PPJTI).</li>
                        <li>Memastikan laporan berkala kondisi TI dan insiden disampaikan tepat waktu ke OJK.</li>
                    </ul>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card p-3 shadow-sm border-0 border-top border-4 border-primary h-100">
                    <h4 class="h5 text-primary mb-2"><i class="fas fa-terminal me-2"></i> Tim Operasional & SysAdmin</h4>
                    <p class="text-muted small">Fokus operasional teknis untuk administrator dan insinyur keamanan:</p>
                    <ul class="small text-muted ps-3 mb-0">
                        <li class="mb-2">Menerapkan segregation of duties & kontrol hak akses istimewa (PAM/MFA).</li>
                        <li class="mb-2">Memelihara konfigurasi DC/DRC, redundansi jaringan, serta jadwal backup onsite/offsite.</li>
                        <li class="mb-2">Menjalankan uji berkala DRP minimal 1x setahun dengan skenario kegagalan total.</li>
                        <li class="mb-2">Mengelola inventaris aset TI dan baseline hardening untuk endpoint & server.</li>
                        <li>Menyimpan audit trail dan log transaksi tersentralisasi tanpa risiko manipulasi.</li>
                    </ul>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card p-3 shadow-sm border-0 border-top border-4 border-warning h-100">
                    <h4 class="h5 text-warning text-dark mb-2"><i class="fas fa-scale-balanced me-2"></i> Legal & Compliance</h4>
                    <p class="text-muted small">Fokus regulasi dan perjanjian hukum bagi fungsi kepatuhan:</p>
                    <ul class="small text-muted ps-3 mb-0">
                        <li class="mb-2">Menjamin klausul mandatory PPJTI mencakup audit rights OJK dan perlindungan data nasabah.</li>
                        <li class="mb-2">Menyiapkan permohonan izin penempatan sistem atau pemrosesan luar negeri ke OJK.</li>
                        <li class="mb-2">Mengawal kepatuhan tenggat LKTPTI (21 Januari) dan pelaporan insiden nonsiber 24 jam.</li>
                        <li class="mb-2">Memperbarui regulatory register dan menyelaraskan SOP internal dengan POJK 11/2022.</li>
                        <li>Mendampingi audit intern TI dan memfasilitasi tindak lanjut rekomendasi audit.</li>
                    </ul>
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
    // Inspector Elements
    const inspId = document.getElementById('insp-id');
    const inspLegal = document.getElementById('insp-legal');
    const inspTitle = document.getElementById('insp-title');
    const inspSummary = document.getElementById('insp-summary');
    const inspChapter = document.getElementById('insp-chapter');
    const inspOwner = document.getElementById('insp-owner');
    const inspCadence = document.getElementById('insp-cadence');
    const inspAnalogy = document.getElementById('insp-analogy');
    const inspFocus = document.getElementById('insp-focus');
    const inspEvidence = document.getElementById('insp-evidence');
    const inspBod = document.getElementById('insp-bod');
    const inspSysadmin = document.getElementById('insp-sysadmin');
    const inspLegalrole = document.getElementById('insp-legalrole');

    // Initialize FrameworkReader Engine
    new FrameworkReader({
        itemSelector: '.fw-item',
        searchInputId: 'fw-search-input',
        filterSelectId: 'fw-filter-select',
        counterId: 'fw-counter',
        onSelect: function (el) {
            if (inspId) inspId.textContent = el.dataset.id || '-';
            if (inspLegal) inspLegal.textContent = el.dataset.legal || 'Pasal Umum';
            if (inspTitle) inspTitle.textContent = el.dataset.title || '';
            if (inspSummary) inspSummary.textContent = el.dataset.summary || '-';
            if (inspChapter) inspChapter.textContent = el.dataset.chapterTitle ? `Bab ${el.dataset.chapter}: ${el.dataset.chapterTitle}` : `Bab ${el.dataset.chapter}`;
            if (inspOwner) inspOwner.textContent = el.dataset.owner || 'Direksi';
            if (inspCadence) inspCadence.textContent = el.dataset.cadence || 'Berkala';
            if (inspAnalogy) inspAnalogy.textContent = el.dataset.analogy || 'Terapkan prinsip kehati-hatian dan kepatuhan dalam seluruh proses perbankan.';

            // Render Focus list
            if (inspFocus) {
                inspFocus.innerHTML = '';
                try {
                    const focusArr = JSON.parse(el.dataset.focus || '[]');
                    focusArr.forEach(f => {
                        const li = document.createElement('li');
                        li.textContent = f;
                        inspFocus.appendChild(li);
                    });
                } catch(e) {}
            }

            // Render Evidence list
            if (inspEvidence) {
                inspEvidence.innerHTML = '';
                try {
                    const evArr = JSON.parse(el.dataset.evidence || '[]');
                    evArr.forEach(ev => {
                        const li = document.createElement('li');
                        li.textContent = ev;
                        inspEvidence.appendChild(li);
                    });
                } catch(e) {}
            }

            // Render Role impacts
            if (inspBod) inspBod.textContent = el.dataset.bod || 'Tinjau kerangka kerja serta pantau pemenuhan melalui komite.';
            if (inspSysadmin) inspSysadmin.textContent = el.dataset.sysadmin || 'Konfigurasikan sistem dan dokumentasikan log kepatuhan.';
            if (inspLegalrole) inspLegalrole.textContent = el.dataset.legalrole || 'Pastikan klausul kontrak dan regulasi terkait telah terpenuhi.';
        }
    });
});
</script>
@endpush
