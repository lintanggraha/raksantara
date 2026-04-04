@extends('layouts.tabler')

@section('title', 'User Guide - Risk Management System')

@push('styles')
<style>
    .guide-card {
        border: 1px solid #dbe4ef;
        border-radius: 14px;
        box-shadow: 0 10px 24px rgba(15, 23, 42, 0.06);
    }

    .guide-tabs {
        display: flex;
        flex-wrap: nowrap;
        gap: 0.45rem;
        overflow-x: auto;
        padding-bottom: 0.3rem;
        scrollbar-width: thin;
    }

    .guide-tabs .nav-link {
        border: 1px solid #c9d6e8;
        border-radius: 999px;
        color: #1f3a5f;
        font-weight: 600;
        white-space: nowrap;
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        padding: 0.42rem 0.85rem;
        background: #ffffff;
    }

    .guide-tabs .nav-link.active {
        color: #fff;
        background: #206bc4;
        border-color: #206bc4;
        box-shadow: 0 6px 14px rgba(32, 107, 196, 0.25);
    }

    .guide-pane-title {
        font-size: 1.02rem;
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

    .guide-note {
        background: #f7faff;
        border: 1px solid #dce7f8;
        border-radius: 10px;
        padding: 0.75rem 0.9rem;
        color: #1f3a5f;
        margin-top: 1rem;
    }

    .guide-table th,
    .guide-table td {
        vertical-align: top;
        font-size: 0.86rem;
    }

    .guide-table th {
        white-space: nowrap;
    }

    .guide-chip {
        display: inline-flex;
        align-items: center;
        border-radius: 999px;
        padding: 0.2rem 0.55rem;
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
</style>
@endpush

@section('content_header')
<div class="row align-items-center">
    <div class="col">
        <h2 class="page-title mb-1">User Guide</h2>
        <div class="text-muted">
            Panduan penggunaan fitur Risk Management System sesuai implementasi aplikasi saat ini.
        </div>
    </div>
</div>
@endsection

@section('content')
<div class="card guide-card">
    <div class="card-body">
        <ul class="nav guide-tabs mb-3" id="userGuideTab" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="guide-start-tab" data-bs-toggle="tab" data-bs-target="#guide-start-pane" type="button" role="tab" aria-controls="guide-start-pane" aria-selected="true">
                    <i class="fas fa-compass"></i> Mulai
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="guide-role-tab" data-bs-toggle="tab" data-bs-target="#guide-role-pane" type="button" role="tab" aria-controls="guide-role-pane" aria-selected="false">
                    <i class="fas fa-users-cog"></i> Role & Akses
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="guide-risk-tab" data-bs-toggle="tab" data-bs-target="#guide-risk-pane" type="button" role="tab" aria-controls="guide-risk-pane" aria-selected="false">
                    <i class="fas fa-exclamation-triangle"></i> Risk Workflow
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="guide-kri-tab" data-bs-toggle="tab" data-bs-target="#guide-kri-pane" type="button" role="tab" aria-controls="guide-kri-pane" aria-selected="false">
                    <i class="fas fa-chart-line"></i> KRI & Kanban
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="guide-incident-tab" data-bs-toggle="tab" data-bs-target="#guide-incident-pane" type="button" role="tab" aria-controls="guide-incident-pane" aria-selected="false">
                    <i class="fas fa-bolt"></i> Incident & Threat
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="guide-compliance-tab" data-bs-toggle="tab" data-bs-target="#guide-compliance-pane" type="button" role="tab" aria-controls="guide-compliance-pane" aria-selected="false">
                    <i class="fas fa-briefcase"></i> Compliance & Audit
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="guide-report-tab" data-bs-toggle="tab" data-bs-target="#guide-report-pane" type="button" role="tab" aria-controls="guide-report-pane" aria-selected="false">
                    <i class="fas fa-file-export"></i> Report & Scheduler
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="guide-superadmin-tab" data-bs-toggle="tab" data-bs-target="#guide-superadmin-pane" type="button" role="tab" aria-controls="guide-superadmin-pane" aria-selected="false">
                    <i class="fas fa-shield-alt"></i> Superadmin
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="guide-account-tab" data-bs-toggle="tab" data-bs-target="#guide-account-pane" type="button" role="tab" aria-controls="guide-account-pane" aria-selected="false">
                    <i class="fas fa-user-lock"></i> Akun & Keamanan
                </button>
            </li>
        </ul>

        <div class="tab-content" id="userGuideTabContent">
            <div class="tab-pane fade show active" id="guide-start-pane" role="tabpanel" aria-labelledby="guide-start-tab">
                <div class="guide-pane-title">Panduan Cepat Sesuai Kebutuhan</div>
                <p class="mb-2">
                    Halaman ini menjelaskan menu yang aktif dan tampil di aplikasi saat ini. Nama menu, tombol, dan alur di bawah disesuaikan dengan implementasi terbaru agar pengguna tidak perlu menebak langkah berikutnya.
                </p>

                <div class="table-responsive mb-2">
                    <table class="table table-sm table-bordered guide-table">
                        <thead>
                            <tr>
                                <th>Jika Anda ingin...</th>
                                <th>Buka menu / URL</th>
                                <th>Yang akan Anda dapatkan</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>Melihat prioritas risiko organisasi secara cepat</td>
                                <td><strong>Risk &gt; Risk Monitoring</strong><br><code>/risk-monitoring</code></td>
                                <td>Dashboard ringkas untuk overview, KPI mitigasi, distribusi status, benchmark, dan root cause analysis.</td>
                            </tr>
                            <tr>
                                <td>Membuat atau memperbarui entri risk</td>
                                <td><strong>Risk &gt; Risk Register</strong><br><code>/risks</code></td>
                                <td>Workspace utama untuk Add Risk, Edit Risk, View detail, import, filter, dan export register.</td>
                            </tr>
                            <tr>
                                <td>Mendalami satu risk sampai level evidence dan governance</td>
                                <td><strong>View</strong> pada baris risk</td>
                                <td>Tab detail risk: Basic, Inherent, Treatment, RCSA, Residual, BCM, Governance, dan Key Risk Indicators.</td>
                            </tr>
                            <tr>
                                <td>Mengelola indikator peringatan dini</td>
                                <td><strong>Risk &gt; Key Risk Indicators</strong><br><code>/kris</code></td>
                                <td>Daftar KRI, status Healthy/Warning/Critical, threshold, dan log nilai terbaru.</td>
                            </tr>
                            <tr>
                                <td>Menggerakkan action mitigasi harian</td>
                                <td><strong>Risk &gt; Mitigation Kanban</strong><br><code>/mitigation-kanban</code></td>
                                <td>Board drag-and-drop untuk treatment status: Not Started, In Progress, Blocked, Completed.</td>
                            </tr>
                            <tr>
                                <td>Melihat owner yang paling sibuk atau paling butuh perhatian</td>
                                <td><strong>Risk &gt; Owner Workload</strong><br><code>/owners/workload</code></td>
                                <td>Skor beban owner berdasarkan open actions, overdue, blocked items, pending approvals, dan red KRIs.</td>
                            </tr>
                            <tr>
                                <td>Menggunakan template mitigasi berulang</td>
                                <td><strong>Risk &gt; Mitigation Playbooks</strong><br><code>/treatment-templates</code></td>
                                <td>Library playbook, checklist evidence, target deadline, default PIC, dan status aktif/nonaktif.</td>
                            </tr>
                            <tr>
                                <td>Mengisi BIA atau BCP</td>
                                <td><strong>Risk &gt; Business Continuity (BCM)</strong><br><code>/bcm</code></td>
                                <td>Register BIA/BCP dan shortcut ke tab BCM di detail risk.</td>
                            </tr>
                            <tr>
                                <td>Mengelola vendor pihak ketiga</td>
                                <td><strong>Third-Party Vendors</strong><br><code>/vendors</code></td>
                                <td>Daftar vendor, status, criticality, contact person, dan associated risks yang terlihat dalam scope Anda.</td>
                            </tr>
                            <tr>
                                <td>Melihat kerangka appetite korporat</td>
                                <td><strong>Risk &gt; Corporate Risk Appetite</strong><br><code>/risk-appetites</code></td>
                                <td>Daftar appetite statement, target, tolerance, dan status aktif.</td>
                            </tr>
                            <tr>
                                <td>Mencatat insiden dan kaitannya ke risk</td>
                                <td><strong>Risk &gt; Incident Management</strong><br><code>/incident-management</code></td>
                                <td>Register incident, severity, handling status, root cause, corrective action, dan resolution notes.</td>
                            </tr>
                            <tr>
                                <td>Melakukan triage ancaman eksternal</td>
                                <td><strong>Threat Intel</strong><br><code>/threat-intelligence</code></td>
                                <td>Feed CVE, CISA KEV, priority queue, dan analyst console.</td>
                            </tr>
                            <tr>
                                <td>Menjalankan audit atau program compliance</td>
                                <td><strong>Audit</strong> dan <strong>Compliance</strong></td>
                                <td>Dashboard audit, import/export audit, dan program kerja Internal Audit, Risk Management, Governance &amp; Compliance.</td>
                            </tr>
                            <tr>
                                <td>Mengirim laporan otomatis berkala</td>
                                <td><code>/reports/scheduled</code></td>
                                <td>Scheduler email report untuk monitoring, board, risks, heatmap, recap, dan overall.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="guide-section-title">Urutan Belajar yang Disarankan</div>
                <ol class="mb-2">
                    <li>Login, lalu cek navbar untuk memastikan role dan divisi Anda sudah sesuai.</li>
                    <li>Mulai dari <strong>Risk Monitoring</strong> untuk melihat portfolio secara umum.</li>
                    <li>Lanjut ke <strong>Risk Register</strong> untuk mencari, membuat, atau membuka detail risk.</li>
                    <li>Masuk ke modul operasional sesuai kebutuhan: <strong>KRI</strong>, <strong>Mitigation Kanban</strong>, <strong>BCM</strong>, <strong>Incident Management</strong>, atau <strong>Third-Party Vendors</strong>.</li>
                    <li>Jika perlu distribusi rutin ke manajemen, buat jadwal di <code>/reports/scheduled</code>.</li>
                </ol>

                <div class="guide-note">
                    Jika menu yang Anda cari tidak muncul, penyebab paling umum adalah akun masih <strong>pending verification</strong>, role akun belum sesuai, atau data Anda berada di luar scope owner/divisi yang boleh diakses.
                </div>
            </div>

            <div class="tab-pane fade" id="guide-role-pane" role="tabpanel" aria-labelledby="guide-role-tab">
                <div class="guide-pane-title">Role, Scope, dan Hak Tulis</div>
                <div class="table-responsive">
                    <table class="table table-sm table-bordered guide-table">
                        <thead>
                            <tr>
                                <th>Role</th>
                                <th>Menu utama yang tersedia</th>
                                <th>Scope data</th>
                                <th>Catatan hak tulis</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><span class="guide-chip role">Superadmin</span></td>
                                <td>Dashboard, User Management, Status Approvals, Alerts, Audit Trail, Threat Intel, User Guide</td>
                                <td>Governance level</td>
                                <td>Read-only untuk workflow Risk Register dan Incident Management. Superadmin difokuskan untuk approval, audit trail, dan administrasi sistem.</td>
                            </tr>
                            <tr>
                                <td><span class="guide-chip role">Verified User</span></td>
                                <td>Risk Monitoring, Risk Register, Trend Analysis, KRI, Mitigation Kanban, Owner Workload, Mitigation Playbooks, Incident Management, BCM, Corporate Risk Appetite, Third-Party Vendors, Threat Intel, User Guide, Scheduled Reports via URL</td>
                                <td>Dibatasi owner/divisi yang diizinkan untuk akun tersebut</td>
                                <td>Dapat membuat dan mengubah risk dalam scope-nya, termasuk BIA/BCP, KRI, treatment, komentar, attachment, incident, dan vendor association.</td>
                            </tr>
                            <tr>
                                <td><span class="guide-chip role">Compliance Division</span></td>
                                <td>Semua menu verified user + Audit + Compliance + import lintas owner</td>
                                <td>Lintas owner / lintas divisi sesuai kewenangan compliance</td>
                                <td>Dapat melakukan import risk register dan audit, melihat dashboard lintas owner, serta mengelola program compliance.</td>
                            </tr>
                            <tr>
                                <td><span class="guide-chip role">Pending Approval</span></td>
                                <td>Halaman pending approval</td>
                                <td>Tidak ada akses operasional</td>
                                <td>Tidak bisa masuk ke modul risk sebelum diverifikasi oleh superadmin.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="guide-note">
                    Catatan penting:
                    <ul class="mb-0 mt-2">
                        <li>Non-compliance hanya melihat risk, BIA/BCP, vendor-associated risk, dan report sesuai scope owner/divisi.</li>
                        <li>Compliance dapat melihat data lintas owner dan menjalankan import compliance.</li>
                        <li><strong>Corporate Risk Appetite</strong> dapat dilihat lebih luas, tetapi tombol add/edit hanya tersedia untuk superadmin.</li>
                        <li><strong>Scheduled Reports</strong> aktif di <code>/reports/scheduled</code> untuk user verified non-superadmin walaupun link belum tampil di navbar.</li>
                    </ul>
                </div>
            </div>

            <div class="tab-pane fade" id="guide-risk-pane" role="tabpanel" aria-labelledby="guide-risk-tab">
                <div class="guide-pane-title">Risk Lifecycle End-to-End</div>

                <div class="guide-section-title">1) Risk Monitoring (<code>/risk-monitoring</code>)</div>
                <ol class="mb-2">
                    <li>Buka menu <strong>Risk &gt; Risk Monitoring</strong>.</li>
                    <li>Pilih tab dashboard sesuai tujuan analisa: overview, status/core matrix, atau root cause analysis.</li>
                    <li>Gunakan <strong>Customize Widgets</strong> untuk menampilkan widget yang relevan untuk meeting Anda.</li>
                    <li>Geser urutan widget dengan ikon grip jika ingin menyusun dashboard sesuai preferensi pribadi.</li>
                    <li>Gunakan tombol export untuk membuat bahan rapat atau board pack.</li>
                </ol>

                <div class="table-responsive mb-2">
                    <table class="table table-sm table-bordered guide-table">
                        <thead>
                            <tr>
                                <th>Tab dashboard</th>
                                <th>Widget utama</th>
                                <th>Dipakai untuk</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><strong>Dashboard 1 - Risk Overview</strong></td>
                                <td>Mitigation, SLA &amp; KPI Dashboard; Organizational Risk Overview</td>
                                <td>Menilai efektivitas mitigasi, SLA response/closure, dan performa per divisi.</td>
                            </tr>
                            <tr>
                                <td><strong>Dashboard 2 - Risk Status &amp; Core Matrix</strong></td>
                                <td>Status &amp; Core Metrics; Portfolio Command Center; Benchmark Scoring</td>
                                <td>Melihat total risk, distribusi status, top risk, dan gap terhadap target/benchmark.</td>
                            </tr>
                            <tr>
                                <td><strong>Dashboard 3 - Risk Root Cause Analysis</strong></td>
                                <td>Root Cause Analysis; Variance Analysis</td>
                                <td>Mengidentifikasi penyebab dominan dan selisih inherent vs residual per kategori.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="guide-note">
                    Preferensi widget dan urutan dashboard disimpan per user. Mengubah tampilan dashboard tidak mengubah data sumber.
                </div>

                <div class="guide-section-title">2) Risk Register Workspace (<code>/risks</code>)</div>
                <ul>
                    <li>Bagian atas menampilkan summary card total risk dan distribusi level residual.</li>
                    <li>Workspace ini memiliki 3 tampilan utama: <strong>Risk Register</strong>, <strong>Risk Heat Map</strong>, dan <strong>Risk Recapitulation</strong>.</li>
                    <li>Halaman ini adalah titik masuk utama untuk pencarian risk, Add Risk, Edit Risk, export, dan import.</li>
                </ul>

                <div class="table-responsive mb-2">
                    <table class="table table-sm table-bordered guide-table">
                        <thead>
                            <tr>
                                <th>Workspace</th>
                                <th>Nama di UI</th>
                                <th>Fungsi utama</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><strong>Dashboard 1</strong></td>
                                <td>Risk Register</td>
                                <td>Tabel kerja harian untuk filter, search, preset kolom, edit, delete, view, import, dan export.</td>
                            </tr>
                            <tr>
                                <td><strong>Dashboard 2</strong></td>
                                <td>Risk Heat Map</td>
                                <td>Pemetaan matrix 5x5 antara likelihood dan impact untuk inherent serta residual risk.</td>
                            </tr>
                            <tr>
                                <td><strong>Dashboard 3</strong></td>
                                <td>Risk Recapitulation</td>
                                <td>Rekap agregat per radar code, summary score, dan quantitative totals.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="table-responsive mb-2">
                    <table class="table table-sm table-bordered guide-table">
                        <thead>
                            <tr>
                                <th>Tombol / kontrol</th>
                                <th>Fungsi</th>
                                <th>Catatan</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><strong>Filter Owner</strong></td>
                                <td>Menyaring daftar risk berdasarkan owner/divisi yang tersedia di scope Anda.</td>
                                <td>Sering dipakai saat review per unit kerja.</td>
                            </tr>
                            <tr>
                                <td><strong>Preset Kolom</strong></td>
                                <td>Mengganti tampilan tabel menjadi Overview, Full, Analysis, Treatment, atau Quantitative.</td>
                                <td>Cocok untuk audience yang berbeda tanpa mengubah data.</td>
                            </tr>
                            <tr>
                                <td><strong>Search / Sorting / Pagination</strong></td>
                                <td>Menelusuri dataset yang besar dengan cepat.</td>
                                <td>Gunakan saat register sudah memiliki banyak entri.</td>
                            </tr>
                            <tr>
                                <td><strong>Export Buttons</strong></td>
                                <td>Mengekspor register sesuai filter yang sedang aktif.</td>
                                <td>Tersedia format PDF, XLSX, atau PPTX sesuai konteks report.</td>
                            </tr>
                            <tr>
                                <td><strong>Import XLS</strong> (compliance)</td>
                                <td>Bulk import risk register dengan opsi dry-run, override year, dan download template.</td>
                                <td>Digunakan untuk onboarding data atau update massal.</td>
                            </tr>
                            <tr>
                                <td><strong>Add Risk</strong></td>
                                <td>Membuka form risk baru.</td>
                                <td>Tidak tersedia untuk superadmin karena workflow risk bersifat read-only untuk role tersebut.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="guide-section-title">3) Action pada baris Risk Register</div>
                <ul>
                    <li><strong>View</strong>: membuka detail risk lengkap untuk review mendalam, evidence, komentar, BCM, dan KRI.</li>
                    <li><strong>Edit</strong>: mengubah entri risk jika akun Anda punya hak kelola.</li>
                    <li><strong>Delete</strong>: menghapus risk jika akun Anda punya hak kelola.</li>
                    <li><strong>Chip KRI</strong>: shortcut langsung ke tab <strong>Key Risk Indicators</strong> pada risk tersebut.</li>
                </ul>

                <div class="guide-section-title">4) Add Risk dan Edit Risk</div>
                <ul>
                    <li>Form <strong>Add Risk</strong> dan <strong>Edit Risk</strong> sama-sama menggunakan 6 section: <strong>Risk</strong>, <strong>Inherent Analysis</strong>, <strong>Risk Treatment</strong>, <strong>Residual Analysis</strong>, <strong>Group Radar</strong>, dan <strong>Quantitative Result</strong>.</li>
                    <li>Pada section <strong>Risk</strong>, pengguna mengisi Process, Risk Owner, Risk Category, Cause Category, Third-Party Vendor, Sasaran Target, Risk Identification, Cause, dan Existing Control.</li>
                    <li><strong>Risk ID</strong> dan <strong>Year</strong> dihasilkan atau ditetapkan sistem sehingga pengguna tidak perlu mengetik manual.</li>
                    <li>Section <strong>Risk Treatment</strong> mendukung pemilihan <strong>Mitigation Playbook</strong>, pengisian PIC, target deadline, effectiveness, dan checklist evidence.</li>
                    <li>Section <strong>Quantitative Result</strong> menghitung potential loss berdasarkan impact x probability untuk inherent dan residual, lalu tetap bisa disesuaikan jika diperlukan.</li>
                    <li>Di form create tersedia tombol <strong>SAVE</strong> dan <strong>SAVE &amp; CONTINUE TO BCM</strong>.</li>
                    <li>Di form edit tersedia tombol <strong>UPDATE</strong> dan <strong>UPDATE &amp; OPEN BCM</strong>.</li>
                    <li><strong>Third-Party Vendor</strong> sudah tersedia di form create dan edit.</li>
                    <li><strong>Corporate Risk Appetite</strong> sekarang tersedia di form add dan edit risk. Jika dipilih dan framework-nya kuantitatif, threshold scanner akan memakai appetite tersebut lebih dulu.</li>
                </ul>

                <div class="guide-section-title">5) Detail Risk: tab dan fungsinya</div>
                <div class="table-responsive mb-2">
                    <table class="table table-sm table-bordered guide-table">
                        <thead>
                            <tr>
                                <th>Tab di detail risk</th>
                                <th>Apa yang dilakukan di tab ini</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><strong>Basic</strong></td>
                                <td>Melihat identitas risk, owner, category, vendor terkait, sasaran, dan deskripsi dasar.</td>
                            </tr>
                            <tr>
                                <td><strong>Inherent</strong></td>
                                <td>Melihat skor, level, dan analisa sebelum treatment diterapkan.</td>
                            </tr>
                            <tr>
                                <td><strong>Treatment</strong></td>
                                <td>Melihat daftar treatment, progress, evidence, playbook yang dipakai, dan target action.</td>
                            </tr>
                            <tr>
                                <td><strong>RCSA</strong></td>
                                <td>Ringkasan workspace RCSA dan shortcut ke halaman RCSA Workspace yang lebih lengkap.</td>
                            </tr>
                            <tr>
                                <td><strong>Residual</strong></td>
                                <td>Melihat skor, level, dan analisa sesudah treatment diterapkan.</td>
                            </tr>
                            <tr>
                                <td><strong>BCM (BIA/BCP)</strong></td>
                                <td>Mengisi Business Impact Analysis terlebih dahulu, lalu Business Continuity Plan untuk risk yang sama.</td>
                            </tr>
                            <tr>
                                <td><strong>Governance</strong></td>
                                <td>Mengelola risk evidence, comments, activity log, status approval history, dan risk alerts.</td>
                            </tr>
                            <tr>
                                <td><strong>Key Risk Indicators</strong></td>
                                <td>Melihat, menambah, mengubah, atau mencatat nilai KRI milik risk tersebut.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="guide-note">
                    Di area governance, komentar mendukung mention user seperti <strong>@username</strong>. Lampiran evidence dan status approval history juga dicatat di sana agar jejak review tidak tersebar.
                </div>

                <div class="guide-section-title">6) RCSA Workspace (<code>/risks/{id}/rcsa</code>)</div>
                <ul>
                    <li>RCSA Workspace dipakai untuk mendokumentasikan konteks assessment, key controls, control gaps, residual rationale, dan sign-off.</li>
                    <li>Blok form utamanya adalah <strong>Assessment Context</strong>, <strong>Control Assessment</strong>, <strong>Residual Rationale</strong>, dan <strong>Sign-Off</strong>.</li>
                    <li>Field penting yang biasa diisi: Process Owner, Review Frequency, Next Review Date, Objective, Scope, Key Controls, Control Gaps, Likelihood Rationale, Impact Rationale, dan Signed-Off Date.</li>
                    <li>Jika akun Anda tidak punya hak kelola pada risk tersebut, halaman ini tetap bisa dilihat tetapi mode-nya read-only.</li>
                </ul>

                <div class="guide-section-title">7) Business Continuity Management / BCM (<code>/bcm</code>)</div>
                <ul>
                    <li>Halaman BCM menampilkan register BIA/BCP dengan ringkasan <strong>Total BIAs</strong>, <strong>Tier 1 (Critical)</strong>, <strong>Tested BCPs</strong>, dan <strong>Missing BCPs</strong>.</li>
                    <li>Tombol <strong>Conduct BIA</strong> menampilkan modal untuk memilih risk yang belum memiliki BIA.</li>
                    <li>Pada register BCM, filter <strong>All Tiers / Tier 1 / Tier 2 / Tier 3 / Tier 4</strong> membantu fokus ke proses paling kritis.</li>
                    <li>Setiap baris BCM memiliki tombol <strong>View</strong> yang mengarah ke detail risk pada tab <strong>#bcm</strong>.</li>
                    <li>Di detail risk, isi form <strong>BIA</strong> terlebih dahulu: RTO, RPO, MAO, financial impact, operational impact, regulatory impact, reputational impact, criticality tier, dan justification notes.</li>
                    <li>Form <strong>BCP</strong> baru aktif setelah BIA tersimpan. Field utamanya mencakup strategy summary, recovery procedures, primary workaround, testing frequency, last tested date, dan last test result.</li>
                    <li>Alur paling cepat untuk pengguna baru adalah membuat risk, lalu gunakan tombol <strong>SAVE &amp; CONTINUE TO BCM</strong> atau <strong>UPDATE &amp; OPEN BCM</strong>.</li>
                </ul>

                <div class="guide-section-title">8) Third-Party Vendors (<code>/vendors</code>)</div>
                <ul>
                    <li>Halaman vendor menampilkan daftar vendor dengan kolom nama, contact, criticality level, status, dan action view/edit.</li>
                    <li>Saat membuat atau mengedit vendor, isi minimal informasi identitas vendor, contact person, email, status, dan criticality level.</li>
                    <li>Detail vendor menampilkan <strong>Associated Risks</strong>, yaitu risk yang terhubung ke vendor tersebut dan masih berada dalam scope akses Anda.</li>
                    <li>Dari detail risk, vendor yang terhubung akan muncul di tab Basic sehingga user bisa berpindah dari risk ke detail vendor.</li>
                    <li>Field vendor juga tersedia di form Add Risk dan Edit Risk agar risk pihak ketiga bisa ditandai sejak awal.</li>
                </ul>

                <div class="guide-section-title">9) Corporate Risk Appetite (<code>/risk-appetites</code>)</div>
                <ul>
                    <li>Halaman ini menampilkan framework appetite organisasi: kategori, nama appetite, appetite statement, appetite target, tolerance limit, dan status active/inactive.</li>
                    <li>Semua user yang memiliki akses menu dapat melihat daftar appetite, tetapi tombol <strong>Add New Appetite Limit</strong> dan tombol edit hanya tersedia untuk superadmin.</li>
                    <li>Jika appetite bersifat kuantitatif, halaman ini menampilkan nilai target appetite dan batas maksimum tolerance.</li>
                    <li>Framework appetite dapat di-link langsung dari form add/edit risk. Jika metriknya kuantitatif, appetite target dan tolerance akan menjadi sumber threshold untuk risk yang terhubung.</li>
                </ul>

                <div class="guide-section-title">10) Risk Trend Analysis (<code>/trend-analysis</code>)</div>
                <ul>
                    <li>Pilih granularity <strong>Monthly</strong>, <strong>Quarterly</strong>, atau <strong>Yearly</strong>.</li>
                    <li>Pilih rentang periode 3 bulan, 6 bulan, 1 tahun, atau 2 tahun sesuai horizon review.</li>
                    <li>Panel utamanya meliputi Risk Score Trend, Risk Score by Category, Status Distribution, Owner Concentration, dan Heatmap High-Risk Trend.</li>
                    <li>Setiap panel dapat di-drag untuk mengubah urutan tampilan sesuai preferensi pengguna.</li>
                </ul>
            </div>

            <div class="tab-pane fade" id="guide-kri-pane" role="tabpanel" aria-labelledby="guide-kri-tab">
                <div class="guide-pane-title">KRI, Mitigation Kanban, Owner Workload, dan Playbooks</div>

                <div class="guide-section-title">1) KRI Dashboard (<code>/kris</code>)</div>
                <ul>
                    <li>Dashboard KRI menampilkan ringkasan <strong>Healthy</strong>, <strong>Warning</strong>, <strong>Critical</strong>, dan <strong>Total</strong>.</li>
                    <li>Gunakan filter status dan risk untuk fokus ke indikator yang paling membutuhkan tindak lanjut.</li>
                    <li>Tombol <strong>Add KRI</strong> hanya bisa dipakai jika ada risk dalam scope akses Anda.</li>
                    <li>Setelah import risk register, sistem dapat membuat atau memperbarui KRI otomatis bertipe <strong>Auto: Residual Risk Score</strong>.</li>
                </ul>

                <div class="guide-section-title">2) KRI pada detail risk</div>
                <ul>
                    <li>Tab <strong>Key Risk Indicators</strong> di detail risk dipakai untuk membuat KRI baru, mengubah threshold, menghapus KRI, dan mencatat nilai terbaru.</li>
                    <li>Status dihitung otomatis berdasarkan arah threshold:
                        <span class="guide-chip info">lower is better</span>
                        atau
                        <span class="guide-chip info">higher is better</span>.
                    </li>
                    <li>Gunakan tab ini saat indikator memang spesifik untuk satu risk dan perlu direview bersama treatment risk tersebut.</li>
                </ul>

                <div class="guide-section-title">3) Mitigation Kanban (<code>/mitigation-kanban</code>)</div>
                <ul>
                    <li>Setiap card mewakili treatment action yang berasal dari risk register.</li>
                    <li>Card dipindah antar kolom status melalui drag-and-drop.</li>
                    <li>Kolom status yang tersedia adalah <strong>Not Started</strong>, <strong>In Progress</strong>, <strong>Blocked</strong>, dan <strong>Completed</strong>.</li>
                    <li>Perubahan status tersimpan otomatis ke database dan langsung memengaruhi dashboard workload maupun monitoring.</li>
                </ul>

                <div class="guide-section-title">4) Owner Workload Dashboard (<code>/owners/workload</code>)</div>
                <ul>
                    <li>Dashboard ini merangkum owner yang paling membutuhkan perhatian berdasarkan jumlah risk, open actions, overdue actions, blocked actions, pending approvals, dan red KRIs.</li>
                    <li>Kartu summary di bagian atas menampilkan total owner dalam scope, open actions, overdue, blocked, pending approvals, dan red KRIs.</li>
                    <li>Bagian <strong>Highest Attention Owner</strong> menunjukkan owner dengan workload score tertinggi saat ini.</li>
                    <li>Tabel <strong>Owner Queue</strong> sudah diurutkan berdasarkan skor tertinggi agar user bisa langsung memprioritaskan owner yang paling berat bebannya.</li>
                </ul>

                <div class="guide-section-title">5) Mitigation Playbooks (<code>/treatment-templates</code>)</div>
                <ul>
                    <li>Playbook adalah template treatment yang bisa dipakai ulang untuk risk serupa.</li>
                    <li>Setiap playbook dapat memiliki nama, risk owner, treatment option, default action description, default PIC, effectiveness, target deadline, next action offset, checklist, dan status active/inactive.</li>
                    <li>Halaman ini juga menampilkan preview checklist agar user bisa menilai isi playbook sebelum dipakai.</li>
                    <li>Di form Add Risk atau Edit Risk, playbook digunakan pada section treatment untuk mempercepat pengisian action yang berulang.</li>
                    <li>Jika akun Anda read-only untuk playbook management, library tetap bisa dilihat tetapi form create/edit tidak tersedia.</li>
                </ul>

                <div class="guide-note">
                    Jika tombol <strong>Add KRI</strong> muncul tetapi dropdown risk kosong, biasanya berarti tidak ada risk yang berada dalam scope akses akun Anda.
                </div>
            </div>

            <div class="tab-pane fade" id="guide-incident-pane" role="tabpanel" aria-labelledby="guide-incident-tab">
                <div class="guide-pane-title">Incident Management dan Threat Intelligence</div>

                <div class="guide-section-title">1) Incident Management (<code>/incident-management</code>)</div>
                <ul>
                    <li>Setiap incident wajib dikaitkan ke risk agar jejak peristiwa tetap nyambung dengan register risiko.</li>
                    <li>Halaman incident menampilkan summary total incident, active, closed, failed, dan affected risks.</li>
                    <li>Grafik trend 6 bulan dan distribusi mitigation verdict membantu review cepat terhadap kualitas penanganan.</li>
                    <li>Field yang tersedia di form incident meliputi severity, handling status, mitigation verdict, occurred time, detected time, closed time, impact, root cause, corrective action, dan resolution notes.</li>
                    <li>Gunakan incident management untuk kejadian operasional nyata, bukan untuk action mitigasi biasa.</li>
                    <li>Superadmin hanya dapat melihat dashboard incident secara read-only.</li>
                </ul>

                <div class="guide-section-title">2) Threat Intelligence (<code>/threat-intelligence</code>)</div>
                <ul>
                    <li>Gunakan filter keyword, severity, days, dan limit untuk memperkecil ruang triage.</li>
                    <li>Tab utama yang tersedia adalah <strong>Current CVE Feed</strong>, <strong>Known Exploited Queue</strong>, <strong>Data Sources</strong>, <strong>Priority Queue</strong>, dan <strong>Analyst Console</strong>.</li>
                    <li>Data source utama berasal dari <strong>NVD CVE API</strong> dan <strong>CISA KEV catalog</strong>.</li>
                    <li>Working set threat intel dapat diperbarui otomatis saat aplikasi dibuka kembali jika interval refresh sudah terlewati.</li>
                </ul>

                <div class="guide-note">
                    Jika feed kosong atau tidak berubah, cek koneksi internet/outbound ke endpoint NVD dan CISA, lalu refresh kembali beberapa menit kemudian.
                </div>
            </div>

            <div class="tab-pane fade" id="guide-compliance-pane" role="tabpanel" aria-labelledby="guide-compliance-tab">
                <div class="guide-pane-title">Compliance dan Audit</div>

                <div class="guide-section-title">1) Audit Dashboard (<code>/audit</code>)</div>
                <ul>
                    <li>Modul ini ditujukan untuk divisi compliance.</li>
                    <li>Audit dashboard mendukung import spreadsheet audit dan export PDF, XLS, XLSX, serta PPTX.</li>
                    <li>Template import audit dapat diunduh dari modal <strong>Import XLS</strong>. Pastikan sheet utama mengikuti format <strong>SUMMARY UPDATE</strong>.</li>
                    <li>Gunakan audit dashboard untuk rekap tindak lanjut audit, monitoring progress, dan distribusi status temuan.</li>
                </ul>

                <div class="guide-section-title">2) Compliance Programs (<code>/compliance/*</code>)</div>
                <ul>
                    <li>Section yang tersedia adalah <strong>Internal Audit</strong>, <strong>Risk Management</strong>, dan <strong>Governance &amp; Compliance</strong>.</li>
                    <li>Masing-masing section mendukung CRUD program kerja.</li>
                    <li>Gunakan modul ini untuk menyimpan rencana kerja compliance yang tidak tepat dimasukkan sebagai risk register.</li>
                </ul>

                <div class="guide-section-title">3) Fitur lintas owner untuk compliance</div>
                <ul>
                    <li>Compliance dapat melakukan import risk register lintas owner dari halaman register sesuai template yang disediakan sistem.</li>
                    <li>Compliance juga memiliki akses ke dashboard audit dan program compliance yang tidak tampil untuk user biasa.</li>
                    <li>Jika Anda adalah compliance tetapi menu audit atau compliance tidak muncul, periksa kembali division pada akun Anda.</li>
                </ul>

                @if(config('features.show_ojk_mitigation', false))
                    <div class="guide-section-title">4) Mitigasi OJK (<code>/ojk-mitigation</code>)</div>
                    <ul>
                        <li>Modul ini hanya tampil jika feature flag OJK diaktifkan.</li>
                        <li>Mendukung import timeline XLS/XLSX, edit, update, delete, dan download template.</li>
                    </ul>
                @endif
            </div>

            <div class="tab-pane fade" id="guide-report-pane" role="tabpanel" aria-labelledby="guide-report-tab">
                <div class="guide-pane-title">Reporting Manual dan Scheduled Reports</div>

                <div class="guide-section-title">1) Manual Export</div>
                <ul>
                    <li>Tombol export tersedia di halaman monitoring, risk register, heat map, dan recapitulation sesuai konteks layar.</li>
                    <li>Format export manual yang tersedia adalah PDF, XLSX, dan PPTX.</li>
                    <li>Scope report yang dipakai sistem mencakup <strong>monitoring</strong>, <strong>board</strong>, <strong>risks</strong>, <strong>heatmap</strong>, <strong>recap</strong>, dan <strong>overall</strong>.</li>
                    <li>Jika Anda sudah menerapkan filter di layar, export biasanya mengikuti konteks filter tersebut.</li>
                </ul>

                <div class="guide-section-title">2) Scheduled Reports (<code>/reports/scheduled</code>)</div>
                <ul>
                    <li>Fitur ini ditujukan untuk user verified non-superadmin dan saat ini diakses melalui URL langsung.</li>
                    <li>Ketika membuat schedule baru, isi minimal: nama schedule, scope, format, frequency, jam kirim, timezone, dan daftar email penerima.</li>
                    <li>Format yang tersedia adalah <strong>PDF</strong>, <strong>XLSX</strong>, dan <strong>PPTX</strong>.</li>
                    <li>Frekuensi yang tersedia adalah <strong>Daily</strong>, <strong>Weekly</strong>, dan <strong>Monthly</strong>.</li>
                    <li>Filter opsional yang tersedia mencakup year, risk category, cause category, dan risk owner.</li>
                    <li>Setelah schedule tersimpan, user dapat <strong>Edit</strong>, <strong>Run Now</strong>, <strong>Pause/Activate</strong>, atau <strong>Delete</strong>.</li>
                    <li>Tabel schedule menampilkan <strong>Next Run</strong>, <strong>Last Delivery</strong>, status aktif, dan ringkasan run terbaru.</li>
                </ul>

                <div class="guide-note">
                    Scheduled Reports cocok untuk board pack atau executive snapshot berkala. Jika route menolak akses, penyebab paling umum adalah akun masih belum verified atau akun menggunakan role superadmin.
                </div>
            </div>

            <div class="tab-pane fade" id="guide-superadmin-pane" role="tabpanel" aria-labelledby="guide-superadmin-tab">
                <div class="guide-pane-title">Fitur Operasional Superadmin</div>

                <div class="guide-section-title">1) User Management (<code>/users</code>)</div>
                <ul>
                    <li>Menyetujui akun yang masih pending dan menentukan division pengguna.</li>
                    <li>Mengubah status superadmin user tertentu bila diperlukan.</li>
                    <li>Melakukan reset password user lain dengan password sementara yang dihasilkan sistem.</li>
                </ul>

                <div class="guide-section-title">2) Status Approvals (<code>/risk-status-approvals</code>)</div>
                <ul>
                    <li>Meninjau request perubahan status risk dari user operasional.</li>
                    <li>Aksi yang tersedia adalah <strong>Approve</strong> atau <strong>Reject</strong> beserta catatan review.</li>
                </ul>

                <div class="guide-section-title">3) Alerts (<code>/risk-alerts</code>)</div>
                <ul>
                    <li>Memantau alert open dan resolved.</li>
                    <li>Menandai alert sebagai resolved setelah tindakan governance selesai.</li>
                </ul>

                <div class="guide-section-title">4) Audit Trail (<code>/audit-logs</code>)</div>
                <ul>
                    <li>Menelusuri aktivitas penting sistem untuk kebutuhan governance, investigasi, dan evidencing.</li>
                </ul>

                <div class="guide-note">
                    Superadmin sebaiknya dipakai untuk administrasi sistem dan approval, bukan untuk data entry risk harian, karena workflow risk dan incident memang dibuat read-only untuk role ini.
                </div>
            </div>

            <div class="tab-pane fade" id="guide-account-pane" role="tabpanel" aria-labelledby="guide-account-tab">
                <div class="guide-pane-title">Akun, Login, dan Keamanan</div>

                <div class="guide-section-title">1) Login dan registrasi</div>
                <ul>
                    <li>Login menggunakan username dan password.</li>
                    <li>Registrasi publik mengikuti konfigurasi <code>ALLOW_PUBLIC_REGISTRATION</code>. Secara default biasanya nonaktif.</li>
                    <li>Jika registrasi aktif, akun baru tetap menunggu verifikasi division oleh superadmin sebelum dapat masuk ke modul operasional.</li>
                </ul>

                <div class="guide-section-title">2) Forgot / Reset Password</div>
                <ul>
                    <li>Form reset password meminta username akun.</li>
                    <li>Jika password di-reset oleh superadmin, pengguna akan diminta mengganti password setelah berhasil login.</li>
                </ul>

                <div class="guide-section-title">3) Password policy</div>
                <ul>
                    <li>Minimal 8 karakter.</li>
                    <li>Wajib mengandung kombinasi huruf dan angka.</li>
                    <li>Password baru tidak boleh sama dengan password lama.</li>
                </ul>

                <div class="guide-section-title">4) Profile</div>
                <ul>
                    <li>Menu profile dipakai untuk memperbarui nama tampilan user.</li>
                    <li>Menu change password dipakai untuk mengganti password secara mandiri.</li>
                </ul>

                <div class="guide-section-title">5) Troubleshooting login singkat</div>
                <ul>
                    <li>Jika login gagal, periksa username dan password terlebih dahulu.</li>
                    <li>Jika berhasil login tetapi hanya melihat halaman pending, artinya akun belum diverifikasi.</li>
                    <li>Jika menu terasa kurang lengkap, cek kembali role dan division akun.</li>
                </ul>

                <div class="guide-note">
                    URL dokumentasi cepat: <strong>/user-guide</strong>. Alias lama <strong>/how-to</strong> otomatis redirect ke halaman ini.
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
