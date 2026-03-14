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
                <div class="guide-pane-title">Quick Start untuk Pengguna Baru</div>
                <ol class="mb-2">
                    <li>Login menggunakan username dan password.</li>
                    <li>Cek menu navbar untuk memastikan role/divisi Anda sudah benar.</li>
                    <li>Buka <strong>Risk Monitoring</strong> untuk melihat ringkasan prioritas.</li>
                    <li>Buka <strong>Risk Register</strong> untuk analisa detail per risiko.</li>
                    <li>Gunakan <strong>View</strong> di baris risk untuk membuka detail, evidence, komentar, dan KRI.</li>
                    <li>Gunakan <strong>Risk Trend Analysis</strong> untuk melihat arah pergerakan risk lintas periode.</li>
                    <li>Gunakan <strong>KRI</strong>, <strong>Mitigation Kanban</strong>, dan <strong>Incident Management</strong> untuk operasional harian.</li>
                    <li>Gunakan <strong>Threat Intel</strong> untuk triage CVE/KEV.</li>
                    <li>Jika butuh distribusi laporan berkala, buka <strong>/reports/scheduled</strong> lewat URL langsung.</li>
                </ol>

                <div class="guide-note">
                    Jika menu yang Anda butuhkan tidak muncul, penyebab paling umum adalah akun masih <strong>pending verification</strong> atau divisi akun belum sesuai.
                </div>
            </div>

            <div class="tab-pane fade" id="guide-role-pane" role="tabpanel" aria-labelledby="guide-role-tab">
                <div class="guide-pane-title">Matriks Akses</div>
                <div class="table-responsive">
                    <table class="table table-sm table-bordered guide-table">
                        <thead>
                            <tr>
                                <th>Role</th>
                                <th>Menu yang Tampil</th>
                                <th>Hak Tulis</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><span class="guide-chip role">Superadmin</span></td>
                                <td>Dashboard, User Management, Status Approvals, Alerts, Audit Trail, Threat Intel</td>
                                <td>Read-only untuk Risk Register dan Incident Management</td>
                            </tr>
                            <tr>
                                <td><span class="guide-chip role">Verified User</span></td>
                                <td>Risk (Monitoring, Register, Trend, KRI, Mitigation Kanban, Incident), Threat Intel, User Guide, Scheduled Reports via URL langsung</td>
                                <td>Kelola risk dalam scope owner/divisi</td>
                            </tr>
                            <tr>
                                <td><span class="guide-chip role">Compliance Division</span></td>
                                <td>Semua menu verified + Audit + Mitigasi OJK + Compliance + import lintas owner</td>
                                <td>Kelola data lintas owner + fitur import compliance</td>
                            </tr>
                            <tr>
                                <td><span class="guide-chip role">Pending Approval</span></td>
                                <td>Halaman pending approval</td>
                                <td>Tidak bisa akses modul risk sebelum diverifikasi</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="guide-note">
                    Scope data non-compliance dibatasi oleh owner/divisi. Compliance dapat melihat lintas owner. Scheduled Reports aktif di <strong>/reports/scheduled</strong> untuk user verified non-superadmin meskipun link belum tampil di navbar.
                </div>
            </div>

            <div class="tab-pane fade" id="guide-risk-pane" role="tabpanel" aria-labelledby="guide-risk-tab">
                <div class="guide-pane-title">Risk Monitoring dan Risk Register (Detail Fungsi)</div>

                <div class="guide-section-title">A) Risk Monitoring (`/risk-monitoring`)</div>
                <ol class="mb-2">
                    <li>Buka menu <strong>Risk > Risk Monitoring</strong>.</li>
                    <li>Pilih dashboard tab sesuai tujuan analisa (Dashboard 1/2/3).</li>
                    <li>Jika perlu, klik <strong>Customize Widgets</strong> untuk show/hide komponen.</li>
                    <li>Drag card memakai ikon grip untuk mengubah urutan widget.</li>
                    <li>Gunakan tombol export untuk mengirim laporan manajerial.</li>
                </ol>

                <div class="table-responsive mb-2">
                    <table class="table table-sm table-bordered guide-table">
                        <thead>
                            <tr>
                                <th>Kontrol</th>
                                <th>Fungsi</th>
                                <th>Catatan Operasional</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><strong>Dashboard 1 / 2 / 3</strong></td>
                                <td>Berpindah fokus analisa monitoring.</td>
                                <td>Tab hanya mengubah tampilan widget, bukan mengubah data sumber.</td>
                            </tr>
                            <tr>
                                <td><strong>Customize Widgets</strong></td>
                                <td>Show/hide widget sesuai kebutuhan meeting.</td>
                                <td>Preferensi visibilitas disimpan per user.</td>
                            </tr>
                            <tr>
                                <td><strong>Drag Widget (ikon grip)</strong></td>
                                <td>Mengatur urutan widget di layar.</td>
                                <td>Urutan disimpan per user.</td>
                            </tr>
                            <tr>
                                <td><strong>Export Buttons</strong></td>
                                <td>Export dashboard monitoring (PDF/XLSX/PPTX sesuai opsi).</td>
                                <td>Gunakan saat monthly review atau komite risiko.</td>
                            </tr>
                            <tr>
                                <td><strong>Add Risk</strong></td>
                                <td>Shortcut membuat risk baru.</td>
                                <td>Tidak tampil untuk superadmin (read-only di risk workflow).</td>
                            </tr>
                            <tr>
                                <td><strong>Auto Refresh Summary</strong></td>
                                <td>Memperbarui angka KPI dan widget secara berkala.</td>
                                <td>Waktu refresh mengikuti konfigurasi frontend.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="guide-section-title">Mapping Seluruh Dashboard di Risk Monitoring</div>
                <div class="table-responsive mb-2">
                    <table class="table table-sm table-bordered guide-table">
                        <thead>
                            <tr>
                                <th>Dashboard Tab</th>
                                <th>Widget</th>
                                <th>Penjelasan Singkat Output</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><strong>Dashboard 1 - Risk Overview</strong></td>
                                <td>Mitigation, SLA & KPI Dashboard</td>
                                <td>Efektivitas mitigasi, SLA target vs actual, dan KPI response/closure/overdue.</td>
                            </tr>
                            <tr>
                                <td><strong>Dashboard 1 - Risk Overview</strong></td>
                                <td>Organizational Risk Overview</td>
                                <td>Performa per divisi: jumlah risk, prioritas inherent/residual, treatment stats, dan score.</td>
                            </tr>
                            <tr>
                                <td><strong>Dashboard 2 - Risk Status & Core Matrix</strong></td>
                                <td>Status & Core Metrics</td>
                                <td>Total risk, status Open/Monitoring/Closed, avg score, dan variance global.</td>
                            </tr>
                            <tr>
                                <td><strong>Dashboard 2 - Risk Status & Core Matrix</strong></td>
                                <td>Portfolio Command Center</td>
                                <td>Donut distribution (priority, category, division, treatment timeline, status) + top risk table.</td>
                            </tr>
                            <tr>
                                <td><strong>Dashboard 2 - Risk Status & Core Matrix</strong></td>
                                <td>Benchmark Scoring</td>
                                <td>Gap internal vs industry/target, posisi benchmark, sumber referensi, metodologi, dan alasan posisi.</td>
                            </tr>
                            <tr>
                                <td><strong>Dashboard 3 - Risk Root Cause Analysis</strong></td>
                                <td>Root Cause Analysis</td>
                                <td>Top cause category, recurring keywords, pattern insights + recommendation.</td>
                            </tr>
                            <tr>
                                <td><strong>Dashboard 3 - Risk Root Cause Analysis</strong></td>
                                <td>Variance Analysis (Inherent vs Residual)</td>
                                <td>Per kategori: risk count, avg inherent, avg residual, dan selisih variance.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="guide-section-title">B) Risk Register Workspace (`/risks`)</div>
                <ul>
                    <li>Bagian atas menampilkan summary card: Total Risks + distribusi level residual (Low s.d High).</li>
                    <li>Halaman ini punya 3 dashboard workspace: Register, Heat Map, dan Recapitulation.</li>
                    <li>Kolom inti workspace Register memuat Risk ID, Year, Process, Sasaran Target, dan Risk Identification.</li>
                </ul>

                <div class="table-responsive mb-2">
                    <table class="table table-sm table-bordered guide-table">
                        <thead>
                            <tr>
                                <th>Dashboard Workspace</th>
                                <th>Nama di UI</th>
                                <th>Fungsi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><strong>Dashboard 1</strong></td>
                                <td>Risk Register</td>
                                <td>Tabel detail semua risiko untuk operasi harian (filter, sort, edit, export).</td>
                            </tr>
                            <tr>
                                <td><strong>Dashboard 2</strong></td>
                                <td>Risk Heat Map</td>
                                <td>Visual matrix 5x5 inherent vs residual + pemetaan risk event.</td>
                            </tr>
                            <tr>
                                <td><strong>Dashboard 3</strong></td>
                                <td>Risk Recapitulation</td>
                                <td>Rekap agregat per radar code + detail recap + quantitative totals.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="guide-section-title">Detail Fungsi Dashboard 1 - Risk Register</div>
                <div class="table-responsive mb-2">
                    <table class="table table-sm table-bordered guide-table">
                        <thead>
                            <tr>
                                <th>Kontrol / Tombol</th>
                                <th>Fungsi</th>
                                <th>Kapan Dipakai</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><strong>Filter Owner</strong></td>
                                <td>Menyaring baris risiko berdasarkan owner/divisi.</td>
                                <td>Saat review risiko per unit kerja.</td>
                            </tr>
                            <tr>
                                <td><strong>Export Buttons</strong></td>
                                <td>Export data register.</td>
                                <td>Saat butuh snapshot untuk rapat dan arsip.</td>
                            </tr>
                            <tr>
                                <td><strong>Import XLS</strong> (compliance)</td>
                                <td>Bulk import risk register dari file spreadsheet, termasuk opsi dry-run dan year override.</td>
                                <td>Saat onboarding data atau update batch.</td>
                            </tr>
                            <tr>
                                <td><strong>Monitoring</strong></td>
                                <td>Pindah cepat ke halaman Risk Monitoring.</td>
                                <td>Saat perlu lihat KPI agregat sebelum drill down.</td>
                            </tr>
                            <tr>
                                <td><strong>Add Risk</strong></td>
                                <td>Buat entri risk baru.</td>
                                <td>Saat ada risk event baru yang belum terdaftar.</td>
                            </tr>
                            <tr>
                                <td><strong>Preset Kolom</strong></td>
                                <td>Ganti tampilan kolom: Overview / Full / Analysis / Treatment / Quantitative.</td>
                                <td>Saat menyesuaikan tampilan untuk audience berbeda.</td>
                            </tr>
                            <tr>
                                <td><strong>Search + Sorting + Pagination</strong></td>
                                <td>Pencarian cepat, urutkan kolom, dan navigasi data.</td>
                                <td>Saat dataset risk sudah besar.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="guide-section-title">Action per Baris Risk Register</div>
                <ul>
                    <li><strong>View</strong>: membuka detail risk lengkap.</li>
                    <li><strong>Edit</strong>: mengubah risk (hanya jika user punya hak kelola).</li>
                    <li><strong>Delete</strong>: menghapus risk (hanya jika user punya hak kelola).</li>
                    <li><strong>Chip KRI</strong>: shortcut ke tab KRI pada detail risk.</li>
                </ul>

                <div class="guide-section-title">Detail Fungsi Dashboard 2 - Risk Heat Map</div>
                <ul>
                    <li><strong>Matrix 5x5 (Likelihood vs Impact)</strong>: memetakan posisi risiko pada skala 1-5.</li>
                    <li><strong>Marker Inherent vs Residual</strong>: chip hitam = inherent, chip putih = residual.</li>
                    <li><strong>Risk Event Mapping</strong>: tabel kode/category/event/owner dengan link ke detail risk.</li>
                    <li><strong>Export Heat Map</strong>: tersedia di toolbar atas panel heatmap.</li>
                </ul>

                <div class="guide-section-title">Detail Fungsi Dashboard 3 - Risk Recapitulation</div>
                <ul>
                    <li><strong>Filter Year</strong>: menyaring data recap per tahun.</li>
                    <li><strong>Summary Recap (By Radar Code)</strong>: agregasi n, sigma inherent, sigma residual, dan risk adjustment.</li>
                    <li><strong>Detailed Recap</strong>: rata-rata skor inherent/residual + evaluasi level + potential loss inherent/residual.</li>
                    <li><strong>Export Recap</strong>: tersedia langsung di panel filter recap.</li>
                </ul>

                <div class="guide-section-title">C) Add/Edit Risk dan Detail Risk</div>
                <ul>
                    <li>Form Add/Edit terdiri dari 6 tab: Basic, Inherent, Treatment, Residual, Group Radar, Quantitative Result.</li>
                    <li>Tab Basic memuat <strong>Risk ID</strong> dan <strong>Year</strong> yang digenerate sistem, serta field <strong>Sasaran Target</strong>.</li>
                    <li>Tab Quantitative Result memuat impact, probability, dan potential loss untuk inherent/residual; nilai potential loss akan terisi otomatis dari impact x probability tetapi masih bisa disesuaikan.</li>
                    <li>Risk detail berisi governance lengkap: evidence, comments, activity log, status approval history, alerts, dan tab KRI.</li>
                    <li>Komentar mendukung mention user (`@username`) dan attachment.</li>
                </ul>

                <div class="guide-section-title">D) Risk Trend Analysis (`/trend-analysis`)</div>
                <ul>
                    <li>Pilih granularity <strong>Monthly</strong>, <strong>Quarterly</strong>, atau <strong>Yearly</strong>.</li>
                    <li>Pilih rentang periode: 3 bulan, 6 bulan, 1 tahun, atau 2 tahun.</li>
                    <li>Panel utama meliputi Risk Score Trend, Risk Score by Category, Status Distribution, Owner Concentration, dan Heatmap High-Risk Trend.</li>
                    <li>Setiap panel bisa di-drag untuk mengubah urutan tampilan sesuai preferensi pengguna.</li>
                </ul>
            </div>

            <div class="tab-pane fade" id="guide-kri-pane" role="tabpanel" aria-labelledby="guide-kri-tab">
                <div class="guide-pane-title">Key Risk Indicators dan Mitigation Kanban</div>

                <div class="guide-section-title">KRI Dashboard (`/kris`)</div>
                <ul>
                    <li>Ringkasan KRI: Healthy, Warning, Critical, Total.</li>
                    <li>Filter berdasarkan status dan risk.</li>
                    <li><strong>Add KRI</strong> memerlukan risk yang bisa diakses user.</li>
                    <li>Setelah import risk register berhasil, sistem dapat membuat atau memperbarui KRI otomatis bertipe <strong>Auto: Residual Risk Score</strong>.</li>
                </ul>

                <div class="guide-section-title">KRI pada Detail Risk</div>
                <ul>
                    <li>Tab KRI di detail risk dipakai untuk edit threshold, delete KRI, dan log nilai terbaru.</li>
                    <li>Status otomatis dihitung dari direction threshold:
                        <span class="guide-chip info">lower is better</span> atau
                        <span class="guide-chip info">higher is better</span>.
                    </li>
                </ul>

                <div class="guide-section-title">Mitigation Kanban (`/mitigation-kanban`)</div>
                <ul>
                    <li>Card treatment dipindah antar kolom status via drag-and-drop.</li>
                    <li>Kolom status tersedia: Not Started, In Progress, Blocked, Completed.</li>
                    <li>Perubahan status disimpan otomatis ke database.</li>
                </ul>

                <div class="guide-note">
                    Jika tombol <strong>Add KRI</strong> muncul tapi tidak bisa input risk, berarti tidak ada risk dalam scope akses Anda.
                </div>
            </div>

            <div class="tab-pane fade" id="guide-incident-pane" role="tabpanel" aria-labelledby="guide-incident-tab">
                <div class="guide-pane-title">Incident Management dan Threat Intelligence</div>

                <div class="guide-section-title">Incident Management (`/incident-management`)</div>
                <ul>
                    <li>Incident wajib dikaitkan ke risk.</li>
                    <li>Tersedia ringkasan total/active/closed/failed/affected risks.</li>
                    <li>Chart trend 6 bulan dan distribusi mitigation verdict tersedia untuk review cepat.</li>
                    <li>Form incident mencakup severity, handling status, mitigation verdict, occurred/detected/closed time, impact, root cause, corrective action, dan resolution notes.</li>
                    <li>Superadmin hanya dapat melihat dashboard incident (read-only).</li>
                </ul>

                <div class="guide-section-title">Threat Intelligence (`/threat-intelligence`)</div>
                <ul>
                    <li>Gunakan filter keyword, severity, days, dan limit untuk memperkecil triage scope.</li>
                    <li>Tab utama: Current CVE Feed, Known Exploited Queue, Data Sources, Priority Queue, Analyst Console.</li>
                    <li>Data source berasal dari NVD CVE API dan CISA KEV catalog.</li>
                    <li>Working set threat intel dapat di-refresh otomatis saat user membuka aplikasi jika interval refresh sudah terlewati.</li>
                </ul>

                <div class="guide-note">
                    Jika feed kosong, cek koneksi internet/outbound ke endpoint NVD dan CISA, lalu coba refresh setelah beberapa menit.
                </div>
            </div>

            <div class="tab-pane fade" id="guide-compliance-pane" role="tabpanel" aria-labelledby="guide-compliance-tab">
                <div class="guide-pane-title">Compliance, Audit, dan OJK</div>

                <div class="guide-section-title">Audit Dashboard (`/audit`)</div>
                <ul>
                    <li>Khusus divisi compliance.</li>
                    <li>Mendukung import spreadsheet audit dan export PDF/XLS/XLSX/PPTX.</li>
                </ul>

                <div class="guide-section-title">Mitigasi OJK (`/ojk-mitigation`)</div>
                <ul>
                    <li>Khusus divisi compliance.</li>
                    <li>Mendukung import timeline XLS/XLSX, edit, update, dan delete item mitigasi.</li>
                </ul>

                <div class="guide-section-title">Compliance Programs (`/compliance/*`)</div>
                <ul>
                    <li>Section yang tersedia: Internal Audit, Risk Management, Governance & Compliance.</li>
                    <li>Masing-masing section mendukung CRUD program kerja.</li>
                </ul>
            </div>

            <div class="tab-pane fade" id="guide-report-pane" role="tabpanel" aria-labelledby="guide-report-tab">
                <div class="guide-pane-title">Reporting Manual dan Scheduled Reports</div>

                <div class="guide-section-title">Manual Export</div>
                <ul>
                    <li>Tombol export tersedia di halaman Monitoring, Risk Register, Heat Map, dan Recapitulation sesuai konteks halaman.</li>
                    <li>Format export manual dapat berupa PDF, XLS, XLSX, atau PPTX tergantung scope report yang dipilih.</li>
                    <li>Scope report yang didukung sistem meliputi monitoring, board, risks, heatmap, recap, dan overall.</li>
                </ul>

                <div class="guide-section-title">Scheduled Reports (`/reports/scheduled`)</div>
                <ul>
                    <li>Fitur ini untuk user verified non-superadmin dan diakses lewat URL langsung karena link belum tampil di navbar.</li>
                    <li>Saat membuat schedule, isi nama schedule, scope, format, frekuensi, jam kirim, timezone, dan email penerima (pisahkan dengan koma).</li>
                    <li>Format scheduled report: PDF, XLSX, dan PPTX. Frekuensi: Daily, Weekly, Monthly.</li>
                    <li>Filter opsional yang tersedia: year, risk category, cause category, dan risk owner.</li>
                    <li>Setelah schedule tersimpan, Anda dapat <strong>Edit</strong>, <strong>Run Now</strong>, <strong>Pause/Activate</strong>, dan <strong>Delete</strong>.</li>
                    <li>Tabel schedule menampilkan Next Run, Last Delivery, status aktif, dan histori run terbaru.</li>
                </ul>

                <div class="guide-note">
                    Scheduled Reports cocok untuk board pack atau executive snapshot berkala. Jika route menolak akses, biasanya karena akun adalah superadmin atau masih belum verified.
                </div>
            </div>

            <div class="tab-pane fade" id="guide-superadmin-pane" role="tabpanel" aria-labelledby="guide-superadmin-tab">
                <div class="guide-pane-title">Fitur Operasional Superadmin</div>

                <div class="guide-section-title">1) User Management (`/users`)</div>
                <ul>
                    <li>Approve divisi user pending.</li>
                    <li>Promote/demote status superadmin.</li>
                    <li>Reset password user lain (sistem generate temporary password).</li>
                </ul>

                <div class="guide-section-title">2) Status Approvals (`/risk-status-approvals`)</div>
                <ul>
                    <li>Review request perubahan status risk dari user operasional.</li>
                    <li>Action: Approve atau Reject dengan catatan review.</li>
                </ul>

                <div class="guide-section-title">3) Alerts (`/risk-alerts`)</div>
                <ul>
                    <li>Monitor alert open/resolved.</li>
                    <li>Resolve alert yang sudah ditindaklanjuti.</li>
                </ul>

                <div class="guide-section-title">4) Audit Trail (`/audit-logs`)</div>
                <ul>
                    <li>Lacak aktivitas penting sistem untuk kebutuhan governance dan investigasi.</li>
                </ul>
            </div>

            <div class="tab-pane fade" id="guide-account-pane" role="tabpanel" aria-labelledby="guide-account-tab">
                <div class="guide-pane-title">Akun, Login, dan Kebijakan Password</div>

                <div class="guide-section-title">Login & Registrasi</div>
                <ul>
                    <li>Login menggunakan username + password.</li>
                    <li>Registrasi publik tergantung konfigurasi `ALLOW_PUBLIC_REGISTRATION` (default nonaktif).</li>
                    <li>Jika registrasi aktif, akun baru tetap menunggu verifikasi divisi oleh superadmin.</li>
                </ul>

                <div class="guide-section-title">Forgot / Reset Password</div>
                <ul>
                    <li>Form reset meminta username akun.</li>
                    <li>Jika reset via superadmin, user akan diminta mengganti password setelah login.</li>
                </ul>

                <div class="guide-section-title">Password Policy</div>
                <ul>
                    <li>Minimal 8 karakter.</li>
                    <li>Wajib kombinasi huruf dan angka.</li>
                    <li>Password baru tidak boleh sama dengan password lama.</li>
                </ul>

                <div class="guide-section-title">Profile</div>
                <ul>
                    <li>Menu profile untuk update nama.</li>
                    <li>Menu change password untuk ganti password mandiri.</li>
                </ul>

                <div class="guide-note">
                    URL dokumentasi cepat: <strong>/user-guide</strong>. Alias lama <strong>/how-to</strong> otomatis redirect ke halaman ini.
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
