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
        font-size: 0.85rem;
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

    .guide-chip.page {
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
            Panduan penggunaan aplikasi dari login sampai operasi harian, per halaman, dan per tombol.
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
                    <i class="fas fa-users-cog"></i> Akses & Role
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="guide-auth-tab" data-bs-toggle="tab" data-bs-target="#guide-auth-pane" type="button" role="tab" aria-controls="guide-auth-pane" aria-selected="false">
                    <i class="fas fa-sign-in-alt"></i> Login & Akun
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="guide-nav-tab" data-bs-toggle="tab" data-bs-target="#guide-nav-pane" type="button" role="tab" aria-controls="guide-nav-pane" aria-selected="false">
                    <i class="fas fa-map-signs"></i> Navigasi
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="guide-monitoring-tab" data-bs-toggle="tab" data-bs-target="#guide-monitoring-pane" type="button" role="tab" aria-controls="guide-monitoring-pane" aria-selected="false">
                    <i class="fas fa-chart-area"></i> Risk Monitoring
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="guide-workspace-tab" data-bs-toggle="tab" data-bs-target="#guide-workspace-pane" type="button" role="tab" aria-controls="guide-workspace-pane" aria-selected="false">
                    <i class="fas fa-table"></i> Risk Workspace
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="guide-form-tab" data-bs-toggle="tab" data-bs-target="#guide-form-pane" type="button" role="tab" aria-controls="guide-form-pane" aria-selected="false">
                    <i class="fas fa-edit"></i> Add / Edit Risk
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="guide-detail-tab" data-bs-toggle="tab" data-bs-target="#guide-detail-pane" type="button" role="tab" aria-controls="guide-detail-pane" aria-selected="false">
                    <i class="fas fa-folder-open"></i> Detail Risk
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="guide-trend-tab" data-bs-toggle="tab" data-bs-target="#guide-trend-pane" type="button" role="tab" aria-controls="guide-trend-pane" aria-selected="false">
                    <i class="fas fa-chart-line"></i> Trend Analysis
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="guide-scheduled-tab" data-bs-toggle="tab" data-bs-target="#guide-scheduled-pane" type="button" role="tab" aria-controls="guide-scheduled-pane" aria-selected="false">
                    <i class="fas fa-clock"></i> Scheduled Reports
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="guide-compliance-tab" data-bs-toggle="tab" data-bs-target="#guide-compliance-pane" type="button" role="tab" aria-controls="guide-compliance-pane" aria-selected="false">
                    <i class="fas fa-briefcase"></i> Compliance
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="guide-audit-tab" data-bs-toggle="tab" data-bs-target="#guide-audit-pane" type="button" role="tab" aria-controls="guide-audit-pane" aria-selected="false">
                    <i class="fas fa-search"></i> Audit
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="guide-superadmin-tab" data-bs-toggle="tab" data-bs-target="#guide-superadmin-pane" type="button" role="tab" aria-controls="guide-superadmin-pane" aria-selected="false">
                    <i class="fas fa-shield-alt"></i> Superadmin
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="guide-profile-tab" data-bs-toggle="tab" data-bs-target="#guide-profile-pane" type="button" role="tab" aria-controls="guide-profile-pane" aria-selected="false">
                    <i class="fas fa-user-circle"></i> Profile
                </button>
            </li>
        </ul>

        <div class="tab-content" id="userGuideTabContent">
            <div class="tab-pane fade show active" id="guide-start-pane" role="tabpanel" aria-labelledby="guide-start-tab">
                <div class="guide-pane-title">Flow Harian Paling Aman untuk Pengguna Baru</div>
                <ol class="mb-2">
                    <li>Login menggunakan username dan password Anda.</li>
                    <li>Cek menu yang tampil di navbar untuk memastikan role/divisi Anda sudah benar.</li>
                    <li>Buka <strong>Risk Monitoring</strong> untuk melihat gambaran risiko terbaru.</li>
                    <li>Lanjut ke <strong>Risk Register</strong> untuk melihat daftar risiko detail.</li>
                    <li>Gunakan <strong>Add Risk</strong> jika perlu membuat risiko baru.</li>
                    <li>Gunakan ikon <strong>View</strong> pada baris risiko untuk membuka detail risiko, evidence, dan diskusi.</li>
                    <li>Jika perlu kirim laporan, gunakan tombol <strong>Export Report</strong>.</li>
                    <li>Jika laporan perlu otomatis, buat jadwal di <strong>Scheduled Reports</strong>.</li>
                </ol>

                <div class="guide-section-title">Prinsip Kerja Harian</div>
                <ul class="mb-2">
                    <li>Mulai dari monitoring, lalu turun ke detail hanya pada item prioritas.</li>
                    <li>Isi data lengkap saat create/edit agar analitik otomatis lebih akurat.</li>
                    <li>Gunakan komentar dan attachment di halaman detail agar jejak kerja terdokumentasi.</li>
                </ul>

                <div class="guide-note">
                    User Guide ini fokus pada cara pakai aplikasi. Detail arsitektur sistem, kontrol keamanan internal, dan mekanisme teknis backend tidak dibahas di dokumen ini.
                </div>
            </div>

            <div class="tab-pane fade" id="guide-role-pane" role="tabpanel" aria-labelledby="guide-role-tab">
                <div class="guide-pane-title">Matriks Akses per Role</div>
                <div class="table-responsive">
                    <table class="table table-sm table-bordered guide-table">
                        <thead>
                            <tr>
                                <th>Role / Kondisi</th>
                                <th>Menu Utama yang Muncul</th>
                                <th>Fokus Tugas</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><span class="guide-chip role">User terverifikasi</span></td>
                                <td>Risk, Audit, Scheduled Reports, User Guide</td>
                                <td>Operasional risk harian, update risk, monitoring, export report.</td>
                            </tr>
                            <tr>
                                <td><span class="guide-chip role">Compliance Division</span></td>
                                <td>Menu user terverifikasi + Compliance</td>
                                <td>Kelola program kerja compliance dan import XLS risk register.</td>
                            </tr>
                            <tr>
                                <td><span class="guide-chip role">Superadmin</span></td>
                                <td>Dashboard, User Management, Status Approvals, Alerts, Audit Trail</td>
                                <td>Administrasi user, approval status risk, penanganan alert, audit log.</td>
                            </tr>
                            <tr>
                                <td><span class="guide-chip role">User pending verifikasi</span></td>
                                <td>Pending Approval</td>
                                <td>Menunggu verifikasi divisi sebelum akses modul utama.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="guide-section-title">Catatan Penting</div>
                <ul class="mb-2">
                    <li>Tidak semua tombol tampil untuk semua role.</li>
                    <li>Jika tombol tertentu tidak terlihat, biasanya karena akses role/divisi belum sesuai.</li>
                    <li>Untuk perubahan hak akses, hubungi superadmin.</li>
                </ul>
            </div>

            <div class="tab-pane fade" id="guide-auth-pane" role="tabpanel" aria-labelledby="guide-auth-tab">
                <div class="guide-pane-title">Login, Registrasi, dan Aktivasi Akun</div>

                <div class="guide-section-title">Halaman Login</div>
                <ol class="mb-2">
                    <li>Isi <strong>Username</strong> dan <strong>Password</strong>.</li>
                    <li>Opsional centang <strong>Remember Me</strong> jika perangkat ini milik Anda.</li>
                    <li>Klik <strong>Sign In</strong>.</li>
                </ol>

                <div class="guide-section-title">Tombol di Login</div>
                <div class="table-responsive">
                    <table class="table table-sm table-bordered guide-table mb-2">
                        <thead>
                            <tr>
                                <th>Tombol / Link</th>
                                <th>Fungsi</th>
                                <th>Kapan Dipakai</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><strong>Sign In</strong></td>
                                <td>Masuk ke aplikasi dengan kredensial yang valid.</td>
                                <td>Setiap kali login.</td>
                            </tr>
                            <tr>
                                <td><strong>Lupa Password?</strong></td>
                                <td>Membuka halaman permintaan reset password.</td>
                                <td>Saat lupa password.</td>
                            </tr>
                            <tr>
                                <td><strong>Daftar Akun Baru</strong></td>
                                <td>Membuka form registrasi user baru.</td>
                                <td>Saat belum punya akun.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="guide-section-title">Halaman Register</div>
                <ol class="mb-2">
                    <li>Isi data: Nama Lengkap, Username, Pengajuan Divisi, Email, Password, Konfirmasi Password.</li>
                    <li>Klik <strong>Register</strong>.</li>
                    <li>Setelah registrasi, akun masuk status menunggu verifikasi divisi.</li>
                </ol>

                <div class="guide-section-title">Halaman Lupa / Reset Password</div>
                <ul class="mb-2">
                    <li><strong>Kirim Link Reset</strong>: kirim permintaan reset berdasarkan username.</li>
                    <li><strong>Update Password</strong> (halaman reset): set password baru setelah mendapatkan token reset.</li>
                    <li><strong>Confirm</strong> (halaman konfirmasi password): validasi password saat diminta sistem.</li>
                    <li><strong>Kirim Ulang Email Verifikasi</strong>: kirim ulang email verifikasi jika belum menerima.</li>
                </ul>

                <div class="guide-section-title">Halaman Pending Approval</div>
                <p class="mb-0">
                    Jika akun Anda belum diverifikasi divisi, Anda akan diarahkan ke halaman <strong>Akun Menunggu Verifikasi</strong>.
                    Tidak ada tombol aksi utama selain menunggu verifikasi superadmin.
                </p>
            </div>
            <div class="tab-pane fade" id="guide-nav-pane" role="tabpanel" aria-labelledby="guide-nav-tab">
                <div class="guide-pane-title">Navigasi Umum dan Tombol Global</div>

                <div class="guide-section-title">Navbar Atas (User Terverifikasi)</div>
                <div class="table-responsive">
                    <table class="table table-sm table-bordered guide-table mb-2">
                        <thead>
                            <tr>
                                <th>Menu</th>
                                <th>Isi Menu</th>
                                <th>Fungsi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><strong>Risk</strong></td>
                                <td>Risk Monitoring, Risk Register, Risk Trend Analysis</td>
                                <td>Pusat kerja seluruh proses manajemen risiko.</td>
                            </tr>
                            <tr>
                                <td><strong>Audit</strong></td>
                                <td>Audit Monitoring Dashboard</td>
                                <td>Monitoring temuan audit per tab analitik.</td>
                            </tr>
                            <tr>
                                <td><strong>Scheduled Reports</strong></td>
                                <td>Daftar jadwal laporan otomatis</td>
                                <td>Automasi distribusi laporan berkala.</td>
                            </tr>
                            <tr>
                                <td><strong>User Guide</strong></td>
                                <td>Dokumen panduan ini</td>
                                <td>Referensi penggunaan aplikasi.</td>
                            </tr>
                            <tr>
                                <td><strong>Compliance</strong> (khusus compliance division)</td>
                                <td>Internal Audit, Risk Management, Governance and Compliance</td>
                                <td>Kelola program kerja compliance per section.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="guide-section-title">Menu User (Avatar kanan atas)</div>
                <div class="table-responsive">
                    <table class="table table-sm table-bordered guide-table mb-2">
                        <thead>
                            <tr>
                                <th>Menu</th>
                                <th>Fungsi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><strong>Profile</strong></td>
                                <td>Membuka halaman profil untuk update nama pengguna.</td>
                            </tr>
                            <tr>
                                <td><strong>Change Password</strong></td>
                                <td>Mengubah password akun yang sedang login.</td>
                            </tr>
                            <tr>
                                <td><strong>Logout</strong></td>
                                <td>Keluar dari aplikasi dan kembali ke halaman login.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="guide-note">
                    Jika menu tidak muncul sesuai yang Anda harapkan, cek status verifikasi divisi atau role akun Anda.
                </div>
            </div>

            <div class="tab-pane fade" id="guide-monitoring-pane" role="tabpanel" aria-labelledby="guide-monitoring-tab">
                <div class="guide-pane-title">Risk Monitoring Dashboard</div>

                <div class="guide-section-title">Cara Membaca untuk Pemula</div>
                <ol class="mb-2">
                    <li>Lihat <strong>Last refresh</strong> untuk memastikan data terbaru.</li>
                    <li>Mulai dari <strong>Dashboard 2 - Portfolio Briefing</strong> untuk gambaran portfolio cepat.</li>
                    <li>Lanjut ke <strong>Dashboard 1 - Operational Command</strong> untuk efektivitas mitigasi, SLA, dan KPI.</li>
                    <li>Terakhir buka <strong>Dashboard 3 - Risk Intelligence</strong> untuk root cause dan variance.</li>
                </ol>

                <div class="guide-section-title">Tombol Header Monitoring</div>
                <div class="table-responsive">
                    <table class="table table-sm table-bordered guide-table mb-2">
                        <thead>
                            <tr>
                                <th>Tombol</th>
                                <th>Fungsi</th>
                                <th>Output</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><strong>Export Report</strong></td>
                                <td>Membuka dropdown format export laporan.</td>
                                <td>File XLSX, PDF, PPT sesuai scope.</td>
                            </tr>
                            <tr>
                                <td><strong>Scheduled Reports</strong></td>
                                <td>Masuk ke halaman pengaturan laporan otomatis.</td>
                                <td>Halaman schedule report.</td>
                            </tr>
                            <tr>
                                <td><strong>Add Risk</strong></td>
                                <td>Membuka form tambah risk baru.</td>
                                <td>Halaman Add Risk.</td>
                            </tr>
                            <tr>
                                <td><strong>Customize Widgets</strong></td>
                                <td>Menampilkan daftar checkbox show/hide widget.</td>
                                <td>Widget sesuai preferensi user.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="guide-section-title">Tombol Navigasi Dashboard</div>
                <div class="table-responsive">
                    <table class="table table-sm table-bordered guide-table mb-2">
                        <thead>
                            <tr>
                                <th>Tombol</th>
                                <th>Fokus Konten</th>
                                <th>Dipakai Saat</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><strong>Dashboard 1 - Operational Command</strong></td>
                                <td>Mitigation, SLA, KPI, organizational overview.</td>
                                <td>Kontrol eksekusi harian tim.</td>
                            </tr>
                            <tr>
                                <td><strong>Dashboard 2 - Portfolio Briefing</strong></td>
                                <td>Status core metrics, portfolio command center, top risk list.</td>
                                <td>Briefing cepat ke manajemen.</td>
                            </tr>
                            <tr>
                                <td><strong>Dashboard 3 - Risk Intelligence</strong></td>
                                <td>Root cause analysis dan variance.</td>
                                <td>Analisa pola risiko dan penyebab dominan.</td>
                            </tr>
                            <tr>
                                <td>Ikon <strong>drag</strong> pada tiap widget</td>
                                <td>Geser urutan widget.</td>
                                <td>Personalisasi urutan tampilan.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="guide-section-title">Isi Dropdown Tombol Export Report</div>
                <ul class="mb-0">
                    <li><strong>[Scope aktif] - XLSX/PDF/PPT</strong>: export sesuai halaman yang sedang dibuka.</li>
                    <li><strong>Overall - XLSX/PDF/PPT</strong>: export ringkasan lintas scope.</li>
                    <li><strong>Board Briefing - PDF/PPT/XLSX</strong>: export format briefing manajemen.</li>
                </ul>
            </div>

            <div class="tab-pane fade" id="guide-workspace-pane" role="tabpanel" aria-labelledby="guide-workspace-tab">
                <div class="guide-pane-title">Risk Workspace: Register, Heat Map, Recapitulation</div>

                <div class="guide-section-title">Tab Workspace</div>
                <ul class="mb-2">
                    <li><strong>Risk Register</strong>: daftar risiko detail dan aksi per baris.</li>
                    <li><strong>Risk Heat Map</strong>: visual matriks 5x5 inherent vs residual.</li>
                    <li><strong>Risk Recapitulation</strong>: ringkasan agregat per kode radar/kategori.</li>
                </ul>

                <div class="guide-section-title">Tombol dan Kontrol di Risk Register</div>
                <div class="table-responsive">
                    <table class="table table-sm table-bordered guide-table">
                        <thead>
                            <tr>
                                <th>Tombol / Kontrol</th>
                                <th>Fungsi</th>
                                <th>Catatan Penggunaan</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><strong>Filter Owner</strong> (dropdown)</td>
                                <td>Menyaring data register berdasarkan owner.</td>
                                <td>Gunakan untuk fokus area/divisi tertentu.</td>
                            </tr>
                            <tr>
                                <td><strong>Export Report</strong></td>
                                <td>Ekspor scope aktif + overall + board briefing.</td>
                                <td>Format: XLSX, PDF, PPT.</td>
                            </tr>
                            <tr>
                                <td><strong>Import XLS</strong> (compliance division)</td>
                                <td>Membuka modal import data risk massal.</td>
                                <td>Gunakan template tim yang sesuai.</td>
                            </tr>
                            <tr>
                                <td><strong>Monitoring</strong></td>
                                <td>Pindah cepat ke halaman monitoring.</td>
                                <td>Untuk kembali ke dashboard utama.</td>
                            </tr>
                            <tr>
                                <td><strong>Add Risk</strong></td>
                                <td>Membuka form input risk baru.</td>
                                <td>Tampil untuk user non-superadmin.</td>
                            </tr>
                            <tr>
                                <td><strong>Overview / Full Table / Analysis / Treatment / Quantitative</strong></td>
                                <td>Preset kolom table agar fokus sesuai kebutuhan review.</td>
                                <td>Preferensi tampilan disimpan di browser.</td>
                            </tr>
                            <tr>
                                <td>Ikon <strong>View</strong> (mata)</td>
                                <td>Membuka halaman detail risk.</td>
                                <td>Gunakan untuk evidence, komentar, histori.</td>
                            </tr>
                            <tr>
                                <td>Ikon <strong>Edit</strong> (pensil)</td>
                                <td>Mengubah data risk.</td>
                                <td>Muncul sesuai izin owner risk.</td>
                            </tr>
                            <tr>
                                <td>Ikon <strong>Delete</strong> (trash)</td>
                                <td>Menghapus risk setelah konfirmasi.</td>
                                <td>Gunakan hanya untuk data salah/duplikat.</td>
                            </tr>
                            <tr>
                                <td><strong>Search</strong> DataTable</td>
                                <td>Pencarian cepat di tabel register.</td>
                                <td>Bisa dipakai bersama filter owner.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="guide-section-title">Tombol di Modal Import XLS</div>
                <div class="table-responsive">
                    <table class="table table-sm table-bordered guide-table mb-2">
                        <thead>
                            <tr>
                                <th>Tombol</th>
                                <th>Fungsi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><strong>Cancel</strong></td>
                                <td>Menutup modal import tanpa proses.</td>
                            </tr>
                            <tr>
                                <td><strong>Start Import</strong></td>
                                <td>Menjalankan import file sesuai opsi yang dipilih.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="guide-section-title">Cara Baca Heat Map</div>
                <ol class="mb-2">
                    <li>Sumbu Y menunjukkan likelihood 1 sampai 5, sumbu X menunjukkan impact 1 sampai 5.</li>
                    <li>Chip gelap menandai posisi inherent, chip putih menandai residual.</li>
                    <li>Semakin banyak marker di level tinggi, semakin tinggi prioritas mitigasi.</li>
                    <li>Gunakan tabel <strong>Risk Event Mapping</strong> di bawah heat map untuk melacak event dan owner.</li>
                </ol>

                <div class="guide-section-title">Tombol di Recapitulation</div>
                <ul class="mb-0">
                    <li><strong>Filter Year</strong> (dropdown): menyaring rekap per tahun, submit otomatis saat dipilih.</li>
                    <li><strong>Export Report</strong>: ekspor rekap, overall, dan board briefing.</li>
                    <li><strong>Monitoring</strong>: kembali ke halaman monitoring.</li>
                </ul>
            </div>
            <div class="tab-pane fade" id="guide-form-pane" role="tabpanel" aria-labelledby="guide-form-tab">
                <div class="guide-pane-title">Halaman Add Risk / Edit Risk (6 Section)</div>

                <div class="guide-section-title">Urutan Section Form</div>
                <ol class="mb-2">
                    <li><strong>Risk</strong>: proses, kategori, owner, cause, existing control, status (khusus edit).</li>
                    <li><strong>Inherent Analysis</strong>: probability + impact sebelum treatment.</li>
                    <li><strong>Risk Treatment</strong>: daftar aksi treatment (bisa lebih dari satu).</li>
                    <li><strong>Residual Analysis</strong>: nilai risiko setelah treatment.</li>
                    <li><strong>Group Radar</strong>: pemetaan kode radar.</li>
                    <li><strong>Quantitative Result</strong>: impact amount, probability persen, potential loss.</li>
                </ol>

                <div class="guide-section-title">Tombol Utama Form</div>
                <div class="table-responsive">
                    <table class="table table-sm table-bordered guide-table mb-2">
                        <thead>
                            <tr>
                                <th>Tombol</th>
                                <th>Fungsi</th>
                                <th>Catatan Praktis</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><strong>Next: ...</strong></td>
                                <td>Pindah ke section berikutnya.</td>
                                <td>Akan menahan pindah jika field wajib belum diisi.</td>
                            </tr>
                            <tr>
                                <td><strong>Back</strong></td>
                                <td>Kembali ke section sebelumnya.</td>
                                <td>Data yang sudah diisi tetap tersimpan di form.</td>
                            </tr>
                            <tr>
                                <td><strong>Add Treatment Option</strong></td>
                                <td>Menambah baris treatment baru.</td>
                                <td>Gunakan untuk risiko dengan lebih dari 1 aksi mitigasi.</td>
                            </tr>
                            <tr>
                                <td><strong>Remove</strong> (baris treatment)</td>
                                <td>Menghapus 1 baris treatment.</td>
                                <td>Hapus baris yang salah input.</td>
                            </tr>
                            <tr>
                                <td><strong>SAVE COMPLETE RISK</strong></td>
                                <td>Menyimpan risk baru.</td>
                                <td>Khusus halaman Add Risk.</td>
                            </tr>
                            <tr>
                                <td><strong>UPDATE COMPLETE RISK</strong></td>
                                <td>Menyimpan perubahan risk existing.</td>
                                <td>Khusus halaman Edit Risk.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="guide-section-title">Catatan Khusus Halaman Edit</div>
                <ul class="mb-0">
                    <li>Di Edit Risk ada field <strong>Status</strong> dan <strong>Status Change Reason</strong>.</li>
                    <li>Isi alasan perubahan status sejelas mungkin agar proses review lebih mudah.</li>
                </ul>
            </div>

            <div class="tab-pane fade" id="guide-detail-pane" role="tabpanel" aria-labelledby="guide-detail-tab">
                <div class="guide-pane-title">Halaman Detail Risk dan Governance</div>

                <div class="guide-section-title">Tab Detail Risk</div>
                <ul class="mb-2">
                    <li><strong>Basic</strong>: identitas risk dan status.</li>
                    <li><strong>Inherent</strong>: detail skor inherent.</li>
                    <li><strong>Treatment</strong>: list treatment + evidence per treatment.</li>
                    <li><strong>Residual</strong>: detail skor residual dan radar.</li>
                    <li><strong>Governance</strong>: risk evidence, komentar, activity feed, approval history, alerts.</li>
                </ul>

                <div class="guide-section-title">Tombol Header Detail</div>
                <div class="table-responsive">
                    <table class="table table-sm table-bordered guide-table mb-2">
                        <thead>
                            <tr>
                                <th>Tombol</th>
                                <th>Fungsi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><strong>Edit</strong></td>
                                <td>Membuka form edit untuk risk aktif.</td>
                            </tr>
                            <tr>
                                <td><strong>Back</strong></td>
                                <td>Kembali ke halaman Risk Register.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="guide-section-title">Tombol di Treatment Evidence dan Risk Evidence</div>
                <div class="table-responsive">
                    <table class="table table-sm table-bordered guide-table mb-2">
                        <thead>
                            <tr>
                                <th>Tombol</th>
                                <th>Fungsi</th>
                                <th>Lokasi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><strong>Upload</strong></td>
                                <td>Mengunggah bukti file (evidence) + catatan opsional.</td>
                                <td>Tab Treatment dan Governance.</td>
                            </tr>
                            <tr>
                                <td><strong>Download</strong></td>
                                <td>Mengunduh lampiran.</td>
                                <td>Daftar lampiran.</td>
                            </tr>
                            <tr>
                                <td><strong>Delete</strong></td>
                                <td>Menghapus lampiran setelah konfirmasi.</td>
                                <td>Daftar lampiran.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="guide-section-title">Tombol di Discussion Thread</div>
                <div class="table-responsive">
                    <table class="table table-sm table-bordered guide-table mb-2">
                        <thead>
                            <tr>
                                <th>Tombol</th>
                                <th>Fungsi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><strong>Post Comment</strong></td>
                                <td>Mengirim komentar baru. Mendukung mention format <code>@username</code>.</td>
                            </tr>
                            <tr>
                                <td><strong>Edit</strong> lalu <strong>Save</strong></td>
                                <td>Mengubah komentar yang sudah dikirim.</td>
                            </tr>
                            <tr>
                                <td><strong>Delete</strong> (comment)</td>
                                <td>Menghapus komentar.</td>
                            </tr>
                            <tr>
                                <td><strong>Attach</strong></td>
                                <td>Menambah attachment ke komentar yang sudah ada.</td>
                            </tr>
                            <tr>
                                <td><strong>Approval Queue</strong> (superadmin)</td>
                                <td>Shortcut ke halaman approval status risk.</td>
                            </tr>
                            <tr>
                                <td><strong>Alert Center</strong> (superadmin)</td>
                                <td>Shortcut ke halaman daftar alert risk.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="tab-pane fade" id="guide-trend-pane" role="tabpanel" aria-labelledby="guide-trend-tab">
                <div class="guide-pane-title">Risk Trend Analysis</div>

                <div class="guide-section-title">Tujuan Halaman</div>
                <p class="mb-2">
                    Halaman ini dipakai untuk membaca perubahan risiko dari waktu ke waktu: rata-rata inherent vs residual, distribusi status, konsentrasi owner, dan trend high-risk pada heatmap.
                </p>

                <div class="guide-section-title">Kontrol dan Tombol</div>
                <div class="table-responsive">
                    <table class="table table-sm table-bordered guide-table mb-2">
                        <thead>
                            <tr>
                                <th>Kontrol / Tombol</th>
                                <th>Fungsi</th>
                                <th>Tips Pakai</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><strong>Granularity</strong> (Monthly / Quarterly / Yearly)</td>
                                <td>Mengubah tingkat agregasi periode trend.</td>
                                <td>Pakai monthly untuk detail, yearly untuk ringkasan manajemen.</td>
                            </tr>
                            <tr>
                                <td><strong>Days</strong> (Last 3 Months sampai Last 2 Years)</td>
                                <td>Mengubah rentang data yang dianalisis.</td>
                                <td>Pilih rentang sesuai kebutuhan rapat/review.</td>
                            </tr>
                            <tr>
                                <td>Ikon <strong>drag handle</strong> pada panel</td>
                                <td>Memindahkan urutan panel chart.</td>
                                <td>Urutan panel disimpan sebagai preferensi tampilan user.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="guide-section-title">Cara Baca Cepat Chart</div>
                <ul class="mb-0">
                    <li><strong>Risk Score Trend</strong>: cek apakah residual konsisten di bawah inherent.</li>
                    <li><strong>Risk Score by Category</strong>: lihat kategori mana dengan residual tertinggi.</li>
                    <li><strong>Status Distribution</strong>: baca komposisi Open, Monitoring, Closed per periode.</li>
                    <li><strong>Owner Concentration</strong>: identifikasi owner dengan konsentrasi risk paling tinggi.</li>
                    <li><strong>Heatmap High-Risk Trend</strong>: cek arah perubahan high-risk (improving, stable, deteriorating).</li>
                </ul>
            </div>
            <div class="tab-pane fade" id="guide-scheduled-pane" role="tabpanel" aria-labelledby="guide-scheduled-tab">
                <div class="guide-pane-title">Scheduled Reports (Laporan Otomatis)</div>

                <div class="guide-section-title">Langkah Membuat Schedule Baru</div>
                <ol class="mb-2">
                    <li>Buka <strong>Scheduled Reports</strong>.</li>
                    <li>Isi <strong>Schedule Name</strong>.</li>
                    <li>Pilih <strong>Scope</strong> dan <strong>Format</strong> output.</li>
                    <li>Pilih <strong>Frequency</strong>, <strong>Send Time</strong>, dan <strong>Timezone</strong>.</li>
                    <li>Jika weekly, isi <strong>Day of Week</strong>. Jika monthly, isi <strong>Day of Month</strong>.</li>
                    <li>Isi filter opsional (Year, Category, Cause Category, Risk Owner) jika dibutuhkan.</li>
                    <li>Isi daftar email di <strong>Recipients</strong> (pisahkan dengan koma).</li>
                    <li>Klik <strong>Save Schedule</strong>.</li>
                </ol>

                <div class="guide-section-title">Tombol di Halaman Scheduled Reports</div>
                <div class="table-responsive">
                    <table class="table table-sm table-bordered guide-table mb-2">
                        <thead>
                            <tr>
                                <th>Tombol</th>
                                <th>Fungsi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><strong>Back to Monitoring</strong></td>
                                <td>Kembali ke halaman monitoring.</td>
                            </tr>
                            <tr>
                                <td><strong>Save Schedule</strong></td>
                                <td>Menyimpan schedule baru.</td>
                            </tr>
                            <tr>
                                <td><strong>Update Schedule</strong></td>
                                <td>Menyimpan perubahan schedule existing.</td>
                            </tr>
                            <tr>
                                <td><strong>Cancel Edit</strong> / <strong>Cancel</strong></td>
                                <td>Keluar dari mode edit tanpa menyimpan perubahan.</td>
                            </tr>
                            <tr>
                                <td><strong>Edit</strong></td>
                                <td>Membuka schedule terpilih ke mode edit.</td>
                            </tr>
                            <tr>
                                <td><strong>Run Now</strong></td>
                                <td>Menjalankan schedule saat itu juga (manual trigger).</td>
                            </tr>
                            <tr>
                                <td><strong>Pause</strong> / <strong>Activate</strong></td>
                                <td>Menonaktifkan atau mengaktifkan schedule.</td>
                            </tr>
                            <tr>
                                <td><strong>Delete</strong></td>
                                <td>Menghapus schedule permanen.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="tab-pane fade" id="guide-compliance-pane" role="tabpanel" aria-labelledby="guide-compliance-tab">
                <div class="guide-pane-title">Compliance Programs</div>

                <div class="guide-section-title">Halaman yang Tersedia</div>
                <ul class="mb-2">
                    <li><span class="guide-chip page">Internal Audit</span> Program kerja section Internal Audit.</li>
                    <li><span class="guide-chip page">Risk Management</span> Program kerja section Risk Management.</li>
                    <li><span class="guide-chip page">Governance and Compliance</span> Program kerja section Governance and Compliance.</li>
                </ul>

                <div class="guide-section-title">Tombol di Halaman Index Program</div>
                <div class="table-responsive">
                    <table class="table table-sm table-bordered guide-table mb-2">
                        <thead>
                            <tr>
                                <th>Tombol</th>
                                <th>Fungsi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><strong>Add Program Kerja</strong></td>
                                <td>Membuka form pembuatan program kerja baru.</td>
                            </tr>
                            <tr>
                                <td>Ikon <strong>Edit</strong></td>
                                <td>Mengubah program kerja yang sudah ada.</td>
                            </tr>
                            <tr>
                                <td>Ikon <strong>Delete</strong></td>
                                <td>Menghapus program kerja setelah konfirmasi.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="guide-section-title">Tombol di Form Program</div>
                <div class="table-responsive">
                    <table class="table table-sm table-bordered guide-table mb-2">
                        <thead>
                            <tr>
                                <th>Tombol</th>
                                <th>Fungsi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><strong>Kembali</strong></td>
                                <td>Kembali ke daftar program section aktif.</td>
                            </tr>
                            <tr>
                                <td><strong>Simpan Program Kerja</strong></td>
                                <td>Menyimpan data program baru.</td>
                            </tr>
                            <tr>
                                <td><strong>Update Program Kerja</strong></td>
                                <td>Menyimpan perubahan data program existing.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="tab-pane fade" id="guide-audit-pane" role="tabpanel" aria-labelledby="guide-audit-tab">
                <div class="guide-pane-title">Audit Monitoring Dashboard</div>

                <div class="guide-section-title">Alur Pakai Dasar</div>
                <ol class="mb-2">
                    <li>Pilih filter <strong>Year</strong> dan <strong>Bank</strong> bila diperlukan.</li>
                    <li>Klik <strong>Apply</strong> untuk menerapkan filter.</li>
                    <li>Gunakan tab-tab audit untuk membaca dimensi analitik yang berbeda.</li>
                </ol>

                <div class="guide-section-title">Tombol dan Tab</div>
                <div class="table-responsive">
                    <table class="table table-sm table-bordered guide-table mb-2">
                        <thead>
                            <tr>
                                <th>Tombol / Tab</th>
                                <th>Fungsi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><strong>Apply</strong></td>
                                <td>Menerapkan filter Year dan Bank.</td>
                            </tr>
                            <tr>
                                <td><strong>Reset</strong></td>
                                <td>Mengembalikan filter ke kondisi default.</td>
                            </tr>
                            <tr>
                                <td><strong>Import XLS</strong></td>
                                <td>(Khusus Compliance) Upload file audit untuk preview analitik semua tab dashboard.</td>
                            </tr>
                            <tr>
                                <td><strong>Executive Summary</strong></td>
                                <td>Ringkasan tindakan dan status global temuan audit.</td>
                            </tr>
                            <tr>
                                <td><strong>Rekap Detail Temuan</strong></td>
                                <td>Daftar detail temuan plus chart bar rekap.</td>
                            </tr>
                            <tr>
                                <td><strong>MAPS Temuan</strong></td>
                                <td>Visualisasi distribusi temuan berbasis peta.</td>
                            </tr>
                            <tr>
                                <td><strong>Progress Temuan</strong></td>
                                <td>Progress per bank, region, product.</td>
                            </tr>
                            <tr>
                                <td><strong>Trend Temuan</strong></td>
                                <td>Trend penciptaan dan penutupan temuan.</td>
                            </tr>
                            <tr>
                                <td><strong>Detail Outstanding</strong></td>
                                <td>List item outstanding per dimensi data.</td>
                            </tr>
                            <tr>
                                <td><strong>Duedate Maturity</strong></td>
                                <td>Analisis jatuh tempo outstanding item.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="tab-pane fade" id="guide-superadmin-pane" role="tabpanel" aria-labelledby="guide-superadmin-tab">
                <div class="guide-pane-title">Halaman Khusus Superadmin</div>

                <div class="guide-section-title">1) User Management</div>
                <div class="table-responsive">
                    <table class="table table-sm table-bordered guide-table mb-2">
                        <thead>
                            <tr>
                                <th>Tombol</th>
                                <th>Fungsi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><strong>Back to Risk Register</strong></td>
                                <td>Kembali ke risk register.</td>
                            </tr>
                            <tr>
                                <td><strong>Apply</strong> (filter user)</td>
                                <td>Menerapkan filter status dan keyword user.</td>
                            </tr>
                            <tr>
                                <td><strong>Reset</strong></td>
                                <td>Reset filter user.</td>
                            </tr>
                            <tr>
                                <td><strong>Save</strong> (division validation)</td>
                                <td>Simpan verifikasi/penyesuaian divisi user.</td>
                            </tr>
                            <tr>
                                <td><strong>Promote</strong> / <strong>Revoke</strong></td>
                                <td>Memberi atau mencabut status superadmin.</td>
                            </tr>
                            <tr>
                                <td><strong>Reset Password</strong></td>
                                <td>Generate password sementara untuk user target.</td>
                            </tr>
                            <tr>
                                <td><strong>Reset Password (Self Disabled)</strong></td>
                                <td>Indikasi bahwa akun sendiri tidak bisa reset via tombol ini.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="guide-section-title">2) Risk Status Approvals</div>
                <div class="table-responsive">
                    <table class="table table-sm table-bordered guide-table mb-2">
                        <thead>
                            <tr>
                                <th>Tombol</th>
                                <th>Fungsi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><strong>Back</strong></td>
                                <td>Kembali ke risk register.</td>
                            </tr>
                            <tr>
                                <td><strong>Filter</strong></td>
                                <td>Menyaring request status berdasarkan state.</td>
                            </tr>
                            <tr>
                                <td><strong>Approve</strong></td>
                                <td>Menyetujui request perubahan status risk.</td>
                            </tr>
                            <tr>
                                <td><strong>Reject</strong></td>
                                <td>Menolak request perubahan status risk.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="guide-section-title">3) Alerts</div>
                <div class="table-responsive">
                    <table class="table table-sm table-bordered guide-table mb-2">
                        <thead>
                            <tr>
                                <th>Tombol</th>
                                <th>Fungsi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><strong>Back</strong></td>
                                <td>Kembali ke risk register.</td>
                            </tr>
                            <tr>
                                <td><strong>Filter</strong></td>
                                <td>Menyaring alert berdasarkan status dan type.</td>
                            </tr>
                            <tr>
                                <td><strong>Resolve</strong></td>
                                <td>Menandai alert sebagai selesai.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="guide-section-title">4) Audit Trail</div>
                <div class="table-responsive">
                    <table class="table table-sm table-bordered guide-table mb-0">
                        <thead>
                            <tr>
                                <th>Tombol</th>
                                <th>Fungsi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><strong>Back</strong></td>
                                <td>Kembali ke risk register.</td>
                            </tr>
                            <tr>
                                <td><strong>Filter</strong></td>
                                <td>Menyaring log aktivitas berdasarkan jenis event.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="tab-pane fade" id="guide-profile-pane" role="tabpanel" aria-labelledby="guide-profile-tab">
                <div class="guide-pane-title">Profile dan Change Password</div>

                <div class="guide-section-title">Halaman My Profile</div>
                <ul class="mb-2">
                    <li>Kolom email dan division bersifat read-only di halaman profile.</li>
                    <li>Nama user dapat diubah sesuai kebutuhan pembaruan data.</li>
                </ul>

                <div class="guide-section-title">Tombol di Profile</div>
                <div class="table-responsive">
                    <table class="table table-sm table-bordered guide-table mb-2">
                        <thead>
                            <tr>
                                <th>Tombol</th>
                                <th>Fungsi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><strong>Update Profile</strong></td>
                                <td>Menyimpan perubahan data nama profil.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="guide-section-title">Halaman Change Password</div>
                <ol class="mb-2">
                    <li>Isi <strong>Password Lama</strong>.</li>
                    <li>Isi <strong>Password Baru</strong>.</li>
                    <li>Isi <strong>Konfirmasi Password Baru</strong>.</li>
                    <li>Klik <strong>Update Password</strong>.</li>
                </ol>

                <div class="guide-section-title">Bantuan Validasi di Halaman Password</div>
                <ul class="mb-2">
                    <li>Peringatan akan muncul jika password baru belum memenuhi aturan minimal.</li>
                    <li>Peringatan akan muncul jika konfirmasi password tidak sama.</li>
                </ul>

                <div class="guide-section-title">Tombol di Change Password</div>
                <div class="table-responsive">
                    <table class="table table-sm table-bordered guide-table mb-0">
                        <thead>
                            <tr>
                                <th>Tombol</th>
                                <th>Fungsi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><strong>Update Password</strong></td>
                                <td>Menyimpan password baru akun Anda.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="guide-note">
                    Demi keamanan operasional, jangan membagikan kredensial akun, file lampiran sensitif, atau informasi akses internal ke pihak yang tidak berwenang.
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
