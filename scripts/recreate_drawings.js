const fs = require('fs');
const zlib = require('zlib');
const https = require('https');
const path = require('path');

const imgDir = path.join(__dirname, '../docs/images');

const flowcharts = [
`graph TD
    A[Mulai] --> B[Masuk Risk Monitoring]
    B --> C{Apakah data risiko banyak?}
    C -->|Ya| D[Import Spreadsheet Excel/CSV]
    C -->|Tidak| E[Isi Formulir Manual]
    D --> F[Validasi Sistem]
    E --> G[Tentukan Konteks, Penyebab, Dampak Inherent]
    F --> H{Format Valid?}
    H -->|Tidak| D
    H -->|Ya| G
    G --> I[Hitung Inherent Risk Score]
    I --> J{Ada kontrol yang sudah berjalan?}
    J -->|Tidak| L[Simpan Risiko Draft]
    J -->|Ya| K[Akses RCSA dan Evaluasi Kontrol]
    K --> M[Hitung Residual Risk Score]
    M --> L
    L --> N[Pengajuan ke Superadmin/Atasan]
    N --> O{Disetujui?}
    O -->|Tidak| P[Revisi Draft Risiko]
    P --> N
    O -->|Ya| Q[Risiko Aktif]
    Q --> R[Selesai]`,

`graph TD
    A[Mulai] --> B[Masuk Menu KRI]
    B --> C[Tentukan Parameter KRI & Batas Kritis]
    C --> D[Log Nilai Aktual]
    D --> E{Apakah Nilai Melebihi Batas Bahaya?}
    E -->|Ya| F[Status KRI Critical / Alert]
    F --> G[Sistem Mengirim Warning ke PIC]
    E -->|Tidak| H{Apakah Melebihi Batas Hati-hati?}
    H -->|Ya| I[Status KRI Warning]
    H -->|Tidak| J[Status KRI Normal]
    I --> K[Sistem Update Log]
    J --> K
    G --> K
    K --> L{Sudah Waktu Siklus Berikutnya?}
    L -->|Belum| K
    L -->|Ya| D`,

`graph TD
    A[Mulai] --> B[Akses Mitigation Kanban]
    B --> C{Punya Playbook/Template?}
    C -->|Ya| E[Gunakan Template Playbook]
    C -->|Tidak| D[Buat Rencana Mitigasi Custom]
    D --> F[Kartu Mitigasi Masuk Ke To-Do]
    E --> F
    F --> G[Mulai Pengerjaan & Pindah ke In-Progress]
    G --> H{Selesai Dikerjakan?}
    H -->|Belum| I[Update Progress & Simpan Pekerjaan]
    I --> G
    H -->|Ya| J[Lampirkan Dokumen Evidence]
    J --> K{Evidence Valid?}
    K -->|Tidak| I
    K -->|Ya| L[Pindahkan Kartu ke Done]
    L --> M[Turunkan Tingkat Residual Risk]
    M --> N[Selesai]`,

`graph TD
    A[Mulai] --> B[Masuk Report Dashboard]
    B --> C{Butuh Laporan Otomatis?}
    C -->|Ya| D[Set Frekuensi & Email Scheduled Report]
    D --> E{Waktu Sesuai Jadwal?}
    E -->|Belum| E
    E -->|Ya| F[Background Generate Laporan]
    F --> G[Kirim Laporan Otomatis via Email]
    C -->|Tidak| H[Pilih Parameter Laporan Manual]
    H --> I[Ekspor Data secara Langsung]
    I --> J[Unduh Laporan Selesai]
    G --> J
    J --> K[Selesai]`,

`graph TD
    A[Mulai] --> B[Monitor Insiden]
    B --> C{Ada Insiden Baru?}
    C -->|Tidak| B
    C -->|Ya| D[Log Detail Insiden]
    D --> E{Berdampak Pada Operasional Inti?}
    E -->|Tidak| F[Tutup Insiden Normal]
    E -->|Ya| G[Tautkan ke Buku Risiko / Eskalasi]
    G --> H[Lakukan Analisis BIA: MAO RTO]
    H --> I[Aktifkan BCP Skenario Pemulihan]
    I --> J{Sistem Telah Pulih?}
    J -->|Tidak| I
    J -->|Ya| K[Akhiri Skenario BCP]
    K --> F
    F --> L[Selesai]`,

`graph TD
    A[Mulai] --> B[Pilih Ranah Program Audit]
    B --> C[Set Program & Distribusi ke Terperiksa]
    C --> D[Auditee Menindaklanjuti & Submit Dokumen]
    D --> E{Tanggapan/Dokumen Cukup Valid?}
    E -->|Tidak| F[Kembalikan untuk Dilengkapi]
    F --> D
    E -->|Ya| G[Auditor Menutup Rekomendasi Temuan]
    G --> H{Ada Temuan Lain?}
    H -->|Ya| C
    H -->|Tidak| I[Ekspor Matriks Kepatuhan]
    I --> J[Selesai]`,

`graph TD
    A[Mulai] --> B[Masuk Panel Superadmin]
    B --> C{Ubah Master Data Utama?}
    C -->|Ya| D[Ubah Parameter Skala / Dampak / Kategori]
    C -->|Tidak| E{Butuh Restrukturisasi Akses?}
    E -->|Ya| F[Ubah Divisi / Role Pengguna / Reset]
    E -->|Tidak| G[Melihat Laporan Audit Trail]
    D --> H[Validasi Dampak pada Data Aktif]
    H --> I{Aman Diubah?}
    I -->|Tidak| J[Batalkan Perubahan]
    I -->|Ya| K[Update Basis Data Seluruh Sistem]
    F --> K
    K --> L[Tercetak di Audit Logs]
    J --> L
    G --> M[Selesai]
    L --> M`
];

async function run() {
    for (let i = 0; i < flowcharts.length; i++) {
        console.log(`Processing flowchart ${i + 1}...`);
        try {
            const data = Buffer.from(flowcharts[i], 'utf8');
            const compressed = zlib.deflateSync(data);
            const payload = compressed.toString('base64').replace(/\+/g, '-').replace(/\//g, '_');
            
            const filename = `flowchart_${i + 1}.png`;
            const dest = path.join(imgDir, filename);
            const url = `https://kroki.io/mermaid/png/${payload}`;
            
            await new Promise((resolve, reject) => {
                https.get(url, (res) => {
                    if (res.statusCode !== 200) {
                        reject(new Error(`Kroki returned ${res.statusCode}`));
                        return;
                    }
                    const file = fs.createWriteStream(dest);
                    res.pipe(file);
                    file.on('finish', resolve);
                }).on('error', reject);
            });
            console.log(`Flowchart ${i + 1} overwritten.`);
        } catch (e) {
            console.error(e);
        }
    }
}
run();
