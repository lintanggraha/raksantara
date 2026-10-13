@extends('layouts.tabler')

@section('title', 'User Guide - Raksantara')

@push('styles')
<style>
    .guide-card {
        border: 1px solid #dbe4ef;
        border-radius: 16px;
        box-shadow: 0 10px 24px rgba(15, 23, 42, 0.06);
    }
    .guide-step-tabs {
        display: flex;
        flex-wrap: wrap;
        gap: 0.2rem 0.35rem;
        border-bottom: 1px solid #dee2e6;
        margin-bottom: 1rem;
    }
    .guide-step-tabs .nav-item {
        margin-bottom: -1px;
    }
    .guide-step-tabs .nav-link {
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        border: 1px solid transparent;
        border-top-left-radius: 0.35rem;
        border-top-right-radius: 0.35rem;
        color: #495057;
        font-weight: 700;
        padding: 0.65rem 0.95rem;
        background: transparent;
        white-space: nowrap;
    }
    .guide-step-tabs .nav-link i {
        margin-right: 0;
        line-height: 1;
    }
    .guide-step-tabs .nav-link:hover,
    .guide-step-tabs .nav-link:focus {
        color: #0b57d0;
    }
    .guide-step-tabs .nav-link.active {
        color: #212529;
        background: #ffffff;
        border-color: #dee2e6 #dee2e6 #ffffff;
        border-bottom: 3px solid #007bff;
        font-weight: 700;
    }
    .guide-pane-title {
        font-size: 1.04rem;
        font-weight: 700;
        color: #1f2937;
        margin-bottom: 0.8rem;
    }
    .guide-section-title {
        font-size: 0.84rem;
        text-transform: uppercase;
        letter-spacing: 0.02em;
        color: #64748b;
        font-weight: 700;
        margin-top: 1rem;
        margin-bottom: 0.45rem;
    }
    .guide-feature {
        border-top: 1px solid #e5edf6;
        margin-top: 1.25rem;
        padding-top: 1.1rem;
    }
    .guide-feature:first-child {
        border-top: 0;
        margin-top: 0;
        padding-top: 0;
    }
    .guide-feature-title {
        font-size: 1rem;
        font-weight: 700;
        color: #0f172a;
        margin-bottom: 0.25rem;
    }
    .guide-feature-kicker {
        display: inline-flex;
        align-items: center;
        gap: 0.38rem;
        font-size: 0.74rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.03em;
        color: #64748b;
        margin-bottom: 0.2rem;
    }
    .guide-feature-route {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        border-radius: 999px;
        padding: 0.22rem 0.62rem;
        background: #eef4ff;
        color: #1d4ed8;
        font-size: 0.74rem;
        font-weight: 700;
        margin-bottom: 0.55rem;
    }
    .guide-note {
        background: #f7faff;
        border: 1px solid #dce7f8;
        border-radius: 10px;
        padding: 0.8rem 0.95rem;
        color: #1f3a5f;
        margin-top: 1rem;
    }
    .guide-mini-note {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        padding: 0.75rem 0.9rem;
        color: #334155;
        margin-bottom: 0.85rem;
        font-size: 0.86rem;
    }
    .guide-table th,
    .guide-table td {
        vertical-align: top;
        font-size: 0.86rem;
    }
    .guide-table th {
        white-space: nowrap;
    }
    .guide-table ul,
    .guide-table ol {
        margin-bottom: 0;
        padding-left: 1.05rem;
    }
    .guide-table li + li {
        margin-top: 0.2rem;
    }
    .guide-chip {
        display: inline-flex;
        align-items: center;
        border-radius: 999px;
        padding: 0.22rem 0.58rem;
        font-size: 0.72rem;
        font-weight: 700;
        line-height: 1.2;
    }
    .guide-chip.role {
        background: #e0f2fe;
        color: #075985;
    }
    .guide-chip.info {
        background: #e2e8f0;
        color: #1e293b;
    }
    .guide-pill-row {
        display: flex;
        flex-wrap: wrap;
        gap: 0.45rem;
        margin-top: 0.7rem;
    }
    .guide-flow {
        margin: 0;
        padding-left: 1.1rem;
    }
    .guide-flow li + li {
        margin-top: 0.28rem;
    }
    .guide-tight {
        margin-bottom: 0.75rem;
    }
    @media (max-width: 767.98px) {
        .guide-step-tabs .nav-link {
            padding: 0.55rem 0.75rem;
            font-size: 0.88rem;
        }
        .guide-step-tabs .nav-link i {
            display: none;
        }
    }
</style>
@endpush

@section('content_header')
<div class="row align-items-center">
    <div class="col">
        <h2 class="page-title mb-1">User Guide</h2>
        <div class="text-muted">
            Panduan ini menjelaskan fitur aktif per role sampai level label, box, filter, modal, dan tombol yang tampil di aplikasi sekarang.
        </div>
    </div>
</div>
@endsection

@section('content')
@php
    $viewer = auth()->user();
    $viewerName = $viewer->username ?: $viewer->name;
    $isSuperadmin = $viewer->isSuperadmin();
    $isCompliance = $viewer->isComplianceDivision();
    $isOperational = $viewer->isDivisionVerified() && !$isSuperadmin;
    $showThreatIntel = $isOperational || $isSuperadmin;
    $showComplianceAudit = $isCompliance && !$isSuperadmin;
    $showOjkMitigation = $showComplianceAudit && config('features.show_ojk_mitigation', false);
    $roleLabel = $isSuperadmin ? 'superadmin' : ($isCompliance ? 'Compliance' : 'verified user');

    $activeMenuChips = ['User Guide'];
    if ($isSuperadmin) {
        $activeMenuChips = array_merge($activeMenuChips, ['Dashboard', 'App Management', 'Status Approvals', 'Alerts', 'Audit Trail', 'Threat Intel']);
    }
    if ($isOperational) {
        $activeMenuChips = array_merge($activeMenuChips, ['Risk', 'Third-Party Vendors', 'Threat Intel']);
    }
    if ($showComplianceAudit) {
        $activeMenuChips = array_merge($activeMenuChips, ['Audit', 'Compliance']);
    }
    if ($showOjkMitigation) {
        $activeMenuChips[] = 'Mitigasi OJK';
    }
    $activeMenuChips = array_values(array_unique($activeMenuChips));

    $guideTabs = array_values(array_filter([
        ['key' => 'start', 'icon' => 'fas fa-compass', 'label' => 'Mulai', 'visible' => true],
        ['key' => 'role', 'icon' => 'fas fa-users-cog', 'label' => 'Role & Akses', 'visible' => true],
        ['key' => 'risk', 'icon' => 'fas fa-exclamation-triangle', 'label' => 'Risk Workspace', 'visible' => $isOperational],
        ['key' => 'intel', 'icon' => 'fas fa-globe', 'label' => $isOperational ? 'Incident & Threat' : 'Threat Intel', 'visible' => $showThreatIntel],
        ['key' => 'compliance', 'icon' => 'fas fa-briefcase', 'label' => 'Audit & Compliance', 'visible' => $showComplianceAudit],
        ['key' => 'superadmin', 'icon' => 'fas fa-shield-alt', 'label' => 'Superadmin', 'visible' => $isSuperadmin],
        ['key' => 'account', 'icon' => 'fas fa-user-lock', 'label' => 'Akun & Keamanan', 'visible' => true],
    ], fn ($tab) => $tab['visible']));
@endphp

