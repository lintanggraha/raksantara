# Risk Management System (RMS)

![Laravel](https://img.shields.io/badge/Laravel-12.x-FF2D20?logo=laravel&logoColor=white)
![PHP](https://img.shields.io/badge/PHP-8.2%2B-777BB4?logo=php&logoColor=white)
![MySQL](https://img.shields.io/badge/Database-MySQL-4479A1?logo=mysql&logoColor=white)
![Redis Ready](https://img.shields.io/badge/Cache-Redis%20Ready-DC382D?logo=redis&logoColor=white)

Web app untuk manajemen risiko organisasi: registrasi risiko, analisis inherent/residual, monitoring dashboard, tren risiko, kepatuhan, dan pelaporan manajerial dalam satu sistem.

## Daftar Isi
- [Ringkasan](#ringkasan)
- [Fitur Utama](#fitur-utama)
- [Stack Teknologi](#stack-teknologi)
- [Persyaratan Sistem](#persyaratan-sistem)
- [Instalasi Lokal](#instalasi-lokal)
- [Bootstrap Akun Pertama](#bootstrap-akun-pertama)
- [Konfigurasi Redis](#konfigurasi-redis)
- [Scheduler dan Automation](#scheduler-dan-automation)
- [Endpoint Penting](#endpoint-penting)
- [Import Audit Monitoring Dashboard](#import-audit-monitoring-dashboard)
- [Perintah Artisan Kustom](#perintah-artisan-kustom)
- [Struktur Direktori](#struktur-direktori)
- [Deployment](#deployment)
- [Testing](#testing)
- [Troubleshooting Ringkas](#troubleshooting-ringkas)

## Ringkasan
RMS dirancang untuk membantu tim risk/compliance menjalankan siklus manajemen risiko end-to-end:
- mencatat risk register secara terstruktur,
- mengukur inherent dan residual risk,
- memonitor progres mitigasi dan SLA treatment,
- menganalisis tren risiko lintas periode,
- menyiapkan laporan untuk manajemen dan Board.

## Fitur Utama
- Risk Register CRUD lengkap dengan kategori, penyebab, owner/divisi, dan status.
- Perhitungan Inherent vs Residual Analysis (probability, impact, risk score, risk level).
- Risk Monitoring Dashboard dengan ringkasan KPI, heatmap trends, dan portfolio overview.
- Audit Monitoring Dashboard (`/audit`) dengan tab eksekutif, rekap, maps, progress, trend, outstanding, dan duedate maturity.
- Import XLS untuk Audit Monitoring Dashboard via UI (`Import XLS`) langsung dari halaman `/audit`.
- Risk Trend Analysis (monthly, quarterly, yearly) via halaman dan endpoint JSON.
- Treatment Management, deadline monitoring, dan alert scanner otomatis.
- Evidence Attachment dan komentar risiko (termasuk mention).
- Approval workflow untuk perubahan status risiko.
- Audit Log dan Risk Alert management untuk superadmin.
- Scheduled Reports + ekspor laporan (`xls`, `xlsx`, `pdf`, `pptx`).
- Compliance Work Program (Internal Audit, Risk Management, Compliance).
- User Guide bawaan aplikasi pada route `/user-guide`.

## Stack Teknologi
- Backend: Laravel 12
- Bahasa: PHP 8.2+ (direkomendasikan 8.3)
- Database: MySQL/MariaDB
- Cache/Session: File (default), Redis (direkomendasikan untuk production)
- Frontend build: Vite, Bootstrap 5, Vue 3
- Export/reporting: Laravel Excel, Dompdf, PHPPresentation

## Persyaratan Sistem
- PHP `>=8.2` dengan extension umum Laravel + `pdo_mysql`.
- Database MySQL/MariaDB aktif.
- Node.js + npm untuk build asset frontend.
- Composer v2.
- Redis (opsional tapi direkomendasikan untuk performa production).

## Instalasi Lokal
1. Clone repository.
2. Install dependency PHP.

```bash
composer install
```

3. Install dependency frontend.

```bash
npm install
```

4. Buat file environment.
- Linux/macOS:

```bash
cp .env.example .env
```

- Windows (PowerShell):

```powershell
Copy-Item .env.example .env
```

5. Isi konfigurasi database di `.env`.
6. Generate application key.

```bash
php artisan key:generate
```

7. Jalankan migrasi database.

```bash
php artisan migrate
```

8. Build asset frontend.

```bash
npm run build
```

9. Jalankan aplikasi.

```bash
php artisan serve
```

Opsional untuk mode development frontend hot-reload:

```bash
npm run dev
```

## Bootstrap Akun Pertama
Jika belum ada akun superadmin, jalankan:

```bash
php artisan user:promote-superadmin <username> --create --name="Nama Admin" --email="admin@company.local"
```

Perintah di atas akan membuat user baru (jika belum ada) lalu mempromosikannya menjadi superadmin.

## Konfigurasi Redis
Untuk performa lebih baik di environment production, aktifkan Redis di `.env`:

```env
CACHE_DRIVER=redis
SESSION_DRIVER=redis
REDIS_CLIENT=phpredis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379
```

Rekomendasi awal:
- Biarkan `QUEUE_CONNECTION=sync` terlebih dahulu.
- Setelah stabil, baru pindah ke queue async jika diperlukan.

Setelah ubah `.env`, refresh cache konfigurasi:

```bash
php artisan optimize:clear
php artisan config:cache
```

## Scheduler dan Automation
Project ini memakai scheduler untuk job rutin:
- `risk:scan-alerts --days=3` (harian)
- `reports:dispatch-scheduled` (tiap menit)

Aktifkan cron di server Linux:

```bash
* * * * * cd /path/to/project && php artisan schedule:run >> /dev/null 2>&1
```

## Endpoint Penting
- Liveness: `GET /health`
- Readiness: `GET /health/ready`
- Monitoring dashboard: `GET /risk-monitoring`
- Audit monitoring dashboard: `GET /audit`
- Import audit monitoring file: `POST /audit/import`
- Trend analysis: `GET /trend-analysis`
- User guide: `GET /user-guide`

## Import Audit Monitoring Dashboard
Fitur ini dipakai untuk mengisi data dashboard audit dari file Excel (`.xls/.xlsx`) tanpa CLI:

1. Buka halaman `GET /audit`.
2. Klik tombol **Import XLS** di area filter.
3. Upload file monitoring audit.
4. Setelah sukses, data dashboard audit langsung diperbarui dan notifikasi sukses muncul.

Catatan:
- Sistem memprioritaskan sheet `SUMMARY UPDATE` saat parsing file audit.
- Jika belum ada import, dashboard audit tampil kosong agar perubahan pasca-import terlihat jelas.
- Akses import audit dibatasi untuk user divisi Compliance (non-superadmin).

## Perintah Artisan Kustom
- Scan alert risiko:

```bash
php artisan risk:scan-alerts --days=3
```

- Dispatch scheduled reports:

```bash
php artisan reports:dispatch-scheduled
```

- Import risk register dari Excel:

```bash
php artisan risk:import-register /path/file.xlsx
```

- Promote user menjadi superadmin:

```bash
php artisan user:promote-superadmin <username>
```

## Struktur Direktori
- `app/Http/Controllers` = endpoint dan business flow utama.
- `app/Console/Commands` = command automation (alert scan, scheduled report, import).
- `app/Models` / `app/*.php` = model Eloquent.
- `resources/views` = Blade templates.
- `routes/web.php` = route web utama.
- `routes/api.php` = route API.
- `database/migrations` = definisi skema database.
- `docs` = dokumentasi tambahan (termasuk deploy offline + Redis).

## Deployment
Panduan deploy offline + Redis untuk pemula tersedia di:
- [docs/DEPLOY_OFFLINE_REDIS_BEGINNER.md](docs/DEPLOY_OFFLINE_REDIS_BEGINNER.md)

Untuk production, jalankan cache bootstrap Laravel:

```bash
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache
```

## Testing
Menjalankan test:

```bash
php artisan test
```

## Troubleshooting Ringkas
- Perubahan `.env` tidak terbaca:

```bash
php artisan optimize:clear
php artisan config:cache
```

- Error cache Redis:
1. Pastikan service Redis aktif.
2. Pastikan extension `phpredis` terpasang.
3. Verifikasi `REDIS_HOST` dan `REDIS_PORT`.

- Aset frontend tidak update:

```bash
npm run build
```

## Catatan
Repository ini ditujukan untuk kebutuhan manajemen risiko internal.  
Hindari memasukkan kredensial, secret key, atau data sensitif ke repository.
