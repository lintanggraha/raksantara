const zlib = require('zlib');
const https = require('https');
const fs = require('fs');
const code = `graph TD
    A[Mulai] --> B[Buka Detail Risiko Aktif]
    B --> C[Masuk ke Tab / Menu KRI]
    C --> D[Tambah Indikator KRI Baru]
    D --> E[Tentukan Threshold: Batas Aman, Hati-hati, Bahaya]
    E --> F[KRI Berhasil Disimpan]
    F --> G[Log Nilai Realisasi KRI Aktual Berkala]
    G --> H{Sistem Cek Nilai vs Threshold}
    H -->|Aman| I[Status KRI: Normal]
    H -->|Hati-hati| J[Status KRI: Warning]
    H -->|Bahaya| K[Status KRI: Critical]
    K --> L[Membentuk Risk Alert Notifikasi]
    J --> M[Update Grafik Tren KRI]
    I --> M
    L --> M
    M --> N[Selesai]`;
const data = Buffer.from(code, 'utf8');
const compressed = zlib.deflateSync(data);
const payload = compressed.toString('base64').replace(/\+/g, '-').replace(/\//g, '_');
const url = 'https://kroki.io/mermaid/png/' + payload;
https.get(url, (res) => {
    console.log(res.statusCode);
    if(res.statusCode === 200) {
        res.pipe(fs.createWriteStream('docs/images/flowchart_2.png'));
    }
});
