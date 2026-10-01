<?php

namespace Database\Seeders\Data;

class RiskDataBatch1
{
    public static function getData(): array
    {
        return [
            // 1. BT - BANKING APPLICATIONS
            'BT - BANKING APPLICATIONS' => [
                [
                    'title' => 'Kegagalan Sinkronisasi Transaksi Batch End-of-Day pada Modul Loan Origination',
                    'target' => 'Penyelesaian batch EOD tepat waktu sebelum pukul 03:00 WIB dengan akurasi 100%.',
                    'description' => 'Lonjakan volume debitur restrukturisasi menyebabkan lock timeout pada tabel amortisasi pinjaman selama proses batching malam.',
                    'category_id' => 7, 'cause_category_id' => 3, 'radar_code' => 'O2',
                    'inherent_p' => 4, 'inherent_i' => 4, 'residual_p' => 2, 'residual_i' => 3, 'impact_amount' => 450000000,
                    'cause' => 'Query tidak optimal pada indexing tabel riwayat mutasi kredit dan lock contention transaksi konkruen.',
                    'existing_control' => 'Monitoring alert log database dan manual re-run batch script oleh tim on-call.',
                    'mitigation' => 'Refactoring modul batch processing dengan sharding database, partition table kredit, dan implementasi automated retry queuing.'
                ],
                [
                    'title' => 'Latensi Tinggi dan Timeout pada Integrasi Pembayaran Multifinance via API',
                    'target' => 'Response time API pembayaran pinjaman multifinance di bawah 800ms dengan availability 99.9%.',
                    'description' => 'Lonjakan traffic saat jam pembayaran tagihan akhir bulan memicu connection pool exhaustion pada middleware microservices.',
                    'category_id' => 9, 'cause_category_id' => 3, 'radar_code' => 'O5',
                    'inherent_p' => 4, 'inherent_i' => 3, 'residual_p' => 2, 'residual_i' => 2, 'impact_amount' => 250000000,
                    'cause' => 'Keterbatasan thread pool worker pada container Kubernetes dan ketiadaan dynamic auto-scaling.',
                    'existing_control' => 'Load balancer round-robin statis dan health check endpoint.',
                    'mitigation' => 'Penerapan Horizontal Pod Autoscaler (HPA), asynchronous message queue RabbitMQ, dan rate limiting adaptif.'
                ],
                [
                    'title' => 'Anomali Perhitungan Bunga Majemuk pada Produk Deposito Khusus High-Net-Worth',
                    'target' => 'Kalkulasi bagi hasil dan bunga simpanan 100% akurat sesuai skema master agreement.',
                    'description' => 'Perubahan aturan leap year dan parameter formula suku bunga bertingkat menghasilkan deviasi perhitungan nominal bagi hasil nasabah prioritas.',
                    'category_id' => 5, 'cause_category_id' => 2, 'radar_code' => 'O4',
                    'inherent_p' => 3, 'inherent_i' => 4, 'residual_p' => 1, 'residual_i' => 3, 'impact_amount' => 500000000,
                    'cause' => 'Validasi logic formula calculation pada engine aplikasi belum meng-cover edge-case tahun kabisat dan penarikan break deposito parsial.',
                    'existing_control' => 'Review manual sampel rekonsiliasi harian oleh back-office.',
                    'mitigation' => 'Pengembangan automated calculation verification tool dan pengetatan automated unit testing parameter bunga.'
                ],
                [
                    'title' => 'Discrepancy Data Saldo Rekening Escrow Nasabah Korporasi Saat Settlement',
                    'target' => 'Zero break pada rekonsiliasi akun penampungan escrow korporasi harian.',
                    'description' => 'Unacknowledged webhook dari payment aggregator menyebabkan saldo virtual ledger tidak ter-update real-time.',
                    'category_id' => 11, 'cause_category_id' => 2, 'radar_code' => 'O2',
                    'inherent_p' => 3, 'inherent_i' => 4, 'residual_p' => 1, 'residual_i' => 3, 'impact_amount' => 600000000,
                    'cause' => 'Mekanisme rekonsiliasi dua arah (two-way reconciliation) masih berbasis polling interval 30 menit.',
                    'existing_control' => 'Script rekonsiliasi harian T+1 pagi hari.',
                    'mitigation' => 'Implementasi idempotency token pada webhook handler dan event-driven ledger sync via Kafka streaming.'
                ],
                [
                    'title' => 'Kerentanan Insecure Direct Object Reference (IDOR) pada Modul Unduh Rekening Koran',
                    'target' => 'Zero critical/high vulnerabilities pada modul laporan nasabah korporasi.',
                    'description' => 'Parameter account number pada endpoint API download e-statement tidak tervalidasi terhadap session user token.',
                    'category_id' => 10, 'cause_category_id' => 3, 'radar_code' => 'O3',
                    'inherent_p' => 4, 'inherent_i' => 5, 'residual_p' => 1, 'residual_i' => 3, 'impact_amount' => 1200000000,
                    'cause' => 'Missing object-level access control check pada controller reporting service.',
                    'existing_control' => 'Autentikasi JWT dasar dan firewall WAF cloud.',
                    'mitigation' => 'Penguatan RBAC berbasis tenant context, implementasi automated DAST/SAST pada CI/CD pipeline, dan audit pentest berkala.'
                ],
                [
                    'title' => 'Kegagalan Otomasi Reversal Transaksi ATM Off-us via Gerbang Pembayaran Nasional',
                    'target' => 'Reversal otomatis atas transaksi tarik tunai ATM gagal maksimal 15 menit.',
                    'description' => 'Kegagalan sinyal reversal dari switching network mengakibatkan dana nasabah terdebet tanpa uang tunai keluar.',
                    'category_id' => 7, 'cause_category_id' => 3, 'radar_code' => 'O5',
                    'inherent_p' => 4, 'inherent_i' => 3, 'residual_p' => 2, 'residual_i' => 2, 'impact_amount' => 300000000,
                    'cause' => 'Timeout interval ISO 8583 message handling yang terlalu ketat saat jaringan interkoneksi switching padat.',
                    'existing_control' => 'Klaim manual dispute transaksi ATM oleh cabang/helpdesk.',
                    'mitigation' => 'Peningkatan auto-reversal daemon engine dan automated dispute clearing integration dengan switching GPN.'
                ],
                [
                    'title' => 'Memory Leak pada Background Daemon Pemrosesan Notifikasi SMS/WhatsApp Banking',
                    'target' => 'SLA pengiriman OTP dan notifikasi mutasi kurang dari 10 detik.',
                    'description' => 'Worker daemon mengalami memory footprint bloat dan unhandled crash setelah memproses jutaan blast notifikasi gajian.',
                    'category_id' => 9, 'cause_category_id' => 3, 'radar_code' => 'O2',
                    'inherent_p' => 3, 'inherent_i' => 3, 'residual_p' => 1, 'residual_i' => 2, 'impact_amount' => 200000000,
                    'cause' => 'Unclosed connection pool redis and lingering buffer variables pada service worker.',
                    'existing_control' => 'Cron job restart service setiap malam pukul 00:00.',
                    'mitigation' => 'Refactoring memory management, implementasi container memory limit restart, dan queue partition microservices.'
                ],
                [
                    'title' => 'Keterlambatan Eksekusi Standing Instruction Payroll Korporasi Skala Besar',
                    'target' => '100% payroll corporate tuntas sebelum jam cut-off operasional 11:00 WIB.',
                    'description' => 'File transfer batch payroll ribuan karyawan mengalami deadlock saat penulisan ke core ledger bersamaan dengan transaksi harian nasabah.',
                    'category_id' => 7, 'cause_category_id' => 2, 'radar_code' => 'O5',
                    'inherent_p' => 4, 'inherent_i' => 4, 'residual_p' => 2, 'residual_i' => 2, 'impact_amount' => 750000000,
                    'cause' => 'Mekanisme bulk insert serial tanpa pembagian batch chunking dan concurrency throttle.',
                    'existing_control' => 'Pemisahan window jam upload payroll korporasi.',
                    'mitigation' => 'Implementasi multi-threaded async chunk processing dengan dedicated read-write replica database.'
                ],
                [
                    'title' => 'Kegagalan Validasi Sertifikat Digital pada Modul Host-to-Host Korporasi',
                    'target' => 'Zero security bypass pada kanal H2H B2B banking.',
                    'description' => 'Kedaluwarsanya CA bundle partner corporate yang tidak termonitor menyebabkan transaksi debit otomatis tertahan 24 jam.',
                    'category_id' => 8, 'cause_category_id' => 2, 'radar_code' => 'C2',
                    'inherent_p' => 3, 'inherent_i' => 4, 'residual_p' => 1, 'residual_i' => 2, 'impact_amount' => 400000000,
                    'cause' => 'Tidak adanya automated alert monitoring masa berlaku SSL/mTLS client certificate korporasi.',
                    'existing_control' => 'Pencatatan masa aktif sertifikat pada dokumen spreadsheet operasional.',
                    'mitigation' => 'Penerapan automated certificate expiration monitoring 30-60-90 hari via notification dashboard.'
                ],
                [
                    'title' => 'Ketidakakuratan Penentuan Scoring Limit Kredit Otomatis pada Modul Digital Lending',
                    'target' => 'Akurasi model credit scoring minimal 95% tanpa deviasi parameter profil risiko.',
                    'description' => 'Drift data behavioral nasabah pasca-update rule engine mengakibatkan over-limit approval pinjaman tanpa jaminan.',
                    'category_id' => 5, 'cause_category_id' => 2, 'radar_code' => 'S2',
                    'inherent_p' => 3, 'inherent_i' => 5, 'residual_p' => 2, 'residual_i' => 3, 'impact_amount' => 1500000000,
                    'cause' => 'Model scoring tidak dikalibrasi ulang terhadap variabel makroekonomi dan kriteria SLIK terbaru.',
                    'existing_control' => 'Supervisi manual berkala oleh credit committee terhadap loan di atas 50 juta.',
                    'mitigation' => 'Audit kuartalan model credit underwriting, dual validation hard-cap limit, dan automated anomaly detection.'
                ],
                [
                    'title' => 'Bottleneck Query Laporan Mutasi Rekening Giro Multi-Currency pada Periode Audit',
                    'target' => 'Response time unduh mutasi di bawah 5 detik untuk data historis 12 bulan.',
                    'description' => 'Permintaan penarikan mutasi giro oleh ratusan cabang korporasi sekaligus menyebabkan spike CPU database 100%.',
                    'category_id' => 9, 'cause_category_id' => 3, 'radar_code' => 'O2',
                    'inherent_p' => 3, 'inherent_i' => 3, 'residual_p' => 1, 'residual_i' => 2, 'impact_amount' => 180000000,
                    'cause' => 'Kueri SQL tanpa pembatasan pagination dan scan full table pada database arsip transaksi.',
                    'existing_control' => 'Pembatasan unduhan maksimal rentang 1 bulan.',
                    'mitigation' => 'Migrasi arsip mutasi historis ke read-only data warehouse berbasis Elasticsearch/ClickHouse.'
                ],
                [
                    'title' => 'Kegagalan Enkripsi Field PII Nasabah pada Database Log Aplikasi Perbankan',
                    'target' => 'Kepatuhan 100% terhadap UU PDP dan standar enkripsi AES-256 field data pribadi nasabah.',
                    'description' => 'Debug level log yang lupa dinonaktifkan di environment production mencatat nomor kartu debit dan NIK ke disk audit.',
                    'category_id' => 11, 'cause_category_id' => 1, 'radar_code' => 'C2',
                    'inherent_p' => 4, 'inherent_i' => 5, 'residual_p' => 1, 'residual_i' => 2, 'impact_amount' => 2000000000,
                    'cause' => 'Human error developer saat merilis hotfix darurat tanpa standard pipeline configuration verification.',
                    'existing_control' => 'Code review manual sebelum merge pull request.',
                    'mitigation' => 'Implementasi data masking library otomatis pada logging framework dan secret scanner di repository.'
                ],
                [
                    'title' => 'Kegagalan Otentikasi Biometrik pada Aplikasi Mobile Teller di Kantor Kas Pembantu',
                    'target' => 'Tingkat keberhasilan verifikasi sidik jari/wajah teller di atas 99.5%.',
                    'description' => 'Inkompatibilitas driver USB fingerprint scanner pada tablet cabang baru menghambat layanan pencairan tunai.',
                    'category_id' => 9, 'cause_category_id' => 3, 'radar_code' => 'O4',
                    'inherent_p' => 3, 'inherent_i' => 2, 'residual_p' => 1, 'residual_i' => 1, 'impact_amount' => 90000000,
                    'cause' => 'Fragmentasi hardware OEM tablet tanpa standard operating environment hardware testing.',
                    'existing_control' => 'Fallback login password supervisor dengan OTP SMS.',
                    'mitigation' => 'Standarisasi procurement spesifikasi device dan implementasi centralized device management (MDM).'
                ],
                [
                    'title' => 'Ketidakcocokan Konversi Mata Uang Asing Real-Time pada Modul FX Remittance',
                    'target' => 'Selisih kurs valas otomatis nol terhadap rate treasury resmi bank.',
                    'description' => 'Cache rate feed valas yang stale 10 menit menyebabkan transaksi transfer luar negeri tereksekusi dengan kurs lama saat fluktuasi tajam.',
                    'category_id' => 5, 'cause_category_id' => 3, 'radar_code' => 'F1',
                    'inherent_p' => 3, 'inherent_i' => 4, 'residual_p' => 1, 'residual_i' => 2, 'impact_amount' => 650000000,
                    'cause' => 'Mekanisme cache TTL tidak meng-handle event-driven rate fluctuation shock threshold.',
                    'existing_control' => 'Approval manual supervisor untuk transfer valas di atas ekuivalen USD 10,000.',
                    'mitigation' => 'Implementasi real-time WebSocket rate publisher dari dealing room ke engine transaksi remitansi.'
                ],
                [
                    'title' => 'Penolakan Transaksi QRIS Merchant Akibat Format Payload QR MPAN Tidak Terstandarisasi',
                    'target' => 'Success rate transaksi pembayaran QRIS Merchant di atas 99.8%.',
                    'description' => 'Pembaruan spesifikasi tag EMVCo ASPI menimbulkan error parsing pada merchant settlement gateway.',
                    'category_id' => 8, 'cause_category_id' => 2, 'radar_code' => 'C1',
                    'inherent_p' => 4, 'inherent_i' => 3, 'residual_p' => 1, 'residual_i' => 2, 'impact_amount' => 320000000,
                    'cause' => 'Keterlambatan update parser library QRIS terhadap petunjuk teknis ASPI terbaru.',
                    'existing_control' => 'Pengujian berkala transaksi QRIS dengan merchant sampel.',
                    'mitigation' => 'Penyesuaian parsing engine sesuai spesifikasi QRIS CPM/MPM ASPI 2026 dan automated sandbox regression testing.'
                ],
                [
                    'title' => 'Hilangnya Jejak Audit Trail pada Modul Override Persetujuan Kredit Segmen Komersial',
                    'target' => '100% kelengkapan audit log immutable untuk seluruh aksi approval dan exception override.',
                    'description' => 'Bug pada event dispatcher menyebabkan aksi bypass limit persetujuan level branch manager tidak tercatat di tabel audit.',
                    'category_id' => 8, 'cause_category_id' => 2, 'radar_code' => 'C2',
                    'inherent_p' => 3, 'inherent_i' => 5, 'residual_p' => 1, 'residual_i' => 2, 'impact_amount' => 1100000000,
                    'cause' => 'Kegagalan penanganan transaksi database nested transaction saat logging async gagal terkirim.',
                    'existing_control' => 'Dokumen fisik nota persetujuan komite kredit.',
                    'mitigation' => 'Sinkronisasi audit trail transaksional wajib ACID dan forward copy langsung ke centralized SIEM audit storage.'
                ],
                [
                    'title' => 'Race Condition pada Pencairan Pinjaman Online Cepat Cair via Aplikasi Mobile',
                    'target' => 'Zero insiden double disbursement dana pencairan kredit.',
                    'description' => 'Double click tombol konfirmasi pada device nasabah dengan koneksi fluktuatif memicu pengiriman 2 kali instruksi transfer dana.',
                    'category_id' => 9, 'cause_category_id' => 3, 'radar_code' => 'O2',
                    'inherent_p' => 3, 'inherent_i' => 4, 'residual_p' => 1, 'residual_i' => 2, 'impact_amount' => 480000000,
                    'cause' => 'Ketiadaan database mutex lock dan idempotency key pada endpoint submit pencairan loan.',
                    'existing_control' => 'Validasi frontend tombol disable setelah klik pertama.',
                    'mitigation' => 'Penerapan Redis distributed locking (Redlock) dan request idempotency key unik per loan agreement ID.'
                ],
                [
                    'title' => 'Kegagalan Otomasi Perhitungan Cadangan Kerugian Penurunan Nilai (CKPN) PSAK 71',
                    'target' => 'Kalkulasi ECL (Expected Credit Loss) 100% valid sesuai staging PSAK 71 sebelum penutupan buku.',
                    'description' => 'Kesalahan mapping bucket probability of default (PD) dan loss given default (LGD) mengakibatkan under-provisioning cadangan modal.',
                    'category_id' => 5, 'cause_category_id' => 2, 'radar_code' => 'C2',
                    'inherent_p' => 3, 'inherent_i' => 5, 'residual_p' => 1, 'residual_i' => 3, 'impact_amount' => 2500000000,
                    'cause' => 'Formula model staging risk credit pada stored procedure database belum mengakomodasi restrukturisasi pasca-bencana.',
                    'existing_control' => 'Review laporan keuangan semesteran oleh kantor akuntan publik eksternal.',
                    'mitigation' => 'Validasi independen model IFRS 9/PSAK 71, automated reconciliation tools, dan integrasi data rating internal.'
                ],
                [
                    'title' => 'Inkonsistensi State Akun Virtual Account Saat Transaksi Pembayaran E-Commerce E-wallet',
                    'target' => 'SLA callback notifikasi payment VA instan di bawah 3 detik.',
                    'description' => 'Koneksi jaringan terputus saat bank mengupdate status bayar namun merchant e-commerce belum menerima callback sukses.',
                    'category_id' => 7, 'cause_category_id' => 3, 'radar_code' => 'O5',
                    'inherent_p' => 4, 'inherent_i' => 3, 'residual_p' => 2, 'residual_i' => 2, 'impact_amount' => 350000000,
                    'cause' => 'Mekanisme callback webhook tidak dilengkapi circuit breaker pattern dan exponential backoff retry.',
                    'existing_control' => 'Tool re-trigger manual status payment oleh call center.',
                    'mitigation' => 'Penerapan dead-letter queue (DLQ) Kafka dan sistem callback retry berjadwal otomatis hingga 24 jam.'
                ],
                [
                    'title' => 'Ketergantungan terhadap Komponen Library Pihak Ketiga yang Mengalami End-of-Life (EOL)',
                    'target' => 'Zero library usang atau EOL dengan CVE aktif pada dependensi aplikasi perbankan.',
                    'description' => 'Modul reporting masih mengandalkan library parsing XML jadul yang rentan terhadap XML External Entity (XXE) injection.',
                    'category_id' => 19, 'cause_category_id' => 3, 'radar_code' => 'S2',
                    'inherent_p' => 4, 'inherent_i' => 4, 'residual_p' => 1, 'residual_i' => 2, 'impact_amount' => 800000000,
                    'cause' => 'Manajemen siklus hidup dependensi software open-source belum diatur dalam gatekeeping deployment.',
                    'existing_control' => 'Scanning kerentanan tahunan oleh auditor eksternal.',
                    'mitigation' => 'Integrasi tools Software Bill of Materials (SBOM) dan automated dependency scanning (Snyk/Trivy) pada CI/CD.'
                ],
            ],

            // 2. BT - CORE BANKING SYSTEM CONVENTIONAL
            'BT - CORE BANKING SYSTEM CONVENTIONAL' => [
                [
                    'title' => 'Overrun Durasi Proses End-of-Day (EOD) Menembus Jam Buka Layanan Kantor Cabang',
                    'target' => 'Proses EOD batch selesai tuntas sebelum pukul 05:00 WIB setiap hari kalender.',
                    'description' => 'Kompilasi ledger transaksi harian jutaan rekening tabungan terhambat locking tabel customer relationship index.',
                    'category_id' => 7, 'cause_category_id' => 3, 'radar_code' => 'O2',
                    'inherent_p' => 4, 'inherent_i' => 5, 'residual_p' => 2, 'residual_i' => 3, 'impact_amount' => 1800000000,
                    'cause' => 'Volume transaksi harian melonjak 300% namun kueri stored procedure batching belum di-tuning ulang.',
                    'existing_control' => 'Monitoring manual konsol batch oleh operator DC shift malam.',
                    'mitigation' => 'Optimasi parallel batch processing, arsitektur database in-memory flash cache, dan tuning I/O throughput storage.'
                ],
                [
                    'title' => 'Deadlock Transaksi Finansial Konkruen pada Rekening Induk Pool Nasabah Institusi',
                    'target' => 'Zero deadlock error pada eksekusi transfer dana korporasi berskala masif.',
                    'description' => 'Ratusan instruksi transfer gaji karyawan dan penarikan sweep account mengeksekusi rekening induk pada milidetik yang sama.',
                    'category_id' => 9, 'cause_category_id' => 3, 'radar_code' => 'O2',
                    'inherent_p' => 4, 'inherent_i' => 4, 'residual_p' => 1, 'residual_i' => 2, 'impact_amount' => 600000000,
                    'cause' => 'Isolation level database core banking belum dioptimalkan untuk concurrent balance update non-blocking.',
                    'existing_control' => 'Automatic retry database transaction dengan delay acak.',
                    'mitigation' => 'Penerapan queue sequence ordering dan virtual sub-ledger allocation untuk rekening volume tinggi.'
                ],
                [
                    'title' => 'Ketidakseimbangan Neraca Harian Buku Besar (General Ledger Out of Balance)',
                    'target' => 'Zero selisih saldo debet-kredit pada rekonsiliasi GL core harian.',
                    'description' => 'Interupsi jaringan saat posting batch bunga pinjaman menyebabkan debet tercatat namun kredit gagal disimpan.',
                    'category_id' => 5, 'cause_category_id' => 2, 'radar_code' => 'O4',
                    'inherent_p' => 3, 'inherent_i' => 5, 'residual_p' => 1, 'residual_i' => 2, 'impact_amount' => 2200000000,
                    'cause' => 'Bug pada transaksi atomik distributed two-phase commit antara modul pinjaman dan engine GL.',
                    'existing_control' => 'Pemeriksaan laporan selisih GL oleh tim Accounting setiap pagi.',
                    'mitigation' => 'Penguatan transaksi atomik ACID ganda dan automated reconciliation script sebelum finalisasi penutupan buku.'
                ],
                [
                    'title' => 'Kegagalan Otomasi Failover High Availability Cluster Node Core Banking ke Backup Node',
                    'target' => 'RTO failover database core banking di bawah 5 menit dengan RPO 0 detik.',
                    'description' => 'Crash pada node master tidak langsung men-trigger failover otomatis karena split-brain protection voting timeout.',
                    'category_id' => 9, 'cause_category_id' => 3, 'radar_code' => 'O2',
                    'inherent_p' => 3, 'inherent_i' => 5, 'residual_p' => 1, 'residual_i' => 3, 'impact_amount' => 3000000000,
                    'cause' => 'Heartbeat network latency antar rak server melebihi threshold deteksi failover quorum cluster.',
                    'existing_control' => 'Prosedur manual promote secondary database node oleh tim DBA.',
                    'mitigation' => 'Peningkatan dedicated low-latency cluster interconnect, penyesuaian quorum witness node, dan drill berkala.'
                ],
                [
                    'title' => 'Duplikasi Nomor Identitas Nasabah Tunggal (Single Customer Information File / CIF)',
                    'target' => 'Zero data redundancy CIF nasabah di seluruh jaringan perbankan.',
                    'description' => 'Pembukaan rekening secara online dan di cabang secara simultan menghasilkan 2 nomor CIF berbeda untuk 1 NIK.',
                    'category_id' => 11, 'cause_category_id' => 2, 'radar_code' => 'C2',
                    'inherent_p' => 4, 'inherent_i' => 4, 'residual_p' => 1, 'residual_i' => 2, 'impact_amount' => 450000000,
                    'cause' => 'Deduplication validation check tidak menerapkan database lock saat proses validasi NIK Dukcapil.',
                    'existing_control' => 'Laporan pembersihan anomali CIF ganda mingguan oleh data quality team.',
                    'mitigation' => 'Penerapan unique constraint strict pada tingkat database core dan integrasi centralized Customer Master Data Management.'
                ],
                [
                    'title' => 'Exhaustion Kapasitas Tablespace Database Transaksi Core Banking',
                    'target' => 'Ketersediaan sisa kapasitas storage tablespace core banking selalu di atas 35%.',
                    'description' => 'Pertumbuhan data tabel audit log dan histori mutasi rekening mengisi storage SAN melebihi proyeksi kapasitas.',
                    'category_id' => 9, 'cause_category_id' => 2, 'radar_code' => 'O2',
                    'inherent_p' => 3, 'inherent_i' => 5, 'residual_p' => 1, 'residual_i' => 2, 'impact_amount' => 1500000000,
                    'cause' => 'Data archiving policy belum otomatis memindahkan partisi data transaksi berusia di atas 3 tahun.',
                    'existing_control' => 'Monitoring kapasitas storage mingguan secara manual.',
                    'mitigation' => 'Implementasi automated table partitioning, daily alert kapasitas threshold 80%, dan cold data archiving pipeline.'
                ],
                [
                    'title' => 'Kegagalan Parsing Format Pesan Finansial ISO 20022 pada Antarmuka SWIFT / BI-FAST',
                    'target' => '100% kepatuhan format message messaging interbank ISO 20022 tanpa rejection tag.',
                    'description' => 'Karakter khusus pada nama benefisiari nasabah asing memicu unhandled rejection pada gateway pembayaran internasional.',
                    'category_id' => 8, 'cause_category_id' => 3, 'radar_code' => 'C1',
                    'inherent_p' => 3, 'inherent_i' => 4, 'residual_p' => 1, 'residual_i' => 2, 'impact_amount' => 700000000,
                    'cause' => 'Validator input data di level core belum sepenuhnya kompatibel dengan UTF-8 sanitization standar SWIFT MX.',
                    'existing_control' => 'Repair queue manual oleh operator remitansi kantor pusat.',
                    'mitigation' => 'Upgrade modul parser ISO 20022 core banking dengan automated schema validation dan pre-clearing sanitization.'
                ],
                [
                    'title' => 'Human Error Modifikasi Langsung (Direct Database Patching) pada Tabel Konfigurasi Akun',
                    'target' => 'Zero unauthorized direct database changes di environment core banking production.',
                    'description' => 'DBA mengeksekusi script update manual tanpa WHERE clause lengkap saat menangani tiket perbaikan saldo akun.',
                    'category_id' => 10, 'cause_category_id' => 1, 'radar_code' => 'C2',
                    'inherent_p' => 3, 'inherent_i' => 5, 'residual_p' => 1, 'residual_i' => 2, 'impact_amount' => 2500000000,
                    'cause' => 'Prosedur Privileged Access Management (PAM) belum memblokir akses direct update DML non-aplikasi.',
                    'existing_control' => 'Persetujuan Change Request (CR) dan log audit database session.',
                    'mitigation' => 'Penerapan Database Activity Monitoring (DAM) real-time blocking, four-eyes approval PAM, dan larangan DML langsung.'
                ],
                [
                    'title' => 'Keterlambatan Eksekusi Standing Order Pembayaran Angsuran Kredit Massal',
                    'target' => 'Eksekusi autodebet angsuran kredit tepat tanggal jatuh tempo tanpa denda keterlambatan.',
                    'description' => 'Ribuan akun debet pinjaman tidak terpotong otomatis karena thread scheduler autodebet crash tidak terdeteksi.',
                    'category_id' => 7, 'cause_category_id' => 3, 'radar_code' => 'O5',
                    'inherent_p' => 4, 'inherent_i' => 3, 'residual_p' => 1, 'residual_i' => 2, 'impact_amount' => 380000000,
                    'cause' => 'Uncaught exception pada nomor rekening tujuan yang terblokir menyebabkan seluruh loop scheduler terhenti.',
                    'existing_control' => 'Pengaduan nasabah yang kaget terkena denda keterlambatan.',
                    'mitigation' => 'Refactoring scheduler job dengan isolasi per-rekening, automated alerting error thread, dan waiver denda otomatis.'
                ],
                [
                    'title' => 'Kebocoran Data Finansial Sensitif Saat Export Database Dump untuk UAT Development',
                    'target' => '100% masking data PII dan saldo rekening pada seluruh data dump non-production.',
                    'description' => 'File backup database core disalin ke server staging tanpa proses data anonymization dan masking.',
                    'category_id' => 11, 'cause_category_id' => 1, 'radar_code' => 'C2',
                    'inherent_p' => 4, 'inherent_i' => 5, 'residual_p' => 1, 'residual_i' => 2, 'impact_amount' => 3500000000,
                    'cause' => 'Ketiadaan automated dynamic data masking pipeline dalam prosedur penyediaan environment testing.',
                    'existing_control' => 'Pernyataan NDA tertulis dari tim vendor programmer.',
                    'mitigation' => 'Implementasi automated data masking tool (synthetic test data generator) dan isolasi ketat akses database production.'
                ],
                [
                    'title' => 'Anomali Perhitungan Bunga Simpanan Berbunga Harian pada Akun Tiering Multi-Saldo',
                    'target' => 'Deviasi perhitungan bunga tabungan bernilai nol rupiah di setiap akhir bulan.',
                    'description' => 'Perubahan saldo intraday dengan frekuensi tinggi menimbulkan pembulatan desimal yang tidak konsisten pada interest ledger.',
                    'category_id' => 5, 'cause_category_id' => 2, 'radar_code' => 'F1',
                    'inherent_p' => 3, 'inherent_i' => 4, 'residual_p' => 1, 'residual_i' => 2, 'impact_amount' => 520000000,
                    'cause' => 'Tipe data floating point digunakan alih-alih fixed precision decimal pada core math module.',
                    'existing_control' => 'Koreksi manual pembukuan penyesuaian bunga (ad-hoc adjustment voucher).',
                    'mitigation' => 'Standardisasi kalkulasi tipe data numeric/decimal 18,4 dan automated math regression test suite.'
                ],
                [
                    'title' => 'Kegagalan Verifikasi Status Blokir Rekening Permintaan Penegak Hukum / PPATK',
                    'target' => 'Pemblokiran rekening mencurigakan tuntas dalam waktu maksimal 15 menit pasca instruksi resmi.',
                    'description' => 'Rekening nasabah yang masuk daftar blokir tindak pidana pencucian uang masih sempat melakukan penarikan via transfer.',
                    'category_id' => 8, 'cause_category_id' => 2, 'radar_code' => 'C2',
                    'inherent_p' => 3, 'inherent_i' => 5, 'residual_p' => 1, 'residual_i' => 2, 'impact_amount' => 1900000000,
                    'cause' => 'Flag pembekuan rekening hanya mengunci channel teller cabang namun tidak memblokir channel mobile dan ATM.',
                    'existing_control' => 'Surat edaran pemblokiran manual ke cabang terkait.',
                    'mitigation' => 'Sinkronisasi instan global freeze flag di seluruh kanal perbankan (omnichannel lock) saat update CIF di core.'
                ],
                [
                    'title' => 'Degradasi Performa Akses Layanan Teller Akibat Indeks Database Core Terfragmentasi',
                    'target' => 'Waktu loading pencarian rekening teller cabang di bawah 2 detik pada jam puncak.',
                    'description' => 'Tingkat fragmentasi indeks tabel transaksi melampaui 45% menyebabkan latensi tinggi pada antrean teller cabang.',
                    'category_id' => 9, 'cause_category_id' => 2, 'radar_code' => 'O2',
                    'inherent_p' => 4, 'inherent_i' => 3, 'residual_p' => 1, 'residual_i' => 2, 'impact_amount' => 280000000,
                    'cause' => 'Pemeliharaan indeks database (index rebuild/reorganize) tidak terjadwal secara periodik mingguan.',
                    'existing_control' => 'Eskalasi komplain keluhan teller saat antrean nasabah menumpuk.',
                    'mitigation' => 'Otomasi automated online index maintenance weekly maintenance window dan monitoring query cost planner.'
                ],
                [
                    'title' => 'Inkonsistensi Saldo Tabungan Antar Modul Core Banking Pasca Rollback Transaksi Gagal',
                    'target' => 'Konsistensi 100% saldo riil vs saldo efektif buku tabungan nasabah.',
                    'description' => 'Saat koneksi internet terputus di tengah settlement kliring, saldo riil terpotong namun saldo efektif belum ter-unblock.',
                    'category_id' => 7, 'cause_category_id' => 3, 'radar_code' => 'O4',
                    'inherent_p' => 3, 'inherent_i' => 4, 'residual_p' => 1, 'residual_i' => 2, 'impact_amount' => 420000000,
                    'cause' => 'Mekanisme compensation logic pada microservices workflow belum mencakup release hold balance timeout.',
                    'existing_control' => 'Pencocokan saldo nasabah saat komplain call center masuk.',
                    'mitigation' => 'Implementasi saga orchestration pattern dengan jaminan compensation transaction jika langkah settlement gagal.'
                ],
                [
                    'title' => 'Kegagalan Replikasi Data Sinkron ke Disaster Recovery Center (DRC) Jarak Jauh',
                    'target' => 'Data replication lag antara Main DC dan DRC selalu di bawah 10 detik.',
                    'description' => 'Penurunan bandwidth fiber optik antar data center menyebabkan delay replikasi transaksi core banking hingga 45 menit.',
                    'category_id' => 9, 'cause_category_id' => 3, 'radar_code' => 'O2',
                    'inherent_p' => 3, 'inherent_i' => 5, 'residual_p' => 1, 'residual_i' => 2, 'impact_amount' => 2000000000,
                    'cause' => 'Gangguan rute jaringan kabel laut ISP penyedia leased line inter-DC tanpa proteksi otomatis.',
                    'existing_control' => 'Monitoring alert SNMP bandwidth link replikasi.',
                    'mitigation' => 'Penyediaan redundant diverse path leased line ISP berbeda dan kompresi protokol replikasi database.'
                ],
                [
                    'title' => 'Ketidakmampuan Core Banking Menangani Perubahan Aturan Batas Minimal Saldo Mengendap',
                    'target' => 'Fleksibilitas konfigurasi parameter minimum balance tanpa downtime sistem.',
                    'description' => 'Instruksi perubahan batas saldo minimal tabungan memerlukan re-deploy seluruh kode modul core alih-alih ubah parameter.',
                    'category_id' => 8, 'cause_category_id' => 2, 'radar_code' => 'S3',
                    'inherent_p' => 3, 'inherent_i' => 3, 'residual_p' => 1, 'residual_i' => 1, 'impact_amount' => 150000000,
                    'cause' => 'Hardcoded logic parameter produk perbankan pada legacy source code aplikasi.',
                    'existing_control' => 'Penerbitan release hotfix darurat dengan masa testing minim.',
                    'mitigation' => 'Refactoring logic core banking berbasis dynamic rule parameter engine yang dikelola terpusat via GUI parameter.'
                ],
                [
                    'title' => 'Anomali Perhitungan Pajak Bunga Deposito (PPh Pasal 4 Ayat 2) bagi Nasabah Non-Residen',
                    'target' => 'Perhitungan withholding tax bunga simpanan nasabah asing 100% akurat sesuai Tax Treaty.',
                    'description' => 'Aplikasi memotong tarif flat 20% padahal nasabah memiliki Surat Keterangan Domisili (DGT) dengan tarif tax treaty 10%.',
                    'category_id' => 8, 'cause_category_id' => 2, 'radar_code' => 'C2',
                    'inherent_p' => 3, 'inherent_i' => 4, 'residual_p' => 1, 'residual_i' => 2, 'impact_amount' => 390000000,
                    'cause' => 'Parameter Tax Treaty rate tidak terhubung otomatis dengan tanggal kedaluwarsa formulir DGT nasabah.',
                    'existing_control' => 'Restitusi pajak manual melalui pengajuan nota koreksi ke kantor pajak.',
                    'mitigation' => 'Pembaruan modul tax core banking dengan automated DGT validity verification dan alert renewal berkala.'
                ],
                [
                    'title' => 'Ketergantungan pada Bahasa Pemrograman Legacy (COBOL/RPG) dan Kelangkaan SDM Ahli',
                    'target' => 'Kesiapan dokumentasi dan transisi arsitektur legacy core banking menuju modern tech stack.',
                    'description' => 'Pensiunnya programmer senior legacy core banking menyisakan kendala pemeliharaan modul kliring cek kuno.',
                    'category_id' => 14, 'cause_category_id' => 1, 'radar_code' => 'O6',
                    'inherent_p' => 4, 'inherent_i' => 4, 'residual_p' => 2, 'residual_i' => 3, 'impact_amount' => 1200000000,
                    'cause' => 'Dokumentasi source code modul legacy tidak diperbarui dan ketiadaan transfer knowledge terstruktur.',
                    'existing_control' => 'Mempekerjakan konsultan eksternal dengan biaya retain mahal.',
                    'mitigation' => 'Program reverse engineering dokumentasi kode legacy dan percepatan migrasi bertahap ke microservices open stack.'
                ],
                [
                    'title' => 'Kegagalan Eksekusi Transaksi Koreksi Nota Debet/Kredit pada Pembukuan Akhir Bulan',
                    'target' => 'Proses koreksi pembukuan (adjustment entry) tuntas dan ter-audit sebelum tanggal 1 bulan berikutnya.',
                    'description' => 'Tiket koreksi kesalahan teller yang disetujui supervisor ditolak oleh engine karena locked accounting period.',
                    'category_id' => 7, 'cause_category_id' => 2, 'radar_code' => 'O4',
                    'inherent_p' => 3, 'inherent_i' => 3, 'residual_p' => 1, 'residual_i' => 2, 'impact_amount' => 210000000,
                    'cause' => 'Alur kerja otorisasi exception handling tidak menyediakan grace period pembukuan pasca-cut off.',
                    'existing_control' => 'Jurnal penyesuaian manual di periode bulan berikutnya yang menimbulkan catatan audit.',
                    'mitigation' => 'Penerapan modul special adjustment authorization dengan dual biometric confirmation dan immutable reason tagging.'
                ],
                [
                    'title' => 'Kerentanan Keamanan pada Interface Komunikasi Terminal Legacy Teller (Emulasi 3270)',
                    'target' => '100% enkripsi TLS modern pada seluruh jalur transmisi data terminal teller cabang.',
                    'description' => 'Jalur komunikasi antara workstation teller di remote branch dan mainframe host menggunakan protokol unencrypted telnet.',
                    'category_id' => 19, 'cause_category_id' => 3, 'radar_code' => 'O3',
                    'inherent_p' => 4, 'inherent_i' => 5, 'residual_p' => 1, 'residual_i' => 2, 'impact_amount' => 1800000000,
                    'cause' => 'Aplikasi emulasi terminal teller warisan belum di-upgrade ke protokol secure terminal over TLS 1.3.',
                    'existing_control' => 'Jaringan tertutup IP-VPN MPLS perbankan.',
                    'mitigation' => 'Modernisasi aplikasi front-end teller berbasis web browser modern dengan enkripsi end-to-end mTLS.'
                ],
            ],

            // 3. BT - CORE BANKING SYSTEM SYARIAH
            'BT - CORE BANKING SYSTEM SYARIAH' => [
                [
                    'title' => 'Deviasi Perhitungan Bagi Hasil Deposito Mudharabah Mutlaqah pada Periode Fluktuasi Pendapatan',
                    'target' => 'Perhitungan porsi nisbah bagi hasil Mudharabah 100% akurat sesuai fatwa DSN-MUI.',
                    'description' => 'Kesalahan penentuan bobot ekuivalen rate pendapatan pembiayaan menghasilkan kelebihan bayar porsi bank kepada nasabah.',
                    'category_id' => 5, 'cause_category_id' => 2, 'radar_code' => 'C2',
                    'inherent_p' => 4, 'inherent_i' => 4, 'residual_p' => 1, 'residual_i' => 3, 'impact_amount' => 850000000,
                    'cause' => 'Parameter revenue sharing pool pada core syariah belum memisahkan pendapatan non-halal dari pool distribusi bagi hasil.',
                    'existing_control' => 'Review sampel oleh Dewan Pengawas Syariah (DPS) internal setiap akhir bulan.',
                    'mitigation' => 'Otomasi pemisahan pendapatan non-halal langsung ke rekening dana kebajikan dan audit formula bagi hasil berkala.'
                ],
                [
                    'title' => 'Pengakuan Pendapatan Margin Murabahah Tidak Sesuai PSAK 102 Akibat Pelunasan Dini',
                    'target' => 'Pemberian potongan pelunasan dini (muqashah) 100% transparan dan sesuai prinsip syariah.',
                    'description' => 'Nasabah yang melunasi KPR syariah sebelum jatuh tempo dikenai pemotongan margin yang dihitung secara formula konvensional.',
                    'category_id' => 8, 'cause_category_id' => 2, 'radar_code' => 'C1',
                    'inherent_p' => 4, 'inherent_i' => 4, 'residual_p' => 1, 'residual_i' => 2, 'impact_amount' => 620000000,
                    'cause' => 'Modul amortisasi margin pada core banking belum menerapkan formula proporsionalitas muqashah fatwa DSN.',
                    'existing_control' => 'Pengecekan nota kalkulasi pelunasan oleh financing operation officer.',
                    'mitigation' => 'Standardisasi formula muqashah otomatis pada core syariah sesuai pedoman DSN-MUI dan OJK.'
                ],
                [
                    'title' => 'Keterlambatan Penyaluran Dana Kebajikan (Qardhul Hasan / Dana Sosial) ke Lembaga Resmi',
                    'target' => 'Penyaluran dana non-halal dan denda (ta\'zir) tuntas maksimal 30 hari kalender setelah akhir kuartal.',
                    'description' => 'Akumulasi denda keterlambatan nasabah tertahan di rekening escrow internal bank tanpa laporan penyaluran publik.',
                    'category_id' => 8, 'cause_category_id' => 2, 'radar_code' => 'C2',
                    'inherent_p' => 3, 'inherent_i' => 4, 'residual_p' => 1, 'residual_i' => 2, 'impact_amount' => 450000000,
                    'cause' => 'Belum adanya modul otomatis reporting dan transfer dana kebajikan ke lembaga amil zakat terakreditasi.',
                    'existing_control' => 'Laporan manual pertanggungjawaban sosial pada annual report.',
                    'mitigation' => 'Implementasi sub-ledger khusus dana kebajikan dengan auto-transfer terjadwal dan dashboard transparansi publik.'
                ],
                [
                    'title' => 'Anomali Status Kepemilikan Aset Underlying pada Akad Ijarah Muntahiyah Bittamlik (IMBT)',
                    'target' => '100% kelengkapan dokumentasi dan legalitas transfer kepemilikan aset IMBT di akhir periode.',
                    'description' => 'Sistem gagal mengeksekusi akad hibah/penjualan aset sewa mobil di akhir tenor pembiayaan sehingga status kepemilikan gantung.',
                    'category_id' => 16, 'cause_category_id' => 2, 'radar_code' => 'C1',
                    'inherent_p' => 3, 'inherent_i' => 4, 'residual_p' => 1, 'residual_i' => 2, 'impact_amount' => 550000000,
                    'cause' => 'Core banking tidak memiliki workflow tracking milestone penyerahan hak milik secara digital.',
                    'existing_control' => 'Pemberkasan manual dokumen BPKB/sertifikat oleh legal financing.',
                    'mitigation' => 'Pengembangan fitur IMBT lifecycle management dengan alert pelepasan hak milik otomatis saat cicilan lunas.'
                ],
                [
                    'title' => 'Pemberian Bonus Wadiah Giro Syariah Tanpa Persetujuan Manajemen dan Dewan Pengawas',
                    'target' => 'Zero indikasi komitmen imbalan di awal (riba) pada produk simpanan Wadiah Yad Dhomanah.',
                    'description' => 'Sales relationship manager menjanjikan nominal bonus tetap setiap bulan kepada nasabah korporasi giro syariah.',
                    'category_id' => 8, 'cause_category_id' => 1, 'radar_code' => 'C2',
                    'inherent_p' => 4, 'inherent_i' => 5, 'residual_p' => 1, 'residual_i' => 2, 'impact_amount' => 1400000000,
                    'cause' => 'Sistem mengizinkan input persentase bonus wadiah tetap di level cabang tanpa approval DPS.',
                    'existing_control' => 'Pemeriksaan audit internal tahunan terhadap rekening korporasi.',
                    'mitigation' => 'Lock down fitur penentuan bonus wadiah pada core banking menjadi wewenang eksklusif ALCO & persetujuan DPS.'
                ],
                [
                    'title' => 'Kegagalan Validasi Akad Wakalah pada Pencairan Pembiayaan Multijasa Perjalanan Umrah',
                    'target' => 'Zero transaksi pembiayaan syariah cair tanpa akad wakalah/ijarah yang terikat sah.',
                    'description' => 'Dana pembiayaan ditransfer langsung ke rekening pribadi debitur alih-alih ke biro travel yang ditunjuk.',
                    'category_id' => 8, 'cause_category_id' => 2, 'radar_code' => 'C1',
                    'inherent_p' => 4, 'inherent_i' => 4, 'residual_p' => 1, 'residual_i' => 2, 'impact_amount' => 780000000,
                    'cause' => 'Alur disbursement pada core banking tidak mengunci rekening tujuan transfer wajib rekening merchant rekanan.',
                    'existing_control' => 'Validasi manual oleh financing approval officer.',
                    'mitigation' => 'Hard validation pada core system bahwa pencairan akad wakalah hanya dapat ditujukan ke rekening rekanan terverifikasi.'
                ],
                [
                    'title' => 'Kesalahan Perhitungan Denda Keterlambatan (Ta\'zir) Menyerupai Bunga Majemuk Bunga Berbunga',
                    'target' => 'Ta\'zir dihitung flat murni berdasarkan biaya riil penagihan tanpa akumulasi bergulir.',
                    'description' => 'Bug pada batching malam menghitung denda keterlambatan atas total saldo tertunggak termasuk denda bulan sebelumnya.',
                    'category_id' => 8, 'cause_category_id' => 3, 'radar_code' => 'C2',
                    'inherent_p' => 3, 'inherent_i' => 5, 'residual_p' => 1, 'residual_i' => 2, 'impact_amount' => 1250000000,
                    'cause' => 'Algoritma denda pada engine core mengadopsi library modul konvensional tanpa modifikasi syariah.',
                    'existing_control' => 'Koreksi manual saat nasabah menyampaikan komplain keberatan.',
                    'mitigation' => 'Refactoring total script perhitungan ta\'zir murni sesuai regulasi OJK perbankan syariah.'
                ],
                [
                    'title' => 'Keterlambatan Penerbitan Sertifikat Kepemilikan Sukuk Ritel pada Nasabah Pemilik Rekening',
                    'target' => 'Penerbitan konfirmasi kepemilikan sukuk real-time pasca settlement Kemenkeu.',
                    'description' => 'Data integrasi antara sub-registry KSEI dan core banking syariah terputus saat periode penutupan penawaran sukuk.',
                    'category_id' => 7, 'cause_category_id' => 3, 'radar_code' => 'O2',
                    'inherent_p' => 3, 'inherent_i' => 3, 'residual_p' => 1, 'residual_i' => 2, 'impact_amount' => 290000000,
                    'cause' => 'File parser data settlement KSEI mengalami failure pada karakter tanda baca identitas nasabah.',
                    'existing_control' => 'Rekonsiliasi manual H+2 oleh divisi wealth management syariah.',
                    'mitigation' => 'Penerapan API direct integration real-time dengan KSEI/Kemenkeu dan automated fallback parser.'
                ],
                [
                    'title' => 'Anomali Pembagian Bagi Hasil Musyarakah Mutanaqisah (MMQ) pada Restrukturisasi Pembiayaan',
                    'target' => 'Perhitungan penurunan porsi kepemilikan bank (hissah) 100% konsisten dengan jadwal restrukturisasi.',
                    'description' => 'Saat jangka waktu pembiayaan diperpanjang, rasio hissah bank tidak terupdate sehingga cicilan nasabah tidak mengurangi pokok kepemilikan.',
                    'category_id' => 5, 'cause_category_id' => 2, 'radar_code' => 'F1',
                    'inherent_p' => 3, 'inherent_i' => 4, 'residual_p' => 1, 'residual_i' => 2, 'impact_amount' => 640000000,
                    'cause' => 'Modul restrukturisasi kredit syariah belum mengakomodasi simulasi dinamis hissah capital sharing MMQ.',
                    'existing_control' => 'Pencatatan manual di spreadsheet operasional cabang komersial.',
                    'mitigation' => 'Otomasi simulasi MMQ rescheduling pada core system dan verifikasi berkala tim risk syariah.'
                ],
                [
                    'title' => 'Kegagalan Pelaporan Rasio Pemenuhan Syariah (Sharia Governance Ratio) ke OJK',
                    'target' => '100% ketepatan waktu pelaporan kepatuhan syariah berkala ke regulator OJK.',
                    'description' => 'Data rasio pembiayaan sektor halal dan portofolio hijau tidak dapat ditarik dari core syariah secara otomatis.',
                    'category_id' => 8, 'cause_category_id' => 2, 'radar_code' => 'C1',
                    'inherent_p' => 3, 'inherent_i' => 4, 'residual_p' => 1, 'residual_i' => 2, 'impact_amount' => 500000000,
                    'cause' => 'Field sektor ekonomi pada master data nasabah tidak memiliki mapping kode klasifikasi halal OJK.',
                    'existing_control' => 'Kompilasi manual data cabang menjelang batas akhir pelaporan.',
                    'mitigation' => 'Standardisasi field mandatory klasifikasi industri syariah pada saat pembukaan fasilitas pembiayaan.'
                ],
                [
                    'title' => 'Risiko Reputasi Akibat Kesalahan Framing Pernyataan Fatwa Produk Pembiayaan di Media Sosial',
                    'target' => 'Zero sengketa opini publik terkait keabsahan syariah produk bank.',
                    'description' => 'Materi promosi pembiayaan emas syariah dipublikasikan tanpa mencantumkan nomor fatwa DSN-MUI dan akad yang jelas.',
                    'category_id' => 6, 'cause_category_id' => 1, 'radar_code' => 'S1',
                    'inherent_p' => 4, 'inherent_i' => 4, 'residual_p' => 1, 'residual_i' => 2, 'impact_amount' => 750000000,
                    'cause' => 'Materi marketing digital tidak melalui proses gate review persetujuan Dewan Pengawas Syariah.',
                    'existing_control' => 'Klarifikasi media jika muncul pertanyaan dari komunitas syariah.',
                    'mitigation' => 'Pemberlakuan mandatory SLA review DPS untuk seluruh materi komunikasi publik dan edukasi literasi syariah.'
                ],
                [
                    'title' => 'Kesalahan Konversi Akad saat Migrasi Rekening Nasabah Konvensional ke Syariah',
                    'target' => '100% persetujuan eksplisit nasabah pada akad baru saat konversi cabang syariah.',
                    'description' => 'Data rekening nasabah terkonversi otomatis ke akad Wadiah tanpa bukti akad tertulis atau verifikasi digital yang sah.',
                    'category_id' => 16, 'cause_category_id' => 2, 'radar_code' => 'C1',
                    'inherent_p' => 4, 'inherent_i' => 5, 'residual_p' => 1, 'residual_i' => 2, 'impact_amount' => 1600000000,
                    'cause' => 'Prosedur bulk migration database mengecualikan langkah consent verifikasi digital nasabah.',
                    'existing_control' => 'Pemberian batas waktu sanggahan nasabah 30 hari.',
                    'mitigation' => 'Penyediaan modul persetujuan e-akad digital via mobile banking dengan rekam jejak digital signature tersertifikasi.'
                ],
                [
                    'title' => 'Anomali Perhitungan Dana Bagi Hasil Nasabah Simpanan Valas Syariah (USD Mudharabah)',
                    'target' => 'Akurasi pembagian hasil deposito valas syariah sesuai real yield aset pembiayaan valas.',
                    'description' => 'Pendapatan valas yang dikonversi ke IDR menghasilkan distorsi yield saat fluktuasi tajam kurs mata uang.',
                    'category_id' => 5, 'cause_category_id' => 2, 'radar_code' => 'F1',
                    'inherent_p' => 3, 'inherent_i' => 4, 'residual_p' => 1, 'residual_i' => 2, 'impact_amount' => 580000000,
                    'cause' => 'Pooling dana valas digabungkan dengan pooling IDR sebelum proses alokasi pendapatan pembiayaan.',
                    'existing_control' => 'Pemisahan laporan rekening koran valas oleh finance departemen.',
                    'mitigation' => 'Implementasi segregated asset-liability currency pooling pada engine core banking syariah.'
                ],
                [
                    'title' => 'Keterlambatan Pengecekan Negatif List Terorisme dan Saldo Rekening Lembaga Filantropi',
                    'target' => 'Screening otomatis AML/CFT real-time terhadap seluruh rekening organisasi nirlaba & zakat.',
                    'description' => 'Rekening yayasan keagamaan menerima transfer anonim dari luar negeri tanpa auto-freeze alert oleh sistem.',
                    'category_id' => 8, 'cause_category_id' => 2, 'radar_code' => 'C2',
                    'inherent_p' => 4, 'inherent_i' => 5, 'residual_p' => 1, 'residual_i' => 2, 'impact_amount' => 2200000000,
                    'cause' => 'Threshold alert AML transaksi incoming remittance NGO terlalu tinggi pada parameter rule engine.',
                    'existing_control' => 'Review transaksi mencurigakan (LTKM) berkala setiap minggu.',
                    'mitigation' => 'Penyesuaian profil risiko high-risk customer NGO dan penerapan real-time sanction screening tool.'
                ],
                [
                    'title' => 'Kegagalan Otomasi Perhitungan Fee Based Income (Ujrah) pada Kartu Pembiayaan Syariah',
                    'target' => 'Zero pengenaan denda bunga bergulir pada pemegang kartu pembiayaan syariah.',
                    'description' => 'Biaya iuran tahunan dan ujrah penarikan tunai terhitung melampaui batasan kafalah yang diizinkan fatwa DSN.',
                    'category_id' => 8, 'cause_category_id' => 2, 'radar_code' => 'C1',
                    'inherent_p' => 3, 'inherent_i' => 4, 'residual_p' => 1, 'residual_i' => 2, 'impact_amount' => 480000000,
                    'cause' => 'Sistem billing kartu syariah mengadopsi core card processor konvensional yang belum disesuaikan limit ujrahnya.',
                    'existing_control' => 'Kompensasi pembebasan iuran kartu secara ad-hoc saat nasabah komplain.',
                    'mitigation' => 'Konfigurasi ulang limit kafalah & ujrah pada engine card processor sesuai parameter fatwa kartu syariah.'
                ],
                [
                    'title' => 'Ketidaklengkapan Arsip Digital Dokumen Akad Murabahah pada Audit Kepatuhan Pembiayaan',
                    'target' => '100% kelengkapan dan keabsahan berkas digital akad pembiayaan tersimpan di database.',
                    'description' => 'Pemeriksaan kepatuhan menemukan 15% berkas akad pembiayaan mikro syariah tidak memiliki lampiran tanda tangan digital nasabah.',
                    'category_id' => 16, 'cause_category_id' => 1, 'radar_code' => 'C2',
                    'inherent_p' => 4, 'inherent_i' => 4, 'residual_p' => 1, 'residual_i' => 2, 'impact_amount' => 690000000,
                    'cause' => 'Tenaga pemasar lapangan mengupload foto dokumen buram tanpa validasi automated OCR dokumen.',
                    'existing_control' => 'Supervisi berkala oleh field auditor cabang.',
                    'mitigation' => 'Implementasi mandatory digital signature terverifikasi dan automated quality gate OCR sebelum disbursement dana.'
                ],
                [
                    'title' => 'Pencatatan Aset Sitaan Pembiayaan Macet (AYDA) Melebihi Batas Waktu 1 Tahun Tanpa Pelepasan',
                    'target' => 'Penyelesaian aset jaminan AYDA syariah tuntas sesuai batas waktu OJK.',
                    'description' => 'Aset properti jaminan nasabah macet yang diambil alih bank terbengkalai tanpa proses lelang hingga melanggar ketentuan OJK.',
                    'category_id' => 8, 'cause_category_id' => 2, 'radar_code' => 'C1',
                    'inherent_p' => 3, 'inherent_i' => 4, 'residual_p' => 1, 'residual_i' => 2, 'impact_amount' => 950000000,
                    'cause' => 'Tidak adanya modul inventory tracking AYDA pada core system untuk memonitor countdown batas waktu pelepasan.',
                    'existing_control' => 'Pencatatan manual di unit remedial dan recovery.',
                    'mitigation' => 'Pengembangan modul tracking AYDA dengan alert otomatis 6 bulan, 3 bulan, dan 1 bulan sebelum jatuh tempo limit OJK.'
                ],
                [
                    'title' => 'Anomali Perhitungan Bagi Hasil Deposito Mudharabah Muqayyadah (Executing / Channeling)',
                    'target' => 'Distribusi hasil investasi khusus 100% dialokasikan murni ke proyek spesifik yang ditunjuk nasabah.',
                    'description' => 'Pendapatan dari proyek infrastruktur spesifik terdistribusi secara keliru ke nasabah tabungan reguler bank.',
                    'category_id' => 5, 'cause_category_id' => 2, 'radar_code' => 'F1',
                    'inherent_p' => 3, 'inherent_i' => 4, 'residual_p' => 1, 'residual_i' => 2, 'impact_amount' => 820000000,
                    'cause' => 'Ketiadaan fitur restricted investment ring-fencing pada core banking syariah.',
                    'existing_control' => 'Pemisahan pembukuan secara manual di level akuntansi pusat.',
                    'mitigation' => 'Implementasi modul Mudharabah Muqayyadah ring-fenced ledger dengan dedicated account tagging.'
                ],
                [
                    'title' => 'Keterbatasan Integrasi Core Syariah dengan Platform ZISWAF dan BAZNAS Nasional',
                    'target' => 'SLA penerbitan bukti setor zakat resmi (BSZ) di bawah 5 detik pasca autodebet.',
                    'description' => 'Nasabah yang membayar zakat penghasilan via auto-debet tidak memperoleh Bukti Setor Zakat untuk pengurang pajak PPh 21.',
                    'category_id' => 7, 'cause_category_id' => 3, 'radar_code' => 'O5',
                    'inherent_p' => 3, 'inherent_i' => 2, 'residual_p' => 1, 'residual_i' => 1, 'impact_amount' => 120000000,
                    'cause' => 'Ketiadaan konektivitas API langsung antara sistem bank dan sistem integrasi SiMBA BAZNAS.',
                    'existing_control' => 'Pengiriman bukti potong fisik melalui pos/email dalam waktu 14 hari kerja.',
                    'mitigation' => 'Integrasi API real-time dengan BAZNAS untuk automated digital BSZ download langsung dari mobile banking.'
                ],
                [
                    'title' => 'Kegagalan Otomasi Konversi Saldo Dormant Account Menjadi Titipan Dana Sosial',
                    'target' => 'Zero saldo rekening dormant syariah yang terdegradasi tanpa perlakuan syariah yang benar.',
                    'description' => 'Biaya administrasi bulanan terus memotong saldo rekening tabungan syariah pasif hingga bersaldo minus.',
                    'category_id' => 8, 'cause_category_id' => 2, 'radar_code' => 'C1',
                    'inherent_p' => 3, 'inherent_i' => 3, 'residual_p' => 1, 'residual_i' => 1, 'impact_amount' => 180000000,
                    'cause' => 'Logic pemotongan biaya administrasi tidak menghentikan debit saat saldo tabungan menyentuh limit nol.',
                    'existing_control' => 'Penutupan rekening otomatis oleh sistem setelah 6 bulan saldo nol.',
                    'mitigation' => 'Pengaturan parameter strict non-negative balance pada rekening syariah dan auto-status dormant tanpa potongan zalim.'
                ],
            ],

            // 4. PM - REGULATORY & FINANCIAL REPORTING
            'PM - REGULATORY & FINANCIAL REPORTING' => [
                [
                    'title' => 'Keterlambatan Penyampaian Laporan Bulanan Bank Umum (LBU / APO) ke Portal OJK',
                    'target' => 'Submit laporan LBU 100% tepat waktu sebelum batas akhir tanggal 10 setiap bulan.',
                    'description' => 'Data staging dari berbagai anak usaha terlambat terkonsolidasi memicu ancaman denda keterlambatan OJK Rp 1 juta per hari.',
                    'category_id' => 8, 'cause_category_id' => 2, 'radar_code' => 'C1',
                    'inherent_p' => 4, 'inherent_i' => 4, 'residual_p' => 1, 'residual_i' => 2, 'impact_amount' => 500000000,
                    'cause' => 'Proses ekstraksi data masih membutuhkan kompilasi manual spreadsheet antar departemen.',
                    'existing_control' => 'Lembur tim pelaporan keuangan menjelang tanggal 10.',
                    'mitigation' => 'Implementasi automated data pipeline ETL BI-ANTASENA dan pre-validation staging engine.'
                ],
                [
                    'title' => 'Discrepancy Data Pelaporan Sistem Layanan Informasi Keuangan (SLIK) ke OJK',
                    'target' => 'Zero rejection rate dan zero mismatch data fasilitas debitur pada pelaporan SLIK.',
                    'description' => 'Kolektibilitas debitur yang sudah lunas di cabang masih terlaporkan menunggak (Kol 2) di server pusat SLIK OJK.',
                    'category_id' => 8, 'cause_category_id' => 3, 'radar_code' => 'C2',
                    'inherent_p' => 4, 'inherent_i' => 4, 'residual_p' => 1, 'residual_i' => 2, 'impact_amount' => 650000000,
                    'cause' => 'Inkonsistensi batch cut-off sinkronisasi update status lunas antara loan system cabang dan database agregasi SLIK.',
                    'existing_control' => 'Koreksi manual tiket perbaikan SLIK berdasarkan komplain nasabah.',
                    'mitigation' => 'Otomasi integrasi status pelunasan kredit real-time ke modul pelaporan SLIK harian.'
                ],
                [
                    'title' => 'Kesalahan Perhitungan Rasio Kewajiban Penyediaan Modal Minimum (KPMM / CAR)',
                    'target' => 'Rasio CAR dilaporkan akurat di atas threshold minimum regulator (minimal 15%).',
                    'description' => 'Salah klasifikasi bobot risiko aset tertimbang menurut risiko (ATMR) operasional mengakibatkan deviasi angka rasio permodalan.',
                    'category_id' => 5, 'cause_category_id' => 2, 'radar_code' => 'C2',
                    'inherent_p' => 3, 'inherent_i' => 5, 'residual_p' => 1, 'residual_i' => 3, 'impact_amount' => 2000000000,
                    'cause' => 'Formula ATMR operasional pada engine pelaporan belum mengadopsi standar Basel III / POJK terbaru.',
                    'existing_control' => 'Validasi manual oleh Kepala Divisi Akuntansi sebelum rilis laporan.',
                    'mitigation' => 'Audit model perhitungan ATMR oleh konsultan independen dan penyesuaian automated regulatory engine.'
                ],
                [
                    'title' => 'Keterlambatan Pelaporan Transaksi Keuangan Mencurigakan (LTKM) ke Sistem GRIPS PPATK',
                    'target' => 'Penyampaian laporan LTKM maksimal 3 hari kerja pasca penetapan transaksi mencurigakan.',
                    'description' => 'Volume alert transaksi anomali yang melonjak tidak sempat di-review oleh analis AML sehingga melewati batas regulasi.',
                    'category_id' => 8, 'cause_category_id' => 2, 'radar_code' => 'C1',
                    'inherent_p' => 4, 'inherent_i' => 5, 'residual_p' => 1, 'residual_i' => 2, 'impact_amount' => 1500000000,
                    'cause' => 'Banyaknya false positive pada rule engine AML yang menghabiskan waktu tim investigasi compliance.',
                    'existing_control' => 'Prioritasi manual tiket alert transaksi di atas 500 juta rupiah.',
                    'mitigation' => 'Penerapan AI/ML anomaly detection untuk mereduksi false positive dan integrasi auto-dispatch format XML GRIPS.'
                ],
                [
                    'title' => 'Kegagalan Otomasi Rekonsiliasi Pelaporan Pajak Penghasilan (e-Bupot Unifikasi)',
                    'target' => '100% kepatuhan dan ketepatan waktu pelaporan SPT Masa Pajak PPh Pasal 21/23/26.',
                    'description' => 'Format file xml pemotongan pajak bunga dan vendor gagal di-generate otomatis memicu sanksi denda pajak DJP.',
                    'category_id' => 8, 'cause_category_id' => 3, 'radar_code' => 'F2',
                    'inherent_p' => 3, 'inherent_i' => 4, 'residual_p' => 1, 'residual_i' => 2, 'impact_amount' => 420000000,
                    'cause' => 'Perubahan skema e-bupot DJP tidak segera diakomodasi oleh tim pengembang modul perpajakan internal.',
                    'existing_control' => 'Pemasukan data bukti potong secara semi-manual oleh tax officer.',
                    'mitigation' => 'Integrasi Application Service Provider (ASP) resmi perpajakan dan pembaruan berkala skema e-bupot.'
                ],
                [
                    'title' => 'Ketidakakuratan Pelaporan Likuiditas Harian (Liquidity Coverage Ratio / LCR)',
                    'target' => 'Rasio LCR terpantau harian di atas 100% sesuai standar Basel III dan ketentuan Bank Indonesia.',
                    'description' => 'Keterlambatan input data arus kas keluar (cash outflow) dari komitmen kredit korporasi mendistorsi angka proyeksi likuiditas.',
                    'category_id' => 5, 'cause_category_id' => 2, 'radar_code' => 'F1',
                    'inherent_p' => 3, 'inherent_i' => 5, 'residual_p' => 1, 'residual_i' => 3, 'impact_amount' => 1800000000,
                    'cause' => 'Feed data komitmen pinjaman korporasi masih berbasis batching T+1 sore hari.',
                    'existing_control' => 'Buffer kas likuiditas tambahan yang ditempatkan di Bank Indonesia.',
                    'mitigation' => 'Implementasi real-time intraday liquidity monitoring engine dengan integrasi direct dealing room data.'
                ],
                [
                    'title' => 'Deviasi Angka Pelaporan Rasio Net Stable Funding Ratio (NSFR) Triwulanan',
                    'target' => 'Pemenuhan rasio NSFR minimal 100% pada setiap akhir triwulan laporan.',
                    'description' => 'Salah perhitungan jangka waktu jatuh tempo simpanan deposito struktural menggeser bobot Available Stable Funding (ASF).',
                    'category_id' => 5, 'cause_category_id' => 2, 'radar_code' => 'C2',
                    'inherent_p' => 3, 'inherent_i' => 4, 'residual_p' => 1, 'residual_i' => 2, 'impact_amount' => 780000000,
                    'cause' => 'Klasifikasi maturity bucket dana nasabah institusi pada data warehouse tidak sinkron dengan core ledger.',
                    'existing_control' => 'Pemeriksaan manual tabel maturity bucket oleh tim Treasury ALM.',
                    'mitigation' => 'Otomasi mapping klasifikasi ASF/RSF berbasis kriteria POJK dan automated cross-check reporting validation.'
                ],
                [
                    'title' => 'Kegagalan Sinkronisasi Pelaporan Transaksi Valas (LLD - Lalu Lintas Devisa) ke Bank Indonesia',
                    'target' => 'Submit laporan LLD 100% tuntas sebelum batas akhir tanggal 15 setiap bulan.',
                    'description' => 'Data dokumen underlying ekspor-impor nasabah tidak terlampir lengkap mengakibatkan rejection pada sistem pelaporan BI.',
                    'category_id' => 8, 'cause_category_id' => 2, 'radar_code' => 'C1',
                    'inherent_p' => 4, 'inherent_i' => 4, 'residual_p' => 1, 'residual_i' => 2, 'impact_amount' => 550000000,
                    'cause' => 'Cabang memproses transaksi devisa tanpa mewajibkan upload mandatory dokumen PIB/PEB di sistem.',
                    'existing_control' => 'Validasi manual oleh tim trade finance back-office.',
                    'mitigation' => 'System blocking pada aplikasi trade finance jika field mandatory dan dokumen underlying LLD belum di-upload.'
                ],
                [
                    'title' => 'Ketidakpatuhan Pelaporan Foreign Account Tax Compliance Act (FATCA) dan CRS',
                    'target' => 'Zero sanksi dan zero rejection pada pertukaran data otomatis (AEOI) dengan DJP.',
                    'description' => 'Nasabah pemegang paspor AS tidak teridentifikasi status US Indicia karena format formulir e-KYC tidak lengkap.',
                    'category_id' => 8, 'cause_category_id' => 2, 'radar_code' => 'C2',
                    'inherent_p' => 3, 'inherent_i' => 5, 'residual_p' => 1, 'residual_i' => 2, 'impact_amount' => 2100000000,
                    'cause' => 'Kurangnya field validasi kewarganegaraan ganda dan tax residency pada sistem pembukaan rekening digital.',
                    'existing_control' => 'Pembersihan data berkala nasabah asing oleh unit KYC remediation.',
                    'mitigation' => 'Penyesuaian form onboarding digital dengan mandatory declaration FATCA/CRS dan automated validation Tax ID.'
                ],
                [
                    'title' => 'Keterlambatan Publikasi Laporan Keuangan Triwulanan di Surat Kabar dan Website Resmi',
                    'target' => 'Publikasi laporan keuangan tepat waktu sesuai POJK batas maksimal akhir bulan berikutnya.',
                    'description' => 'Proses audit review terbatas oleh Kantor Akuntan Publik molor akibat lambatnya konfirmasi saldo antar bank.',
                    'category_id' => 6, 'cause_category_id' => 2, 'radar_code' => 'C1',
                    'inherent_p' => 3, 'inherent_i' => 4, 'residual_p' => 1, 'residual_i' => 2, 'impact_amount' => 600000000,
                    'cause' => 'Konfirmasi saldo pihak ketiga masih mengandalkan surat fisik via ekspedisi tanpa konfirmasi digital.',
                    'existing_control' => 'Timeline jadwal penutupan buku yang dimajukan 5 hari.',
                    'mitigation' => 'Pemanfaatan platform digital bank confirmation resmi dan percepatan approval komite audit.'
                ],
                [
                    'title' => 'Ketidaksesuaian Format Ekstraksi Data Laporan Profil Risiko (SIPP) ke OJK',
                    'target' => '100% validitas data tingkat kesehatan bank (TKS) pada portal pelaporan OJK.',
                    'description' => 'Perubahan format metadata XML pada portal OJK menyebabkan berkas kompilasi profil risiko gagal diunggah.',
                    'category_id' => 8, 'cause_category_id' => 3, 'radar_code' => 'C1',
                    'inherent_p' => 3, 'inherent_i' => 3, 'residual_p' => 1, 'residual_i' => 2, 'impact_amount' => 350000000,
                    'cause' => 'Tidak adanya automated pre-flight checker yang memvalidasi struktur XML terhadap XSD schema OJK terbaru.',
                    'existing_control' => 'Uji coba upload 1 hari sebelum batas akhir pelaporan.',
                    'mitigation' => 'Penerapan modul automated XML schema validator dan alert update rilis teknis dari regulator.'
                ],
                [
                    'title' => 'Kesalahan Rekonsiliasi Data Posisi Devisa Neto (PDN) pada Jam Penutupan Transaksi Valas',
                    'target' => 'Posisi PDN selalu terjaga di bawah threshold maksimum 20% dari modal bank.',
                    'description' => 'Keterlambatan input transaksi valas cabang menyebabkan angka PDN melampaui batas regulasi pada penutupan hari.',
                    'category_id' => 5, 'cause_category_id' => 2, 'radar_code' => 'C2',
                    'inherent_p' => 4, 'inherent_i' => 5, 'residual_p' => 1, 'residual_i' => 2, 'impact_amount' => 1700000000,
                    'cause' => 'Sistem treasury tidak memiliki warning threshold otomatis saat posisi terbuka valas mendekati 18%.',
                    'existing_control' => 'Monitoring spreadsheet oleh Treasury ALM officer setiap jam.',
                    'mitigation' => 'Implementasi automated circuit breaker transaksi valas saat PDN menyentuh 17% dan real-time alerting.'
                ],
                [
                    'title' => 'Kegagalan Otomasi Perhitungan Cadangan Wajib Minimum (GWM) Harian Bank Indonesia',
                    'target' => 'Pemenuhan rekening giro di Bank Indonesia 100% memenuhi persentase GWM primer & sekunder.',
                    'description' => 'Arus kas keluar kliring yang tidak terprediksi menyebabkan saldo giro di BI defisit di bawah batas minimum.',
                    'category_id' => 5, 'cause_category_id' => 2, 'radar_code' => 'F1',
                    'inherent_p' => 3, 'inherent_i' => 4, 'residual_p' => 1, 'residual_i' => 2, 'impact_amount' => 890000000,
                    'cause' => 'Proyeksi saldo rekening penampung kliring tidak memperhitungkan penarikan dana mendadak korporasi BUMN.',
                    'existing_control' => 'Penempatan pinjaman pasar uang antar bank (PUAB) over-night darurat.',
                    'mitigation' => 'Pemberlakuan kewajiban pemberitahuan penarikan dana besar (>50 Miliar) H-1 dan automated liquidity forecasting.'
                ],
                [
                    'title' => 'Inkonsistensi Data Aset Tertimbang Menurut Risiko (ATMR) Kredit pada Portofolio UMKM',
                    'target' => 'Penghitungan ATMR kredit segmen retail dan UMKM 100% akurat sesuai Surat Edaran OJK.',
                    'description' => 'Kesalahan tagging agunan sertifikat tanah yang belum dibebani hak tanggungan membuat bobot risiko terhitung 100% alih-alih 50%.',
                    'category_id' => 5, 'cause_category_id' => 2, 'radar_code' => 'O4',
                    'inherent_p' => 3, 'inherent_i' => 4, 'residual_p' => 1, 'residual_i' => 2, 'impact_amount' => 670000000,
                    'cause' => 'Field status pengikatan agunan pada sistem loan recovery tidak terintegrasi otomatis dengan sistem pelaporan ATMR.',
                    'existing_control' => 'Review manual portofolio kredit oleh risk modeling team.',
                    'mitigation' => 'Integrasi modul pengikatan agunan legal ke dalam data mart ATMR dan automated credit data hygiene review.'
                ],
                [
                    'title' => 'Keterlambatan Pelaporan Suku Bunga Dasar Kredit (SBDK) ke Situs Web Resmi dan OJK',
                    'target' => 'Publikasi SBDK setiap bulan paling lambat tanggal 15 secara transparan.',
                    'description' => 'Keterlambatan rapat Asset and Liability Committee (ALCO) menyebabkan penundaan penetapan angka SBDK terbaru.',
                    'category_id' => 8, 'cause_category_id' => 2, 'radar_code' => 'C1',
                    'inherent_p' => 3, 'inherent_i' => 3, 'residual_p' => 1, 'residual_i' => 1, 'impact_amount' => 250000000,
                    'cause' => 'Proses approval berjenjang masih menggunakan sirkular nota fisik manual.',
                    'existing_control' => 'Penggunaan angka SBDK bulan sebelumnya dengan catatan revisi.',
                    'mitigation' => 'Digitalisasi alur penetapan SBDK via komite eksekutif dashboard dan auto-publish API ke website.'
                ],
                [
                    'title' => 'Ketidakpatuhan Format Pelaporan Transaksi Lindung Nilai (Hedging) Syariah / Konvensional',
                    'target' => 'Pelaporan transaksi derivatif dan hedging 100% sesuai ketentuan Bank Indonesia.',
                    'description' => 'Transaksi forward valas korporasi tidak disertai kelengkapan dokumen underlying hedging ekspor-impor yang valid.',
                    'category_id' => 8, 'cause_category_id' => 2, 'radar_code' => 'C2',
                    'inherent_p' => 4, 'inherent_i' => 4, 'residual_p' => 1, 'residual_i' => 2, 'impact_amount' => 920000000,
                    'cause' => 'Sistem dealing room mengizinkan booking deal derivatif tanpa mandatory upload softcopy kontrak dagang.',
                    'existing_control' => 'Pengumpulan dokumen susulan maksimal 7 hari kerja pasca transaksi.',
                    'mitigation' => 'Pengetatan rule sistem treasury: deal trading terkunci sampai dokumen underlying tervalidasi tim legal.'
                ],
                [
                    'title' => 'Kesalahan Pelaporan Rencana Bisnis Bank (RBB) dan Realisasi Penyaluran Kredit Hijau',
                    'target' => 'Realisasi kredit berkelanjutan (sustainable finance) sesuai target RBB minimal 15%.',
                    'description' => 'Kesalahan klasifikasi sektor usaha hijau (kategori EBT / efisiensi energi) menyebabkan laporan realisasi tidak diakui OJK.',
                    'category_id' => 8, 'cause_category_id' => 2, 'radar_code' => 'S3',
                    'inherent_p' => 3, 'inherent_i' => 4, 'residual_p' => 1, 'residual_i' => 2, 'impact_amount' => 460000000,
                    'cause' => 'Kurangnya pemahaman relationship manager cabang terhadap Taksonomi Hijau Indonesia edisi terbaru.',
                    'existing_control' => 'Pemeriksaan berkala oleh unit Keberlanjutan (Sustainability Office).',
                    'mitigation' => 'Pelatihan sertifikasi green taxonomy bagi tim analis kredit dan penyesuaian kuesioner kelayakan pada loan app.'
                ],
                [
                    'title' => 'Anomali Perhitungan Pajak Bunga Obligasi Pemerintah (SBN) pada Portofolio Investasi Bank',
                    'target' => 'Pencatatan beban pajak PPh final obligasi negara 100% tepat sesuai UU HPP.',
                    'description' => 'Sistem accounting memotong tarif pajak lama 15% padahal tarif PPh final obligasi telah disesuaikan menjadi 10%.',
                    'category_id' => 5, 'cause_category_id' => 2, 'radar_code' => 'F1',
                    'inherent_p' => 3, 'inherent_i' => 3, 'residual_p' => 1, 'residual_i' => 1, 'impact_amount' => 310000000,
                    'cause' => 'Tabel master tarif pajak pada financial reporting engine tidak di-update pasca undang-undang perpajakan baru berlaku.',
                    'existing_control' => 'Koreksi fiskal tahunan saat audit laporan keuangan tahunan.',
                    'mitigation' => 'Prosedur formal manajemen perubahan parameter regulasi perpajakan dengan mandatory cross-check tim legal pajak.'
                ],
                [
                    'title' => 'Kegagalan Pelaporan Data Pinjaman Sindikasi Multi-Bank pada Sistem Debitur OJK',
                    'target' => 'Pencatatan porsi pembiayaan sindikasi akurat pada pelaporan SLIK tanpa tumpang tindih plafon.',
                    'description' => 'Bank sebagai agen fasilitas melaporkan 100% nilai total kredit tanpa membagi porsi bank peserta sindikasi lainnya.',
                    'category_id' => 8, 'cause_category_id' => 2, 'radar_code' => 'C1',
                    'inherent_p' => 3, 'inherent_i' => 4, 'residual_p' => 1, 'residual_i' => 2, 'impact_amount' => 740000000,
                    'cause' => 'Modul reporting kredit komersial tidak membedakan peran facility agent vs member lender.',
                    'existing_control' => 'Surat konfirmasi saldo berkala antar bank anggota sindikasi.',
                    'mitigation' => 'Pembaruan arsitektur modul pelaporan pembiayaan sindikasi dengan segmentasi otomatis peran agen & partisipan.'
                ],
                [
                    'title' => 'Discrepancy Data Rekonsiliasi Kas Kantor Cabang dengan Laporan Keuangan Neraca Pusat',
                    'target' => 'Zero selisih fisik uang kas vault cabang dengan catatan saldo buku besar (GL) harian.',
                    'description' => 'Keterlambatan input mutasi fisik pengiriman uang antar cabang (cash-in-transit) menimbulkan temporary break saldo kas.',
                    'category_id' => 7, 'cause_category_id' => 2, 'radar_code' => 'O4',
                    'inherent_p' => 3, 'inherent_i' => 3, 'residual_p' => 1, 'residual_i' => 1, 'impact_amount' => 220000000,
                    'cause' => 'Serah terima uang fisik oleh vendor pengelola kas (CIT) dicatat manual sebelum rekonsiliasi elektronik malam hari.',
                    'existing_control' => 'Perhitungan fisik kas (kas opname) setiap sore oleh supervisor cabang.',
                    'mitigation' => 'Penerapan mobile electronic cash transfer slip dengan barcode scanning real-time terhubung ke financial ledger.'
                ],
            ],

            // 5. QA - QA & INNOVATION
            'QA - QA & INNOVATION' => [
                [
                    'title' => 'Flakiness dan Kegagalan Eksekusi Automated Regression Testing pada Pipeline CI/CD',
                    'target' => 'Pass-rate automated regression testing di atas 98% dengan false positive di bawah 2%.',
                    'description' => 'Test script automated UI testing sering gagal secara acak akibat asynchronous animation loading delay pada browser.',
                    'category_id' => 9, 'cause_category_id' => 3, 'radar_code' => 'O4',
                    'inherent_p' => 4, 'inherent_i' => 3, 'residual_p' => 1, 'residual_i' => 2, 'impact_amount' => 260000000,
                    'cause' => 'Penggunaan hardcoded sleep wait alih-alih dynamic explicit element wait pada framework testing Selenium/Cypress.',
                    'existing_control' => 'Manual re-run build pipeline oleh QA engineer.',
                    'mitigation' => 'Standardisasi smart wait assertion, retry mechanism test runner, dan refactoring script flakiness.'
                ],
                [
                    'title' => 'Kebocoran Data Produksi (Data Leakage) Akibat Sanitasi Data UAT yang Tidak Tuntas',
                    'target' => '100% data uji di environment testing merupakan data sintetis atau masked data.',
                    'description' => 'Nomor rekening dan NIK nasabah riil ditemukan pada environment QA yang dapat diakses oleh staf magang dan vendor.',
                    'category_id' => 11, 'cause_category_id' => 1, 'radar_code' => 'C2',
                    'inherent_p' => 4, 'inherent_i' => 5, 'residual_p' => 1, 'residual_i' => 2, 'impact_amount' => 1800000000,
                    'cause' => 'Script anonymization data database dump gagal melakukan masking pada tabel relasi baru.',
                    'existing_control' => 'Perjanjian kerahasiaan NDA bagi seluruh tim engineering.',
                    'mitigation' => 'Penggunaan synthetic test data generator tool dan audit kepatuhan lingkungan non-production berkala.'
                ],
                [
                    'title' => 'Kegagalan Stress Testing Menemukan Bottleneck Concurrency Saat Promosi Gajian (Payday)',
                    'target' => 'Performance testing mampu mensimulasikan peak traffic hingga 5x rata-rata transaksi harian.',
                    'description' => 'Aplikasi mobile banking crash saat event payday karena load testing sebelumnya hanya menguji beban rata-rata biasa.',
                    'category_id' => 9, 'cause_category_id' => 2, 'radar_code' => 'O2',
                    'inherent_p' => 4, 'inherent_i' => 4, 'residual_p' => 1, 'residual_i' => 2, 'impact_amount' => 750000000,
                    'cause' => 'Environment performance testing memiliki spesifikasi kapasitas hardware hanya 20% dari kapasitas production.',
                    'existing_control' => 'Pelaksanaan stress test standar 500 virtual user selama 30 menit.',
                    'mitigation' => 'Standarisasi load testing dengan scaled production-like environment dan chaos engineering injection testing.'
                ],
                [
                    'title' => 'Lolosnya Celah Keamanan Injeksi SQL / XSS Akibat Minimnya Security Testing Otomatis',
                    'target' => 'Zero vulnerability High/Critical yang lolos dari tahap QA ke production.',
                    'description' => 'Fitur pencarian transaksi merchant mengandung celah Blind SQL Injection yang tidak terdeteksi oleh uji fungsional.',
                    'category_id' => 19, 'cause_category_id' => 3, 'radar_code' => 'O3',
                    'inherent_p' => 4, 'inherent_i' => 5, 'residual_p' => 1, 'residual_i' => 2, 'impact_amount' => 1500000000,
                    'cause' => 'Pengujian keamanan SAST/DAST hanya dilakukan manual menjelang peluncuran tahunan tanpa integrasi per-build.',
                    'existing_control' => 'Code review manual oleh QA lead.',
                    'mitigation' => 'Integrasi mandatory automated security gate (SonarQube & OWASP ZAP) pada setiap merge request branch QA.'
                ],
                [
                    'title' => 'Desinkronisasi Konfigurasi Environment Testing (UAT) dengan Environment Production',
                    'target' => 'Paritas 100% versi software, OS patch, dan dependensi konfigurasi antara UAT dan Production.',
                    'description' => 'Fitur pembayaran berhasil 100% di UAT namun error fatal di production karena perbedaan minor version database driver.',
                    'category_id' => 9, 'cause_category_id' => 2, 'radar_code' => 'O4',
                    'inherent_p' => 4, 'inherent_i' => 4, 'residual_p' => 1, 'residual_i' => 2, 'impact_amount' => 600000000,
                    'cause' => 'Manajemen provisioning server testing masih dilakukan manual tanpa Infrastructure-as-Code (IaC).',
                    'existing_control' => 'Pencatatan konfigurasi server pada checklist dokumen deployment.',
                    'mitigation' => 'Penerapan Infrastructure-as-Code (Terraform/Ansible) untuk menjamin keseragaman konfigurasi seluruh environment.'
                ],
                [
                    'title' => 'Tidak Tercapainya Cakupan Pengujian (Test Coverage) pada Modul Kritis Perbankan',
                    'target' => 'Code coverage minimal 80% pada unit test dan 100% pada logic perhitungan finansial.',
                    'description' => 'Bug edge-case bunga pinjaman lolos ke production karena branch logic kode tidak memiliki unit test yang memadai.',
                    'category_id' => 3, 'cause_category_id' => 2, 'radar_code' => 'S2',
                    'inherent_p' => 4, 'inherent_i' => 3, 'residual_p' => 1, 'residual_i' => 2, 'impact_amount' => 340000000,
                    'cause' => 'Target timeline peluncuran produk yang terburu-buru mengorbankan penulisan test cases komprehensif.',
                    'existing_control' => 'User Acceptance Test (UAT) manual oleh tim bisnis.',
                    'mitigation' => 'Penetapan quality gate strict: pull request otomatis ditolak jika coverage unit test turun di bawah 80%.'
                ],
                [
                    'title' => 'Ketidakmampuan Menguji Integrasi Pihak Ketiga Akibat Mocking / Sandbox Partner yang Tidak Stabil',
                    'target' => 'Ketersediaan mock server pihak ketiga 99.9% untuk kelancaran pengujian tanpa hambatan eksternal.',
                    'description' => 'Jadwal rilis integrasi pembayaran e-wallet tertunda 3 minggu karena sandbox server partner sering down dan error.',
                    'category_id' => 13, 'cause_category_id' => 3, 'radar_code' => 'O4',
                    'inherent_p' => 4, 'inherent_i' => 3, 'residual_p' => 1, 'residual_i' => 2, 'impact_amount' => 280000000,
                    'cause' => 'Ketergantungan langsung pada sandbox live pihak ketiga tanpa isolasi virtualisasi API lokal.',
                    'existing_control' => 'Menunggu perbaikan sandbox dari tim support vendor.',
                    'mitigation' => 'Pengembangan service virtualization (Mock Server WireMock/Prism) internal berbasis spesifikasi OpenAPI/Swagger.'
                ],
                [
                    'title' => 'Fragmentasi Device dan OS Testing yang Tidak Memadai pada Aplikasi Mobile Banking',
                    'target' => 'Uji kompatibilitas mencakup minimal 90% distribusi versi Android dan iOS nasabah aktif.',
                    'description' => 'Aplikasi mobile perbankan force close pada perangkat Android tipe lama berlayar resolusi rendah pasca rilis update.',
                    'category_id' => 9, 'cause_category_id' => 2, 'radar_code' => 'O5',
                    'inherent_p' => 4, 'inherent_i' => 3, 'residual_p' => 1, 'residual_i' => 2, 'impact_amount' => 410000000,
                    'cause' => 'Pengujian mobile hanya mengandalkan emulator desktop dan beberapa device fisik milik tim tester internal.',
                    'existing_control' => 'Rilis perbaikan darurat hotfix setelah menerima rating bintang satu di Play Store.',
                    'mitigation' => 'Pemanfaatan automated cloud device farm (BrowserStack / AWS Device Farm) untuk pengujian paralel ratusan tipe device.'
                ],
                [
                    'title' => 'Keterlambatan Dokumentasi Test Evidence dan Ketidakterlacakan Hasil Uji terhadap Persyaratan',
                    'target' => '100% requirements traceability matrix (RTM) terdokumentasi dan dapat diaudit regulator.',
                    'description' => 'Auditor internal menemukan rilis fitur baru tidak memiliki bukti screenshot eksekusi uji acceptance bisnis yang valid.',
                    'category_id' => 8, 'cause_category_id' => 1, 'radar_code' => 'C2',
                    'inherent_p' => 3, 'inherent_i' => 3, 'residual_p' => 1, 'residual_i' => 1, 'impact_amount' => 190000000,
                    'cause' => 'Pencatatan test evidence dilakukan parsial di aplikasi chat dan folder personal tanpa sistem test management terpusat.',
                    'existing_control' => 'Kompilasi dokumen evidence secara terburu-buru saat ada audit.',
                    'mitigation' => 'Standardisasi test management tool (Jira Xray / TestRail) dengan integrasi otomatis report build CI/CD.'
                ],
                [
                    'title' => 'Kegagalan Deteksi Memory Leak pada Pengujian Long-Running Soak Testing',
                    'target' => 'Zero memory exhaustion pada backend microservices setelah running non-stop 7x24 jam.',
                    'description' => 'Service backend transfer dana mengalami out-of-memory crash setelah 4 hari berjalan di environment production.',
                    'category_id' => 9, 'cause_category_id' => 3, 'radar_code' => 'O2',
                    'inherent_p' => 3, 'inherent_i' => 4, 'residual_p' => 1, 'residual_i' => 2, 'impact_amount' => 520000000,
                    'cause' => 'Pengujian performa hanya dilakukan dengan stress test durasi pendek tanpa pengujian ketahanan berkelanjutan (soak test).',
                    'existing_control' => 'Restart berkala pod Kubernetes terjadwal harian.',
                    'mitigation' => 'Penerapan mandatory soak testing durasi 48 jam dengan profiler memory tracking sebelum major release.'
                ],
                [
                    'title' => 'Ketergantungan pada Proses Pengujian Manual yang Memperlambat Time-to-Market',
                    'target' => 'Tingkat otomasi uji regresi mencapai minimal 70% dari total skenario pengujian.',
                    'description' => 'Siklus rilis fitur baru memakan waktu 4 minggu hanya untuk verifikasi manual skenario regresi berulang.',
                    'category_id' => 4, 'cause_category_id' => 2, 'radar_code' => 'S3',
                    'inherent_p' => 4, 'inherent_i' => 3, 'residual_p' => 2, 'residual_i' => 2, 'impact_amount' => 330000000,
                    'cause' => 'Rendahnya kompetensi script automation pada tim QA operasional cabang dan alokasi waktu minim.',
                    'existing_control' => 'Penambahan tenaga tester outsourcing kontrak jangka pendek.',
                    'mitigation' => 'Program upskilling QA automation engineering dan penerapan codeless / low-code automated test framework.'
                ],
                [
                    'title' => 'Ketidakakuratan Penilaian Kesiapan Rilis (Go/No-Go Decision) Akibat Metrik Kualitas Subjektif',
                    'target' => 'Keputusan rilis produk berbasis 100% data metrik defek obyektif (zero blocker/critical bug).',
                    'description' => 'Aplikasi dirilis ke nasabah karena tekanan deadline bisnis meskipun terdapat 5 defek medium yang belum teratasi.',
                    'category_id' => 3, 'cause_category_id' => 1, 'radar_code' => 'S3',
                    'inherent_p' => 4, 'inherent_i' => 4, 'residual_p' => 1, 'residual_i' => 2, 'impact_amount' => 670000000,
                    'cause' => 'Ketiadaan kriteria exit criteria yang mengikat secara formal antara tim bisnis, IT, dan risk manajemen.',
                    'existing_control' => 'Rapat koordinasi pelepasan rilis bersama pimpinan divisi.',
                    'mitigation' => 'Penerapan Quality Gate Policy formal: sistem otomatis memblokir pipeline rilis jika exit criteria tidak terpenuhi.'
                ],
                [
                    'title' => 'Resiko Keusangan (Obsolescence) Framework Pengujian Otomatis Tanpa Pemeliharaan',
                    'target' => 'Arsitektur automation testing framework modular dan kompatibel dengan versi tech stack terbaru.',
                    'description' => 'Upgrade versi Node.js dan browser engine menyebabkan ribuan test script tidak dapat dijalankan secara bersamaan.',
                    'category_id' => 9, 'cause_category_id' => 3, 'radar_code' => 'S2',
                    'inherent_p' => 3, 'inherent_i' => 3, 'residual_p' => 1, 'residual_i' => 1, 'impact_amount' => 220000000,
                    'cause' => 'Framework testing dibangun dengan dependensi library usang yang sudah tidak dimaintain komunitas open source.',
                    'existing_control' => 'Mengunci versi browser dan node pada runner server pengujian.',
                    'mitigation' => 'Modernisasi automation testing framework ke Playwright modern dan refactoring Page Object Model (POM).'
                ],
                [
                    'title' => 'Gagal Terdeteksinya Regresi Performa API Akibat Ketiadaan Baseline Benchmark',
                    'target' => 'Zero degradasi latency API di atas 15% dari baseline performa rilis sebelumnya.',
                    'description' => 'Pembaruan logic middleware memperlambat waktu response API cek saldo dari 150ms menjadi 950ms tanpa disadari.',
                    'category_id' => 9, 'cause_category_id' => 2, 'radar_code' => 'O5',
                    'inherent_p' => 4, 'inherent_i' => 3, 'residual_p' => 1, 'residual_i' => 2, 'impact_amount' => 390000000,
                    'cause' => 'Pengujian performa tidak membandingkan hasil secara otomatis terhadap metrik baseline historis build sebelumnya.',
                    'existing_control' => 'Pengujian performa ad-hoc hanya saat ada keluhan nasabah.',
                    'mitigation' => 'Integrasi automated performance regression testing (k6 / JMeter) dengan threshold alert pada CI pipeline.'
                ],
                [
                    'title' => 'Kesalahan Validasi Alur Transaksi Akibat Ketidaklengkapan Skenario Negatif (Negative Testing)',
                    'target' => '100% skenario pengujian mencakup penanganan input tidak valid, boundary values, dan error injection.',
                    'description' => 'Input nominal transfer angka negatif pada API berhasil diproses dan menyebabkan anomali penambahan saldo akun.',
                    'category_id' => 9, 'cause_category_id' => 2, 'radar_code' => 'O2',
                    'inherent_p' => 3, 'inherent_i' => 5, 'residual_p' => 1, 'residual_i' => 2, 'impact_amount' => 1100000000,
                    'cause' => 'Test case hanya fokus pada happy path alur bisnis standar tanpa boundary value analysis yang mendalam.',
                    'existing_control' => 'Validasi backend standard framework perbankan.',
                    'mitigation' => 'Standardisasi metodologi equivalence partitioning, boundary value testing, dan fuzz testing pada seluruh API.'
                ],
                [
                    'title' => 'Kebocoran Kredensial dan API Key Layanan Eksternal pada Repository Script Pengujian',
                    'target' => 'Zero hardcoded secrets pada repository script automation testing dan test fixtures.',
                    'description' => 'Token akses staging gateway pembayaran ter-commit ke public repository oleh staf penguji pihak ketiga.',
                    'category_id' => 19, 'cause_category_id' => 1, 'radar_code' => 'C2',
                    'inherent_p' => 4, 'inherent_i' => 4, 'residual_p' => 1, 'residual_i' => 2, 'impact_amount' => 850000000,
                    'cause' => 'Penyimpanan environment credential pada file konfigurasi lokal yang ikut terdorong ke git remote.',
                    'existing_control' => 'Review manual commit history git oleh repository admin.',
                    'mitigation' => 'Implementasi pre-commit hooks (TruffleHog/GitGuardian) dan integrasi centralized HashiCorp Vault secrets management.'
                ],
                [
                    'title' => 'Inkompatibilitas Fitur Aksesibilitas (Accessibility WCAG 2.1) pada Aplikasi Layanan Publik',
                    'target' => 'Kepatuhan standar aksesibilitas aplikasi perbankan bagi penyandang disabilitas (WCAG 2.1 AA).',
                    'description' => 'Aplikasi perbankan tidak dapat dibaca oleh pembaca layar (screen reader) tunanetra sehingga menuai komplain asosiasi.',
                    'category_id' => 3, 'cause_category_id' => 2, 'radar_code' => 'S1',
                    'inherent_p' => 3, 'inherent_i' => 3, 'residual_p' => 1, 'residual_i' => 1, 'impact_amount' => 240000000,
                    'cause' => 'Ketiadaan pengujian aksesibilitas digital dalam checklist pengujian standar tim QA.',
                    'existing_control' => 'Fasilitas layanan khusus teller tatap muka di kantor cabang.',
                    'mitigation' => 'Implementasi automated accessibility testing tools (axe-core) dan uji usability bersama perwakilan komunitas disabilitas.'
                ],
                [
                    'title' => 'Tingginya Biaya Infrastruktur Pengujian Cloud Akibat Server Testing yang Berjalan Non-Stop',
                    'target' => 'Efisiensi biaya cloud environment non-production hingga 40% dengan sistem auto-shutdown.',
                    'description' => 'Server testing dan cluster Kubernetes UAT terus aktif selama akhir pekan dan malam hari saat tidak ada aktivitas pengujian.',
                    'category_id' => 1, 'cause_category_id' => 2, 'radar_code' => 'F1',
                    'inherent_p' => 4, 'inherent_i' => 3, 'residual_p' => 1, 'residual_i' => 1, 'impact_amount' => 360000000,
                    'cause' => 'Ketiadaan otomasi schedule shutdown/startup pada resource cloud virtual machine testing.',
                    'existing_control' => 'Peringatan tagihan bulanan dari penyedia layanan cloud computing.',
                    'mitigation' => 'Implementasi serverless dynamic test environments dan scheduler auto-shutdown di luar jam operasional kerja.'
                ],
                [
                    'title' => 'Ketidakmampuan Melakukan Pengujian Pemulihan Bencana (Disaster Recovery Simulation) Rutin',
                    'target' => 'Eksekusi simulasi failover DR aplikasi secara semi-otomatis minimal 2 kali setahun.',
                    'description' => 'Tim QA kesulitan memvalidasi integritas data dan fungsionalitas aplikasi saat sistem dialihkan ke server DRC.',
                    'category_id' => 9, 'cause_category_id' => 2, 'radar_code' => 'O3',
                    'inherent_p' => 3, 'inherent_i' => 5, 'residual_p' => 1, 'residual_i' => 2, 'impact_amount' => 1400000000,
                    'cause' => 'Script automated test suite tidak dirancang untuk dapat diarahkan ke DNS endpoint DRC dengan mulus.',
                    'existing_control' => 'Uji DR terbatas pada verifikasi ping dan sinkronisasi database tanpa verifikasi transaksi end-to-end.',
                    'mitigation' => 'Penyusunan automated end-to-end smoke test suite yang dapat langsung dipicu saat pelaksanaan latihan DR.'
                ],
                [
                    'title' => 'Risiko Hambatan Adopsi Inovasi AI/GenAI dalam Pembuatan Test Cases Pengujian Perbankan',
                    'target' => 'Peningkatan produktivitas penulisan skenario uji hingga 50% dengan bantuan AI yang aman.',
                    'description' => 'Penggunaan tool AI publik gratisan oleh tester membocorkan logika bisnis internal dan data arsitektur aplikasi bank.',
                    'category_id' => 19, 'cause_category_id' => 1, 'radar_code' => 'S2',
                    'inherent_p' => 4, 'inherent_i' => 4, 'residual_p' => 1, 'residual_i' => 2, 'impact_amount' => 780000000,
                    'cause' => 'Ketiadaan panduan tata kelola (governance) dan platform internal GenAI yang aman bagi tim engineering.',
                    'existing_control' => 'Himbauan umum larangan input data sensitif ke chatbot publik.',
                    'mitigation' => 'Penyediaan internal enterprise secure LLM assistant dengan data privacy guardrails untuk akselerasi test generation.'
                ],
            ],

            // 6. DI - DIGITAL SOLUTION
            'DI - DIGITAL SOLUTION' => [
                [
                    'title' => 'Eksploitasi Celah Keamanan API Gateway Berupa Broken Object Level Authorization (BOLA)',
                    'target' => 'Zero authorization bypass pada seluruh endpoint public API Digital Banking.',
                    'description' => 'Penyerang memodifikasi parameter ID mutasi pada request HTTP untuk melihat rincian transaksi rekening nasabah lain.',
                    'category_id' => 10, 'cause_category_id' => 3, 'radar_code' => 'O3',
                    'inherent_p' => 4, 'inherent_i' => 5, 'residual_p' => 1, 'residual_i' => 2, 'impact_amount' => 1900000000,
                    'cause' => 'API gateway hanya memverifikasi validitas token JWT tanpa memvalidasi kepemilikan resource ID terhadap user identity.',
                    'existing_control' => 'WAF rules standar dan rate limiting per IP address.',
                    'mitigation' => 'Implementasi context-aware authorization filter pada API gateway dan automated BOLA security regression testing.'
                ],
                [
                    'title' => 'Serangan Otomatis Credential Stuffing dan Brute Force pada Portal Mobile Banking',
                    'target' => 'Keberhasilan mitigasi serangan bot dan akun takeover di atas 99.9%.',
                    'description' => 'Botnet menggunakan kombinasi jutaan username dan password bocoran dark web untuk mencoba login massal.',
                    'category_id' => 19, 'cause_category_id' => 3, 'radar_code' => 'O3',
                    'inherent_p' => 5, 'inherent_i' => 4, 'residual_p' => 2, 'residual_i' => 2, 'impact_amount' => 1200000000,
                    'cause' => 'Endpoint login belum dilengkapi proteksi behavioral bot detection dan CAPTCHA adaptif cerdas.',
                    'existing_control' => 'Penguncian akun sementara setelah 3 kali salah input password.',
                    'mitigation' => 'Penerapan Cloudflare Turnstile / Bot Management cerdas, device fingerprinting, dan IP reputation filtering.'
                ],
                [
                    'title' => 'Penipuan Manipulasi Biometrik Face Recognition Menggunakan Teknologi Deepfake AI',
                    'target' => 'Deteksi keaslian wajah (liveness detection) aktif mencegah 100% video injection/deepfake.',
                    'description' => 'Pelaku kejahatan membuka rekening pinjol menggunakan video wajah hasil render AI deepfake dari foto KTP korban.',
                    'category_id' => 19, 'cause_category_id' => 3, 'radar_code' => 'O3',
                    'inherent_p' => 4, 'inherent_i' => 5, 'residual_p' => 1, 'residual_i' => 3, 'impact_amount' => 2300000000,
                    'cause' => 'Algoritma liveness detection lama hanya mengandalkan deteksi kedipan mata pasif yang mudah dikelabui.',
                    'existing_control' => 'Verifikasi video call acak oleh petugas helpdesk untuk aplikasi pinjaman besar.',
                    'mitigation' => 'Upgrade engine liveness ke standar 3D passive liveness bersertifikasi iBeta Level 2 dan challenge dinamis interaktif.'
                ],
                [
                    'title' => 'Injeksi Script Jahat (Cross-Site Scripting / XSS) pada Komponen WebView Mobile App',
                    'target' => 'Zero DOM-based XSS pada seluruh modul promosi dan webview aplikasi mobile.',
                    'description' => 'Hacker menyisipkan kode javascript pada banner promosi yang mencuri session token login nasabah di local storage.',
                    'category_id' => 19, 'cause_category_id' => 3, 'radar_code' => 'O3',
                    'inherent_p' => 3, 'inherent_i' => 5, 'residual_p' => 1, 'residual_i' => 2, 'impact_amount' => 1400000000,
                    'cause' => 'Pengaturan konfigurasi WebView mengaktifkan JavaScriptInterface tanpa isolasi domain origin yang aman.',
                    'existing_control' => 'Content Security Policy (CSP) standar pada web server banner.',
                    'mitigation' => 'Disable file access pada WebView, sanitasi input ketat, dan implementasi secure storage berbasis hardware Keystore/Keychain.'
                ],
                [
                    'title' => 'Downtime Layanan Digital Akibat Lonjakan Beban Transaksi Flash Sale E-Commerce',
                    'target' => 'Tingkat ketersediaan (uptime) platform API perbankan digital minimal 99.95%.',
                    'description' => 'Event promo tanggal kembar (11.11 / 12.12) memicu lonjakan 10.000 transaksi per detik yang melumpuhkan database connection pool.',
                    'category_id' => 7, 'cause_category_id' => 3, 'radar_code' => 'O2',
                    'inherent_p' => 4, 'inherent_i' => 4, 'residual_p' => 1, 'residual_i' => 2, 'impact_amount' => 950000000,
                    'cause' => 'Kapasitas auto-scaling pod lambat merespons spike traffic mendadak dalam hitungan detik.',
                    'existing_control' => 'Peningkatan kapasitas server manual sebelum jam promosi dimulai.',
                    'mitigation' => 'Implementasi predictive auto-scaling KEDA Kubernetes, Redis caching cluster, dan queue buffering decoupling.'
                ],
                [
                    'title' => 'Kegagalan Otomasi Deteksi Aplikasi Mobile Dimodifikasi (Repackaged / Tampered APK)',
                    'target' => '100% deteksi dan penolakan eksekusi pada aplikasi yang di-repack atau berjalan di OS rooted.',
                    'description' => 'Beredar file APK palsu hasil decompiling yang disisipi malware trojan untuk merekam PIN transaksi nasabah.',
                    'category_id' => 19, 'cause_category_id' => 3, 'radar_code' => 'O3',
                    'inherent_p' => 4, 'inherent_i' => 5, 'residual_p' => 1, 'residual_i' => 2, 'impact_amount' => 2500000000,
                    'cause' => 'Aplikasi tidak dilengkapi proteksi Runtime Application Self-Protection (RASP) dan anti-tampering signing check.',
                    'existing_control' => 'Peringatan keamanan kepada nasabah agar hanya mengunduh dari Google Play Store resmi.',
                    'mitigation' => 'Integrasi library RASP (anti-root, anti-debugger, code obfuscation DexGuard, dan checksum signature validation).'
                ],
                [
                    'title' => 'Eksploitasi Broken Function Level Authorization pada Endpoint Admin Backoffice Portal',
                    'target' => 'Zero hak akses administratif yang dapat diakses oleh user role nasabah publik.',
                    'description' => 'Nasabah reguler dapat mengakses endpoint /api/v1/admin/refund dengan mengubah parameter role pada request header.',
                    'category_id' => 10, 'cause_category_id' => 3, 'radar_code' => 'O3',
                    'inherent_p' => 3, 'inherent_i' => 5, 'residual_p' => 1, 'residual_i' => 2, 'impact_amount' => 1600000000,
                    'cause' => 'Pemisahan modul admin dan modul publik masih berada dalam satu gateway tanpa network segmentation.',
                    'existing_control' => 'Pemeriksaan hak akses role di level frontend UI.',
                    'mitigation' => 'Isolasi jaringan portal admin ke private subnet VPN dan enforced backend authorization middleware pada semua API.'
                ],
                [
                    'title' => 'Kebocoran Data Sensitif Nasabah pada Push Notification Firebase Cloud Messaging (FCM)',
                    'target' => 'Zero pencantuman saldo dan nomor rekening lengkap pada payload push notification pihak ketiga.',
                    'description' => 'Teks notifikasi mutasi yang dikirim melalui server Google mencantumkan saldo rekening dan nama lengkap secara unencrypted.',
                    'category_id' => 11, 'cause_category_id' => 2, 'radar_code' => 'C2',
                    'inherent_p' => 4, 'inherent_i' => 4, 'residual_p' => 1, 'residual_i' => 2, 'impact_amount' => 700000000,
                    'cause' => 'Arsitektur notifikasi tidak menggunakan sistem silent push dengan local payload decryption.',
                    'existing_control' => 'Masking 4 digit terakhir nomor rekening.',
                    'mitigation' => 'Penerapan silent notification: push payload hanya memicu fetch terenkripsi langsung dari server bank ke secure app.'
                ],
                [
                    'title' => 'Kerentanan Server-Side Request Forgery (SSRF) pada Fitur Import Tagihan Pembayaran via URL',
                    'target' => 'Zero akses tidak sah ke internal cloud metadata service via celah SSRF.',
                    'description' => 'Fitur parsing e-invoice dari URL eksternal dimanipulasi penyerang untuk mengakses AWS/GCP metadata endpoint internal.',
                    'category_id' => 19, 'cause_category_id' => 3, 'radar_code' => 'O3',
                    'inherent_p' => 3, 'inherent_i' => 5, 'residual_p' => 1, 'residual_i' => 2, 'impact_amount' => 2100000000,
                    'cause' => 'Tidak adanya whitelist domain dan ketiadaan filtering terhadap alamat IP privat (RFC 1918 dan 169.254.169.254).',
                    'existing_control' => 'WAF inspection pada input parameter form.',
                    'mitigation' => 'Pemberlakuan strict URL domain whitelisting, isolasi container parser pada DMZ, dan blocking cloud metadata access.'
                ],
                [
                    'title' => 'Kegagalan Otomasi Sinkronisasi State Transaksi Antara Web Portal dan Mobile App',
                    'target' => 'Konsistensi 100% status transaksi dan riwayat mutasi di seluruh kanal digital.',
                    'description' => 'Nasabah melakukan pembayaran di web browser namun statusnya masih unpaid pada aplikasi mobile selama beberapa jam.',
                    'category_id' => 7, 'cause_category_id' => 3, 'radar_code' => 'O4',
                    'inherent_p' => 3, 'inherent_i' => 3, 'residual_p' => 1, 'residual_i' => 1, 'impact_amount' => 250000000,
                    'cause' => 'Arsitektur backend mengandalkan local memory cache yang tidak terdistribusi (non-distributed cache).',
                    'existing_control' => 'Fitur refresh manual tarik layar (pull-to-refresh).',
                    'mitigation' => 'Implementasi centralized Redis Pub/Sub event streaming dan WebSocket synchronization antar perangkat nasabah.'
                ],
                [
                    'title' => 'Serangan Man-in-the-Middle (MITM) Akibat Kegagalan Penerapan SSL Pinning yang Benar',
                    'target' => '100% resistensi aplikasi mobile terhadap intersepsi proxy (Burp Suite / Charles Proxy).',
                    'description' => 'Penyerang di jaringan Wi-Fi publik menginstal sertifikat palsu dan merekam traffic API login perbankan.',
                    'category_id' => 19, 'cause_category_id' => 3, 'radar_code' => 'O3',
                    'inherent_p' => 4, 'inherent_i' => 5, 'residual_p' => 1, 'residual_i' => 2, 'impact_amount' => 1750000000,
                    'cause' => 'Implementasi public key pinning dapat dibypass menggunakan script frida/objection standar pada ponsel Android.',
                    'existing_control' => 'Enkripsi traffic HTTPS TLS 1.3 standar.',
                    'mitigation' => 'Penguatan certificate public key pinning dengan anti-hooking detector dan dynamic payload encryption ganda.'
                ],
                [
                    'title' => 'Penyalahgunaan Fitur Referral Code dan Promo Cashback oleh Akun Bot Sindikat Fraud',
                    'target' => 'Zero pencairan dana promosi pemasaran oleh sindikat akun bodong / bot farm.',
                    'description' => 'Sindikat kejahatan menggunakan ribuan nomor virtual untuk mendaftar akun fiktif dan menguras budget reward promo.',
                    'category_id' => 5, 'cause_category_id' => 1, 'radar_code' => 'F1',
                    'inherent_p' => 4, 'inherent_i' => 4, 'residual_p' => 1, 'residual_i' => 2, 'impact_amount' => 880000000,
                    'cause' => 'Verifikasi nomor HP mengizinkan nomor virtual VoIP dan ketiadaan deteksi kesamaan device ID.',
                    'existing_control' => 'Pembatasan kuota promo harian per akun.',
                    'mitigation' => 'Penerapan fraud score engine pada onboarding promo, verifikasi NIK Dukcapil instan, dan device ID blacklist.'
                ],
                [
                    'title' => 'Keterlambatan Rilis Aplikasi di App Store dan Google Play Akibat Rejection Kebijakan Privasi',
                    'target' => 'Persetujuan rilis update aplikasi mobile maksimal 48 jam pasca submit ke App Store.',
                    'description' => 'Rilis perbaikan bug kritis tertolak oleh Google Play Store karena penggunaan permission ACCESS_FINE_LOCATION yang tidak jelas.',
                    'category_id' => 8, 'cause_category_id' => 2, 'radar_code' => 'C1',
                    'inherent_p' => 4, 'inherent_i' => 3, 'residual_p' => 1, 'residual_i' => 1, 'impact_amount' => 320000000,
                    'cause' => 'Developer menambahkan library iklan analitik pihak ketiga yang meminta izin akses sensitif tanpa izin risk officer.',
                    'existing_control' => 'Uji internal sebelum submit ke store.',
                    'mitigation' => 'Audit berkala Android manifest permission dan pemenuhan pre-release checklist kepatuhan Google/Apple privacy policy.'
                ],
                [
                    'title' => 'Eksploitasi Race Condition pada Fitur Transfer Antar Rekening Virtual Wallet',
                    'target' => 'Zero saldo ganda atau double-spending pada seluruh transaksi transfer digital.',
                    'description' => 'Pengiriman dua request transfer simultan dalam 5 milidetik berhasil mentransfer dana dua kali dengan saldo awal yang sama.',
                    'category_id' => 9, 'cause_category_id' => 3, 'radar_code' => 'O2',
                    'inherent_p' => 3, 'inherent_i' => 5, 'residual_p' => 1, 'residual_i' => 2, 'impact_amount' => 1350000000,
                    'cause' => 'Ketiadaan transactional row-level lock saat membaca dan mengurangi saldo akun di level database.',
                    'existing_control' => 'Validasi saldo cukup di level application logic memory.',
                    'mitigation' => 'Implementasi pessimistic locking (SELECT FOR UPDATE) atau distributed mutex lock pada seluruh endpoint mutasi saldo.'
                ],
                [
                    'title' => 'Penolakan Akses Layanan bagi Nasabah Pengguna Sistem Operasi Mobile Terbaru (Day-One Bug)',
                    'target' => 'Kesiapan 100% kompatibilitas aplikasi mobile pada hari pertama rilis versi OS Android/iOS terbaru.',
                    'description' => 'Peluncuran versi baru iOS menyebabkan crash pada fitur pemindaian QRIS karena perubahan permission API kamera.',
                    'category_id' => 9, 'cause_category_id' => 2, 'radar_code' => 'O5',
                    'inherent_p' => 4, 'inherent_i' => 3, 'residual_p' => 1, 'residual_i' => 1, 'impact_amount' => 280000000,
                    'cause' => 'Pengujian beta OS tidak dilakukan selama masa developer preview Apple/Google yang berlangsung 3 bulan.',
                    'existing_control' => 'Menghimbau nasabah menunda upgrade versi OS smartphone mereka.',
                    'mitigation' => 'Partisipasi wajib tim mobile engineering dalam Apple/Google beta program dan rilis kompatibilitas H-14 peluncuran resmi.'
                ],
                [
                    'title' => 'Kebocoran Token Autentikasi Pengguna Melalui Celah Log Server dan Monitoring APM',
                    'target' => 'Zero rekaman bearer token dan password pada server log monitoring (Datadog/Elastic).',
                    'description' => 'Log tracing error mencatat request header Authorization Bearer secara utuh sehingga dapat dibaca operator monitoring.',
                    'category_id' => 11, 'cause_category_id' => 1, 'radar_code' => 'C2',
                    'inherent_p' => 3, 'inherent_i' => 5, 'residual_p' => 1, 'residual_i' => 2, 'impact_amount' => 1650000000,
                    'cause' => 'Middleware logging HTTP belum dikonfigurasi untuk menyaring (sanitasi) sensitive header.',
                    'existing_control' => 'Restriksi akses ke dashboard log monitoring menggunakan VPN.',
                    'mitigation' => 'Otomasi redaction header otentikasi pada framework logging dan rotasi token otomatis dengan masa aktif pendek.'
                ],
                [
                    'title' => 'Serangan Injeksi Parameter pada Fitur Deep Linking Aplikasi Mobile Perbankan',
                    'target' => 'Validasi 100% skema URL deep link dan universal link sebelum mengeksekusi navigasi app.',
                    'description' => 'Link jebakan di SMS memicu aplikasi perbankan membuka halaman transfer dengan nomor rekening tujuan penipu yang terisi otomatis.',
                    'category_id' => 19, 'cause_category_id' => 3, 'radar_code' => 'O3',
                    'inherent_p' => 4, 'inherent_i' => 4, 'residual_p' => 1, 'residual_i' => 2, 'impact_amount' => 820000000,
                    'cause' => 'Handler universal link tidak memverifikasi origin domain dan tidak meminta konfirmasi manual pengguna.',
                    'existing_control' => 'Nasabah tetap diwajibkan memasukkan PIN transaksi sebelum transfer tereksekusi.',
                    'mitigation' => 'Pemberlakuan validasi cryptographically signed payload pada deep link dan pembatasan aksi transfer langsung via link.'
                ],
                [
                    'title' => 'Kegagalan Pelacakan Bug Kritis Akibat Crash Reporting Tool Pihak Ketiga Diblokir Firewall',
                    'target' => 'Penyampaian crash report aplikasi mobile secara real-time ke monitoring dashboard pusat.',
                    'description' => 'Ratusan crash aplikasi di segmen korporasi tidak terpantau karena domain reporting terblokir oleh firewall kantor nasabah.',
                    'category_id' => 9, 'cause_category_id' => 3, 'radar_code' => 'O4',
                    'inherent_p' => 3, 'inherent_i' => 3, 'residual_p' => 1, 'residual_i' => 1, 'impact_amount' => 210000000,
                    'cause' => 'Ketergantungan penuh pada layanan cloud publik (Firebase Crashlytics) tanpa in-house fallback endpoint.',
                    'existing_control' => 'Laporan manual keluhan nasabah korporasi ke customer care.',
                    'mitigation' => 'Implementasi self-hosted crash reporting gateway pada domain resmi bank sebagai jalur pelaporan cadangan.'
                ],
                [
                    'title' => 'Kehabisan Kuota Batas Pemanggilan API (Quota Limit Exhaustion) pada Layanan SMS OTP Eksternal',
                    'target' => 'Ketersediaan multi-vendor SMS OTP gateway dengan automated failover zero downtime.',
                    'description' => 'Pengiriman OTP login terhenti total selama 45 menit karena deposit kuota SMS pada agregator telekomunikasi habis.',
                    'category_id' => 13, 'cause_category_id' => 2, 'radar_code' => 'O5',
                    'inherent_p' => 4, 'inherent_i' => 4, 'residual_p' => 1, 'residual_i' => 2, 'impact_amount' => 640000000,
                    'cause' => 'Tidak adanya auto-topup saldo SMS gateway dan ketiadaan multi-vendor failover otomatis.',
                    'existing_control' => 'Alert manual via email jika saldo kuota SMS mendekati 10%.',
                    'mitigation' => 'Integrasi multi-vendor SMS gateway (Telkomsel/Indosat/XL agregator) dengan dynamic load balancing dan auto-failover WhatsApp OTP.'
                ],
                [
                    'title' => 'Pencurian Sesi Pengguna Melalui Celah Session Fixation pada Portal Web Banking',
                    'target' => 'Regenerasi session ID secara otomatis pada setiap perubahan status otentikasi login nasabah.',
                    'description' => 'Attacker menjebak nasabah login menggunakan URL dengan session ID tertentu yang sudah diketahui attacker.',
                    'category_id' => 10, 'cause_category_id' => 3, 'radar_code' => 'O3',
                    'inherent_p' => 3, 'inherent_i' => 4, 'residual_p' => 1, 'residual_i' => 2, 'impact_amount' => 790000000,
                    'cause' => 'Web application framework tidak memanggil fungsi session_regenerate_id() saat nasabah berhasil memasukkan password.',
                    'existing_control' => 'Session timeout otomatis setelah 5 menit inaktif.',
                    'mitigation' => 'Penerapan mandatory session ID regeneration saat login, bind session ke IP/user-agent, dan strict cookie SameSite=Strict.'
                ],
            ],

            // 7. OM - BUSINESS & DIGITAL OPERATION
            'OM - BUSINESS & DIGITAL OPERATION' => [
                [
                    'title' => 'Discrepancy Rekonsiliasi Settlement Merchant QRIS Harian (Unsettled Funds)',
                    'target' => 'Penyelesaian kliring settlement dana merchant QRIS 100% pada H+1 pukul 09:00 WIB.',
                    'description' => 'Selisih data antara switching ASPI dan internal settlement ledger menahan pencairan dana ribuan UMKM.',
                    'category_id' => 7, 'cause_category_id' => 2, 'radar_code' => 'O4',
                    'inherent_p' => 4, 'inherent_i' => 4, 'residual_p' => 1, 'residual_i' => 2, 'impact_amount' => 680000000,
                    'cause' => 'File incoming kliring dari lembaga switching corrupt saat proses transfer SFTP batch malam.',
                    'existing_control' => 'Pemeriksaan manual log SFTP oleh tim operasional kliring.',
                    'mitigation' => 'Implementasi automated 3-way reconciliation engine dan auto-reprocessing batch SFTP.'
                ],
                [
                    'title' => 'Keterlambatan Penanganan Tiket Dispute Transaksi Gagal BI-FAST (Suspended Funds)',
                    'target' => 'Resolusi investigasi dana tertahan BI-FAST maksimal 1x24 jam kerja.',
                    'description' => 'Dana terdebet dari rekening pengirim namun bank penerima belum mengkreditkan dana ke rekening tujuan.',
                    'category_id' => 7, 'cause_category_id' => 3, 'radar_code' => 'O5',
                    'inherent_p' => 4, 'inherent_i' => 3, 'residual_p' => 2, 'residual_i' => 2, 'impact_amount' => 340000000,
                    'cause' => 'Antrean pesan settlement BI-FAST di gateway Bank Indonesia mengalami time-out saat rush hour.',
                    'existing_control' => 'Klaim manual dispute via portal BI-FAST dispute handling.',
                    'mitigation' => 'Otomasi modul resolution ticketing terintegrasi langsung dengan API dispute portal Bank Indonesia.'
                ],
                [
                    'title' => 'Anomali Kliring Cek dan Bilyet Giro Elektronik (SKNBI) Akibat Gambar Warkat Buram',
                    'target' => 'Tingkat keberhasilan pemrosesan warkat kliring debet SKNBI di atas 99.5%.',
                    'description' => 'Penolakan warkat kliring bilyet giro oleh Bank Indonesia karena resolusi hasil scan scanner cabang di bawah 300 DPI.',
                    'category_id' => 7, 'cause_category_id' => 1, 'radar_code' => 'O4',
                    'inherent_p' => 3, 'inherent_i' => 3, 'residual_p' => 1, 'residual_i' => 1, 'impact_amount' => 180000000,
                    'cause' => 'Staf cabang tidak melakukan kalibrasi hardware scanner warkat secara berkala.',
                    'existing_control' => 'Verifikasi visual oleh supervisor operasional cabang sebelum submit batch.',
                    'mitigation' => 'Penerapan auto-DPI quality check pada software capture kliring cabang sebelum paket data dikirim.'
                ],
                [
                    'title' => 'Kegagalan Pemrosesan File Batch Gaji Instansi Pemerintah (Kemenkeu SPAN / Taspen)',
                    'target' => 'Penyaluran gaji pensiunan dan ASN tuntas 100% pada tanggal 1 sebelum pukul 06:00 WIB.',
                    'description' => 'Kesalahan format delimiter file teks dari instansi pemerintah menyebabkan pemrosesan payroll massal tertolak.',
                    'category_id' => 6, 'cause_category_id' => 2, 'radar_code' => 'O5',
                    'inherent_p' => 4, 'inherent_i' => 5, 'residual_p' => 1, 'residual_i' => 2, 'impact_amount' => 1500000000,
                    'cause' => 'Perubahan format data SPAN tanpa pemberitahuan resmi dan ketiadaan pre-validation validator file.',
                    'existing_control' => 'Uji coba upload file H-1 oleh tim operasional institusi.',
                    'mitigation' => 'Penyediaan automated staging validator dengan format converter fleksibel multi-delimiter.'
                ],
                [
                    'title' => 'Selisih Fisik Uang Tunai di Mesin Cash Recycling Machine (CRM) Cabang Utama',
                    'target' => 'Zero selisih saldo kaset CRM dengan rekapitulasi audit transaksi sistem perbankan.',
                    'description' => 'Macetnya mekanik dispenser saat nasabah menyetor uang lusuh menyebabkan selisih fisik kas sebesar puluhan juta rupiah.',
                    'category_id' => 5, 'cause_category_id' => 3, 'radar_code' => 'F1',
                    'inherent_p' => 4, 'inherent_i' => 3, 'residual_p' => 1, 'residual_i' => 1, 'impact_amount' => 220000000,
                    'cause' => 'Sensor pendeteksi uang palsu/lusuh kotor akibat kurangnya jadwal preventive maintenance mesin CRM.',
                    'existing_control' => 'Penghitungan fisik kas CRM harian oleh dual custodian cabang.',
                    'mitigation' => 'Peningkatan frekuensi pembersihan sensor CRM oleh vendor CIT dan otomasi balancing journal.'
                ],
                [
                    'title' => 'Penumpukan Tiket Sanggahan Transaksi Kartu Kredit / Debit (Chargeback Backlog)',
                    'target' => 'Penyelesaian investigasi chargeback internasional Visa/Mastercard sesuai batas waktu 30 hari.',
                    'description' => 'Lonjakan klaim fraud kartu nasabah di merchant luar negeri menyebabkan keterlambatan respon ke asosiasi kartu.',
                    'category_id' => 8, 'cause_category_id' => 2, 'radar_code' => 'F2',
                    'inherent_p' => 4, 'inherent_i' => 4, 'residual_p' => 1, 'residual_i' => 2, 'impact_amount' => 580000000,
                    'cause' => 'Workflow pengumpulan bukti transaksi (chargeback document) masih dilakukan secara manual antar divisi.',
                    'existing_control' => 'Monitoring spreadsheet berkala deadline representment Visa/Mastercard.',
                    'mitigation' => 'Implementasi Chargeback Management System terintegrasi langsung dengan portal VROL/Mastercard Connect.'
                ],
                [
                    'title' => 'Keterlambatan Pembaruan Kurs Transaksi Kartu Debit Luar Negeri di Core Settlement',
                    'target' => 'Penerapan rate settlement valas kartu internasional tepat waktu dan akurat.',
                    'description' => 'Keterlambatan input kurs konversi valas menyebabkan nasabah didebit dengan kurs lama yang merugikan bank.',
                    'category_id' => 5, 'cause_category_id' => 2, 'radar_code' => 'F1',
                    'inherent_p' => 3, 'inherent_i' => 4, 'residual_p' => 1, 'residual_i' => 2, 'impact_amount' => 410000000,
                    'cause' => 'Feed kurs harian dari dealing room ke unit card operation masih dilakukan via email harian.',
                    'existing_control' => 'Pemeriksaan berkala oleh kepala bagian operasional kartu.',
                    'mitigation' => 'Otomasi ingest exchange rate via direct API dealing room ke settlement engine kartu debit.'
                ],
                [
                    'title' => 'Kegagalan Validasi Data Identitas Nasabah pada Proses Pembukaan Rekening Laku Pandai',
                    'target' => '100% nasabah Laku Pandai terverifikasi NIK dan biometrik sesuai batas transaksi inklusi keuangan.',
                    'description' => 'Agen bank di pelosok desa mendaftarkan nasabah tabungan tanpa memindai KTP asli karena sinyal internet lemah.',
                    'category_id' => 8, 'cause_category_id' => 1, 'radar_code' => 'C2',
                    'inherent_p' => 4, 'inherent_i' => 4, 'residual_p' => 1, 'residual_i' => 2, 'impact_amount' => 620000000,
                    'cause' => 'Aplikasi agen Laku Pandai mengizinkan offline store-and-forward tanpa kontrol validasi biometrik lokal.',
                    'existing_control' => 'Pembatasan saldo rekening Laku Pandai maksimal 20 juta rupiah.',
                    'mitigation' => 'Pengetatan fitur verifikasi offline dengan encrypted local caching dan mandatory online sync sebelum aktivasi akun.'
                ],
                [
                    'title' => 'Anomali Perhitungan Pajak Hadiah Program Undian Simpanan Nasabah Tabungan',
                    'target' => 'Kepatuhan 100% pemotongan dan penyetoran pajak undian gratis berhadiah (UGB) Kemensos & DJP.',
                    'description' => 'Kesalahan penghitungan PPh 25% atas hadiah mobil menyebabkan underpayment pajak yang menjadi temuan BPK.',
                    'category_id' => 8, 'cause_category_id' => 2, 'radar_code' => 'C1',
                    'inherent_p' => 3, 'inherent_i' => 4, 'residual_p' => 1, 'residual_i' => 2, 'impact_amount' => 490000000,
                    'cause' => 'Biaya pajak hadiah belum dialokasikan secara akurat pada master budget promo marketing bank.',
                    'existing_control' => 'Verifikasi dokumen pemenang bersama notaris dan perwakilan dinas sosial.',
                    'mitigation' => 'Standardisasi klausul pajak hadiah dalam syarat & ketentuan promo serta auto-escrow pajak sebelum pengumuman.'
                ],
                [
                    'title' => 'Pencurian Fisik Kartu Debit dan PIN Mailer di Kantor Pos Pengiriman Logistik',
                    'target' => 'Zero interception fisik kartu perbankan dalam jalur pengiriman ke alamat nasabah.',
                    'description' => 'Kartu debit pengganti yang dikirim via kurir ekspedisi dicuri dan digunakan berbelanja online sebelum sampai ke nasabah.',
                    'category_id' => 19, 'cause_category_id' => 3, 'radar_code' => 'O3',
                    'inherent_p' => 4, 'inherent_i' => 4, 'residual_p' => 1, 'residual_i' => 2, 'impact_amount' => 530000000,
                    'cause' => 'Kartu dikirim dalam keadaan status aktif tanpa mewajibkan aktivasi mandiri via mobile banking.',
                    'existing_control' => 'Asuransi pengiriman barang berharga dengan pihak ekspedisi.',
                    'mitigation' => 'Kebijakan dormant delivery: kartu dikirim dalam status non-aktif dan wajib diaktivasi menggunakan biometrik app.'
                ],
                [
                    'title' => 'Keterlambatan Proses Rekonsiliasi Settlement Merchant Aggregator Payment Gateway',
                    'target' => 'Penyelesaian rekonsiliasi transaksi aggregator T+1 sebelum pukul 12:00 WIB.',
                    'description' => 'File mutasi dari payment gateway pihak ketiga mengalami perbedaan format kolom transaksi harian.',
                    'category_id' => 7, 'cause_category_id' => 2, 'radar_code' => 'O4',
                    'inherent_p' => 4, 'inherent_i' => 3, 'residual_p' => 1, 'residual_i' => 2, 'impact_amount' => 310000000,
                    'cause' => 'Tidak adanya kontrak Service Level Agreement (SLA) format data standar yang mengikat aggregator.',
                    'existing_control' => 'Penyesuaian manual script parsing oleh tim data entry operasional.',
                    'mitigation' => 'Pemberlakuan API rekonsiliasi terstandarisasi SNAP BI dan penalti denda keterlambatan data pada PKS vendor.'
                ],
                [
                    'title' => 'Kesalahan Eksekusi Pengembalian Saldo (Refund) Transaksi Gagal Merchant Biller Listrik/Air',
                    'target' => 'Pengembalian dana (refund) transaksi biller gagal otomatis kembali ke saldo nasabah dalam 10 menit.',
                    'description' => 'Token PLN tidak terbit namun dana rekening nasabah terpotong tanpa proses refund otomatis oleh sistem.',
                    'category_id' => 7, 'cause_category_id' => 3, 'radar_code' => 'O5',
                    'inherent_p' => 4, 'inherent_i' => 3, 'residual_p' => 1, 'residual_i' => 1, 'impact_amount' => 240000000,
                    'cause' => 'Ketiadaan asynchronous reversal handler saat koneksi gateway biller mengalami timeout respons.',
                    'existing_control' => 'Pengajuan klaim manual nasabah ke call center dengan SLA 3 hari kerja.',
                    'mitigation' => 'Penerapan auto-reversal daemon instan jika confirmation token dari biller tidak diterima dalam 30 detik.'
                ],
                [
                    'title' => 'Inkonsistensi Pencatatan Pendapatan Komisi Transaksi (Fee Based Income) Agen Laku Pandai',
                    'target' => 'Pembagian komisi bagi hasil agen Laku Pandai 100% transparan dan tepat tanggal 1.',
                    'description' => 'Agen perbankan di daerah mengeluhkan insentif bagi hasil transaksi tarik tunai tidak masuk ke rekening tabungan agen.',
                    'category_id' => 5, 'cause_category_id' => 2, 'radar_code' => 'F1',
                    'inherent_p' => 3, 'inherent_i' => 3, 'residual_p' => 1, 'residual_i' => 1, 'impact_amount' => 190000000,
                    'cause' => 'Perhitungan komisi masih dilakukan manual menggunakan rekapitulasi spreadsheet di akhir bulan.',
                    'existing_control' => 'Koreksi penyesuaian komisi pada bulan berikutnya.',
                    'mitigation' => 'Otomasi pembagian komisi secara real-time (instant fee split) langsung ke rekening agen pada setiap transaksi.'
                ],
                [
                    'title' => 'Penyalahgunaan Akun Supervisor Cabang untuk Override Batas Tarik Tunai Tanpa Wewenang',
                    'target' => 'Zero unauthorized transaction override di seluruh jaringan kantor cabang bank.',
                    'description' => 'Teller menggunakan kartu akses dan password supervisor yang tertinggal di meja untuk menyetujui transaksi fiktif.',
                    'category_id' => 10, 'cause_category_id' => 1, 'radar_code' => 'C2',
                    'inherent_p' => 3, 'inherent_i' => 5, 'residual_p' => 1, 'residual_i' => 2, 'impact_amount' => 1200000000,
                    'cause' => 'Sistem otorisasi override teller masih mengandalkan password teks biasa tanpa autentikasi biometrik.',
                    'existing_control' => 'Pemeriksaan rekaman CCTV teller oleh kepala cabang.',
                    'mitigation' => 'Implementasi mandatory supervisor biometric fingerprint approval pada setiap transaksi override di cabang.'
                ],
                [
                    'title' => 'Keterlambatan Verifikasi Dokumen Ekspor-Impor pada Transaksi Pembukaan Letter of Credit (LC)',
                    'target' => 'Verifikasi berkas dokumen perkapalan LC tuntas maksimal 3 hari kerja sesuai standar UCP 600.',
                    'description' => 'Keterlambatan pemeriksaan dokumen bill of lading menyebabkan timbulnya biaya demorage kontainer nasabah korporasi di pelabuhan.',
                    'category_id' => 7, 'cause_category_id' => 2, 'radar_code' => 'O4',
                    'inherent_p' => 4, 'inherent_i' => 4, 'residual_p' => 1, 'residual_i' => 2, 'impact_amount' => 730000000,
                    'cause' => 'Pemeriksaan dokumen perdagangan luar negeri masih bergantung pada staf ahli bersertifikasi CDCS yang terbatas.',
                    'existing_control' => 'Prioritasi dokumen LC nasabah platinum debitur utama.',
                    'mitigation' => 'Pemanfaatan AI Trade Document Inspection untuk mempercepat verifikasi kepatuhan dokumen UCP 600.'
                ],
                [
                    'title' => 'Kerusakan Fisik Mesin Passbook Printer Buku Tabungan di Kantor Cabang Pembantu',
                    'target' => 'Ketersediaan layanan cetak buku tabungan mandiri nasabah di atas 98%.',
                    'description' => 'Macetnya mekanik penarik kertas pada mesin cetak buku tabungan menyebabkan antrean panjang di loker teller.',
                    'category_id' => 9, 'cause_category_id' => 3, 'radar_code' => 'O4',
                    'inherent_p' => 4, 'inherent_i' => 2, 'residual_p' => 1, 'residual_i' => 1, 'impact_amount' => 85000000,
                    'cause' => 'Usia operasional hardware passbook printer sudah melampaui 7 tahun tanpa penggantian peremajaan.',
                    'existing_control' => 'Penyediaan unit printer cadangan di kantor cabang terdekat.',
                    'mitigation' => 'Migrasi layanan ke e-Statement digital paperless dan penggantian bertahap mesin kios cetak buku tabungan mandiri.'
                ],
                [
                    'title' => 'Anomali Pemotongan Biaya Administrasi Bulanan Akun Dormant Dibawah Batas Minimum',
                    'target' => '100% konsistensi pemotongan biaya admin rekening sesuai buku tarif resmi bank.',
                    'description' => 'Rekening pasif terdebet dua kali biaya pemeliharaan akibat scheduler penagihan berjalan ganda di awal bulan.',
                    'category_id' => 7, 'cause_category_id' => 3, 'radar_code' => 'O4',
                    'inherent_p' => 3, 'inherent_i' => 3, 'residual_p' => 1, 'residual_i' => 1, 'impact_amount' => 160000000,
                    'cause' => 'Cron job server penagihan tidak memiliki locking flag pencegah eksekusi ganda.',
                    'existing_control' => 'Koreksi refund manual saat nasabah mengeluh ke cabang.',
                    'mitigation' => 'Implementasi idempotency locking pada billing scheduler dan audit trail pemotongan biaya rekening.'
                ],
                [
                    'title' => 'Keterlambatan Pelaksanaan Audit Kas Fisik Khazanah (Vault) Kantor Kas Pembantu',
                    'target' => 'Inspeksi kas mendadak (surprise cash count) dilakukan minimal satu kali per bulan per cabang.',
                    'description' => 'Audit kas fisik tidak terlaksana selama 4 bulan karena keterbatasan personel tim pemeriksa internal wilayah.',
                    'category_id' => 8, 'cause_category_id' => 1, 'radar_code' => 'C2',
                    'inherent_p' => 3, 'inherent_i' => 4, 'residual_p' => 1, 'residual_i' => 2, 'impact_amount' => 450000000,
                    'cause' => 'Jadwal supervisi cabang tidak terpantau secara otomatis di dashboard kepatuhan operasional.',
                    'existing_control' => 'Berita acara kas opname harian yang ditandatangani supervisor cabang.',
                    'mitigation' => 'Dashboard monitoring kepatuhan cabang digital dengan alert mandatory pelaksanaan surprise cash count.'
                ],
                [
                    'title' => 'Kegagalan Otomasi Penerbitan Bank Garansi Digital pada Pengadaan Barang/Jasa BUMN',
                    'target' => 'Penerbitan e-Bank Garansi terverifikasi secara instan pada portal e-Procurement BUMN.',
                    'description' => 'Sertifikat bank garansi tender proyek gagal diverifikasi rekanan BUMN karena barcode QR verifikasi tidak valid.',
                    'category_id' => 7, 'cause_category_id' => 3, 'radar_code' => 'O4',
                    'inherent_p' => 3, 'inherent_i' => 4, 'residual_p' => 1, 'residual_i' => 2, 'impact_amount' => 520000000,
                    'cause' => 'Sertifikat digital penandatanganan QR code kedaluwarsa pada server e-Guarantee bank.',
                    'existing_control' => 'Penerbitan surat konfirmasi keabsahan bank garansi manual dalam bentuk hardcopy.',
                    'mitigation' => 'Automated monitoring masa berlaku sertifikat tanda tangan digital dan integrasi API e-Procurement resmi.'
                ],
                [
                    'title' => 'Pencemaran Nama Baik Bank Akibat Penanganan Kasus Uang Palsu yang Tidak Sesuai SOP',
                    'target' => '100% penanganan temuan uang palsu di loket teller sesuai prosedur Bank Indonesia.',
                    'description' => 'Teller menuduh nasabah secara terbuka membawa uang palsu tanpa membuat Berita Acara Klarifikasi Uang Palsu yang benar.',
                    'category_id' => 6, 'cause_category_id' => 1, 'radar_code' => 'S1',
                    'inherent_p' => 4, 'inherent_i' => 3, 'residual_p' => 1, 'residual_i' => 1, 'impact_amount' => 280000000,
                    'cause' => 'Kurangnya pelatihan penanganan uang rupiah diragukan keasliannya bagi petugas teller baru.',
                    'existing_control' => 'Pemberian formulir tanda terima penahanan fisik uang palsu.',
                    'mitigation' => 'Pelatihan sertifikasi kebanksentralan ciri keaslian uang rupiah (CIUR) dan SOP pelayanan nasabah ramah.'
                ],
            ],
        ];
    }
}
