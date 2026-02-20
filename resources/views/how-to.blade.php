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
            display: flex;
            flex-wrap: nowrap;
            gap: 0.5rem;
            overflow-x: auto;
            padding-bottom: 0.25rem;
        }

        .howto-tabs .nav-link {
            border: 1px solid #c9d6e8;
            border-radius: 999px;
            color: #1f3a5f;
            font-weight: 600;
            white-space: nowrap;
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
                    <button class="nav-link" id="addrisk-tab" data-bs-toggle="tab" data-bs-target="#addrisk-pane" type="button" role="tab" aria-controls="addrisk-pane" aria-selected="false">
                        <i class="fas fa-plus-circle me-1"></i> Add Risk
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="kelola-tab" data-bs-toggle="tab" data-bs-target="#kelola-pane" type="button" role="tab" aria-controls="kelola-pane" aria-selected="false">
                        <i class="fas fa-edit me-1"></i> Kelola Risk
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="filter-tab" data-bs-toggle="tab" data-bs-target="#filter-pane" type="button" role="tab" aria-controls="filter-pane" aria-selected="false">
                        <i class="fas fa-filter me-1"></i> Filter & Kolom
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="monitor-tab" data-bs-toggle="tab" data-bs-target="#monitor-pane" type="button" role="tab" aria-controls="monitor-pane" aria-selected="false">
                        <i class="fas fa-chart-area me-1"></i> Recap & Heat Map
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="export-tab" data-bs-toggle="tab" data-bs-target="#export-pane" type="button" role="tab" aria-controls="export-pane" aria-selected="false">
                        <i class="fas fa-file-export me-1"></i> Export
                    </button>
                </li>
            </ul>

            <div class="tab-content" id="howtoTabContent">
                <div class="tab-pane fade show active" id="akun-pane" role="tabpanel" aria-labelledby="akun-tab">
                    <div class="howto-pane-title">Registrasi dan Login</div>
                    <ol class="mb-0">
                        <li>Buka <code>/register</code>, lalu isi Nama Lengkap, Username, Divisi, Email, Password, dan Konfirmasi Password.</li>
                        <li>Klik <strong>Register</strong> untuk membuat akun.</li>
                        <li>Masuk ke <code>/login</code>, masukkan email dan password.</li>
                        <li>Setelah login, Anda langsung diarahkan ke halaman <strong>Risk Register</strong>.</li>
                    </ol>
                </div>

                <div class="tab-pane fade" id="addrisk-pane" role="tabpanel" aria-labelledby="addrisk-tab">
                    <div class="howto-pane-title">Tambah Risk Baru</div>
                    <ol class="mb-0">
                        <li>Buka menu <strong>Risk Register</strong> lalu klik tombol <strong>Add Risk</strong>.</li>
                        <li>Isi setiap tab: Basic Info, Inherent Analysis, Risk Treatment, dan Residual Analysis.</li>
                        <li>Klik <strong>Save</strong> atau <strong>Submit</strong> untuk menyimpan data.</li>
                    </ol>
                    <div class="howto-note">
                        Pastikan owner, score, dan treatment sudah sesuai sebelum submit.
                    </div>
                </div>

                <div class="tab-pane fade" id="kelola-pane" role="tabpanel" aria-labelledby="kelola-tab">
                    <div class="howto-pane-title">Lihat, Edit, dan Hapus Risk</div>
                    <ol class="mb-0">
                        <li><strong>View:</strong> klik tombol ikon mata untuk melihat detail lengkap risk.</li>
                        <li><strong>Edit:</strong> klik ikon edit, ubah data yang dibutuhkan, lalu klik <strong>Update</strong>.</li>
                        <li><strong>Delete:</strong> klik ikon trash dan konfirmasi saat popup muncul.</li>
                    </ol>
                </div>

                <div class="tab-pane fade" id="filter-pane" role="tabpanel" aria-labelledby="filter-tab">
                    <div class="howto-pane-title">Filter Owner, Preset Kolom, dan Search</div>
                    <ul class="mb-0">
                        <li>Gunakan <strong>Filter Owner</strong> untuk menampilkan owner tertentu.</li>
                        <li>Gunakan <strong>Column Preset</strong> untuk mode Overview, Full Table, Analysis, Treatment, atau Quantitative.</li>
                        <li>Gunakan kotak search tabel untuk mencari kata kunci risk dengan cepat.</li>
                    </ul>
                </div>

                <div class="tab-pane fade" id="monitor-pane" role="tabpanel" aria-labelledby="monitor-tab">
                    <div class="howto-pane-title">Monitoring Risiko</div>
                    <ul class="mb-0">
                        <li><strong>Heat Map:</strong> melihat sebaran level risiko pada matriks.</li>
                        <li><strong>Recap:</strong> melihat ringkasan metrik risiko berdasarkan kategori atau radar code.</li>
                    </ul>
                </div>

                <div class="tab-pane fade" id="export-pane" role="tabpanel" aria-labelledby="export-tab">
                    <div class="howto-pane-title">Export Laporan</div>
                    <ol class="mb-0">
                        <li>Di halaman Risk Register, klik tombol <strong>Export</strong>.</li>
                        <li>Pilih scope dan format (XLS/PDF/PPTX) sesuai kebutuhan.</li>
                        <li>File akan otomatis terunduh ke perangkat Anda.</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
@endsection
