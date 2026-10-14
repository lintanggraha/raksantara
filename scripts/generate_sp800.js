const fs = require('fs');

// Family names for SP 800-53
const families = {
    AC: 'Access Control',
    AU: 'Audit and Accountability',
    AT: 'Awareness and Training',
    CM: 'Configuration Management',
    CP: 'Contingency Planning',
    IA: 'Identification and Authentication',
    IR: 'Incident Response',
    PE: 'Physical and Environmental Protection',
    PL: 'Planning',
    PM: 'Program Management',
    RA: 'Risk Assessment',
    SA: 'System and Services Acquisition',
    SC: 'System and Communications Protection',
    SI: 'System and Information Integrity'
};

// SP 800-53 Rev 5 control descriptions (curated)
const controlData = {
    // AC - Access Control
    'AC-1':  { desc: 'Kebijakan dan prosedur pengendalian akses ditetapkan, didokumentasikan, disebarluaskan, dan ditinjau secara berkala.', bukti: ['Dokumen Kebijakan Akses', 'Prosedur Akses Terkini', 'Notulensi Tinjauan Kebijakan'] },
    'AC-2':  { desc: 'Akun pengguna dikelola selama siklus hidupnya: pembuatan, pengaktifan, modifikasi, penonaktifan, dan penghapusan.', bukti: ['Log Pembuatan/Penghapusan Akun', 'Laporan Akun Aktif', 'Formulir Permintaan Akun'] },
    'AC-3':  { desc: 'Sistem memberlakukan otorisasi yang disetujui untuk akses logis ke informasi dan sumber daya sistem.', bukti: ['Konfigurasi ACL (Access Control List)', 'Laporan Tinjauan Hak Akses'] },
    'AC-4':  { desc: 'Sistem memberlakukan otorisasi yang disetujui untuk mengontrol alur informasi antar sistem.', bukti: ['Aturan Firewall', 'Konfigurasi DLP', 'Diagram Alur Data'] },
    'AC-5':  { desc: 'Tugas dan tanggung jawab individu dipisahkan untuk mengurangi risiko aktivitas jahat.', bukti: ['Matriks Pemisahan Tugas', 'Diagram RACI'] },
    'AC-6':  { desc: 'Hak istimewa minimum diberikan: pengguna hanya memiliki akses yang diperlukan untuk menyelesaikan tugasnya.', bukti: ['Laporan Hak Akses Administratif', 'Review Privilege Triwulanan', 'Konfigurasi PAM'] },
    'AC-7':  { desc: 'Sistem membatasi percobaan login yang gagal dan memberikan respons otomatis setelah batas terlampaui.', bukti: ['Konfigurasi Account Lockout Policy', 'Log Kegagalan Autentikasi'] },
    'AC-8':  { desc: 'Sistem menampilkan pesan notifikasi penggunaan sebelum pengguna mengakses sistem.', bukti: ['Banner Login Sistem', 'Screenshot Pesan Peringatan'] },
    'AC-11': { desc: 'Sistem menerapkan kunci sesi setelah periode tidak aktif yang telah ditentukan.', bukti: ['Kebijakan Kunci Layar Otomatis', 'Konfigurasi Screen Lock'] },
    'AC-17': { desc: 'Akses jarak jauh ke sistem dipantau, dikendalikan, dan dibatasi sesuai kebijakan.', bukti: ['Log Sesi VPN', 'Kebijakan Akses Jarak Jauh', 'Laporan Pengguna Remote'] },
    'AC-18': { desc: 'Koneksi jaringan nirkabel dipantau dan dikendalikan menggunakan konfigurasi yang aman.', bukti: ['Konfigurasi WAP', 'Log Koneksi Wifi', 'Kebijakan Penggunaan Wireless'] },
    'AC-19': { desc: 'Akses dari perangkat seluler ke sistem dikendalikan dan dipantau.', bukti: ['Konfigurasi MDM (Mobile Device Management)', 'Kebijakan BYOD'] },
    'AC-20': { desc: 'Penggunaan sistem eksternal untuk mengakses sumber daya organisasi dikontrol sesuai kebijakan.', bukti: ['Perjanjian Penggunaan Sistem Eksternal', 'Kebijakan Cloud/SaaS'] },

    // AU - Audit and Accountability
    'AU-1':  { desc: 'Kebijakan dan prosedur audit serta akuntabilitas ditetapkan dan ditinjau secara berkala.', bukti: ['Kebijakan Logging & Audit', 'Prosedur Tinjauan Log'] },
    'AU-2':  { desc: 'Organisasi menentukan jenis peristiwa yang harus diaudit oleh sistem.', bukti: ['Daftar Event Audit Terdefinisi', 'Konfigurasi Log Event'] },
    'AU-3':  { desc: 'Rekaman audit berisi informasi yang cukup untuk menentukan apa yang terjadi, kapan, dan oleh siapa.', bukti: ['Contoh Log Audit', 'Format Rekaman Audit'] },
    'AU-6':  { desc: 'Rekaman audit ditinjau, dianalisis, dan dilaporkan kepada pihak yang berwenang berdasarkan keperluan.', bukti: ['Laporan Tinjauan Log Mingguan/Bulanan', 'Notifikasi Alert SIEM'] },
    'AU-9':  { desc: 'Rekaman audit dilindungi dari akses, modifikasi, dan penghapusan yang tidak sah.', bukti: ['Konfigurasi Perlindungan Log', 'Audit Log Immutability'] },
    'AU-11': { desc: 'Rekaman audit dipertahankan sesuai kebijakan retensi data yang telah ditetapkan.', bukti: ['Kebijakan Retensi Log', 'Laporan Ukuran Penyimpanan Log'] },
    'AU-12': { desc: 'Sistem menyediakan kemampuan pembuatan catatan audit untuk peristiwa yang dapat diaudit.', bukti: ['Konfigurasi Syslog/SIEM', 'Daftar Sumber Log Aktif'] },

    // AT - Awareness and Training
    'AT-1':  { desc: 'Kebijakan dan prosedur pelatihan kesadaran keamanan informasi ditetapkan dan ditinjau secara berkala.', bukti: ['Kebijakan Pelatihan Keamanan', 'Jadwal Program Pelatihan'] },
    'AT-2':  { desc: 'Pengguna dan manajer menerima pelatihan kesadaran keamanan informasi secara berkala.', bukti: ['Sertifikat Pelatihan Karyawan', 'Laporan Kehadiran Pelatihan', 'Konten E-Learning Keamanan'] },
    'AT-3':  { desc: 'Personel dengan tanggung jawab keamanan menerima pelatihan peran berbasis keamanan.', bukti: ['Matriks Pelatihan Berbasis Peran', 'Sertifikasi Teknis Tim Keamanan'] },
    'AT-4':  { desc: 'Organisasi mendokumentasikan dan memantau kegiatan pelatihan keamanan informasi.', bukti: ['Laporan Pemantauan Pelatihan', 'Basis Data Pelatihan Karyawan'] },

    // CM - Configuration Management
    'CM-1':  { desc: 'Kebijakan dan prosedur manajemen konfigurasi ditetapkan dan ditinjau secara berkala.', bukti: ['Kebijakan Manajemen Konfigurasi', 'Prosedur Standar Konfigurasi'] },
    'CM-2':  { desc: 'Konfigurasi dasar (baseline) dari sistem informasi dikembangkan, didokumentasikan, dan dipelihara.', bukti: ['Dokumen Baseline Konfigurasi', 'Image Sistem Standar', 'Perbandingan Konfigurasi'] },
    'CM-6':  { desc: 'Pengaturan konfigurasi keamanan ditetapkan dan diberlakukan sesuai praktik terbaik industri.', bukti: ['Konfigurasi CIS Benchmark', 'Hasil Audit Kepatuhan Konfigurasi'] },
    'CM-7':  { desc: 'Sistem dikonfigurasi hanya menyediakan kemampuan yang esensial, menonaktifkan fitur yang tidak diperlukan.', bukti: ['Daftar Layanan Aktif', 'Laporan Port Scanning', 'Kebijakan Minimize Attack Surface'] },
    'CM-8':  { desc: 'Inventaris komponen sistem informasi dikembangkan, didokumentasikan, dan dipelajari secara akurat.', bukti: ['Inventaris Aset TI', 'CMDB', 'Hasil Pemindaian Aset'] },

    // CP - Contingency Planning
    'CP-1':  { desc: 'Kebijakan dan prosedur perencanaan kontinjensi ditetapkan dan ditinjau secara berkala.', bukti: ['Kebijakan Kontinjensi', 'Prosedur Pemulihan Sistem'] },
    'CP-2':  { desc: 'Rencana darurat/kontinjensi dikembangkan untuk sistem informasi.', bukti: ['Business Continuity Plan (BCP)', 'Disaster Recovery Plan (DRP)', 'Versi BCP Terkini'] },
    'CP-4':  { desc: 'Rencana kontinjensi diuji untuk menentukan keefektifan rencana dan kesiapan organisasi.', bukti: ['Laporan Pengujian BCP/DRP', 'Hasil Simulasi Pemulihan', 'Jadwal Uji Kontinjensi'] },
    'CP-9':  { desc: 'Salinan cadangan (backup) dari informasi pengguna, perangkat lunak, dan dokumentasi sistem dibuat dan diuji.', bukti: ['Log Backup Harian/Mingguan', 'Laporan Pengujian Restore', 'Kebijakan Backup'] },
    'CP-10': { desc: 'Sistem informasi dipulihkan ke kondisi yang diketahui aman setelah gangguan atau kegagalan.', bukti: ['Prosedur Pemulihan Sistem', 'Laporan Pasca Insiden', 'RTO/RPO yang Terdokumentasi'] },

    // IA - Identification and Authentication
    'IA-1':  { desc: 'Kebijakan dan prosedur identifikasi dan autentikasi ditetapkan dan ditinjau secara berkala.', bukti: ['Kebijakan Autentikasi', 'Prosedur Manajemen Identitas'] },
    'IA-2':  { desc: 'Sistem mengidentifikasi dan mengautentikasi pengguna secara unik.', bukti: ['Konfigurasi SSO', 'Log Autentikasi Pengguna', 'Laporan Adopsi MFA'] },
    'IA-4':  { desc: 'Pengidentifikasi pengguna dikelola dengan mencegah penggunaan kembali identifikasi yang sama.', bukti: ['Kebijakan Keunikan ID Pengguna', 'Log Pengelolaan Identifikasi'] },
    'IA-5':  { desc: 'Autentikator sistem dikelola dengan menetapkan dan menegakkan persyaratan kekuatan autentikator.', bukti: ['Kebijakan Password', 'Hasil Audit Kekuatan Kata Sandi', 'Konfigurasi MFA'] },

    // IR - Incident Response
    'IR-1':  { desc: 'Kebijakan dan prosedur respons insiden ditetapkan dan ditinjau secara berkala.', bukti: ['Kebijakan Respons Insiden', 'Prosedur Penanganan Insiden (IRP)'] },
    'IR-4':  { desc: 'Organisasi mengimplementasikan kemampuan penanganan insiden untuk insiden keamanan meliputi persiapan, deteksi, analisis, penahanan, pemberantasan, dan pemulihan.', bukti: ['Playbook Respons Insiden', 'Laporan Insiden Tertutup', 'Metrik MTTR (Mean Time to Respond)'] },
    'IR-5':  { desc: 'Organisasi melacak dan mendokumentasikan insiden keamanan sistem informasi.', bukti: ['Daftar/Tracker Insiden', 'Laporan Insiden Bulanan', 'TIcket Sistem Helpdesk'] },
    'IR-6':  { desc: 'Personel melaporkan insiden keamanan yang diketahui kepada kemampuan respons insiden organisasi.', bukti: ['Formulir Pelaporan Insiden', 'Prosedur Eskalasi', 'Kontak Tim IR'] },
    'IR-8':  { desc: 'Organisasi mengembangkan rencana respons insiden yang berisi prosedur penanganan insiden.', bukti: ['IRP Terdokumentasi', 'Matriks Komunikasi Krisis', 'Jadwal Latihan IR'] },

    // PE - Physical and Environmental
    'PE-1':  { desc: 'Kebijakan dan prosedur perlindungan fisik dan lingkungan ditetapkan dan ditinjau.', bukti: ['Kebijakan Keamanan Fisik', 'Prosedur Akses Fasilitas'] },
    'PE-2':  { desc: 'Otorisasi akses fisik ke fasilitas yang berisi sistem informasi dikelola dan ditinjau secara berkala.', bukti: ['Daftar Otorisasi Akses Fisik', 'Log Akses Kartu', 'Rekaman CCTV'] },
    'PE-3':  { desc: 'Akses fisik ke fasilitas dikontrol dengan mengautentikasi individu sebelum masuk.', bukti: ['Sistem Akses Kartu/Biometrik', 'Log Akses Pintu', 'Laporan Insiden Keamanan Fisik'] },
    'PE-6':  { desc: 'Area fisik yang ditentukan dipantau menggunakan peralatan pengawasan atau sumber daya manusia.', bukti: ['Sistem CCTV Aktif', 'Laporan Patroli Keamanan', 'Rekaman Kamera'] },

    // PL - Planning
    'PL-1':  { desc: 'Kebijakan dan prosedur perencanaan keamanan ditetapkan dan ditinjau secara berkala.', bukti: ['Kebijakan Perencanaan Keamanan', 'Rencana Keamanan Sistem'] },
    'PL-2':  { desc: 'Rencana keamanan untuk sistem informasi dikembangkan, ditinjau, diperbarui, dan dilindungi dari pengungkapan yang tidak sah.', bukti: ['System Security Plan (SSP)', 'Riwayat Tinjauan SSP'] },

    // PM - Program Management
    'PM-1':  { desc: 'Kerangka manajemen keamanan informasi dikembangkan dan diimplementasikan termasuk struktur tata kelola.', bukti: ['Kebijakan IS Tingkat Enterprise', 'Piagam Komite Keamanan', 'Struktur Governance IT'] },
    'PM-9':  { desc: 'Strategi manajemen risiko dikembangkan untuk mendukung pengambilan keputusan risiko yang konsisten di seluruh organisasi.', bukti: ['Dokumen Strategi Manajemen Risiko', 'Risk Appetite Statement', 'Laporan Risk Register'] },
    'PM-11': { desc: 'Proses bisnis yang mendukung misi ditetapkan dan didokumentasikan termasuk dampak keamanan informasi.', bukti: ['Peta Proses Bisnis Kritis', 'BIA (Business Impact Analysis)'] },

    // RA - Risk Assessment
    'RA-2':  { desc: 'Sistem informasi dikategorikan sesuai dengan regulasi yang berlaku dan dampak potensial dari kerugian.', bukti: ['Dokumen Kategorisasi Risiko', 'Risk Classification Matrix'] },
    'RA-3':  { desc: 'Penilaian risiko dilakukan yang mengidentifikasi ancaman dan kerentanan pada sistem informasi.', bukti: ['Laporan Risk Assessment', 'Daftar Ancaman dan Kerentanan', 'Risk Register Terperinci'] },
    'RA-5':  { desc: 'Kerentanan dalam sistem informasi dipindai secara berkala dan ketika kerentanan baru teridentifikasi.', bukti: ['Laporan Vulnerability Scan', 'Jadwal Pemindaian Kerentanan', 'Dashboard Manajemen Kerentanan'] },

    // SA - System and Services Acquisition
    'SA-1':  { desc: 'Kebijakan dan prosedur akuisisi sistem dan layanan ditetapkan dan ditinjau secara berkala.', bukti: ['Kebijakan Pengadaan IT', 'Prosedur Akuisisi Sistem'] },
    'SA-3':  { desc: 'Sistem informasi dikembangkan, diakuisisi, atau dimodifikasi menggunakan siklus pengembangan sistem yang mencakup pertimbangan keamanan.', bukti: ['Kebijakan SDLC', 'Prosedur Security Review dalam SDLC', 'Checklist Keamanan Pengembangan'] },
    'SA-8':  { desc: 'Prinsip rekayasa keamanan diterapkan dalam spesifikasi, desain, pengembangan, implementasi, dan modifikasi sistem.', bukti: ['Dokumen Arsitektur Keamanan', 'Panduan Secure Coding', 'Hasil Code Review'] },

    // SC - System and Communications Protection
    'SC-1':  { desc: 'Kebijakan dan prosedur perlindungan sistem dan komunikasi ditetapkan dan ditinjau secara berkala.', bukti: ['Kebijakan Perlindungan Komunikasi', 'Prosedur Keamanan Jaringan'] },
    'SC-5':  { desc: 'Sistem informasi melindungi terhadap serangan penolakan layanan (DoS) atau membatasi dampak serangan semacam itu.', bukti: ['Konfigurasi Anti-DDoS', 'Laporan Uji Beban Jaringan', 'Konfigurasi Rate Limiting'] },
    'SC-7':  { desc: 'Sistem informasi memantau dan mengendalikan komunikasi di batas eksternal dan batas internal utama.', bukti: ['Konfigurasi Firewall Perimeter', 'Konfigurasi DMZ', 'Aturan Network Segmentation'] },
    'SC-8':  { desc: 'Mekanisme kriptografi diimplementasikan untuk mencegah pengungkapan informasi yang tidak sah selama transmisi.', bukti: ['Sertifikat TLS/SSL Aktif', 'Konfigurasi VPN', 'Kebijakan Enkripsi Transmisi Data'] },
    'SC-12': { desc: 'Kunci kriptografi dibuat dan dikelola sesuai kebijakan keamanan yang ditetapkan.', bukti: ['Kebijakan Manajemen Kunci', 'Catatan Rotasi Kunci', 'HSM (Hardware Security Module)'] },
    'SC-28': { desc: 'Sistem informasi melindungi kerahasiaan dan integritas informasi yang tersimpan.', bukti: ['Konfigurasi Enkripsi Database', 'Laporan Enkripsi Storage', 'Bukti Enkripsi Full Disk'] },

    // SI - System and Information Integrity
    'SI-1':  { desc: 'Kebijakan dan prosedur integritas sistem dan informasi ditetapkan dan ditinjau secara berkala.', bukti: ['Kebijakan Integritas Sistem', 'Prosedur Perlindungan Malware'] },
    'SI-2':  { desc: 'Kerentanan dan kesalahan dalam sistem informasi diidentifikasi, dilaporkan, dan diperbaiki tepat waktu.', bukti: ['Laporan Patching Bulanan', 'Log Remediasi Kerentanan', 'Kebijakan Manajemen Patch'] },
    'SI-3':  { desc: 'Mekanisme perlindungan kode berbahaya (malware) diimplementasikan di lokasi-lokasi yang sesuai.', bukti: ['Laporan AV/EDR Aktif', 'Statistik Malware Terblokir', 'Konfigurasi Anti-Malware'] },
    'SI-4':  { desc: 'Sistem informasi dipantau untuk mendeteksi serangan dan indikator potensi serangan.', bukti: ['Dashboard SIEM', 'Log Pemantauan IDS/IPS', 'Perjanjian Layanan SOC'] },
    'SI-5':  { desc: 'Informasi peringatan keamanan, saran, dan arahan dari sumber eksternal diterima dan disebarluaskan.', bukti: ['Langganan Alert Keamanan (CERT)', 'Prosedur Distribusi Intelijen Ancaman'] },
    'SI-7':  { desc: 'Integritas perangkat lunak dan firmware sistem informasi dipantau dan dilindungi.', bukti: ['Laporan File Integrity Monitoring (FIM)', 'Konfigurasi Pemeriksaan Integritas'] },
    'SI-10': { desc: 'Sistem informasi memeriksa validitas informasi yang dimasukkan.', bukti: ['Mekanisme Input Validation pada Aplikasi', 'Hasil SAST/DAST', 'Laporan Pengujian Web Application'] },
};

