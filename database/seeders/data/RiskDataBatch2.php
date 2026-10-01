<?php

namespace Database\Seeders\Data;

class RiskDataBatch2
{
    public static function getData(): array
    {
        return [
            // 8. BT - DIGITAL BANKING
            'BT - DIGITAL BANKING' => [
                [
                    'title' => 'Kegagalan Pemenuhan Standar Keamanan Open API Pembayaran Standar Nasional (SNAP BI)',
                    'target' => 'Kepatuhan 100% terhadap spesifikasi teknis dan tata kelola keamanan SNAP Bank Indonesia.',
                    'description' => 'Inkonsistensi implementasi signature asymmetric HMAC-SHA256 pada header request transaksi API transfer SNAP.',
                    'category_id' => 8, 'cause_category_id' => 3, 'radar_code' => 'C1',
                    'inherent_p' => 4, 'inherent_i' => 5, 'residual_p' => 1, 'residual_i' => 2, 'impact_amount' => 1700000000,
                    'cause' => 'Perbedaan format timestamp ISO 8601 dan public key certificate encoding antara bank dan partner agregator.',
                    'existing_control' => 'Pengujian manual konektivitas sandbox SNAP dengan merchant terpilih.',
                    'mitigation' => 'Standardisasi gateway library SNAP BI, automated signature verifier, dan sandbox conformance testing reguler.'
                ],
                [
                    'title' => 'Eksploitasi Celah Keamanan SIM Swap Fraud untuk Pengambilalihan Akun Mobile Banking',
                    'target' => 'Deteksi instan dan pencegahan pembajakan akun akibat penggantian kartu SIM seluler.',
                    'description' => 'Pelaku menggandakan kartu SIM nasabah di gerai operator lalu meminta reset password aplikasi mobile banking.',
                    'category_id' => 19, 'cause_category_id' => 1, 'radar_code' => 'O3',
                    'inherent_p' => 4, 'inherent_i' => 5, 'residual_p' => 1, 'residual_i' => 3, 'impact_amount' => 2400000000,
                    'cause' => 'Alur recovery password hanya mengandalkan SMS OTP tanpa verifikasi biometrik wajah dan device binding.',
                    'existing_control' => 'Notifikasi email saat terjadi pergantian password akun.',
                    'mitigation' => 'Integrasi API SIM Swap Operator telekomunikasi (GSMA Open Gateway), mandatory biometric e-KYC, dan cooldown period 24 jam.'
                ],
                [
                    'title' => 'Anomali Gagal Debet Transaksi Pembayaran QRIS Cross-Border di Luar Negeri',
                    'target' => 'Success rate transaksi QRIS antarnegara (Singapura, Malaysia, Thailand) minimal 99.2%.',
                    'description' => 'Nasabah Indonesia gagal melakukan scan QRIS di Singapura akibat timeout integrasi switching NETS dengan ASPI.',
                    'category_id' => 7, 'cause_category_id' => 3, 'radar_code' => 'O5',
                    'inherent_p' => 4, 'inherent_i' => 3, 'residual_p' => 2, 'residual_i' => 2, 'impact_amount' => 380000000,
                    'cause' => 'Latensi jaringan interkoneksi switching antar-negara yang melebihi timeout interval 15 detik.',
                    'existing_control' => 'Monitoring dashboard ketersediaan gateway cross-border oleh tim NOC.',
                    'mitigation' => 'Tuning timeout parameter, dynamic routing antar switching partner, dan automated customer push alert.'
                ],
                [
                    'title' => 'Penipuan Social Engineering Berkedok Undangan Pernikahan / APK Kurir di WhatsApp',
                    'target' => 'Penurunan insiden nasabah terinfeksi malware pencuri SMS OTP sebesar 90%.',
                    'description' => 'Nasabah menginstal file APK sniffing malware yang membaca SMS kode OTP login dan menguras saldo tabungan.',
                    'category_id' => 6, 'cause_category_id' => 1, 'radar_code' => 'O3',
                    'inherent_p' => 5, 'inherent_i' => 4, 'residual_p' => 2, 'residual_i' => 2, 'impact_amount' => 1600000000,
                    'cause' => 'Rendahnya literasi keamanan digital nasabah dan absennya proteksi accessibility service abuse pada app.',
                    'existing_control' => 'Kampanye edukasi waspada hoaks dan penipuan di media sosial bank.',
                    'mitigation' => 'Implementasi malware scanner internal pada mobile app yang otomatis memblokir eksekusi jika terdeteksi accessibility abuse.'
                ],
                [
                    'title' => 'Lonjakan Unhandled Timeout pada Fitur Transfer Real-Time BI-FAST Saat Jam Sibuk',
                    'target' => 'Tingkat keberhasilan transaksi BI-FAST di mobile banking di atas 99.7%.',
                    'description' => 'Nasabah panik karena saldo terpotong namun mutasi BI-FAST berstatus pending di aplikasi mobile banking.',
                    'category_id' => 7, 'cause_category_id' => 3, 'radar_code' => 'O2',
                    'inherent_p' => 4, 'inherent_i' => 4, 'residual_p' => 1, 'residual_i' => 2, 'impact_amount' => 850000000,
                    'cause' => 'Antrean koneksi message broker internal bank mengalami throttling saat menerima feedback Bank Indonesia.',
                    'existing_control' => 'Auto-refresh status transaksi setelah 15 menit.',
                    'mitigation' => 'Peningkatan throughput gateway BI-FAST dengan arsitektur microservices async dan auto-polling status update.'
                ],
                [
                    'title' => 'Penyalahgunaan Akun Virtual Account (VA) untuk Penampungan Dana Judi Online',
                    'target' => 'Deteksi dan pemblokiran 100% nomor VA yang terafiliasi jaringan judi daring / scam.',
                    'description' => 'Merchant aggregator memfasilitasi pembuatan ribuan nomor VA dinamis yang disalahgunakan situs judi online.',
                    'category_id' => 8, 'cause_category_id' => 1, 'radar_code' => 'C2',
                    'inherent_p' => 5, 'inherent_i' => 5, 'residual_p' => 1, 'residual_i' => 3, 'impact_amount' => 3000000000,
                    'cause' => 'Lemahnya due diligence profiling merchant agregator dan ketiadaan deteksi transaksi micro-deposit cepat.',
                    'existing_control' => 'Pemblokiran manual nomor rekening berdasarkan surat edaran Kominfo / OJK.',
                    'mitigation' => 'Implementasi AI Anti-Fraud Engine berbasis graf transaksi untuk auto-freeze VA dengan pola transaksi judi online.'
                ],
                [
                    'title' => 'Kegagalan Otomasi Registrasi Rekening Digital Akibat Downtime Server e-KTP Dukcapil',
                    'target' => 'Zero kegagalan onboarding pembukaan rekening digital baru saat server Dukcapil mengalami pemeliharaan.',
                    'description' => 'Calon nasabah tidak dapat membuka rekening tabungan digital selama 6 jam karena koneksi API Dukcapil terputus.',
                    'category_id' => 13, 'cause_category_id' => 3, 'radar_code' => 'O5',
                    'inherent_p' => 4, 'inherent_i' => 4, 'residual_p' => 1, 'residual_i' => 2, 'impact_amount' => 550000000,
                    'cause' => 'Ketiadaan asynchronous queue onboarding dengan verifikasi terjadwal otomatis (deferred verification).',
                    'existing_control' => 'Pemberitahuan kepada nasabah untuk mencoba kembali beberapa saat lagi.',
                    'mitigation' => 'Penerapan async onboarding queue: data disimpan aman terenkripsi dan diverifikasi otomatis saat link Dukcapil pulih.'
                ],
                [
                    'title' => 'Eksploitasi Celah Session Hijacking Akibat Penyimpanan Token Login di Local Storage Browser',
                    'target' => 'Zero insiden pencurian session token pada layanan internet banking retail dan corporate.',
                    'description' => 'Ekstensi browser berbahaya mencuri access token dari local storage dan mengeksekusi transfer dari laptop nasabah.',
                    'category_id' => 19, 'cause_category_id' => 3, 'radar_code' => 'O3',
                    'inherent_p' => 3, 'inherent_i' => 5, 'residual_p' => 1, 'residual_i' => 2, 'impact_amount' => 1450000000,
                    'cause' => 'Single Page Application (SPA) menyimpan authentication token pada web local storage yang rentan dibaca script ekstensi.',
                    'existing_control' => 'Session timeout singkat setelah 3 menit inaktivitas.',
                    'mitigation' => 'Migrasi token storage ke secure HttpOnly SameSite=Strict cookies dengan rotasi token berbasis Web Crypto API.'
                ],
                [
                    'title' => 'Keterlambatan Pengiriman Notifikasi Mutasi Rekening Melalui Kanal WhatsApp Resmi',
                    'target' => 'SLA pengiriman WhatsApp notification transaksi masuk/keluar di bawah 5 detik.',
                    'description' => 'Nasabah mengeluhkan notifikasi transfer gaji baru masuk ke WhatsApp 4 jam setelah dana efektif di rekening.',
                    'category_id' => 7, 'cause_category_id' => 3, 'radar_code' => 'O5',
                    'inherent_p' => 4, 'inherent_i' => 3, 'residual_p' => 1, 'residual_i' => 1, 'impact_amount' => 210000000,
                    'cause' => 'Throttling limit pengiriman pesan dari Meta Cloud API akibat lonjakan volume pesan per detik.',
                    'existing_control' => 'Pemberian SMS notifikasi sebagai jalur alternatif jika WhatsApp gagal.',
                    'mitigation' => 'Peningkatan tier kuota WhatsApp Business API Enterprise dan optimasi priority queue notifikasi finansial.'
                ],
                [
                    'title' => 'Kerentanan Keamanan pada Fitur Cardless Cash Withdrawal di Mesin ATM',
                    'target' => 'Zero pencairan uang tunai tanpa kartu menggunakan kode tarik tunai hasil penipuan / phising.',
                    'description' => 'Penipu memperdaya nasabah untuk memberikan kode tarik tunai 6 digit yang baru digenerate dari mobile banking.',
                    'category_id' => 19, 'cause_category_id' => 1, 'radar_code' => 'O3',
                    'inherent_p' => 4, 'inherent_i' => 4, 'residual_p' => 1, 'residual_i' => 2, 'impact_amount' => 720000000,
                    'cause' => 'Masa berlaku kode penarikan tunai terlalu panjang (60 menit) tanpa konfirmasi lokasi geofencing ATM.',
                    'existing_control' => 'Kewajiban memasukkan nomor HP terdaftar saat eksekusi di ATM.',
                    'mitigation' => 'Perpendek masa aktif token menjadi 15 menit, verifikasi QR scan langsung di layar ATM, dan limitasi nominal harian.'
                ],
                [
                    'title' => 'Anomali Perhitungan Limit Transaksi Harian Akumulatif pada Akun Nasabah Baru (Onboarded)',
                    'target' => 'Penegakan limitasi transaksi transfer harian 100% konsisten sesuai level profil risiko nasabah.',
                    'description' => 'Nasabah baru yang belum verifikasi tatap muka mampu mentransfer dana hingga 100 juta rupiah dalam 1 hari.',
                    'category_id' => 8, 'cause_category_id' => 2, 'radar_code' => 'C2',
                    'inherent_p' => 3, 'inherent_i' => 4, 'residual_p' => 1, 'residual_i' => 2, 'impact_amount' => 650000000,
                    'cause' => 'Flag grading profil risiko nasabah tidak mengunci limit transaksi pada modul transactional limit engine.',
                    'existing_control' => 'Laporan rekapitulasi nasabah over-limit harian oleh compliance.',
                    'mitigation' => 'Enforced limit hard-capping di level backend API berdasarkan tier verifikasi KYC nasabah.'
                ],
                [
                    'title' => 'Kegagalan Otomasi Sinkronisasi Jadwal Tagihan Rekening Listrik Pascabayar (PLN)',
                    'target' => 'Ketersediaan data tagihan PLN 100% akurat pada tanggal 1 setiap bulan kalender.',
                    'description' => 'Nasabah menerima notifikasi tagihan nihil padahal memiliki tunggakan listrik akibat cache biller yang tidak refresh.',
                    'category_id' => 7, 'cause_category_id' => 3, 'radar_code' => 'O4',
                    'inherent_p' => 4, 'inherent_i' => 2, 'residual_p' => 1, 'residual_i' => 1, 'impact_amount' => 130000000,
                    'cause' => 'Cache response inquiry tagihan disimpan terlalu lama tanpa memperhatikan siklus update biller eksternal.',
                    'existing_control' => 'Fitur inquiry ulang manual saat nasabah klik bayar.',
                    'mitigation' => 'Penyesuaian cache policy TTL menjadi maksimal 5 menit pada jam-jam pembaruan tagihan awal bulan.'
                ],
                [
                    'title' => 'Risiko Reputasi Akibat Rating Aplikasi Mobile Banking Merosot di Google Play Store & App Store',
                    'target' => 'Rating aplikasi mobile perbankan bertahan stabil di atas 4.6 bintang di semua app marketplace.',
                    'description' => 'Ribuan ulasan bintang satu membanjiri Play Store pasca update aplikasi yang menyebabkan blank screen pada pengguna Android.',
                    'category_id' => 6, 'cause_category_id' => 2, 'radar_code' => 'S1',
                    'inherent_p' => 4, 'inherent_i' => 4, 'residual_p' => 1, 'residual_i' => 2, 'impact_amount' => 890000000,
                    'cause' => 'Peluncuran rilis baru langsung ke 100% pengguna tanpa tahapan staged rollout bertahap.',
                    'existing_control' => 'Tanggapan manual oleh tim media sosial customer care.',
                    'mitigation' => 'Penerapan mandatory phased rollout (1%, 5%, 20%, 50%, 100%) dengan crash threshold trigger auto-halt.'
                ],
                [
                    'title' => 'Penyalahgunaan Fitur Transfer Dana Menggunakan Berita Acara Transfer untuk Pornografi/SARA',
                    'target' => '100% penyaringan kata-kata terlarang (hate speech, vulgar, judol) pada field berita transfer.',
                    'description' => 'Field catatan transfer nominal 1 rupiah disalahgunakan pelaku cyberbullying untuk mengirimkan pesan ancaman ke korban.',
                    'category_id' => 6, 'cause_category_id' => 1, 'radar_code' => 'S1',
                    'inherent_p' => 4, 'inherent_i' => 3, 'residual_p' => 1, 'residual_i' => 1, 'impact_amount' => 270000000,
                    'cause' => 'Ketiadaan filter kamus kata terlarang (profanity filter) pada input text field berita transfer mobile banking.',
                    'existing_control' => 'Pemblokiran manual rekening pengirim setelah ada laporan dari korban.',
                    'mitigation' => 'Implementasi automated profanity filter dan deteksi anomali transfer nominal mikroskopik berulang.'
                ],
                [
                    'title' => 'Ketidakmampuan Mengakses Layanan Mobile Banking dari Luar Negeri (Geo-Blocking Glitch)',
                    'target' => 'Aksesibilitas global aman bagi nasabah diaspora dan WNI yang bepergian ke luar negeri.',
                    'description' => 'Nasabah prioritas gagal melakukan transfer darurat saat berada di Eropa karena IP address terblokir firewall otomatis.',
                    'category_id' => 7, 'cause_category_id' => 2, 'radar_code' => 'O5',
                    'inherent_p' => 4, 'inherent_i' => 3, 'residual_p' => 1, 'residual_i' => 2, 'impact_amount' => 420000000,
                    'cause' => 'Rule geoblocking WAF terlalu agresif memblokir seluruh range IP address luar negeri tanpa fitur whitelist nasabah.',
                    'existing_control' => 'Pemberitahuan rencana bepergian ke luar negeri manual via kantor cabang.',
                    'mitigation' => 'Penerapan fitur self-service International Travel Notice pada mobile app dan adaptive risk-based authentication.'
                ],
                [
                    'title' => 'Kegagalan Otomasi Penerbitan Bukti Pemotongan Pajak Bunga Tabungan pada Super App',
                    'target' => 'Ketersediaan e-Bupot tahunan untuk SPT Pajak Orang Pribadi langsung dari aplikasi mobile.',
                    'description' => 'Nasabah harus datang ke kantor cabang mengantre hanya untuk meminta lembar fisik bukti potong bunga tabungan.',
                    'category_id' => 7, 'cause_category_id' => 2, 'radar_code' => 'O4',
                    'inherent_p' => 3, 'inherent_i' => 2, 'residual_p' => 1, 'residual_i' => 1, 'impact_amount' => 140000000,
                    'cause' => 'Modul perpajakan nasabah belum terintegrasi dengan modul self-service dokumen di super app perbankan.',
                    'existing_control' => 'Pelayanan cetak dokumen bukti potong di loker customer service cabang.',
                    'mitigation' => 'Integrasi modul e-Tax Statement otomatis terhubung ke sistem perpajakan untuk download instan PDF resmi bertanda tangan digital.'
                ],
                [
                    'title' => 'Kebocoran Data Kontak Buku Telepon Pengguna Akibat Permintaan Izin Akses yang Tidak Perlu',
                    'target' => 'Kepatuhan penuh pada prinsip minimalisasi data (data minimization) UU Perlindungan Data Pribadi.',
                    'description' => 'Aplikasi meminta izin baca seluruh kontak telepon nasabah padahal hanya digunakan untuk fitur transfer nomor kontak.',
                    'category_id' => 8, 'cause_category_id' => 2, 'radar_code' => 'C1',
                    'inherent_p' => 4, 'inherent_i' => 4, 'residual_p' => 1, 'residual_i' => 2, 'impact_amount' => 950000000,
                    'cause' => 'Penggunaan sistem permission lama tanpa mengadopsi Android/iOS Contact Picker API modern.',
                    'existing_control' => 'Pencantuman klausul persetujuan privasi panjang di lembar Syarat & Ketentuan.',
                    'mitigation' => 'Refactoring modul kontak menggunakan native platform Contact Picker tanpa meminta izin akses seluruh phonebook.'
                ],
                [
                    'title' => 'Eksploitasi Celah Keamanan pada Fitur Pembayaran Berulang (Auto-Debit Subscription)',
                    'target' => 'Zero unauthorized recurring payment deductions pada rekening tabungan nasabah.',
                    'description' => 'Merchant nakal terus mendebet dana rekening nasabah meskipun nasabah telah membatalkan langganan di aplikasi.',
                    'category_id' => 7, 'cause_category_id' => 2, 'radar_code' => 'O4',
                    'inherent_p' => 4, 'inherent_i' => 3, 'residual_p' => 1, 'residual_i' => 2, 'impact_amount' => 360000000,
                    'cause' => 'Ketiadaan fitur centralized subscription management di mana nasabah dapat mencabut mandat autodebet mandiri.',
                    'existing_control' => 'Klaim pemblokiran autodebet manual melalui pengaduan call center.',
                    'mitigation' => 'Penyediaan menu Kelola Mandat Langganan pada mobile banking dengan kontrol aktivasi/deaktivasi instan.'
                ],
                [
                    'title' => 'Inkonsistensi Saldo Tabungan Rencana (Auto-Saving Goal) dengan Akun Rekening Induk',
                    'target' => 'Sinkronisasi 100% real-time saldo kantong tabungan rencana dan portofolio nasabah.',
                    'description' => 'Dana autodebet tabungan rencana sudah terpotong dari rekening utama namun saldo kantong target saving tidak bertambah.',
                    'category_id' => 7, 'cause_category_id' => 3, 'radar_code' => 'O4',
                    'inherent_p' => 3, 'inherent_i' => 3, 'residual_p' => 1, 'residual_i' => 1, 'impact_amount' => 230000000,
                    'cause' => 'Kegagalan transaksi atomik pada modul sub-ledger pocket saat database mengalami network jitter.',
                    'existing_control' => 'Penyelesaian manual oleh tim customer care dalam 3 hari kerja.',
                    'mitigation' => 'Penerapan transactional outbox pattern dengan automated compensation event reconciliation.'
                ],
                [
                    'title' => 'Kegagalan Validasi Keaslian Dokumen Bukti Transfer PDF (Manipulasi Fake Transfer Receipt)',
                    'target' => 'Setiap bukti transfer digital dilengkapi verifikasi kriptografi QR code anti-pemalsuan.',
                    'description' => 'Penipu mengedit screenshot bukti transfer mobile banking menggunakan aplikasi editor gambar untuk menipu toko online.',
                    'category_id' => 6, 'cause_category_id' => 1, 'radar_code' => 'S1',
                    'inherent_p' => 5, 'inherent_i' => 3, 'residual_p' => 1, 'residual_i' => 1, 'impact_amount' => 310000000,
                    'cause' => 'Bukti transfer tidak memuat QR code verifikasi dinamis yang dapat dipindai publik untuk memeriksa keabsahan transaksi.',
                    'existing_control' => 'Himbauan kepada merchant untuk selalu mengecek saldo mutasi riil di rekening.',
                    'mitigation' => 'Penyematan dynamic digitally signed QR code pada setiap e-receipt yang terhubung ke portal verifikasi resmi bank.'
                ],
            ],

            // 9. BT - SURROUNDING APPLICATION
            'BT - SURROUNDING APPLICATION' => [
                [
                    'title' => 'Desinkronisasi Database Customer Relationship Management (CRM) dengan Core Banking',
                    'target' => 'Data profil dan riwayat nasabah di CRM sinkron real-time dengan latency di bawah 5 detik.',
                    'description' => 'Perubahan nomor telepon nasabah di cabang tidak terupdate di CRM sehingga penawaran produk terkirim ke nomor lama.',
                    'category_id' => 11, 'cause_category_id' => 3, 'radar_code' => 'O4',
                    'inherent_p' => 4, 'inherent_i' => 3, 'residual_p' => 1, 'residual_i' => 2, 'impact_amount' => 280000000,
                    'cause' => 'Proses integrasi data customer master masih menggunakan batch file export harian tengah malam.',
                    'existing_control' => 'Update manual data profil nasabah oleh relationship manager.',
                    'mitigation' => 'Implementasi Change Data Capture (CDC Debezium) streaming via Kafka dari core ke platform CRM.'
                ],
                [
                    'title' => 'Downtime pada Sistem Document Management System (DMS) Penghambat Pencairan Kredit',
                    'target' => 'Ketersediaan sistem arsip dokumen kredit digital (DMS) minimal 99.8%.',
                    'description' => 'Server penyimpanan berkas jaminan dan akad kredit mengalami storage filesystem corruption saat jam sibuk cabang.',
                    'category_id' => 9, 'cause_category_id' => 3, 'radar_code' => 'O2',
                    'inherent_p' => 3, 'inherent_i' => 4, 'residual_p' => 1, 'residual_i' => 2, 'impact_amount' => 650000000,
                    'cause' => 'Penyimpanan berkas masih menggunakan Network Attached Storage (NAS) lokal tanpa replikasi multi-node.',
                    'existing_control' => 'Penggunaan berkas fisik hardcopy manual di lemari arsip cabang.',
                    'mitigation' => 'Migrasi DMS ke arsitektur Cloud Object Storage (S3-compatible) dengan geo-redundant storage.'
                ],
                [
                    'title' => 'Kegagalan Otomasi Screening Daftar Terduga Teroris (DTTOT) pada Sistem Anti-Money Laundering',
                    'target' => '100% screening otomatis nama nasabah baru terhadap daftar DTTOT dan Proliferasi WMD.',
                    'description' => 'Nama nasabah pembuka rekening baru tidak tersaring karena engine name matching AML mengalami service crash.',
                    'category_id' => 8, 'cause_category_id' => 3, 'radar_code' => 'C2',
                    'inherent_p' => 4, 'inherent_i' => 5, 'residual_p' => 1, 'residual_i' => 2, 'impact_amount' => 2200000000,
                    'cause' => 'Algoritma fuzzy matching kehabisan memori CPU saat memproses lonjakan pembukaan rekening digital.',
                    'existing_control' => 'Pengecekan manual nama nasabah oleh unit compliance AML.',
                    'mitigation' => 'Optimasi algoritma Levenshtein/Jaro-Winkler, auto-restart service failover, dan health check probe.'
                ],
                [
                    'title' => 'Keterlambatan Transmisi Transaksi Dealing Room Treasury ke Back-Office Settlement',
                    'target' => 'Trade capture transaksi pasar uang dan valas terkirim instan dalam 30 detik.',
                    'description' => 'Deal transaksi valas jutaan dolar tertahan di aplikasi dealing room tanpa terkonfirmasi ke bagian settlement.',
                    'category_id' => 5, 'cause_category_id' => 3, 'radar_code' => 'F1',
                    'inherent_p' => 3, 'inherent_i' => 5, 'residual_p' => 1, 'residual_i' => 2, 'impact_amount' => 1800000000,
                    'cause' => 'Message broker middleware antara front-office dealing system dan back-office mengalami koneksi putus.',
                    'existing_control' => 'Pemberitahuan transaksi melalui sambungan telepon langsung antar dealer.',
                    'mitigation' => 'Implementasi high-availability messaging queue dengan persistent delivery guarantee dan alert delay monitor.'
                ],
                [
                    'title' => 'Kegagalan Predictive Dialer pada Aplikasi Collection Menghambat Penagihan Kredit Macet',
                    'target' => 'Efektivitas utilisasi jam kerja staf penagihan kredit tele-collection di atas 85%.',
                    'description' => 'Server telephoni VoIP integrasi dialer mengalami audio latency parah dan panggilan terputus mendadak.',
                    'category_id' => 7, 'cause_category_id' => 3, 'radar_code' => 'O4',
                    'inherent_p' => 4, 'inherent_i' => 3, 'residual_p' => 1, 'residual_i' => 2, 'impact_amount' => 320000000,
                    'cause' => 'Bandwidth SIP trunk telekomunikasi tercekik oleh traffic unduhan file audit di jam yang sama.',
                    'existing_control' => 'Panggilan manual menggunakan handphone kantor penagihan.',
                    'mitigation' => 'Implementasi Quality of Service (QoS) bandwidth prioritas pada SIP trunk dan migrasi ke Cloud Contact Center.'
                ],
                [
                    'title' => 'Inkonsistensi Data Pipeline Enterprise Data Warehouse (EDW) untuk Dashboard Eksekutif',
                    'target' => 'Pembaruan data analitik bisnis harian tuntas sebelum pukul 07:00 WIB setiap pagi.',
                    'description' => 'Laporan kinerja portofolio kredit direksi menampilkan data usang karena proses ETL malam hari gagal total.',
                    'category_id' => 11, 'cause_category_id' => 2, 'radar_code' => 'O2',
                    'inherent_p' => 4, 'inherent_i' => 3, 'residual_p' => 1, 'residual_i' => 2, 'impact_amount' => 290000000,
                    'cause' => 'Perubahan skema tabel pada database transaksi harian tidak diinformasikan ke tim data engineering.',
                    'existing_control' => 'Re-run manual job ETL oleh tim support data warehouse.',
                    'mitigation' => 'Penerapan schema registry contract testing dan automated alerting kegagalan pipeline Apache Airflow.'
                ],
                [
                    'title' => 'Kebocoran Kunci Enkripsi Master Key pada Aplikasi Hardware Security Module (HSM) Internal',
                    'target' => '100% kepatuhan tata kelola cryptographic key management standar PCI-HSM.',
                    'description' => 'Komponen key split pemegang kunci master key disimpan di brankas yang sama oleh pejabat yang berwenang.',
                    'category_id' => 19, 'cause_category_id' => 1, 'radar_code' => 'C2',
                    'inherent_p' => 3, 'inherent_i' => 5, 'residual_p' => 1, 'residual_i' => 2, 'impact_amount' => 3500000000,
                    'cause' => 'Pengabaian prinsip dual-custody dan pemisahan wewenang (split knowledge) pada upacara kunci HSM.',
                    'existing_control' => 'Pencatatan serah terima kunci di buku log fisik keamanan.',
                    'mitigation' => 'Pengetatan SOP key ceremony dengan rekaman video terisolasi, dual custodian brankas terpisah, dan rotasi rutin.'
                ],
                [
                    'title' => 'Kegagalan Otomasi Perhitungan Insentif Penjualan Sales Force pada Aplikasi Kinerja Pegawai',
                    'target' => 'Akurasi 100% perhitungan komisi insentif marketing perbankan setiap bulan.',
                    'description' => 'Ratusan marketing relationship officer melakukan aksi protes karena komisi pencairan kredit tidak terhitung akurat.',
                    'category_id' => 14, 'cause_category_id' => 2, 'radar_code' => 'O6',
                    'inherent_p' => 4, 'inherent_i' => 3, 'residual_p' => 1, 'residual_i' => 1, 'impact_amount' => 340000000,
                    'cause' => 'Formula insentif baru tidak diuji coba dengan sampel data riil sebelum diterapkan pada sistem payroll.',
                    'existing_control' => 'Koreksi penyesuaian komisi manual di bulan berikutnya.',
                    'mitigation' => 'Automated simulation tool perhitungan insentif dan verifikasi komite SDM sebelum cut-off payroll.'
                ],
                [
                    'title' => 'Kerentanan Keamanan pada Modul Integrasi Enterprise Resource Planning (ERP) Keuangan',
                    'target' => 'Zero celah otorisasi pada modul pengajuan dan persetujuan pengadaan barang operasional.',
                    'description' => 'User staf operasional dapat menyetujui Purchase Order senilai ratusan juta karena privilege escalation di ERP.',
                    'category_id' => 10, 'cause_category_id' => 3, 'radar_code' => 'O3',
                    'inherent_p' => 3, 'inherent_i' => 4, 'residual_p' => 1, 'residual_i' => 2, 'impact_amount' => 780000000,
                    'cause' => 'Role mapping pada sistem ERP tidak diselaraskan dengan matrix kewenangan jabatan (Delegation of Authority).',
                    'existing_control' => 'Pemeriksaan invoice manual oleh bagian verifikasi keuangan.',
                    'mitigation' => 'Review triwulanan role-based access control (RBAC) ERP dan audit trail approval berjenjang.'
                ],
                [
                    'title' => 'Keterlambatan Pelaksanaan Backup Otomatis Database Aplikasi Surrounding Kritis',
                    'target' => 'Penyelesaian backup harian 100% terverifikasi integritasnya sebelum pukul 05:00 WIB.',
                    'description' => 'File backup database treasury korup dan tidak dapat direstore saat dilakukan uji coba pemulihan berkala.',
                    'category_id' => 9, 'cause_category_id' => 2, 'radar_code' => 'O2',
                    'inherent_p' => 3, 'inherent_i' => 5, 'residual_p' => 1, 'residual_i' => 2, 'impact_amount' => 1600000000,
                    'cause' => 'Script backup tidak memvalidasi checksum hasil kompresi dan kapasitas disk storage tujuan penuh.',
                    'existing_control' => 'Pengecekan ukuran file backup di server storage.',
                    'mitigation' => 'Implementasi automated backup verification dengan restore sandbox otomatis dan alert kegagalan instan.'
                ],
                [
                    'title' => 'Anomali Integrasi Sistem Manajemen Aset Gedung dan Pemeliharaan ATM (Facility Management)',
                    'target' => 'Response time perbaikan kerusakan fisik ATM di bawah 4 jam sejak tiket dibuat.',
                    'description' => 'AC ruang ATM mati berhari-hari menyebabkan mesin ATM overheating dan rusak tanpa penanganan teknisi.',
                    'category_id' => 12, 'cause_category_id' => 2, 'radar_code' => 'O4',
                    'inherent_p' => 4, 'inherent_i' => 2, 'residual_p' => 1, 'residual_i' => 1, 'impact_amount' => 110000000,
                    'cause' => 'Sensor suhu IoT di gerai ATM tidak terhubung dengan sistem tiket otomatis vendor pemeliharaan.',
                    'existing_control' => 'Laporan manual dari nasabah atau petugas keamanan setempat.',
                    'mitigation' => 'Integrasi IoT temperature monitoring di seluruh gerai ATM dengan auto-dispatch tiket ke vendor servis.'
                ],
                [
                    'title' => 'Kegagalan Otomasi Verifikasi Bukti Kepemilikan Agunan Properti pada Aplikasi Appraisal',
                    'target' => '100% sertifikat tanah agunan kredit terverifikasi valid di portal Kementerian ATR/BPN.',
                    'description' => 'Penilai agunan (appraiser) tertipu sertifikat tanah palsu karena sistem appraisal tidak memverifikasi barcode BPN.',
                    'category_id' => 5, 'cause_category_id' => 1, 'radar_code' => 'C2',
                    'inherent_p' => 3, 'inherent_i' => 5, 'residual_p' => 1, 'residual_i' => 2, 'impact_amount' => 2100000000,
                    'cause' => 'Aplikasi appraisal internal belum terhubung via API dengan layanan pengecekan sertifikat BPN.',
                    'existing_control' => 'Pengecekan fisik manual ke kantor pertanahan setempat oleh notaris rekanan.',
                    'mitigation' => 'Integrasi API resmi ATR/BPN untuk verifikasi elektronik keabsahan sertifikat tanah jaminan kredit.'
                ],
                [
                    'title' => 'Inkonsistensi Saldo Poin Reward Loyalitas Nasabah pada Aplikasi Loyalty Engine',
                    'target' => 'Zero deviasi saldo poin belanja reward nasabah di seluruh katalog merchant rekanan.',
                    'description' => 'Bug pada modul penukaran poin menyebabkan nasabah dapat menukarkan voucher belanja berkali-kali tanpa poin berkurang.',
                    'category_id' => 5, 'cause_category_id' => 3, 'radar_code' => 'F1',
                    'inherent_p' => 3, 'inherent_i' => 4, 'residual_p' => 1, 'residual_i' => 2, 'impact_amount' => 460000000,
                    'cause' => 'Ketiadaan database distributed transaction lock saat penukaran reward simultan dilakukan.',
                    'existing_control' => 'Audit bulanan saldo penukaran poin reward.',
                    'mitigation' => 'Implementasi transaksi atomik pada loyalty point deduction engine dan limitasi penukaran per jam.'
                ],
                [
                    'title' => 'Keterlambatan Ekstraksi Laporan Pajak Transaksi Elektronik pada Modul Pengadaan IT',
                    'target' => 'Penyediaan bukti potong PPh 22/23 pembelian software tepat waktu sebelum penutupan buku.',
                    'description' => 'Vendor software asing menahan lisensi tahunan karena sertifikat pemotongan pajak belum diterbitkan bank.',
                    'category_id' => 8, 'cause_category_id' => 2, 'radar_code' => 'C1',
                    'inherent_p' => 3, 'inherent_i' => 3, 'residual_p' => 1, 'residual_i' => 1, 'impact_amount' => 250000000,
                    'cause' => 'Alur kerja pengadaan software luar negeri belum terintegrasi otomatis dengan sistem e-Tax settlement.',
                    'existing_control' => 'Pemrosesan surat keterangan pajak secara manual melalui konsultan pajak.',
                    'mitigation' => 'Penyusunan modul otomatis pemotongan pajak transaksi digital internasional terhubung ke procurement system.'
                ],
                [
                    'title' => 'Kegagalan Otomasi Notifikasi Batas Kadaluarsa Polis Asuransi Jiwa Kredit Debitur',
                    'target' => '100% debitur aktif memiliki perlindungan asuransi jiwa kredit yang masih berlaku.',
                    'description' => 'Debitur meninggal dunia saat klaim asuransi ditolak perusahaan asuransi karena polis expired tanpa perpanjangan.',
                    'category_id' => 5, 'cause_category_id' => 2, 'radar_code' => 'F2',
                    'inherent_p' => 4, 'inherent_i' => 4, 'residual_p' => 1, 'residual_i' => 2, 'impact_amount' => 820000000,
                    'cause' => 'Aplikasi kredit tidak memiliki reminder otomatis perpanjangan asuransi untuk kredit yang diperpanjang tenornya.',
                    'existing_control' => 'Pemeriksaan berkas kredit saat nasabah mengajukan klaim santunan.',
                    'mitigation' => 'Automated alert renewal asuransi H-60 dan auto-debet premi perpanjangan dari rekening tabungan debitur.'
                ],
                [
                    'title' => 'Ketidakmampuan Aplikasi Whistleblowing System (WBS) Menjaga Kerahasiaan Identitas Pelapor',
                    'target' => 'Kerahasiaan 100% identitas pelapor fraud dan pelanggaran etika terlindungi enkripsi.',
                    'description' => 'Karyawan enggan melaporkan kecurangan karena alamat IP pengirim laporan dapat terlacak oleh administrator IT.',
                    'category_id' => 8, 'cause_category_id' => 3, 'radar_code' => 'C2',
                    'inherent_p' => 3, 'inherent_i' => 4, 'residual_p' => 1, 'residual_i' => 1, 'impact_amount' => 370000000,
                    'cause' => 'Platform WBS dihosting pada infrastruktur internal yang masih mencatat web server access log lengkap.',
                    'existing_control' => 'Jaminan tertulis pimpinan tentang perlindungan saksi dan pelapor.',
                    'mitigation' => 'Migrasi platform WBS ke vendor independen tersertifikasi dengan enkripsi end-to-end tanpa logging IP address.'
                ],
                [
                    'title' => 'Anomali Pemrosesan Data Presensi Kehadiran Pegawai pada Sistem Manajemen SDM (HRIS)',
                    'target' => 'Akurasi 100% data absensi biometrik untuk perhitungan tunjangan kinerja karyawan.',
                    'description' => 'Ribuan data check-in mobile pegawai cabang hilang saat sinkronisasi server HRIS mengalami network timeout.',
                    'category_id' => 14, 'cause_category_id' => 3, 'radar_code' => 'O6',
                    'inherent_p' => 4, 'inherent_i' => 2, 'residual_p' => 1, 'residual_i' => 1, 'impact_amount' => 150000000,
                    'cause' => 'Aplikasi absensi mobile tidak menyimpan offline encrypted cache saat sinyal GPS lemah.',
                    'existing_control' => 'Pengajuan form absensi manual yang disetujui atasan langsung.',
                    'mitigation' => 'Pembaruan aplikasi mobile HRIS dengan offline storage capability dan timestamp cryptographic signature.'
                ],
                [
                    'title' => 'Kegagalan Otomasi Penghapusan Hak Akses Pegawai Resign pada Berbagai Aplikasi Pendukung',
                    'target' => 'Pencabutan 100% user account karyawan non-aktif maksimal 2 jam setelah tanggal efektif berhenti.',
                    'description' => 'Mantan staf marketing masih dapat mengakses database leads nasabah sebulan setelah mengundurkan diri.',
                    'category_id' => 10, 'cause_category_id' => 1, 'radar_code' => 'C2',
                    'inherent_p' => 4, 'inherent_i' => 5, 'residual_p' => 1, 'residual_i' => 2, 'impact_amount' => 1300000000,
                    'cause' => 'Aplikasi surrounding belum terintegrasi dengan Centralized Identity and Access Management (Single Sign-On).',
                    'existing_control' => 'Pemberitahuan email manual dari HRD ke puluhan admin aplikasi setiap ada karyawan resign.',
                    'mitigation' => 'Integrasi seluruh surrounding application ke SSO Active Directory/Okta dengan automated deprovisioning workflow.'
                ],
                [
                    'title' => 'Ketergantungan pada Lisensi Software Analitik Bisnis Komersial dengan Kenaikan Biaya Drastis',
                    'target' => 'Efisiensi biaya lisensi software surrounding dengan transisi ke teknologi open-source matang.',
                    'description' => 'Vendor software visualisasi data menaikkan biaya renewal lisensi tahunan sebesar 300% secara sepihak.',
                    'category_id' => 1, 'cause_category_id' => 2, 'radar_code' => 'F1',
                    'inherent_p' => 4, 'inherent_i' => 3, 'residual_p' => 1, 'residual_i' => 2, 'impact_amount' => 480000000,
                    'cause' => 'Vendor lock-in akibat proprietary format dashboard yang sulit dimigrasikan ke platform lain.',
                    'existing_control' => 'Negosiasi diskon perpanjangan kontrak tahunan.',
                    'mitigation' => 'Penyusunan roadmap migrasi bertahap ke platform business intelligence open-source (Apache Superset/Metabase).'
                ],
                [
                    'title' => 'Kerusakan Data Akibat Eksekusi Script SQL Maintenance Tanpa Prosedur Rollback yang Teruji',
                    'target' => '100% script perubahan data operasional wajib dilengkapi file undo rollback script terverifikasi.',
                    'description' => 'Script pembersihan duplikasi nasabah CRM tidak sengaja menghapus relasi data historis interaksi nasabah.',
                    'category_id' => 11, 'cause_category_id' => 1, 'radar_code' => 'O2',
                    'inherent_p' => 3, 'inherent_i' => 4, 'residual_p' => 1, 'residual_i' => 2, 'impact_amount' => 610000000,
                    'cause' => 'Change Management System tidak mewajibkan lampiran rollback plan teruji untuk maintenance DML.',
                    'existing_control' => 'Restore database dari snapshot backup malam sebelumnya.',
                    'mitigation' => 'Penerapan Change Advisory Board (CAB) strict: penolakan otomatis perubahan database tanpa verified rollback script.'
                ],
            ],

            // 10. IF - SWITCHING & MIDDLEWARE
            'IF - SWITCHING & MIDDLEWARE' => [
                [
                    'title' => 'Kegagalan Parsing Format Pesan Finansial ISO 8583 Akibat Perbedaan Subfield Bit 48',
                    'target' => '100% interoperabilitas message switching ISO 8583 antar jaringan ATM/EDC dan switching partner.',
                    'description' => 'Transaksi tarik tunai nasabah bank lain di mesin ATM ditolak karena ketidaksesuaian parsing tag private data.',
                    'category_id' => 9, 'cause_category_id' => 3, 'radar_code' => 'O2',
                    'inherent_p' => 4, 'inherent_i' => 4, 'residual_p' => 1, 'residual_i' => 2, 'impact_amount' => 550000000,
                    'cause' => 'Partner switching memperbarui format subfield bit 48 tanpa koordinasi masa transisi sandbox testing.',
                    'existing_control' => 'Rollback parameter parser switching ke versi sebelumnya.',
                    'mitigation' => 'Implementasi schema validation dinamis dan automated regression testing protocol ISO 8583.'
                ],
                [
                    'title' => 'Kongesti Antrean Pesan (Queue Congestion) pada Klaster Enterprise Service Bus (ESB)',
                    'target' => 'Throughput ESB minimal 3.000 TPS dengan latency pemrosesan di bawah 50 milidetik.',
                    'description' => 'Penumpukan jutaan pesan transaksi pembayaran di queue ESB memicu cascading timeout pada mobile banking.',
                    'category_id' => 9, 'cause_category_id' => 3, 'radar_code' => 'O2',
                    'inherent_p' => 4, 'inherent_i' => 5, 'residual_p' => 1, 'residual_i' => 2, 'impact_amount' => 1800000000,
                    'cause' => 'Salah satu backend legacy mitra merespons lambat (slow consumer) yang menahan antrean thread worker ESB.',
                    'existing_control' => 'Restart manual node cluster ESB yang mengalami high memory.',
                    'mitigation' => 'Penerapan circuit breaker pattern (Resilience4j) dan dedicated isolated queue per layanan bisnis.'
                ],
                [
                    'title' => 'Kegagalan Rebalancing Partisi Klaster Apache Kafka Saat Pergantian Broker Node',
                    'target' => 'Zero message loss dan consumer lag di bawah 1 detik pada seluruh data pipeline streaming.',
                    'description' => 'Kegagalan satu broker Kafka memicu consumer group storm rebalancing yang menghentikan streaming transaksi 20 menit.',
                    'category_id' => 9, 'cause_category_id' => 3, 'radar_code' => 'O2',
                    'inherent_p' => 3, 'inherent_i' => 4, 'residual_p' => 1, 'residual_i' => 2, 'impact_amount' => 720000000,
                    'cause' => 'Konfigurasi timeout session heartbeat terlalu sensitif terhadap spike latency jaringan internal.',
                    'existing_control' => 'Monitoring dashboard Kafka Grafana dengan alert consumer lag.',
                    'mitigation' => 'Tuning parameter session.timeout.ms, adopsi static group membership, dan upgrade klaster Kafka multi-broker.'
                ],
                [
                    'title' => 'Memory High Watermark Error pada Klaster RabbitMQ Pemroses Notifikasi Finansial',
                    'target' => 'Ketersediaan broker RabbitMQ 99.99% tanpa terjadinya connection blocking.',
                    'description' => 'RabbitMQ memblokir semua koneksi publisher baru karena penggunaan RAM melebihi batas 40% memory threshold.',
                    'category_id' => 9, 'cause_category_id' => 3, 'radar_code' => 'O2',
                    'inherent_p' => 3, 'inherent_i' => 4, 'residual_p' => 1, 'residual_i' => 2, 'impact_amount' => 640000000,
                    'cause' => 'Pesan antrean tidak terambil consumer karena worker service mati mendadak saat antrean menumpuk.',
                    'existing_control' => 'Pembersihan antrean pesan secara manual menggunakan script purge.',
                    'mitigation' => 'Penerapan quorum queues, lazy queue storage to disk, dan auto-scaling consumer pod Kubernetes.'
                ],
                [
                    'title' => 'Kegagalan Handshake TLS / SSL Akibat Keusangan Cipher Suite pada Antarmuka Switching Eksternal',
                    'target' => '100% konektivitas interface switching terenkripsi TLS 1.3 dengan cipher suite modern.',
                    'description' => 'Koneksi ke gateway kartu internasional terputus karena partner menolak koneksi yang masih mendukung TLS 1.0/1.1.',
                    'category_id' => 8, 'cause_category_id' => 3, 'radar_code' => 'C1',
                    'inherent_p' => 4, 'inherent_i' => 4, 'residual_p' => 1, 'residual_i' => 2, 'impact_amount' => 880000000,
                    'cause' => 'Sistem operasi server gateway switching belum diperbarui patch kriptografi OpenSSL-nya.',
                    'existing_control' => 'Permohonan pengecualian sementara kepada pihak prinsipal kartu.',
                    'mitigation' => 'Penonaktifan legacy TLS protocols, standardisasi TLS 1.3 strict cipher suites, dan automated TLS health check.'
                ],
                [
                    'title' => 'Exhaustion Koneksi Database Pool pada Layer Middleware Transaksi Pembayaran',
                    'target' => 'Ketersediaan connection pool middleware selalu di atas 25% kapasitas maksimum.',
                    'description' => 'Ratusan transaksi nasabah berstatus unhandled error karena connection pool database PostgreSQL terpakai 100%.',
                    'category_id' => 9, 'cause_category_id' => 3, 'radar_code' => 'O2',
                    'inherent_p' => 4, 'inherent_i' => 4, 'residual_p' => 1, 'residual_i' => 2, 'impact_amount' => 790000000,
                    'cause' => 'Aplikasi middleware tidak merilis koneksi database kembali ke pool saat terjadi exception timeout eksternal.',
                    'existing_control' => 'Restart service middleware untuk membebaskan koneksi database.',
                    'mitigation' => 'Penerapan connection pooler terpusat (PgBouncer/HikariCP) dengan leak detection threshold dan max lifetime ketat.'
                ],
                [
                    'title' => 'Kegagalan Transmisi Pesan Real-Time Gross Settlement (BI-RTGS) ke Bank Sentral',
                    'target' => 'Submit transaksi BI-RTGS nominal besar tuntas di bawah 1 menit sebelum jam cut-off BI.',
                    'description' => 'Transfer dana korporasi senilai ratusan miliar tertahan karena gateway RTGS bank gagal mengontak server BI.',
                    'category_id' => 5, 'cause_category_id' => 3, 'radar_code' => 'O5',
                    'inherent_p' => 3, 'inherent_i' => 5, 'residual_p' => 1, 'residual_i' => 3, 'impact_amount' => 2500000000,
                    'cause' => 'Antarmuka perangkat kripto smartcard token RTGS mengalami unhandled hardware communication glitch.',
                    'existing_control' => 'Pengalihan manual ke terminal workstation RTGS cadangan.',
                    'mitigation' => 'Penyediaan redundant active-active RTGS gateway hardware token dan automated communication health check.'
                ],
                [
                    'title' => 'Anomali Routing Transaksi Pembayaran Akibat Kerusakan Tabel Routing Dynamic Switch',
                    'target' => 'Akurasi 100% pemilihan rute switching transaksi dengan rute biaya paling efisien (Least Cost Routing).',
                    'description' => 'Transaksi kartu debit terkirim ke jaringan switching internasional berbiaya mahal alih-alih melalui GPN lokal.',
                    'category_id' => 1, 'cause_category_id' => 2, 'radar_code' => 'F1',
                    'inherent_p' => 3, 'inherent_i' => 4, 'residual_p' => 1, 'residual_i' => 2, 'impact_amount' => 430000000,
                    'cause' => 'File konfigurasi routing table tertimpa setting default pasca deployment update perangkat lunak switch.',
                    'existing_control' => 'Audit rekapitulasi tagihan interchange bulanan dari lembaga switching.',
                    'mitigation' => 'Penerapan version-controlled configuration-as-code dan automated validation routing rules sebelum live.'
                ],
                [
                    'title' => 'Kehilangan Pesan Transaksi Finansial (Message Loss) pada Kegagalan Jaringan Unhandled',
                    'target' => 'Zero message loss dengan jaminan pesan finansial terkirim tepat satu kali (Exactly-Once Semantics).',
                    'description' => 'Instruksi pembayaran tagihan hilang saat koneksi jaringan drop di antara layer switching dan core banking.',
                    'category_id' => 7, 'cause_category_id' => 3, 'radar_code' => 'O4',
                    'inherent_p' => 3, 'inherent_i' => 5, 'residual_p' => 1, 'residual_i' => 2, 'impact_amount' => 1650000000,
                    'cause' => 'Middleware menggunakan mode acknowledgment otomatis sebelum data tersimpan persisten ke disk.',
                    'existing_control' => 'Rekonsiliasi transaksi manual di akhir hari kerja.',
                    'mitigation' => 'Penerapan transactional outbox pattern, persistent message queues, dan manual acknowledgement protocol.'
                ],
                [
                    'title' => 'Latensi Tinggi pada Layer API Management Akibat Kegagalan Eksekusi Logging Script yang Lambat',
                    'target' => 'Overhead pemrosesan API gateway selalu berada di bawah 15 milidetik.',
                    'description' => 'Waktu respons API perbankan melonjak dari 100ms menjadi 1200ms karena script audit logging mengeksekusi I/O sinkron.',
                    'category_id' => 9, 'cause_category_id' => 2, 'radar_code' => 'O5',
                    'inherent_p' => 4, 'inherent_i' => 3, 'residual_p' => 1, 'residual_i' => 1, 'impact_amount' => 260000000,
                    'cause' => 'Plugin logging custom pada API gateway mengeksekusi penulisan database remote secara blocking.',
                    'existing_control' => 'Penonaktifan logging detail pada jam-jam sibuk transaksi.',
                    'mitigation' => 'Refactoring plugin logging menjadi non-blocking asynchronous log shipping (FluentBit/Kafka producer).'
                ],
                [
                    'title' => 'Kegagalan Otomasi Failover Klaster Load Balancer Layer 4/7 (F5 / Nginx)',
                    'target' => 'Seamless failover load balancer dengan zero dropped active connections.',
                    'description' => 'Crash pada load balancer master menyebabkan koneksi seluruh nasabah mobile banking terputus serentak.',
                    'category_id' => 9, 'cause_category_id' => 3, 'radar_code' => 'O2',
                    'inherent_p' => 3, 'inherent_i' => 5, 'residual_p' => 1, 'residual_i' => 2, 'impact_amount' => 1950000000,
                    'cause' => 'Sinkronisasi status session state table (connection mirroring) antar node load balancer macet.',
                    'existing_control' => 'Pengalihan manual alamat IP Virtual (VIP) ke node standby.',
                    'mitigation' => 'Pengujian berkala stateful connection mirroring dan audit berkala konfigurasi High Availability VRRP/CARP.'
                ],
                [
                    'title' => 'Eksploitasi Celah Keamanan Injeksi Format Pesan pada Parser XML/JSON Middleware',
                    'target' => '100% sanitasi dan validasi skema payload pada layer gateway middleware.',
                    'description' => 'Attacker mengirimkan payload XML Bomb (Billion Laughs Attack) yang melumpuhkan memori server middleware.',
                    'category_id' => 19, 'cause_category_id' => 3, 'radar_code' => 'O3',
                    'inherent_p' => 3, 'inherent_i' => 5, 'residual_p' => 1, 'residual_i' => 2, 'impact_amount' => 1400000000,
                    'cause' => 'Parser XML pada middleware mengaktifkan pemrosesan eksternal DTD entity secara default.',
                    'existing_control' => 'WAF inspection standar pada traffic HTTP yang masuk.',
                    'mitigation' => 'Hardening parser XML dengan menonaktifkan DTD dan external entities (disallow-doctype-decl true).'
                ],
                [
                    'title' => 'Anomali Sinkronisasi Waktu Server (NTP Drift) pada Node Switching Transaksi',
                    'target' => 'Perbedaan waktu server di seluruh klaster switching selalu di bawah 5 milidetik.',
                    'description' => 'Perbedaan waktu 2 detik antar node switching menyebabkan kegagalan validasi timestamp tanda tangan digital token transaksi.',
                    'category_id' => 9, 'cause_category_id' => 3, 'radar_code' => 'O4',
                    'inherent_p' => 3, 'inherent_i' => 4, 'residual_p' => 1, 'residual_i' => 1, 'impact_amount' => 380000000,
                    'cause' => 'NTP daemon pada beberapa server gateway terputus dari master stratum-1 time server lokal.',
                    'existing_control' => 'Sinkronisasi manual jam server saat ada laporan error audit.',
                    'mitigation' => 'Implementasi Chrony NTP dengan multi-source GPS Stratum clock dan automated alert time drift di atas 50ms.'
                ],
                [
                    'title' => 'Kegagalan Otomasi Reversal pada Gateway Interkoneksi Switching LinkAja / OVO / GoPay',
                    'target' => 'Penyelesaian auto-reversal top-up e-wallet gagal tuntas dalam waktu maksimal 10 detik.',
                    'description' => 'Top-up saldo e-wallet gagal di sisi mitra namun saldo rekening nasabah tidak kembali secara otomatis.',
                    'category_id' => 7, 'cause_category_id' => 3, 'radar_code' => 'O5',
                    'inherent_p' => 4, 'inherent_i' => 4, 'residual_p' => 1, 'residual_i' => 2, 'impact_amount' => 510000000,
                    'cause' => 'Mitra e-wallet mengirimkan kode respons HTTP ambigu (500 Internal Error alih-alih 504 Gateway Timeout).',
                    'existing_control' => 'Klaim manual dispute tiket oleh tim helpdesk perbankan.',
                    'mitigation' => 'Standardisasi error code mapping dan automated status inquiry sebelum eksekusi reversal transaksi.'
                ],
                [
                    'title' => 'Inkompatibilitas Karakter Khusus pada Konversi Payload Format ISO 20022 ke Format JSON',
                    'target' => 'Akurasi 100% serialisasi dan deserialisasi data transaksi antar protokol messaging.',
                    'description' => 'Simbol tanda petik dan ampersand pada nama penerima transfer menyebabkan syntax error parsing JSON di middleware.',
                    'category_id' => 9, 'cause_category_id' => 3, 'radar_code' => 'O4',
                    'inherent_p' => 3, 'inherent_i' => 3, 'residual_p' => 1, 'residual_i' => 1, 'impact_amount' => 190000000,
                    'cause' => 'Library converter XML-to-JSON tidak melakukan sanitasi karakter escape Unicode secara benar.',
                    'existing_control' => 'Manual repair data transaksi yang tertahan di antrean error.',
                    'mitigation' => 'Implementasi strict JSON serializer yang mematuhi standar RFC 8259 dan automated edge-case test suite.'
                ],
                [
                    'title' => 'Keterbatasan Kapasitas Throughput Jaringan Antar Data Center (Inter-DC Latency Spike)',
                    'target' => 'Latency konektivitas dedicated link antar Main DC dan DRC selalu stabil di bawah 3 milidetik.',
                    'description' => 'Spike latensi jaringan inter-DC memperlambat sinkronisasi transaksi database cluster active-active.',
                    'category_id' => 9, 'cause_category_id' => 3, 'radar_code' => 'O2',
                    'inherent_p' => 3, 'inherent_i' => 4, 'residual_p' => 1, 'residual_i' => 2, 'impact_amount' => 670000000,
                    'cause' => 'Penyedia jaringan fiber optik mengalami degradasi signal loss pada kabel bawah laut rute primer.',
                    'existing_control' => 'Pengalihan manual traffic ke jalur leased line sekunder.',
                    'mitigation' => 'Implementasi SD-WAN cerdas dengan dynamic automated link steering berdasarkan real-time jitter and packet loss.'
                ],
                [
                    'title' => 'Kebocoran Kredensial Akses Database Sistem Switching pada Konfigurasi File Teks Polos',
                    'target' => 'Zero plaintext credential pada file konfigurasi seluruh server switching dan middleware.',
                    'description' => 'Username dan password database switching tersimpan dalam file appsettings.json yang dapat dibaca semua staf server.',
                    'category_id' => 19, 'cause_category_id' => 1, 'radar_code' => 'C2',
                    'inherent_p' => 4, 'inherent_i' => 5, 'residual_p' => 1, 'residual_i' => 2, 'impact_amount' => 2100000000,
                    'cause' => 'Kurangnya implementasi tools centralized secret manager pada arsitektur legacy switching.',
                    'existing_control' => 'Pembatasan hak akses file permission OS (chmod 600) di server Linux.',
                    'mitigation' => 'Integrasi HashiCorp Vault untuk dynamic credential injection saat runtime dan rotasi password berkala.'
                ],
                [
                    'title' => 'Kegagalan Pembersihan Data Log Transaksi Sementara (Scratch Pad Table) Menyebabkan Disk Penuh',
                    'target' => 'Tingkat utilisasi storage tabel penampung sementara selalu berada di bawah 60%.',
                    'description' => 'Tabel staging transaksi ISO menampung ratusan juta baris data lawas hingga memperlambat kueri insert transaksi baru.',
                    'category_id' => 9, 'cause_category_id' => 2, 'radar_code' => 'O2',
                    'inherent_p' => 4, 'inherent_i' => 3, 'residual_p' => 1, 'residual_i' => 1, 'impact_amount' => 280000000,
                    'cause' => 'Prosedur pembersihan data (purge table) gagal berjalan karena lock timeout saat volume transaksi tinggi.',
                    'existing_control' => 'Truncate tabel manual oleh DBA saat kapasitas storage menyentuh 95%.',
                    'mitigation' => 'Penerapan rolling partition table dengan auto-drop partisi data yang telah berusia lebih dari 7 hari.'
                ],
                [
                    'title' => 'Anomali Perhitungan Waktu Kadaluarsa Token Akses OAuth 2.0 pada API Gateway',
                    'target' => 'Validasi masa aktif access token OAuth 2.0 100% konsisten tanpa token zombie.',
                    'description' => 'Access token yang seharusnya kedaluwarsa dalam 15 menit masih dapat digunakan bertransaksi hingga berjam-jam.',
                    'category_id' => 10, 'cause_category_id' => 3, 'radar_code' => 'O3',
                    'inherent_p' => 3, 'inherent_i' => 4, 'residual_p' => 1, 'residual_i' => 2, 'impact_amount' => 840000000,
                    'cause' => 'Verifikasi token di API gateway menggunakan clock skew tolerance yang terlalu longgar (60 menit).',
                    'existing_control' => 'Revokasi token manual via database saat ada indikasi pencurian sesi.',
                    'mitigation' => 'Pengetatan parameter clock skew maksimal 30 detik dan implementasi token blacklist real-time via Redis.'
                ],
                [
                    'title' => 'Ketergantungan pada Solusi Switching Komersial Proprietary Tanpa Ketersediaan Source Code',
                    'target' => 'Kemandirian tata kelola sistem switching pembayaran dengan arsitektur terbuka dan terdokumentasi.',
                    'description' => 'Bank tidak dapat menambahkan fitur enkripsi baru pada protokol ATM karena vendor switching menolak kustomisasi kode.',
                    'category_id' => 13, 'cause_category_id' => 2, 'radar_code' => 'S2',
                    'inherent_p' => 3, 'inherent_i' => 4, 'residual_p' => 1, 'residual_i' => 2, 'impact_amount' => 920000000,
                    'cause' => 'Perjanjian lisensi masa lalu tidak mencakup klausul source code escrow dan kepemilikan kustomisasi modul.',
                    'existing_control' => 'Pengajuan Change Request berbayar mahal ke vendor luar negeri.',
                    'mitigation' => 'Penyusunan rencana transisi bertahap menuju modern open switching platform berbasis cloud-native microservices.'
                ],
            ],

            // 11. BS - BUSINESS STRATEGY
            'BS - BUSINESS STRATEGY' => [
                [
                    'title' => 'Erosi Pangsa Pasar Dana Pihak Ketiga (DPK) Akibat Agresi Suku Bunga Bank Digital',
                    'target' => 'Pertumbuhan DPK ritel minimal 8% year-on-year dengan biaya dana (Cost of Fund) terjaga di bawah 3.5%.',
                    'description' => 'Eksodus simpanan nasabah muda ke bank digital yang menawarkan bunga tabungan harian tinggi dan bebas biaya transfer.',
                    'category_id' => 15, 'cause_category_id' => 2, 'radar_code' => 'S2',
                    'inherent_p' => 4, 'inherent_i' => 5, 'residual_p' => 2, 'residual_i' => 3, 'impact_amount' => 3500000000,
                    'cause' => 'Fitur produk tabungan konvensional kurang kompetitif dan pengalaman pengguna aplikasi digital tertinggal.',
                    'existing_control' => 'Program promosi suku bunga khusus bagi nasabah saldo besar.',
                    'mitigation' => 'Peluncuran produk tabungan digital berfitur gamifikasi, loyalitas ekosistem e-commerce, dan efisiensi biaya dana.'
                ],
                [
                    'title' => 'Penurunan Margin Bunga Bersih (Net Interest Margin / NIM) Akibat Kenaikan Suku Bunga Acuan',
                    'target' => 'NIM dipertahankan pada kisaran sehat minimal 4.8% di tengah siklus pengetatan moneter.',
                    'description' => 'Biaya bunga dana mahal (deposito) melonjak tajam namun suku bunga kredit pinjaman tidak dapat dinaikkan secepatnya.',
                    'category_id' => 5, 'cause_category_id' => 2, 'radar_code' => 'F1',
                    'inherent_p' => 4, 'inherent_i' => 4, 'residual_p' => 2, 'residual_i' => 3, 'impact_amount' => 2800000000,
                    'cause' => 'Komposisi struktur dana masih didominasi deposito berbiaya tinggi dibanding Current Account Savings Account (CASA).',
                    'existing_control' => 'Penyesuaian berkala suku bunga simpanan dan pinjaman melalui rapat ALCO.',
                    'mitigation' => 'Fokus akuisisi payroll korporasi dan transaksi ekosistem merchant untuk mendongkrak porsi dana murah CASA murah.'
                ],
                [
                    'title' => 'Kegagalan Eksekusi Strategi Kemitraan Digital (Embedded Finance / Banking-as-a-Service)',
                    'target' => 'Kontribusi pendapatan fee-based kemitraan ekosistem digital minimal 15% dari total FBI bank.',
                    'description' => 'Investasi jutaan dolar pada platform open API BaaS gagal mencapai target akuisisi partner fintech terkemuka.',
                    'category_id' => 15, 'cause_category_id' => 2, 'radar_code' => 'O1',
                    'inherent_p' => 4, 'inherent_i' => 4, 'residual_p' => 2, 'residual_i' => 2, 'impact_amount' => 1600000000,
                    'cause' => 'Proses legal onboarding partner memakan waktu hingga 6 bulan dan dokumentasi teknis API tidak ramah pengembang.',
                    'existing_control' => 'Tim business development khusus kemitraan fintech.',
                    'mitigation' => 'Penyederhanaan proses legal sandbox onboarding mandiri (self-service developer portal) dan percepatan SLA review.'
                ],
                [
                    'title' => 'Ketidaktercapaian Target Portofolio Penyaluran Kredit Berkelanjutan (ESG / Green Finance)',
                    'target' => 'Penyaluran pembiayaan hijau (Green Financing) mencapai minimal 20% dari total loan portfolio sesuai target RBB.',
                    'description' => 'Bank kesulitan menemukan debitur sektor energi terbarukan yang memenuhi kriteria kelayakan bankable.',
                    'category_id' => 15, 'cause_category_id' => 2, 'radar_code' => 'S3',
                    'inherent_p' => 4, 'inherent_i' => 4, 'residual_p' => 2, 'residual_i' => 2, 'impact_amount' => 1200000000,
                    'cause' => 'Minimnya pipeline proyek ramah lingkungan berskala menengah dan ketiadaan skema penjaminan risiko hijau.',
                    'existing_control' => 'Pemberian insentif suku bunga lebih murah untuk proyek bersertifikasi ramah lingkungan.',
                    'mitigation' => 'Kemitraan strategis dengan lembaga multilateral (IFC/ADB) untuk skema blended finance dan de-risking kredit hijau.'
                ],
                [
                    'title' => 'Pembengkakan Biaya Belanja Modal Teknologi (Capex IT Overrun) Tanpa Realisasi Manfaat Bisnis',
                    'target' => 'Return on Investment (ROI) proyek strategis IT mencapai minimal 18% dalam 3 tahun pasca peluncuran.',
                    'description' => 'Biaya implementasi sistem ERP dan cloud membengkak dua kali lipat dari budget awal tanpa peningkatan efisiensi riil.',
                    'category_id' => 1, 'cause_category_id' => 2, 'radar_code' => 'F1',
                    'inherent_p' => 4, 'inherent_i' => 4, 'residual_p' => 2, 'residual_i' => 2, 'impact_amount' => 2200000000,
                    'cause' => 'Lingkup proyek (scope creep) tidak terkendali dan minimnya pengawasan governance Value Realization Office.',
                    'existing_control' => 'Persetujuan anggaran investasi IT oleh Dewan Komisaris.',
                    'mitigation' => 'Penerapan kerangka kerja IT Governance Val IT, tahapan milestone pembiayaan bertahap, dan post-implementation review.'
                ],
                [
                    'title' => 'Kanibalisasi Pendapatan Transaksi Cabang Fisik Akibat Akselerasi Layanan Mandiri Digital',
                    'target' => 'Transformasi jaringan kantor cabang menjadi advisory hub yang menguntungkan tanpa lonjakan opex.',
                    'description' => 'Penurunan tajam transaksi di loket teller cabang membuat biaya operasional gedung kantor cabang menjadi tidak efisien.',
                    'category_id' => 15, 'cause_category_id' => 2, 'radar_code' => 'O1',
                    'inherent_p' => 4, 'inherent_i' => 3, 'residual_p' => 2, 'residual_i' => 2, 'impact_amount' => 850000000,
                    'cause' => 'Konsep kantor cabang masih didesain untuk transaksi operasional manual daripada penjualan produk wealth & lending.',
                    'existing_control' => 'Penutupan bertahap kantor kas pembantu yang merugi.',
                    'mitigation' => 'Rasionalisasi jaringan cabang menjadi Smart Branch berbasis self-service machine dan upskilling teller menjadi sales advisor.'
                ],
                [
                    'title' => 'Ketergantungan Pendapatan Fee-Based Income pada Satu Mitra Asuransi (Bancassurance)',
                    'target' => 'Diversifikasi sumber fee-based income di mana tidak ada satu mitra yang menyumbang lebih dari 30%.',
                    'description' => 'Pemutusan kontrak sepihak oleh mitra asuransi jiwa utama berisiko memangkas 40% target fee-based income non-bunga bank.',
                    'category_id' => 15, 'cause_category_id' => 2, 'radar_code' => 'O1',
                    'inherent_p' => 3, 'inherent_i' => 5, 'residual_p' => 1, 'residual_i' => 3, 'impact_amount' => 2600000000,
                    'cause' => 'Perjanjian kerjasama eksklusif jangka panjang yang membatasi bank menggandeng mitra asuransi lain.',
                    'existing_control' => 'Monitoring berkala target volume penjualan premi asuransi.',
                    'mitigation' => 'Transisi ke model open architecture bancassurance multi-partner dan ekspansi produk wealth management obligasi/reksadana.'
                ],
                [
                    'title' => 'Risiko Kegagalan Integrasi Budaya dan Operasional Pasca Merger / Akuisisi Perusahaan Anak',
                    'target' => 'Realisasi sinergi bisnis pasca akuisisi tuntas sesuai rencana integrasi 100 hari pertama.',
                    'description' => 'Akuisisi multifinance mengalami konflik internal budaya kerja dan resistensi integrasi sistem TI perbankan.',
                    'category_id' => 15, 'cause_category_id' => 1, 'radar_code' => 'S1',
                    'inherent_p' => 4, 'inherent_i' => 4, 'residual_p' => 2, 'residual_i' => 2, 'impact_amount' => 1750000000,
                    'cause' => 'Minimnya program Change Management terstruktur dan pemaksaan sistem perbankan yang kaku pada anak usaha.',
                    'existing_control' => 'Penempatan direksi perwakilan bank induk pada anak perusahaan.',
                    'mitigation' => 'Pembentukan Post-Merger Integration (PMI) Office khusus dengan fokus harmonisasi kompensasi dan integrasi API bertahap.'
                ],
                [
                    'title' => 'Kerugian Finansial Akibat Ketidaktepatan Proyeksi Pertumbuhan Portofolio Kredit Konsumer',
                    'target' => 'Deviasi realisasi penyaluran kredit konsumer maksimal 5% dari target rencana bisnis bank.',
                    'description' => 'Target kredit pemilikan rumah (KPR) meleset jauh dari target akibat lesunya daya beli masyarakat dan inflasi properti.',
                    'category_id' => 5, 'cause_category_id' => 2, 'radar_code' => 'F1',
                    'inherent_p' => 4, 'inherent_i' => 4, 'residual_p' => 2, 'residual_i' => 2, 'impact_amount' => 1400000000,
                    'cause' => 'Model peramalan bisnis hanya mengandalkan tren historis tanpa memperhitungkan variabel makro leading economic indicators.',
                    'existing_control' => 'Revisi target rencana bisnis bank pada periode tengah tahun (RBB penyesuaian).',
                    'mitigation' => 'Penerapan predictive macro analytics modeling dan diversifikasi produk ke segmen payroll loan yang lebih resilient.'
                ],
                [
                    'title' => 'Ancaman Disrupsi Model Bisnis oleh Platform Paylater dan Pinjaman Peer-to-Peer (P2P)',
                    'target' => 'Pertumbuhan portofolio kartu kredit dan kredit mikro bank tetap tumbuh positif di atas 6%.',
                    'description' => 'Generasi muda lebih memilih fitur paylater di aplikasi belanja online dibanding mengajukan kartu kredit konvensional bank.',
                    'category_id' => 15, 'cause_category_id' => 2, 'radar_code' => 'S2',
                    'inherent_p' => 5, 'inherent_i' => 4, 'residual_p' => 2, 'residual_i' => 2, 'impact_amount' => 1900000000,
                    'cause' => 'Proses pengajuan kartu kredit masih membutuhkan dokumen fisik slip gaji dan verifikasi manual hingga 14 hari.',
                    'existing_control' => 'Promosi diskon merchant dining dan tiket pesawat untuk pengguna kartu kredit.',
                    'mitigation' => 'Peluncuran kartu kredit virtual instan (Virtual Credit Card) dengan persetujuan instan 5 menit berbasis alternatif credit scoring.'
                ],
                [
                    'title' => 'Kegagalan Strategi Penetrasi Pembiayaan Rantai Pasok Korporasi (Supply Chain Financing)',
                    'target' => 'Peningkatan utilisasi limit fasilitas supply chain financing minimal 75% dari total komitmen plafon.',
                    'description' => 'Pemasok dan distributor prinsipal korporasi enggan memanfaatkan pembiayaan anjak piutang karena suku bunga tidak menarik.',
                    'category_id' => 15, 'cause_category_id' => 2, 'radar_code' => 'O1',
                    'inherent_p' => 3, 'inherent_i' => 4, 'residual_p' => 1, 'residual_i' => 2, 'impact_amount' => 980000000,
                    'cause' => 'Platform digital supply chain financing sulit digunakan dan belum terintegrasi dengan ERP perusahaan prinsipal.',
                    'existing_control' => 'Kunjungan bersama relationship manager korporasi dan tim produk.',
                    'mitigation' => 'Modernisasi platform SCF berbasis direct ERP host-to-host integration dan pricing berjenjang berbasis credit tiering.'
                ],
                [
                    'title' => 'Ketidaktercapaian Target Rasio Pembiayaan Inklusif Makroprudensial (RPIM) Bank Indonesia',
                    'target' => 'Penyaluran kredit UMKM mencapai minimal 25% dari total portofolio kredit sesuai ketentuan BI.',
                    'description' => 'Bank berisiko terkena sanksi disinsentif giro di Bank Indonesia karena porsi kredit UMKM hanya mencapai 18%.',
                    'category_id' => 8, 'cause_category_id' => 2, 'radar_code' => 'C1',
                    'inherent_p' => 4, 'inherent_i' => 4, 'residual_p' => 1, 'residual_i' => 2, 'impact_amount' => 1350000000,
                    'cause' => 'Tingginya risiko kredit macet (NPL) pada segmen mikro membuat cabang enggan agresif menyalurkan pinjaman.',
                    'existing_control' => 'Penyaluran pembiayaan melalui skema channeling dengan perusahaan fintech P2P lending.',
                    'mitigation' => 'Ekspansi pembiayaan klaster usaha binaan terintegrasi dengan asuransi penjaminan kredit (Jamkrindo/Askrindo).'
                ],
                [
                    'title' => 'Kerugian Finansial Akibat Penempatan Dana Investasi Surat Berharga pada Aset Berimbal Hasil Rendah',
                    'target' => 'Yield portofolio surat berharga treasury melampaui benchmark indeks pasar obligasi (IBPA Index).',
                    'description' => 'Penempatan ekses likuiditas pada instrumen jangka pendek menghasilkan return yang tidak mampu menutup biaya modal bank.',
                    'category_id' => 5, 'cause_category_id' => 2, 'radar_code' => 'F1',
                    'inherent_p' => 3, 'inherent_i' => 4, 'residual_p' => 1, 'residual_i' => 2, 'impact_amount' => 870000000,
                    'cause' => 'Strategi durasi portofolio treasury terlalu konservatif di tengah tren penurunan suku bunga pasar.',
                    'existing_control' => 'Persetujuan komite investasi obligasi sebelum eksekusi transaksi pasar sekunder.',
                    'mitigation' => 'Optimalisasi alokasi aset dinamis (dynamic asset allocation) dengan pemanfaatan obligasi korporasi berperingkat AAA.'
                ],
                [
                    'title' => 'Resiko Kehilangan Nasabah Institusi BUMN Akibat Kebijakan Holdingisasi Keuangan Pemerintah',
                    'target' => 'Retensi 100% nasabah institusi strategis melalui penyediaan solusi cash management terpadu.',
                    'description' => 'Dana simpanan giro BUMN dialihkan ke bank BUMN lain dalam rangka konsolidasi rekening kas holding industri.',
                    'category_id' => 15, 'cause_category_id' => 2, 'radar_code' => 'S1',
                    'inherent_p' => 4, 'inherent_i' => 5, 'residual_p' => 2, 'residual_i' => 3, 'impact_amount' => 3200000000,
                    'cause' => 'Keterbatasan bank dalam menyediakan fasilitas cash pooling multi-bank dan integrasi sistem perpajakan terpusat.',
                    'existing_control' => 'Pendekatan personal tingkat direksi dengan kementerian dan manajemen BUMN terkait.',
                    'mitigation' => 'Pengembangan solusi Advanced Corporate Cash Management Platform dengan fitur automated sweeping dan multi-currency account.'
                ],
                [
                    'title' => 'Kegagalan Strategi Re-branding dan Positioning Merek Bank bagi Segmen Nasabah Milenial',
                    'target' => 'Peningkatan Brand Awareness dan Brand Consideration indeks bank di atas 70% pada segmen usia 20-35 tahun.',
                    'description' => 'Kampanye peluncuran logo dan positioning baru menelan biaya puluhan miliar namun citra bank tetap dinilai jadul.',
                    'category_id' => 6, 'cause_category_id' => 1, 'radar_code' => 'S1',
                    'inherent_p' => 3, 'inherent_i' => 3, 'residual_p' => 1, 'residual_i' => 2, 'impact_amount' => 680000000,
                    'cause' => 'Pesan komunikasi pemasaran tidak terhubung dengan kebutuhan finansial riil generasi muda (rumah pertama/investasi).',
                    'existing_control' => 'Survei kepuasan merek berkala oleh lembaga riset pasar independen.',
                    'mitigation' => 'Penyelarasan kampanye merek dengan aktivasi produk finansial nyata: program KPR millenial dan edukasi investasi micro-saving.'
                ],
                [
                    'title' => 'Keterlambatan Penyesuaian Model Bisnis Terhadap Regulasi Pajak Karbon dan Emisi Industri',
                    'target' => 'Kesiapan portofolio pembiayaan bank terhadap penerapan pajak karbon dan instrumen bursa karbon IDXCarbon.',
                    'description' => 'Debitur industri semen dan batubara mengalami penurunan kemampuan bayar utang akibat penerapan pajak emisi karbon.',
                    'category_id' => 15, 'cause_category_id' => 2, 'radar_code' => 'S3',
                    'inherent_p' => 3, 'inherent_i' => 4, 'residual_p' => 1, 'residual_i' => 2, 'impact_amount' => 1100000000,
                    'cause' => 'Analisis kelayakan kredit belum memasukkan variabel stress test biaya pajak emisi karbon debitur.',
                    'existing_control' => 'Pemberian syarat dokumen izin AMDAL pada saat analisis permohonan kredit.',
                    'mitigation' => 'Implementasi Climate Risk Stress Testing pada portofolio korporasi beremisi tinggi dan transisi ke pembiayaan dekarbonisasi.'
                ],
                [
                    'title' => 'Risiko Litigasi Hukum Terkait Persaingan Usaha Tidak Sehat pada Perjanjian Kemitraan Merchant',
                    'target' => '100% kontrak kerjasama merchant bebas dari klausul tying-in atau anti-persaingan KPPU.',
                    'description' => 'Laporan dugaan monopoli mesin EDC di pusat perbelanjaan memicu penyelidikan resmi dari Komisi Pengawas Persaingan Usaha.',
                    'category_id' => 16, 'cause_category_id' => 2, 'radar_code' => 'C1',
                    'inherent_p' => 3, 'inherent_i' => 5, 'residual_p' => 1, 'residual_i' => 2, 'impact_amount' => 1850000000,
                    'cause' => 'Perjanjian kerjasama merchant mewajibkan merchant hanya menggunakan satu-satunya mesin EDC bank tersebut.',
                    'existing_control' => 'Review legal kontrak merchant oleh divisi hukum sebelum penandatanganan.',
                    'mitigation' => 'Penyesuaian template kontrak merchant agar mematuhi UU Larangan Praktik Monopoli dan pedoman kepatuhan persaingan usaha.'
                ],
                [
                    'title' => 'Kegagalan Monetisasi Aset Data Nasabah (Data Monetization) Akibat Keterbatasan Regulasi Privasi',
                    'target' => 'Pengembangan layanan analitik data bernilai tambah yang 100% mematuhi UU Pelindungan Data Pribadi.',
                    'description' => 'Rencana peluncuran produk insight profil belanja nasabah ke pihak ketiga dibatalkan karena risiko pelanggaran consent privasi.',
                    'category_id' => 11, 'cause_category_id' => 2, 'radar_code' => 'C1',
                    'inherent_p' => 3, 'inherent_i' => 4, 'residual_p' => 1, 'residual_i' => 2, 'impact_amount' => 740000000,
                    'cause' => 'Arsitektur data analitik tidak mengadopsi teknik anonymization data agregat yang terbukti aman secara hukum.',
                    'existing_control' => 'Konsultasi hukum dengan konsultan privasi data eksternal.',
                    'mitigation' => 'Penerapan differential privacy framework dan pemanfaatan data internal secara eksklusif untuk personalisasi produk bank sendiri.'
                ],
                [
                    'title' => 'Ketidaktercapaian Target Profitabilitas Unit Bisnis Wealth Management di Luar Kota Besar',
                    'target' => 'Seluruh sentra priority banking cabang sekunder mencapai status break-even point dalam 18 bulan.',
                    'description' => 'Biaya sewa lounge prioritas dan gaji relationship manager di kota tier-2 tidak sebanding dengan perolehan dana nasabah kaya.',
                    'category_id' => 5, 'cause_category_id' => 2, 'radar_code' => 'F1',
                    'inherent_p' => 4, 'inherent_i' => 3, 'residual_p' => 1, 'residual_i' => 1, 'impact_amount' => 390000000,
                    'cause' => 'Studi kelayakan pasar (feasibility study) salah mengestimasi jumlah populasi High-Net-Worth Individuals di daerah.',
                    'existing_control' => 'Evaluasi kinerja triwulanan unit bisnis wealth management.',
                    'mitigation' => 'Konversi lounge fisik menjadi Digital Priority Lounge dan adopsi model hybrid remote relationship management.'
                ],
                [
                    'title' => 'Resiko Hilangnya Peluang Pertumbuhan Bisnis Akibat Kelambatan Persetujuan Izin Produk Baru di Regulator',
                    'target' => 'Persetujuan izin produk baru OJK/BI diperoleh dalam waktu maksimal 60 hari kerja pasca permohonan.',
                    'description' => 'Peluncuran produk tabungan emas digital tertunda 9 bulan karena lamanya proses revisi dokumen uji coba di OJK.',
                    'category_id' => 8, 'cause_category_id' => 2, 'radar_code' => 'S3',
                    'inherent_p' => 4, 'inherent_i' => 4, 'residual_p' => 1, 'residual_i' => 2, 'impact_amount' => 820000000,
                    'cause' => 'Dokumen kajian risiko dan kesiapan teknologi informasi yang diajukan tidak memenuhi standar regulatory sandbox OJK.',
                    'existing_control' => 'Audiensi berkala tim regulasi bank dengan pengawas perbankan OJK.',
                    'mitigation' => 'Penyusunan dokumen kelayakan produk berbasis panduan inovasi keuangan digital OJK dan proaktif pra-konsultasi formal.'
                ],
            ],

            // 12. CR - CUSTOMER RELATIONS
            'CR - CUSTOMER RELATIONS' => [
                [
                    'title' => 'Lonjakan Penumpukan Antrean Pengaduan Nasabah (Customer Complaint Backlog) Melebihi SLA OJK',
                    'target' => 'Penyelesaian pengaduan nasabah 100% tuntas maksimal 10 hari kerja sesuai ketentuan POJK Perlindungan Konsumen.',
                    'description' => 'Ribuan tiket komplain transaksi gagal kartu debit menumpuk tanpa kepastian penyelesaian akibat minimnya staf analis investigasi.',
                    'category_id' => 8, 'cause_category_id' => 2, 'radar_code' => 'C1',
                    'inherent_p' => 5, 'inherent_i' => 4, 'residual_p' => 1, 'residual_i' => 2, 'impact_amount' => 850000000,
                    'cause' => 'Sistem CRM pengaduan tidak memiliki auto-escalation matrix dan pembagian beban kerja antrean tiket yang otomatis.',
                    'existing_control' => 'Penambahan jam lembur staf customer care pada hari kerja.',
                    'mitigation' => 'Penerapan AI Copilot Customer Service untuk resolusi tiket otomatis dan auto-routing ke tim operasional terkait.'
                ],
                [
                    'title' => 'Kelumpuhan Jalur Telepon Layanan Call Center (Interactive Voice Response / IVR Outage)',
                    'target' => 'Ketersediaan trunk telepon call center 24/7 di atas 99.9% dengan call abandonment rate di bawah 3%.',
                    'description' => 'Penyedia telekomunikasi SIP trunk mengalami kabel fiber optik putus menyebabkan nasabah tidak dapat menghubungi call center saat insiden phishing.',
                    'category_id' => 7, 'cause_category_id' => 3, 'radar_code' => 'O5',
                    'inherent_p' => 4, 'inherent_i' => 5, 'residual_p' => 1, 'residual_i' => 2, 'impact_amount' => 1600000000,
                    'cause' => 'Layanan telepon call center hanya mengandalkan satu operator telekomunikasi tanpa jalur trunk sekunder.',
                    'existing_control' => 'Pengalihan nasabah ke kanal pengaduan email dan chatbot web.',
                    'mitigation' => 'Penyediaan dual-homed SIP trunking dari dua operator telekomunikasi berbeda (Telkom & Indosat) dengan auto-failover.'
                ],
                [
                    'title' => 'Viraliasi Keluhan Nasabah Korban Kejahatan Siber di Media Sosial (Social Media Crisis)',
                    'target' => 'Waktu tanggap (first response time) keluhan nasabah di media sosial di bawah 15 menit.',
                    'description' => 'Postingan video nasabah yang kehilangan uang ratusan juta akibat transfer mencurigakan menjadi trending topic nasional di Twitter/X.',
                    'category_id' => 6, 'cause_category_id' => 1, 'radar_code' => 'S1',
                    'inherent_p' => 5, 'inherent_i' => 5, 'residual_p' => 2, 'residual_i' => 2, 'impact_amount' => 2500000000,
                    'cause' => 'Petugas customer service di cabang merespons keluhan nasabah dengan sikap defensif dan menyalahkan nasabah.',
                    'existing_control' => 'Monitoring keyword media sosial oleh tim Humas kantor pusat.',
                    'mitigation' => 'Pelatihan empati penanganan nasabah korban fraud, pembentukan tim respons krisis medsos 24/7, dan prosedur santunan khusus.'
                ],
                [
                    'title' => 'Kebocoran Data Pribadi Sensitif Nasabah oleh Oknum Petugas Call Center (Rogue Agent)',
                    'target' => 'Zero pencurian atau penjualan data nasabah oleh karyawan internal customer service.',
                    'description' => 'Agen customer care menyalin nomor telepon, saldo, dan mutasi nasabah prioritas lalu menjualnya ke sindikat pinjaman online ilegal.',
                    'category_id' => 11, 'cause_category_id' => 1, 'radar_code' => 'C2',
                    'inherent_p' => 4, 'inherent_i' => 5, 'residual_p' => 1, 'residual_i' => 2, 'impact_amount' => 2900000000,
                    'cause' => 'Aplikasi CRM di call center menampilkan nomor telepon dan identitas lengkap tanpa data masking dan tanpa pelarangan ponsel pribadi.',
                    'existing_control' => 'Pemeriksaan fisik tas dan ponsel di pintu masuk ruang call center.',
                    'mitigation' => 'Penerapan data masking penuh pada CRM, pelarangan ponsel pintar di ruang kerja (clean desk policy), dan audit log akses data.'
                ],
                [
                    'title' => 'Manipulasi Verifikasi Identitas Nasabah via Call Center (Social Engineering Caller Spoofing)',
                    'target' => 'Zero keberhasilan penipu membobol verifikasi data pribadi nasabah saat menghubungi call center.',
                    'description' => 'Penipu menggunakan data KTP korban hasil kebocoran data untuk meminta reset password dan pembukaan blokir rekening korban.',
                    'category_id' => 19, 'cause_category_id' => 1, 'radar_code' => 'O3',
                    'inherent_p' => 4, 'inherent_i' => 5, 'residual_p' => 1, 'residual_i' => 2, 'impact_amount' => 1750000000,
                    'cause' => 'Pertanyaan verifikasi identitas (Knowledge-Based Authentication) masih mengandalkan data statis seperti nama ibu kandung dan tanggal lahir.',
                    'existing_control' => 'Prosedur pertanyaan verifikasi 5 poin data nasabah.',
                    'mitigation' => 'Penerapan voice biometrics authentication pada call center dan push notification approval ke aplikasi mobile terdaftar.'
                ],
                [
                    'title' => 'Halusinasi dan Kesalahan Pemberian Informasi Finansial oleh AI Virtual Assistant / Chatbot',
                    'target' => 'Akurasi jawaban AI chatbot perbankan di atas 99.5% tanpa memberikan rekomendasi finansial yang keliru.',
                    'description' => 'Chatbot bank berbasis Generative AI menjanjikan penghapusan denda kredit kepada nasabah yang menunggak cicilan.',
                    'category_id' => 3, 'cause_category_id' => 3, 'radar_code' => 'S2',
                    'inherent_p' => 4, 'inherent_i' => 4, 'residual_p' => 1, 'residual_i' => 2, 'impact_amount' => 620000000,
                    'cause' => 'Model LLM chatbot belum dibatasi guardrails ketat (Retrieval-Augmented Generation / RAG) pada basis data resmi bank.',
                    'existing_control' => 'Opsi pengalihan chat ke agen manusia jika nasabah mengetik kata "bantuan".',
                    'mitigation' => 'Implementasi NeMo Guardrails strict, pemfilteran output respon berbasis aturan kepatuhan, dan pembatasan wewenang komitmen chatbot.'
                ],
                [
                    'title' => 'Tingginya Tingkat Pengunduran Diri (Turnover Rate) Tenaga Customer Care Call Center',
                    'target' => 'Tingkat retensi agen customer service di atas 85% dengan tingkat kepuasan kerja yang sehat.',
                    'description' => 'Beban kerja berat dan tekanan emosional menangani nasabah komplain memicu resign massal 30% staf call center dalam 2 bulan.',
                    'category_id' => 14, 'cause_category_id' => 1, 'radar_code' => 'O6',
                    'inherent_p' => 4, 'inherent_i' => 3, 'residual_p' => 2, 'residual_i' => 2, 'impact_amount' => 410000000,
                    'cause' => 'Kurangnya program konseling kesehatan mental dan skema insentif penanganan komplain yang tidak sebanding.',
                    'existing_control' => 'Perekrutan tenaga pengganti cepat via vendor outsourcing tenaga kerja.',
                    'mitigation' => 'Program Employee Assistance Program (EAP) konseling psikologis, rotasi shift fleksibel, dan perbaikan jenjang karir.'
                ],
                [
                    'title' => 'Ketidakakuratan Pencatatan Kategori Komplain pada Portal Aplikasi Pengaduan Konsumen (APPK) OJK',
                    'target' => '100% kepatuhan klasifikasi jenis sengketa konsumen pada portal pelaporan APPK OJK.',
                    'description' => 'Komplain sengketa transaksi finansial dicatat keliru sebagai pengaduan informasi produk untuk memperindah metrik cabang.',
                    'category_id' => 8, 'cause_category_id' => 1, 'radar_code' => 'C1',
                    'inherent_p' => 4, 'inherent_i' => 4, 'residual_p' => 1, 'residual_i' => 2, 'impact_amount' => 520000000,
                    'cause' => 'Staf cabang berusaha menghindari sanksi penalti SLA penanganan komplain berat dari kantor pusat.',
                    'existing_control' => 'Sampling berkala laporan pengaduan oleh unit quality assurance customer care.',
                    'mitigation' => 'Otomasi audit klasifikasi tiket komplain menggunakan Natural Language Processing (NLP) berbasis isi pesan nasabah.'
                ],
                [
                    'title' => 'Kegagalan Penyediaan Fasilitas Layanan Nasabah Disabilitas di Sentra Layanan Terpadu',
                    'target' => 'Pemenuhan 100% sarana dan prasarana perbankan ramah disabilitas sesuai Surat Edaran OJK.',
                    'description' => 'Nasabah tunanetra kesulitan mengakses layanan perbankan karena ketiadaan staf yang menguasai bahasa isyarat dan formulir braille.',
                    'category_id' => 8, 'cause_category_id' => 2, 'radar_code' => 'C1',
                    'inherent_p' => 3, 'inherent_i' => 4, 'residual_p' => 1, 'residual_i' => 1, 'impact_amount' => 280000000,
                    'cause' => 'Standarisasi layanan inklusi perbankan belum diterapkan secara merata di seluruh kantor cabang pembantu.',
                    'existing_control' => 'Pendampingan langsung oleh petugas keamanan cabang saat nasabah disabilitas berkunjung.',
                    'mitigation' => 'Pelatihan bahasa isyarat bagi staf customer service cabang dan penyediaan formulir transaksi huruf braille & audio teller.'
                ],
                [
                    'title' => 'Anomali Perhitungan Net Promoter Score (NPS) Akibat Pemalsuan Survei Kepuasan oleh Staf Cabang',
                    'target' => 'Survei kepuasan nasabah (CSAT/NPS) 100% murni berasal dari feedback nasabah riil tanpa manipulasi.',
                    'description' => 'Petugas cabang meminjam smartphone nasabah lansia dan mengisi sendiri rating bintang lima pada survei kepuasan aplikasi.',
                    'category_id' => 3, 'cause_category_id' => 1, 'radar_code' => 'S1',
                    'inherent_p' => 4, 'inherent_i' => 3, 'residual_p' => 1, 'residual_i' => 1, 'impact_amount' => 210000000,
                    'cause' => 'Target penilaian kinerja cabang terlampau berat dan dikaitkan langsung dengan pemotongan bonus tahunan.',
                    'existing_control' => 'Pengecekan anomali jika ada cabang yang mencatat skor kepuasan 100% sempurna beruntun.',
                    'mitigation' => 'Survei kepuasan dikirimkan secara acak via WhatsApp/SMS otomatis pasca nasabah meninggalkan area cabang (geofenced delay).'
                ],
                [
                    'title' => 'Keterlambatan Penanganan Komplain Nasabah Prioritas (High-Net-Worth) Mengakibatkan Penarikan Dana',
                    'target' => 'First contact resolution untuk komplain nasabah prioritas di bawah 2 jam kerja.',
                    'description' => 'Nasabah simpanan puluhan miliar menarik seluruh dananya karena masalah limit kartu debit tidak selesai dalam 3 hari.',
                    'category_id' => 15, 'cause_category_id' => 2, 'radar_code' => 'O1',
                    'inherent_p' => 4, 'inherent_i' => 5, 'residual_p' => 1, 'residual_i' => 2, 'impact_amount' => 3100000000,
                    'cause' => 'Tidak adanya dedicated escalation channel langsung dari Priority Banking Officer ke divisi operasional pusat.',
                    'existing_control' => 'Pemberian gift souvenir kompensasi permohonan maaf kepada nasabah.',
                    'mitigation' => 'Pemberlakuan jalur VIP express resolution ticketing dengan kewenangan override khusus bagi Relationship Manager senior.'
                ],
                [
                    'title' => 'Pencemaran Data Kontak Nasabah Akibat Kesalahan Input Nomor Telepon Pengaduan',
                    'target' => 'Validasi nomor handphone penerima SMS update tiket komplain 100% sesuai master CIF nasabah.',
                    'description' => 'SMS perkembangan investigasi sengketa finansial terkirim ke nomor orang lain karena salah ketik satu digit oleh staf CS.',
                    'category_id' => 11, 'cause_category_id' => 1, 'radar_code' => 'C2',
                    'inherent_p' => 4, 'inherent_i' => 3, 'residual_p' => 1, 'residual_i' => 1, 'impact_amount' => 180000000,
                    'cause' => 'Formulir input tiket pengaduan mengizinkan entri manual nomor telepon alih-alih mengunci nomor terdaftar di CIF.',
                    'existing_control' => 'Konfirmasi ulang nomor telepon secara lisan saat nasabah berada di loket.',
                    'mitigation' => 'System locking: nomor kontak pengaduan wajib ditarik otomatis dari core customer database yang terotentikasi.'
                ],
                [
                    'title' => 'Kegagalan Dokumentasi Rekaman Percakapan Call Center untuk Kebutuhan Pembuktian Hukum',
                    'target' => 'Penyimpanan rekaman audio call center terarsip aman minimal 5 tahun sesuai regulasi OJK.',
                    'description' => 'Rekaman percakapan saat nasabah menyetujui transaksi via telepon hilang saat diminta sebagai bukti di pengadilan.',
                    'category_id' => 16, 'cause_category_id' => 3, 'radar_code' => 'C2',
                    'inherent_p' => 3, 'inherent_i' => 5, 'residual_p' => 1, 'residual_i' => 2, 'impact_amount' => 1400000000,
                    'cause' => 'Server voice recording call center mengalami penimpaan file (overwrite) otomatis akibat disk storage penuh.',
                    'existing_control' => 'Pemindahan manual file rekaman pilihan ke harddisk eksternal.',
                    'mitigation' => 'Arsip otomatis audio rekaman telepon ke immutable cloud storage (WORM storage - Write Once Read Many) terenkripsi.'
                ],
                [
                    'title' => 'Ketidakmampuan Mengatasi Lonjakan Panggilan Saat Terjadi Insiden Sistem Perbankan Down',
                    'target' => 'Kapasitas serap antrean panggilan call center mampu menangani lonjakan hingga 10x beban normal.',
                    'description' => 'Ketika mobile banking down selama 3 jam, 95% nasabah yang menelpon call center mendapat nada sibuk dan terputus.',
                    'category_id' => 7, 'cause_category_id' => 3, 'radar_code' => 'O5',
                    'inherent_p' => 4, 'inherent_i' => 4, 'residual_p' => 2, 'residual_i' => 2, 'impact_amount' => 750000000,
                    'cause' => 'Arsitektur telephony on-premise memiliki keterbatasan jumlah kanal concurrent lines fisik.',
                    'existing_control' => 'Pemberian pesan rekaman suara IVR bahwa sistem sedang dalam pemeliharaan.',
                    'mitigation' => 'Adopsi hybrid cloud telephony elastic scaling yang otomatis menambah kapasitas trunk secara dinamis saat krisis.'
                ],
                [
                    'title' => 'Tingginya Sengketa Nasabah Akibat Ketidakjelasan Informasi Biaya Administrasi Tambahan Produk',
                    'target' => '100% transparansi informasi biaya (Ringkasan Informasi Produk dan Layanan / RIPLAY) sebelum pembukaan rekening.',
                    'description' => 'Nasabah melapor ke Badan Penyelesaian Sengketa Konsumen (BPSK) karena kaget dikenai biaya pemeliharaan kartu debit premium.',
                    'category_id' => 8, 'cause_category_id' => 2, 'radar_code' => 'C1',
                    'inherent_p' => 4, 'inherent_i' => 3, 'residual_p' => 1, 'residual_i' => 1, 'impact_amount' => 290000000,
                    'cause' => 'Petugas customer care tidak menjelaskan lembar dokumen RIPLAY secara detail saat nasabah membuka tabungan di cabang.',
                    'existing_control' => 'Tanda tangan nasabah pada formulir persetujuan pembukaan rekening fisik.',
                    'mitigation' => 'Pemberian mandatory digital RIPLAY checklist dengan verifikasi centang per klausul biaya sebelum persetujuan akhir.'
                ],
                [
                    'title' => 'Kebocoran Percakapan Konsultasi Nasabah Akibat Ruang Layanan Customer Service Tidak Kedap Suara',
                    'target' => 'Privasi 100% informasi saldo dan transaksi nasabah terlindungi saat berkonsultasi di cabang.',
                    'description' => 'Nasabah lain di antrean cabang dapat mendengar dengan jelas jumlah saldo simpanan dan masalah kredit nasabah di meja CS.',
                    'category_id' => 11, 'cause_category_id' => 1, 'radar_code' => 'C2',
                    'inherent_p' => 4, 'inherent_i' => 3, 'residual_p' => 1, 'residual_i' => 1, 'impact_amount' => 170000000,
                    'cause' => 'Desain tata letak meja customer service terbuka tanpa sekat akustik pembatas suara.',
                    'existing_control' => 'Himbauan kepada petugas customer service untuk mengecilkan volume suara bicara.',
                    'mitigation' => 'Pemasangan sound masking acoustic panel dan penyediaan ruang konsultasi privat (private consultation booth) di cabang.'
                ],
                [
                    'title' => 'Anomali Status Pelaporan Pengaduan Selesai Padahal Belum Ada Solusi Konkret bagi Nasabah',
                    'target' => 'Tiket komplain hanya dapat ditutup (closed status) setelah adanya konfirmasi penerimaan solusi dari nasabah.',
                    'description' => 'Agen menutup status tiket pengaduan di CRM agar metrik KPI pribadinya terlihat bagus padahal uang nasabah belum kembali.',
                    'category_id' => 3, 'cause_category_id' => 1, 'radar_code' => 'O4',
                    'inherent_p' => 4, 'inherent_i' => 4, 'residual_p' => 1, 'residual_i' => 1, 'impact_amount' => 350000000,
                    'cause' => 'Sistem CRM tidak memiliki mekanisme konfirmasi dua arah (customer confirmation code) sebelum tiket ditutup.',
                    'existing_control' => 'Telepon sampling acak oleh supervisor terhadap tiket yang telah berstatus resolved.',
                    'mitigation' => 'Penerapan status "Pending Customer Verification": penutupan tiket wajib melalui konfirmasi OTP atau rating klik nasabah.'
                ],
                [
                    'title' => 'Kerentanan Keamanan pada Fasilitas Layanan Video Banking Kiosk di Kantor Cabang Digital',
                    'target' => 'Enkripsi end-to-end pada seluruh sesi audio-video konsultasi nasabah di mesin video teller.',
                    'description' => 'Sesi video call nasabah di bilik mesin kiosk pintar dapat direkam oleh aplikasi third party yang terinstal di komputer mesin.',
                    'category_id' => 19, 'cause_category_id' => 3, 'radar_code' => 'O3',
                    'inherent_p' => 3, 'inherent_i' => 4, 'residual_p' => 1, 'residual_i' => 2, 'impact_amount' => 670000000,
                    'cause' => 'Sistem operasi komputer kiosk tidak menerapkan lock down kiosk mode berbasis hardware.',
                    'existing_control' => 'Pemeriksaan keamanan fisik berkala oleh vendor pengelola mesin kiosk.',
                    'mitigation' => 'Hardening OS kiosk dengan unified write filter (UWF) dan enkripsi jalur WebRTC video streaming bersertifikasi FIPS.'
                ],
                [
                    'title' => 'Penyalahgunaan Data Kontak Darurat Debitur Macet oleh Staf Layanan Pengaduan',
                    'target' => 'Zero kontak penagihan atau komunikasi kepada pihak darurat tanpa persetujuan sah debitur.',
                    'description' => 'Staf customer care memberikan nomor kontak darurat nasabah kepada debt collector pihak ketiga tanpa izin resmi.',
                    'category_id' => 8, 'cause_category_id' => 1, 'radar_code' => 'C1',
                    'inherent_p' => 4, 'inherent_i' => 4, 'residual_p' => 1, 'residual_i' => 2, 'impact_amount' => 780000000,
                    'cause' => 'Akses terhadap data kontak darurat (emergency contact) pada sistem CRM terbuka luas tanpa batasan role khusus.',
                    'existing_control' => 'Pakta integritas kepatuhan etika perbankan yang ditandatangani karyawan.',
                    'mitigation' => 'Enkripsi dan restriksi akses nomor kontak darurat dengan audit logging ketat sesuai aturan POJK penagihan kredit.'
                ],
                [
                    'title' => 'Ketidakmampuan Customer Service Menjelaskan Mekanisme Rekonfirmasi Biometrik Nasabah Lansia',
                    'target' => 'Tingkat kepuasan layanan nasabah senior (lansia) di atas 90% pada saat migrasi layanan digital.',
                    'description' => 'Nasabah pensiunan kebingungan dan kecewa berat karena rekening diblokir akibat gagal verifikasi biometrik berkali-kali.',
                    'category_id' => 6, 'cause_category_id' => 1, 'radar_code' => 'S1',
                    'inherent_p' => 4, 'inherent_i' => 3, 'residual_p' => 1, 'residual_i' => 1, 'impact_amount' => 220000000,
                    'cause' => 'Petugas customer service cabang tidak dibekali panduan asistensi khusus nasabah rentan (vulnerable customer).',
                    'existing_control' => 'Asistensi manual oleh satpam kantor cabang.',
                    'mitigation' => 'Penyusunan SOP Pelayanan Nasabah Prioritas Lansia dan penyediaan opsi verifikasi tatap muka manual di cabang.'
                ],
            ],

            // 13. OM - DATA CENTER OPERATION
            'OM - DATA CENTER OPERATION' => [
                [
                    'title' => 'Kegagalan Baterai Uninterruptible Power Supply (UPS) Saat Terjadi Pemadaman Listrik PLN',
                    'target' => 'Ketersediaan daya listrik data center Tier III 99.982% tanpa terjadinya interupsi operasional.',
                    'description' => 'Saat pasokan listrik PLN padam, 2 modul baterai UPS meledak dan gagal menopang beban server core banking.',
                    'category_id' => 12, 'cause_category_id' => 3, 'radar_code' => 'O2',
                    'inherent_p' => 4, 'inherent_i' => 5, 'residual_p' => 1, 'residual_i' => 2, 'impact_amount' => 3200000000,
                    'cause' => 'Pengujian kapasitas baterai (battery discharge load test) tidak dilaksanakan sesuai jadwal pemeliharaan triwulanan.',
                    'existing_control' => 'Sistem genset diesel ganda dengan sakelar transfer otomatis (ATS).',
                    'mitigation' => 'Penggantian baterai VRLA ke Lithium-ion bersistem Battery Management System (BMS) cerdas dan uji beban rutin.'
                ],
                [
                    'title' => 'Kebocoran Pipa Pendingin Presisi (Precision Air Conditioning / PAC) di Atas Rak Server Kritis',
                    'target' => 'Suhu ruang data center selalu stabil pada rentang 18°C - 24°C dengan kelembaban 45% - 60%.',
                    'description' => 'Kondensasi air pada pipa AC pendingin menetes ke atas rak server database transaksi dan memicu korsleting listrik.',
                    'category_id' => 12, 'cause_category_id' => 3, 'radar_code' => 'O2',
                    'inherent_p' => 3, 'inherent_i' => 5, 'residual_p' => 1, 'residual_i' => 2, 'impact_amount' => 2400000000,
                    'cause' => 'Pipa drainase air pendingin tersumbat debu dan sensor deteksi kebocoran air (water leak detector) rusak.',
                    'existing_control' => 'Inspeksi patroli keliling fisik ruang server setiap 2 jam oleh operator.',
                    'mitigation' => 'Pemasangan rope water leak detector di sepanjang jalur bawah lantai dan pemindahan jalur pipa jauh dari rak server.'
                ],
                [
                    'title' => 'Pelepasan Gas Pemadam Kebakaran (FM-200 / Novec 1230) Akibat Sinyal Palsu (False Alarm)',
                    'target' => 'Zero inadvertent discharge pada sistem pencegah kebakaran ruang data center.',
                    'description' => 'Debu pekerjaan renovasi ruangan sebelah memicu sensor optik kebakaran dan menyemprotkan gas pemadam tabung berharga ratusan juta.',
                    'category_id' => 12, 'cause_category_id' => 3, 'radar_code' => 'O2',
                    'inherent_p' => 3, 'inherent_i' => 4, 'residual_p' => 1, 'residual_i' => 1, 'impact_amount' => 650000000,
                    'cause' => 'Detektor asap tidak diisolasi (disarm) saat pekerjaan konstruksi fisik berlangsung di area data center.',
                    'existing_control' => 'Sistem deteksi ganda (cross-zone smoke detection) sebelum gas menyemprot.',
                    'mitigation' => 'Penerapan Very Early Smoke Detection Apparatus (VESDA) terkalibrasi dan permit-to-work isolasi sensor saat maintenance.'
                ],
                [
                    'title' => 'Pelanggaran Akses Fisik Ruang Server Tanpa Otorisasi oleh Teknisi Pihak Ketiga (Tailgating)',
                    'target' => '100% kepatuhan pencatatan biometrik dan pengawalan fisik untuk setiap orang asing di data center.',
                    'description' => 'Teknisi vendor pendingin masuk ke zona ruang server core banking tanpa didampingi staf data center bank.',
                    'category_id' => 12, 'cause_category_id' => 1, 'radar_code' => 'C2',
                    'inherent_p' => 4, 'inherent_i' => 4, 'residual_p' => 1, 'residual_i' => 2, 'impact_amount' => 900000000,
                    'cause' => 'Pintu mantrap pengaman data center mengalami kerusakan engsel otomatis sehingga tidak terkunci sempurna.',
                    'existing_control' => 'Buku tamu pencatatan satpam dan kartu akses RFID.',
                    'mitigation' => 'Penerapan interlocking mantrap door dengan otentikasi biometrik ganda dan pengawalan mandatory staf DC.'
                ],
                [
                    'title' => 'Kegagalan Starter Otomatis Mesin Generator Diesel Saat Terjadi Blackout Total Jaringan PLN',
                    'target' => 'Genset otomatis mengambil alih beban listrik dalam waktu maksimal 15 detik setelah listrik padam.',
                    'description' => 'Bahan bakar solar pada tangki genset terkontaminasi air dan endapan lumpur sehingga mesin generator gagal menyala.',
                    'category_id' => 12, 'cause_category_id' => 3, 'radar_code' => 'O2',
                    'inherent_p' => 3, 'inherent_i' => 5, 'residual_p' => 1, 'residual_i' => 2, 'impact_amount' => 2800000000,
                    'cause' => 'Tidak dilakukannya proses pemurnian bahan bakar (fuel polishing) dan rotasi solar secara berkala.',
                    'existing_control' => 'Uji pemanasan mesin genset (warming up) mingguan tanpa beban listrik.',
                    'mitigation' => 'Pemasangan automated fuel polishing system, sensor pemantau kualitas solar, dan uji beban bulanan menggunakan load bank.'
                ],
                [
                    'title' => 'Trip Sakelar Daya Listrik (Power Distribution Unit / PDU Breaker Overload) pada Rak Server',
                    'target' => 'Beban daya listrik per PDU rak server selalu terjaga di bawah 70% kapasitas batas maksimal.',
                    'description' => 'Pemasangan server GPU artificial intelligence baru pada satu rak memicu overload breaker PDU dan mematikan rak server.',
                    'category_id' => 9, 'cause_category_id' => 1, 'radar_code' => 'O2',
                    'inherent_p' => 3, 'inherent_i' => 4, 'residual_p' => 1, 'residual_i' => 2, 'impact_amount' => 750000000,
                    'cause' => 'Staf instalasi server tidak menghitung total daya konsumsi (power budget) sebelum memasang perangkat keras baru.',
                    'existing_control' => 'Sistem dual power supply (jalur A dan B) pada setiap server.',
                    'mitigation' => 'Penerapan Data Center Infrastructure Management (DCIM) untuk kalkulasi daya otomatis sebelum persetujuan instalasi.'
                ],
                [
                    'title' => 'Kerusakan Hardware Akibat Titik Panas Lokal (Hot Spots) pada Lorong Server Densitas Tinggi',
                    'target' => 'Perbedaan suhu antar rak server tidak melebihi 3°C dengan distribusi udara dingin merata.',
                    'description' => 'Server blade penyimpanan data sering restart sendiri karena suhu di bagian atas rak mencapai 38°C.',
                    'category_id' => 12, 'cause_category_id' => 2, 'radar_code' => 'O2',
                    'inherent_p' => 4, 'inherent_i' => 3, 'residual_p' => 1, 'residual_i' => 1, 'impact_amount' => 320000000,
                    'cause' => 'Banyak slot rak kosong yang tidak dipasangi blanking panel sehingga udara panas berputar kembali ke lorong dingin.',
                    'existing_control' => 'Pengukuran suhu manual menggunakan thermometer inframerah.',
                    'mitigation' => 'Penerapan Cold Aisle Containment System tertutup penuh dan pemasangan mandatory blanking panel di seluruh rak.'
                ],
                [
                    'title' => 'Bencana Banjir Merendam Ruang Panel Listrik dan Generator Data Center di Lantai Dasar',
                    'target' => 'Ketahanan fisik data center terhadap risiko bencana banjir 100 tahunan.',
                    'description' => 'Hujan ekstrem meluapkan sungai di samping gedung operasional dan menenggelamkan pompa bahan bakar genset.',
                    'category_id' => 12, 'cause_category_id' => 4, 'radar_code' => 'O2',
                    'inherent_p' => 3, 'inherent_i' => 5, 'residual_p' => 1, 'residual_i' => 2, 'impact_amount' => 3800000000,
                    'cause' => 'Peralatan daya listrik vital ditempatkan di area basement tanpa dinding tanggul penahan banjir yang memadai.',
                    'existing_control' => 'Pompa submersible otomatis di kolam penampungan air basement.',
                    'mitigation' => 'Peninggian posisi intake udara genset, pemasangan flood barrier otomatis, dan relokasi bertahap perangkat daya ke lantai atas.'
                ],
                [
                    'title' => 'Serangan Hama Tikus Menggigit Kabel Serat Optik Backbone di Bawah Lantai Raised Floor',
                    'target' => 'Integritas 100% kabel fisik infrastruktur terlindungi dari ancaman hama pengerat.',
                    'description' => 'Koneksi jaringan antar rak server terputus tiba-tiba akibat kabel patch cord fiber optik putus digigit tikus.',
                    'category_id' => 12, 'cause_category_id' => 4, 'radar_code' => 'O2',
                    'inherent_p' => 3, 'inherent_i' => 4, 'residual_p' => 1, 'residual_i' => 1, 'impact_amount' => 450000000,
                    'cause' => 'Lubang penetrasi kabel antar ruangan tidak ditutup (unsealed) dengan bahan firestop anti-rodent.',
                    'existing_control' => 'Jasa pest control bulanan penyemprotan area gedung.',
                    'mitigation' => 'Penutupan seluruh lubang kabel menggunakan mortar firestop bersertifikasi dan pemasangan ultrasonic rodent repeller.'
                ],
                [
                    'title' => 'Human Error Tertekan Tombol Emergency Power Off (EPO) oleh Pekerja Pembersih Ruangan',
                    'target' => 'Zero pemadaman listrik darurat data center yang diakibatkan oleh ketidaksengajaan manusia.',
                    'description' => 'Pembersih ruangan tidak sengaja menabrak tombol EPO darurat dengan gagang kain pel dan memadamkan seluruh daya DC.',
                    'category_id' => 12, 'cause_category_id' => 1, 'radar_code' => 'O2',
                    'inherent_p' => 2, 'inherent_i' => 5, 'residual_p' => 1, 'residual_i' => 1, 'impact_amount' => 3500000000,
                    'cause' => 'Tombol pemutus daya darurat (EPO) tidak dilengkapi penutup pelindung transparan dengan alarm lokal.',
                    'existing_control' => 'Tulisan peringatan dilarang menyentuh di bawah tombol EPO.',
                    'mitigation' => 'Pemasangan lockable flip-cover berengsel ganda dengan alarm suara saat tutup tombol dibuka.'
                ],
                [
                    'title' => 'Kegagalan Pemantauan Sensor Lingkungan Akibat Kerusakan Server Building Management System (BMS)',
                    'target' => 'Ketersediaan sistem pemantauan lingkungan data center (BMS/DCIM) 24/7 di atas 99.9%.',
                    'description' => 'Operator tidak menyadari kenaikan suhu di lorong server karena aplikasi BMS hang dan tidak memperbarui grafik telemetry.',
                    'category_id' => 9, 'cause_category_id' => 3, 'radar_code' => 'O2',
                    'inherent_p' => 3, 'inherent_i' => 4, 'residual_p' => 1, 'residual_i' => 2, 'impact_amount' => 540000000,
                    'cause' => 'Aplikasi BMS berjalan pada server tunggal (single point of failure) tanpa konfigurasi redundant server.',
                    'existing_control' => 'Inspeksi temperatur berkala oleh petugas shift operasi.',
                    'mitigation' => 'Implementasi sistem BMS berbasis klaster redundan dengan pengiriman alert darurat mandiri via SMS gateway independen.'
                ],
                [
                    'title' => 'Keterlambatan Pengiriman Bahan Bakar Minyak Solar Saat Terjadi Pemadaman Listrik Berkepanjangan',
                    'target' => 'Cadangan bahan bakar genset di lokasi selalu mencukupi kebutuhan operasi minimal 72 jam non-stop.',
                    'description' => 'Truk tangki bahan bakar terjebak kemacetan parah saat pemadaman listrik massal kota metropolitan berlangsung 18 jam.',
                    'category_id' => 13, 'cause_category_id' => 2, 'radar_code' => 'O5',
                    'inherent_p' => 3, 'inherent_i' => 5, 'residual_p' => 1, 'residual_i' => 2, 'impact_amount' => 2600000000,
                    'cause' => 'Bank hanya menandatangani kontrak pasokan BBM dengan satu vendor penyedia lokal tanpa SLA pengiriman krisis.',
                    'existing_control' => 'Kapasitas tangki harian genset mencukupi untuk durasi 12 jam operasi.',
                    'mitigation' => 'Pembangunan tangki timbun solar cadangan kapasitas 72 jam dan kontrak prioritas pasokan dengan Pertamina Patra Niaga.'
                ],
                [
                    'title' => 'Inkompatibilitas Beban Listrik Non-Linier Server Modern Terhadap Harmonisa Gelombang Daya Listrik',
                    'target' => 'Total Harmonic Distortion (THD) arus listrik data center selalu terjaga di bawah 5%.',
                    'description' => 'Tingginya distorsi harmonisa arus listrik menyebabkan transformator isolasi data center mendengung dan overheating.',
                    'category_id' => 12, 'cause_category_id' => 3, 'radar_code' => 'O2',
                    'inherent_p' => 3, 'inherent_i' => 4, 'residual_p' => 1, 'residual_i' => 1, 'impact_amount' => 480000000,
                    'cause' => 'Banyaknya power supply server jenis switched-mode tanpa pemasangan filter harmonisa aktif pada panel PDU.',
                    'existing_control' => 'Pemeriksaan kualitas daya listrik tahunan oleh auditor kelistrikan.',
                    'mitigation' => 'Pemasangan Active Harmonic Filter (AHF) pada seluruh panel distribusi utama dan monitoring THD real-time.'
                ],
                [
                    'title' => 'Kegagalan Sistem Pembumian Listrik (Grounding System) Menahan Lonjakan Petir (Lightning Strike)',
                    'target' => 'Nilai resistansi pembumian (grounding resistance) data center selalu berada di bawah 1 Ohm.',
                    'description' => 'Sambaran petir pada penangkal petir atap gedung merusak puluhan port kartu jaringan switch data center.',
                    'category_id' => 12, 'cause_category_id' => 4, 'radar_code' => 'O2',
                    'inherent_p' => 3, 'inherent_i' => 4, 'residual_p' => 1, 'residual_i' => 1, 'impact_amount' => 720000000,
                    'cause' => 'Resistansi grounding tanah naik menjadi 8 Ohm akibat elektroda pembumian mengalami korosi tanah kering.',
                    'existing_control' => 'Pemasangan Surge Protective Device (SPD) Kelas 1 di panel utama gedung.',
                    'mitigation' => 'Peremajaan jaringan grounding dengan sistem grounding rod kimia dan pemasangan SPD Kelas 2/3 bertingkat di setiap rak.'
                ],
                [
                    'title' => 'Getaran dan Kebisingan Berlebih (Acoustic Noise) dari Nozzle Pemadam Kebakaran Merusak Harddisk',
                    'target' => 'Zero kerusakan mechanical harddisk drive (HDD) storage saat pengujian suara pemadam kebakaran.',
                    'description' => 'Bunyi bising frekuensi tinggi saat nozzle gas pemadam menyemprot menyebabkan read/write head storage crash.',
                    'category_id' => 12, 'cause_category_id' => 3, 'radar_code' => 'O2',
                    'inherent_p' => 2, 'inherent_i' => 5, 'residual_p' => 1, 'residual_i' => 1, 'impact_amount' => 1900000000,
                    'cause' => 'Nozzle pelepasan gas pemadam kebakaran masih bertipe standar industri lama dengan desibel di atas 130 dB.',
                    'existing_control' => 'Backup snapshot data storage sebelum pemeliharaan sistem proteksi kebakaran.',
                    'mitigation' => 'Penggantian nozzle gas pemadam dengan Acoustic Silencer Nozzle berteknologi peredam suara frekuensi rendah.'
                ],
                [
                    'title' => 'Debu Partikulat Halus dari Pekerjaan Pengeboran Gedung Merusak Kipas Pendingin Server',
                    'target' => 'Kebersihan udara ruang server memenuhi standar ISO 14644-1 Kelas 8 untuk data center.',
                    'description' => 'Debu semen dari proyek renovasi tangga darurat terhisap oleh kipas server dan menyebabkan kerusakan bearing.',
                    'category_id' => 12, 'cause_category_id' => 1, 'radar_code' => 'O2',
                    'inherent_p' => 3, 'inherent_i' => 3, 'residual_p' => 1, 'residual_i' => 1, 'impact_amount' => 230000000,
                    'cause' => 'Sistem tata udara ruangan data center tidak menggunakan filter udara partikulat berefisiensi tinggi (HEPA).',
                    'existing_control' => 'Pembersihan debu lantai raised floor mingguan dengan vacuum cleaner biasa.',
                    'mitigation' => 'Penerapan tekanan udara positif di dalam ruang server dan pembersihan profesional bersertifikasi ISO data center.'
                ],
                [
                    'title' => 'Penyusupan Fisik Melalui Ruang Plafon Gantung (Above False Ceiling) yang Tidak Terproteksi',
                    'target' => 'Zona keamanan perimeter fisik data center tertutup rapat dari lantai struktur hingga beton dak atas.',
                    'description' => 'Penyusup dapat merayap melewati celah di atas plafon partisi gypsum untuk masuk ke ruang kontrol CCTV bank.',
                    'category_id' => 12, 'cause_category_id' => 1, 'radar_code' => 'C2',
                    'inherent_p' => 3, 'inherent_i' => 4, 'residual_p' => 1, 'residual_i' => 1, 'impact_amount' => 490000000,
                    'cause' => 'Dinding pembatas ruang server tidak dibangun menerus hingga menyentuh dak beton atas (slab-to-slab wall).',
                    'existing_control' => 'Pemasangan sensor gerak PIR di dalam ruangan server.',
                    'mitigation' => 'Pemasangan kawat berduri jeruji baja di atas plafon dan penyempurnaan dinding perimeter beton penuh (slab-to-slab).'
                ],
                [
                    'title' => 'Kegagalan Sertifikasi Audit Uptime Institute Tier III Akibat Dokumen As-Built Drawing Tidak Sesuai',
                    'target' => 'Keberhasilan pembaruan sertifikasi Tier III Constructed Facility dari Uptime Institute tepat waktu.',
                    'description' => 'Sertifikasi keandalan data center terancam dicabut karena modifikasi jalur pipa pendingin tidak dilaporkan.',
                    'category_id' => 8, 'cause_category_id' => 2, 'radar_code' => 'C1',
                    'inherent_p' => 3, 'inherent_i' => 4, 'residual_p' => 1, 'residual_i' => 2, 'impact_amount' => 810000000,
                    'cause' => 'Kontraktor mekanikal elektrikal melakukan jalan pintas instalasi pipa tanpa persetujuan konsultan Tier Designer.',
                    'existing_control' => 'Pemeriksaan berkala oleh tim mechanical & electrical internal bank.',
                    'mitigation' => 'Audit kepatuhan desain berkala bersama Accredited Tier Designer dan pembaruan dokumen as-built berkala.'
                ],
                [
                    'title' => 'Kerusakan Peralatan IT Akibat Pelepasan Listrik Statis (Electrostatic Discharge / ESD)',
                    'target' => '100% perlindungan komponen elektronik dari risiko sengatan listrik statis saat maintenance.',
                    'description' => 'Modul kartu memori RAM server database terbakar saat dipasang oleh teknisi yang tidak memakai wrist strap ESD.',
                    'category_id' => 12, 'cause_category_id' => 1, 'radar_code' => 'O2',
                    'inherent_p' => 3, 'inherent_i' => 3, 'residual_p' => 1, 'residual_i' => 1, 'impact_amount' => 190000000,
                    'cause' => 'Tingkat kelembaban udara ruang server terlalu rendah (kering di bawah 30%) dan ubin lantai tidak konduktif.',
                    'existing_control' => 'Kewajiban mengenakan baju coverall khusus bagi teknisi yang masuk.',
                    'mitigation' => 'Pemasangan ubin anti-static dissipative raised floor dan mandatory ESD grounding wrist strap saat memegang hardware.'
                ],
                [
                    'title' => 'Ketidakmampuan Mengakses Data Center Saat Terjadi Unjuk Rasa / Kerusuhan di Depan Gedung',
                    'target' => 'Operasional data center tetap berjalan normal 100% saat terjadi blokade fisik akses gedung.',
                    'description' => 'Staf shift operasional tidak dapat keluar-masuk gedung pusat karena demonstrasi massa memblokir gerbang utama.',
                    'category_id' => 12, 'cause_category_id' => 4, 'radar_code' => 'S1',
                    'inherent_p' => 4, 'inherent_i' => 4, 'residual_p' => 1, 'residual_i' => 2, 'impact_amount' => 740000000,
                    'cause' => 'Gedung data center berada di jalur protokol pusat kota tanpa akses pintu keluar darurat di jalan belakang.',
                    'existing_control' => 'Pengamanan perimeter oleh aparat kepolisian dan satpam gedung.',
                    'mitigation' => 'Penyediaan fasilitas bunkering akomodasi staf di lokasi DC untuk 7 hari dan aktivasi kontrol jarak jauh dari DRC.'
                ],
            ],

            // 14. IF - INFRASTRUCTURE
            'IF - INFRASTRUCTURE' => [
                [
                    'title' => 'Kegagalan Kartu Supervisor Engine pada Core Switch Jaringan Data Center (Dual Supervisor Crash)',
                    'target' => 'Ketersediaan jaringan switching backbone data center 99.999% tanpa single point of failure.',
                    'description' => 'Bug firmware switch memicu memory leak yang me-reboot modul supervisor primer dan sekunder secara bersamaan.',
                    'category_id' => 9, 'cause_category_id' => 3, 'radar_code' => 'O2',
                    'inherent_p' => 3, 'inherent_i' => 5, 'residual_p' => 1, 'residual_i' => 2, 'impact_amount' => 2800000000,
                    'cause' => 'Firmware switch chassis core belum di-upgrade ke patch maintenance release yang stabil dari vendor.',
                    'existing_control' => 'Arsitektur dual chassis switch core dengan protokol Virtual Switching System (VSS/StackWise).',
                    'mitigation' => 'Penerapan arsitektur network fabric non-blocking spine-leaf EVPN-VXLAN dan update firmware berkala.'
                ],
                [
                    'title' => 'Serangan Distributed Denial of Service (DDoS) Melumpuhkan Bandwidth ISP Gateway Bank',
                    'target' => 'Mitigasi serangan volumetrik DDoS hingga 100 Gbps dalam waktu kurang dari 3 menit.',
                    'description' => 'Serangan DNS amplification flooding menenggelamkan pipa koneksi internet dan membuat website bank tidak bisa diakses.',
                    'category_id' => 19, 'cause_category_id' => 3, 'radar_code' => 'O3',
                    'inherent_p' => 4, 'inherent_i' => 5, 'residual_p' => 1, 'residual_i' => 2, 'impact_amount' => 2100000000,
                    'cause' => 'Layanan proteksi DDoS ISP lokal hanya mampu membersihkan traffic scrubbing hingga kapasitas 10 Gbps.',
                    'existing_control' => 'Penerapan firewall boundary dan rate limiting koneksi per IP.',
                    'mitigation' => 'Pemanfaatan Cloud DDoS Mitigation scrubbing center global (Cloudflare Magic Transit/Akamai Prolexic) otomatis.'
                ],
                [
                    'title' => 'Kedaluwarsa Sertifikat SSL/TLS Internal Mengakibatkan Putusnya Komunikasi Antar Microservices',
                    'target' => 'Zero downtime layanan perbankan yang diakibatkan oleh kedaluwarsanya sertifikat digital TLS.',
                    'description' => 'Sertifikat mTLS internal pada ingress Kubernetes kedaluwarsa pada dini hari dan melumpuhkan seluruh transaksi API.',
                    'category_id' => 9, 'cause_category_id' => 2, 'radar_code' => 'O2',
                    'inherent_p' => 4, 'inherent_i' => 4, 'residual_p' => 1, 'residual_i' => 2, 'impact_amount' => 950000000,
                    'cause' => 'Manajemen sertifikat masih dikelola manual tanpa alat automasi pembaruan cert-manager.',
                    'existing_control' => 'Kalender pengingat masa berlaku sertifikat oleh tim operasional infrastruktur.',
                    'mitigation' => 'Implementasi automated certificate lifecycle management (Cert-Manager HashiCorp Vault) dengan auto-renewal H-30.'
                ],
                [
                    'title' => 'BGP Route Flapping pada Penyedia Jaringan Internet Upstream Menimbulkan Latensi Tinggi',
                    'target' => 'Stabilitas routing BGP internet gateway dengan konvergensi otomatis di bawah 10 detik.',
                    'description' => 'Terganggunya rute kabel bawah laut ISP menyebabkan packet loss 40% pada transaksi nasabah dari operator seluler.',
                    'category_id' => 9, 'cause_category_id' => 3, 'radar_code' => 'O2',
                    'inherent_p' => 4, 'inherent_i' => 4, 'residual_p' => 1, 'residual_i' => 2, 'impact_amount' => 620000000,
                    'cause' => 'Router edge bank tidak mengaktifkan fitur BGP Route Dampening untuk meredam rute yang berfluktuasi.',
                    'existing_control' => 'Penggunaan multi-homed ISP dengan 3 koneksi upstream berbeda.',
                    'mitigation' => 'Penerapan BGP Dampening, BFD (Bidirectional Forwarding Detection) ultra-fast, dan monitoring peering aktif.'
                ],
                [
                    'title' => 'Exhaustion Alokasi IP Address Private (IPv4 Subnet Exhaustion) pada Klaster Kubernetes',
                    'target' => 'Ketersediaan subnet IP address container pod selalu mencukupi pertumbuhan autoscaling hingga 300%.',
                    'description' => 'Aplikasi baru gagal di-deploy karena cluster Kubernetes kehabisan IP address CIDR block pod yang tersedia.',
                    'category_id' => 9, 'cause_category_id' => 2, 'radar_code' => 'O4',
                    'inherent_p' => 3, 'inherent_i' => 4, 'residual_p' => 1, 'residual_i' => 1, 'impact_amount' => 340000000,
                    'cause' => 'Alokasi subnetting IP awal dirancang terlalu sempit tanpa memperhitungkan skalabilitas microservices masa depan.',
                    'existing_control' => 'Pembersihan manual pod dan namespace yang sudah tidak digunakan.',
                    'mitigation' => 'Migrasi arsitektur jaringan container ke CNI Calico/Cilium berbasis eBPF dan adopsi dual-stack IPv4/IPv6.'
                ],
                [
                    'title' => 'Kegagalan Storage Area Network (SAN) Fabric Switch Mengakibatkan Disk I/O Freeze pada Database',
                    'target' => 'Ketersediaan jalur transmisi SAN fiber channel 100% tanpa adanya packet drop.',
                    'description' => 'Koneksi switch fiber channel mengalami slow drain device yang memperlambat seluruh I/O storage perbankan.',
                    'category_id' => 9, 'cause_category_id' => 3, 'radar_code' => 'O2',
                    'inherent_p' => 3, 'inherent_i' => 5, 'residual_p' => 1, 'residual_i' => 2, 'impact_amount' => 2200000000,
                    'cause' => 'Transceiver SFP optik pada salah satu port switch SAN rusak dan mengirimkan sinyal noise ke fabric network.',
                    'existing_control' => 'Jalur multi-path I/O (MPIO) ganda pada setiap server database.',
                    'mitigation' => 'Penerapan fitur auto-quarantine slow drain device pada switch Brocade SAN dan penggantian SFP berkala.'
                ],
                [
                    'title' => 'Kerentanan Zero-Day pada Perangkat Virtual Private Network (VPN Gateway / SSL-VPN)',
                    'target' => 'Penerapan patch darurat pada seluruh perimeter internet facing gateway maksimal 24 jam pasca rilis CVE.',
                    'description' => 'Penyerang mengeksploitasi celah autentikasi bypass pada VPN appliance untuk menyusup ke intranet perbankan.',
                    'category_id' => 19, 'cause_category_id' => 3, 'radar_code' => 'O3',
                    'inherent_p' => 4, 'inherent_i' => 5, 'residual_p' => 1, 'residual_i' => 2, 'impact_amount' => 2900000000,
                    'cause' => 'Keterlambatan tim infrastruktur menginstal firmware hotfix vendor firewall saat akhir pekan.',
                    'existing_control' => 'Autentikasi dua faktor (2FA) OTP pada koneksi VPN karyawan.',
                    'mitigation' => 'Transisi dari legacy VPN ke arsitektur Zero Trust Network Access (ZTNA) dan patching otomatis perimeter gateway.'
                ],
                [
                    'title' => 'Kegagalan Otomasi Sinkronisasi Direktori Identitas (Active Directory Replication Lag)',
                    'target' => 'Latency replikasi direktori user Active Directory antar domain controller selalu di bawah 15 detik.',
                    'description' => 'Staf cabang baru tidak dapat login ke sistem perbankan karena akunnya belum tereplikasi dari server pusat.',
                    'category_id' => 10, 'cause_category_id' => 3, 'radar_code' => 'O4',
                    'inherent_p' => 4, 'inherent_i' => 3, 'residual_p' => 1, 'residual_i' => 1, 'impact_amount' => 230000000,
                    'cause' => 'Port komunikasi RPC/Kerberos antar domain controller cabang diblokir oleh salah konfigurasi firewall lokal.',
                    'existing_control' => 'Replikasi manual paksa menggunakan tool repadmin oleh admin sistem.',
                    'mitigation' => 'Otomasi monitoring integritas replikasi AD, standardisasi port rule firewall, dan konsolidasi domain controller.'
                ],
                [
                    'title' => 'Kerusakan Klaster Server Virtualisasi Hypervisor (VMware ESXi Purple Screen of Death)',
                    'target' => 'High Availability VM otomatis merestart virtual machine yang terdampak di bawah 3 menit.',
                    'description' => 'Kesalahan driver kartu jaringan memicu crash kernel pada 3 server host fisik yang menampung 60 VM aplikasi bisnis.',
                    'category_id' => 9, 'cause_category_id' => 3, 'radar_code' => 'O2',
                    'inherent_p' => 3, 'inherent_i' => 5, 'residual_p' => 1, 'residual_i' => 2, 'impact_amount' => 1700000000,
                    'cause' => 'Inkompatibilitas firmware kartu PCIe server terhadap update versi hypervisor VMware terbaru.',
                    'existing_control' => 'Konfigurasi VMware High Availability (HA) dan Distributed Resource Scheduler (DRS).',
                    'mitigation' => 'Validasi ketat VMware Hardware Compatibility List (HCL) sebelum update firmware dan penyediaan cadangan host N+2.'
                ],
                [
                    'title' => 'Pembajakan Server Domain Name System (DNS Cache Poisoning / DNS Hijacking)',
                    'target' => 'Zero kerentanan manipulasi resolusi alamat domain internet perbankan.',
                    'description' => 'Traffic nasabah yang hendak mengakses internet banking diarahkan ke server web palsu penipu phishing.',
                    'category_id' => 19, 'cause_category_id' => 3, 'radar_code' => 'O3',
                    'inherent_p' => 3, 'inherent_i' => 5, 'residual_p' => 1, 'residual_i' => 2, 'impact_amount' => 3100000000,
                    'cause' => 'Server DNS autoritatif bank belum menerapkan penandatanganan kriptografis DNS Security Extensions (DNSSEC).',
                    'existing_control' => 'Registrasi kunci registry domain (Registry Lock) pada registrar nama domain.',
                    'mitigation' => 'Implementasi mandatory DNSSEC penuh, anycast authoritative DNS provider global, dan monitoring integritas DNS 24/7.'
                ],
                [
                    'title' => 'Degradasi Throughput Jaringan WAN Kantor Cabang Akibat Lonjakan Traffic Non-Bisnis',
                    'target' => 'Ketersediaan bandwidth minimal 5 Mbps khusus transaksi perbankan di setiap kantor cabang.',
                    'description' => 'Layanan teller cabang lambat memproses setoran karena bandwidth jaringan terpakai oleh staf yang streaming video.',
                    'category_id' => 9, 'cause_category_id' => 1, 'radar_code' => 'O5',
                    'inherent_p' => 4, 'inherent_i' => 3, 'residual_p' => 1, 'residual_i' => 1, 'impact_amount' => 180000000,
                    'cause' => 'Router cabang tidak dilengkapi kebijakan pembatasan traffic (traffic shaping) dan pemblokiran situs hiburan.',
                    'existing_control' => 'Pemberitahuan surat edaran etika penggunaan internet kantor.',
                    'mitigation' => 'Penerapan SD-WAN dengan Application-Aware Routing yang memprioritaskan traffic transaksi finansial di atas traffic lain.'
                ],
                [
                    'title' => 'Kegagalan Pembaruan Sistem Operasi Server Linux Akibat Inkompatibilitas Kernel Modul',
                    'target' => 'Penerapan security patch OS Linux tuntas tanpa menimbulkan kegagalan booting server.',
                    'description' => 'Update kernel Linux menyebabkan driver kartu Fiber Channel HBA tidak terbaca dan server database gagal boot.',
                    'category_id' => 9, 'cause_category_id' => 3, 'radar_code' => 'O2',
                    'inherent_p' => 3, 'inherent_i' => 4, 'residual_p' => 1, 'residual_i' => 2, 'impact_amount' => 740000000,
                    'cause' => 'Proses patching server langsung dieksekusi di production tanpa tahapan pengujian di environment staging identik.',
                    'existing_control' => 'Penyediaan snapshot VM sebelum proses patching dieksekusi.',
                    'mitigation' => 'Prosedur bertahap staging patching, automated kernel regression testing, dan mekanisme kexec live-patching tanpa reboot.'
                ],
                [
                    'title' => 'Kerusakan Fisik Kabel Jaringan Fiber Optik Backbone Antar Gedung Akibat Proyek Galian Umum',
                    'target' => 'Jalur fiber optik data center dilindungi konektivitas beragam (diverse path) dengan zero single point of cut.',
                    'description' => 'Ekskavator proyek pelebaran jalan memotong kabel serat optik utama bank dan memutus akses ribuan ATM.',
                    'category_id' => 12, 'cause_category_id' => 4, 'radar_code' => 'O2',
                    'inherent_p' => 4, 'inherent_i' => 4, 'residual_p' => 1, 'residual_i' => 2, 'impact_amount' => 890000000,
                    'cause' => 'Jalur kabel fiber optik primer dan sekunder ternyata ditanam di gorong-gorong jalan yang sama oleh vendor.',
                    'existing_control' => 'Asuransi ganti rugi pemotongan utilitas oleh kontraktor proyek jalan.',
                    'mitigation' => 'Audit rute fisik fiber optik: wajib menggunakan dual diverse path melalui dua ruas jalan kota yang terpisah jauh.'
                ],
                [
                    'title' => 'Exhaustion Memori RAM pada Klaster Distributed Cache (Redis Cluster Out of Memory)',
                    'target' => 'Utilisasi memori klaster Redis selalu terjaga di bawah 75% kapasitas total.',
                    'description' => 'Penyimpanan session token dan cache produk perbankan meluap dan menyebabkan Redis menolak transaksi baru.',
                    'category_id' => 9, 'cause_category_id' => 3, 'radar_code' => 'O2',
                    'inherent_p' => 3, 'inherent_i' => 4, 'residual_p' => 1, 'residual_i' => 2, 'impact_amount' => 580000000,
                    'cause' => 'Developer menyimpan data cache tanpa menentukan batas waktu kedaluwarsa (Time-To-Live / TTL).',
                    'existing_control' => 'Pemberian konfigurasi maxmemory-policy volatile-lru.',
                    'mitigation' => 'Standardisasi mandatory TTL pada semua penulisan key Redis dan alert monitoring kapasitas memori real-time.'
                ],
                [
                    'title' => 'Kegagalan Otomasi Rotasi Kunci SSH Key Pair pada Infrastruktur Server Linux',
                    'target' => '100% akses administrasi server Linux menggunakan temporary certificate-based SSH authentication.',
                    'description' => 'Mantan karyawan pihak ketiga masih dapat login ke server aplikasi karena public key miliknya tertinggal di authorized_keys.',
                    'category_id' => 10, 'cause_category_id' => 1, 'radar_code' => 'C2',
                    'inherent_p' => 4, 'inherent_i' => 5, 'residual_p' => 1, 'residual_i' => 2, 'impact_amount' => 1800000000,
                    'cause' => 'Pengelolaan file authorized_keys dilakukan manual tanpa integrasi dengan sistem manajemen identitas terpusat.',
                    'existing_control' => 'Pemeriksaan berkala isi file authorized_keys oleh administrator sistem.',
                    'mitigation' => 'Penerapan SSH Certificate Authority (Teleport/HashiCorp Vault) dengan masa aktif kredensial maksimal 8 jam.'
                ],
                [
                    'title' => 'Kerentanan Keamanan pada Komponen Container Base Image yang Mengandung Malware',
                    'target' => 'Zero vulnerability High/Critical dan zero unverified base images pada registry container produksi.',
                    'description' => 'Developer mendownload base image Docker dari repository publik yang disusupi kriptominer tersembunyi.',
                    'category_id' => 19, 'cause_category_id' => 3, 'radar_code' => 'O3',
                    'inherent_p' => 4, 'inherent_i' => 4, 'residual_p' => 1, 'residual_i' => 2, 'impact_amount' => 920000000,
                    'cause' => 'Tidak adanya private container registry yang diaudit dan ketiadaan image signing verification saat deployment.',
                    'existing_control' => 'Pemindaian kerentanan image menggunakan tool open-source.',
                    'mitigation' => 'Penerapan Private Enterprise Container Registry (Harbor) dengan image signing Cosign dan automated admission controller.'
                ],
                [
                    'title' => 'Anomali Alokasi Resource Virtual Machine (Resource Contention / Noisy Neighbor)',
                    'target' => 'Zero degradasi performa I/O atau CPU pada virtual machine tier-1 perbankan.',
                    'description' => 'Server database kartu kredit berjalan sangat lambat karena VM pengujian di host fisik yang sama memonopoli CPU 100%.',
                    'category_id' => 9, 'cause_category_id' => 2, 'radar_code' => 'O4',
                    'inherent_p' => 4, 'inherent_i' => 3, 'residual_p' => 1, 'residual_i' => 1, 'impact_amount' => 270000000,
                    'cause' => 'Tidak diterapkannya penetapan alokasi resource reservation (CPU/RAM Reservation) pada VM kritis.',
                    'existing_control' => 'Migrasi manual VM yang lambat ke host server fisik lain.',
                    'mitigation' => 'Penerapan 100% CPU and RAM reservation untuk seluruh VM Tier-1 dan isolasi lingkungan non-prod pada klaster fisik berbeda.'
                ],
                [
                    'title' => 'Kegagalan Sinkronisasi Konfigurasi Firewall Cluster High Availability (State Desync)',
                    'target' => 'Sinkronisasi 100% rule policy firewall antar node active dan passive tanpa celah keamanan.',
                    'description' => 'Saat firewall master restart, node backup menolak seluruh koneksi keluar karena rule izin akses belum tersinkronisasi.',
                    'category_id' => 9, 'cause_category_id' => 3, 'radar_code' => 'O2',
                    'inherent_p' => 3, 'inherent_i' => 5, 'residual_p' => 1, 'residual_i' => 2, 'impact_amount' => 1450000000,
                    'cause' => 'Jalur kabel dedicated HA sync antar perangkat firewall mengalami transmisi error tanpa memicu alarm kegagalan.',
                    'existing_control' => 'Pemeriksaan status kesamaan hash konfigurasi firewall secara berkala.',
                    'mitigation' => 'Penerapan dual dedicated link HA synchronization dan automated daily configuration parity check.'
                ],
                [
                    'title' => 'Tingginya Biaya Egress Data Transfer Cloud Akibat Arsitektur Jaringan Multi-Cloud yang Boros',
                    'target' => 'Optimalisasi biaya data transfer cloud hingga 35% dengan routing lokal yang efisien.',
                    'description' => 'Tagihan cloud bulanan melonjak ratusan juta rupiah akibat lalu lintas transfer data antar region cloud publik yang tidak terkompresi.',
                    'category_id' => 1, 'cause_category_id' => 2, 'radar_code' => 'F1',
                    'inherent_p' => 4, 'inherent_i' => 3, 'residual_p' => 1, 'residual_i' => 1, 'impact_amount' => 380000000,
                    'cause' => 'Layanan cloud berkomunikasi melalui jaringan internet publik alih-alih melalui jalur private interconnect langsung.',
                    'existing_control' => 'Pemantauan tagihan bulanan melalui portal billing cloud.',
                    'mitigation' => 'Penerapan Cloud Dedicated Interconnect (AWS Direct Connect / Google Cloud Interconnect) dan kompresi payload transmisi data.'
                ],
                [
                    'title' => 'Ketidakmampuan Memulihkan Konfigurasi Perangkat Jaringan (Network Config Backup Failure)',
                    'target' => '100% backup otomatis harian konfigurasi seluruh router, switch, dan firewall tersimpan di repository aman.',
                    'description' => 'Switch distribusi cabang terbakar dan teknisi membutuhkan waktu 12 jam untuk menyusun kembali konfigurasi VLAN manual.',
                    'category_id' => 9, 'cause_category_id' => 2, 'radar_code' => 'O2',
                    'inherent_p' => 3, 'inherent_i' => 4, 'residual_p' => 1, 'residual_i' => 1, 'impact_amount' => 310000000,
                    'cause' => 'Tool automated network configuration backup (RANCID/Oxidized) gagal mengekstrak konfigurasi tipe switch model baru.',
                    'existing_control' => 'Penyimpanan file teks backup konfigurasi di komputer teknisi jaringan.',
                    'mitigation' => 'Automated configuration backup terpusat (Git-based network automation) dengan notifikasi kegagalan harian.'
                ],
            ],
        ];
    }
}
