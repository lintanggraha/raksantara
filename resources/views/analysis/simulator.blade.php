@extends('layouts.tabler')

@section('title', '"What-If" Compliance Simulator & Scenario Engine - Raksantara')

@push('styles')
<style>
.sim-page {
    --ink: #0f172a;
    --muted: #475569;
    --line: rgba(15, 23, 42, 0.1);
    --shell: linear-gradient(180deg, #f8fafc 0%, #f1f5f9 100%);
    color: var(--ink);
    padding: .25rem;
    border-radius: 32px;
    background: var(--shell);
}
.sim-shell { display: grid; gap: 1rem; }
.sim-hero {
    display: grid; grid-template-columns: 1.6fr .9fr; gap: 1.25rem;
    align-items: stretch; min-height: 280px;
    padding: 1.45rem; border-radius: 28px; overflow: hidden; position: relative;
    background:
        radial-gradient(circle at top right, rgba(254, 215, 170, .85), transparent 32%),
        radial-gradient(circle at bottom left, rgba(253, 230, 138, .6), transparent 28%),
        linear-gradient(135deg, #1e1b4b 0%, #312e81 45%, #4f46e5 100%);
    box-shadow: 0 20px 44px rgba(49, 46, 129, .15);
}
.sim-hero > * { position: relative; z-index: 1; }
.sim-kicker {
    display: inline-flex; align-items: center; gap: .45rem; padding: .35rem .7rem;
    border-radius: 999px; background: rgba(255, 255, 255, .18); color: #e0e7ff;
    font-size: .75rem; font-weight: 700; text-transform: uppercase; letter-spacing: .08em;
}
.sim-title { margin: 1rem 0 .7rem; color: #ffffff; font-size: clamp(2rem, 3.8vw, 2.75rem); font-weight: 800; line-height: 1.05; }
.sim-lede { margin: 0; max-width: 680px; color: rgba(224, 231, 255, .9); line-height: 1.65; font-size: .95rem; }

.sim-panel {
    padding: 1.35rem; border-radius: 20px; border: 1px solid var(--line);
    background: #ffffff; box-shadow: 0 14px 28px rgba(15, 23, 42, .04);
}
.sim-panel-head { display: flex; align-items: center; justify-content: space-between; gap: .85rem; margin-bottom: 1rem; }
.sim-panel-head h3 { margin: 0; font-size: 1.15rem; font-weight: 800; color: #0f172a; }

/* Step navigation */
.step-nav { display: flex; justify-content: space-between; position: relative; margin-bottom: 2rem; }
.step-nav::before {
    content: ''; position: absolute; top: 18px; left: 20px; right: 20px; height: 3px;
    background: #e2e8f0; z-index: 1;
}
.step-nav-progress {
    position: absolute; top: 18px; left: 20px; height: 3px;
    background: #4f46e5; z-index: 2; transition: width .3s ease;
}
.step-node {
    position: relative; z-index: 3; display: flex; flex-direction: column; align-items: center;
    cursor: pointer;
}
.step-circle {
    width: 38px; height: 38px; border-radius: 50%; background: #ffffff;
    border: 3px solid #cbd5e1; display: flex; align-items: center; justify-content: center;
    font-weight: 800; font-size: .88rem; color: #64748b; transition: all .2s ease;
}
.step-node.active .step-circle { border-color: #4f46e5; background: #4f46e5; color: #ffffff; box-shadow: 0 0 0 4px rgba(79, 70, 229, .2); }
.step-node.completed .step-circle { border-color: #10b981; background: #10b981; color: #ffffff; }
.step-label { margin-top: .45rem; font-size: .75rem; font-weight: 700; color: #64748b; }
.step-node.active .step-label { color: #4f46e5; }

/* Option Cards */
.sim-opt-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: .85rem; }
.sim-opt-card {
    border: 2px solid var(--line); border-radius: 16px; padding: 1.15rem;
    background: #ffffff; cursor: pointer; transition: all .15s ease;
    display: flex; flex-direction: column; position: relative;
}
.sim-opt-card:hover { transform: translateY(-2px); border-color: #818cf8; box-shadow: 0 8px 18px rgba(79, 70, 229, .08); }
.sim-opt-card.selected { border-color: #4f46e5; background: #f5f3ff; box-shadow: 0 8px 20px rgba(79, 70, 229, .12); }
.sim-opt-card strong { font-size: .95rem; color: #0f172a; margin-top: .5rem; display: block; }
.sim-opt-card p { font-size: .8rem; color: var(--muted); margin: .3rem 0 0; line-height: 1.45; }
.sim-opt-icon {
    width: 2.4rem; height: 2.4rem; border-radius: 12px; display: inline-flex;
    align-items: center; justify-content: center; font-size: 1rem;
    background: #e0e7ff; color: #4338ca;
}
.sim-opt-card.selected .sim-opt-icon { background: #4f46e5; color: #ffffff; }

/* Findings & Roadmap */
.finding-card {
    border-radius: 16px; border: 1px solid var(--line); padding: 1.15rem;
    background: #ffffff; margin-bottom: .85rem; transition: all .15s ease;
}
.finding-card:hover { border-color: #cbd5e1; box-shadow: 0 6px 16px rgba(15, 23, 42, .04); }
.finding-card.critical { border-left: 5px solid #dc2626; }
.finding-card.high { border-left: 5px solid #ea580c; }
.finding-card.medium { border-left: 5px solid #ca8a04; }
.finding-card.low { border-left: 5px solid #16a34a; }

.timeline-phase {
    border-radius: 16px; border: 1px solid var(--line); background: #ffffff;
    padding: 1.15rem; margin-bottom: 1rem;
}
.phase-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: .85rem; }
.phase-badge { padding: .35rem .75rem; border-radius: 999px; font-size: .75rem; font-weight: 800; text-transform: uppercase; }

@media (max-width: 992px) {
    .sim-hero { grid-template-columns: 1fr; }
    .step-label { display: none; }
}
</style>
@endpush

@section('content')
<div class="sim-page">
    <div class="sim-shell">
        <!-- Hero Header -->
        <div class="sim-hero">
            <div>
                <span class="sim-kicker"><i class="fas fa-radar me-1"></i> Analisis Kepatuhan • Mesin Simulasi</span>
                <h1 class="sim-title">"What-If" Compliance Simulator</h1>
                <p class="sim-lede">
                    Simulasikan kewajiban regulasi dan risiko kepatuhan berdasarkan profil operasional organisasi Anda. Pilih industri, model komputasi awan, jenis data yang diproses, serta vendor alih daya untuk menghasilkan daftar kewajiban hukum otomatis beserta roadmap eksekusi 12 bulan.
                </p>
            </div>
            <div style="background: rgba(255, 255, 255, .12); border: 1px solid rgba(255, 255, 255, .25); border-radius: 20px; padding: 1.25rem; backdrop-filter: blur(8px); display: flex; flex-direction: column; justify-content: center;">
                <label style="font-size: .72rem; font-weight: 700; color: #fde68a; text-transform: uppercase;">Deteksi Otomatis Regulasi</label>
                <h3 style="color: #ffffff; font-size: 1.1rem; margin: .4rem 0 .6rem;">Hasil Spesifik & Aplikatif</h3>
                <p style="color: rgba(255, 255, 255, .82); font-size: .84rem; line-height: 1.5; margin: 0;">
                    Mengidentifikasi klausul mandatory dari POJK 11/2022, PADK 1/2026, PBI 02/2024, PADG 32/2025, UU PDP 27/2022, dan ISO 27001 secara presisi.
                </p>
            </div>
        </div>

        <!-- Wizard Panel -->
        <div class="sim-panel" id="sim-wizard-box">
            <!-- Step Navigation Progress -->
            <div class="step-nav">
                <div class="step-nav-progress" id="step-progress-bar" style="width: 0%;"></div>
                
                <div class="step-node active" data-step="1">
                    <div class="step-circle">1</div>
                    <span class="step-label">Industri</span>
                </div>
                <div class="step-node" data-step="2">
                    <div class="step-circle">2</div>
                    <span class="step-label">Lokasi Data</span>
                </div>
                <div class="step-node" data-step="3">
                    <div class="step-circle">3</div>
                    <span class="step-label">Tipe Data</span>
                </div>
                <div class="step-node" data-step="4">
                    <div class="step-circle">4</div>
                    <span class="step-label">Pihak Ketiga</span>
                </div>
                <div class="step-node" data-step="5">
                    <div class="step-circle">5</div>
                    <span class="step-label">Standar Target</span>
                </div>
                <div class="step-node" data-step="6">
                    <div class="step-circle"><i class="fas fa-flag-checkered"></i></div>
                    <span class="step-label">Hasil</span>
                </div>
            </div>

            <!-- STEP 1: Sektor & Industri -->
            <div class="sim-step-content" id="step-content-1">
                <div class="sim-panel-head">
                    <div>
                        <h3>Langkah 1: Pilih Sektor & Karakteristik Industri</h3>
                        <p class="text-muted mb-0 small">Pilih satu atau lebih sektor yang mencerminkan model bisnis organisasi Anda.</p>
                    </div>
                    <span class="badge bg-indigo-lt">Pilih Minimal 1</span>
                </div>

                <div class="sim-opt-grid">
                    <div class="sim-opt-card" data-field="industries" data-value="banking">
                        <div class="sim-opt-icon"><i class="fas fa-building-columns"></i></div>
                        <strong>Perbankan Umum & Syariah</strong>
                        <p>Bank konvensional, bank digital, BUK/BUS yang tunduk pada POJK 11/2022, PADK 1/2026, dan SEOJK 29/2022.</p>
                    </div>
                    <div class="sim-opt-card" data-field="industries" data-value="fintech">
                        <div class="sim-opt-icon"><i class="fas fa-credit-card"></i></div>
                        <strong>Penyelenggara Sistem Pembayaran (PJP/PIP)</strong>
                        <p>Dompet digital, payment gateway, switching, transfer dana yang tunduk pada PBI 02/2024 dan PADG 32/2025.</p>
                    </div>
                    <div class="sim-opt-card" data-field="industries" data-value="ecommerce">
                        <div class="sim-opt-icon"><i class="fas fa-cart-shopping"></i></div>
                        <strong>E-Commerce & Platform Digital</strong>
                        <p>Marketplace, SaaS B2B/B2C, aplikasi retail yang mengelola volume data akun dan transaksi masif.</p>
                    </div>
                    <div class="sim-opt-card" data-field="industries" data-value="health">
                        <div class="sim-opt-icon"><i class="fas fa-heart-pulse"></i></div>
                        <strong>Kesehatan & Telemedisin</strong>
                        <p>Rumah sakit, apotek digital, lab diagnostik yang memproses rekam medis dan data kesehatan pribadi spesifik.</p>
                    </div>
                    <div class="sim-opt-card" data-field="industries" data-value="public">
                        <div class="sim-opt-icon"><i class="fas fa-landmark"></i></div>
                        <strong>BUMN & Sektor Publik</strong>
                        <p>Badan usaha milik negara atau instansi pelayanan publik yang wajib mematuhi SPBE dan perlindungan data nasional.</p>
                    </div>
                    <div class="sim-opt-card" data-field="industries" data-value="enterprise">
                        <div class="sim-opt-icon"><i class="fas fa-briefcase"></i></div>
                        <strong>Korporasi Umum & Manufaktur</strong>
                        <p>Perusahaan swasta nasional / multinasional yang berfokus pada ketahanan rantai pasok dan keamanan siber ISMS.</p>
                    </div>
                </div>
            </div>

            <!-- STEP 2: Lokasi & Penempatan Data -->
            <div class="sim-step-content d-none" id="step-content-2">
                <div class="sim-panel-head">
                    <div>
                        <h3>Langkah 2: Lokasi & Model Pusat Data (DC / DRC)</h3>
                        <p class="text-muted mb-0 small">Tentukan di mana data utama nasabah dan sistem elektronik beroperasi.</p>
                    </div>
                    <span class="badge bg-indigo-lt">Pilih Infrastruktur</span>
                </div>

                <div class="sim-opt-grid">
                    <div class="sim-opt-card" data-field="locations" data-value="onprem">
                        <div class="sim-opt-icon"><i class="fas fa-server"></i></div>
                        <strong>Pusat Data On-Premise Indonesia</strong>
                        <p>Data center dan DRC mandiri fisik yang berada di dalam wilayah kedaulatan Negara Kesatuan Republik Indonesia.</p>
                    </div>
                    <div class="sim-opt-card" data-field="locations" data-value="local_cloud">
                        <div class="sim-opt-icon"><i class="fas fa-cloud"></i></div>
                        <strong>Cloud Domestik Indonesia (Region Jakarta)</strong>
                        <p>Menggunakan penyedia cloud global/lokal yang memiliki data center fisik di Indonesia (AWS/GCP/Azure Jakarta Region).</p>
                    </div>
                    <div class="sim-opt-card" data-field="locations" data-value="hybrid">
                        <div class="sim-opt-icon"><i class="fas fa-network-wired"></i></div>
                        <strong>Hybrid Multi-Cloud</strong>
                        <p>Kombinasi antara server lokal on-premise untuk data kritikal dan cloud untuk skalabilitas beban kerja aplikasi.</p>
                    </div>
                    <div class="sim-opt-card" data-field="locations" data-value="crossborder">
                        <div class="sim-opt-icon"><i class="fas fa-globe"></i></div>
                        <strong>Penempatan Luar Negeri (Cross-Border)</strong>
                        <p>Data center, DRC, atau sistem pemrosesan transaksi berlokasi di luar negeri (Memicu kewajiban izin OJK Format 4.1/4.2!).</p>
                    </div>
                </div>
            </div>

            <!-- STEP 3: Tipe Data -->
            <div class="sim-step-content d-none" id="step-content-3">
                <div class="sim-panel-head">
                    <div>
                        <h3>Langkah 3: Karakteristik Data yang Dikelola</h3>
                        <p class="text-muted mb-0 small">Kategori data menentukan kewajiban DPIA, DPO, dan enkripsi sesuai UU PDP 27/2022.</p>
                    </div>
                    <span class="badge bg-indigo-lt">Pilih Kategori Data</span>
                </div>

                <div class="sim-opt-grid">
                    <div class="sim-opt-card" data-field="dataTypes" data-value="general_pdp">
                        <div class="sim-opt-icon"><i class="fas fa-id-card"></i></div>
                        <strong>Data Pribadi Umum</strong>
                        <p>Nama lengkap, email, nomor HP, jenis kelamin, alamat, dan kombinasi data identitas dasar pengguna.</p>
                    </div>
                    <div class="sim-opt-card" data-field="dataTypes" data-value="specific_pdp">
                        <div class="sim-opt-icon"><i class="fas fa-fingerprint"></i></div>
                        <strong>Data Pribadi Spesifik / Sensitif</strong>
                        <p>Data biometrik, riwayat medis/kesehatan, genetika, anak di bawah umur (Wajib DPIA & DPO berdasar Pasal 4 & 53!).</p>
                    </div>
                    <div class="sim-opt-card" data-field="dataTypes" data-value="financial_tx">
                        <div class="sim-opt-icon"><i class="fas fa-money-bill-transfer"></i></div>
                        <strong>Data Finansial & Kartu Pembayaran</strong>
                        <p>PIN transaksi, nomor kartu kredit/debit, saldo rekening, riwayat mutasi transaksi perbankan.</p>
                    </div>
                    <div class="sim-opt-card" data-field="dataTypes" data-value="trade_secret">
                        <div class="sim-opt-icon"><i class="fas fa-key"></i></div>
                        <strong>Rahasia Dagang & Kode Sumber</strong>
                        <p>Hak kekayaan intelektual, source code algoritma proprietary, strategi bisnis bernilai komersial tinggi.</p>
                    </div>
                </div>
            </div>

            <!-- STEP 4: Pihak Ketiga & Model Operasional -->
            <div class="sim-step-content d-none" id="step-content-4">
                <div class="sim-panel-head">
                    <div>
                        <h3>Langkah 4: Alih Daya & Ketergantungan Pihak Ketiga</h3>
                        <p class="text-muted mb-0 small">Hubungan dengan vendor TI menentukan tata kelola PPJTI dan klausul audit regulator.</p>
                    </div>
                    <span class="badge bg-indigo-lt">Model Ketergantungan</span>
                </div>

                <div class="sim-opt-grid">
                    <div class="sim-opt-card" data-field="usages" data-value="inhouse">
                        <div class="sim-opt-icon"><i class="fas fa-code"></i></div>
                        <strong>Internal Mandiri (In-House)</strong>
                        <p>Seluruh pengembangan aplikasi dan operasional server dikelola langsung oleh tim internal perusahaan.</p>
                    </div>
                    <div class="sim-opt-card" data-field="usages" data-value="foreign_vendor">
                        <div class="sim-opt-icon"><i class="fas fa-handshake-angle"></i></div>
                        <strong>Alih Daya Vendor Asing (PPJTI Luar Negeri)</strong>
                        <p>Menggunakan vendor perangkat lunak inti atau infrastruktur yang beroperasi dari yurisdiksi luar negeri.</p>
                    </div>
                    <div class="sim-opt-card" data-field="usages" data-value="global_saas">
                        <div class="sim-opt-icon"><i class="fas fa-cloud-arrow-up"></i></div>
                        <strong>Layanan SaaS Global</strong>
                        <p>Ketergantungan pada Salesforce, Microsoft 365, Google Workspace, atau Workday untuk operasional harian.</p>
                    </div>
                    <div class="sim-opt-card" data-field="usages" data-value="open_api">
                        <div class="sim-opt-icon"><i class="fas fa-plug"></i></div>
                        <strong>Konektivitas Open API / Mitra Ekosistem</strong>
                        <p>Membuka integrasi API perbankan/pembayaran kepada pihak ketiga (Standar SNAP Bank Indonesia).</p>
                    </div>
                </div>
            </div>

            <!-- STEP 5: Target Regulasi -->
            <div class="sim-step-content d-none" id="step-content-5">
                <div class="sim-panel-head">
                    <div>
                        <h3>Langkah 5: Standar & Sertifikasi Sasaran</h3>
                        <p class="text-muted mb-0 small">Pilih regulasi yang ingin Anda verifikasi tingkat kesiapannya.</p>
                    </div>
                    <span class="badge bg-indigo-lt">Opsional / Rekomendasi</span>
                </div>

                <div class="sim-opt-grid">
                    <div class="sim-opt-card selected" data-field="targets" data-value="pdp">
                        <div class="sim-opt-icon"><i class="fas fa-user-shield"></i></div>
                        <strong>UU PDP No. 27/2022</strong>
                        <p>Kepatuhan privasi dan hak subjek data nasional Republik Indonesia.</p>
                    </div>
                    <div class="sim-opt-card selected" data-field="targets" data-value="iso27001">
                        <div class="sim-opt-icon"><i class="fas fa-shield-alt"></i></div>
                        <strong>ISO 27001:2022</strong>
                        <p>Sistem Manajemen Keamanan Informasi (ISMS) berstandar global.</p>
                    </div>
                    <div class="sim-opt-card" data-field="targets" data-value="ojk">
                        <div class="sim-opt-icon"><i class="fas fa-landmark"></i></div>
                        <strong>Regulasi Perbankan OJK</strong>
                        <p>POJK 11/2022, PADK 1/2026, dan SEOJK 29/2022.</p>
                    </div>
                    <div class="sim-opt-card" data-field="targets" data-value="bi">
                        <div class="sim-opt-icon"><i class="fas fa-university"></i></div>
                        <strong>Regulasi Sistem Pembayaran BI</strong>
                        <p>PBI 02/2024 dan PADG 32/2025.</p>
                    </div>
                    <div class="sim-opt-card" data-field="targets" data-value="nist">
                        <div class="sim-opt-icon"><i class="fas fa-compass"></i></div>
                        <strong>NIST CSF 2.0</strong>
                        <p>National Institute of Standards & Technology Cybersecurity Framework.</p>
                    </div>
                    <div class="sim-opt-card" data-field="targets" data-value="owasp">
                        <div class="sim-opt-icon"><i class="fas fa-bug"></i></div>
                        <strong>OWASP Top 10 & ASVS</strong>
                        <p>Keamanan kode aplikasi dan standar verifikasi audit pengujian.</p>
                    </div>
                </div>
            </div>

            <!-- STEP 6: Hasil Simulasi & Roadmap -->
            <div class="sim-step-content d-none" id="step-content-6">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <h2 class="mb-1 fw-bold text-dark"><i class="fas fa-radar text-primary me-2"></i> Hasil Analisis & Temuan Kepatuhan</h2>
                        <p class="text-muted mb-0 small">Berdasarkan profil simulasi yang Anda inputkan, terdeteksi kewajiban berikut:</p>
                    </div>
                    <div class="d-flex gap-2">
                        <button class="btn btn-outline-primary btn-sm" onclick="window.print()">
                            <i class="fas fa-print me-1"></i> Cetak Laporan PDF
                        </button>
                        <button class="btn btn-outline-secondary btn-sm" id="btn-restart-sim">
                            <i class="fas fa-rotate me-1"></i> Mulai Ulang
                        </button>
                    </div>
                </div>

                <!-- Metrics Top Banner -->
                <div class="row g-3 mb-4">
                    <div class="col-md-3 col-6">
                        <div class="p-3 rounded-3 bg-light border text-center">
                            <small class="text-muted fw-bold text-uppercase d-block" style="font-size: .7rem;">Tingkat Risiko Inheren</small>
                            <strong class="fs-2 text-danger" id="res-risk-rating">TINGGI</strong>
                        </div>
                    </div>
                    <div class="col-md-3 col-6">
                        <div class="p-3 rounded-3 bg-light border text-center">
                            <small class="text-muted fw-bold text-uppercase d-block" style="font-size: .7rem;">Total Kewajiban Wajib</small>
                            <strong class="fs-2 text-dark" id="res-total-findings">0</strong>
                        </div>
                    </div>
                    <div class="col-md-3 col-6">
                        <div class="p-3 rounded-3 bg-light border text-center">
                            <small class="text-muted fw-bold text-uppercase d-block" style="font-size: .7rem;">Kewajiban Kritikal / Segera</small>
                            <strong class="fs-2 text-danger" id="res-critical-findings">0</strong>
                        </div>
                    </div>
                    <div class="col-md-3 col-6">
                        <div class="p-3 rounded-3 bg-light border text-center">
                            <small class="text-muted fw-bold text-uppercase d-block" style="font-size: .7rem;">Estimasi Waktu Kepatuhan</small>
                            <strong class="fs-2 text-primary">12 Bulan</strong>
                        </div>
                    </div>
                </div>

                <!-- Findings List -->
                <div class="mb-4">
                    <h4 class="fw-bold mb-3"><i class="fas fa-triangle-exclamation text-warning me-2"></i> Daftar Kewajiban Hukum & Kontrol Mandatori</h4>
                    <div id="findings-container">
                        <!-- Populated by JS -->
                    </div>
                </div>

                <!-- 12-Month Execution Roadmap -->
                <div>
                    <h4 class="fw-bold mb-3"><i class="fas fa-timeline text-primary me-2"></i> Roadmap Eksekusi 12 Bulan (4 Fase Strategis)</h4>
                    <div id="roadmap-container">
                        <!-- Populated by JS -->
                    </div>
                </div>
            </div>

            <!-- Navigation Buttons -->
            <div class="d-flex justify-content-between align-items-center mt-4 pt-3 border-top" id="sim-nav-buttons">
                <button class="btn btn-outline-secondary" id="btn-sim-prev" style="visibility: hidden;">
                    <i class="fas fa-arrow-left me-1"></i> Sebelumnya
                </button>
                <button class="btn btn-primary" id="btn-sim-next">
                    Selanjutnya <i class="fas fa-arrow-right ms-1"></i>
                </button>
            </div>
        </div>

    </div>
</div>
@endsection

@push('scripts')
<script>
let currentStep = 1;
const scenario = {
    industries: [],
    locations: [],
    dataTypes: [],
    usages: [],
    targets: ['pdp', 'iso27001']
};

document.addEventListener('DOMContentLoaded', function () {
    const prevBtn = document.getElementById('btn-sim-prev');
    const nextBtn = document.getElementById('btn-sim-next');
    const progressBar = document.getElementById('step-progress-bar');
    const stepNodes = document.querySelectorAll('.step-node');

    // Toggle card selection
    document.querySelectorAll('.sim-opt-card').forEach(card => {
        card.addEventListener('click', () => {
            const field = card.dataset.field;
            const val = card.dataset.value;

            card.classList.toggle('selected');

            if (card.classList.contains('selected')) {
                if (!scenario[field].includes(val)) scenario[field].push(val);
            } else {
                scenario[field] = scenario[field].filter(v => v !== val);
            }
        });
    });

    function updateStepView() {
        // Toggle step content
        for (let i = 1; i <= 6; i++) {
            const el = document.getElementById(`step-content-${i}`);
            if (el) {
                if (i === currentStep) el.classList.remove('d-none');
                else el.classList.add('d-none');
            }
        }

        // Update step nodes
        stepNodes.forEach(node => {
            const stepNum = parseInt(node.dataset.step);
            node.classList.remove('active', 'completed');
            if (stepNum === currentStep) node.classList.add('active');
            else if (stepNum < currentStep) node.classList.add('completed');
        });

        // Progress bar width
        const pct = ((currentStep - 1) / 5) * 100;
        progressBar.style.width = pct + '%';

        // Buttons
        if (currentStep === 1) {
            prevBtn.style.visibility = 'hidden';
            nextBtn.innerHTML = 'Selanjutnya <i class="fas fa-arrow-right ms-1"></i>';
        } else if (currentStep < 5) {
            prevBtn.style.visibility = 'visible';
            nextBtn.innerHTML = 'Selanjutnya <i class="fas fa-arrow-right ms-1"></i>';
        } else if (currentStep === 5) {
            prevBtn.style.visibility = 'visible';
            nextBtn.innerHTML = '<i class="fas fa-play me-1"></i> Jalankan Simulasi';
        } else if (currentStep === 6) {
            document.getElementById('sim-nav-buttons').style.display = 'none';
            generateSimulationResults();
        }
    }

    nextBtn.addEventListener('click', () => {
        if (currentStep < 6) {
            currentStep++;
            updateStepView();
        }
    });

    prevBtn.addEventListener('click', () => {
        if (currentStep > 1) {
            currentStep--;
            updateStepView();
        }
    });

    stepNodes.forEach(node => {
        node.addEventListener('click', () => {
            const s = parseInt(node.dataset.step);
            if (s <= currentStep) {
                currentStep = s;
                if (currentStep < 6) document.getElementById('sim-nav-buttons').style.display = 'flex';
                updateStepView();
            }
        });
    });

    document.getElementById('btn-restart-sim')?.addEventListener('click', () => {
        currentStep = 1;
        document.getElementById('sim-nav-buttons').style.display = 'flex';
        updateStepView();
    });

    function generateSimulationResults() {
        const findings = [];

        // Check conditions
        const isBanking = scenario.industries.includes('banking');
        const isFintech = scenario.industries.includes('fintech');
        const isCrossborder = scenario.locations.includes('crossborder');
        const hasSpecificData = scenario.dataTypes.includes('specific_pdp');
        const hasFinancialData = scenario.dataTypes.includes('financial_tx');
        const hasForeignVendor = scenario.usages.includes('foreign_vendor');
        const hasOpenApi = scenario.usages.includes('open_api');

        if (isBanking) {
            findings.push({
                severity: 'critical',
                title: 'Kewajiban Pengkinian Kebijakan ke PADK 1/2026',
                citation: 'PADK 1/2026 Pasal 1 & 4 (Mencabut SEOJK 21/2017)',
                deadline: '1 Maret 2026',
                desc: 'Seluruh kebijakan, standar, dan SOP manajemen risiko TI Bank wajib disesuaikan dengan PADK 1/2026 dan SEOJK Siber 29/2022.',
                remediation: 'Lakukan gap assessment internal dan susun compliance action plan sebelum tenggat efektif.'
            });
            findings.push({
                severity: 'high',
                title: 'Pelaporan Kondisi TI Tahunan (LKTPTI) via Sistem OJK',
                citation: 'POJK 11/2022 Pasal 3 & PADK Format 3.2',
                deadline: 'Paling lambat 21 Januari tahun berikutnya',
                desc: 'Bank wajib menyusun dan menyampaikan 17 subformat LKTPTI yang memuat profil aplikasi, DRC, terminal perbankan, dan biaya TI.',
                remediation: 'Bentuk taskforce lintas fungsi untuk memvalidasi kelengkapan data 17 subformat pelaporan OJK.'
            });
        }

        if (isCrossborder) {
            findings.push({
                severity: 'critical',
                title: 'Permohonan Izin Penempatan Sistem Elektronik Luar Negeri',
                citation: 'POJK 11/2022 Pasal 21 & PADK Format 4.1',
                deadline: 'Sebelum penempatan dilakukan',
                desc: 'Penempatan data center atau DRC di luar wilayah Indonesia wajib memperoleh persetujuan izin tertulis terlebih dahulu dari OJK.',
                remediation: 'Siapkan dokumen due diligence teknis, analisis risiko, konsep perjanjian, serta jaminan akses audit bagi regulator.'
            });
        }

        if (hasSpecificData) {
            findings.push({
                severity: 'critical',
                title: 'Penunjukan DPO & Pelaksanaan Penilaian Dampak (DPIA)',
                citation: 'UU PDP No. 27/2022 Pasal 39 & Pasal 53',
                deadline: 'Wajib sebelum pemrosesan berlangsung',
                desc: 'Pengendali data yang memproses data pribadi spesifik atau skala besar wajib menunjuk Data Protection Officer (DPO) dan melakukan DPIA.',
                remediation: 'Terbitkan SK Penunjukan Pejabat DPO independen dan jalankan DPIA formal atas arsitektur data sensitif.'
            });
        }

        if (hasFinancialData || isFintech) {
            findings.push({
                severity: 'high',
                title: 'Implementasi Fraud Detection System (FDS) Real-Time',
                citation: 'PBI 02/2024 & PADG 32/2025 Bab Pengaturan SP',
                deadline: 'Berkelanjutan',
                desc: 'Penyelenggara wajib mengoperasikan sistem deteksi fraud secara otomatis dan real-time untuk mencegah transaksi anomali.',
                remediation: 'Konfigurasikan alert anomali, rule-based fraud detection, dan integrasikan dengan saluran notifikasi instan nasabah.'
            });
        }

        if (hasForeignVendor) {
            findings.push({
                severity: 'high',
                title: 'Klausul Wajib Audit Regulator & Data Protection Agreement (DPA)',
                citation: 'POJK 11/2022 Bab V & UU PDP Pasal 38',
                deadline: 'Saat penandatanganan kontrak vendor',
                desc: 'Kontrak dengan pihak penyedia jasa TI (PPJTI) asing wajib memuat klausul hak audit regulator (OJK/BI) dan larangan transfer sub-processor tanpa izin.',
                remediation: 'Review seluruh SLA dan amandemen addendum kontrak PPJTI untuk memasukkan klausul kepatuhan regulasi Indonesia.'
            });
        }

        if (hasOpenApi) {
            findings.push({
                severity: 'medium',
                title: 'Standarisasi Keamanan API & Standar Nasional SNAP BI',
                citation: 'PADG Standar Nasional Open API Pembayaran (SNAP)',
                deadline: 'Saat implementasi interface API',
                desc: 'API publik atau interkoneksi mitra wajib menerapkan enkripsi TLS 1.3, tanda tangan digital (asymmetric signature), dan rate limiting.',
                remediation: 'Lakukan audit keamanan aplikasi mengacu pada OWASP ASVS Chapter V2 & V14.'
            });
        }

        // Generic fallback finding
        findings.push({
            severity: 'high',
            title: 'Kesiapan Notifikasi Kegagalan Pelindungan Data (3x24 Jam)',
            citation: 'UU PDP No. 27/2022 Pasal 46 & SEOJK 29/2022',
            deadline: 'Maksimal 3x24 Jam sejak insiden terkonfirmasi',
            desc: 'Kewajiban memberitahukan insiden kebocoran data pribadi kepada lembaga otoritas dan seluruh subjek data yang terdampak.',
            remediation: 'Bentuk Tim CSIRT/Tanggap Darurat dan susun playbook eskalasi serta template notifikasi resmi insiden.'
        });

        // Render findings
        const container = document.getElementById('findings-container');
        container.innerHTML = '';

        document.getElementById('res-total-findings').textContent = findings.length;
        const criticalCount = findings.filter(f => f.severity === 'critical').length;
        document.getElementById('res-critical-findings').textContent = criticalCount;
        document.getElementById('res-risk-rating').textContent = criticalCount > 0 ? 'TINGGI' : 'MODERAT';

        findings.forEach(f => {
            const card = document.createElement('div');
            card.className = `finding-card ${f.severity}`;
            card.innerHTML = `
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <span class="badge ${f.severity === 'critical' ? 'bg-danger' : (f.severity === 'high' ? 'bg-warning text-dark' : 'bg-primary')}">${f.severity.toUpperCase()}</span>
                    <small class="text-danger fw-bold"><i class="fas fa-clock me-1"></i> Tenggat: ${f.deadline}</small>
                </div>
                <strong style="font-size: 1rem; color: #0f172a;">${f.title}</strong>
                <span class="text-secondary small d-block mb-2 font-monospace">${f.citation}</span>
                <p class="mb-2 text-dark" style="font-size: .85rem; line-height: 1.5;">${f.desc}</p>
                <div class="p-2 rounded bg-light border">
                    <small class="text-primary fw-bold"><i class="fas fa-wrench me-1"></i> Rekomendasi Tindakan:</small>
                    <p class="mb-0 small text-muted">${f.remediation}</p>
                </div>
            `;
            container.appendChild(card);
        });

        // Render Roadmap 12 Months
        const roadmapContainer = document.getElementById('roadmap-container');
        roadmapContainer.innerHTML = `
            <div class="timeline-phase" style="border-left: 5px solid #dc2626;">
                <div class="phase-header">
                    <div>
                        <strong style="color: #dc2626; font-size: 1rem;">Fase 1 (Bulan 1–3) — Fondasi & Mitigasi Darurat</strong>
                        <p class="text-muted small mb-0">Menutup celah regulasi berisiko sanksi langsung dan pembentukan organ pengawas.</p>
                    </div>
                    <span class="phase-badge bg-danger-lt">Bulan 1-3</span>
                </div>
                <ul class="mb-0 small text-dark" style="line-height: 1.6;">
                    <li>Penunjukan resmi Data Protection Officer (DPO) dan pembentukan Komite Pengarah TI.</li>
                    <li>Pelaksanaan Gap Assessment terhadap PADK 1/2026 dan inventarisasi data sensitif (DPIA).</li>
                    <li>Penyusunan Playbook Notifikasi Insiden Siber 24 Jam (OJK) dan 3x24 Jam (UU PDP).</li>
                </ul>
            </div>

            <div class="timeline-phase" style="border-left: 5px solid #ea580c;">
                <div class="phase-header">
                    <div>
                        <strong style="color: #ea580c; font-size: 1rem;">Fase 2 (Bulan 4–6) — Pembangunan Kontrol Inti</strong>
                        <p class="text-muted small mb-0">Implementasi kontrol teknis, hardening server, dan amandemen kontrak pihak ketiga.</p>
                    </div>
                    <span class="phase-badge bg-warning-lt">Bulan 4-6</span>
                </div>
                <ul class="mb-0 small text-dark" style="line-height: 1.6;">
                    <li>Penyesuaian klausul kontrak seluruh PPJTI / vendor aktif dengan hak audit regulator.</li>
                    <li>Penerapan enkripsi data at-rest (AES-256) dan in-transit (TLS 1.3) pada database sensitif.</li>
                    <li>Implementasi kontrol akses multi-faktor (MFA) dan pemisahan tugas (Segregation of Duties).</li>
                </ul>
            </div>

            <div class="timeline-phase" style="border-left: 5px solid #ca8a04;">
                <div class="phase-header">
                    <div>
                        <strong style="color: #ca8a04; font-size: 1rem;">Fase 3 (Bulan 7–9) — Pematangan & Pengujian Ketahanan</strong>
                        <p class="text-muted small mb-0">Uji coba simulasi pemulihan bencana (DRP) dan pengujian kerentanan aplikasi.</p>
                    </div>
                    <span class="phase-badge bg-warning-lt">Bulan 7-9</span>
                </div>
                <ul class="mb-0 small text-dark" style="line-height: 1.6;">
                    <li>Pelaksanaan simulasi failover DRC dan uji pemulihan cadangan data (backup restore test).</li>
                    <li>Uji penetrasi (Penetration Testing) dan vulnerability assessment independen berstandar OWASP.</li>
                    <li>Tuning Fraud Detection System (FDS) dan integrasi logging tersentralisasi (SIEM).</li>
                </ul>
            </div>

            <div class="timeline-phase" style="border-left: 5px solid #16a34a;">
                <div class="phase-header">
                    <div>
                        <strong style="color: #16a34a; font-size: 1rem;">Fase 4 (Bulan 10–12) — Asurans & Pelaporan Regulator</strong>
                        <p class="text-muted small mb-0">Audit intern komprehensif dan submission laporan resmi tepat waktu.</p>
                    </div>
                    <span class="phase-badge bg-success-lt">Bulan 10-12</span>
                </div>
                <ul class="mb-0 small text-dark" style="line-height: 1.6;">
                    <li>Pelaksanaan Audit Intern TI tahunan dan dokumentasi tindak lanjut rekomendasi audit.</li>
                    <li>Penyusunan Rencana Strategis TI (RSTI) dan penyampaian ke OJK paling lambat akhir November.</li>
                    <li>Finalisasi kertas kerja 17 subformat LKTPTI untuk submission ke sistem pelaporan OJK (Januari).</li>
                </ul>
            </div>
        `;
    }
});
</script>
@endpush
