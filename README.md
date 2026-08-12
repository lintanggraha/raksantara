# Risk Management System

![Laravel](https://img.shields.io/badge/Laravel-12.x-FF2D20?logo=laravel&logoColor=white)
![PHP](https://img.shields.io/badge/PHP-8.2%2B-777BB4?logo=php&logoColor=white)
![Database](https://img.shields.io/badge/Database-MySQL%20%7C%20SQLite-4479A1?logo=mysql&logoColor=white)
![Frontend](https://img.shields.io/badge/Frontend-Vite-646CFF?logo=vite&logoColor=white)

**Risk Management System (RMS)** adalah aplikasi Laravel untuk mengelola siklus Enterprise Risk Management (ERM) dari risk register sampai assurance, incident learning, committee governance, evidence, dan operational resilience. Aplikasi ini dirancang sebagai **Laravel monolith**: web application, database, upload evidence, queue, dan scheduler dijalankan dalam lingkungan PHP yang sama atau service operasional pendukungnya.

> Gunakan panduan [local testing](docs/LOCAL_TESTING.md) untuk menjalankan aplikasi tanpa akun cloud maupun database berbayar.

## Overview

RMS menghubungkan proses identifikasi, penilaian, treatment, monitoring, assurance, dan review risiko ke dalam satu data model yang dapat diaudit. Scope pengguna diterapkan berdasarkan **role, organizational unit, risk owner, division, dan permission**.

| Domain | Kapabilitas utama |
|---|---|
| Risk lifecycle | Risk register, inherent/residual analysis, treatment, quantitative loss, heatmap, recap, trend, dan status approval. |
| Control assurance | RCSA, control register, control testing, evidence, remediation, regulatory framework mapping, dan risk acceptance. |
| Monitoring | KRI threshold, log nilai, data lineage, freshness, quality status, auto-sync, dan signed external ingestion. |
| Incident learning | Incident register, root cause, CAPA, overdue action, lesson learned, serta feedback review ke risk, control, dan KRI. |
| Governance | Enterprise risk profile, exposure concentration, committee decision register, scenario analysis, dan maturity assessment. |
| Resilience | BIA/BCP, backup register, restore and ransomware-recovery drill, retention schedule, legal hold, purge approval, dan Excel evidence pack. |
| Compliance | Audit dashboard, compliance work program, framework explorer, threat intelligence, reporting, dan audit trail. |

## Capability Map

### Risk register and treatment

Risk workspace menyatukan register, heatmap, dan recap. Pengguna dapat membuat risk berdasarkan risk owner yang diizinkan, mencatat inherent dan residual analysis, memilih mitigation playbook atau treatment manual, mengelola PIC/deadline/effectiveness, dan melampirkan evidence. Perubahan status risk tertentu masuk ke approval queue agar governance tetap terjaga.

| Workspace | Route | Ringkasan |
|---|---|---|
| Risk Monitoring | `/risk-monitoring` | Executive, operational control, dan risk-intelligence dashboard dengan user preference. |
| Risk Register | `/risks` | Register, heatmap, recap, filter owner/year, import, dan export. |
| Risk Detail | `/risks/{risk}` | Basic, inherent, treatment, RCSA, residual, BCM, governance, dan KRI. |
| Treatment Kanban | `/mitigation-kanban` | Prioritas action berdasarkan status, SLA, overdue, blocker, dan evidence. |
| Trend Analysis | `/trend-analysis` | Trend inherent/residual score, category, owner concentration, dan heatmap trend. |

### Control assurance and risk acceptance

Setiap risk dapat memiliki control yang dikelola dalam workspace Governance. Control menyimpan owner, frekuensi test, status, hasil effectiveness, bukti, findings, dan remediation. Risk acceptance mendukung request, justification, compensating control, expiry date, serta keputusan superadmin yang dicatat sebagai audit event.

Regulatory control mapping memungkinkan Compliance memetakan control ke framework, requirement reference, status coverage, evidence reference, dan review date. Ini menghubungkan assurance dengan kebutuhan compliance tanpa mengubah control menjadi data statis.

### KRI data quality and integration readiness

KRI menggunakan threshold `lower_is_better` atau `higher_is_better` dan otomatis menghitung Green, Amber, atau Red. Setiap reading dapat menyimpan **data source, source identifier, source timestamp, source reference, dan quality status**. Quality service menandai data sebagai `valid`, `stale`, `missing`, atau `invalid` berdasarkan kelengkapan, nilai, dan expected refresh interval.

Endpoint `POST /api/kri/ingestion` mendukung ingestion eksternal dengan HMAC SHA-256, source allowlist, timestamp window, throttle, dan event idempotency. Endpoint ini **nonaktif secara default** sampai secret dan source allowlist dikonfigurasi pada environment target.

### Incident learning and CAPA

Incident dapat dikaitkan langsung ke risk lalu dikelola sampai penutupan melalui root cause, immediate action, corrective action, preventive action, owner, due date, verification, dan lessons learned. Post-incident review memaksa feedback eksplisit ke risk register, control assurance, dan KRI setelah CAPA selesai.

### Enterprise governance and strategic review

Enterprise Risk Profile mengagregasi top residual exposure serta konsentrasi exposure berdasarkan owner dan category. Committee Decision Register menyimpan keputusan rapat, related risk, action owner, target date, status, dan closure notes. Scenario Analysis memodelkan stress scenario beserta dampak per risk, sementara Maturity Review mencatat current score, target score, evidence, owner, dan improvement plan per governance domain.

### Third-party risk and operational resilience

Vendor lifecycle mencakup risk rating, onboarding, due diligence, periodic review, decision, findings, dan treatment. Operational Resilience mengelola backup register, encryption/checksum/reference, restore test, RTO/RPO evidence, ransomware recovery drill, retention schedule, legal hold, independent purge approval, serta execution evidence. Compliance dapat mengekspor seluruh evidence resilience ke Excel audit pack.

### Audit, compliance, and reporting

Audit Dashboard menerima import spreadsheet dan mendukung export PDF, XLS/XLSX, serta PPTX. Compliance Programs mengelola work program Internal Audit, Risk Management, dan Governance & Compliance. Framework Explorer menyediakan referensi ISO 27001, NIST CSF 2.0, SEOJK, Panduan Resiliensi OJK, dan PBI 02/2024. Threat Intelligence menggabungkan NVD CVE, CISA KEV, dan OWASP mapping melalui scheduler serta cache; refresh tidak dijalankan dari request pengguna.

Manual report export mendukung scope monitoring, board, risks, heatmap, recap, dan overall. Scheduled Reports mendukung frequency daily, weekly, dan monthly dengan recipient, timezone, filter, run-now, pause, dan run history.

## Identity, Access, and Security

RMS mempertahankan compatibility dengan division/risk-owner legacy sambil menambahkan RBAC yang dapat diadopsi bertahap.

| Control | Implementasi |
|---|---|
| RBAC | Role, permission, organizational unit, assignment scope, expiry access, dan audit trail assignment. |
| Superadmin | Administrasi user, parameter, access control, approval, alerts, dan audit oversight. |
| Compliance | Cross-owner monitoring, audit, control mapping, governance decision, third-party review, resilience, dan retention control. |
| MFA | TOTP, recovery code sekali pakai, challenge setelah password, rotasi code, dan audit event. |
| SSO readiness | OpenID Connect authorization-code handoff, state/nonce, discovery, claim mapping, dan default fail-closed. |
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

Suite tervalidasi saat ini mencakup **133 tests dan 714 assertions**. CI menjalankan backend test/security, frontend build/security, syntax validation, serta dependency audit.

## Documentation

| Document | Purpose |
|---|---|
| [Feature flows and ERD](docs/FEATURE_FLOWS_AND_ERD.md) | Domain flow, data relation, dan batas modul utama. |
| [Local testing guide](docs/LOCAL_TESTING.md) | Setup SQLite, test commands, health checks, dan troubleshooting lokal. |
| [Railway deployment guide](docs/RAILWAY_DEPLOYMENT.md) | Referensi native PHP deployment jika kelak dibutuhkan. |

## Production Notes

Production deployment memerlukan persistent database, session/cache backend, object storage untuk evidence attachment, dan worker/scheduler terpisah bila scheduled report atau threat-intelligence refresh digunakan. Set `APP_DEBUG=false`, gunakan secret manager/environment variables, dan jangan commit credential, token, data production, atau private evidence ke repository.

---

Internal use only.