<div class="card guide-card">
    <div class="card-body">
        <ul class="nav nav-tabs guide-step-tabs" id="userGuideTab" role="tablist">
            @foreach($guideTabs as $index => $tab)
                <li class="nav-item" role="presentation">
                    <button
                        class="nav-link {{ $index === 0 ? 'active' : '' }}"
                        id="guide-{{ $tab['key'] }}-tab"
                        data-bs-toggle="tab"
                        data-bs-target="#guide-{{ $tab['key'] }}-pane"
                        type="button"
                        role="tab"
                        aria-controls="guide-{{ $tab['key'] }}-pane"
                        aria-selected="{{ $index === 0 ? 'true' : 'false' }}"
                    >
                        <i class="{{ $tab['icon'] }}"></i>
                        <span>{{ $tab['label'] }}</span>
                    </button>
                </li>
            @endforeach
        </ul>

        <div class="tab-content" id="userGuideTabContent">
            <div class="tab-pane fade show active" id="guide-start-pane" role="tabpanel" aria-labelledby="guide-start-tab">
                <div class="guide-pane-title">Ringkasan Akses Aktif</div>
                <p class="mb-2">
                    Anda login sebagai <strong>{{ $viewerName }}</strong> dengan role <strong>{{ $roleLabel }}</strong>.
                    Panduan ini sengaja hanya membahas menu yang memang aktif untuk akun sekarang, dan setiap bagian memakai nama label serta tombol yang sama dengan layar aplikasi.
                </p>

                <div class="guide-pill-row">
                    @foreach($activeMenuChips as $chip)
                        <span class="guide-chip info">{{ $chip }}</span>
                    @endforeach
                </div>

                <div class="guide-section-title">Cara membaca panduan ini</div>
                <div class="table-responsive guide-tight">
                    <table class="table table-sm table-bordered guide-table">
                        <thead>
                            <tr>
                                <th>Istilah di panduan</th>
                                <th>Artinya di layar</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><strong>Box / card / summary</strong></td>
                                <td>Area informasi yang hanya dibaca, misalnya KPI card, summary card, preview, atau chart header.</td>
                            </tr>
                            <tr>
                                <td><strong>Label / field</strong></td>
                                <td>Input yang harus dipilih atau diisi user, misalnya dropdown, textarea, date picker, dan switch.</td>
                            </tr>
                            <tr>
                                <td><strong>Tombol / action</strong></td>
                                <td>Kontrol yang memindahkan halaman, menyimpan data, membuka modal, mengubah status, atau menjalankan export/import.</td>
                            </tr>
                            <tr>
                                <td><strong>Modal / tab</strong></td>
                                <td>Jendela pop-up atau panel bertab yang muncul untuk mempercepat input tanpa pindah terlalu jauh dari halaman utama.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="guide-section-title">Mulai dari menu mana</div>
                <div class="table-responsive">
                    <table class="table table-sm table-bordered guide-table">
                        <thead>
                            <tr>
                                <th>Kalau Anda ingin...</th>
                                <th>Buka menu</th>
                                <th>Hasilnya</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if($isSuperadmin)
                                <tr><td>Mengelola user, parameter, dan framework appetite</td><td><strong>App Management</strong></td><td>Masuk ke <strong>User Management</strong>, <strong>Parameter</strong>, dan <strong>Corporate Risk Appetite</strong> dari satu dropdown.</td></tr>
                                <tr><td>Review request perubahan status risk</td><td><strong>Status Approvals</strong></td><td>Melihat alasan request, memberi review note, lalu <strong>Approve</strong> atau <strong>Reject</strong>.</td></tr>
                                <tr><td>Memantau alert dan jejak perubahan sistem</td><td><strong>Alerts</strong> dan <strong>Audit Trail</strong></td><td>Melihat alert open/resolved serta aktivitas user yang tercatat oleh sistem.</td></tr>
                                <tr><td>Meninjau ancaman eksternal</td><td><strong>Threat Intel</strong></td><td>Membaca CVE feed, KEV overlap, source health, priority queue, dan analyst console.</td></tr>
                            @endif
                            @if($isOperational)
                                <tr><td>Melihat prioritas portfolio risiko secara cepat</td><td><strong>Risk &gt; Risk Monitoring</strong></td><td>Dashboard 1-3, KPI, benchmarking, status matrix, dan root cause analysis.</td></tr>
                                <tr><td>Mencari, membuat, atau membuka detail risk</td><td><strong>Risk &gt; Risk Register</strong></td><td>Workspace utama untuk <strong>Add Risk</strong>, <strong>Import XLS</strong>, filter owner, heat map, recap, dan detail risk.</td></tr>
                                <tr><td>Mengelola action mitigasi dan indikator peringatan dini</td><td><strong>Risk &gt; Key Risk Indicators</strong> atau <strong>Risk &gt; Mitigation Kanban</strong></td><td>Mengatur threshold KRI, melihat card treatment, blocked reason, evidence, dan SLA.</td></tr>
                                <tr><td>Mengelola vendor dan BCM</td><td><strong>Third-Party Vendors</strong> atau <strong>Risk &gt; Business Continuity (BCM)</strong></td><td>Mencatat detail vendor, BIA, BCP, RTO, RPO, MAO, dan testing record.</td></tr>
                            @endif
                            @if($showThreatIntel && !$isSuperadmin)
                                <tr><td>Mencatat kejadian operasional dan mengaitkannya ke risk</td><td><strong>Risk &gt; Incident Management</strong></td><td>Membuat incident, severity, handling status, mitigation verdict, root cause, dan resolution notes.</td></tr>
                            @endif
                            @if($showComplianceAudit)
                                <tr><td>Mengelola audit dan program kerja compliance</td><td><strong>Audit</strong> / <strong>Compliance</strong></td><td>Memakai dashboard audit, import XLS, export, dan CRUD program kerja lintas section.</td></tr>
                            @endif
                        </tbody>
                    </table>
                </div>

                <div class="guide-note">
                    Kalau menu tidak muncul di tab ini, artinya menu tersebut memang belum aktif untuk role Anda, sedang di luar scope akun Anda, atau feature flag-nya sedang nonaktif.
                </div>
            </div>

            <div class="tab-pane fade" id="guide-role-pane" role="tabpanel" aria-labelledby="guide-role-tab">
                <div class="guide-pane-title">Role, Scope, dan Hak Tulis</div>
                <div class="table-responsive guide-tight">
                    <table class="table table-sm table-bordered guide-table">
                        <thead>
                            <tr>
                                <th>Role</th>
                                <th>Menu aktif</th>
                                <th>Scope data</th>
                                <th>Hak tulis utama</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr><td><span class="guide-chip role">Superadmin</span></td><td>Dashboard, App Management, Status Approvals, Alerts, Audit Trail, Threat Intel, User Guide</td><td>Governance level</td><td>Administrasi user, parameter, appetite, approval, alert, dan audit trail. Workflow risk harian bukan workspace utama superadmin.</td></tr>
                            <tr><td><span class="guide-chip role">Verified User</span></td><td>Risk, Third-Party Vendors, Threat Intel, User Guide</td><td>Sesuai owner/divisi yang diizinkan</td><td>Bisa mengelola risk, treatment, KRI, BCM, incident, playbook, comment, attachment, dan vendor yang masuk scope akses.</td></tr>
                            <tr><td><span class="guide-chip role">Compliance</span></td><td>Semua menu verified user + Audit + Compliance</td><td>Lintas owner sesuai kewenangan compliance</td><td>Bisa import risk register, memonitor audit, dan mengelola program kerja compliance lintas owner.</td></tr>
                            <tr><td><span class="guide-chip role">Pending Approval</span></td><td>Pending Approval</td><td>Tidak ada akses operasional</td><td>Belum bisa masuk ke modul risiko sebelum division diverifikasi superadmin.</td></tr>
                        </tbody>
                    </table>
                </div>

                <div class="guide-section-title">Identitas akun di navbar</div>
                <div class="table-responsive guide-tight">
                    <table class="table table-sm table-bordered guide-table">
                        <thead>
                            <tr>
                                <th>Bagian</th>
                                <th>Apa yang tampil</th>
                                <th>Fungsinya</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr><td>Baris 1</td><td><strong>{{ $viewerName }}</strong></td><td>Menunjukkan username akun yang sedang login.</td></tr>
                            <tr><td>Baris 2 user biasa</td><td><strong>Compliance</strong> untuk akun compliance, atau label division sesuai scope akun</td><td>Membantu user memastikan konteks akses sebelum input data.</td></tr>
                            <tr><td>Baris 2 superadmin</td><td><strong>superadmin</strong></td><td>Menandakan akun sedang berada di mode administrasi sistem.</td></tr>
                            <tr><td>Dropdown akun</td><td><strong>Profile</strong>, <strong>Change Password</strong>, <strong>Logout</strong></td><td>Memperbarui identitas akun sendiri, ganti password, atau keluar dari sesi login.</td></tr>
                        </tbody>
                    </table>
                </div>

                <div class="guide-note">
                    Aturan sederhana: kalau layar menampilkan form simpan, akun Anda sedang punya hak tulis untuk objek itu. Kalau hanya muncul summary, table, atau alert read-only, akun Anda sedang berada di mode baca.
                </div>
            </div>

            @if($isOperational)
                <div class="tab-pane fade" id="guide-risk-pane" role="tabpanel" aria-labelledby="guide-risk-tab">
                    <div class="guide-pane-title">Risk Workspace dan Modul Operasional</div>
                    <div class="guide-feature">
                        <div class="guide-feature-kicker"><i class="fas fa-chart-pie"></i> Feature 1</div>
                        <div class="guide-feature-title">Risk Monitoring</div>
                        <div class="guide-feature-route">Risk > Risk Monitoring</div>
                        <p class="mb-2">Halaman ini dipakai untuk membaca portfolio risk secara real-time sebelum user masuk ke register detail.</p>

                        <div class="guide-mini-note">
                            <strong>Alur cepat:</strong> pilih tab dashboard dulu, lihat summary card dan widget yang paling relevan, lalu pakai <strong>Customize Widgets</strong> kalau ingin fokus ke panel tertentu.
                        </div>

                        <div class="guide-section-title">Box, tab, dan informasi yang tampil</div>
                        <div class="table-responsive guide-tight">
                            <table class="table table-sm table-bordered guide-table">
                                <thead>
                                    <tr>
                                        <th>Box / tab</th>
                                        <th>Yang dibaca user</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr><td><strong>Last refresh</strong></td><td>Waktu data dashboard terakhir diambil. Dipakai untuk cek apakah summary masih fresh saat dipakai meeting.</td></tr>
                                    <tr><td><strong>Dashboard 1 - Risk Overview</strong></td><td>Melihat portfolio command center, benchmark scoring, mitigation, SLA, KPI, dan division performance.</td></tr>
                                    <tr><td><strong>Dashboard 2 - Risk Status &amp; Core Matrix</strong></td><td>Fokus ke status risk, core metrics, average scores, dan pergeseran inherent vs residual.</td></tr>
                                    <tr><td><strong>Dashboard 3 - Risk Root Cause Analysis</strong></td><td>Melihat pola root cause category, keyword dominan, dan pattern yang sering muncul.</td></tr>
                                    <tr><td><strong>Status &amp; Core Metrics</strong></td><td>Menampilkan <strong>Total Risks</strong>, <strong>Open</strong>, <strong>Monitoring</strong>, <strong>Closed</strong>, serta rata-rata inherent, residual, overall, dan variance.</td></tr>
                                    <tr><td><strong>Portfolio Command Center</strong></td><td>Meringkas distribusi portfolio berdasarkan priority, category, division, timeline treatment, risk status, dan top risks.</td></tr>
                                    <tr><td><strong>Mitigation, SLA &amp; KPI Dashboard</strong></td><td>Membaca efektivitas treatment, coverage risk, SLA compliance, overdue count, dan response KPI.</td></tr>
                                    <tr><td><strong>Department / Division Performance</strong></td><td>Membandingkan division berdasarkan total risks, average score, dan ranking performa.</td></tr>
                                    <tr><td><strong>Benchmark Scoring</strong></td><td>Membandingkan hasil portfolio dengan benchmark internal atau referensi sumber yang dicantumkan sistem.</td></tr>
                                </tbody>
                            </table>
                        </div>

                        <div class="guide-section-title">Tombol dan kontrol</div>
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered guide-table">
                                <thead>
                                    <tr>
                                        <th>Tombol / kontrol</th>
                                        <th>Fungsi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr><td><strong>Export Report</strong></td><td>Mengekspor tampilan monitoring ke format report yang tersedia untuk bahan presentasi atau distribusi.</td></tr>
                                    <tr><td><strong>Add Risk</strong></td><td>Membuka form <strong>Add Risk</strong> langsung dari dashboard tanpa balik ke register.</td></tr>
                                    <tr><td><strong>Customize Widgets</strong></td><td>Membuka daftar checkbox <strong>Show/Hide Widgets</strong> untuk menampilkan atau menyembunyikan panel tertentu.</td></tr>
                                    <tr><td><strong>Checkbox widget</strong></td><td>Menyalakan atau mematikan widget per nama panel.</td></tr>
                                    <tr><td><strong>Drag handle</strong></td><td>Mengubah urutan kartu widget sesuai preferensi user.</td></tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="guide-feature">
                        <div class="guide-feature-kicker"><i class="fas fa-table"></i> Feature 2</div>
                        <div class="guide-feature-title">Risk Register Workspace</div>
                        <div class="guide-feature-route">Risk > Risk Register</div>
                        <p class="mb-2">Workspace utama untuk pencarian risk, import massal, entry baru, dan akses ke heat map maupun recap.</p>

                        <div class="guide-section-title">Tab workspace</div>
                        <div class="table-responsive guide-tight">
                            <table class="table table-sm table-bordered guide-table">
                                <thead>
                                    <tr>
                                        <th>Tab</th>
                                        <th>Dipakai untuk</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr><td><strong>Dashboard 1 - Risk Register</strong></td><td>Tabel kerja harian untuk filter owner, import, add risk, dan action per baris.</td></tr>
                                    <tr><td><strong>Dashboard 2 - Risk Heat Map</strong></td><td>Melihat posisi risk pada matrix inherent dan residual.</td></tr>
                                    <tr><td><strong>Dashboard 3 - Risk Recapitulation</strong></td><td>Merangkum register dengan sudut pandang rekap yang lebih siap dibaca manajemen.</td></tr>
                                </tbody>
                            </table>
                        </div>

                        <div class="guide-section-title">Filter, box, modal, dan action</div>
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered guide-table">
                                <thead>
                                    <tr>
                                        <th>Label / box / tombol</th>
                                        <th>Fungsi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr><td><strong>Last refresh</strong></td><td>Menunjukkan waktu data register terakhir direfresh di halaman.</td></tr>
                                    <tr><td><strong>Filter Owner</strong></td><td>Menyaring tabel risk berdasarkan risk owner yang dipilih.</td></tr>
                                    @if($isCompliance)
                                        <tr><td><strong>Import XLS</strong></td><td>Membuka modal <strong>Import Risk Register</strong> dengan field <strong>Excel File (.xls/.xlsx)</strong>, <strong>Year (Optional)</strong>, checkbox <strong>Dry-run dulu</strong>, tombol <strong>Download Template</strong>, <strong>Cancel</strong>, dan <strong>Start Import</strong>.</td></tr>
                                    @endif
                                    <tr><td><strong>Add Risk</strong></td><td>Membuka form baru untuk membuat risk lengkap dari tab 1 sampai tab 6.</td></tr>
                                    <tr><td><strong>View</strong></td><td>Membuka halaman <strong>Risk Details</strong> lengkap beserta RCSA, BCM, governance, dan KRI.</td></tr>
                                    <tr><td><strong>Edit</strong></td><td>Mengubah risk yang masih berada dalam scope kelola akun Anda.</td></tr>
                                    <tr><td><strong>Delete</strong></td><td>Menghapus risk setelah konfirmasi. Hanya tampil untuk akun dengan hak kelola.</td></tr>
                                    <tr><td><strong>Export Report</strong></td><td>Mengekspor data register, heat map, atau recap sesuai konteks tab yang sedang aktif.</td></tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="guide-feature">
                        <div class="guide-feature-kicker"><i class="fas fa-edit"></i> Feature 3</div>
                        <div class="guide-feature-title">Add Risk dan Edit Risk</div>
                        <div class="guide-feature-route">Add Risk / Edit Risk - Complete Form (6 Sections)</div>
                        <p class="mb-2">Form create dan edit memakai enam tab besar. Field yang wajib biasanya bertanda <strong>*</strong>, sementara field referensi hanya dibaca.</p>

                        <div class="guide-section-title">Tab 1 - Risk</div>
                        <div class="table-responsive guide-tight">
                            <table class="table table-sm table-bordered guide-table">
                                <thead>
                                    <tr>
                                        <th>Label / field</th>
                                        <th>Yang diisi atau dibaca</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr><td><strong>Risk ID</strong></td><td>Auto generate. User tidak mengetik manual.</td></tr>
                                    <tr><td><strong>Year</strong></td><td>Tahun berjalan atau tahun risk yang sedang diedit.</td></tr>
                                    <tr><td><strong>Risk Category</strong></td><td>Memilih klasifikasi utama risk dari parameter aktif.</td></tr>
                                    <tr><td><strong>Process</strong></td><td>Nama proses atau judul risk yang menjadi objek risiko.</td></tr>
                                    <tr><td><strong>Risk Owner</strong></td><td>Owner risk dari daftar parameter. Tidak diinput manual bebas.</td></tr>
                                    <tr><td><strong>Corporate Risk Appetite</strong></td><td>Memilih framework appetite bila risk harus mengikuti threshold korporat.</td></tr>
                                    <tr><td><strong>Cause Category</strong></td><td>Memilih kategori penyebab utama agar root cause lebih terstruktur.</td></tr>
                                    <tr><td><strong>Third-Party Vendor</strong></td><td>Dipakai kalau risk terkait vendor tertentu.</td></tr>
                                    <tr><td><strong>Sasaran Target</strong></td><td>Target bisnis atau sasaran yang ingin dijaga oleh risk ini.</td></tr>
                                    <tr><td><strong>Risk Identification</strong></td><td>Deskripsi utama risk yang diidentifikasi.</td></tr>
                                    <tr><td><strong>Cause</strong></td><td>Penjelasan sebab atau pemicu risk.</td></tr>
                                    <tr><td><strong>Existing Control</strong></td><td>Kontrol yang sudah berjalan sebelum treatment baru ditambah.</td></tr>
                                </tbody>
                            </table>
                        </div>

                        <div class="guide-section-title">Tab 2 - Inherent Analysis</div>
                        <div class="table-responsive guide-tight">
                            <table class="table table-sm table-bordered guide-table">
                                <thead>
                                    <tr>
                                        <th>Label / field</th>
                                        <th>Yang diisi atau dibaca</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr><td><strong>Summary (Reference)</strong></td><td>Ringkasan Risk ID, Process, dan Risk Identification agar user tidak kehilangan konteks saat scoring.</td></tr>
                                    <tr><td><strong>Probability Description</strong></td><td>Memilih skor probabilitas beserta deskripsinya untuk kondisi sebelum treatment.</td></tr>
                                    <tr><td><strong>Impact Category</strong></td><td>Memilih kelompok impact yang akan membuka daftar impact description terkait.</td></tr>
                                    <tr><td><strong>Impact Description</strong></td><td>Memilih skor dan deskripsi impact sesuai category yang dipilih.</td></tr>
                                    <tr><td><strong>Inherent Risk Score (Auto)</strong></td><td>Dihitung otomatis dari probability x impact sebelum treatment.</td></tr>
                                </tbody>
                            </table>
                        </div>

                        <div class="guide-section-title">Tab 3 - Risk Treatment</div>
                        <div class="table-responsive guide-tight">
                            <table class="table table-sm table-bordered guide-table">
                                <thead>
                                    <tr>
                                        <th>Label / field / tombol</th>
                                        <th>Fungsi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr><td><strong>Add Treatment Manually</strong></td><td>Menambah row treatment kosong agar user bisa mengisi sendiri action plan.</td></tr>
                                    <tr><td><strong>Manage Playbooks</strong></td><td>Membuka library playbook untuk melihat atau membuat template treatment.</td></tr>
                                    <tr><td><strong>Mitigation Playbook (Optional)</strong></td><td>Memilih template yang hanya melakukan prefill field treatment, bukan menggantikan input manual.</td></tr>
                                    <tr><td><strong>Apply Playbook</strong></td><td>Mengisikan row treatment dengan data default playbook yang dipilih.</td></tr>
                                    <tr><td><strong>Treatment Option</strong></td><td>Jenis respons treatment, misalnya avoidance, mitigation, transfer, atau acceptance.</td></tr>
                                    <tr><td><strong>Description</strong></td><td>Rincian action yang akan dijalankan.</td></tr>
                                    <tr><td><strong>PIC / Related Division</strong></td><td>Penanggung jawab atau unit terkait yang mengerjakan action.</td></tr>
                                    <tr><td><strong>Deadline</strong></td><td>Tanggal jatuh tempo action treatment.</td></tr>
                                    <tr><td><strong>Effectiveness</strong></td><td>Estimasi efektivitas treatment, sekaligus dipakai sistem untuk scoring pendukung.</td></tr>
                                    <tr><td><strong>Next Action Date</strong></td><td>Tanggal follow-up berikutnya jika action masih berjalan.</td></tr>
                                    <tr><td><strong>Blocked Reason</strong></td><td>Alasan hambatan. Wajib diisi jika treatment nanti dipindah ke kolom blocked di kanban.</td></tr>
                                    <tr><td><strong>Playbook Checklist</strong></td><td>Daftar langkah atau evidence yang harus dipenuhi untuk action tersebut.</td></tr>
                                </tbody>
                            </table>
                        </div>

                        <div class="guide-section-title">Tab 4 sampai 6</div>
                        <div class="table-responsive guide-tight">
                            <table class="table table-sm table-bordered guide-table">
                                <thead>
                                    <tr>
                                        <th>Tab</th>
                                        <th>Field yang dipakai</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr><td><strong>Residual Analysis</strong></td><td><strong>Probability Description</strong>, <strong>Impact Category</strong>, <strong>Impact Description</strong>, <strong>Residual Risk Score (Auto)</strong>, <strong>Residual Risk Adjustment</strong>, dan <strong>Level Adjustment</strong> untuk kondisi setelah treatment.</td></tr>
                                    <tr><td><strong>Group Radar</strong></td><td><strong>Group Radar Description</strong> dan <strong>Group Radar Code</strong> untuk mengaitkan risk ke radar group yang dipakai quant profile.</td></tr>
                                    <tr><td><strong>Quantitative Result</strong></td><td><strong>Inherent Impact (Rp)</strong>, <strong>Inherent Probability (%)</strong>, <strong>Inherent Potential Loss (Rp)</strong>, <strong>Residual Impact (Rp)</strong>, <strong>Residual Probability (%)</strong>, dan <strong>Residual Potential Loss (Rp)</strong>.</td></tr>
                                </tbody>
                            </table>
                        </div>

                        <div class="guide-section-title">Tombol navigasi dan simpan</div>
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered guide-table">
                                <thead>
                                    <tr>
                                        <th>Tombol</th>
                                        <th>Fungsi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr><td><strong>Back</strong></td><td>Kembali ke tab sebelumnya tanpa menyimpan.</td></tr>
                                    <tr><td><strong>Next: ...</strong></td><td>Pindah ke tab berikutnya setelah user selesai mengisi section saat ini.</td></tr>
                                    <tr><td><strong>SAVE COMPLETE RISK</strong></td><td>Menyimpan seluruh form dan kembali ke alur normal setelah risk selesai dibuat.</td></tr>
                                    <tr><td><strong>SAVE &amp; CONTINUE TO BCM</strong></td><td>Menyimpan risk baru lalu langsung membuka bagian BCM untuk lanjut BIA.</td></tr>
                                    <tr><td><strong>UPDATE</strong></td><td>Menyimpan perubahan pada risk yang sedang diedit.</td></tr>
                                    <tr><td><strong>UPDATE &amp; OPEN BCM</strong></td><td>Menyimpan perubahan lalu melompat ke tab BCM pada risk terkait.</td></tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="guide-feature">
                        <div class="guide-feature-kicker"><i class="fas fa-search"></i> Feature 4</div>
                        <div class="guide-feature-title">Risk Details, Governance, dan RCSA Workspace</div>
                        <div class="guide-feature-route">View pada baris Risk Register</div>
                        <p class="mb-2">Halaman detail adalah tempat review satu risk secara lengkap, termasuk evidence, komentar, activity feed, BIA/BCP, dan shortcut ke RCSA Workspace.</p>

                        <div class="guide-section-title">Tombol di header</div>
                        <div class="table-responsive guide-tight">
                            <table class="table table-sm table-bordered guide-table">
                                <thead>
                                    <tr>
                                        <th>Tombol</th>
                                        <th>Fungsi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr><td><strong>RCSA Workspace</strong></td><td>Membuka halaman assessment formal untuk risk yang sedang ditinjau.</td></tr>
                                    <tr><td><strong>Edit</strong></td><td>Membuka form edit risk bila user punya hak kelola.</td></tr>
                                    <tr><td><strong>Back</strong></td><td>Kembali ke Risk Register.</td></tr>
                                </tbody>
                            </table>
                        </div>

                        <div class="guide-section-title">Tab di halaman detail risk</div>
                        <div class="table-responsive guide-tight">
                            <table class="table table-sm table-bordered guide-table">
                                <thead>
                                    <tr>
                                        <th>Tab</th>
                                        <th>Yang tampil atau dilakukan</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr><td><strong>Basic</strong></td><td>Menampilkan data master risk, termasuk category, cause category, vendor, dan corporate appetite yang terhubung.</td></tr>
                                    <tr><td><strong>Inherent</strong></td><td>Menampilkan probability description, impact description, score, dan level inheren.</td></tr>
                                    <tr><td><strong>Treatment</strong></td><td>Menampilkan setiap treatment dengan option, description, PIC, deadline, effectiveness, next action, blocked reason, dan evidence terkait.</td></tr>
                                    <tr><td><strong>RCSA</strong></td><td>Ringkasan workspace assessment atau status belum ada assessment.</td></tr>
                                    <tr><td><strong>Residual</strong></td><td>Menampilkan hasil risk setelah treatment, termasuk radar description dan score residual.</td></tr>
                                    <tr><td><strong>BCM (BIA/BCP)</strong></td><td>Form Business Impact Analysis dan Business Continuity Plan untuk risk yang sama.</td></tr>
                                    <tr><td><strong>Governance</strong></td><td>Upload evidence, diskusi komentar, mention, attachment, dan activity feed.</td></tr>
                                    <tr><td><strong>Key Risk Indicators</strong></td><td>Pengelolaan KRI yang khusus terkait risk tersebut.</td></tr>
                                </tbody>
                            </table>
                        </div>

                        <div class="guide-section-title">Governance tab</div>
                        <div class="table-responsive guide-tight">
                            <table class="table table-sm table-bordered guide-table">
                                <thead>
                                    <tr>
                                        <th>Label / tombol</th>
                                        <th>Fungsi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr><td><strong>Risk Evidence</strong></td><td>Area untuk menyimpan file bukti risk level, treatment, atau review.</td></tr>
                                    <tr><td><strong>file</strong> + <strong>notes</strong></td><td>Field upload evidence risk utama.</td></tr>
                                    <tr><td><strong>Upload</strong></td><td>Menyimpan file evidence ke risk.</td></tr>
                                    <tr><td><strong>Download</strong></td><td>Mengambil attachment yang sudah diunggah.</td></tr>
                                    <tr><td><strong>Delete</strong></td><td>Menghapus attachment risk atau comment attachment jika user berhak.</td></tr>
                                    <tr><td><strong>Comment</strong></td><td>Isi diskusi. Mendukung mention dengan format <strong>@username</strong>.</td></tr>
                                    <tr><td><strong>Attachments (optional)</strong></td><td>Melampirkan file tambahan ke comment yang sedang diposting.</td></tr>
                                    <tr><td><strong>Post Comment</strong></td><td>Menyimpan komentar baru pada discussion thread.</td></tr>
                                    <tr><td><strong>Edit</strong> dan <strong>Save</strong></td><td>Mengubah isi komentar milik sendiri.</td></tr>
                                    <tr><td><strong>Attach</strong></td><td>Menambah attachment baru ke komentar yang sudah ada.</td></tr>
                                    <tr><td><strong>Activity Feed</strong></td><td>Tabel waktu, actor, action, entity, dan details untuk audit trail internal risk tersebut.</td></tr>
                                </tbody>
                            </table>
                        </div>

                        <div class="guide-section-title">RCSA Workspace</div>
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered guide-table">
                                <thead>
                                    <tr>
                                        <th>Section / field</th>
                                        <th>Yang diisi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr><td><strong>Assessment Context</strong></td><td><strong>Process Owner</strong>, <strong>Review Frequency</strong>, <strong>Next Review Date</strong>, <strong>Objective</strong>, dan <strong>Scope</strong>.</td></tr>
                                    <tr><td><strong>Control Assessment</strong></td><td><strong>Key Controls</strong> dan <strong>Control Gaps / Improvement Themes</strong>.</td></tr>
                                    <tr><td><strong>Residual Rationale</strong></td><td><strong>Likelihood Rationale</strong>, <strong>Impact Rationale</strong>, dan <strong>Notes</strong>.</td></tr>
                                    <tr><td><strong>Sign-Off</strong></td><td><strong>Sign-Off Name</strong>, <strong>Sign-Off Role</strong>, dan <strong>Signed-Off Date</strong>.</td></tr>
                                    <tr><td><strong>Save RCSA Workspace</strong></td><td>Menyimpan hasil assessment formal untuk risk tersebut.</td></tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="guide-feature">
                        <div class="guide-feature-kicker"><i class="fas fa-chart-line"></i> Feature 5</div>
                        <div class="guide-feature-title">Risk Trend Analysis</div>
                        <div class="guide-feature-route">Risk > Risk Trend Analysis</div>
                        <p class="mb-2">Halaman ini dipakai untuk membaca pergerakan inherent, residual, dan distribusi risk dari waktu ke waktu.</p>

                        <div class="table-responsive guide-tight">
                            <table class="table table-sm table-bordered guide-table">
                                <thead>
                                    <tr>
                                        <th>Label / box</th>
                                        <th>Fungsi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr><td><strong>Granularity</strong></td><td>Memilih horizon grafik: <strong>Monthly</strong>, <strong>Quarterly</strong>, atau <strong>Yearly</strong>.</td></tr>
                                    <tr><td><strong>Period</strong></td><td>Memilih rentang: <strong>Last 3 Months</strong>, <strong>Last 6 Months</strong>, <strong>Last 1 Year</strong>, atau <strong>Last 2 Years</strong>.</td></tr>
                                    <tr><td><strong>Avg Inherent Risk Score</strong></td><td>Rata-rata score inheren pada periode yang dipilih.</td></tr>
                                    <tr><td><strong>Avg Residual Risk Score</strong></td><td>Rata-rata score residual pada periode yang dipilih.</td></tr>
                                    <tr><td><strong>Risk Reduction</strong></td><td>Persentase perbaikan residual terhadap inherent.</td></tr>
                                    <tr><td><strong>Total Risks</strong></td><td>Jumlah risk yang masuk ke analisa periode aktif.</td></tr>
                                    <tr><td><strong>Risk Score Trend</strong></td><td>Grafik garis inherent vs residual.</td></tr>
                                    <tr><td><strong>Risk Score by Category</strong></td><td>Membandingkan score rata-rata per kategori risk.</td></tr>
                                    <tr><td><strong>Drag handle</strong></td><td>Mengubah urutan panel analisis sesuai preferensi pengguna.</td></tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="guide-feature">
                        <div class="guide-feature-kicker"><i class="fas fa-wave-square"></i> Feature 6</div>
                        <div class="guide-feature-title">Key Risk Indicators (KRIs)</div>
                        <div class="guide-feature-route">Risk > Key Risk Indicators</div>
                        <p class="mb-2">Modul ini dipakai untuk threshold monitoring per risk. KRI bisa dibuat cepat dari dashboard atau dikelola dari detail risk.</p>

                        <div class="guide-section-title">Box, filter, dan table</div>
                        <div class="table-responsive guide-tight">
                            <table class="table table-sm table-bordered guide-table">
                                <thead>
                                    <tr>
                                        <th>Label / box</th>
                                        <th>Fungsi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr><td><strong>Healthy Parameters</strong></td><td>Jumlah KRI yang masih aman.</td></tr>
                                    <tr><td><strong>Warning Thresholds Hit</strong></td><td>Jumlah KRI yang sudah menyentuh amber threshold.</td></tr>
                                    <tr><td><strong>Critical Violations</strong></td><td>Jumlah KRI yang sudah red threshold.</td></tr>
                                    <tr><td><strong>Total KRI Metrics</strong></td><td>Total indikator yang aktif di scope akun.</td></tr>
                                    <tr><td><strong>Status</strong></td><td>Filter Green, Amber, Red, atau semua status.</td></tr>
                                    <tr><td><strong>Risk</strong></td><td>Filter indikator berdasarkan risk tertentu.</td></tr>
                                    <tr><td><strong>Apply</strong> / <strong>Reset</strong></td><td>Menerapkan atau membersihkan filter dashboard KRI.</td></tr>
                                    <tr><td><strong>Open Risk</strong></td><td>Membuka tab KRI pada risk yang terkait.</td></tr>
                                </tbody>
                            </table>
                        </div>

                        <div class="guide-section-title">Modal Add KRI</div>
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered guide-table">
                                <thead>
                                    <tr>
                                        <th>Field / tombol</th>
                                        <th>Yang diisi atau dilakukan</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr><td><strong>Risk</strong></td><td>Memilih risk yang akan diberi indikator.</td></tr>
                                    <tr><td><strong>Metric Name</strong></td><td>Nama indikator, misalnya SLA breach count atau jumlah incident.</td></tr>
                                    <tr><td><strong>Description</strong></td><td>Catatan penjelas agar indikator mudah dibaca tim.</td></tr>
                                    <tr><td><strong>Threshold Direction</strong></td><td>Menentukan apakah nilai kecil lebih baik atau nilai besar lebih baik.</td></tr>
                                    <tr><td><strong>Warning Threshold (Amber)</strong></td><td>Nilai batas peringatan awal.</td></tr>
                                    <tr><td><strong>Critical Threshold (Red)</strong></td><td>Nilai batas kritis.</td></tr>
                                    <tr><td><strong>Cancel</strong></td><td>Menutup modal tanpa menyimpan.</td></tr>
                                    <tr><td><strong>Save KRI</strong></td><td>Menyimpan indikator baru ke risk yang dipilih.</td></tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="guide-feature">
                        <div class="guide-feature-kicker"><i class="fas fa-columns"></i> Feature 7</div>
                        <div class="guide-feature-title">Mitigation Kanban</div>
                        <div class="guide-feature-route">Risk > Mitigation Kanban</div>
                        <p class="mb-2">Kanban dipakai untuk menjalankan treatment harian dan memaksa disiplin blocked reason, evidence, serta SLA.</p>

                        <div class="table-responsive guide-tight">
                            <table class="table table-sm table-bordered guide-table">
                                <thead>
                                    <tr>
                                        <th>Box / tombol</th>
                                        <th>Fungsi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr><td><strong>Owner Workload</strong></td><td>Shortcut ke dashboard beban owner.</td></tr>
                                    <tr><td><strong>Playbooks</strong></td><td>Shortcut ke library mitigation playbook.</td></tr>
                                    <tr><td><strong>Operational rules</strong></td><td>Menjelaskan bahwa item blocked harus punya blocked reason dan item completed harus punya evidence.</td></tr>
                                    <tr><td><strong>Not Started / In Progress / Blocked / Completed</strong></td><td>Kolom status utama treatment.</td></tr>
                                    <tr><td><strong>SLA chip</strong></td><td>Menunjukkan kondisi deadline: overdue, due soon, on track, completed, atau no deadline.</td></tr>
                                    <tr><td><strong>Aging chip</strong></td><td>Menunjukkan berapa hari card berada di status sekarang.</td></tr>
                                    <tr><td><strong>Evidence chip</strong></td><td>Menunjukkan jumlah attachment evidence pada treatment.</td></tr>
                                    <tr><td><strong>Blocked reason</strong></td><td>Tampil bila treatment tertahan.</td></tr>
                                    <tr><td><strong>Checklist</strong></td><td>Preview langkah playbook yang harus dibuktikan.</td></tr>
                                    <tr><td><strong>Drag and drop</strong></td><td>Memindahkan card antar kolom untuk mengubah status treatment.</td></tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="guide-feature">
                        <div class="guide-feature-kicker"><i class="fas fa-users-cog"></i> Feature 8</div>
                        <div class="guide-feature-title">Owner Workload</div>
                        <div class="guide-feature-route">Risk > Owner Workload</div>
                        <p class="mb-2">Dashboard ini membantu melihat owner mana yang paling butuh perhatian operasional.</p>

                        <div class="table-responsive guide-tight">
                            <table class="table table-sm table-bordered guide-table">
                                <thead>
                                    <tr>
                                        <th>Box / kolom</th>
                                        <th>Fungsi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr><td><strong>Owners in Scope</strong></td><td>Jumlah owner yang masuk ke cakupan akun.</td></tr>
                                    <tr><td><strong>Open Actions</strong></td><td>Total treatment yang masih aktif.</td></tr>
                                    <tr><td><strong>Overdue</strong></td><td>Jumlah treatment yang lewat deadline.</td></tr>
                                    <tr><td><strong>Blocked</strong></td><td>Jumlah treatment yang tertahan.</td></tr>
                                    <tr><td><strong>Pending Approvals</strong></td><td>Jumlah request perubahan status risk yang masih menunggu review.</td></tr>
                                    <tr><td><strong>Red KRIs</strong></td><td>Jumlah indikator kritis yang dimiliki owner.</td></tr>
                                    <tr><td><strong>Highest Attention Owner</strong></td><td>Owner dengan workload score tertinggi saat ini.</td></tr>
                                    <tr><td><strong>Owner Queue</strong></td><td>Tabel ranking owner dengan kolom attention, risk count, high risks, open actions, overdue, blocked, due soon, pending approvals, red KRIs, dan score.</td></tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="guide-feature">
                        <div class="guide-feature-kicker"><i class="fas fa-book-open"></i> Feature 9</div>
                        <div class="guide-feature-title">Mitigation Playbooks</div>
                        <div class="guide-feature-route">Risk > Mitigation Playbooks</div>
                        <p class="mb-2">Playbook menyimpan template action plan yang sering dipakai ulang agar entry treatment lebih cepat dan konsisten.</p>

                        <div class="table-responsive guide-tight">
                            <table class="table table-sm table-bordered guide-table">
                                <thead>
                                    <tr>
                                        <th>Field / tombol</th>
                                        <th>Fungsi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr><td><strong>Playbook Name</strong></td><td>Nama template treatment.</td></tr>
                                    <tr><td><strong>Risk Owner</strong></td><td>Owner yang boleh memakai template ini sebagai default filter.</td></tr>
                                    <tr><td><strong>Treatment Option</strong></td><td>Jenis action default untuk playbook.</td></tr>
                                    <tr><td><strong>Default Action Description</strong></td><td>Isi deskripsi treatment yang akan diprefill ke row action.</td></tr>
                                    <tr><td><strong>Default PIC</strong></td><td>PIC default untuk action yang sama berulang.</td></tr>
                                    <tr><td><strong>Effectiveness</strong></td><td>Efektivitas default treatment.</td></tr>
                                    <tr><td><strong>Target Deadline (Days)</strong></td><td>Offset hari untuk deadline otomatis.</td></tr>
                                    <tr><td><strong>Next Action Offset (Days)</strong></td><td>Offset follow-up berikutnya.</td></tr>
                                    <tr><td><strong>Status - Active</strong></td><td>Menentukan playbook masih bisa dipilih di form risk atau tidak.</td></tr>
                                    <tr><td><strong>Checklist</strong></td><td>Daftar langkah atau evidence yang harus dipenuhi.</td></tr>
                                    <tr><td><strong>Save Playbook</strong></td><td>Menyimpan template baru.</td></tr>
                                    <tr><td><strong>Edit Playbook</strong>, <strong>Update</strong>, <strong>Delete Playbook</strong></td><td>Mengubah atau menghapus template yang sudah ada.</td></tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="guide-feature">
                        <div class="guide-feature-kicker"><i class="fas fa-life-ring"></i> Feature 10</div>
                        <div class="guide-feature-title">Business Continuity Management (BCM)</div>
                        <div class="guide-feature-route">Risk > Business Continuity (BCM)</div>
                        <p class="mb-2">BCM menghubungkan risk register dengan Business Impact Analysis (BIA) dan Business Continuity Plan (BCP).</p>

                        <div class="guide-section-title">Dashboard BCM</div>
                        <div class="table-responsive guide-tight">
                            <table class="table table-sm table-bordered guide-table">
                                <thead>
                                    <tr>
                                        <th>Box / tombol</th>
                                        <th>Fungsi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr><td><strong>Total BIAs</strong></td><td>Jumlah process assessment yang sudah punya BIA.</td></tr>
                                    <tr><td><strong>Tier 1 (Critical)</strong></td><td>Jumlah process dengan criticality tier tertinggi.</td></tr>
                                    <tr><td><strong>Tested BCPs</strong></td><td>Jumlah recovery plan yang sudah pernah diuji.</td></tr>
                                    <tr><td><strong>Missing BCPs</strong></td><td>Jumlah BIA yang belum punya plan.</td></tr>
                                    <tr><td><strong>Conduct BIA</strong></td><td>Membuka modal pemilihan risk yang belum punya BIA.</td></tr>
                                    <tr><td><strong>All Tiers / Tier 1 / Tier 2 / Tier 3 / Tier 4</strong></td><td>Filter register BCM berdasarkan criticality tier.</td></tr>
                                    <tr><td><strong>View</strong></td><td>Membuka tab BCM pada risk yang dipilih.</td></tr>
                                    <tr><td><strong>Select Process (Risk)</strong> + <strong>Continue to BIA Form</strong></td><td>Dipakai di modal untuk memilih risk dan lanjut ke form BIA.</td></tr>
                                </tbody>
                            </table>
                        </div>

                        <div class="guide-section-title">Form BIA dan BCP di detail risk</div>
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered guide-table">
                                <thead>
                                    <tr>
                                        <th>Section / field</th>
                                        <th>Yang diisi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr><td><strong>Business Impact Analysis (BIA)</strong></td><td><strong>Recovery Time Objective (RTO)</strong>, <strong>Recovery Point Objective (RPO)</strong>, <strong>Max Acceptable Outage</strong>, <strong>Financial Impact</strong>, <strong>Operational Impact</strong>, <strong>Regulatory Impact</strong>, <strong>Reputational Impact</strong>, <strong>Criticality Tier</strong>, dan <strong>Justification / Notes</strong>.</td></tr>
                                    <tr><td><strong>Save BIA</strong></td><td>Menyimpan assessment impact proses.</td></tr>
                                    <tr><td><strong>Business Continuity Plan (BCP)</strong></td><td><strong>Recovery Strategy Summary</strong>, <strong>Recovery Step-by-Step Procedures</strong>, <strong>Primary Workaround</strong>, <strong>Testing Frequency</strong>, <strong>Last Tested Date</strong>, dan <strong>Last Test Result</strong>.</td></tr>
                                    <tr><td><strong>Save BCP</strong></td><td>Menyimpan recovery plan. Form BCP baru aktif setelah BIA ada.</td></tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="guide-feature">
                        <div class="guide-feature-kicker"><i class="fas fa-handshake"></i> Feature 11</div>
                        <div class="guide-feature-title">Third-Party Vendors</div>
                        <div class="guide-feature-route">Third-Party Vendors</div>
                        <p class="mb-2">Modul vendor dipakai untuk mendata pihak ketiga dan menghubungkannya dengan risk terkait.</p>

                        <div class="table-responsive guide-tight">
                            <table class="table table-sm table-bordered guide-table">
                                <thead>
                                    <tr>
                                        <th>Label / tombol</th>
                                        <th>Fungsi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr><td><strong>Add Vendor</strong></td><td>Membuka form vendor baru.</td></tr>
                                    <tr><td><strong>Vendor Name</strong></td><td>Nama vendor atau penyedia layanan.</td></tr>
                                    <tr><td><strong>Description / Services Provided</strong></td><td>Menjelaskan layanan atau ruang lingkup vendor.</td></tr>
                                    <tr><td><strong>Contact Name</strong></td><td>Nama PIC vendor.</td></tr>
                                    <tr><td><strong>Contact Email</strong></td><td>Email PIC vendor.</td></tr>
                                    <tr><td><strong>Criticality Level</strong></td><td>Level kritikal vendor: Low, Medium, atau High.</td></tr>
                                    <tr><td><strong>Status</strong></td><td>Menentukan vendor aktif atau tidak aktif.</td></tr>
                                    <tr><td><strong>Save Vendor</strong></td><td>Menyimpan vendor baru.</td></tr>
                                    <tr><td><strong>View</strong></td><td>Membuka detail vendor beserta associated risks yang terlihat dalam scope Anda.</td></tr>
                                    <tr><td><strong>Edit</strong></td><td>Mengubah data vendor yang sudah ada.</td></tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="guide-feature">
                        <div class="guide-feature-kicker"><i class="fas fa-shield-alt"></i> Feature 12</div>
                        <div class="guide-feature-title">Corporate Risk Appetite</div>
                        <div class="guide-feature-route">Risk > Corporate Risk Appetite</div>
                        <p class="mb-2">Framework appetite menjadi referensi threshold untuk risk yang memakai pendekatan appetite korporat.</p>

                        <div class="table-responsive">
                            <table class="table table-sm table-bordered guide-table">
                                <thead>
                                    <tr>
                                        <th>Box / field / tombol</th>
                                        <th>Fungsi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr><td><strong>Category</strong></td><td>Filter kategori atau kelompok appetite yang berlaku.</td></tr>
                                    <tr><td><strong>Name</strong></td><td>Nama framework appetite.</td></tr>
                                    <tr><td><strong>Appetite Statement</strong></td><td>Pernyataan kebijakan appetite yang dibaca risk owner.</td></tr>
                                    <tr><td><strong>Metrics (Appetite / Tolerance)</strong></td><td>Menunjukkan metric threshold, target appetite, dan maximum tolerance.</td></tr>
                                    <tr><td><strong>Status</strong></td><td>Active atau Inactive.</td></tr>
                                    <tr><td><strong>Back</strong></td><td>Kembali ke daftar framework.</td></tr>
                                    <tr><td><strong>Framework Summary</strong></td><td>Di halaman detail, menampilkan category, status, threshold metric, appetite statement, appetite target, max tolerance, dan jumlah linked risks.</td></tr>
                                    <tr><td><strong>Linked Risks In Your Scope</strong></td><td>Tabel risk yang memakai framework ini dan masih terlihat oleh akun Anda.</td></tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @endif

            @if($showThreatIntel)
                <div class="tab-pane fade" id="guide-intel-pane" role="tabpanel" aria-labelledby="guide-intel-tab">
                    <div class="guide-pane-title">{{ $isOperational ? 'Incident Management dan Threat Intelligence' : 'Threat Intelligence' }}</div>
                    @if($isOperational)
                        <div class="guide-feature">
                            <div class="guide-feature-kicker"><i class="fas fa-bolt"></i> Feature 1</div>
                            <div class="guide-feature-title">Incident Management</div>
                            <div class="guide-feature-route">Risk > Incident Management</div>
                            <p class="mb-2">Incident dipakai untuk mencatat kejadian nyata yang harus dikaitkan ke risk tertentu, bukan sekadar task mitigasi biasa.</p>

                            <div class="guide-section-title">Summary dan register</div>
                            <div class="table-responsive guide-tight">
                                <table class="table table-sm table-bordered guide-table">
                                    <thead>
                                        <tr>
                                            <th>Box / kolom</th>
                                            <th>Fungsi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr><td><strong>Total Incident</strong></td><td>Jumlah seluruh incident yang terlihat dalam scope Anda.</td></tr>
                                        <tr><td><strong>Active Handling</strong></td><td>Incident yang masih open atau sedang ditangani.</td></tr>
                                        <tr><td><strong>Closed</strong></td><td>Incident yang sudah selesai.</td></tr>
                                        <tr><td><strong>Mitigation Failed</strong></td><td>Incident yang verdict mitigasinya gagal.</td></tr>
                                        <tr><td><strong>Affected Risks</strong></td><td>Jumlah risk yang terkena dampak incident.</td></tr>
                                        <tr><td><strong>Incident Trend (Last 6 Months)</strong></td><td>Grafik tren kejadian per bulan.</td></tr>
                                        <tr><td><strong>Mitigation Verdict Distribution</strong></td><td>Distribusi verdict efektivitas mitigasi incident.</td></tr>
                                        <tr><td><strong>Incident Register</strong></td><td>Tabel incident dengan kolom Incident ID, Occurred, Risk Link, Severity, Status, Mitigation Verdict, Reporter, Last Update, Summary, dan Action.</td></tr>
                                    </tbody>
                                </table>
                            </div>

                            <div class="guide-section-title">Form Record New Incident dan Edit Incident</div>
                            <div class="table-responsive">
                                <table class="table table-sm table-bordered guide-table">
                                    <thead>
                                        <tr>
                                            <th>Field / tombol</th>
                                            <th>Yang diisi atau dilakukan</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr><td><strong>Related Risk</strong></td><td>Memilih risk yang terdampak incident.</td></tr>
                                        <tr><td><strong>Incident Title</strong></td><td>Judul singkat insiden.</td></tr>
                                        <tr><td><strong>Occurred At</strong></td><td>Waktu kejadian benar-benar terjadi.</td></tr>
                                        <tr><td><strong>Detected At</strong></td><td>Waktu insiden terdeteksi.</td></tr>
                                        <tr><td><strong>Severity</strong></td><td>Level keparahan incident.</td></tr>
                                        <tr><td><strong>Handling Status</strong></td><td>Status penanganan: open, investigating, mitigated, atau closed.</td></tr>
                                        <tr><td><strong>Mitigation Verdict</strong></td><td>Menilai apakah mitigasi tidak diuji, gagal, sebagian efektif, atau efektif.</td></tr>
                                        <tr><td><strong>Incident Description</strong></td><td>Deskripsi kronologi incident.</td></tr>
                                        <tr><td><strong>Business Impact (Optional)</strong></td><td>Dampak bisnis yang ditimbulkan incident.</td></tr>
                                        <tr><td><strong>Immediate Action (Optional)</strong></td><td>Tindakan darurat yang dilakukan segera.</td></tr>
                                        <tr><td><strong>Root Cause (Optional)</strong></td><td>Penyebab utama incident.</td></tr>
                                        <tr><td><strong>Corrective Action (Optional)</strong></td><td>Tindakan korektif permanen.</td></tr>
                                        <tr><td><strong>Resolution Notes (Optional)</strong></td><td>Catatan penutupan atau penyelesaian.</td></tr>
                                        <tr><td><strong>Closed At (Optional)</strong></td><td>Waktu incident dinyatakan selesai.</td></tr>
                                        <tr><td><strong>Save Incident</strong></td><td>Menyimpan incident baru.</td></tr>
                                        <tr><td><strong>Edit</strong></td><td>Membuka modal edit incident dengan field yang sama.</td></tr>
                                        <tr><td><strong>Delete</strong></td><td>Menghapus incident setelah konfirmasi.</td></tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @endif

                    <div class="guide-feature">
                        <div class="guide-feature-kicker"><i class="fas fa-globe"></i> {{ $isOperational ? 'Feature 2' : 'Feature 1' }}</div>
                        <div class="guide-feature-title">Threat Intelligence</div>
                        <div class="guide-feature-route">Threat Intel</div>
                        <div class="guide-section-title">Header dan filter working set</div>
                        <div class="table-responsive guide-tight">
                            <table class="table table-sm table-bordered guide-table">
                                <thead>
                                    <tr>
                                        <th>Box / field / tombol</th>
                                        <th>Fungsi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr><td><strong>Displayed CVEs</strong></td><td>Jumlah CVE yang sedang tampil pada working set.</td></tr>
                                    <tr><td><strong>KEV Match Ratio</strong></td><td>Persentase CVE yang overlap dengan CISA KEV.</td></tr>
                                    <tr><td><strong>High + Critical</strong></td><td>Jumlah item severity tinggi dan kritis pada working set.</td></tr>
                                    <tr><td><strong>Source Health</strong></td><td>Status feed sumber data, apakah sehat atau perlu perhatian.</td></tr>
                                    <tr><td><strong>Keyword</strong></td><td>Memfilter CVE berdasarkan produk, vendor, atau kata kunci tertentu.</td></tr>
                                    <tr><td><strong>Severity</strong></td><td>Memfilter severity CRITICAL, HIGH, MEDIUM, atau LOW.</td></tr>
                                    <tr><td><strong>OWASP</strong></td><td>Memfilter CVE berdasarkan mapping OWASP Top 10.</td></tr>
                                    <tr><td><strong>Window</strong></td><td>Memilih rentang data 7, 30, atau 90 hari.</td></tr>
                                    <tr><td><strong>Limit</strong></td><td>Membatasi jumlah item yang ditampilkan di halaman.</td></tr>
                                    <tr><td><strong>Apply</strong></td><td>Menerapkan filter working set.</td></tr>
                                </tbody>
                            </table>
                        </div>

                        <div class="guide-section-title">Tab explorer</div>
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered guide-table">
                                <thead>
                                    <tr>
                                        <th>Tab</th>
                                        <th>Yang ditampilkan</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr><td><strong>Current CVE Feed</strong></td><td>Daftar CVE terbaru dengan severity, KEV overlap, OWASP code, CVSS, tanggal publish/update, dan link reference.</td></tr>
                                    <tr><td><strong>Known Exploited Queue</strong></td><td>Item yang sudah diketahui dieksploitasi di dunia nyata.</td></tr>
                                    <tr><td><strong>Data Sources</strong></td><td>Status sumber seperti NVD CVE API dan CISA KEV.</td></tr>
                                    <tr><td><strong>Priority Queue</strong></td><td>Item yang paling layak ditindak dulu berdasarkan severity, KEV, dan exposure.</td></tr>
                                    <tr><td><strong>Analyst Console</strong></td><td>Ruang baca cepat untuk analisa manual dan prioritasi triage.</td></tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @endif

            @if($showComplianceAudit)
                <div class="tab-pane fade" id="guide-compliance-pane" role="tabpanel" aria-labelledby="guide-compliance-tab">
                    <div class="guide-pane-title">Audit dan Compliance</div>
                    <div class="guide-feature">
                        <div class="guide-feature-kicker"><i class="fas fa-clipboard-list"></i> Feature 1</div>
                        <div class="guide-feature-title">Audit Monitoring Dashboard</div>
                        <div class="guide-feature-route">Audit</div>
                        <p class="mb-2">Dashboard audit dipakai untuk monitoring temuan, filter periode, import spreadsheet, dan export bahan rapat.</p>

                        <div class="guide-section-title">Filter, tombol, dan tab utama</div>
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered guide-table">
                                <thead>
                                    <tr>
                                        <th>Label / tombol / tab</th>
                                        <th>Fungsi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr><td><strong>year</strong></td><td>Memfilter dashboard audit berdasarkan tahun.</td></tr>
                                    <tr><td><strong>bank</strong></td><td>Memfilter dashboard berdasarkan bank atau unit yang tersedia.</td></tr>
                                    <tr><td><strong>Apply</strong></td><td>Menerapkan filter year dan bank.</td></tr>
                                    <tr><td><strong>Reset</strong></td><td>Mengembalikan filter ke semua data.</td></tr>
                                    <tr><td><strong>Import XLS</strong></td><td>Mengunggah spreadsheet audit sesuai template.</td></tr>
                                    <tr><td><strong>Export</strong></td><td>Membuka pilihan export <strong>PDF</strong>, <strong>XLSX</strong>, dan <strong>PPTX</strong>.</td></tr>
                                    <tr><td><strong>Executive Summary</strong></td><td>Ringkasan manajemen dan intro kondisi audit.</td></tr>
                                    <tr><td><strong>Rekap Detail Temuan</strong></td><td>Merinci daftar temuan utama.</td></tr>
                                    <tr><td><strong>MAPS Temuan</strong></td><td>Membaca distribusi temuan berbasis maps.</td></tr>
                                    <tr><td><strong>Progress Temuan</strong></td><td>Memantau progres penyelesaian per status dan per bank.</td></tr>
                                    <tr><td><strong>Trend Temuan</strong></td><td>Melihat tren bulanan created date dan closed date.</td></tr>
                                    <tr><td><strong>Detail Outstanding</strong></td><td>Melihat resume outstanding dan detail per bank.</td></tr>
                                    <tr><td><strong>Duedate Maturity</strong></td><td>Mengamati due date maturity per bank, divisi, dan product.</td></tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="guide-feature">
                        <div class="guide-feature-kicker"><i class="fas fa-briefcase"></i> Feature 2</div>
                        <div class="guide-feature-title">Compliance Programs</div>
                        <div class="guide-feature-route">Compliance > Internal Audit / Risk Management / Governance & Compliance</div>
                        <p class="mb-2">Modul compliance dipakai untuk mengelola program kerja yang tidak tepat dicatat sebagai risk register.</p>

                        <div class="guide-section-title">Halaman daftar program kerja</div>
                        <div class="table-responsive guide-tight">
                            <table class="table table-sm table-bordered guide-table">
                                <thead>
                                    <tr>
                                        <th>Label / tombol / kolom</th>
                                        <th>Fungsi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr><td><strong>Add Program Kerja</strong></td><td>Membuka form program baru untuk section yang sedang aktif.</td></tr>
                                    <tr><td><strong>Actions</strong></td><td>Kolom action dengan tombol <strong>Edit</strong> dan <strong>Delete</strong>.</td></tr>
                                    <tr><td><strong>ID</strong></td><td>Nomor work item program.</td></tr>
                                    <tr><td><strong>Program / Key Initiative</strong></td><td>Nama inisiatif utama.</td></tr>
                                    <tr><td><strong>Description & Rationales</strong></td><td>Alasan dan konteks program.</td></tr>
                                    <tr><td><strong>Deliverables</strong></td><td>Output yang harus dihasilkan.</td></tr>
                                    <tr><td><strong>How to Measure</strong></td><td>Cara menilai keberhasilan program.</td></tr>
                                    <tr><td><strong>Budget</strong></td><td>Anggaran program.</td></tr>
                                    <tr><td><strong>Updated</strong></td><td>Waktu perubahan terakhir.</td></tr>
                                    <tr><td><strong>Suport Needed</strong></td><td>Dukungan tambahan yang dibutuhkan untuk eksekusi.</td></tr>
                                </tbody>
                            </table>
                        </div>

                        <div class="guide-section-title">Form Add / Edit Program Kerja</div>
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered guide-table">
                                <thead>
                                    <tr>
                                        <th>Field / tombol</th>
                                        <th>Yang diisi atau dilakukan</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr><td><strong>ID (Auto Generated)</strong></td><td>Nomor otomatis dari sistem.</td></tr>
                                    <tr><td><strong>Program / Key Initiative</strong></td><td>Nama program kerja utama.</td></tr>
                                    <tr><td><strong>Description & Rationales</strong></td><td>Penjelasan alasan program dibuat.</td></tr>
                                    <tr><td><strong>Deliverables</strong></td><td>Output atau sasaran kerja yang harus selesai.</td></tr>
                                    <tr><td><strong>How to Measure</strong></td><td>Metode atau indikator untuk mengukur keberhasilan.</td></tr>
                                    <tr><td><strong>Budget</strong></td><td>Nilai anggaran. Sistem menerima angka dan tanda baca.</td></tr>
                                    <tr><td><strong>Suport Needed</strong></td><td>Support tambahan dari unit lain bila ada.</td></tr>
                                    <tr><td><strong>Kembali</strong></td><td>Kembali ke daftar program section aktif.</td></tr>
                                    <tr><td><strong>Simpan Program Kerja</strong> / <strong>Update Program Kerja</strong></td><td>Menyimpan perubahan data.</td></tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    @if($showOjkMitigation)
                        <div class="guide-feature">
                            <div class="guide-feature-kicker"><i class="fas fa-landmark"></i> Feature 3</div>
                            <div class="guide-feature-title">Mitigasi OJK</div>
                            <div class="guide-feature-route">Mitigasi OJK</div>
                            <p class="mb-2">Modul ini hanya muncul kalau feature flag OJK aktif pada environment saat ini.</p>

                            <div class="table-responsive">
                                <table class="table table-sm table-bordered guide-table">
                                    <thead>
                                        <tr>
                                            <th>Label / tombol</th>
                                            <th>Fungsi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr><td><strong>Import XLS</strong></td><td>Mengunggah timeline mitigasi dari file excel.</td></tr>
                                        <tr><td><strong>Download Template</strong></td><td>Mengambil template import yang benar.</td></tr>
                                        <tr><td><strong>Edit</strong></td><td>Mengubah point mitigasi yang sudah ada.</td></tr>
                                        <tr><td><strong>Delete</strong></td><td>Menghapus point mitigasi setelah konfirmasi.</td></tr>
                                        <tr><td><strong>Action Plan</strong></td><td>Kolom rencana aksi mitigasi pada item OJK.</td></tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @endif
                </div>
            @endif

            @if($isSuperadmin)
                <div class="tab-pane fade" id="guide-superadmin-pane" role="tabpanel" aria-labelledby="guide-superadmin-tab">
                    <div class="guide-pane-title">Panduan Operasional Superadmin</div>
                    <div class="guide-feature">
                        <div class="guide-feature-kicker"><i class="fas fa-sitemap"></i> Feature 1</div>
                        <div class="guide-feature-title">App Management</div>
                        <div class="guide-feature-route">App Management</div>
                        <p class="mb-2">Dropdown ini merangkum modul administrasi inti agar superadmin tidak bercampur dengan workflow risk harian.</p>

                        <div class="table-responsive guide-tight">
                            <table class="table table-sm table-bordered guide-table">
                                <thead>
                                    <tr>
                                        <th>Submenu</th>
                                        <th>Dipakai untuk</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr><td><strong>User Management</strong></td><td>Verifikasi division user, promosi superadmin, dan reset password.</td></tr>
                                    <tr><td><strong>Parameter</strong></td><td>Kelola master data isian form risk nomor 1 sampai 10.</td></tr>
                                    <tr><td><strong>Corporate Risk Appetite</strong></td><td>Tambah, edit, dan mematikan framework appetite organisasi.</td></tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="guide-feature">
                        <div class="guide-feature-kicker"><i class="fas fa-users"></i> Feature 2</div>
                        <div class="guide-feature-title">User Management</div>
                        <div class="guide-feature-route">App Management > User Management</div>
                        <p class="mb-2">Halaman ini dipakai untuk aktivasi akun, penetapan division, dan kontrol status superadmin.</p>

                        <div class="table-responsive">
                            <table class="table table-sm table-bordered guide-table">
                                <thead>
                                    <tr>
                                        <th>Label / tombol / kolom</th>
                                        <th>Fungsi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr><td><strong>Status</strong></td><td>Filter user berdasarkan <strong>Pending Verification</strong>, <strong>Approved</strong>, atau <strong>All</strong>.</td></tr>
                                    <tr><td><strong>Search User</strong></td><td>Mencari nama, username, atau email.</td></tr>
                                    <tr><td><strong>Apply</strong> / <strong>Reset</strong></td><td>Menerapkan atau membersihkan filter daftar user.</td></tr>
                                    <tr><td><strong>User</strong></td><td>Kolom identitas berisi name, username, email, dan waktu join.</td></tr>
                                    <tr><td><strong>Division Status</strong></td><td>Menampilkan division current, requested, dan badge Verified/Pending.</td></tr>
                                    <tr><td><strong>Division Validation</strong></td><td>Dropdown division plus tombol <strong>Save</strong> untuk mengesahkan division user.</td></tr>
                                    <tr><td><strong>Superadmin</strong></td><td>Menampilkan badge Yes/No dan tombol <strong>Promote</strong> atau <strong>Revoke</strong>.</td></tr>
                                    <tr><td><strong>Reset Password</strong></td><td>Membuat temporary password baru untuk user yang dipilih. Tombol self-reset sengaja disabled untuk akun sendiri.</td></tr>
                                    <tr><td><strong>Temporary Password Generated</strong></td><td>Alert hasil reset yang menampilkan password sementara sekali saja sampai halaman di-refresh.</td></tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="guide-feature">
                        <div class="guide-feature-kicker"><i class="fas fa-sliders-h"></i> Feature 3</div>
                        <div class="guide-feature-title">Parameter Management</div>
                        <div class="guide-feature-route">App Management > Parameter</div>
                        <p class="mb-2">Halaman parameter dibagi ke tab 1 sampai 10 agar master data risk bisa dikelola tanpa scroll panjang.</p>

                        <div class="table-responsive">
                            <table class="table table-sm table-bordered guide-table">
                                <thead>
                                    <tr>
                                        <th>Tab parameter</th>
                                        <th>Label / tombol yang dikelola</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr><td><strong>1. Risk Category</strong></td><td><strong>Category Name</strong>, <strong>Add Risk Category</strong>, <strong>Edit</strong>, <strong>Save</strong>.</td></tr>
                                    <tr><td><strong>2. Risk Owner</strong></td><td><strong>Owner Label</strong>, <strong>Sort Order</strong>, switch <strong>Active</strong>, tombol <strong>Add</strong> dan <strong>Save</strong>.</td></tr>
                                    <tr><td><strong>3. Corporate Risk Appetite</strong></td><td>Shortcut ke modul framework appetite lewat tombol <strong>Open Corporate Risk Appetite</strong>.</td></tr>
                                    <tr><td><strong>4. Cause Category</strong></td><td><strong>Cause Category Name</strong>, <strong>Add Cause Category</strong>, <strong>Edit</strong>, <strong>Save</strong>.</td></tr>
                                    <tr><td><strong>5. Third-Party Vendor</strong></td><td>Shortcut ke modul vendor lewat tombol <strong>Open Third-Party Vendors</strong>.</td></tr>
                                    <tr><td><strong>6. Probability Description</strong></td><td><strong>Score</strong>, <strong>Description</strong>, <strong>Sort Order</strong>, switch <strong>Active</strong>, <strong>Add Probability Description</strong>, <strong>Edit</strong>, <strong>Save</strong>.</td></tr>
                                    <tr><td><strong>7. Impact Category</strong></td><td><strong>Impact Category Name</strong>, <strong>Add Impact Category</strong>, <strong>Edit</strong>, <strong>Save</strong>.</td></tr>
                                    <tr><td><strong>8. Impact Description</strong></td><td><strong>Impact Category</strong>, <strong>Score</strong>, <strong>Description</strong>, <strong>Add Impact Description</strong>, <strong>Edit</strong>, <strong>Save</strong>.</td></tr>
                                    <tr><td><strong>9. Mitigation Playbook</strong></td><td>Shortcut ke library playbook melalui tombol <strong>Open Mitigation Playbook</strong>.</td></tr>
                                    <tr><td><strong>10. Group Radar Description</strong></td><td><strong>Radar Code</strong>, <strong>Description</strong>, <strong>Add Radar Group</strong>, <strong>Edit</strong>, <strong>Save</strong>.</td></tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="guide-feature">
                        <div class="guide-feature-kicker"><i class="fas fa-shield-alt"></i> Feature 4</div>
                        <div class="guide-feature-title">Corporate Risk Appetite Management</div>
                        <div class="guide-feature-route">App Management > Corporate Risk Appetite</div>
                        <p class="mb-2">Superadmin mengelola framework appetite dari sini dan user operasional membacanya sebagai referensi.</p>

                        <div class="table-responsive guide-tight">
                            <table class="table table-sm table-bordered guide-table">
                                <thead>
                                    <tr>
                                        <th>Field / tombol</th>
                                        <th>Fungsi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr><td><strong>Add New Appetite Limit</strong></td><td>Membuka form deklarasi framework baru.</td></tr>
                                    <tr><td><strong>Appetite Name</strong></td><td>Nama framework appetite.</td></tr>
                                    <tr><td><strong>Category Filter (Optional)</strong></td><td>Mengaitkan framework ke kategori risk tertentu.</td></tr>
                                    <tr><td><strong>Threshold Metric</strong></td><td>Memilih metric threshold, kualitatif atau kuantitatif.</td></tr>
                                    <tr><td><strong>Appetite Statement</strong></td><td>Pernyataan kebijakan appetite yang akan dibaca risk owner.</td></tr>
                                    <tr><td><strong>Quantitative Target (Appetite)</strong></td><td>Target batas aman sebelum risk naik di atas appetite.</td></tr>
                                    <tr><td><strong>Maximum Tolerance Limit</strong></td><td>Batas pelanggaran maksimum.</td></tr>
                                    <tr><td><strong>Unit of Measure</strong></td><td>Satuan nilai, misalnya IDR, incidents, hours, atau Score.</td></tr>
                                    <tr><td><strong>Available for Selection (Active)</strong></td><td>Menentukan framework bisa dipilih di form risk atau tidak.</td></tr>
                                    <tr><td><strong>Save Declaration</strong></td><td>Menyimpan framework baru.</td></tr>
                                    <tr><td><strong>Edit</strong> / <strong>Edit Framework</strong></td><td>Mengubah framework dari daftar atau halaman detail.</td></tr>
                                    <tr><td><strong>Delete Framework</strong></td><td>Menghapus framework dari halaman edit.</td></tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="guide-feature">
                        <div class="guide-feature-kicker"><i class="fas fa-check-double"></i> Feature 5</div>
                        <div class="guide-feature-title">Status Approvals</div>
                        <div class="guide-feature-route">Status Approvals</div>
                        <p class="mb-2">Queue ini dipakai untuk menilai request perubahan status risk yang diajukan user operasional.</p>

                        <div class="table-responsive guide-tight">
                            <table class="table table-sm table-bordered guide-table">
                                <thead>
                                    <tr>
                                        <th>Label / tombol</th>
                                        <th>Fungsi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr><td><strong>Status</strong></td><td>Filter queue berdasarkan pending, approved, rejected, atau all.</td></tr>
                                    <tr><td><strong>Filter</strong></td><td>Menerapkan filter ke daftar request.</td></tr>
                                    <tr><td><strong>Requested At</strong>, <strong>Risk</strong>, <strong>Requester</strong>, <strong>From</strong>, <strong>To</strong>, <strong>Status</strong>, <strong>Reason</strong>, <strong>Review</strong></td><td>Kolom utama untuk membaca konteks perpindahan status.</td></tr>
                                    <tr><td><strong>Review note (optional)</strong></td><td>Catatan reviewer saat menyetujui request.</td></tr>
                                    <tr><td><strong>Approve</strong></td><td>Menyetujui perubahan status risk.</td></tr>
                                    <tr><td><strong>Review note (required)</strong></td><td>Catatan alasan saat request ditolak.</td></tr>
                                    <tr><td><strong>Reject</strong></td><td>Menolak perubahan status risk.</td></tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="guide-feature">
                        <div class="guide-feature-kicker"><i class="fas fa-bell"></i> Feature 6</div>
                        <div class="guide-feature-title">Alerts</div>
                        <div class="guide-feature-route">Alerts</div>
                        <p class="mb-2">Halaman alert membantu superadmin melihat breach, overdue, atau notifikasi governance yang perlu ditutup.</p>

                        <div class="table-responsive guide-tight">
                            <table class="table table-sm table-bordered guide-table">
                                <thead>
                                    <tr>
                                        <th>Label / tombol</th>
                                        <th>Fungsi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr><td><strong>Status</strong></td><td>Filter open, resolved, atau all.</td></tr>
                                    <tr><td><strong>Type</strong></td><td>Filter jenis alert yang ingin dilihat.</td></tr>
                                    <tr><td><strong>Filter</strong></td><td>Menerapkan filter alert.</td></tr>
                                    <tr><td><strong>Created</strong>, <strong>Risk</strong>, <strong>Treatment</strong>, <strong>Type</strong>, <strong>Severity</strong>, <strong>Status</strong>, <strong>Due Date</strong>, <strong>Message</strong></td><td>Kolom utama untuk membaca konteks alert.</td></tr>
                                    <tr><td><strong>Resolve</strong></td><td>Menutup alert open setelah tindak lanjut governance selesai.</td></tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="guide-feature">
                        <div class="guide-feature-kicker"><i class="fas fa-history"></i> Feature 7</div>
                        <div class="guide-feature-title">Audit Trail</div>
                        <div class="guide-feature-route">Audit Trail</div>
                        <p class="mb-2">Audit trail menampilkan jejak event penting sistem untuk kebutuhan evidencing dan investigasi.</p>

                        <div class="table-responsive">
                            <table class="table table-sm table-bordered guide-table">
                                <thead>
                                    <tr>
                                        <th>Label / tombol / kolom</th>
                                        <th>Fungsi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr><td><strong>Event</strong></td><td>Filter jenis event atau activity yang ingin dilihat.</td></tr>
                                    <tr><td><strong>Filter</strong></td><td>Menerapkan filter event.</td></tr>
                                    <tr><td><strong>Time</strong></td><td>Waktu aktivitas terjadi.</td></tr>
                                    <tr><td><strong>User</strong></td><td>Aktor yang menjalankan aktivitas.</td></tr>
                                    <tr><td><strong>Activity</strong></td><td>Nama aktivitas atau event label yang tercatat.</td></tr>
                                    <tr><td><strong>Detail</strong></td><td>Informasi ringkas yang menjelaskan perubahan yang terjadi.</td></tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="guide-note">
                        Superadmin sebaiknya dipakai untuk governance, approval, alert, dan administrasi sistem. Entry risk harian tetap lebih aman dilakukan oleh owner atau compliance yang memang punya scope operasional.
                    </div>
                </div>
            @endif

            <div class="tab-pane fade" id="guide-account-pane" role="tabpanel" aria-labelledby="guide-account-tab">
                <div class="guide-pane-title">Akun dan Keamanan</div>
                <div class="guide-feature">
                    <div class="guide-feature-kicker"><i class="fas fa-sign-in-alt"></i> Feature 1</div>
                    <div class="guide-feature-title">Login, profile, dan change password</div>
                    <div class="guide-feature-route">Dropdown akun kanan atas</div>
                    <p class="mb-2">Semua akun memakai username dan password. Menu akun ada di navbar kanan atas.</p>

                    <div class="table-responsive guide-tight">
                        <table class="table table-sm table-bordered guide-table">
                            <thead>
                                <tr>
                                    <th>Menu / field / tombol</th>
                                    <th>Fungsi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr><td><strong>Profile</strong></td><td>Membuka halaman profil untuk memperbarui nama tampilan akun.</td></tr>
                                <tr><td><strong>Change Password</strong></td><td>Membuka form penggantian password mandiri.</td></tr>
                                <tr><td><strong>Logout</strong></td><td>Mengakhiri sesi login saat ini.</td></tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="guide-section-title">Form Change Password</div>
                    <div class="table-responsive guide-tight">
                        <table class="table table-sm table-bordered guide-table">
                            <thead>
                                <tr>
                                    <th>Field / tombol</th>
                                    <th>Yang diisi atau dilakukan</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr><td><strong>Password Lama</strong></td><td>Password yang dipakai saat ini.</td></tr>
                                <tr><td><strong>Password Baru</strong></td><td>Password pengganti yang ingin dipakai.</td></tr>
                                <tr><td><strong>Konfirmasi Password Baru</strong></td><td>Pengulangan password baru agar tidak salah ketik.</td></tr>
                                <tr><td><strong>Update Password</strong></td><td>Menyimpan password baru setelah validasi lolos.</td></tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="guide-section-title">Aturan password</div>
                    <ol class="guide-flow">
                        <li>Minimal 8 karakter.</li>
                        <li>Gunakan kombinasi huruf dan angka.</li>
                        <li>Password baru tidak boleh sama dengan password lama.</li>
                        <li>Kalau password di-reset superadmin, user harus mengganti password setelah login.</li>
                    </ol>
                </div>

                <div class="guide-feature">
                    <div class="guide-feature-kicker"><i class="fas fa-tools"></i> Feature 2</div>
                    <div class="guide-feature-title">Troubleshooting cepat</div>
                    <div class="guide-feature-route">Masalah yang paling sering terjadi</div>
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered guide-table">
                            <thead>
                                <tr>
                                    <th>Masalah</th>
                                    <th>Apa yang dicek lebih dulu</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr><td>Tidak bisa login</td><td>Cek username, password, dan apakah password baru saja di-reset oleh superadmin.</td></tr>
                                <tr><td>Login berhasil tapi menu sedikit</td><td>Cek role akun dan division. Bisa jadi akun masih pending approval atau bukan compliance/superadmin.</td></tr>
                                <tr><td>Tidak bisa simpan di halaman tertentu</td><td>Cek apakah akun Anda memang punya hak tulis untuk object itu atau sedang berada di mode read-only.</td></tr>
                                <tr><td>Fitur compliance atau OJK tidak muncul</td><td>Cek division akun dan status feature flag environment.</td></tr>
                                <tr><td>User tidak tahu sedang login sebagai siapa</td><td>Lihat navbar kanan: baris 1 username, baris 2 role atau division aktif.</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
