@extends('layouts.tabler')

@section('title', 'How To - Risk Management System')

@push('styles')
    <style>
        .howto-card {
            border: 1px solid #dbe4ef;
            border-radius: 14px;
            box-shadow: 0 10px 24px rgba(15, 23, 42, 0.06);
        }

        .howto-tabs {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(190px, 1fr));
            gap: 0.5rem;
            overflow: visible;
            padding-bottom: 0;
        }

        .howto-tabs .nav-item {
            min-width: 0;
        }

        .howto-tabs .nav-item.howto-tab-wide {
            grid-column: span 2;
        }

        .howto-tabs .nav-link {
            border: 1px solid #c9d6e8;
            border-radius: 999px;
            color: #1f3a5f;
            font-weight: 600;
            width: 100%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.35rem;
            white-space: nowrap;
            text-align: center;
            padding: 0.45rem 0.9rem;
        }

        .howto-tabs .nav-link.active {
            color: #fff;
            background: #206bc4;
            border-color: #206bc4;
            box-shadow: 0 6px 14px rgba(32, 107, 196, 0.25);
        }

        .howto-pane-title {
            font-size: 1rem;
            font-weight: 700;
            color: #1f2937;
            margin-bottom: 0.75rem;
        }

        .howto-note {
            background: #f7faff;
            border: 1px solid #dce7f8;
            border-radius: 10px;
            padding: 0.75rem 0.9rem;
            color: #1f3a5f;
            margin-top: 1rem;
        }

        @media (max-width: 992px) {
            .howto-tabs {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media (max-width: 576px) {
            .howto-tabs {
                display: flex;
                flex-wrap: nowrap;
                overflow-x: auto;
                padding-bottom: 0.35rem;
                scroll-snap-type: x proximity;
                -ms-overflow-style: none;
                scrollbar-width: none;
            }

            .howto-tabs::-webkit-scrollbar {
                display: none;
            }

            .howto-tabs .nav-item {
                flex: 0 0 auto;
                scroll-snap-align: start;
            }

            .howto-tabs .nav-item.howto-tab-wide {
                grid-column: auto;
            }

            .howto-tabs .nav-link {
                width: auto;
                white-space: nowrap;
            }
        }
    </style>
@endpush

@section('content_header')
    <div class="row align-items-center">
        <div class="col">
            <h2 class="page-title mb-1">How To</h2>
            <div class="text-muted">Panduan penggunaan aplikasi Risk Management System.</div>
        </div>
    </div>
@endsection

@section('content')
    <div class="card howto-card">
        <div class="card-body">
            <ul class="nav howto-tabs mb-3" id="howtoTab" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="akun-tab" data-bs-toggle="tab" data-bs-target="#akun-pane" type="button" role="tab" aria-controls="akun-pane" aria-selected="true">
                        <i class="fas fa-user-circle me-1"></i> Akun
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="register-tab" data-bs-toggle="tab" data-bs-target="#register-pane" type="button" role="tab" aria-controls="register-pane" aria-selected="false">
                        <i class="fas fa-table me-1"></i> Risk Register
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="addrisk-tab" data-bs-toggle="tab" data-bs-target="#addrisk-pane" type="button" role="tab" aria-controls="addrisk-pane" aria-selected="false">
                        <i class="fas fa-plus-circle me-1"></i> Add / Edit Risk
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="detailrisk-tab" data-bs-toggle="tab" data-bs-target="#detailrisk-pane" type="button" role="tab" aria-controls="detailrisk-pane" aria-selected="false">
                        <i class="fas fa-search me-1"></i> Detail Risk
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="monitor-tab" data-bs-toggle="tab" data-bs-target="#monitor-pane" type="button" role="tab" aria-controls="monitor-pane" aria-selected="false">
                        <i class="fas fa-chart-area me-1"></i> Monitoring
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="discussion-tab" data-bs-toggle="tab" data-bs-target="#discussion-pane" type="button" role="tab" aria-controls="discussion-pane" aria-selected="false">
                        <i class="fas fa-comments me-1"></i> Komentar & Mention
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="import-tab" data-bs-toggle="tab" data-bs-target="#import-pane" type="button" role="tab" aria-controls="import-pane" aria-selected="false">
                        <i class="fas fa-file-import me-1"></i> Import XLS
                    </button>
                </li>
                <li class="nav-item howto-tab-wide" role="presentation">
                    <button class="nav-link" id="report-tab" data-bs-toggle="tab" data-bs-target="#report-pane" type="button" role="tab" aria-controls="report-pane" aria-selected="false">
                        <i class="fas fa-file-export me-1"></i> Recap, Heat Map, Export
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="compliance-tab" data-bs-toggle="tab" data-bs-target="#compliance-pane" type="button" role="tab" aria-controls="compliance-pane" aria-selected="false">
                        <i class="fas fa-briefcase me-1"></i> Compliance Plan
                    </button>
                </li>
            </ul>

            <div class="tab-content" id="howtoTabContent">
                <div class="tab-pane fade show active" id="akun-pane" role="tabpanel" aria-labelledby="akun-tab">
                    <div class="howto-pane-title">Akun, Login, dan Akses</div>
                    <ol class="mb-2">
                        <li>Registrasi akun di <code>/register</code> dengan data: Nama, Username, Divisi, Email, Password, dan konfirmasi password.</li>
                        <li>Login di <code>/login</code> menggunakan email dan password.</li>
                        <li>Jika lupa password, gunakan menu <strong>Forgot Your Password?</strong> di halaman login, lalu reset melalui link email.</li>
                        <li>Setelah login pertama kali, cek profil di menu user kanan atas untuk memastikan username/divisi sudah benar (karena dipakai untuk mention dan hak akses).</li>
                        <li>Hak akses berbasis role/divisi:
                            <ul>
                                <li><strong>User division</strong>: kelola risk sesuai owner yang diizinkan.</li>
                                <li><strong>Compliance division</strong>: tambahan akses menu Compliance + Import XLS.</li>
                                <li><strong>Akun administrasi</strong>: akses kontrol tambahan sesuai kebijakan internal perusahaan.</li>
                            </ul>
                        </li>
                    </ol>
                    <div class="howto-note">Jika muncul halaman pending approval/divisi belum sesuai, hubungi administrator aplikasi.</div>
                </div>

                <div class="tab-pane fade" id="register-pane" role="tabpanel" aria-labelledby="register-tab">
                    <div class="howto-pane-title">Navigasi Halaman Risk Register</div>
                    <ol class="mb-2">
                        <li>Buka menu <strong>Risk Register</strong> untuk melihat tabel utama risiko.</li>
                        <li>Bagian atas halaman berisi summary cards (Total, Low, Low to Moderate, Moderate, Moderate to High, High).</li>
                        <li>Panel kontrol atas:
                            <ul>
                                <li><strong>Filter Owner</strong> untuk membatasi tabel berdasarkan risk owner.</li>
                                <li>Toolbar aksi: Export, Import XLS (jika berhak), Monitoring, Recap, Heat Map, Add Risk.</li>
                            </ul>
                        </li>
                        <li>Header tabel berisi tombol preset kolom: <strong>Overview</strong>, <strong>Full Table</strong>, <strong>Analysis</strong>, <strong>Treatment</strong>, <strong>Quantitative</strong>.</li>
                        <li>Pencarian cepat tersedia di kanan atas tabel (DataTables search).</li>
                    </ol>
                    <div class="howto-note">Preset kolom disimpan di browser, jadi saat buka ulang halaman biasanya tetap memakai preset terakhir.</div>
                </div>

                <div class="tab-pane fade" id="addrisk-pane" role="tabpanel" aria-labelledby="addrisk-tab">
                    <div class="howto-pane-title">Tambah / Edit Risk End-to-End</div>
                    <ol class="mb-2">
                        <li>Klik <strong>Add Risk</strong> (atau tombol edit di tabel untuk ubah data existing).</li>
                        <li>Isi form per tab secara berurutan:
                            <ul>
                                <li><strong>Risk</strong>: process, risk identification, kategori, cause, owner.</li>
                                <li><strong>Inherent Analysis</strong>: probability, impact, score, nilai kuantitatif jika ada.</li>
                                <li><strong>Risk Treatment</strong>: opsi treatment, PIC, deadline, effectiveness.</li>
                                <li><strong>Residual Analysis</strong>: score residual + adjustment.</li>
                                <li><strong>Group Radar</strong>: pilih radar code/description sesuai mapping.</li>
                                <li><strong>Quantitative Result</strong>: nominal impact/probability/potential loss.</li>
                            </ul>
                        </li>
                        <li>Isi field angka dengan format angka valid. Persentase/probability akan dinormalisasi oleh sistem.</li>
                        <li>Klik <strong>Save Complete Risk</strong> atau <strong>Update</strong> untuk menyimpan.</li>
                        <li>Jika berhasil, data langsung muncul di tabel Risk Register.</li>
                    </ol>
                </div>

                <div class="tab-pane fade" id="detailrisk-pane" role="tabpanel" aria-labelledby="detailrisk-tab">
                    <div class="howto-pane-title">Detail Risk, Evidence, dan Status</div>
                    <ol class="mb-2">
                        <li>Klik ikon <strong>View</strong> pada tabel untuk membuka halaman detail risk.</li>
                        <li>Di halaman detail, cek tab: Basic, Inherent, Treatment, Residual, Governance.</li>
                        <li>Upload evidence bisa dilakukan di:
                            <ul>
                                <li><strong>Treatment Evidence</strong> per treatment.</li>
                                <li><strong>Risk Evidence</strong> untuk evidence level risk.</li>
                            </ul>
                        </li>
                        <li>Untuk perubahan status terkontrol, gunakan mekanisme <strong>Status Change Request</strong> (persetujuan oleh pihak berwenang).</li>
                        <li>Jika ada alert, bagian <strong>Risk Alerts</strong> akan tampil di detail.</li>
                        <li>Edit/hapus risk tetap dari halaman tabel (ikon edit/trash), sesuai permission owner.</li>
                    </ol>
                </div>

                <div class="tab-pane fade" id="monitor-pane" role="tabpanel" aria-labelledby="monitor-tab">
                    <div class="howto-pane-title">Monitoring Dashboard (Real-time)</div>
                    <ol class="mb-2">
                        <li>Buka menu <strong>Monitoring</strong> untuk dashboard ringkasan risiko real-time.</li>
                        <li>Komponen utama:
                            <ul>
                                <li>Status & Core Metrics (open/monitoring/closed, average scores, variance).</li>
                                <li>Average Risk Score Trends (menampilkan <strong>3 bulan terakhir</strong>).</li>
                                <li>Mitigation Effectiveness & SLA Compliance.</li>
                                <li>Summary by Category/Owner/Status (dengan pie/bar chart).</li>
                                <li>Variance Analysis (Inherent vs Residual).</li>
                            </ul>
                        </li>
                        <li>Dashboard refresh otomatis setiap sekitar 30 detik.</li>
                        <li>Klik <strong>Customize Widgets</strong> untuk show/hide widget.</li>
                        <li>Drag widget dari icon handle untuk ubah urutan sesuai preferensi.</li>
                        <li>Di header dashboard tersedia shortcut ke Risk Register, Recap, Heat Map, dan Add Risk.</li>
                    </ol>
                    <div class="howto-note">Preferensi widget (urut + visibility) disimpan di browser lokal (localStorage).</div>
                </div>

                <div class="tab-pane fade" id="discussion-pane" role="tabpanel" aria-labelledby="discussion-tab">
                    <div class="howto-pane-title">Discussion Thread, Mention, Attachment, Activity Feed</div>
                    <ol class="mb-2">
                        <li>Buka detail risk, lalu masuk tab <strong>Governance</strong>.</li>
                        <li>Di bagian <strong>Discussion Thread</strong>, tulis komentar lalu submit.</li>
                        <li>Untuk mention user, ketik format <code>@username</code> di isi komentar.</li>
                        <li>Attachment komentar dapat upload saat post komentar (multiple) atau setelah komentar dibuat.</li>
                        <li>Setiap komentar bisa di-edit/hapus sesuai permission.</li>
                        <li>Semua aktivitas penting (comment, attachment, status, update) tercatat di <strong>Activity Feed</strong>.</li>
                    </ol>
                </div>

                <div class="tab-pane fade" id="import-pane" role="tabpanel" aria-labelledby="import-tab">
                    <div class="howto-pane-title">Import Risk Register dari XLS/XLSX</div>
                    <ol class="mb-2">
                        <li>Fitur ini muncul di halaman Risk Register (umumnya untuk role Compliance Division).</li>
                        <li>Klik tombol <strong>Import XLS</strong>.</li>
                        <li>Upload file Excel sesuai template yang dipakai sistem.</li>
                        <li>Field <strong>Year</strong> bisa diisi manual (optional) atau biarkan auto-detect.</li>
                        <li>Aktifkan <strong>Dry-run</strong> jika ingin simulasi tanpa menyimpan data.</li>
                        <li>Setelah proses selesai, lihat ringkasan hasil import di alert <strong>Import Output</strong> pada halaman.</li>
                    </ol>
                </div>

                <div class="tab-pane fade" id="report-pane" role="tabpanel" aria-labelledby="report-tab">
                    <div class="howto-pane-title">Recap, Heat Map, dan Export</div>
                    <ol class="mb-2">
                        <li><strong>Recap</strong>: ringkasan metrik risiko per radar/kategori, termasuk view detail kuantitatif.</li>
                        <li><strong>Heat Map</strong>: distribusi risiko inherent/residual pada matriks 5x5.</li>
                        <li>Di halaman Risk Register, Recap, dan Heat Map tersedia tombol <strong>Export Report</strong>.</li>
                        <li>Pilih format <strong>XLS</strong>, <strong>PDF</strong>, atau <strong>PPTX</strong> sesuai kebutuhan pelaporan.</li>
                        <li>Untuk Recap, gunakan filter tahun agar output sesuai periode audit/reporting.</li>
                    </ol>
                </div>

                <div class="tab-pane fade" id="compliance-pane" role="tabpanel" aria-labelledby="compliance-tab">
                    <div class="howto-pane-title">Compliance Plan (Internal Audit / Risk Management / Governance & Compliance)</div>
                    <ol class="mb-2">
                        <li>Buka menu <strong>Compliance</strong> lalu pilih salah satu section:
                            <ul>
                                <li>Internal Audit Plan</li>
                                <li>Risk Management Plan</li>
                                <li>Governance &amp; Compliance Plan</li>
                            </ul>
                        </li>
                        <li>Di header tabel section, klik <strong>Add Program Kerja</strong>.</li>
                        <li>Isi field:
                            <ul>
                                <li>Program / Key Initiative</li>
                                <li>Description &amp; Rationales</li>
                                <li>Deliverables</li>
                                <li>How to Measure</li>
                                <li>Budget</li>
                                <li>Suport Needed</li>
                            </ul>
                        </li>
                        <li>Klik <strong>Simpan Program Kerja</strong>.</li>
                        <li>Gunakan ikon edit/trash di kolom Actions untuk ubah atau hapus data plan.</li>
                    </ol>
                    <div class="howto-note">ID plan dibuat otomatis oleh sistem berdasarkan section + tahun + urutan.</div>
                </div>
            </div>
        </div>
    </div>
@endsection
