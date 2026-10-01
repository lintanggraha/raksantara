<?php

namespace Database\Seeders\Data;

class RiskDataBatch3
{
    public static function getData(): array
    {
        return [
            // 15. OM - IT OPERATION MANAGEMENT
            'OM - IT OPERATION MANAGEMENT' => [
                [
                    'title' => 'Penerapan Emergency Patch Melewati Prosedur Change Advisory Board (CAB) Tanpa Otorisasi',
                    'target' => 'Penerapan 100% change request melalui CAB approval dan post-implementation review terdokumentasi.',
                    'description' => 'Deploy hotfix darurat langsung ke server produksi tanpa pengujian regresi memicu service dependency failure.',
                    'category_id' => 7, 'cause_category_id' => 1, 'radar_code' => 'O1',
                    'inherent_p' => 4, 'inherent_i' => 4, 'residual_p' => 1, 'residual_i' => 2, 'impact_amount' => 850000000,
                    'cause' => 'Tekanan waktu penyelesaian insiden dan ketiadaan automated emergency change approval workflow.',
                    'existing_control' => 'Kebijakan Change Management manual menggunakan formulir email.',
                    'mitigation' => 'Implementasi ITSM change gatekeeper otomatis, automated rollback canary deployment, dan audit trail deployment pipeline.'
                ],
                [
                    'title' => 'Divergensi Konfigurasi Sistem (Configuration Drift) antara DC Utama dan DRC',
                    'target' => 'Paritas konfigurasi 100% antara Data Center utama dan Disaster Recovery Center.',
                    'description' => 'Parameter kernel dan versi library pada DRC tertinggal, mengakibatkan kegagalan saat simulasi disaster switchover.',
                    'category_id' => 7, 'cause_category_id' => 3, 'radar_code' => 'O2',
                    'inherent_p' => 4, 'inherent_i' => 5, 'residual_p' => 1, 'residual_i' => 2, 'impact_amount' => 1400000000,
                    'cause' => 'Perubahan konfigurasi di DC utama dilakukan secara manual tanpa sinkronisasi ke server DRC.',
                    'existing_control' => 'Dokumentasi manual spreadsheet dan drill DRC berkala setiap 6 bulan.',
                    'mitigation' => 'Penerapan Infrastructure as Code (Ansible/Terraform) dengan automated configuration compliance checker harian.'
                ],
                [
                    'title' => 'Kegagalan Sistem Akibat Partisi Disk Database Mengalami Saturasi Penuh (Disk Space Exhaustion)',
                    'target' => 'Kapasitas storage partisi database dan logging terjaga di bawah ambang batas 80%.',
                    'description' => 'Akumulasi binary log dan core dump tanpa rotasi otomatis menghabiskan sisa storage partisi data kritis.',
                    'category_id' => 7, 'cause_category_id' => 3, 'radar_code' => 'O1',
                    'inherent_p' => 3, 'inherent_i' => 4, 'residual_p' => 1, 'residual_i' => 2, 'impact_amount' => 720000000,
                    'cause' => 'Script log rotate gagal dieksekusi dan threshold alert monitoring disk diatur terlalu tinggi (95%).',
                    'existing_control' => 'Monitoring Zabbix/Prometheus untuk kapasitas disk server.',
                    'mitigation' => 'Automated log pruning, auto-expanding volume storage pada SAN, dan eskalasi proaktif alert disk pada 75% dan 85%.'
                ],
                [
                    'title' => 'Kejenuhan Peringatan (Alert Fatigue) Menyebabkan Notifikasi Insiden Kritis Terabaikan',
                    'target' => 'Penurunan alert volume hingga 70% dan resolusi 100% peringatan dengan severity High/Critical.',
                    'description' => 'Ribuan alert false-positive harian menyebabkan operator NOC mengabaikan peringatan disk degradation pada SAN storage.',
                    'category_id' => 7, 'cause_category_id' => 1, 'radar_code' => 'O4',
                    'inherent_p' => 4, 'inherent_i' => 4, 'residual_p' => 2, 'residual_i' => 2, 'impact_amount' => 680000000,
                    'cause' => 'Ketiadaan alert correlation, threshold sensitivitas yang tidak dituning, dan ketiadaan deduplikasi alarm.',
                    'existing_control' => 'Grup chat operasional Telegram/Slack penerima notifikasi monitoring.',
                    'mitigation' => 'Implementasi AIOps/alert suppression engine, dynamic baseline anomaly detection, dan integrasi automated PagerDuty.'
                ],
                [
                    'title' => 'Penyalahgunaan Akun Istimewa (Privileged Access Breakglass) Tanpa Tiket Insiden',
                    'target' => 'Seluruh sesi akses root/administrator tersinkronisasi dengan nomor tiket ITSM yang valid.',
                    'description' => 'Administrator menggunakan kredensial root darurat untuk aktivitas harian tanpa pencatatan audit trail yang jelas.',
                    'category_id' => 10, 'cause_category_id' => 1, 'radar_code' => 'O3',
                    'inherent_p' => 4, 'inherent_i' => 4, 'residual_p' => 1, 'residual_i' => 2, 'impact_amount' => 950000000,
                    'cause' => 'Prosedur check-out password root pada solusi PAM sering di-bypass menggunakan password lokal darurat.',
                    'existing_control' => 'Log review manual aktivitas user root oleh tim internal audit.',
                    'mitigation' => 'Enforcement Privileged Access Management (PAM) dengan session recording, rotasi password otomatis setelah sesi, dan mandatory ticket binding.'
                ],
                [
                    'title' => 'Keterlambatan Patch Keamanan OS dan Middleware Lebih dari 180 Hari',
                    'target' => 'Instalasi patch keamanan dengan severity Critical/High selesai dalam waktu maksimal 14 hari kerja.',
                    'description' => 'Kerentanan zero-day pada Apache dan Linux kernel tidak ditutup karena kekhawatiran merusak aplikasi legacy.',
                    'category_id' => 7, 'cause_category_id' => 3, 'radar_code' => 'O2',
                    'inherent_p' => 4, 'inherent_i' => 5, 'residual_p' => 2, 'residual_i' => 2, 'impact_amount' => 1350000000,
                    'cause' => 'Ketiadaan staging environment yang identik untuk menguji dampak patch sebelum masuk produksi.',
                    'existing_control' => 'Siklus patching ad-hoc tahunan saat jendela maintenance akhir tahun.',
                    'mitigation' => 'Standardisasi monthly patch cycle, automasi staging canary testing, dan implementasi virtual patching via WAF/IPS.'
                ],
                [
                    'title' => 'Kegagalan Verifikasi Integritas Backup (Restoration Verification Failure) Saat Bencana',
                    'target' => 'Pengujian uji coba restorasi data backup secara berkala dengan Success Rate 100%.',
                    'description' => 'File backup database harian berstatus valid tetapi rusak (corrupt) saat diuji coba restore di DRC.',
                    'category_id' => 7, 'cause_category_id' => 3, 'radar_code' => 'O1',
                    'inherent_p' => 3, 'inherent_i' => 5, 'residual_p' => 1, 'residual_i' => 2, 'impact_amount' => 2100000000,
                    'cause' => 'Hanya memverifikasi exit code script backup tanpa melakukan restore checksum verification otomatis.',
                    'existing_control' => 'Pengecekan ukuran file backup di storage lokal setiap pagi.',
                    'mitigation' => 'Automated monthly sandbox restore test, immutable backup object storage, dan hash checksum validation otomatis.'
                ],
                [
                    'title' => 'Pelanggaran SLA Penanganan Insiden Prioritas 1 (P1) Akibat Silo Shift Handover',
                    'target' => 'Rata-rata waktu tanggap insiden P1 (MTTR) di bawah 30 menit sesuai ketentuan SLA perbankan.',
                    'description' => 'Insiden pemadaman gateway pembayaran tertunda penanganannya selama 40 menit saat pergantian shift NOC malam.',
                    'category_id' => 1, 'cause_category_id' => 1, 'radar_code' => 'O4',
                    'inherent_p' => 3, 'inherent_i' => 4, 'residual_p' => 1, 'residual_i' => 2, 'impact_amount' => 580000000,
                    'cause' => 'Catatan operasional diserahterimakan secara lisan tanpa checklist digital mandatory shift log.',
                    'existing_control' => 'Buku log fisik penyerahan tugas shift operator.',
                    'mitigation' => 'Standardisasi electronic shift handover log pada ITSM tool, digital sign-off, dan mandatory overlapping shift 30 menit.'
                ],
                [
                    'title' => 'Insiden Gangguan Berulang (Recurring Outage) Akibat Analisis Akar Masalah (RCA) Tidak Efektif',
                    'target' => 'Penyelesaian tuntas akar masalah seluruh insiden major tanpa kejadian berulang dalam 90 hari.',
                    'description' => 'Server aplikasi mengalami restart berulang setiap hari Kamis akibat memory leak yang hanya ditangani dengan reboot rutin.',
                    'category_id' => 7, 'cause_category_id' => 3, 'radar_code' => 'O1',
                    'inherent_p' => 4, 'inherent_i' => 3, 'residual_p' => 1, 'residual_i' => 2, 'impact_amount' => 470000000,
                    'cause' => 'Teknisi fokus pada pemulihan cepat (workaround) tanpa mendedikasikan waktu untuk deep memory dump inspection.',
                    'existing_control' => 'Pembuatan dokumen RCA pasca insiden major dalam format Word.',
                    'mitigation' => 'Penerapan mandatory Problem Management process terpisah dari Incident Management dan audit post-mortem bulanan.'
                ],
                [
                    'title' => 'Keberadaan Aset Virtual Server Orphan (Shadow VM) Mengonsumsi Kapasitas dan Menimbulkan Celah',
                    'target' => 'Rekonsiliasi 100% aset virtual machine dengan CMDB aktif dan penonaktifan instance terlantar.',
                    'description' => 'VM testing lawas yang tidak dipantau terinfeksi malware dan menggunakan resource compute hypervisor produksi.',
                    'category_id' => 7, 'cause_category_id' => 3, 'radar_code' => 'O2',
                    'inherent_p' => 3, 'inherent_i' => 3, 'residual_p' => 1, 'residual_i' => 1, 'impact_amount' => 340000000,
                    'cause' => 'Proses de-provisioning VM setelah project selesai tidak dijalankan secara otomatis.',
                    'existing_control' => 'Inventarisasi manual aset server virtual setiap semester.',
                    'mitigation' => 'Integrasi vCenter/Nutanix dengan CMDB otomatis, automated tagging expiry date pada setiap VM baru, dan auto-quarantine.'
                ],
                [
                    'title' => 'Benturan Eksekusi Job ETL dan Batch Script Berat pada Jam Sibuk Transaksi Nasabah',
                    'target' => 'Isolasi 100% proses batch reporting dan ETL di luar jam operasional prime transaksi perbankan.',
                    'description' => 'Eksekusi cron ETL data warehouse pada pukul 14:00 WIB menyebabkan lonjakan CPU load 100% pada database core.',
                    'category_id' => 7, 'cause_category_id' => 3, 'radar_code' => 'O1',
                    'inherent_p' => 4, 'inherent_i' => 4, 'residual_p' => 1, 'residual_i' => 2, 'impact_amount' => 760000000,
                    'cause' => 'Penjadwalan cron dilakukan secara desentralisasi di masing-masing server tanpa enterprise scheduler terpusat.',
                    'existing_control' => 'Pengawasan manual dashboard utilization oleh duty manager.',
                    'mitigation' => 'Penerapan Enterprise Job Scheduling terpadu dengan resource quota governance dan offloading query ke read-replica.'
                ],
                [
                    'title' => 'Ketergantungan Kritis pada Pengetahuan Tunggal Personil (Single Person Dependency) di Tim Sysadmin',
                    'target' => 'Dokumentasi lengkap Standard Operating Procedure (SOP) dan rotasi peran teknis minimal 2 personil.',
                    'description' => 'Gangguan pada server switching tidak dapat diselesaikan selama 3 jam karena satu-satunya engineer yang memahami sedang sakit.',
                    'category_id' => 1, 'cause_category_id' => 1, 'radar_code' => 'O4',
                    'inherent_p' => 4, 'inherent_i' => 4, 'residual_p' => 2, 'residual_i' => 2, 'impact_amount' => 610000000,
                    'cause' => 'Arsitektur dan troubleshooting guide tersimpan di catatan personal tanpa standardisasi knowledge base perusahaan.',
                    'existing_control' => 'Panggilan darurat via telepon ke engineer saat berada di luar jam kerja.',
                    'mitigation' => 'Pembangunan internal engineering wiki yang terverifikasi, cross-training berkala, dan mandatory shadowing penanganan insiden.'
                ],
                [
                    'title' => 'Pergeseran Waktu Server (NTP Clock Drift) Mengacaukan Integritas Urutan Log Audit Transaksi',
                    'target' => 'Sinkronisasi waktu seluruh node server dan appliances di bawah batas toleransi 5 milidetik.',
                    'description' => 'Desinkronisasi jam antar cluster database menyebabkan transaksi rollback gagal dan ketidaksesuaian urutan mutasi buku besar.',
                    'category_id' => 7, 'cause_category_id' => 3, 'radar_code' => 'O2',
                    'inherent_p' => 3, 'inherent_i' => 4, 'residual_p' => 1, 'residual_i' => 1, 'impact_amount' => 520000000,
                    'cause' => 'NTP client lokal gagal menghubungi upstream time server akibat pemblokiran port UDP 123 oleh access-list firewall.',
                    'existing_control' => 'Pengecekan manual date/time server saat patching berkala.',
                    'mitigation' => 'Penyediaan redundant internal GPS Stratum-1 NTP server dengan monitoring otomatis peringatan time offset.'
                ],
                [
                    'title' => 'Padamnya Layanan Internal Akibat Kedaluwarsanya Sertifikat SSL/TLS Tanpa Notifikasi Awal',
                    'target' => 'Zero downtime akibat sertifikat kedaluwarsa dengan notifikasi peringatan H-60 dan auto-renewal.',
                    'description' => 'Sertifikat TLS internal API gateway habis masa berlaku pada akhir pekan, memutus komunikasi seluruh microservices.',
                    'category_id' => 7, 'cause_category_id' => 3, 'radar_code' => 'O1',
                    'inherent_p' => 4, 'inherent_i' => 4, 'residual_p' => 1, 'residual_i' => 1, 'impact_amount' => 890000000,
                    'cause' => 'Manajemen sertifikat dicatat manual di lembar kerja Excel tanpa integrasi automated alerting.',
                    'existing_control' => 'Review masa aktif sertifikat secara berkala oleh tim infrastruktur.',
                    'mitigation' => 'Implementasi Certificate Management System terpusat dengan automated cert renewal protocol (ACME) dan monitoring SSL exporter.'
                ],
                [
                    'title' => 'Kesalahan Manusia (Human Error) Saat Eksekusi Prosedur Manual Failover Bencana',
                    'target' => 'Otomatisasi 90% proses failover bencana dengan eliminasi intervensi manual rawan salah.',
                    'description' => 'Engineer salah memasukkan parameter IP address gateway saat cutover darurat, menyebabkan isolasi jaringan DRC.',
                    'category_id' => 1, 'cause_category_id' => 1, 'radar_code' => 'O4',
                    'inherent_p' => 3, 'inherent_i' => 5, 'residual_p' => 1, 'residual_i' => 2, 'impact_amount' => 1250000000,
                    'cause' => 'Instruksi runbook failover berbentuk narasi panjang tanpa script otomatis yang divalidasi.',
                    'existing_control' => 'Runbook dokumen PDF yang dicetak di ruang kontrol DRC.',
                    'mitigation' => 'Otomatisasi Disaster Recovery Orchestrator (1-click failover script) yang telah lolos simulasi berkala tanpa intervensi teks manual.'
                ],
                [
                    'title' => 'Kegagalan Pemenuhan SLA Respon Insiden oleh Penyedia Managed Service Provider (MSP)',
                    'target' => 'Kepatuhan vendor MSP terhadap SLA respon 15 menit dan penyelesaian insiden 2 jam sebesar 99.5%.',
                    'description' => 'Vendor outsourcing NOC lambat merespon pemadaman sistem core networking di cabang wilayah timur selama 3 jam.',
                    'category_id' => 1, 'cause_category_id' => 2, 'radar_code' => 'O5',
                    'inherent_p' => 3, 'inherent_i' => 4, 'residual_p' => 1, 'residual_i' => 2, 'impact_amount' => 460000000,
                    'cause' => 'Tingginya perputaran personil pada vendor MSP dan ketiadaan penalti denda SLA yang tegas.',
                    'existing_control' => 'Rapat evaluasi bulanan performa pihak ketiga.',
                    'mitigation' => 'Penerapan real-time SLA tracking dashboard terintegrasi API, klausul klaim penalti otomatis, dan opsi dual-vendor vendor support.'
                ],
                [
                    'title' => 'Penurunan Performa (Noisy Neighbor) Akibat Overcommit Resource pada Cluster Virtualisasi',
                    'target' => 'Rasio alokasi memory/CPU vCPU-to-pCPU terjaga di bawah 2:1 untuk beban kerja tier-1.',
                    'description' => 'Aplikasi batch analytics yang agresif menyerap shared memory, memperlambat latensi respon aplikasi core banking.',
                    'category_id' => 7, 'cause_category_id' => 3, 'radar_code' => 'O2',
                    'inherent_p' => 3, 'inherent_i' => 4, 'residual_p' => 1, 'residual_i' => 2, 'impact_amount' => 670000000,
                    'cause' => 'Alokasi resource virtual machine tidak menggunakan batasan memory reservation dan CPU affinity.',
                    'existing_control' => 'Monitoring kapasitas global cluster vCenter.',
                    'mitigation' => 'Penetapan strict resource pooling, 100% memory reservation untuk database tier-1, dan pemisahan cluster analytics.'
                ],
                [
                    'title' => 'Pembersihan Log Audit Sebelum Batas Waktu Retensi Regulasi (Log Retention Shortage)',
                    'target' => 'Penyimpanan log transaksi dan sistem operasional minimum 12 bulan sesuai ketentuan OJK.',
                    'description' => 'Ruang log server penuh sehingga log sistem berumur 3 bulan otomatis terhapus, melanggar ketentuan pemeriksaan kepatuhan.',
                    'category_id' => 8, 'cause_category_id' => 3, 'radar_code' => 'C1',
                    'inherent_p' => 4, 'inherent_i' => 4, 'residual_p' => 1, 'residual_i' => 2, 'impact_amount' => 820000000,
                    'cause' => 'Arsitektur log forwarding terpusat belum dilengkapi cold storage tiering otomatis.',
                    'existing_control' => 'Script shell pembersih file zip lama saat disk mencapai 90%.',
                    'mitigation' => 'Implementasi enterprise log management dengan lifecycle policy otomatis memindahkan log berumur >30 hari ke immutable object storage.'
                ],
                [
                    'title' => 'Eksploitasi Celah Keamanan Firmware pada Switch dan SAN Storage Fabric',
                    'target' => 'Audit dan pembaruan berkala firmware perangkat keras enterprise minimal satu kali per tahun.',
                    'description' => 'Kerentanan zero-day pada firmware fiber channel switch memungkinkan eksekusi kode remote oleh peretas.',
                    'category_id' => 7, 'cause_category_id' => 3, 'radar_code' => 'O3',
                    'inherent_p' => 3, 'inherent_i' => 5, 'residual_p' => 1, 'residual_i' => 2, 'impact_amount' => 1650000000,
                    'cause' => 'Fokus patching hanya pada lapisan OS server tanpa mengalokasikan downtime untuk upgrade firmware controller.',
                    'existing_control' => 'Pemeriksaan status hardware oleh tim maintenance vendor tahunan.',
                    'mitigation' => 'Jadwal pemeliharaan firmware hardware berkala dengan arsitektur dual-controller redundant untuk zero-downtime rolling reboot.'
                ],
                [
                    'title' => 'Penggunaan Script Otomasi Ad-hoc dengan Kredensial Hardcoded pada Server Produksi',
                    'target' => 'Zero hardcoded credentials pada seluruh automation scripts dan konfigurasi server.',
                    'description' => 'Script bash untuk maintenance database menyimpan username dan password plain text yang dapat dibaca semua user lokal.',
                    'category_id' => 10, 'cause_category_id' => 1, 'radar_code' => 'O3',
                    'inherent_p' => 4, 'inherent_i' => 4, 'residual_p' => 1, 'residual_i' => 2, 'impact_amount' => 910000000,
                    'cause' => 'Kemudahan operasional teknisi dalam menjadwalkan tugas otomatis tanpa integrasi secret vault.',
                    'existing_control' => 'File permission rw------- pada file script bash.',
                    'mitigation' => 'Migrasi seluruh script pemeliharaan ke Ansible/AWX dengan integrasi HashiCorp Vault dan secret scanning pipeline.'
                ],
            ],

            // 16. RI - RESEARCH & INNOVATION
            'RI - RESEARCH & INNOVATION' => [
                [
                    'title' => 'Kebocoran Data Nasabah dan Kode Sumber Internal Akibat Penggunaan Layanan Public Generative AI (Shadow AI)',
                    'target' => 'Zero incident transmisi data sensitif bank ke platform AI eksternal tanpa izin.',
                    'description' => 'Karyawan dan developer memasukkan data finansial nasabah serta potongan source code proprietary ke ChatGPT publik untuk debugging.',
                    'category_id' => 10, 'cause_category_id' => 1, 'radar_code' => 'O3',
                    'inherent_p' => 5, 'inherent_i' => 5, 'residual_p' => 2, 'residual_i' => 2, 'impact_amount' => 2800000000,
                    'cause' => 'Tingginya kebutuhan produktivitas personil tanpa ketersediaan enterprise generative AI sandbox yang aman di internal.',
                    'existing_control' => 'Himbauan tertulis via email mengenai larangan membagikan data perusahaan ke media eksternal.',
                    'mitigation' => 'Penerapan Data Loss Prevention (DLP) web filter pemblokir domain AI publik, penyediaan In-house On-premise LLM sandbox, dan training AI governance.'
                ],
                [
                    'title' => 'Halusinasi Model AI (AI Hallucination) pada Chatbot Layanan Konsultasi Produk Finansial',
                    'target' => 'Tingkat akurasi jawaban chatbot finansial berbasis LLM mencapai minimal 99.8% bebas klaim palsu.',
                    'description' => 'Chatbot AI menjanjikan suku bunga deposito fiktif sebesar 15% kepada nasabah prioritas, memicu komplain sengketa hukum.',
                    'category_id' => 9, 'cause_category_id' => 3, 'radar_code' => 'O2',
                    'inherent_p' => 4, 'inherent_i' => 4, 'residual_p' => 1, 'residual_i' => 2, 'impact_amount' => 950000000,
                    'cause' => 'Penerapan open-ended generation tanpa guardrails pembatas konteks domain pengetahuan resmi bank.',
                    'existing_control' => 'Pemberitahuan disclaimer bahwa jawaban bot merupakan estimasi.',
                    'mitigation' => 'Penerapan arsitektur Retrieval-Augmented Generation (RAG) ketat berbasis dokumen produk bank resmi dan semantic guardrails (NeMo).'
                ],
                [
                    'title' => 'Serangan Manipulasi Input (Prompt Injection & Jailbreak) untuk Membocorkan System Prompt dan API Key',
                    'target' => 'Ketahanan 100% endpoint AI publik terhadap teknik jailbreak dan prompt extraction.',
                    'description' => 'Attacker memasukkan crafted prompt khusus yang memaksa model AI mengabaikan instruksi keamanan dan menampilkan secret token.',
                    'category_id' => 10, 'cause_category_id' => 3, 'radar_code' => 'O3',
                    'inherent_p' => 4, 'inherent_i' => 4, 'residual_p' => 1, 'residual_i' => 2, 'impact_amount' => 1200000000,
                    'cause' => 'Prompt template menggabungkan input pengguna mentah secara langsung dengan instruksi sistem tanpa filtering.',
                    'existing_control' => 'Penyaringan kata kunci sederhana berbasis blacklist manual.',
                    'mitigation' => 'Implementasi input sanitization layer khusus LLM, pemisahan role system-user, isolasi kredensial dari konteks model, dan AI red teaming berkala.'
                ],
                [
                    'title' => 'Bias Algoritma (Algorithmic Bias) pada Model Machine Learning Penilaian Kelayakan Kredit',
                    'target' => 'Keadilan (fairness metric) model scoring bebas diskriminasi gender, usia, dan wilayah domisili.',
                    'description' => 'Model deep learning secara sistematis menolak pengajuan pinjaman dari wilayah geografis tertentu akibat bias data historis.',
                    'category_id' => 8, 'cause_category_id' => 3, 'radar_code' => 'C1',
                    'inherent_p' => 4, 'inherent_i' => 5, 'residual_p' => 1, 'residual_i' => 2, 'impact_amount' => 1750000000,
                    'cause' => 'Data latih historis mencerminkan disparitas ekonomi masa lalu tanpa teknik mitigasi bias dan re-sampling.',
                    'existing_control' => 'Evaluasi metrik akurasi global (AUC-ROC) saat validasi model.',
                    'mitigation' => 'Penerapan Fairlearn/AIF360 toolkit, regular model fairness auditing, dan exclusion fitur proksi diskriminatif dalam feature engineering.'
                ],
                [
                    'title' => 'Penurunan Akurasi Model AI Deteksi Fraud Akibat Perubahan Pola Kejahatan Siber (Model Drift)',
                    'target' => 'Deteksi instan model degradation dan retraining siklus mingguan otomatis.',
                    'description' => 'Akurasi deteksi penipuan rekening bank anjlok dari 94% menjadi 68% karena sindikat penipuan beralih menggunakan modus baru.',
                    'category_id' => 1, 'cause_category_id' => 3, 'radar_code' => 'O2',
                    'inherent_p' => 4, 'inherent_i' => 4, 'residual_p' => 1, 'residual_i' => 2, 'impact_amount' => 1300000000,
                    'cause' => 'Model machine learning tidak dipantau secara real-time untuk population stability index (PSI) dan concept drift.',
                    'existing_control' => 'Retraining model manual setiap 6 bulan sekali.',
                    'mitigation' => 'Penerapan MLOps continuous monitoring (Evidently AI/WhyLabs), auto-alert saat drift terdeteksi, dan automated retraining pipeline.'
                ],
                [
                    'title' => 'Pembengkakan Biaya Komputasi Cloud GPU Tanpa Kontrol Alokasi Anggaran (Cost Explosion)',
                    'target' => 'Efisiensi biaya operasional R&D AI sesuai plafon anggaran dengan real-time budget ceiling enforcement.',
                    'description' => 'Cluster GPU cloud dibiarkan aktif tanpa auto-scaling saat akhir pekan, menimbulkan tagihan tak terduga miliaran rupiah.',
                    'category_id' => 2, 'cause_category_id' => 1, 'radar_code' => 'F1',
                    'inherent_p' => 4, 'inherent_i' => 4, 'residual_p' => 1, 'residual_i' => 2, 'impact_amount' => 850000000,
                    'cause' => 'Ketiadaan auto-shutdown policy dan limit spending terpusat pada project machine learning cloud.',
                    'existing_control' => 'Review tagihan invoice cloud bulanan oleh tim finance.',
                    'mitigation' => 'Penerapan FinOps practices, auto-shutdown GPU idle >30 menit, kuota alokasi per tim, dan pemanfaatan spot instance untuk workload non-kritis.'
                ],
                [
                    'title' => 'Kontaminasi Hak Cipta Perangkat Lunak Akibat Penggunaan Model Terlatih dari Open-Source Berlisensi GPL/Copyleft',
                    'target' => 'Kepatuhan 100% lisensi intelektual perangkat lunak (IP compliance) pada seluruh artefak R&D.',
                    'description' => 'Aplikasi internal perbankan terancam kewajiban publikasi source code karena menggunakan library berlisensi viral GPL v3.',
                    'category_id' => 8, 'cause_category_id' => 1, 'radar_code' => 'L1',
                    'inherent_p' => 3, 'inherent_i' => 4, 'residual_p' => 1, 'residual_i' => 2, 'impact_amount' => 1100000000,
                    'cause' => 'Peneliti mengunduh repositori riset GitHub tanpa meninjau lisensi komersial dan klausul paten.',
                    'existing_control' => 'Pemeriksaan mandiri oleh engineer sebelum merge code.',
                    'mitigation' => 'Integrasi automated software composition analysis (Snyk/FOSSA) pada pipeline code review dan legal IP licensing clearance.'
                ],
                [
                    'title' => 'Keracunan Data Latih (Data Poisoning) pada Dataset Pelatihan Model Pendeteksi Transaksi Mencurigakan',
                    'target' => 'Integritas dan validitas 100% dataset pelatihan machine learning dari manipulasi pihak luar.',
                    'description' => 'Sumber data feedback eksternal disusupi transaksi manipulatif yang sengaja dirancang untuk membutakan deteksi pencucian uang.',
                    'category_id' => 10, 'cause_category_id' => 2, 'radar_code' => 'O3',
                    'inherent_p' => 3, 'inherent_i' => 5, 'residual_p' => 1, 'residual_i' => 2, 'impact_amount' => 1950000000,
                    'cause' => 'Data training ditarik secara otomatis dari input user feedback tanpa proses kurasi dan anomali sanitasi.',
                    'existing_control' => 'Pembersihan data dasar (null checking dan deduplikasi data mentah).',
                    'mitigation' => 'Penerapan cryptographic data provenance, robust outlier detection pada training dataset, dan human-in-the-loop validation.'
                ],
                [
                    'title' => 'Ketidakmampuan Menjelaskan Keputusan Kredit Berbasis AI (Lack of Explainability) kepada Regulator OJK',
                    'target' => 'Pemenuhan 100% transparansi alasan penolakan kredit sesuai prinsip Explainable AI (XAI).',
                    'description' => 'Bank dijatuhi teguran regulasi karena tidak mampu membeberkan pertimbangan logis saat menolak debitur pinjaman digital.',
                    'category_id' => 8, 'cause_category_id' => 3, 'radar_code' => 'C1',
                    'inherent_p' => 4, 'inherent_i' => 4, 'residual_p' => 1, 'residual_i' => 2, 'impact_amount' => 890000000,
                    'cause' => 'Penggunaan model black-box (deep neural network) tanpa modul interpretasi lokal seperti SHAP atau LIME.',
                    'existing_control' => 'Pemberian alasan umum template standar penolakan pinjaman.',
                    'mitigation' => 'Implementasi SHAP/TreeSHAP explainability engine untuk menghasilkan feature contribution scorecard pada setiap keputusan kredit.'
                ],
                [
                    'title' => 'Penerapan Terburu-buru Prototipe Proof-of-Concept (PoC) Riset Langsung ke Lingkungan Produksi',
                    'target' => 'Pemberlakuan tahapan Quality Gate dan Security Hardening sebelum inovasi di-deploy live.',
                    'description' => 'Engine pencarian dokumen riset di-deploy ke nasabah korporasi tanpa arsitektur high-availability, memicu crash massal.',
                    'category_id' => 7, 'cause_category_id' => 1, 'radar_code' => 'O1',
                    'inherent_p' => 4, 'inherent_i' => 4, 'residual_p' => 1, 'residual_i' => 2, 'impact_amount' => 670000000,
                    'cause' => 'Tekanan manajemen untuk meluncurkan produk inovasi lebih cepat dari pesaing tanpa melalui fase pilot production.',
                    'existing_control' => 'Uji coba fungsi sederhana oleh tim inovasi.',
                    'mitigation' => 'Pemisahan tegas staging gateway inovasi, mandatory enterprise architecture review board (ARB), dan load test ketat.'
                ],
                [
                    'title' => 'Kebocoran Informasi Pribadi (PII Extraction) Akibat Model Memorization pada Large Language Model',
                    'target' => 'Zero retention data identitas pribadi nasabah pada bobot parameter model AI.',
                    'description' => 'Model LLM internal yang di-fine-tune menghafal NIK dan nomor rekening nasabah yang tidak sengaja terbawa di dataset teks perbankan.',
                    'category_id' => 10, 'cause_category_id' => 3, 'radar_code' => 'O3',
                    'inherent_p' => 4, 'inherent_i' => 5, 'residual_p' => 1, 'residual_i' => 2, 'impact_amount' => 2400000000,
                    'cause' => 'Ketiadaan tahap de-identifikasi dan pseudonimisasi otomatis pada data teks sebelum masuk proses pelatihan LLM.',
                    'existing_control' => 'Pembersihan manual menggunakan script regex parsial.',
                    'mitigation' => 'Implementasi automated PII masking pipeline (Microsoft Presidio) dan differential privacy training algoritma.'
                ],
                [
                    'title' => 'Latensi Tinggi Komputasi Model Biometrik AI Wajah Saat Puncak Transaksi Nasabah',
                    'target' => 'Waktu inferensi biometrik wajah (Face Liveness Detection) di bawah 1 detik pada konkurensi 1.000 TPS.',
                    'description' => 'Nasabah gagal melakukan verifikasi onboarding saat jam promo karena model inferensi biometrik mengalami GPU queue bottleneck.',
                    'category_id' => 7, 'cause_category_id' => 3, 'radar_code' => 'O2',
                    'inherent_p' => 3, 'inherent_i' => 4, 'residual_p' => 1, 'residual_i' => 2, 'impact_amount' => 780000000,
                    'cause' => 'Model deep learning tidak dioptimalkan menggunakan teknik model quantization (INT8) dan pruning.',
                    'existing_control' => 'Penambahan kapasitas instance server virtual konvensional.',
                    'mitigation' => 'Konversi model ke format ONNX/TensorRT, model quantization, dan auto-scaling inference cluster berbasis GPU Kubernetes.'
                ],
                [
                    'title' => 'Ketidaksiapan Menghadapi Ancaman Komputasi Kuantum Terhadap Kriptografi Perbankan (Quantum Threat)',
                    'target' => 'Penyusunan roadmap migrasi algoritma kriptografi ke standar Post-Quantum Cryptography (PQC).',
                    'description' => 'Data terenkripsi masa kini berisiko diarsipkan peretas untuk didekripsi di masa depan (Harvest Now, Decrypt Later).',
                    'category_id' => 10, 'cause_category_id' => 3, 'radar_code' => 'O3',
                    'inherent_p' => 3, 'inherent_i' => 5, 'residual_p' => 1, 'residual_i' => 2, 'impact_amount' => 3100000000,
                    'cause' => 'Infrastruktur perbankan masih bergantung sepenuhnya pada RSA-2048 dan ECC tanpa crypto-agility architecture.',
                    'existing_control' => 'Penggunaan enkripsi standar industri TLS 1.3 dan AES-256.',
                    'mitigation' => 'Pelaksanaan post-quantum crypto readiness assessment, isolasi data jangka panjang, dan piloting standar NIST PQC (ML-KEM/Kyber).'
                ],
                [
                    'title' => 'Rendahnya Kualitas Data Sintetis Menyebabkan Kegagalan Pendeteksian Bug Kritis pada Sandbox',
                    'target' => 'Pembangkitan data uji sintetis dengan korelasi statistik 95% identik terhadap data riil perbankan.',
                    'description' => 'Sistem payment gateway lolos pengujian menggunakan data sintetis namun gagal saat live akibat variasi format rekening di dunia nyata.',
                    'category_id' => 9, 'cause_category_id' => 3, 'radar_code' => 'O4',
                    'inherent_p' => 3, 'inherent_i' => 3, 'residual_p' => 1, 'residual_i' => 1, 'impact_amount' => 420000000,
                    'cause' => 'Pembangkit data sintetis hanya menggunakan aturan acak statis tanpa memodelkan edge cases dan inter-table dependency.',
                    'existing_control' => 'Pembuatan dummy data manual menggunakan faker library dasar.',
                    'mitigation' => 'Penerapan generative synthetic data tools (SDV/CTGAN) yang memvalidasi integritas relasional data finansial komplek.'
                ],
                [
                    'title' => 'Pelanggaran Etika Penerapan Kecerdasan Buatan Terhadap Surat Edaran Kominfo No 9/2023',
                    'target' => 'Kepatuhan 100% terhadap prinsip inklusivitas, transparansi, dan akuntabilitas etika AI nasional.',
                    'description' => 'Fitur penilaian calon karyawan internal berbasis AI dikomplain karena mendiskriminasi kandidat berdasarkan aksen bicara.',
                    'category_id' => 8, 'cause_category_id' => 1, 'radar_code' => 'C1',
                    'inherent_p' => 3, 'inherent_i' => 4, 'residual_p' => 1, 'residual_i' => 1, 'impact_amount' => 540000000,
                    'cause' => 'Riset implementasi HR-tech AI tidak melibatkan tinjauan komite etik dan kepatuhan ketenagakerjaan.',
                    'existing_control' => 'Persetujuan internal di tingkat manajer divisi inovasi.',
                    'mitigation' => 'Pembentukan Komite Etika AI Perusahaan (AI Ethics Board) dan mandatory ethical assessment checklist pada setiap proyek riset.'
                ],
                [
                    'title' => 'Ketergantungan Tunggal pada Penyedia API Closed-Source LLM Luar Negeri (Vendor Lock-in & Embargo)',
                    'target' => 'Penyediaan opsi fallback multi-provider dan self-hosted open-weights model berkinerja tinggi.',
                    'description' => 'Fitur analisis sentimen pasar saham lumpuh total saat penyedia API AI luar negeri mengubah kebijakan geografis layanan.',
                    'category_id' => 1, 'cause_category_id' => 2, 'radar_code' => 'O5',
                    'inherent_p' => 4, 'inherent_i' => 4, 'residual_p' => 1, 'residual_i' => 2, 'impact_amount' => 890000000,
                    'cause' => 'Arsitektur aplikasi terikat erat pada SDK proprietary salah satu vendor model tanpa abstraksi gateway.',
                    'existing_control' => 'Monitoring status service status page vendor AI.',
                    'mitigation' => 'Penerapan AI Model Gateway dengan protokol standar (OpenAI-compatible) yang mendukung seamless failover ke lokal open-weights LLM.'
                ],
                [
                    'title' => 'Inkonsistensi Versi Model Machine Learning Akibat Ketiadaan Model Registry Terpusat',
                    'target' => 'Pelacakan 100% artefak, hyperparameter, dan bobot model machine learning pada central registry.',
                    'description' => 'Dua server fraud detection menjalankan bobot model yang berbeda, menghasilkan keputusan verifikasi transaksi yang tidak konsisten.',
                    'category_id' => 7, 'cause_category_id' => 1, 'radar_code' => 'O2',
                    'inherent_p' => 3, 'inherent_i' => 4, 'residual_p' => 1, 'residual_i' => 1, 'impact_amount' => 490000000,
                    'cause' => 'Penyebaran file model (.pkl/.onnx) dilakukan secara copy-paste manual antar server oleh engineer.',
                    'existing_control' => 'Pencatatan versi model pada commit log Git.',
                    'mitigation' => 'Implementasi MLflow Model Registry terpusat dengan automated CI/CD deployment pipeline dan model tagging ketat.'
                ],
                [
                    'title' => 'Kebocoran Konteks Dokumen Antar-Departemen pada Vector Database (Multi-Tenant RAG Isolation Failure)',
                    'target' => 'Isolasi 100% akses semantic search antar-tenant divisi berdasarkan role-based access control.',
                    'description' => 'Staf junior dapat mengakses ringkasan dokumen gaji direksi melalui fitur tanya jawab chatbot knowledge base internal.',
                    'category_id' => 10, 'cause_category_id' => 3, 'radar_code' => 'O3',
                    'inherent_p' => 4, 'inherent_i' => 5, 'residual_p' => 1, 'residual_i' => 2, 'impact_amount' => 1800000000,
                    'cause' => 'Vector store mengindeks seluruh dokumen tanpa menyertakan metadata access control list (ACL) saat similarity search.',
                    'existing_control' => 'Pemisahan folder shared drive secara konvensional.',
                    'mitigation' => 'Penerapan metadata filtering berbasis Active Directory token pada query vector database dan granular tenant collection isolation.'
                ],
                [
                    'title' => 'Kegagalan Kuantifikasi Return on Investment (ROI) Proyek Eksperimen Teknologi Baru',
                    'target' => 'Evaluasi kelayakan bisnis bertahap (Stage-Gate) sebelum inisiasi riset teknologi bernilai investasi besar.',
                    'description' => 'Investasi puluhan miliar rupiah pada platform Metaverse dan Private Blockchain dihentikan karena tidak memiliki adopsi bisnis riil.',
                    'category_id' => 2, 'cause_category_id' => 1, 'radar_code' => 'F1',
                    'inherent_p' => 4, 'inherent_i' => 4, 'residual_p' => 2, 'residual_i' => 2, 'impact_amount' => 1600000000,
                    'cause' => 'Inisiasi proyek riset berbasis hype teknologi tanpa studi kelayakan pasar dan customer journey mapping yang jelas.',
                    'existing_control' => 'Presentasi pitch deck ide inovasi kepada komite transformasi.',
                    'mitigation' => 'Penerapan Framework Stage-Gate Innovation dengan batas maksimal anggaran PoC Rp 200 juta dan kriteria kill-switch yang terukur.'
                ],
                [
                    'title' => 'Kehilangan Personil Kunci Ahli AI & Machine Learning Akibat Perebutan Talenta Global (Brain Drain)',
                    'target' => 'Retensi 90% talenta spesialis riset AI melalui skema remunerasi berbasis inovasi dan jalur karir spesialis.',
                    'description' => 'Tiga arsitek AI inti mengundurkan diri serentak ke perusahaan teknologi multinasional, melumpuhkan proyek analitik prediktif.',
                    'category_id' => 1, 'cause_category_id' => 1, 'radar_code' => 'O4',
                    'inherent_p' => 4, 'inherent_i' => 4, 'residual_p' => 2, 'residual_i' => 2, 'impact_amount' => 750000000,
                    'cause' => 'Struktur kompensasi perbankan kaku yang tidak kompetitif dibanding industri big-tech global.',
                    'existing_control' => 'Perjanjian ikatan dinas standar pada kontrak kerja.',
                    'mitigation' => 'Penyusunan competitive technical career path, skema insentif paten/riset, dan kemitraan riset dengan laboratorium universitas terkemuka.'
                ],
            ],

            // 17. BS - CEO OFFICE
            'BS - CEO OFFICE' => [
                [
                    'title' => 'Gejolak Geopolitik dan Depresiasi Kurs Rupiah Terhadap Beban Kewajiban Valuta Asing Bank',
                    'target' => 'Mitigasi 100% eksposur risiko valuta asing dan volatilitas kurs melalui instrumen hedging terukur.',
                    'description' => 'Pelemahan tajam nilai tukar Rupiah memicu lonjakan biaya lisensi teknologi multi-tahun dan biaya utang valas.',
                    'category_id' => 2, 'cause_category_id' => 4, 'radar_code' => 'F1',
                    'inherent_p' => 4, 'inherent_i' => 5, 'residual_p' => 2, 'residual_i' => 3, 'impact_amount' => 4500000000,
                    'cause' => 'Tingginya ketidakpastian suku bunga global dan krisis rantai pasok energi internasional.',
                    'existing_control' => 'Monitoring posisi devisa neto (PDN) harian oleh treasury.',
                    'mitigation' => 'Eksekusi lindung nilai (hedging) FX forward terstruktur dan renegosiasi kontrak lisensi berbasis mata uang Rupiah.'
                ],
                [
                    'title' => 'Krisis Komunikasi Publik dan Rusaknya Reputasi Perusahaan Akibat Pemberitaan Negatif Viral',
                    'target' => 'Penanganan krisis media dalam waktu kurang dari 60 menit dengan mitigasi sentimen publik terkoordinasi.',
                    'description' => 'Gangguan operasional perbankan selama 2 jam memicu gelombang boikot dan trending topic negatif di platform X/Twitter.',
                    'category_id' => 9, 'cause_category_id' => 1, 'radar_code' => 'O4',
                    'inherent_p' => 4, 'inherent_i' => 5, 'residual_p' => 1, 'residual_i' => 2, 'impact_amount' => 3800000000,
                    'cause' => 'Keterlambatan rilis klarifikasi resmi dan pernyataan kontradiktif antar juru bicara perusahaan.',
                    'existing_control' => 'Pernyataan pers tertulis standar yang disusun oleh tim humas.',
                    'mitigation' => 'Protokol Crisis Communication Management 24/7, media listening monitoring real-time, dan centralized single-voice public response.'
                ],
                [
                    'title' => 'Lonjakan Kredit Bermasalah (Non-Performing Loan / NPL) Akibat Resesi Sektoral Ekonomi',
                    'target' => 'Rasio NPL gross terjaga aman di bawah batas toleransi internal 2.5%.',
                    'description' => 'Penurunan daya beli masyarakat dan lesunya sektor manufaktur menyebabkan gagal bayar debitur segmen komersial.',
                    'category_id' => 2, 'cause_category_id' => 4, 'radar_code' => 'F1',
                    'inherent_p' => 4, 'inherent_i' => 5, 'residual_p' => 2, 'residual_i' => 3, 'impact_amount' => 6200000000,
                    'cause' => 'Kelesuan makroekonomi domestik dan ketergantungan portofolio pinjaman pada sektor industri rentan.',
                    'existing_control' => 'Komite pemantauan kredit bulanan dan pencadangan CKPN.',
                    'mitigation' => 'Pengetatan kriteria underwriting kredit sektor berisiko tinggi, restrukturisasi dini debitur potensial, dan ekspansi ke segmen ultra-mikro.'
                ],
                [
                    'title' => 'Sanksi Administratif Berat dan Pembatasan Operasional oleh Regulator Akibat Pelanggaran Sistemik',
                    'target' => 'Zero regulatory sanction berupa pembatasan kegiatan usaha dari OJK dan Bank Indonesia.',
                    'description' => 'OJK membekukan sementara izin penerbitan produk digital baru karena keterlambatan berulang dalam pemenuhan tata kelola IT.',
                    'category_id' => 8, 'cause_category_id' => 1, 'radar_code' => 'C1',
                    'inherent_p' => 3, 'inherent_i' => 5, 'residual_p' => 1, 'residual_i' => 2, 'impact_amount' => 5500000000,
                    'cause' => 'Kurangnya prioritisasi tindak lanjut rekomendasi audit kepatuhan oleh jajaran manajemen eksekutif.',
                    'existing_control' => 'Laporan kepatuhan kuartalan kepada dewan direksi.',
                    'mitigation' => 'Pembentukan Supervisory Action Plan Tracker langsung di bawah CEO dan pelibatan independen auditor untuk validasi perbaikan.'
                ],
                [
                    'title' => 'Ketergantungan Ekstrem pada Tokoh Kunci (Key-Person Risk) pada Posisi C-Level Tanpa Suksesi',
                    'target' => 'Kesiapan suksesi kepemimpinan minimal 2 kandidat ready-now untuk setiap posisi direksi.',
                    'description' => 'Pengunduran diri mendadak Chief Technology Officer menyebabkan tertundanya seluruh program transformasi digital selama 6 bulan.',
                    'category_id' => 1, 'cause_category_id' => 1, 'radar_code' => 'O4',
                    'inherent_p' => 4, 'inherent_i' => 4, 'residual_p' => 1, 'residual_i' => 2, 'impact_amount' => 1800000000,
                    'cause' => 'Ketiadaan rencana suksesi formal dan transfer pengetahuan kepemimpinan strategis.',
                    'existing_control' => 'Program rotasi karyawan di tingkat manajerial madya.',
                    'mitigation' => 'Penyusunan Executive Succession Planning yang disetujui Dewan Komisaris dan skema golden handcuff retensi eksekutif.'
                ],
                [
                    'title' => 'Kegagalan Integrasi Akuisisi / Merger Anak Perusahaan Teknologi (M&A Failure)',
                    'target' => 'Pencapaian sinergi operasional dan target laba bersih anak perusahaan hasil akuisisi dalam 18 bulan.',
                    'description' => 'Fintech yang diakuisisi mengalami penurunan valuasi drastis akibat benturan budaya kerja dan ketidakcocokan arsitektur IT.',
                    'category_id' => 2, 'cause_category_id' => 1, 'radar_code' => 'F1',
                    'inherent_p' => 3, 'inherent_i' => 5, 'residual_p' => 1, 'residual_i' => 3, 'impact_amount' => 7500000000,
                    'cause' => 'Due diligence teknis dan budaya yang kurang mendalam saat proses pra-akuisisi.',
                    'existing_control' => 'Financial due diligence oleh kantor akuntan publik independen.',
                    'mitigation' => 'Pembentukan Post-Merger Integration (PMI) Management Office dengan roadmap unifikasi sistem dan retensi talenta kunci.'
                ],
                [
                    'title' => 'Penurunan Skor ESG (Environmental, Social, Governance) Menghambat Pendanaan Internasional',
                    'target' => 'Pemeliharaan skor ESG perbankan di peringkat teratas (Leader tier) pada indeks keberlanjutan global.',
                    'description' => 'Portofolio pembiayaan ke sektor batu bara yang tinggi memicu eksklusi bank dari pendanaan green bond dan investor global.',
                    'category_id' => 8, 'cause_category_id' => 4, 'radar_code' => 'C1',
                    'inherent_p' => 3, 'inherent_i' => 4, 'residual_p' => 1, 'residual_i' => 2, 'impact_amount' => 2900000000,
                    'cause' => 'Lambatnya penyelarasan portofolio pembiayaan dengan Taksonomi Keuangan Berkelanjutan Indonesia.',
                    'existing_control' => 'Penerbitan Laporan Keberlanjutan tahunan formal.',
                    'mitigation' => 'Penyusunan Transition Plan menuju Net Zero Emission, penghentian pembiayaan PLTU batu bara baru, dan insentif pembiayaan hijau.'
                ],
                [
                    'title' => 'Gugatan Hukum dan Sengketa dari Pemegang Saham Minoritas (Shareholder Activism Litigation)',
                    'target' => 'Keterbukaan informasi dan tata kelola perusahaan yang bersih (Good Corporate Governance) bebas gugatan.',
                    'description' => 'Pemegang saham minoritas menggugat direksi terkait transparansi penentuan dividen dan transaksi afiliasi.',
                    'category_id' => 8, 'cause_category_id' => 1, 'radar_code' => 'L1',
                    'inherent_p' => 2, 'inherent_i' => 4, 'residual_p' => 1, 'residual_i' => 1, 'impact_amount' => 2200000000,
                    'cause' => 'Dokumentasi risalah RUPS dan keterbukaan informasi publik yang kurang komprehensif.',
                    'existing_control' => 'Review legal compliance sebelum pelaksanaan RUPS tahunan.',
                    'mitigation' => 'Penerapan prinsip transparansi GCG ketat, konsultasi proaktif dengan OJK Pasar Modal, dan penguatan fungsi Corporate Secretary.'
                ],
                [
                    'title' => 'Tuntutan Pidana dan Perdata Pribadi pada Pimpinan Perusahaan atas Kebocoran Data Sesuai UU PDP',
                    'target' => 'Perlindungan tata kelola hukum bagi direksi melalui kepatuhan penuh pemrosesan data pribadi korporasi.',
                    'description' => 'Insiden kebocoran data nasabah memicu ancaman tanggung jawab pribadi bagi direksi karena dinilai lalai dalam pengawasan IT.',
                    'category_id' => 8, 'cause_category_id' => 1, 'radar_code' => 'L1',
                    'inherent_p' => 4, 'inherent_i' => 5, 'residual_p' => 1, 'residual_i' => 2, 'impact_amount' => 4200000000,
                    'cause' => 'Ketiadaan penunjukan Pejabat Pelindung Data Pribadi (DPO) resmi dan minimnya alokasi anggaran keamanan siber.',
                    'existing_control' => 'Kebijakan privasi data standar di website bank.',
                    'mitigation' => 'Penunjukan Data Protection Officer independen yang melapor langsung ke Dewan Direksi dan kepemilikan polis asuransi D&O (Directors & Officers).'
                ],
                [
                    'title' => 'Ketidakselarasan Target Pertumbuhan Bisnis Agresif dengan Kapasitas Manajemen Risiko (Risk Appetite Misalignment)',
                    'target' => 'Penetapan target pertumbuhan bisnis yang terkalibrasi penuh dengan Risk Appetite Statement (RAS) bank.',
                    'description' => 'Target penyaluran pinjaman digital dinaikkan 300% tanpa diimbangi peningkatan kapasitas modal risiko dan fraud control.',
                    'category_id' => 1, 'cause_category_id' => 1, 'radar_code' => 'O4',
                    'inherent_p' => 4, 'inherent_i' => 4, 'residual_p' => 1, 'residual_i' => 2, 'impact_amount' => 3100000000,
                    'cause' => 'Dorongan mengejar pangsa pasar mengorbankan prinsip kehati-hatian (prudential banking).',
                    'existing_control' => 'Penyusunan Rencana Bisnis Bank (RBB) tahunan ke regulator.',
                    'mitigation' => 'Pemberian hak veto (Risk Challenge) bagi Chief Risk Officer atas peluncuran kampanye bisnis yang melampaui risk appetite.'
                ],
                [
                    'title' => 'Disrupsi Pangsa Pasar Akibat Penetrasi Agresif Raksasa Teknologi Finansial (Big Tech / Super-App)',
                    'target' => 'Pertumbuhan nasabah aktif retail tetap positif minimal 15% YoY melalui kolaborasi ekosistem terbuka.',
                    'description' => 'Super-app eksternal meluncurkan layanan pembayaran dan pinjaman instan yang menggerus volume transaksi fee-based bank.',
                    'category_id' => 1, 'cause_category_id' => 4, 'radar_code' => 'O2',
                    'inherent_p' => 4, 'inherent_i' => 4, 'residual_p' => 2, 'residual_i' => 2, 'impact_amount' => 2500000000,
                    'cause' => 'Kalahnya user experience dan ekosistem merchant aplikasi perbankan dibanding super-app komersial.',
                    'existing_control' => 'Program promo cashback dan loyalitas poin nasabah.',
                    'mitigation' => 'Transformasi menuju Open Banking / Banking-as-a-Service (BaaS) menjadi enabler infrastruktur perbankan bagi platform super-app.'
                ],
                [
                    'title' => 'Ketidaksesuaian Arus Kas dan Likuiditas Akibat Fluktuasi Pembiayaan Perdagangan Internasional (Trade Finance)',
                    'target' => 'Ketersediaan likuiditas valas harian mencukupi 100% kewajiban jatuh tempo Letter of Credit (LC).',
                    'description' => 'Gagal bayar importir besar atas transaksi LC senilai jutaan dolar memicu tekanan likuiditas valuta asing bank.',
                    'category_id' => 2, 'cause_category_id' => 4, 'radar_code' => 'F1',
                    'inherent_p' => 3, 'inherent_i' => 5, 'residual_p' => 1, 'residual_i' => 2, 'impact_amount' => 5100000000,
                    'cause' => 'Pemeriksaan solvabilitas counterparty bank luar negeri tidak diperbarui pasca krisis rantai pasok global.',
                    'existing_control' => 'Pemberlakuan limit kredit perdagangan per debitur korporasi.',
                    'mitigation' => 'Pengetatan seleksi konfirmasi LC dengan bank koresponden tier-1 dan kewajiban asuransi kredit ekspor-impor.'
                ],
                [
                    'title' => 'Perubahan Kebijakan Mendadak Terkait Kedaulatan Data dan Tarif Pajak Transaksi Digital',
                    'target' => 'Kepatuhan seketika terhadap regulasi fiskal dan moneter baru tanpa disrupsi pendapatan operasional.',
                    'description' => 'Pemerintah menerbitkan aturan kenaikan pajak transaksi finansial digital yang menggerus margin laba bank secara drastis.',
                    'category_id' => 8, 'cause_category_id' => 4, 'radar_code' => 'C1',
                    'inherent_p' => 3, 'inherent_i' => 4, 'residual_p' => 1, 'residual_i' => 2, 'impact_amount' => 1900000000,
                    'cause' => 'Ketidakpastian arah kebijakan fiskal pemerintah dalam menggenjot penerimaan negara dari sektor digital.',
                    'existing_control' => 'Monitoring rancangan undang-undang oleh divisi legal.',
                    'mitigation' => 'Partisipasi aktif dalam asosiasi perbankan (Perbanas/ASPI) untuk advokasi industri dan diversifikasi sumber pendapatan komisi.'
                ],
                [
                    'title' => 'Kelambanan Pengambilan Keputusan Krisis Akibat Tim Tanggap Darurat Eksekutif Tidak Efektif',
                    'target' => 'Aktivasi Tim Manajemen Krisis Eksekutif (CMT) dalam waktu maksimal 30 menit sejak insiden level 1.',
                    'description' => 'Rapat penentuan evakuasi sistem ke DRC terkatung-katung selama 4 jam karena ketiadaan threshold kewenangan yang disepakati.',
                    'category_id' => 1, 'cause_category_id' => 1, 'radar_code' => 'O4',
                    'inherent_p' => 3, 'inherent_i' => 5, 'residual_p' => 1, 'residual_i' => 2, 'impact_amount' => 3400000000,
                    'cause' => 'Simulasi tabletop crisis management tidak pernah melibatkan jajaran dewan direksi secara langsung.',
                    'existing_control' => 'Buku pedoman Business Continuity Plan (BCP) perusahaan.',
                    'mitigation' => 'Pemberian mandat delegasi kewenangan deklarasi bencana yang jelas dan pelaksanaan crisis simulation tahunan bagi direksi.'
                ],
                [
                    'title' => 'Kehilangan Rekening Pengelolaan Kas Institusi BUMN / Pemerintah ke Bank Kompetitor',
                    'target' => 'Tingkat retensi dana institusional BUMN dan kementerian minimal 95% dengan kualitas layanan prima.',
                    'description' => 'Kementerian memindahkan dana simpanan triliunan rupiah ke bank pesaing akibat seringnya kegagalan sistem pembayaran payroll.',
                    'category_id' => 1, 'cause_category_id' => 1, 'radar_code' => 'O4',
                    'inherent_p' => 4, 'inherent_i' => 5, 'residual_p' => 1, 'residual_i' => 3, 'impact_amount' => 8900000000,
                    'cause' => 'Layanan perbankan transaksional (Cash Management) tidak responsif terhadap kustomisasi pelaporan institusi.',
                    'existing_control' => 'Kunjungan rutin Relationship Manager ke nasabah institusi.',
                    'mitigation' => 'Dedicated institutional onboarding squad, SLA penyelesaian keluhan korporasi prioritas, dan modernisasi fitur host-to-host.'
                ],
                [
                    'title' => 'Penyusutan Rasio Kecukupan Modal (Capital Adequacy Ratio / CAR) Mendekati Batas Regulasi',
                    'target' => 'Pemeliharaan rasio CAR minimum pada level 18% untuk menopang ketahanan ekspansi aset berisiko.',
                    'description' => 'Pembengkakan Aset Tertimbang Menurut Risiko (ATMR) akibat lonjakan pinjaman berisiko menekan permodalan tier-1.',
                    'category_id' => 2, 'cause_category_id' => 1, 'radar_code' => 'F1',
                    'inherent_p' => 3, 'inherent_i' => 5, 'residual_p' => 1, 'residual_i' => 2, 'impact_amount' => 6700000000,
                    'cause' => 'Alokasi modal tidak efisien dan kurangnya permodalan cadangan untuk mengantisipasi gejolak makroekonomi.',
                    'existing_control' => 'Monitoring rasio CAR bulanan oleh komite ALCO.',
                    'mitigation' => 'Penerbitan obligasi subordinasi berkelanjutan untuk memperkuat modal tier-2 dan optimalisasi alokasi ATMR berbasis rating internal.'
                ],
                [
                    'title' => 'Tuduhan Skandal Etik dan Kecurangan (Fraud) yang Melibatkan Manajemen Senior',
                    'target' => 'Pencegahan menyeluruh tindak kecurangan internal dengan toleransi nol (Zero Tolerance Fraud Policy).',
                    'description' => 'Beredar laporan investigasi independen mengenai konflik kepentingan pejabat tinggi dalam pengadaan gedung kantor pusat.',
                    'category_id' => 1, 'cause_category_id' => 1, 'radar_code' => 'O3',
                    'inherent_p' => 2, 'inherent_i' => 5, 'residual_p' => 1, 'residual_i' => 1, 'impact_amount' => 4800000000,
                    'cause' => 'Kelemahan sistem verifikasi benturan kepentingan pada transaksi pengadaan strategis bernilai besar.',
                    'existing_control' => 'Penandatanganan pakta integritas tahunan oleh seluruh pejabat.',
                    'mitigation' => 'Pelaporan kekayaan pejabat wajib berkala, audit forensik pengadaan oleh pihak ketiga independen, dan perlindungan saksi whistleblowing.'
                ],
                [
                    'title' => 'Kegagalan Realisasi Manfaat Finansial dari Program Transformasi Digital Berskala Besar',
                    'target' => 'Pencapaian penurunan Rasio Beban Operasional terhadap Pendapatan Operasional (BOPO) ke bawah 75%.',
                    'description' => 'Investasi ratusan miliar untuk peremajaan sistem IT tidak membuahkan efisiensi biaya operasional yang dijanjikan dalam business case.',
                    'category_id' => 2, 'cause_category_id' => 1, 'radar_code' => 'F1',
                    'inherent_p' => 4, 'inherent_i' => 4, 'residual_p' => 2, 'residual_i' => 2, 'impact_amount' => 3900000000,
                    'cause' => 'Adopsi digital di cabang rendah dan proses bisnis manual konvensional masih dipertahankan secara paralel.',
                    'existing_control' => 'Laporan berkala progres proyek teknologi ke dewan direksi.',
                    'mitigation' => 'Penutupan bertahap proses manual non-digital, restrukturisasi biaya kantor cabang fisik, dan KPI realisasi efisiensi berbasis unit bisnis.'
                ],
                [
                    'title' => 'Risiko Penularan Finansial (Contagion Risk) Akibat Kegagalan Bank Koresponden Global',
                    'target' => 'Diversifikasi penempatan saldo rekening nostro pada minimal 5 bank global berkredibilitas tinggi.',
                    'description' => 'Kolapsnya bank mitra koresponden luar negeri menyebabkan tersangkutnya saldo nostro devisa bank selama proses kepailitan.',
                    'category_id' => 2, 'cause_category_id' => 4, 'radar_code' => 'F1',
                    'inherent_p' => 2, 'inherent_i' => 5, 'residual_p' => 1, 'residual_i' => 2, 'impact_amount' => 5800000000,
                    'cause' => 'Penempatan saldo devisa terkonsentrasi pada institusi luar negeri yang mengalami krisis likuiditas.',
                    'existing_control' => 'Review credit rating tahunan bank koresponden.',
                    'mitigation' => 'Pemberlakuan limit penempatan saldo nostro harian yang ketat dan sweep otomatis saldo berlebih ke rekening bank sentral.'
                ],
                [
                    'title' => 'Kanibalisasi Nasabah dan Konflik Strategis Antara Unit Bisnis Konvensional dan Syariah',
                    'target' => 'Pertumbuhan sinergis kedua entitas perbankan tanpa terjadi migrasi sepihak nasabah yang merugikan konsolidasi.',
                    'description' => 'Unit usaha syariah menawarkan margin pembiayaan yang memicu nasabah komersial konvensional membatalkan fasilitas pinjaman eksisting.',
                    'category_id' => 1, 'cause_category_id' => 1, 'radar_code' => 'O4',
                    'inherent_p' => 3, 'inherent_i' => 3, 'residual_p' => 1, 'residual_i' => 1, 'impact_amount' => 1200000000,
                    'cause' => 'Ketiadaan panduan segmentasi pasar terpadu di tingkat grup holding perbankan.',
                    'existing_control' => 'Rapat koordinasi komersial ad-hoc antar kepala divisi.',
                    'mitigation' => 'Penyusunan Group Market Segmentation Policy yang tegas dan insentif cross-selling yang saling menguntungkan kedua entitas.'
                ],
            ],

            // 18. CS - CYBER SECURITY
            'CS - CYBER SECURITY' => [
                [
                    'title' => 'Infeksi Ransomware Skala Korporasi Melalui Eksploitasi Kerentanan SSL-VPN Gateway',
                    'target' => 'Ketahanan penuh terhadap ransomware dengan isolasi zero-trust dan immutable recovery.',
                    'description' => 'Kelompok ransomware mengeksploitasi zero-day VPN gateway, mengenkripsi server virtual dan backup lokal bank.',
                    'category_id' => 10, 'cause_category_id' => 2, 'radar_code' => 'O3',
                    'inherent_p' => 4, 'inherent_i' => 5, 'residual_p' => 1, 'residual_i' => 2, 'impact_amount' => 5800000000,
                    'cause' => 'Ketiadaan multi-factor authentication (MFA) berbasis hardware token pada akses remote pekerja dan lambatnya patching VPN.',
                    'existing_control' => 'Antivirus endpoint konvensional dan firewall perimeter.',
                    'mitigation' => 'Penerapan arsitektur Zero Trust Network Access (ZTNA), mandatory FIDO2 hardware MFA, EDR terkelola 24/7, dan immutable air-gapped backup.'
                ],
                [
                    'title' => 'Serangan Masif Credential Stuffing Menggunakan Botnet Terdistribusi pada Endpoint Mobile Banking',
                    'target' => 'Pencegahan otomatis 99.9% upaya login fraud berbasis botnet tanpa menghambat nasabah sah.',
                    'description' => 'Jutaan kombinasi email dan password hasil kebocoran database e-commerce diuji coba ke API login bank.',
                    'category_id' => 10, 'cause_category_id' => 2, 'radar_code' => 'O3',
                    'inherent_p' => 5, 'inherent_i' => 4, 'residual_p' => 1, 'residual_i' => 2, 'impact_amount' => 2100000000,
                    'cause' => 'Endpoint API mobile banking tidak dilengkapi bot detection dan rate limiting berbasis behavior fingerprinting.',
                    'existing_control' => 'Penguncian akun setelah 3 kali salah memasukkan password.',
                    'mitigation' => 'Implementasi Web Application and API Protection (WAAP) dengan advanced bot management, device attestation, dan forced password reset.'
                ],
                [
                    'title' => 'Penyusupan Kode Berbahaya pada Repositori Dependensi Pihak Ketiga (Supply Chain Attack)',
                    'target' => 'Validasi dan pengawasan 100% pustaka open-source sebelum diserap ke dalam pipeline produksi.',
                    'description' => 'Package npm populer disusupi skrip pencuri token oleh peretas, yang kemudian ikut terkompilasi ke dalam web banking.',
                    'category_id' => 10, 'cause_category_id' => 2, 'radar_code' => 'O3',
                    'inherent_p' => 4, 'inherent_i' => 5, 'residual_p' => 1, 'residual_i' => 2, 'impact_amount' => 3400000000,
                    'cause' => 'Developer mengunduh package open-source langsung dari internet publik tanpa melalui secure artifact registry lokal.',
                    'existing_control' => 'Pemindaian kerentanan CVE menggunakan static code analysis dasar.',
                    'mitigation' => 'Penerapan Software Bill of Materials (SBOM), private artifact repository dengan automated malware sandboxing, dan dependency pinning.'
                ],
                [
                    'title' => 'Kompromi Domain Controller Active Directory Melalui Teknik Kerberoasting dan Pass-the-Hash',
                    'target' => 'Proteksi penuh kredensial Active Directory dengan monitoring anomali otentikasi real-time.',
                    'description' => 'Penyerang yang berhasil masuk ke workstation staf berhasil mengekstraksi tiket Kerberos dan meningkatkan hak akses menjadi Domain Admin.',
                    'category_id' => 10, 'cause_category_id' => 2, 'radar_code' => 'O3',
                    'inherent_p' => 3, 'inherent_i' => 5, 'residual_p' => 1, 'residual_i' => 2, 'impact_amount' => 3900000000,
                    'cause' => 'Penggunaan Service Principal Names (SPN) dengan password lemah dan ketiadaan pembatasan hak delegasi akun layanan.',
                    'existing_control' => 'Kebijakan penggantian password berkala setiap 90 hari.',
                    'mitigation' => 'Implementasi Microsoft Defender for Identity, Tiered Administrative Model (Tier 0/1/2 separation), dan rotasi password service account terotomatisasi (gMSA).'
                ],
                [
                    'title' => 'Serangan Spear-Phishing Tertarget Menjerat Pejabat Otorisator Transfer Dana Treasury',
                    'target' => 'Penurunan kegagalan simulasi phishing internal di bawah 2% dan deteksi zero payload execution.',
                    'description' => 'Email palsu berkedok undangan rapat regulator berisi tautan infostealer berhasil mencuri sesi browser pimpinan treasury.',
                    'category_id' => 10, 'cause_category_id' => 2, 'radar_code' => 'O3',
                    'inherent_p' => 4, 'inherent_i' => 5, 'residual_p' => 1, 'residual_i' => 2, 'impact_amount' => 4700000000,
                    'cause' => 'Teknik rekayasa sosial yang sangat meyakinkan dipadukan dengan ketiadaan pemeriksaan keamanan tautan real-time.',
                    'existing_control' => 'Secure Email Gateway (SEG) dengan filter spam standar.',
                    'mitigation' => 'Penerapan DMARC/DKIM/SPF enforcement, AI-powered advanced email phishing protection, isolasi browser web (RBI), dan simulasi phishing bulanan.'
                ],
                [
                    'title' => 'Serangan Distributed Denial of Service (DDoS) Volumetrik dan Layer 7 Menumbangkan Layanan Publik',
                    'target' => 'Mitigasi serangan DDoS multi-vektor secara instan tanpa mengganggu ketersediaan layanan publik (99.99%).',
                    'description' => 'Serangan gabungan DNS amplification dan HTTP POST flood melebihi 200 Gbps melumpuhkan jaringan koneksi ISP bank.',
                    'category_id' => 10, 'cause_category_id' => 2, 'radar_code' => 'O3',
                    'inherent_p' => 4, 'inherent_i' => 4, 'residual_p' => 1, 'residual_i' => 2, 'impact_amount' => 1850000000,
                    'cause' => 'Kapasitas scrubbing center on-premise tidak sanggup menampung bandwidth serangan berskala gigabit masif.',
                    'existing_control' => 'Perangkat anti-DDoS appliance lokal di gateway data center.',
                    'mitigation' => 'Langganan Cloud DDoS Protection Always-On dengan kapasitas scrubbing terdistribusi global dan auto-rate limiting layer 7.'
                ],
                [
                    'title' => 'Eksploitasi Celah Keamanan Zero-Day Remote Code Execution (RCE) pada Server Aplikasi Web',
                    'target' => 'Penyediaan mitigasi virtual patching dalam waktu maksimal 2 jam sejak publikasi CVE.',
                    'description' => 'Celah keamanan baru pada framework Java (mirip Log4j) dimanfaatkan peretas untuk menanamkan web shell pada server internet.',
                    'category_id' => 10, 'cause_category_id' => 2, 'radar_code' => 'O3',
                    'inherent_p' => 3, 'inherent_i' => 5, 'residual_p' => 1, 'residual_i' => 2, 'impact_amount' => 3200000000,
                    'cause' => 'Tingginya kompleksitas komponen aplikasi monolitik dan keterlambatan rilis patch resmi dari vendor open-source.',
                    'existing_control' => 'Web Application Firewall (WAF) berbasis signature statis.',
                    'mitigation' => 'Penerapan Next-Gen WAF berbasis behavioral detection, Runtime Application Self-Protection (RASP), dan isolasi egress network server web.'
                ],
                [
                    'title' => 'Eksfiltrasi Data Nasabah Melalui Kanal Terselubung (Covert DNS / ICMP Tunneling)',
                    'target' => 'Pendeteksian dan pemblokiran 100% lalu lintas tunneling data tidak sah pada perimeter jaringan.',
                    'description' => 'Malware mencuri jutaan baris data nasabah secara perlahan menggunakan paket query DNS yang disamarkan.',
                    'category_id' => 10, 'cause_category_id' => 2, 'radar_code' => 'O3',
                    'inherent_p' => 3, 'inherent_i' => 5, 'residual_p' => 1, 'residual_i' => 2, 'impact_amount' => 2900000000,
                    'cause' => 'Lalu lintas DNS internal ke resolver publik tidak dibatasi dan tidak dipantau perilakunya.',
                    'existing_control' => 'Firewall yang hanya mengizinkan port 53 terbuka.',
                    'mitigation' => 'Penerapan DNS Security Filter terpusat (DNS sinkholing), inspeksi entropi kueri DNS, dan pelarangan DNS resolver eksternal langsung.'
                ],
                [
                    'title' => 'Kompromi Prosedur Pengelolaan Kunci Enkripsi Master pada Hardware Security Module (HSM)',
                    'target' => 'Kepatuhan 100% prosedur dual-custody dan split-knowledge pada pengelolaan kunci kriptografi master.',
                    'description' => 'Kunci master zonal PIN (ZPK) disalin tanpa kehadiran saksi ganda resmi saat pemindahan ke perangkat HSM cadangan.',
                    'category_id' => 10, 'cause_category_id' => 1, 'radar_code' => 'O3',
                    'inherent_p' => 2, 'inherent_i' => 5, 'residual_p' => 1, 'residual_i' => 1, 'impact_amount' => 5200000000,
                    'cause' => 'Kelonggaran pengawasan fisik ruang HSM dan minimnya penegakan protokol upacara kunci (Key Ceremony).',
                    'existing_control' => 'Smartcard otentikasi ganda yang disimpan di dalam brankas terpisah.',
                    'mitigation' => 'Audit berkala kepatuhan PCI-HSM, rekaman CCTV ruang aman dengan dual-biometric access, dan audit trail tamper-proof logging.'
                ],
                [
                    'title' => 'Celah Keamanan Otorisasi Objek API (Broken Object Level Authorization / BOLA)',
                    'target' => 'Zero kerentanan BOLA/IDOR pada seluruh API publik yang melayani nasabah.',
                    'description' => 'Nasabah jahat dapat melihat mutasi rekening nasabah lain hanya dengan mengganti parameter account_id pada request JSON API.',
                    'category_id' => 10, 'cause_category_id' => 3, 'radar_code' => 'O3',
                    'inherent_p' => 4, 'inherent_i' => 5, 'residual_p' => 1, 'residual_i' => 2, 'impact_amount' => 2700000000,
                    'cause' => 'Pengembang hanya memeriksa keabsahan token JWT tanpa memvalidasi kepemilikan objek data terhadap user ID yang login.',
                    'existing_control' => 'Verifikasi keaslian signature token OAuth 2.0 di API Gateway.',
                    'mitigation' => 'Enforcement fine-grained authorization middleware pada level controller aplikasi dan automated dynamic API security testing (DAST).'
                ],
                [
                    'title' => 'Rekayasa Balik (Reverse Engineering) dan Ekstraksi Kunci API Rahasia dari Aplikasi Mobile',
                    'target' => 'Penerapan enkripsi biner dan proteksi anti-tamper bersertifikasi pada aplikasi mobile perbankan.',
                    'description' => 'Attacker membongkar file APK Android dan mengekstrak private key enkripsi payload serta endpoint internal bank.',
                    'category_id' => 10, 'cause_category_id' => 3, 'radar_code' => 'O3',
                    'inherent_p' => 4, 'inherent_i' => 4, 'residual_p' => 1, 'residual_i' => 1, 'impact_amount' => 1400000000,
                    'cause' => 'Aplikasi dirilis tanpa menggunakan teknologi code obfuscation dan runtime application self-protection (RASP).',
                    'existing_control' => 'Penggunaan ProGuard dasar bawaan tools Android Studio.',
                    'mitigation' => 'Penerapan enterprise Mobile App Shielding (RASP, string encryption, anti-hooking/Frida detection, dan root/jailbreak prevention).'
                ],
                [
                    'title' => 'Penyadapan Transaksi Melalui Serangan Man-in-the-Middle (MITM) Akibat Kegagalan SSL Pinning',
                    'target' => 'Enforcement SSL Pinning dan deteksi instalasi sertifikat root berbahaya pada perangkat nasabah.',
                    'description' => 'Pelaku menggunakan proxy lokal untuk mendekripsi lalu lintas perbankan pada perangkat yang sertifikat custom-nya disusupi.',
                    'category_id' => 10, 'cause_category_id' => 3, 'radar_code' => 'O3',
                    'inherent_p' => 3, 'inherent_i' => 4, 'residual_p' => 1, 'residual_i' => 1, 'impact_amount' => 950000000,
                    'cause' => 'Mekanisme certificate pinning dinonaktifkan sementara untuk debugging development namun terbawa ke versi rilis.',
                    'existing_control' => 'Enkripsi kanal komunikasi menggunakan standar HTTPS TLS 1.3.',
                    'mitigation' => 'Penerapan hard-coded public key pinning (HPKP) dengan backup pin cadangan serta automated build verification pipeline.'
                ],
                [
                    'title' => 'Akses Ilegal dan Manipulasi Data Buku Besar oleh Administrator Basis Data (Malicious DBA)',
                    'target' => 'Pencegahan modifikasi data transaksi secara langsung pada database tanpa jejak audit tamper-proof.',
                    'description' => 'Oknum DBA memodifikasi saldo rekening penampung langsung menggunakan query UPDATE di server database produksi.',
                    'category_id' => 1, 'cause_category_id' => 1, 'radar_code' => 'O3',
                    'inherent_p' => 3, 'inherent_i' => 5, 'residual_p' => 1, 'residual_i' => 1, 'impact_amount' => 3600000000,
                    'cause' => 'Tingginya hak akses DBA tanpa keberadaan solusi Database Activity Monitoring (DAM) yang independen.',
                    'existing_control' => 'Log database bawaan yang tersimpan lokal di server.',
                    'mitigation' => 'Implementasi Database Activity Monitoring (DAM) real-time dengan alert instan saat ada modifikasi DML pada tabel finansial, serta masking data sensitif.'
                ],
                [
                    'title' => 'Kesalahan Konfigurasi Penyimpanan Cloud (Cloud Storage Misconfiguration) Membocorkan Foto Dokumen KYC',
                    'target' => 'Zero public bucket exposure pada seluruh penyimpanan cloud storage korporasi.',
                    'description' => 'Folder cloud bucket tempat menyimpan foto KTP dan swafoto nasabah terbuka untuk akses publik tanpa password.',
                    'category_id' => 10, 'cause_category_id' => 1, 'radar_code' => 'O3',
                    'inherent_p' => 4, 'inherent_i' => 5, 'residual_p' => 1, 'residual_i' => 2, 'impact_amount' => 3100000000,
                    'cause' => 'DevOps engineer mengubah permission bucket menjadi public-read saat melakukan integrasi aplikasi pihak ketiga.',
                    'existing_control' => 'Tinjauan manual berkala terhadap daftar bucket penyimpanan cloud.',
                    'mitigation' => 'Penerapan Cloud Security Posture Management (CSPM), automated policy "Block Public Access" pada level root, dan real-time remediator.'
                ],
                [
                    'title' => 'Pembajakan Catatan DNS (DNS Hijacking) Mengarahkan Nasabah ke Situs Phishing Kloningan',
                    'target' => 'Proteksi 100% integritas catatan DNS resmi perbankan dari manipulasi registrar eksternal.',
                    'description' => 'Akun registrar domain dibobol penyerang, yang mengubah IP A-record domain web banking ke server penipuan.',
                    'category_id' => 10, 'cause_category_id' => 2, 'radar_code' => 'O3',
                    'inherent_p' => 3, 'inherent_i' => 5, 'residual_p' => 1, 'residual_i' => 2, 'impact_amount' => 4300000000,
                    'cause' => 'Akun domain registrar hanya menggunakan proteksi password sederhana tanpa multi-factor authentication berbasis hardware.',
                    'existing_control' => 'Pemberitahuan perubahan DNS via email admin.',
                    'mitigation' => 'Aktivasi DNSSEC, Registry Lock pada penyedia domain, mandatory multi-person approval untuk perubahan DNS, dan automated DNS monitoring.'
                ],
                [
                    'title' => 'Celah Keamanan Server-Side Request Forgery (SSRF) Mengekspos Metadata Layanan Cloud Internal',
                    'target' => 'Pemblokiran menyeluruh upaya akses ke layanan metadata internal (IMDS) dari seluruh aplikasi web.',
                    'description' => 'Fitur konversi dokumen invoice dieksploitasi untuk mengirim request internal ke alamat 169.254.169.254 guna mencuri IAM role token.',
                    'category_id' => 10, 'cause_category_id' => 3, 'radar_code' => 'O3',
                    'inherent_p' => 3, 'inherent_i' => 5, 'residual_p' => 1, 'residual_i' => 2, 'impact_amount' => 2600000000,
                    'cause' => 'Server cloud mengizinkan akses instance metadata versi 1 (IMDSv1) yang tidak membutuhkan token otentikasi header.',
                    'existing_control' => 'Filtering URL sederhana untuk kata kunci localhost.',
                    'mitigation' => 'Migrasi wajib ke IMDSv2 (session-oriented), penerapan strict outbound firewall rules pada worker server, dan input validation URL whitelist.'
                ],
                [
                    'title' => 'Keterlambatan Ingesti Log SIEM (Log Ingestion Lag) Menghambat Deteksi Serangan Siber Aktif',
                    'target' => 'Waktu latensi pengiriman log ke sistem SIEM di bawah 30 detik untuk deteksi ancaman real-time.',
                    'description' => 'Antrian log menumpuk selama 5 jam pada message broker, menyebabkan alert pergerakan lateral penyerang baru muncul setelah sistem terenkripsi.',
                    'category_id' => 7, 'cause_category_id' => 3, 'radar_code' => 'O1',
                    'inherent_p' => 4, 'inherent_i' => 4, 'residual_p' => 1, 'residual_i' => 2, 'impact_amount' => 1700000000,
                    'cause' => 'Kapasitas cluster SIEM tidak mampu menangani lonjakan throughput log transaksi harian yang mencapai 50.000 EPS.',
                    'existing_control' => 'Pengecekan utilisasi disk server SIEM secara harian.',
                    'mitigation' => 'Peningkatan arsitektur streaming log berbasis Kafka cluster horizontal, log filtering di level agent, dan tuning aturan korelasi SIEM.'
                ],
                [
                    'title' => 'Penyusupan Skrip Pencuri Kartu (E-skimming / Magecart) pada Halaman Pembayaran Checkout Web',
                    'target' => 'Proteksi 100% halaman checkout dari injeksi skrip pihak ketiga tanpa integritas hash terverifikasi.',
                    'description' => 'Peretas menyusupi library analytics eksternal untuk merekam nomor kartu kredit dan CVV yang diketik nasabah.',
                    'category_id' => 10, 'cause_category_id' => 2, 'radar_code' => 'O3',
                    'inherent_p' => 3, 'inherent_i' => 5, 'residual_p' => 1, 'residual_i' => 2, 'impact_amount' => 3500000000,
                    'cause' => 'Halaman pembayaran memuat script JavaScript eksternal tanpa verifikasi Subresource Integrity (SRI).',
                    'existing_control' => 'Pemindaian kerentanan web mingguan dari sisi luar.',
                    'mitigation' => 'Penerapan ketat Content Security Policy (CSP), Subresource Integrity (SRI), dan pemantauan client-side script execution (Page Integrity Protection).'
                ],
                [
                    'title' => 'Topologi Jaringan Internal Datar (Flat Network) Mempermudah Gerakan Lateral Penyerang',
                    'target' => 'Penerapan segmentasi jaringan mikro (Microsegmentation) memisahkan zona DMZ, aplikasi, dan database.',
                    'description' => 'Kompromi pada satu PC teller kantor cabang memungkinkan penyerang melakukan pemindaian port langsung ke database core banking.',
                    'category_id' => 7, 'cause_category_id' => 3, 'radar_code' => 'O2',
                    'inherent_p' => 4, 'inherent_i' => 5, 'residual_p' => 1, 'residual_i' => 2, 'impact_amount' => 3800000000,
                    'cause' => 'Arsitektur jaringan warisan tanpa pembatasan firewall internal antar subnet VLAN.',
                    'existing_control' => 'Pembagian VLAN standar tanpa stateful firewall filtering antar VLAN.',
                    'mitigation' => 'Penerapan Software-Defined Microsegmentation, firewall internal dengan default-deny rule, dan pembatasan ketat koneksi inter-VLAN.'
                ],
                [
                    'title' => 'Kredensial dan Token VPN Pihak Ketiga Tetap Aktif Pasca Penyelesaian Proyek Pengembang Luar',
                    'target' => 'Pencabutan otomatis 100% hak akses pihak ketiga segera setelah tanggal kontrak berakhir.',
                    'description' => 'Konsultan vendor yang telah selesai kontrak setahun lalu masih memiliki akun VPN aktif dan mengunduh data dokumentasi internal.',
                    'category_id' => 10, 'cause_category_id' => 1, 'radar_code' => 'O3',
                    'inherent_p' => 4, 'inherent_i' => 4, 'residual_p' => 1, 'residual_i' => 1, 'impact_amount' => 1250000000,
                    'cause' => 'Ketiadaan proses integrasi antara database kontrak pengadaan vendor dengan sistem identity provisioning.',
                    'existing_control' => 'Permintaan penonaktifan akun secara manual oleh Project Manager via tiket.',
                    'mitigation' => 'Penerapan automated expiration date pada akun eksternal di Active Directory, regular third-party access review, dan mandatory jump-host dengan rekaman sesi.'
                ],
            ],

            // 19. BS - BUSINESS SUPPORT
            'BS - BUSINESS SUPPORT' => [
                [
                    'title' => 'Ketergantungan Pasokan Tunggal (Single Sourcing) pada Vendor Pengadaan Perangkat Keras ATM dan Server',
                    'target' => 'Diversifikasi penyedia perangkat keras kritis minimal memiliki 2 vendor alternatif yang qualified.',
                    'description' => 'Satu-satunya vendor penyedia mesin ATM mengalami kebangkrutan, menghentikan pengiriman suku cadang mesin di seluruh cabang.',
                    'category_id' => 1, 'cause_category_id' => 2, 'radar_code' => 'O5',
                    'inherent_p' => 4, 'inherent_i' => 4, 'residual_p' => 1, 'residual_i' => 2, 'impact_amount' => 1650000000,
                    'cause' => 'Kebijakan pengadaan masa lalu yang memilih standarisasi satu merk demi efisiensi jangka pendek.',
                    'existing_control' => 'Kontrak payung pengadaan tahunan.',
                    'mitigation' => 'Penerapan Dual-Vendor Sourcing Strategy, standarisasi spesifikasi multi-merk modular, dan kualifikasi vendor cadangan aktif.'
                ],
                [
                    'title' => 'Pembayaran Tagihan Ganda (Duplicate Payment) Akibat Kegagalan Proses 3-Way Matching Faktur',
                    'target' => 'Pencocokan otomatis 100% antara Purchase Order, Goods Receipt, dan Invoice sebelum proses transfer dana.',
                    'description' => 'Bagian keuangan membayarkan dua invoice berbeda untuk pengadaan genset yang sama karena penomoran faktur vendor membingungkan.',
                    'category_id' => 2, 'cause_category_id' => 1, 'radar_code' => 'F1',
                    'inherent_p' => 4, 'inherent_i' => 4, 'residual_p' => 1, 'residual_i' => 1, 'impact_amount' => 780000000,
                    'cause' => 'Verifikasi dokumen masih menggunakan verifikasi fisik manual tanpa integrasi ERP otomatis.',
                    'existing_control' => 'Pengecekan paraf manual oleh supervisor akuntansi utang.',
                    'mitigation' => 'Otomasi 3-Way Matching pada modul ERP Procurement, validasi nomor faktur pajak elektronik, dan deteksi duplikasi pembayaran berbasis AI.'
                ],
                [
                    'title' => 'Kelangkaan Pasokan Global Cip Semikonduktor Menghambat Penerbitan Kartu Debit dan Kredit Fisik',
                    'target' => 'Ketersediaan stok kartu fisik perbankan mencukupi kebutuhan operasional minimal 4 bulan ke depan.',
                    'description' => 'Keterlambatan impor bahan baku chip kartu selama 5 bulan mengakibatkan antrian panjang pencetakan kartu ATM nasabah baru.',
                    'category_id' => 1, 'cause_category_id' => 4, 'radar_code' => 'O2',
                    'inherent_p' => 4, 'inherent_i' => 4, 'residual_p' => 2, 'residual_i' => 2, 'impact_amount' => 1100000000,
                    'cause' => 'Disrupsi rantai pasok manufaktur global dan penetapan safety stock lokal yang terlalu tipis.',
                    'existing_control' => 'Pemesanan kartu rutin setiap kuartal berdasarkan proyeksi historis.',
                    'mitigation' => 'Peningkatan buffer stock kartu chip menjadi 6 bulan kebutuhan, pembinaan produsen kartu domestik, dan ekspansi opsi aktivasi kartu virtual instan.'
                ],
                [
                    'title' => 'Kegagalan Berfungsinya Sistem Pemadam Kebakaran Otomatis (FM-200) di Ruang Server dan Arsip Dokumen',
                    'target' => 'Inspeksi dan sertifikasi berkala 100% sistem proteksi kebakaran gedung dan ruang data.',
                    'description' => 'Tabung gas penekan api mengalami kebocoran tekanan tanpa disadari, membahayakan keselamatan aset saat terjadi korsleting listrik.',
                    'category_id' => 1, 'cause_category_id' => 3, 'radar_code' => 'O1',
                    'inherent_p' => 3, 'inherent_i' => 5, 'residual_p' => 1, 'residual_i' => 2, 'impact_amount' => 2800000000,
                    'cause' => 'Kelalaian vendor pemeliharaan fasilitas dalam melakukan uji hydro test berkala pada silinder pemadam.',
                    'existing_control' => 'Pemeriksaan fisik visual indikator jarum manometer setiap bulan.',
                    'mitigation' => 'Penggantian sensor tekanan digital terintegrasi Building Management System (BMS), maintenance triwulanan bersertifikasi, dan audit keselamatan K3 independen.'
                ],
                [
                    'title' => 'Perbedaan Selisih Fisik Barang dan Catatan Inventaris Aset Tetap Teknologi Perusahaan',
                    'target' => 'Akurasi rekonsiliasi aset fisik dengan catatan pembukuan akuntansi mencapai minimal 99.5%.',
                    'description' => 'Ratusan unit laptop dan monitor bekas kantor raib saat relokasi gedung karena tidak adanya barcode tracking terpadu.',
                    'category_id' => 2, 'cause_category_id' => 1, 'radar_code' => 'F1',
                    'inherent_p' => 4, 'inherent_i' => 3, 'residual_p' => 1, 'residual_i' => 1, 'impact_amount' => 480000000,
                    'cause' => 'Pelaporan pergerakan perpindahan aset antar ruangan mengandalkan nota kertas yang sering tercecer.',
                    'existing_control' => 'Stock opname aset fisik satu kali dalam setahun.',
                    'mitigation' => 'Implementasi sistem pelabelan RFID/QR code terintegrasi Fixed Asset Management ERP dan gate-pass scanner pada setiap pintu keluar gedung.'
                ],
                [
                    'title' => 'Klausul Kontrak Vendor Merugikan Perusahaan Akibat Mekanisme Perpanjangan Otomatis (Auto-Renewal)',
                    'target' => 'Pemberitahuan evaluasi kontrak H-90 sebelum jatuh tempo masa perpanjangan otomatis.',
                    'description' => 'Kontrak sewa lisensi software yang sudah tidak terpakai terperpanjang secara otomatis selama 2 tahun dengan eskalasi biaya 15%.',
                    'category_id' => 8, 'cause_category_id' => 1, 'radar_code' => 'L1',
                    'inherent_p' => 4, 'inherent_i' => 3, 'residual_p' => 1, 'residual_i' => 1, 'impact_amount' => 650000000,
                    'cause' => 'Arsip kontrak tersebar di berbagai divisi tanpa basis data manajemen kontrak terpusat.',
                    'existing_control' => 'Pencatatan masa aktif kontrak dalam lembar kerja spreadsheet terpisah.',
                    'mitigation' => 'Penerapan Contract Lifecycle Management (CLM) terpusat dengan automated reminder jatuh tempo dan renegosiasi harga wajib sebelum perpanjangan.'
                ],
                [
                    'title' => 'Pelanggaran Keamanan Fisik pada Fasilitas Ruang Khazanah dan Pemrosesan Uang Tunai (Cash Center)',
                    'target' => 'Nol insiden akses tidak sah ke area khazanah dengan pemantauan kamera CCTV 360 derajat tanpa titik buta.',
                    'description' => 'Pihak luar dapat memasuki area penyortiran uang tunai cabang tanpa pemeriksaan kartu identitas dan pencatatan buku tamu.',
                    'category_id' => 1, 'cause_category_id' => 1, 'radar_code' => 'O3',
                    'inherent_p' => 3, 'inherent_i' => 5, 'residual_p' => 1, 'residual_i' => 1, 'impact_amount' => 2200000000,
                    'cause' => 'Pintu akses interlocking ruang khazanah mengalami kerusakan magnetik dan sengaja diganjal oleh petugas.',
                    'existing_control' => 'Penjagaan pos satpam di pintu masuk depan gedung cabang.',
                    'mitigation' => 'Perbaikan sistem interlocking door berbasis biometrik wajah, pemasangan CCTV AI deteksi anomali di area steril, dan rotasi harian petugas pengawas.'
                ],
                [
                    'title' => 'Keterlambatan Pengisian Kas Mesin ATM Akibat Kerusakan Armada Kendaraan Pengangkut Uang (CIT)',
                    'target' => 'Kepatuhan jadwal replenishment kas ATM tepat waktu mencapai 99% dengan downtime kas nol.',
                    'description' => 'Mobil baja pengangkut uang mogok di perjalanan, mengakibatkan belasan mesin ATM di pusat perbelanjaan kehabisan uang tunai saat libur panjang.',
                    'category_id' => 1, 'cause_category_id' => 2, 'radar_code' => 'O5',
                    'inherent_p' => 4, 'inherent_i' => 4, 'residual_p' => 1, 'residual_i' => 2, 'impact_amount' => 820000000,
                    'cause' => 'Vendor Cash-in-Transit (CIT) tidak melakukan pemeliharaan armada kendaraan pengawalan secara rutin.',
                    'existing_control' => 'Laporan status ketersediaan kas mesin ATM pada dashboard monitoring.',
                    'mitigation' => 'Enforcement penalti kontraktual SLA ketersediaan armada cadangan CIT dan kewajiban inspeksi kelaikan armada vendor setiap bulan.'
                ],
                [
                    'title' => 'Kegagalan Starter Genset Utama Akibat Akumulasi Endapan Bahan Bakar Solar yang Terkontaminasi',
                    'target' => 'Genset cadangan mampu mengambil alih beban listrik gedung dalam waktu maksimal 15 detik setelah pasokan PLN terputus.',
                    'description' => 'Saat pemadaman listrik kota, generator diesel darurat kantor pusat gagal menyala karena saringan bahan bakar tersumbat lumut.',
                    'category_id' => 1, 'cause_category_id' => 3, 'radar_code' => 'O1',
                    'inherent_p' => 3, 'inherent_i' => 5, 'residual_p' => 1, 'residual_i' => 2, 'impact_amount' => 1950000000,
                    'cause' => 'Bahan bakar solar disimpan di tangki pendam lebih dari 12 bulan tanpa proses pemurnian (fuel polishing).',
                    'existing_control' => 'Uji pemanasan mesin genset tanpa beban selama 15 menit setiap pekan.',
                    'mitigation' => 'Implementasi uji beban penuh (full-load testing) bulanan menggunakan dummy load bank, fuel filtration treatment, dan dual starter battery redundancy.'
                ],
                [
                    'title' => 'Kerusakan Dokumen Fisik Perjanjian Kredit dan Sertifikat Agunan Akibat Kelembapan Gudang Arsip',
                    'target' => 'Penyimpanan 100% dokumen berharga di ruang berstandar arsip nasional dengan kontrol suhu dan kelembapan konstan.',
                    'description' => 'Sertifikat tanah agunan kredit nasabah rusak parah dimakan rayap dan jamur di gudang arsip bawah tanah (basement).',
                    'category_id' => 1, 'cause_category_id' => 3, 'radar_code' => 'O1',
                    'inherent_p' => 3, 'inherent_i' => 5, 'residual_p' => 1, 'residual_i' => 2, 'impact_amount' => 3400000000,
                    'cause' => 'Gudang penyimpanan dokumen tidak dilengkapi alat dehumidifier otomatis dan perlindungan hama berkala.',
                    'existing_control' => 'Penyimpanan dokumen di dalam lemari arsip besi tertutup.',
                    'mitigation' => 'Digitalisasi berkas agunan menjadi e-arsip tersertifikasi, relokasi fisik ke fasilitas arsip profesional berstandar ISO 11799, dan pest control terjadwal.'
                ],
                [
                    'title' => 'Pelanggaran Ketentuan Keselamatan dan Kesehatan Kerja (K3) Mengakibatkan Kecelakaan Kerja Fatal',
                    'target' => 'Pencapaian Zero Accident di seluruh gedung operasional dan kepatuhan 100% regulasi K3 Kemenaker.',
                    'description' => 'Teknisi fasilitas terjatuh dari tangga saat memperbaiki lampu lobi gedung karena tidak dilengkapi alat pelindung diri (APD) harness.',
                    'category_id' => 8, 'cause_category_id' => 1, 'radar_code' => 'L1',
                    'inherent_p' => 3, 'inherent_i' => 5, 'residual_p' => 1, 'residual_i' => 1, 'impact_amount' => 1500000000,
                    'cause' => 'Pengawasan keselamatan kerja lemah dan ketiadaan izin kerja risiko tinggi (Work Permit).',
                    'existing_control' => 'Penyediaan helm pengaman di ruang maintenance gedung.',
                    'mitigation' => 'Pemberlakuan mandatory Permit-to-Work untuk pekerjaan di ketinggian/kelistrikan, sertifikasi personil K3 Umum, dan safety induction wajib harian.'
                ],
                [
                    'title' => 'Tertahannya Impor Perangkat SAN Storage dan Server di Bea Cukai Akibat Perubahan Regulasi Lartas',
                    'target' => 'Kelancaran izin kepabeanan peralatan IT strategis tanpa penahanan kontainer di pelabuhan.',
                    'description' => 'Perangkat keras ekspansi kapasitas database tertahan 3 bulan di pelabuhan Tanjung Priok karena regulasi kuota persetujuan impor baru.',
                    'category_id' => 1, 'cause_category_id' => 4, 'radar_code' => 'O2',
                    'inherent_p' => 3, 'inherent_i' => 4, 'residual_p' => 1, 'residual_i' => 2, 'impact_amount' => 980000000,
                    'cause' => 'Vendor forwarder lambat memperbarui sertifikasi Tingkat Komponen Dalam Negeri (TKDN) dan izin pos telekomunikasi.',
                    'existing_control' => 'Pendelegasian penuh urusan kepabeanan kepada vendor distributor pengadaan.',
                    'mitigation' => 'Pemilihan distributor bersertifikasi Authorized Economic Operator (AEO), pre-clearance dokumentasi legal sebelum pengapalan, dan penyiapan jalur impor alternatif.'
                ],
                [
                    'title' => 'Penggusuran Kantor Cabang Akibat Sengketa Kepemilikan Lahan dan Keterlambatan Perpanjangan Sewa',
                    'target' => 'Penyelesaian perpanjangan sewa gedung kantor minimal 12 bulan sebelum masa sewa berakhir.',
                    'description' => 'Kantor cabang ditutup paksa oleh pemilik tanah karena masa sewa habis dan hak waris gedung sedang dalam sengketa pengadilan.',
                    'category_id' => 1, 'cause_category_id' => 1, 'radar_code' => 'O1',
                    'inherent_p' => 3, 'inherent_i' => 4, 'residual_p' => 1, 'residual_i' => 1, 'impact_amount' => 1200000000,
                    'cause' => 'Kelalaian pelacakan masa berlaku sewa properti oleh tim general affairs daerah.',
                    'existing_control' => 'Pencatatan manual daftar gedung sewa di buku registrasi properti.',
                    'mitigation' => 'Pemeriksaan legal due diligence kepemilikan sertifikat sebelum kontrak sewa dan pemantauan masa sewa berbasis sistem real estate management otomatis.'
                ],
                [
                    'title' => 'Penyedia Jasa Utama Menyerahkan Pekerjaan Fasilitas Gedung ke Subkontraktor Tanpa Kualifikasi',
                    'target' => 'Pencegahan 100% praktik subkontrak liar tanpa persetujuan tertulis manajemen pengadaan perbankan.',
                    'description' => 'Vendor pemeliharaan AC mengalihkan pekerjaan perbaikan chiller utama kepada pihak ketiga yang merusak kompresor pendingin.',
                    'category_id' => 1, 'cause_category_id' => 2, 'radar_code' => 'O5',
                    'inherent_p' => 3, 'inherent_i' => 4, 'residual_p' => 1, 'residual_i' => 2, 'impact_amount' => 540000000,
                    'cause' => 'Ketiadaan klausul pembatasan subkontraktor dan audit lapangan yang ketat terhadap teknisi pelaksana.',
                    'existing_control' => 'Pemeriksaan kartu nama pekerja saat registrasi di pos keamanan.',
                    'mitigation' => 'Klausul anti-subcontracting tanpa izin tertulis, verifikasi identitas badge pekerja resmi, dan sanksi blacklist vendor yang melanggar.'
                ],
                [
                    'title' => 'Kebocoran Data Rahasia Akibat Pemusnahan Aset Komputer dan Media Penyimpanan (E-Waste) yang Tidak Aman',
                    'target' => 'Pemusnahan 100% hard disk dan media magnetik usang menggunakan mesin degausser dan penghancur fisik (shredder).',
                    'description' => 'Hard disk bekas server kantor dijual sebagai besi tua dan data transaksi di dalamnya berhasil dipulihkan oleh pembeli rongsokan.',
                    'category_id' => 10, 'cause_category_id' => 1, 'radar_code' => 'O3',
                    'inherent_p' => 4, 'inherent_i' => 5, 'residual_p' => 1, 'residual_i' => 1, 'impact_amount' => 2600000000,
                    'cause' => 'Pemberian instruksi pembuangan aset TI ke vendor rongsokan tanpa proses penghapusan data tersertifikasi (sanitization).',
                    'existing_control' => 'Format drive standar menggunakan perintah format sistem operasi sebelum pelepasan aset.',
                    'mitigation' => 'Penerapan standar NIST SP 800-88 untuk media sanitization, penghancuran fisik hard disk dengan sertifikat pemusnahan resmi (Certificate of Destruction).'
                ],
                [
                    'title' => 'Pemborosan Konsumsi Energi Listrik dan Kegagalan Pencapaian Target Reduksi Emisi Karbon Perusahaan',
                    'target' => 'Pengurangan konsumsi energi gedung kantor pusat sebesar 15% melalui inisiatif green building.',
                    'description' => 'Tagihan listrik gedung melonjak akibat sistem penerangan dan pendingin ruangan tetap menyala 24 jam di lantai yang tidak digunakan.',
                    'category_id' => 2, 'cause_category_id' => 1, 'radar_code' => 'F1',
                    'inherent_p' => 4, 'inherent_i' => 3, 'residual_p' => 1, 'residual_i' => 1, 'impact_amount' => 450000000,
                    'cause' => 'Ketiadaan teknologi otomatisasi manajemen gedung dan sensor gerak hemat energi.',
                    'existing_control' => 'Patroli satpam malam hari untuk mematikan saklar lampu manual.',
                    'mitigation' => 'Instalasi smart energy monitoring dengan IoT sensor, retrofit lampu LED otomatis, dan penjadwalan HVAC berbasis okupansi ruangan.'
                ],
                [
                    'title' => 'Penyalahgunaan Fasilitas Perjalanan Dinas dan Kartu Kredit Korporasi oleh Oknum Pegawai',
                    'target' => 'Kepatuhan 100% terhadap kebijakan perjalanan dinas dan penghapusan pengeluaran non-bisnis fiktif.',
                    'description' => 'Audit internal menemukan klaim ganda tiket pesawat dan kwitansi hotel fiktif bernilai ratusan juta rupiah.',
                    'category_id' => 1, 'cause_category_id' => 1, 'radar_code' => 'O3',
                    'inherent_p' => 4, 'inherent_i' => 3, 'residual_p' => 1, 'residual_i' => 1, 'impact_amount' => 360000000,
                    'cause' => 'Pemeriksaan kwitansi manual yang mudah dipalsukan tanpa integrasi sistem pemesanan terpusat.',
                    'existing_control' => 'Persetujuan reimbursement oleh atasan langsung pemohon.',
                    'mitigation' => 'Penggunaan Corporate Travel Management platform terintegrasi dengan penagihan langsung ke korporasi dan audit kepatuhan otomatis berbasis OCR.'
                ],
                [
                    'title' => 'Padamnya AC Presisi di Ruang Hub Jaringan Cabang Mengakibatkan Perangkat Router Overheating',
                    'target' => 'Suhu ruang server cabang stabil pada rentang 18-22 derajat Celcius dengan pemantauan sensor IoT real-time.',
                    'description' => 'Unit pendingin split di ruang server cabang mati mendadak saat akhir pekan, memicu thermal shutdown pada core switch cabang.',
                    'category_id' => 7, 'cause_category_id' => 3, 'radar_code' => 'O1',
                    'inherent_p' => 3, 'inherent_i' => 4, 'residual_p' => 1, 'residual_i' => 2, 'impact_amount' => 410000000,
                    'cause' => 'Ruang rack server cabang tidak memiliki unit AC cadangan (N+1) dan ketiadaan sensor alarm temperatur jarak jauh.',
                    'existing_control' => 'Pengecekan hembusan udara AC oleh staf kantor saat pagi hari.',
                    'mitigation' => 'Pemasangan unit dual-AC otomatis bergantian (lead-lag system) dan instalasi sensor suhu IoT terkoneksi alert NOC 24/7.'
                ],
                [
                    'title' => 'Pencetakan Formulir Nasabah dan Brosur Produk Tanpa Pembaruan Ketentuan Transparansi OJK Terkini',
                    'target' => 'Pemusnahan seketika dan penggantian seluruh materi cetak promosi yang tidak memenuhi kaidah regulasi.',
                    'description' => 'Cabang masih menyebarkan brosur deposito dengan klausul lama yang tidak mencantumkan logo peringatan penjaminan LPS.',
                    'category_id' => 8, 'cause_category_id' => 1, 'radar_code' => 'C1',
                    'inherent_p' => 3, 'inherent_i' => 4, 'residual_p' => 1, 'residual_i' => 1, 'impact_amount' => 520000000,
                    'cause' => 'Distribusi materi cetak sisa stok lama di gudang logistik tanpa mekanisme penarikan barang kedaluwarsa.',
                    'existing_control' => 'Pemeriksaan sampel materi cetak di kantor cabang secara berkala.',
                    'mitigation' => 'Sentralisasi pengadaan material promosi berbarcode versi dokumen dan kewajiban sertifikasi izin materi promosi dari divisi kepatuhan.'
                ],
                [
                    'title' => 'Hilangnya Surat Berisi PIN Kartu Kredit Nasabah oleh Jasa Kurir Eksternal',
                    'target' => 'Peniadaan pengiriman PIN fisik melalui migrasi 100% ke pembuatan e-PIN mandiri via mobile banking.',
                    'description' => 'Puluhan amplop PIN mailer nasabah tercecer saat pengantaran oleh vendor kurir, memicu komplain kebocoran data rahasia.',
                    'category_id' => 1, 'cause_category_id' => 2, 'radar_code' => 'O5',
                    'inherent_p' => 3, 'inherent_i' => 4, 'residual_p' => 1, 'residual_i' => 1, 'impact_amount' => 490000000,
                    'cause' => 'Penggunaan metode pengiriman konvensional pihak ketiga tanpa sistem tracking resi tersertifikasi per dokumen rahasia.',
                    'existing_control' => 'Pengiriman terpisah antara kartu fisik dan surat PIN selang 3 hari kerja.',
                    'mitigation' => 'Penghapusan pencetakan PIN fisik kertas (paperless) dan pengalihan ke pembuatan PIN secara aman di aplikasi mobile banking nasabah.'
                ],
            ],

            // 20. HC - HC, FINANCE, & LEGAL
            'HC - HC, FINANCE, & LEGAL' => [
                [
                    'title' => 'Pencurian Data Rahasia dan Rahasia Dagang oleh Karyawan yang Mengundurkan Diri (Data Exfiltration on Resignation)',
                    'target' => 'Pencegahan eksfiltrasi 100% data bisnis strategis selama masa transisi pengunduran diri pekerja.',
                    'description' => 'Senior product manager mengunduh database prospek nasabah prioritas ke USB drive beberapa hari sebelum pindah ke bank pesaing.',
                    'category_id' => 10, 'cause_category_id' => 1, 'radar_code' => 'O3',
                    'inherent_p' => 4, 'inherent_i' => 5, 'residual_p' => 1, 'residual_i' => 2, 'impact_amount' => 2400000000,
                    'cause' => 'Tidak adanya pemantauan aktivitas file transfer khusus terhadap karyawan yang telah mengajukan surat pengunduran diri.',
                    'existing_control' => 'Penandatanganan perjanjian kerahasiaan data (NDA) saat proses exit interview.',
                    'mitigation' => 'Penerapan status pengawasan khusus pada sistem DLP bagi pekerja dalam masa notice period, pemblokiran USB mass storage, dan audit forensik laptop sebelum clearance.'
                ],
                [
                    'title' => 'Tingginya Tingkat Turn-Over pada Posisi Kritis Spesialis Basis Data dan Arsitek Keamanan Informasi',
                    'target' => 'Tingkat retensi talenta teknologi spesialis minimal 90% melalui skema kompensasi dan jalur karir adaptif.',
                    'description' => 'Eksodus 5 arsitek sistem senior dalam tempo 2 bulan menyebabkan penundaan proyek pembaruan core banking.',
                    'category_id' => 1, 'cause_category_id' => 1, 'radar_code' => 'O4',
                    'inherent_p' => 4, 'inherent_i' => 4, 'residual_p' => 2, 'residual_i' => 2, 'impact_amount' => 1450000000,
                    'cause' => 'Ketidakmampuan struktur gaji konvensional mengimbangi standar kompensasi industri teknologi global.',
                    'existing_control' => 'Survei kepuasan kerja karyawan tahunan.',
                    'mitigation' => 'Penyusunan Special Talent Retention Framework, pemberian tunjangan keahlian sertifikasi profesional, dan fleksibilitas kerja hybrid terstruktur.'
                ],
                [
                    'title' => 'Sengketa Hubungan Industrial Terkait Ketentuan Perjanjian Kerja Bersama dan UU Cipta Kerja',
                    'target' => 'Penyelesaian sengketa ketenagakerjaan melalui musyawarah mufakat bipartit tanpa aksi mogok kerja.',
                    'description' => 'Serikat pekerja melakukan unjuk rasa menentang perubahan skema uang pisah dan lembur pasca restrukturisasi operasional.',
                    'category_id' => 8, 'cause_category_id' => 1, 'radar_code' => 'L1',
                    'inherent_p' => 3, 'inherent_i' => 4, 'residual_p' => 1, 'residual_i' => 2, 'impact_amount' => 1100000000,
                    'cause' => 'Sosialisasi perubahan regulasi internal yang minim dan kurangnya keterlibatan perwakilan pekerja sejak tahap perumusan.',
                    'existing_control' => 'Forum dialog bipartit rutin setiap 6 bulan.',
                    'mitigation' => 'Pembaruan Perjanjian Kerja Bersama (PKB) transparan yang selaras dengan UU Cipta Kerja serta mediasi proaktif dengan dinas tenaga kerja.'
                ],
                [
                    'title' => 'Klaim Pelanggaran Hak Cipta Perangkat Lunak Akibat Penggunaan Lisensi Komputasi Melampaui Batas (Audit Under-Licensing)',
                    'target' => 'Kepatuhan 100% lisensi software enterprise berbasis Software Asset Management (SAM) otomatis.',
                    'description' => 'Vendor basis data multinasional menuntut denda penalti puluhan miliar rupiah karena jumlah core prosesor melebihi kuota lisensi kontrak.',
                    'category_id' => 8, 'cause_category_id' => 1, 'radar_code' => 'L1',
                    'inherent_p' => 3, 'inherent_i' => 5, 'residual_p' => 1, 'residual_i' => 2, 'impact_amount' => 3800000000,
                    'cause' => 'Tim infrastruktur menambah server virtual tanpa melakukan pengecekan lisensi dengan bagian legal pengadaan.',
                    'existing_control' => 'Penyimpanan bukti sertifikat lisensi fisik di file arsip legal.',
                    'mitigation' => 'Penerapan alat pemantau Software Asset Management otomatis yang memblokir penambahan vCPU/core sebelum lisensi tambahan dibeli.'
                ],
                [
                    'title' => 'Kekalahan Sengketa Litigasi Akibat Ambiguitas Klausul Eksekusi Jaminan dalam Akad Kredit Komersial',
                    'target' => 'Standarisasi klausul perjanjian pembiayaan dan jaminan yang memiliki kekuatan eksekutorial mutlak.',
                    'description' => 'Debitur macet berhasil membatalkan lelang agunan gedung karena adanya klausul yang saling bertentangan dalam perjanjian kredit.',
                    'category_id' => 8, 'cause_category_id' => 1, 'radar_code' => 'L1',
                    'inherent_p' => 3, 'inherent_i' => 5, 'residual_p' => 1, 'residual_i' => 2, 'impact_amount' => 2900000000,
                    'cause' => 'Penggunaan template akad lama tanpa telaah berkala terhadap preseden putusan Mahkamah Agung terkini.',
                    'existing_control' => 'Pemeriksaan legal drafting standar oleh legal officer cabang.',
                    'mitigation' => 'Pembaruan master credit agreement oleh konsultan hukum terkemuka dan mandatory legal review independen untuk kredit bernilai >Rp 10 miliar.'
                ],
                [
                    'title' => 'Kesalahan Perhitungan Pajak Penghasilan (PPh 21) dan Pajak Badan Berujung Surat Tagihan Pajak (STP)',
                    'target' => 'Akurasi 100% pelaporan dan pemotongan kewajiban perpajakan bebas denda administrasi pajak.',
                    'description' => 'Kekeliruan pemotongan PPh pasal 21 atas bonus karyawan dan tantiem direksi menyebabkan diterbitkannya SKP Kurang Bayar miliaran rupiah.',
                    'category_id' => 2, 'cause_category_id' => 1, 'radar_code' => 'F1',
                    'inherent_p' => 3, 'inherent_i' => 4, 'residual_p' => 1, 'residual_i' => 1, 'impact_amount' => 870000000,
                    'cause' => 'Keterlambatan pembaruan modul payroll sistem terhadap Peraturan Pemerintah mengenai Tarif Efektif Rata-Rata (TER) terbaru.',
                    'existing_control' => 'Rekonsiliasi pajak bulanan menggunakan spreadsheet manual.',
                    'mitigation' => 'Otomasi formula TER PPh 21 pada sistem payroll dan audit berkala oleh konsultan pajak terdaftar independen sebelum pelaporan SPT.'
                ],
                [
                    'title' => 'Ketiadaan Perlindungan Hukum yang Memadai dalam Perjanjian Kerahasiaan (NDA) dengan Vendor Pihak Ketiga',
                    'target' => 'Penetapan klausul kewajiban ganti rugi kerahasiaan data yang terukur dan berkekuatan hukum pada setiap NDA.',
                    'description' => 'Vendor yang gagal terpilih membagikan informasi arsitektur internal bank ke publik tanpa bank dapat menuntut ganti rugi karena lemahnya klausul NDA.',
                    'category_id' => 8, 'cause_category_id' => 1, 'radar_code' => 'L1',
                    'inherent_p' => 3, 'inherent_i' => 4, 'residual_p' => 1, 'residual_i' => 1, 'impact_amount' => 740000000,
                    'cause' => 'Penggunaan draf NDA generik pihak ketiga tanpa penyesuaian yurisdiksi dan klausul liquidated damages.',
                    'existing_control' => 'Kewajiban penandatanganan NDA sebelum dimulainya tender proyek.',
                    'mitigation' => 'Pemberlakuan template NDA terstandarisasi divisi legal dengan klausul ganti rugi tegas dan pemilihan yurisdiksi Pengadilan Negeri setempat.'
                ],
                [
                    'title' => 'Tuntutan Hukum Akibat Praktik Diskriminasi dan Ketidakadilan dalam Proses Promosi Jabatan',
                    'target' => 'Pelaksanaan asesmen karir dan suksesi jabatan yang transparan berbasis kompetensi teruji.',
                    'description' => 'Karyawan mengajukan gugatan atas dugaan bias gender dalam pengangkatan posisi kepala divisi yang mengabaikan hasil penilaian formal.',
                    'category_id' => 8, 'cause_category_id' => 1, 'radar_code' => 'L1',
                    'inherent_p' => 2, 'inherent_i' => 4, 'residual_p' => 1, 'residual_i' => 1, 'impact_amount' => 590000000,
                    'cause' => 'Keputusan promosi posisi strategis didominasi subjektivitas personal tanpa matriks kompetensi terukur.',
                    'existing_control' => 'Rapat dewan pertimbangan jabatan manajerial.',
                    'mitigation' => 'Implementasi sistem talent management berbasis 360-degree feedback, asesmen center independen, dan audit keragaman kompetensi.'
                ],
                [
                    'title' => 'Keterlibatan Karyawan dalam Praktik Suap dan Gratifikasi Penyaluran Kredit Fiktif (Internal Fraud)',
                    'target' => 'Pemberantasan tuntas internal fraud dengan deteksi dini dan zero tolerance bagi pelaku kejahatan perbankan.',
                    'description' => 'Account Officer bekerja sama dengan sindikat penipu untuk mencairkan kredit menggunakan KTP palsu demi imbalan uang pelicin.',
                    'category_id' => 1, 'cause_category_id' => 1, 'radar_code' => 'O3',
                    'inherent_p' => 4, 'inherent_i' => 5, 'residual_p' => 1, 'residual_i' => 2, 'impact_amount' => 3100000000,
                    'cause' => 'Tingginya target penjualan dipadukan dengan lemahnya verifikasi on-the-spot dan gaya hidup mewah tidak wajar staf.',
                    'existing_control' => 'Pemeriksaan kredit acak oleh auditor internal cabang.',
                    'mitigation' => 'Penerapan Fraud Detection System berbasis analitik transaksi rekening staf, rotasi mutasi wajib 2 tahun, dan pelaporan hukum kepolisian tanpa kompromi.'
                ],
                [
                    'title' => 'Keterlambatan Pelaporan Pengangkatan Pejabat Eksekutif kepada OJK untuk Uji Kelayakan dan Kepatutan',
                    'target' => 'Kepatuhan 100% jadwal pengajuan Fit and Proper Test sebelum pelantikan resmi seluruh pejabat eksekutif.',
                    'description' => 'Bank melantik Kepala Divisi Kepatuhan baru sebelum memperoleh surat keputusan persetujuan dari dewan komisioner OJK.',
                    'category_id' => 8, 'cause_category_id' => 1, 'radar_code' => 'C1',
                    'inherent_p' => 3, 'inherent_i' => 4, 'residual_p' => 1, 'residual_i' => 1, 'impact_amount' => 450000000,
                    'cause' => 'Kurangnya sinkronisasi antara jadwal rapat direksi pengangkatan dengan kalender proses perizinan regulator.',
                    'existing_control' => 'Konsultasi lisan dengan pengawas OJK.',
                    'mitigation' => 'Penyusunan Regulatory Approval Timeline Tracker yang mewajibkan clearance Fit and Proper Test sebelum penetapan SK direksi.'
                ],
                [
                    'title' => 'Kegagalan Pemrosesan Pembayaran Gaji Karyawan (Payroll Batch Failure) pada Tanggal Cut-Off',
                    'target' => 'Eksekusi transaksi penggajian karyawan berhasil 100% tepat waktu pada tanggal 25 setiap bulan.',
                    'description' => 'File data payroll mengalami kerusakan encoding, mengakibatkan puluhan ribu karyawan tidak menerima gaji tepat pada hari gajian.',
                    'category_id' => 1, 'cause_category_id' => 3, 'radar_code' => 'O1',
                    'inherent_p' => 3, 'inherent_i' => 4, 'residual_p' => 1, 'residual_i' => 1, 'impact_amount' => 620000000,
                    'cause' => 'Server batch payroll mengalami crash akibat file format yang dimodifikasi tanpa pengujian pra-eksekusi.',
                    'existing_control' => 'Validasi manual total nilai nominal transfer sebelum eksekusi sistem.',
                    'mitigation' => 'Implementasi automated pre-flight testing pada file payroll H-2 sebelum eksekusi dan ketersediaan jalur transfer pemulihan cepat (failover).'
                ],
                [
                    'title' => 'Kesenjangan Keterampilan Tenaga Kerja (Skill Gap) Menghadapi Migrasi Sistem Berbasis Cloud',
                    'target' => 'Sertifikasi kompetensi cloud minimal bagi 75% insinyur sistem dan pengembang aplikasi dalam 12 bulan.',
                    'description' => 'Pekerja operasional tidak memahami troubleshooting kontainer Kubernetes, menyebabkan ketergantungan mahal pada konsultan pihak ketiga.',
                    'category_id' => 1, 'cause_category_id' => 1, 'radar_code' => 'O4',
                    'inherent_p' => 4, 'inherent_i' => 3, 'residual_p' => 2, 'residual_i' => 2, 'impact_amount' => 780000000,
                    'cause' => 'Alokasi anggaran pelatihan teknologi tidak memprioritaskan sertifikasi arsitektur modern.',
                    'existing_control' => 'Program pelatihan e-learning umum tahunan.',
                    'mitigation' => 'Pembentukan IT Cloud Academy internal, pendanaan sertifikasi profesional (AWS/Azure/GCP/CKA), dan KPI pembelajaran wajib per individu.'
                ],
                [
                    'title' => 'Bocornya Identitas Pelapor Kasus Kejahatan Internal (Whistleblower Retaliation)',
                    'target' => 'Kerahasiaan 100% identitas pelapor kecurangan dan proteksi mutlak dari tindakan intimidasi balas dendam.',
                    'description' => 'Nama karyawan yang melaporkan penggelapan dana kas cabang diketahui oleh pelaku, memicu intimidasi fisik dan psikologis.',
                    'category_id' => 1, 'cause_category_id' => 1, 'radar_code' => 'O3',
                    'inherent_p' => 3, 'inherent_i' => 5, 'residual_p' => 1, 'residual_i' => 1, 'impact_amount' => 1350000000,
                    'cause' => 'Laporan whistleblowing dikirim melalui kotak surat email internal yang dapat diakses oleh beberapa administrator sistem.',
                    'existing_control' => 'Kebijakan perlindungan pelapor tertulis di pedoman perusahaan.',
                    'mitigation' => 'Penerapan Whistleblowing System (WBS) independen yang dikelola pihak ketiga tersertifikasi dengan enkripsi anonim mutlak.'
                ],
                [
                    'title' => 'Kekeliruan Perhitungan Aktuaria Pencadangan Imbalan Pasca Kerja Karyawan Sesuai PSAK 24',
                    'target' => 'Penetapan liabilitas imbalan kerja yang akurat dan terverifikasi oleh aktuaris publik independen.',
                    'description' => 'Kesalahan estimasi tingkat diskonto dan kenaikan gaji masa depan mengakibatkan lonjakan beban imbalan kerja tidak terduga di laporan laba rugi.',
                    'category_id' => 2, 'cause_category_id' => 1, 'radar_code' => 'F1',
                    'inherent_p' => 3, 'inherent_i' => 4, 'residual_p' => 1, 'residual_i' => 1, 'impact_amount' => 920000000,
                    'cause' => 'Data sensus demografi karyawan yang diserahkan ke aktuaris terlambat diperbarui dengan mutasi pekerja terkini.',
                    'existing_control' => 'Peninjauan laporan aktuaria tahunan oleh bagian akuntansi.',
                    'mitigation' => 'Integrasi data sensus kepegawaian real-time dari Core HRIS ke model perhitungan aktuaria dan audit berkala oleh auditor eksternal.'
                ],
                [
                    'title' => 'Penyerobotan Merk Dagang dan Hak Cipta Aplikasi Digital Bank oleh Pihak Ketiga',
                    'target' => 'Pendaftaran 100% merk, logo, dan hak kekayaan intelektual (HAKI) produk digital baru sebelum peluncuran.',
                    'description' => 'Nama aplikasi mobile banking baru dipatenkan oleh perusahaan startup lain karena keterlambatan pendaftaran ke Ditjen KI Kemenkumham.',
                    'category_id' => 8, 'cause_category_id' => 1, 'radar_code' => 'L1',
                    'inherent_p' => 3, 'inherent_i' => 4, 'residual_p' => 1, 'residual_i' => 1, 'impact_amount' => 850000000,
                    'cause' => 'Divisi bisnis meluncurkan kampanye branding tanpa meminta izin clearance penelusuran merk ke tim legal.',
                    'existing_control' => 'Pendaftaran merk dagang setelah produk resmi dipublikasikan.',
                    'mitigation' => 'Prosedur wajib Trademark Search dan pendaftaran HAKI pada tahapan awal inisiasi proyek teknologi (Stage Gate 1).'
                ],
                [
                    'title' => 'Kejenuhan Kerja Kronis (Burnout) dan Gangguan Kesehatan Mental pada Tim On-Call Engineering',
                    'target' => 'Pengurangan tingkat absensi sakit dan perputaran kerja tim operasional melalui manajemen beban kerja sehat.',
                    'description' => 'Insiden pemadaman berulang di luar jam kerja menyebabkan stres berkepanjangan dan penurunan fokus fatal personil teknis on-call.',
                    'category_id' => 1, 'cause_category_id' => 1, 'radar_code' => 'O4',
                    'inherent_p' => 4, 'inherent_i' => 3, 'residual_p' => 2, 'residual_i' => 1, 'impact_amount' => 420000000,
                    'cause' => 'Jadwal on-call yang tidak seimbang dan minimnya kompensasi waktu istirahat pengganti (off-in-lieu).',
                    'existing_control' => 'Program konseling kesehatan karyawan melalui klinik perusahaan.',
                    'mitigation' => 'Penerapan rotasi on-call berbasis follow-the-sun, mandatory compensatory time-off setelah penanganan insiden malam, dan program Employee Assistance Program (EAP).'
                ],
                [
                    'title' => 'Koreksi Pajak atas Penentuan Harga Transfer (Transfer Pricing) Transaksi Antar-Perusahaan Terafiliasi',
                    'target' => 'Dokumentasi Local File dan Master File Transfer Pricing yang solid dan sesuai prinsip Arm\'s Length Principle.',
                    'description' => 'Direktorat Jenderal Pajak mengoreksi biaya management fee dan lisensi TI yang dibayarkan ke perusahaan induk di luar negeri.',
                    'category_id' => 2, 'cause_category_id' => 1, 'radar_code' => 'F1',
                    'inherent_p' => 3, 'inherent_i' => 5, 'residual_p' => 1, 'residual_i' => 2, 'impact_amount' => 2700000000,
                    'cause' => 'Studi analisis kesebandingan (benchmarking study) biaya jasa intra-grup tidak mencerminkan kontribusi manfaat ekonomi riil.',
                    'existing_control' => 'Penyusunan dokumen Transfer Pricing tahunan formal.',
                    'mitigation' => 'Penyusunan dokumentasi TP kontemporer dengan analisis kontribusi nilai ekonomi (benefit test) yang diaudit oleh konsultan pajak independen.'
                ],
                [
                    'title' => 'Kesalahan Klasifikasi Akuntansi atas Instrumen Lindung Nilai Derivatif Sesuai Standar PSAK 71',
                    'target' => 'Penerapan akuntansi lindung nilai (Hedge Accounting) yang tepat bebas salah saji material laba komprehensif.',
                    'description' => 'Ketidakefektifan lindung nilai suku bunga tidak diukur dengan benar, memicu penyesuaian laba rugi mendadak senilai puluhan miliar rupiah.',
                    'category_id' => 2, 'cause_category_id' => 1, 'radar_code' => 'F1',
                    'inherent_p' => 3, 'inherent_i' => 4, 'residual_p' => 1, 'residual_i' => 1, 'impact_amount' => 1350000000,
                    'cause' => 'Sistem pencatatan treasury terpisah dari modul akuntansi buku besar umum dan pengujian efektivitas masih manual.',
                    'existing_control' => 'Review transaksi derivatif oleh auditor eksternal setiap akhir tahun buku.',
                    'mitigation' => 'Otomasi modul Hedge Accounting terintegrasi PSAK 71 dengan pengujian efektivitas prospektif dan retrospektif bulanan.'
                ],
                [
                    'title' => 'Pembatalan Kontrak Kerja Sama Teknologi Berbahasa Asing Akibat Pelanggaran UU Bendera dan Bahasa (UU 24/2009)',
                    'target' => 'Kewajiban penggunaan versi Bahasa Indonesia berdampingan pada seluruh kontrak komersial di wilayah RI.',
                    'description' => 'Pengadilan membatalkan perjanjian sewa sistem core banking dengan vendor asing karena kontrak hanya dibuat dalam bahasa Inggris.',
                    'category_id' => 8, 'cause_category_id' => 1, 'radar_code' => 'L1',
                    'inherent_p' => 3, 'inherent_i' => 4, 'residual_p' => 1, 'residual_i' => 1, 'impact_amount' => 890000000,
                    'cause' => 'Keinginan mempercepat tanda tangan kontrak dengan mengabaikan kewajiban penerjemahan resmi dwibahasa (bilingual format).',
                    'existing_control' => 'Pemeriksaan legal review kontrak luar negeri.',
                    'mitigation' => 'Mandatory bilingual clause execution pada setiap kontrak bisnis yang melibatkan pihak asing sesuai putusan MA dan UU 24/2009.'
                ],
                [
                    'title' => 'Keterlambatan Pencabutan Hak Akses Sistem Pegawai yang Berhenti Kerja (Delayed Account Deprovisioning)',
                    'target' => 'Pencabutan 100% hak akses user, email, dan VPN dalam waktu kurang dari 60 menit sejak pemutusan hubungan kerja.',
                    'description' => 'Mantan karyawan yang di-PHK masih dapat mengakses portal dokumen internal perusahaan selama 5 hari setelah tanggal efektif keluar.',
                    'category_id' => 10, 'cause_category_id' => 1, 'radar_code' => 'O3',
                    'inherent_p' => 4, 'inherent_i' => 4, 'residual_p' => 1, 'residual_i' => 1, 'impact_amount' => 670000000,
                    'cause' => 'Notifikasi pemutusan kerja dari divisi HR dikirim secara manual melalui email dan tidak terhubung otomatis dengan identity management.',
                    'existing_control' => 'Pemberitahuan daftar karyawan keluar setiap akhir pekan ke divisi IT.',
                    'mitigation' => 'Integrasi otomatis real-time antara HRIS Core dengan Active Directory (SCIM protocol) yang otomatis menonaktifkan akun saat status terminated tercapai.'
                ],
            ],

            // 21. CO - COMPLIANCE
            'CO - COMPLIANCE' => [
                [
                    'title' => 'Sanksi Denda Administratif Maksimal 2% Pendapatan Tahunan Akibat Kegagalan Melaporkan Kebocoran Data (UU PDP)',
                    'target' => 'Penyampaian notifikasi tertulis insiden kegagalan perlindungan data kepada otoritas dalam tempo 3x24 jam.',
                    'description' => 'Bank terlambat memberitahukan insiden kebocoran data nasabah kepada Lembaga Pelindung Data Pribadi dan publik melebihi batas 72 jam.',
                    'category_id' => 8, 'cause_category_id' => 1, 'radar_code' => 'C1',
                    'inherent_p' => 4, 'inherent_i' => 5, 'residual_p' => 1, 'residual_i' => 2, 'impact_amount' => 5200000000,
                    'cause' => 'Birokrasi pelaporan eskalasi internal yang berbelit-belit dan keengganan membuka insiden karena takut rusaknya citra bank.',
                    'existing_control' => 'Prosedur eskalasi insiden keamanan informasi standar.',
                    'mitigation' => 'Penyusunan Data Breach Notification Playbook terpadu dengan SLA maksimal 24 jam penentuan status eskalasi ke DPO dan Komite Kepatuhan.'
                ],
                [
                    'title' => 'Ketidakpatuhan Penyelenggaraan Ketahanan Siber Terhadap Surat Edaran Otoritas Jasa Keuangan (SEOJK 29/2022)',
                    'target' => 'Pemenuhan 100% klausul audit ketahanan siber dan pengujian skenario krisis perbankan berkala.',
                    'description' => 'OJK menemukan bahwa bank belum melakukan pengujian penetrasi independen (pen-test) tahunan pada seluruh aplikasi transaksi utama.',
                    'category_id' => 8, 'cause_category_id' => 1, 'radar_code' => 'C1',
                    'inherent_p' => 4, 'inherent_i' => 4, 'residual_p' => 1, 'residual_i' => 2, 'impact_amount' => 1800000000,
                    'cause' => 'Alokasi anggaran pen-test pihak ketiga dipotong dan fokus audit hanya pada laporan penilaian mandiri (self-assessment).',
                    'existing_control' => 'Pemindaian kerentanan otomatis internal oleh staf cyber security.',
                    'mitigation' => 'Pelaksanaan independent third-party penetration testing tahunan, cyber drill skenario krisis, dan pemenuhan checklist SEOJK 29 secara kuartalan.'
                ],
                [
                    'title' => 'Pelanggaran Tata Kelola Penyelenggaraan Sistem Pembayaran Berdasarkan Peraturan Bank Indonesia (PADG 24/2022)',
                    'target' => 'Ketersediaan layanan transfer BI-FAST mencapai 99.9% dan penyelesaian keluhan transaksi sengketa dalam 5 hari kerja.',
                    'description' => 'Tingginya angka kegagalan transaksi transfer antarbank memicu surat teguran tertulis dan ancaman penurunan peringkat kepatuhan dari Bank Indonesia.',
                    'category_id' => 8, 'cause_category_id' => 3, 'radar_code' => 'C1',
                    'inherent_p' => 4, 'inherent_i' => 4, 'residual_p' => 1, 'residual_i' => 2, 'impact_amount' => 1500000000,
                    'cause' => 'Kapasitas konektivitas jaringan sistem pembayaran tidak ditambah sebanding dengan lonjakan volume transaksi belanja daring.',
                    'existing_control' => 'Monitoring uptime layanan konektivitas BI-FAST oleh tim switching.',
                    'mitigation' => 'Peningkatan kapasitas bandwidth gateway BI, implementasi redundant switching connector, dan auto-reversal transaksi gagal.'
                ],
                [
                    'title' => 'Keterlambatan dan Kelalaian Penyampaian Laporan Transaksi Keuangan Mencurigakan (LTKM) ke PPATK',
                    'target' => 'Penyampaian 100% laporan LTKM ke aplikasi goAML PPATK paling lambat 3 hari kerja setelah terindikasi mencurigakan.',
                    'description' => 'Pola transaksi rekening penampung judi online tidak dilaporkan selama 2 minggu karena antrian telaah analis AML manual menumpuk.',
                    'category_id' => 8, 'cause_category_id' => 1, 'radar_code' => 'C1',
                    'inherent_p' => 4, 'inherent_i' => 5, 'residual_p' => 1, 'residual_i' => 2, 'impact_amount' => 2300000000,
                    'cause' => 'Sistem monitoring transaksi anti-money laundering (AML) menghasilkan ribuan false-positive yang membebani tim analis.',
                    'existing_control' => 'Penyaringan aturan statis transaksi di atas ambang batas Rp 500 juta.',
                    'mitigation' => 'Penerapan AML Transaction Monitoring berbasis machine learning untuk mereduksi false positive dan automated draft report generator ke format XML PPATK.'
                ],
                [
                    'title' => 'Pelanggaran Ketentuan Transfer Data Pribadi Melintasi Batas Wilayah Negara (Cross-Border Data Transfer) UU PDP',
                    'target' => 'Pemenuhan 100% kepatuhan standar kesetaraan perlindungan data sebelum transmisi lintas batas negara.',
                    'description' => 'Data profil keuangan nasabah dikirim ke server analitik cloud di luar wilayah Indonesia tanpa persetujuan eksplisit nasabah.',
                    'category_id' => 8, 'cause_category_id' => 1, 'radar_code' => 'C1',
                    'inherent_p' => 4, 'inherent_i' => 5, 'residual_p' => 1, 'residual_i' => 2, 'impact_amount' => 3200000000,
                    'cause' => 'Pemilihan region data center cloud di luar yurisdiksi Indonesia demi efisiensi biaya tanpa legal data transfer impact assessment.',
                    'existing_control' => 'Pencantuman klausul izin umum pemrosesan data pihak ketiga pada syarat dan ketentuan.',
                    'mitigation' => 'Migrasi penyimpanan data nasabah ke region cloud lokal Indonesia, penyusunan Data Transfer Agreement (DTA), dan persetujuan eksplisit (explicit consent).'
                ],
                [
                    'title' => 'Pengenaan Sanksi Perlindungan Konsumen Sektor Jasa Keuangan Terkait Praktik Biaya Tersembunyi (POJK 6/2022)',
                    'target' => 'Transparansi penuh pengenaan biaya administrasi dan suku bunga pinjaman pada seluruh saluran layanan.',
                    'description' => 'OJK mengenakan sanksi denda atas aduan nasabah mengenai pengenaan biaya asuransi pinjaman yang tidak diinformasikan di ringkasan produk.',
                    'category_id' => 8, 'cause_category_id' => 1, 'radar_code' => 'C1',
                    'inherent_p' => 4, 'inherent_i' => 4, 'residual_p' => 1, 'residual_i' => 1, 'impact_amount' => 950000000,
                    'cause' => 'Dokumen Ringkasan Informasi Produk dan Layanan (RIPLAY) versi cetak berbeda dengan penjelasan telemarketing.',
                    'existing_control' => 'Verifikasi rekaman telepon acak oleh tim quality assurance telemarketing.',
                    'mitigation' => 'Standardisasi dokumen digital RIPLAY pada aplikasi mobile sebelum persetujuan pinjaman dan audit kepatuhan materi penjualan berkala.'
                ],
                [
                    'title' => 'Kelalaian Penyediaan Saluran Penyampaian Pengaduan Konsumen Ramah Disabilitas dan Transparan',
                    'target' => 'Ketersediaan layanan pengaduan nasabah inklusif 24/7 dengan tracking nomor tiket online.',
                    'description' => 'Aplikasi perbankan tidak dapat dioperasikan oleh nasabah tunanetra (tidak lolos standar aksesibilitas WCAG), menuai teguran regulator.',
                    'category_id' => 8, 'cause_category_id' => 3, 'radar_code' => 'C1',
                    'inherent_p' => 3, 'inherent_i' => 3, 'residual_p' => 1, 'residual_i' => 1, 'impact_amount' => 420000000,
                    'cause' => 'Pengembangan antarmuka aplikasi digital mengabaikan pedoman aksesibilitas inklusif bagi penyandang disabilitas.',
                    'existing_control' => 'Layanan antrean khusus nasabah prioritas di kantor cabang.',
                    'mitigation' => 'Audit aksesibilitas aplikasi mobile banking sesuai standar WCAG 2.1 level AA dan peluncuran portal tracking status sengketa terintegrasi Kontak OJK 157.'
                ],
                [
                    'title' => 'Kegagalan Penyaringan Profil Tokoh yang Berpengaruh Secara Politik (Politically Exposed Persons / PEP)',
                    'target' => 'Penyaringan otomatis 100% nasabah baru terhadap database PEP, sanksi internasional, dan daftar terorisme.',
                    'description' => 'Pejabat negara yang sedang dalam penyidikan korupsi dapat membuka rekening private banking tanpa proses Enhanced Due Diligence (EDD).',
                    'category_id' => 8, 'cause_category_id' => 1, 'radar_code' => 'C1',
                    'inherent_p' => 4, 'inherent_i' => 5, 'residual_p' => 1, 'residual_i' => 2, 'impact_amount' => 2800000000,
                    'cause' => 'Sistem screening hanya mencocokkan kesamaan nama persis (exact match) tanpa menggunakan algoritma fuzzy name matching.',
                    'existing_control' => 'Pengecekan nama nasabah ke database PEP secara berkala.',
                    'mitigation' => 'Penerapan modul automated name screening terintegrasi World-Check / Dow Jones dengan algoritma fuzzy matching dan mandatory senior management approval untuk nasabah PEP.'
                ],
                [
                    'title' => 'Pelanggaran Batas Maksimum Pemberian Kredit (BMPK) kepada Pihak Terkait dan Debitur Grup Konglomerasi',
                    'target' => 'Kepatuhan 100% ketentuan BMPK/Lending Limit terhadap modal bank sesuai POJK 32/2018.',
                    'description' => 'Pencairan pinjaman tambahan menyebabkan eksposur kredit grup debitur tertentu melebihi 25% dari total modal inti bank.',
                    'category_id' => 8, 'cause_category_id' => 1, 'radar_code' => 'C1',
                    'inherent_p' => 3, 'inherent_i' => 5, 'residual_p' => 1, 'residual_i' => 2, 'impact_amount' => 3700000000,
                    'cause' => 'Struktur kepemilikan konglomerasi debitur tidak terkonsolidasi dengan benar pada sistem loan monitoring bank.',
                    'existing_control' => 'Pengecekan limit kredit manual sebelum penandatanganan akad.',
                    'mitigation' => 'Implementasi modul automated Group Borrower Exposure Check yang otomatis memblokir persetujuan kredit jika melampaui ambang batas BMPK.'
                ],
                [
                    'title' => 'Pelanggaran Ketentuan Kewajiban Penggunaan Mata Uang Rupiah di Wilayah NKRI (PBI No 17/3/PBI/2015)',
                    'target' => 'Kepatuhan mutlak penggunaan Rupiah pada seluruh transaksi domestik non-tunai dan tunai perbankan.',
                    'description' => 'Bank mengeksekusi kontrak pembayaran jasa pemeliharaan server lokal menggunakan denominasi Dolar AS tanpa mengantongi izin Bank Indonesia.',
                    'category_id' => 8, 'cause_category_id' => 1, 'radar_code' => 'C1',
                    'inherent_p' => 3, 'inherent_i' => 4, 'residual_p' => 1, 'residual_i' => 1, 'impact_amount' => 650000000,
                    'cause' => 'Vendor asing mendesak penagihan dalam mata uang valuta asing dan tim procurement tidak berkonsultasi dengan tim kepatuhan.',
                    'existing_control' => 'Review invoice pembayaran valas oleh bagian settlement treasury.',
                    'mitigation' => 'Kewajiban pencantuman klausul pembayaran denominasi Rupiah pada seluruh kontrak domestik dan verifikasi surat pengecualian BI resmi.'
                ],
                [
                    'title' => 'Tidak Berjalannya Sistem Pelaporan Pelanggaran (Whistleblowing System) Sesuai Tata Kelola POJK GCG',
                    'target' => 'Pengelolaan saluran whistleblowing independen yang terjamin kerahasiaannya dengan tindak lanjut 100% aduan.',
                    'description' => 'OJK menemukan bahwa aduan whistleblowing mengenai gratifikasi pimpinan cabang tidak pernah diinvestigasi selama 6 bulan.',
                    'category_id' => 8, 'cause_category_id' => 1, 'radar_code' => 'C1',
                    'inherent_p' => 3, 'inherent_i' => 4, 'residual_p' => 1, 'residual_i' => 1, 'impact_amount' => 780000000,
                    'cause' => 'Pengelolaan kotak aduan WBS berada di bawah unit operasional yang berkonflik kepentingan dengan pihak terlapor.',
                    'existing_control' => 'Alamat email pengaduan yang dikelola sekretariat perusahaan.',
                    'mitigation' => 'Pemisahan pengelolaan WBS langsung di bawah Komite Audit Dewan Komisaris dan integrasi platform pelaporan eksternal tersertifikasi.'
                ],
                [
                    'title' => 'Pelanggaran Pemenuhan Rasio Kecukupan Likuiditas (Liquidity Coverage Ratio / LCR) di Bawah Batas Minimum',
                    'target' => 'Pemeliharaan rasio LCR harian di atas batas ambang aman regulasi minimum 100%.',
                    'description' => 'Penarikan dana giro korporasi secara serentak di akhir bulan menyebabkan rasio likuiditas turun menjadi 88%, memicu pengawasan intensif OJK.',
                    'category_id' => 2, 'cause_category_id' => 4, 'radar_code' => 'F1',
                    'inherent_p' => 3, 'inherent_i' => 5, 'residual_p' => 1, 'residual_i' => 2, 'impact_amount' => 4100000000,
                    'cause' => 'Manajemen likuiditas tidak mengantisipasi jadwal pembayaran dividen BUMN dan penempatan HQLA terlalu tipis.',
                    'existing_control' => 'Perhitungan rasio LCR mingguan oleh divisi ALM Treasury.',
                    'mitigation' => 'Penyediaan cadangan aset likuid berkualitas tinggi (HQLA) dengan buffer 15% di atas batas regulasi dan stress testing likuiditas mingguan.'
                ],
                [
                    'title' => 'Inkonsistensi dan Keterlambatan Pelaporan Keuangan Berkala pada Sistem Otomasi Pelaporan OJK (APOLO)',
                    'target' => 'Pengiriman laporan berkala APOLO OJK dengan akurasi 100% sebelum batas waktu cut-off.',
                    'description' => 'Data pembukuan cabang tidak sinkron dengan laporan neraca gabungan, menyebabkan denda keterlambatan pelaporan APOLO ratusan juta rupiah.',
                    'category_id' => 8, 'cause_category_id' => 3, 'radar_code' => 'C1',
                    'inherent_p' => 4, 'inherent_i' => 4, 'residual_p' => 1, 'residual_i' => 1, 'impact_amount' => 720000000,
                    'cause' => 'Ekstraksi data dari core banking ke template pelaporan APOLO masih menggunakan konversi file semi-manual.',
                    'existing_control' => 'Rekonsiliasi data pelaporan oleh tim regulasi finance H-1 sebelum deadline.',
                    'mitigation' => 'Automated Regulatory Reporting Gateway dengan automated validation engine yang memverifikasi integritas data sebelum transmisi ke OJK.'
                ],
                [
                    'title' => 'Penyimpangan Prinsip Syariah (Sharia Non-Compliance Risk) pada Pembiayaan Murabahah Unit Usaha Syariah',
                    'target' => 'Kepatuhan 100% akad syariah terhadap fatwa Dewan Syariah Nasional Majelis Ulama Indonesia (DSN-MUI).',
                    'description' => 'Bank mencairkan dana pembiayaan jual beli kepada debitur sebelum barang komoditas sah dimiliki oleh bank (qabdh belum terpenuhi).',
                    'category_id' => 8, 'cause_category_id' => 1, 'radar_code' => 'C1',
                    'inherent_p' => 3, 'inherent_i' => 5, 'residual_p' => 1, 'residual_i' => 2, 'impact_amount' => 1950000000,
                    'cause' => 'Petugas legal dan sales syariah belum memahami syarat sah rukun akad murabahah mutlak.',
                    'existing_control' => 'Pemeriksaan sampel akad pembiayaan oleh Dewan Pengawas Syariah (DPS).',
                    'mitigation' => 'Standardisasi digital workflow pembiayaan syariah yang mengunci pencairan dana sebelum dokumen wakalah dan bukti kepemilikan terverifikasi DPS.'
                ],
                [
                    'title' => 'Kelalaian Pelaksanaan Penilaian Mandiri Keamanan Informasi (Cyber Security Maturity Assessment) Tahunan',
                    'target' => 'Penyelesaian dan penyampaian hasil Cyber Security Maturity level minimal Tier 4 kepada OJK tepat waktu.',
                    'description' => 'Bank tidak menyerahkan laporan maturitas siber tahunan yang diwajibkan oleh regulator perbankan, memicu audit investigatif khusus.',
                    'category_id' => 8, 'cause_category_id' => 1, 'radar_code' => 'C1',
                    'inherent_p' => 3, 'inherent_i' => 4, 'residual_p' => 1, 'residual_i' => 1, 'impact_amount' => 880000000,
                    'cause' => 'Ketidakjelasan koordinasi penanggung jawab antara divisi Information Security dan divisi Compliance.',
                    'existing_control' => 'Penyusunan jadwal audit kepatuhan tahunan internal.',
                    'mitigation' => 'Penunjukan divisi kepatuhan sebagai koordinator pelaporan resmi dan pelibatan asesor independen terakreditasi sejak awal kuartal ke-3.'
                ],
                [
                    'title' => 'Kelalaian Pembaruan Daftar Sanksi Terorisme dan Proliferasi Senjata Pemusnah Massal (DTTOT)',
                    'target' => 'Sinkronisasi instan database sanksi DTTOT dan pemblokiran otomatis rekening terduga teroris.',
                    'description' => 'Nasabah yang terdaftar dalam daftar DTTOT terbaru berhasil melakukan transfer dana ke luar negeri karena sistem screening terlambat diperbarui.',
                    'category_id' => 8, 'cause_category_id' => 1, 'radar_code' => 'C1',
                    'inherent_p' => 4, 'inherent_i' => 5, 'residual_p' => 1, 'residual_i' => 2, 'impact_amount' => 3900000000,
                    'cause' => 'Pengunduhan daftar DTTOT dari portal kepolisian dan PPATK dilakukan manual oleh staf setiap hari Senin.',
                    'existing_control' => 'Pemeriksaan nama debitur saat pengajuan kredit baru.',
                    'mitigation' => 'Automasi API ingestion database DTTOT real-time dengan auto-freeze saldo seketika saat terdeteksi kecocokan profil nama dan tanggal lahir.'
                ],
                [
                    'title' => 'Klaim Menyesatkan Terkait Pembiayaan Berkelanjutan (Greenwashing) pada Laporan Keberlanjutan',
                    'target' => 'Validasi dan assurance independen 100% data pembiayaan hijau sesuai Taksonomi Keuangan Berkelanjutan Indonesia (TKBI).',
                    'description' => 'Bank mengklaim pembiayaan pabrik kelapa sawit sebagai proyek ramah lingkungan tanpa verifikasi sertifikasi ISPO/RSPO, memicu teguran OJK.',
                    'category_id' => 8, 'cause_category_id' => 1, 'radar_code' => 'C1',
                    'inherent_p' => 3, 'inherent_i' => 4, 'residual_p' => 1, 'residual_i' => 1, 'impact_amount' => 1100000000,
                    'cause' => 'Ketiadaan kerangka kerja verifikasi teknis keberlanjutan yang ketat di level komite kredit.',
                    'existing_control' => 'Laporan sustainability report tahunan yang disusun tim corporate communication.',
                    'mitigation' => 'Penerapan Sustainable Finance Framework dengan verifikasi audit independen pihak ketiga dan kriteria hijau berbasis TKBI.'
                ],
                [
                    'title' => 'Peluncuran Produk Pinjaman Digital Baru Tanpa Persetujuan Tertulis (Regulatory Approval) OJK',
                    'target' => 'Penyelesaian izin regulator (Non-Objection Letter) 100% sebelum peluncuran fitur digital ke publik.',
                    'description' => 'Fitur Paylater pada aplikasi mobile banking dirilis secara komersial sebelum mengantongi izin persetujuan produk baru dari OJK.',
                    'category_id' => 8, 'cause_category_id' => 1, 'radar_code' => 'C1',
                    'inherent_p' => 4, 'inherent_i' => 5, 'residual_p' => 1, 'residual_i' => 2, 'impact_amount' => 3400000000,
                    'cause' => 'Tekanan target persaingan pasar yang memaksa go-live lebih cepat dan salah mengartikan status konsultasi lisan regulator.',
                    'existing_control' => 'Review kepatuhan produk oleh legal officer.',
                    'mitigation' => 'Enforcement Product Governance Policy yang mensyaratkan surat izin resmi OJK sebagai prasyarat wajib pembukaan release flag sistem produksi.'
                ],
                [
                    'title' => 'Benturan Kepentingan dalam Transaksi dengan Pihak Afiliasi Tanpa Persetujuan Komite Audit',
                    'target' => 'Kepatuhan 100% tata kelola transaksi benturan kepentingan dan keterbukaan informasi pihak terafiliasi.',
                    'description' => 'Kontrak sewa ruang kantor cabang ditandatangani dengan yayasan milik keluarga komisaris tanpa persetujuan komite audit independen.',
                    'category_id' => 8, 'cause_category_id' => 1, 'radar_code' => 'L1',
                    'inherent_p' => 3, 'inherent_i' => 4, 'residual_p' => 1, 'residual_i' => 1, 'impact_amount' => 1250000000,
                    'cause' => 'Kelemahan sistem pengungkapan deklarasi benturan kepentingan (Conflict of Interest Declaration) bagi pejabat struktural.',
                    'existing_control' => 'Pengisian formulir keterbukaan hubungan keluarga saat awal menjabat.',
                    'mitigation' => 'Pemberlakuan verifikasi database profil afiliasi pejabat otomatis pada setiap pengadaan barang/jasa dan mandatory audit committee clearance.'
                ],
                [
                    'title' => 'Pemusnahan Dokumen Arsip Perusahaan Sebelum Masa Retensi Wajib Sesuai Undang-Undang Dokumen Perusahaan',
                    'target' => 'Penyimpanan dokumen finansial dan legal perusahaan minimum 10 tahun sesuai ketentuan hukum perdata dan pajak.',
                    'description' => 'Berkas bukti transfer dan rekening koran berumur 5 tahun dimusnahkan sehingga bank tidak mampu menyajikan bukti dalam sidang sengketa perpajakan.',
                    'category_id' => 8, 'cause_category_id' => 1, 'radar_code' => 'L1',
                    'inherent_p' => 3, 'inherent_i' => 4, 'residual_p' => 1, 'residual_i' => 1, 'impact_amount' => 1400000000,
                    'cause' => 'Pembersihan gudang arsip dilakukan secara sepihak oleh unit cabang tanpa berpedoman pada Jadwal Retensi Arsip (JRA) resmi.',
                    'existing_control' => 'Persetujuan pimpinan cabang untuk pemusnahan dokumen lokal.',
                    'mitigation' => 'Sentralisasi Jadwal Retensi Arsip terpadu dengan persetujuan wajib divisi Compliance dan Legal sebelum pemusnahan fisik dilakukan.'
                ],
            ],
        ];
    }
}
