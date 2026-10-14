# Raksantara

**Platform Enterprise Risk Management open-source untuk lembaga keuangan dan organisasi yang diatur regulator.**

![Laravel](https://img.shields.io/badge/Laravel-12.x-FF2D20?logo=laravel&logoColor=white)
![PHP](https://img.shields.io/badge/PHP-8.2%2B-777BB4?logo=php&logoColor=white)
![Database](https://img.shields.io/badge/Database-MySQL%20%7C%20SQLite-4479A1?logo=mysql&logoColor=white)
![Frontend](https://img.shields.io/badge/Frontend-Vite-646CFF?logo=vite&logoColor=white)

**Raksantara** adalah aplikasi Laravel untuk mengelola siklus Enterprise Risk Management (ERM): risk register, risk appetite, KRI, incident learning, business continuity per layanan, committee governance, evidence, dan operational resilience. Aplikasi ini dirancang sebagai **Laravel monolith**: web application, database, upload evidence, queue, dan scheduler dijalankan dalam lingkungan PHP yang sama atau service operasional pendukungnya.

> Gunakan panduan [local testing](docs/LOCAL_TESTING.md) untuk menjalankan aplikasi tanpa akun cloud maupun database berbayar.

## Overview

Raksantara menghubungkan proses identifikasi, penilaian, treatment, monitoring, dan review risiko ke dalam satu data model yang dapat diaudit. Scope pengguna diterapkan berdasarkan **role, organizational unit, risk owner, division, dan permission**.

| Domain | Kapabilitas utama |
|---|---|
| Dashboard | Dashboard Ringkasan (appetite, KRI, overdue action, loss, incident, attention list, heatmap drill-down, top risks, trend) dan dashboard Analisis, dengan filter tahun/owner/kategori dan agregasi di sisi database. |
| Risk lifecycle | Risk register, inherent/residual analysis, treatment, quantitative loss, heatmap, recap, trend, snapshot skor harian, dan status approval. |
| Risk appetite | Appetite statement per kategori risiko dengan batas appetite dan toleransi, default korporat, override per risiko, approval board, dan coverage register. |
| Monitoring | KRI threshold, library KRI siap pakai, status *no data*/*stale*, log nilai, data lineage, quality status, auto-sync, dan signed external ingestion. |
| Incident learning | Incident register, root cause, CAPA, overdue action, lesson learned, serta feedback review ke risk dan KRI. |
| Governance | Enterprise risk profile, exposure concentration, committee decision register, scenario analysis, dan maturity assessment. |
| Resilience | Katalog layanan dengan BIA, BCP, dan riwayat uji per layanan (ISO 22301), backup register, restore and ransomware-recovery drill, retention schedule, legal hold, purge approval, dan Excel evidence pack. |
| Compliance | Audit dashboard, compliance work program, framework explorer, threat intelligence, reporting, dan audit trail. |

## Capability Map

### Risk register and treatment

Risk workspace menyatukan register, heatmap, dan recap. Pengguna dapat membuat risk berdasarkan risk owner yang diizinkan, mencatat inherent dan residual analysis, memilih mitigation playbook atau treatment manual, mengelola PIC/deadline/effectiveness, dan melampirkan evidence. Perubahan status risk tertentu masuk ke approval queue agar governance tetap terjaga.

| Workspace | Route | Ringkasan |
|---|---|---|
| Dashboard | `/dashboard` | Ringkasan (default) dan Analisis (`?view=analytical`); setiap section dimuat terpisah dengan loading dan empty state. |
| Risk Register | `/risks` | Register, heatmap, recap, filter owner/year, import, dan export. |
| Risk Detail | `/risks/{risk}` | Basic, inherent, treatment, residual, layanan terdampak, appetite efektif, dan KRI. |
| Risk Appetite | `/risk-appetites` | Appetite per kategori, default, coverage, dan daftar risiko yang melewati batas. |
| Business Continuity | `/bcm` | Katalog layanan, BIA, BCP, dan uji BCP per layanan. |
| Treatment Kanban | `/mitigation-kanban` | Prioritas action berdasarkan status, SLA, overdue, blocker, dan evidence. |
| Trend Analysis | `/trend-analysis` | Trend inherent/residual score, category, owner concentration, dan heatmap trend. |

Semua tabel dapat diurutkan per kolom dengan mengklik judul kolom (angka, Rupiah, persentase, dan tanggal dikenali otomatis).

### Risk appetite

Appetite ditetapkan per **kategori risiko** dengan satu appetite default korporat sebagai fallback. Untuk setiap risiko, appetite efektif ditentukan dengan urutan: override pada risiko → appetite aktif kategori → default. Status (`within`, `warning`, `breach`) dihitung dari skor residual terhadap batas appetite dan toleransi, dengan logika yang sama di PHP dan SQL sehingga dashboard dan halaman detail selalu konsisten. Appetite baru berstatus draft sampai referensi persetujuan dan tanggal approval diisi. `php artisan appetite:seed-sample` membuat draft appetite contoh dari profil risiko yang ada (`--rollback` untuk menghapusnya).

### Business continuity per layanan

Mengikuti ISO 22301 klausul 8.2.2 dan ISO/TS 22317, BIA dilakukan **per layanan bisnis**, bukan per risiko. Dampak dinilai pada beberapa horizon waktu; MTPD adalah horizon pertama dengan dampak ≥ 3, tier layanan diturunkan dari MTPD, dan RTO wajib ≤ MTPD. BIA disetujui dengan prinsip maker-checker (penilai tidak boleh menyetujui sendiri), BCP disusun per layanan, dan riwayat uji BCP dicatat beserta temuannya. Risiko dapat ditautkan ke layanan yang terdampak. `php artisan bcm:seed-service-catalog` mengisi katalog layanan awal.

### KRI data quality and integration readiness

KRI menggunakan threshold `lower_is_better` atau `higher_is_better` dan otomatis menghitung Green, Amber, atau Red. Setiap reading dapat menyimpan **data source, source identifier, source timestamp, source reference, dan quality status**. Quality service menandai data sebagai `valid`, `stale`, `missing`, atau `invalid` berdasarkan kelengkapan, nilai, dan expected refresh interval. KRI yang belum pernah diisi ditampilkan sebagai **No data yet** dan tidak dihitung sebagai Green.

`php artisan kri:import-library [path]` memuat library KRI dari file JSON (default `storage/app/kri/kri_library_2026.json`, tidak ikut repository) dan menautkannya ke risiko di register. Setiap item berisi `risk_code`, `metric_name`, `description`, `threshold_type`, `threshold_warn`, `threshold_critical`, `data_source`, `source_identifier`, dan `expected_refresh_minutes`. Import bersifat idempotent (satu KRI per risiko, ditandai tag `kri-library-2026`); gunakan `--dry-run` untuk validasi dan `--rollback` untuk menghapus KRI yang belum punya data.

Endpoint `POST /api/kri/ingestion` mendukung ingestion eksternal dengan HMAC SHA-256, source allowlist, timestamp window, throttle, dan event idempotency. Endpoint ini **nonaktif secara default** sampai secret dan source allowlist dikonfigurasi pada environment target.

### Incident learning and CAPA

Incident dapat dikaitkan langsung ke risk lalu dikelola sampai penutupan melalui root cause, immediate action, corrective action, preventive action, owner, due date, verification, dan lessons learned. Post-incident review memaksa feedback eksplisit ke risk register dan KRI setelah CAPA selesai.

### Enterprise governance and strategic review

Enterprise Risk Profile mengagregasi top residual exposure serta konsentrasi exposure berdasarkan owner dan category. Committee Decision Register menyimpan keputusan rapat, related risk, action owner, target date, status, dan closure notes. Scenario Analysis memodelkan stress scenario beserta dampak per risk, sementara Maturity Review mencatat current score, target score, evidence, owner, dan improvement plan per governance domain.

### Third-party risk and operational resilience

Vendor lifecycle mencakup risk rating, onboarding, due diligence, periodic review, decision, findings, dan treatment. Operational Resilience mengelola backup register, encryption/checksum/reference, restore test, RTO/RPO evidence, ransomware recovery drill, retention schedule, legal hold, independent purge approval, serta execution evidence. Compliance dapat mengekspor seluruh evidence resilience ke Excel audit pack.

### Audit, compliance, and reporting

Audit Dashboard menerima import spreadsheet dan mendukung export PDF, XLS/XLSX, serta PPTX. Compliance Programs mengelola work program Internal Audit, Risk Management, dan Governance & Compliance. Framework Explorer menyediakan referensi ISO 27001, NIST CSF 2.0, SEOJK, Panduan Resiliensi OJK, dan PBI 02/2024. Threat Intelligence menggabungkan NVD CVE, CISA KEV, dan OWASP mapping melalui scheduler serta cache; refresh tidak dijalankan dari request pengguna.

Manual report export mendukung scope monitoring, board, risks, heatmap, recap, dan overall. Scheduled Reports mendukung frequency daily, weekly, dan monthly dengan recipient, timezone, filter, run-now, pause, dan run history.

## Identity, Access, and Security

Raksantara mempertahankan compatibility dengan division/risk-owner legacy sambil menambahkan RBAC yang dapat diadopsi bertahap.

| Control | Implementasi |
|---|---|
| RBAC | Role, permission, organizational unit, assignment scope, expiry access, dan audit trail assignment. |
| Superadmin | Administrasi user, parameter, access control, approval, alerts, dan audit oversight. |
| Compliance | Cross-owner monitoring, audit, governance decision, third-party review, resilience, dan retention control. |
| MFA | Authenticator app (TOTP) dengan recovery code sekali pakai, serta **email OTP** yang dapat diaktifkan atau diwajibkan lewat `MFA_EMAIL_OTP_ENABLED` / `MFA_EMAIL_OTP_ENFORCED`. |
| SSO | OpenID Connect authorization-code flow dengan state/nonce, discovery, claim mapping, dan verifikasi email. Jika SSO nonaktif, salah konfigurasi, atau provider tidak tersedia, pengguna otomatis kembali ke login username/password. |
| Evidence protection | Route authorization, file validation, audit trail, dan rekomendasi object storage untuk production. |
| Audit integrity | Idempotency key untuk event export serta ledger audit untuk event security/governance penting. |

Public registration default-nya nonaktif. User baru menunggu approval division sebelum memperoleh akses operasional. Password policy, forced password change setelah reset, dan MFA enforcement diterapkan melalui route middleware.

## Technology Stack

| Layer | Technology |
|---|---|
| Backend | Laravel 12, PHP 8.2+ |
| Database | MySQL/MariaDB untuk deployment; SQLite untuk local testing dan test suite |
| Frontend | Vite, Vue 3, Bootstrap 5, Tabler UI, DataTables, Chart.js |
| Reporting | Dompdf, Laravel Excel, PHPPresentation |
| Integration | Guzzle, signed KRI ingestion, OpenID Connect readiness |
| Testing | PHPUnit feature suite dengan SQLite terisolasi |

## Local Quick Start

Gunakan SQLite untuk menjalankan aplikasi tanpa MySQL atau cloud service. Prasyaratnya adalah PHP 8.2+, Composer, dan Node.js 22+.

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
mkdir -p database
: > database/database.sqlite
```

Set nilai berikut di `.env`:

```dotenv
APP_ENV=local
APP_DEBUG=true
APP_URL=http://127.0.0.1:8000
DB_CONNECTION=sqlite
DB_DATABASE=/absolute/path/to/riskmanagement/database/database.sqlite
CACHE_STORE=file
SESSION_DRIVER=file
QUEUE_CONNECTION=sync
MAIL_MAILER=log
FILESYSTEM_DISK=local
```

Lalu migrate, seed, build asset, dan jalankan aplikasi:

```bash
php artisan migrate --seed
npm run build
php artisan serve
```

Buka `http://127.0.0.1:8000`. Untuk development asset dengan hot reload, jalankan `npm run dev` di terminal kedua.

Panduan lengkap termasuk Windows, testing environment terisolasi, health checks, dan local scheduler tersedia di [docs/LOCAL_TESTING.md](docs/LOCAL_TESTING.md).

## Data Organisasi (`private/`)

Repository ini tidak memuat data organisasi mana pun. Nama client, hasil audit, dan register risiko asli disimpan di folder `private/` yang di-gitignore; aplikasi membacanya bila ada dan memakai default generik atau contoh fiktif bila tidak ada. Lihat [private/README.md](private/README.md) untuk daftar file yang dikenali. Seed register risiko milik organisasi dapat ditaruh di `database/seeders/data/` (juga di-gitignore) dan dimuat oleh `RealWorldRiskRegisterSeeder`.

## Bootstrap First Superadmin

Jika belum ada akun superadmin, jalankan:

```bash
php artisan user:promote-superadmin <username> --create --name="Nama Admin" --email="admin@company.local"
```

Tambahkan `--division=` untuk menentukan division awal, atau `--password=` untuk menetapkan password manual. Tanpa `--password`, sistem menghasilkan temporary password dan memaksa user mengganti password saat login pertama.

## Scheduler and Background Work

Scheduler menjalankan alert scan harian, scheduled-report dispatch setiap menit, dan threat-intelligence refresh setiap 15 menit. Pada local development, jalankan scheduler dan worker di terminal terpisah bila workflow background perlu diuji.

```bash
php artisan schedule:work
php artisan queue:work
```

Untuk basic local testing, `QUEUE_CONNECTION=sync` sudah cukup. Jangan menjalankan scheduler terhadap database production dari mesin development.

## Health Checks

| Endpoint | Expected result |
|---|---|
| `/health` | Liveness JSON dengan status `ok`. |
| `/health/ready` | Readiness JSON; memeriksa database, cache, dan write access storage. |

## Testing and Quality Gates

Gunakan database testing terpisah. Jangan pernah menjalankan test terhadap database utama dari `.env`.

```bash
php artisan test
composer audit --no-interaction
npm audit --audit-level=low
npm run build
```

Suite saat ini mencakup **194 tests dan 1.249 assertions**. CI menjalankan backend test/security, frontend build/security, syntax validation, serta dependency audit.

## Documentation

| Document | Purpose |
|---|---|
| [Feature flows and ERD](docs/FEATURE_FLOWS_AND_ERD.md) | Domain flow, data relation, dan batas modul utama. |
| [Local testing guide](docs/LOCAL_TESTING.md) | Setup SQLite, test commands, health checks, dan troubleshooting lokal. |
| [Railway deployment guide](docs/RAILWAY_DEPLOYMENT.md) | Referensi native PHP deployment jika kelak dibutuhkan. |

## Production Notes

Production deployment memerlukan persistent database, session/cache backend, object storage untuk evidence attachment, dan worker/scheduler terpisah bila scheduled report atau threat-intelligence refresh digunakan. Set `APP_DEBUG=false`, gunakan secret manager/environment variables, dan jangan commit credential, token, data production, atau private evidence ke repository.

---

Kontribusi, laporan bug, dan usulan fitur dipersilakan melalui GitHub Issues dan pull request.