// Read existing nist_csf.json to get all unique SP800 IDs referenced
const nistData = JSON.parse(fs.readFileSync('public/data/nist_csf.json', 'utf8'));
const allSpIds = new Set();
nistData.forEach(c => {
    (c.sp80053 || []).forEach(sp => allSpIds.add(sp));
});

// Also include IDs we have detailed data for
Object.keys(controlData).forEach(id => allSpIds.add(id));

// Generate the SP 800-53 dataset
const sp800List = [...allSpIds].sort((a, b) => {
    const [fa, na] = a.split('-');
    const [fb, nb] = b.split('-');
    if (fa !== fb) return fa.localeCompare(fb);
    return parseInt(na) - parseInt(fb.replace(/\D/g,''));
}).map(id => {
    const family = id.split('-')[0];
    const familyName = families[family] || family;
    const cd = controlData[id] || null;
    
    // Find NIST controls that reference this SP control
    const relatedNist = nistData.filter(c => (c.sp80053 || []).includes(id)).map(c => c.id);
    
    // Derive a generic description if not in curated list
    const desc = cd ? cd.desc : `Kontrol ${id} menerapkan persyaratan keamanan pada keluarga ${familyName} sesuai standar NIST SP 800-53 Rev 5.`;
    
    // Derive bukti: curated first, then from related NIST evidence
    let bukti = cd ? cd.bukti : [];
    if (bukti.length === 0 && relatedNist.length > 0) {
        // Pull from first related NIST control's evidence
        const firstRelated = nistData.find(c => c.id === relatedNist[0]);
        if (firstRelated && firstRelated.evidence) {
            bukti = firstRelated.evidence.slice(0, 3);
        }
    }
    if (bukti.length === 0) {
        bukti = [`Dokumentasi Kepatuhan ${id}`, `Laporan Implementasi ${familyName}`];
    }
    
    return {
        id,
        family,
        family_name: familyName,
        description: desc,
        bukti_audit: bukti,
        nist_references: relatedNist
    };
});

fs.writeFileSync('public/data/sp800_53.json', JSON.stringify(sp800List, null, 2));
console.log(`Generated ${sp800List.length} SP 800-53 controls to public/data/sp800_53.json`);
