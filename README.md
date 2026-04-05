# Risk Management System (RMS)

![Laravel](https://img.shields.io/badge/Laravel-12.x-FF2D20?logo=laravel&logoColor=white)
![PHP](https://img.shields.io/badge/PHP-8.2%2B-777BB4?logo=php&logoColor=white)
![MySQL](https://img.shields.io/badge/Database-MySQL-4479A1?logo=mysql&logoColor=white)
![Vite](https://img.shields.io/badge/Frontend-Vite-646CFF?logo=vite&logoColor=white)

Platform internal untuk workflow end-to-end manajemen risiko: monitoring, register risiko detail, KRI, incident, trend analysis, threat intelligence, audit/compliance, serta reporting manual dan terjadwal.

## Table of Contents

- [Overview](#overview)
- [Feature Map](#feature-map)
- [Access Model](#access-model)
- [Navigation](#navigation)
- [Security and Account Rules](#security-and-account-rules)
- [Tech Stack](#tech-stack)
- [System Requirements](#system-requirements)
- [Local Setup](#local-setup)
- [Bootstrap First Superadmin](#bootstrap-first-superadmin)
- [Run the App](#run-the-app)
- [Scheduler and Automation](#scheduler-and-automation)
- [Important Routes](#important-routes)
- [Import Features](#import-features)
- [Threat Intel Configuration](#threat-intel-configuration)
- [Testing](#testing)
- [Deployment Notes](#deployment-notes)
- [Troubleshooting](#troubleshooting)

## Overview

RMS dipakai untuk:

- menyimpan dan memelihara risk register secara terstruktur,
- memantau performa mitigasi dan SLA risiko,
- mengelola KRI (threshold + log nilai),
- mencatat incident yang terhubung langsung ke risk,
- menganalisis tren risiko lintas periode,
- meninjau threat intel (NVD + CISA KEV),
- menjalankan fungsi compliance, audit, dan OJK mitigation,
- mendistribusikan report manual maupun scheduled report via email.

## Feature Map

### 1) Risk Monitoring Dashboard (`/risk-monitoring`)

- Multi-widget dashboard dengan 3 mode tampilan:
  - Dashboard 1: Risk Overview
  - Dashboard 2: Risk Status & Core Matrix
  - Dashboard 3: Risk Root Cause Analysis
- Widget bisa:
  - show/hide,
  - drag and reorder,
  - auto-refresh via endpoint summary.
- Mendukung export report dari halaman monitoring.

### 2) Risk Register (`/risks`)

- Satu halaman kerja dengan 3 workspace:
  - Risk Register
  - Risk Heat Map
  - Risk Recapitulation
- Tabel register dengan:
  - sorting per kolom (client-side DataTables),
  - quick search,
  - owner filter,
  - column preset (`Overview`, `Full Table`, `Analysis`, `Treatment`, `Quantitative`).
- Kolom inti menampilkan `Risk ID`, `Year`, `Process`, `Sasaran Target`, dan risk identification.
- Action di setiap baris:
  - view risk detail,
  - edit/delete (non-superadmin sesuai scope),
  - quick jump ke tab KRI risk tersebut.
- Menampilkan ringkasan level risiko dan ringkasan KRI.
- Import risk register XLS/XLSX tersedia untuk divisi compliance.
- Modal import mendukung `dry-run`, override `year`, dan auto-sync KRI dari score residual/inherent hasil import.

### 3) Risk Form (Create/Edit)

Form risk disusun per tab:

1. Basic Information
2. Inherent Analysis
3. Risk Treatment
4. Residual Analysis
5. Group Radar
6. Quantitative Result

Basic tab juga memuat:

- `Risk ID` dan `Year` sebagai field baca-saja yang digenerate sistem,
- `Sasaran Target` untuk mendokumentasikan objective/target proses yang terkait risk.

Kuantitatif per risk mencakup:

- Inherent impact/probability/potential loss,
- Residual impact/probability/potential loss.

`Potential loss` dihitung otomatis dari impact x probability, tetapi tetap dapat disesuaikan manual bila diperlukan.

### 4) Risk Detail (`/risks/{id}`)

Tab detail risk:

- Basic
- Inherent
- Treatment (termasuk evidence per treatment)
- Residual
- Governance (risk evidence, comment, activity log, status approvals, alerts)
- Key Risk Indicators

Fitur governance:

- upload evidence risk/treatment/comment,
- comment dan mention user (`@username`),
- histori request approval perubahan status,
- histori alert untuk risk.

### 5) Key Risk Indicators (`/kris`)

- Dashboard KRI: Healthy / Warning / Critical / Total.
- Filter berdasarkan status dan risk.
- Add/Edit/Delete KRI.
- Log nilai metrik KRI dan kalkulasi status otomatis (`Green`, `Amber`, `Red`) berdasarkan threshold.
- Integrasi langsung dengan halaman detail risk (tab KRI).
- Risk import dapat membuat atau memperbarui KRI otomatis bertipe `Auto: Residual Risk Score`.

### 6) Mitigation Action Plan Kanban (`/mitigation-kanban`)

- Kanban board treatment status:
  - `Not Started`
  - `In Progress`
  - `Blocked`
  - `Completed`
- Drag-and-drop antar kolom dengan update status via AJAX.

### 7) Incident Management (`/incident-management`)

- Incident register terhubung ke risk.
- Summary card + chart:
  - trend incident bulanan,
  - distribusi mitigation verdict.
- CRUD incident sesuai scope user.
- Superadmin bersifat read-only di modul incident.

### 8) Risk Trend Analysis (`/trend-analysis`)

- Filter granularity: `monthly`, `quarterly`, `yearly`.
- Filter window periode: 3 bulan, 6 bulan, 1 tahun, 2 tahun.
- Panel utama:
  - Risk Score Trend (Inherent vs Residual),
  - Risk Score by Category,
  - Risk Status Distribution Over Time,
  - Risk Concentration by Owner,
  - Heatmap High-Risk Trend.
- Panel bisa di-drag untuk reorder sesuai preferensi user.

### 9) Threat Intelligence (`/threat-intelligence`)

- Source data:
  - NVD CVE API
  - CISA KEV Catalog (primary + mirror fallback)
- Filter:
  - keyword
  - severity
  - window hari (`7/30/90`)
  - limit item
- Menyediakan:
  - severity breakdown,
  - publication trend,
  - vendor exposure,
  - priority queue,
  - source health.

### 10) Compliance, Audit, OJK

- **Audit Dashboard** (`/audit`, compliance only):
  - import spreadsheet audit,
  - export PDF/XLS/XLSX/PPTX.
- **Mitigasi OJK** (`/ojk-mitigation`, compliance only):
  - import XLS/XLSX,
  - edit/update status per item.
- **Compliance Programs** (`/compliance/*`, compliance only):
  - Internal Audit,
  - Risk Management,
  - Governance & Compliance.

### 11) Superadmin Operations

- User Management:
  - approve/division verification,
  - ubah status superadmin,
  - reset password user (temporary password + force change).
- Risk Status Approvals:
  - approve/reject request perubahan status risk.
- Alerts:
  - monitoring alert risk + resolve.
- Audit Trail:
  - jejak aktivitas sistem.

### 12) Reporting

- Export report: `xls`, `xlsx`, `pdf`, `pptx`.
- Scope export: `monitoring`, `board`, `risks`, `heatmap`, `recap`, `overall`.
- Scheduled reports tersedia di `/reports/scheduled` untuk user verified non-superadmin.
- Scheduled reports mendukung:
  - format `pdf`, `xlsx`, `pptx`,
  - frekuensi `daily`, `weekly`, `monthly`,
  - filter `year`, `risk category`, `cause category`, `risk owner`,
  - multi-recipient email,
  - `run now`, `pause/activate`, dan histori run terbaru.
- Route scheduled report tidak muncul di navbar utama, jadi diakses lewat URL langsung.

## Access Model

| Role / Kondisi | Akses |
|---|---|
| Superadmin | Dashboard admin, user management, approval queue, alerts, audit trail, threat intel. Read-only untuk operasi risk register dan incident. |
| Verified user (non-compliance) | Risk menu (Monitoring, Register, Trend, KRI, Mitigation Kanban, Incident), Threat Intel, User Guide, dan Scheduled Reports via URL langsung. |
| Compliance division | Semua akses verified user + Audit + Mitigasi OJK + Compliance Programs + import data compliance lintas owner. |
| Pending verification | Hanya halaman pending approval sampai divisi diverifikasi superadmin. |

## Navigation

Navbar utama:

- `Risk` dropdown:
  - Risk Monitoring
  - Risk Register
  - Risk Trend Analysis
  - Key Risk Indicators
  - Mitigation Kanban
  - Incident Management
- `Threat Intel`
- `User Guide`
- `Audit` (compliance only)
- `Mitigasi OJK` (compliance only)
- `Compliance` dropdown (compliance only)

Catatan:

- `Scheduled Reports` aktif di route `/reports/scheduled`, tetapi saat ini tidak ditampilkan di navbar.

## Security and Account Rules

- Public registration default **off** (`ALLOW_PUBLIC_REGISTRATION=false`).
- Akun non-superadmin wajib melalui verifikasi divisi oleh superadmin.
- Password minimum:
  - minimal 8 karakter,
  - wajib kombinasi huruf dan angka.
- Superadmin reset password user akan memberi temporary password dan memaksa user mengganti password pada login berikutnya.
- Change password self-service tersedia di menu profile.

## Tech Stack

- Backend: Laravel 12
- Language: PHP 8.2+
- Database: MySQL / MariaDB
- Frontend: Bootstrap 5, Tabler UI, DataTables, Chart.js, Vite, Vue 3
- Reporting: Dompdf, Laravel Excel, PHPPresentation

## System Requirements

- PHP `>=8.2`
- Composer v2
- MySQL / MariaDB
- Node.js + npm
- (Opsional) Redis untuk cache/session production

## Local Setup

```bash
composer install
npm install
```

Copy env:

- Linux/macOS:

```bash
cp .env.example .env
```

- Windows PowerShell:

```powershell
Copy-Item .env.example .env
```

Generate key + migrate:

```bash
php artisan key:generate
php artisan migrate
```

Build assets:

```bash
npm run build
```

## Bootstrap First Superadmin

Jika belum ada akun superadmin:

```bash
php artisan user:promote-superadmin <username> --create --name="Nama Admin" --email="admin@company.local"
```

## Run the App

```bash
php artisan serve
```

Untuk frontend hot reload:

```bash
npm run dev
```

## Scheduler and Automation

Task scheduler utama:

- `risk:scan-alerts --days=3` setiap hari pukul `07:00`
- `reports:dispatch-scheduled` setiap menit
- `threat-intel:refresh --force` setiap `15` menit

Contoh cron Linux:

```bash
* * * * * cd /path/to/project && php artisan schedule:run >> /dev/null 2>&1
```

## Important Routes

- Health: `/health`, `/health/ready`
- Risk Monitoring: `/risk-monitoring`
- Risk Register: `/risks`
- Trend Analysis: `/trend-analysis`
- KRI: `/kris`
- Mitigation Kanban: `/mitigation-kanban`
- Incident Management: `/incident-management`
- Threat Intelligence: `/threat-intelligence`
- Audit Dashboard: `/audit` (compliance only)
- OJK Mitigation: `/ojk-mitigation` (compliance only)
- Compliance Programs: `/compliance/*` (compliance only)
- Scheduled Reports: `/reports/scheduled` (verified user non-superadmin; direct URL)
- User Guide: `/user-guide`

`/health/ready` memeriksa kesiapan database, cache, dan write access ke storage.

## Import Features

### Risk Register Import

- Endpoint: `POST /risks/import`
- Access: compliance division
- Format: XLS/XLSX
- Opsi tambahan:
  - `year` override bila tahun tidak ingin auto-detect dari file,
  - `dry_run=1` untuk validasi hasil tanpa menyimpan data.
- Setelah import sukses, sistem dapat auto-sync KRI `Auto: Residual Risk Score` dari nilai residual/inherent risk.

### Audit Import

- Endpoint: `POST /audit/import`
- Access: compliance division

### OJK Mitigation Import

- Endpoint: `POST /ojk-mitigation/import`
- Access: compliance division
- Format: XLS/XLSX

## Threat Intel Configuration

Set env berikut jika ingin optimalkan threat intel:

- `NVD_API_KEY` (optional, recommended)
- `NVD_API_BASE_URL`
- `CISA_KEV_FEED_URL`
- `CISA_KEV_MIRROR_URL`
- `THREAT_INTEL_AUTO_REFRESH_ENABLED` (default `true`)
- `THREAT_INTEL_AUTO_REFRESH_INTERVAL_MINUTES` (default `30`)
- `THREAT_INTEL_AUTO_REFRESH_FORCE` (default `true`)

Auto-refresh behavior (lokal / Laragon):

- Saat user login dan membuka aplikasi, middleware akan mengecek interval refresh threat intel.
- Jika sudah melewati interval, sistem akan refresh working-set threat intel otomatis.
- Command manual tersedia:

```bash
php artisan threat-intel:refresh --force
```

Untuk mode always-on (server/VM), scheduler juga menjalankan refresh berkala:

- `threat-intel:refresh --force` setiap 15 menit.

## Testing

Run all tests:

```bash
php artisan test
```

Jika environment lokal tidak punya MySQL test yang aktif, jalankan full regression dengan SQLite file-based test database.

Windows PowerShell (Laragon example):

```powershell
New-Item -ItemType File -Path 'database/testing.sqlite' -Force | Out-Null
$env:APP_ENV='testing'
$env:APP_DEBUG='false'
$env:APP_URL='http://localhost'
$env:DB_CONNECTION='sqlite'
$env:DB_DATABASE='database/testing.sqlite'
$env:CACHE_DRIVER='array'
$env:SESSION_DRIVER='array'
$env:QUEUE_CONNECTION='sync'
$env:MAIL_MAILER='array'
& 'C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe' artisan migrate:fresh --force
& 'C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe' artisan test
```

Coverage summary:

- Feature workflow tests act as end-to-end SIT/UAT-style automation for business flows.
- Security/regression tests cover access control, export hardening, scheduled report scope, and attachment/comment boundaries.
- Unit tests cover validation helpers, spreadsheet parsing, and PDF layout helpers.

Run specific suite:

```bash
php artisan test --filter="OjkMitigationMonitoringWorkflowTest"
```

Dependency security audit:

```bash
php composer.phar audit --no-interaction
npm audit --audit-level=low
```

## Deployment Notes

Setelah deploy / update `.env`:

```bash
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache
```

Optional schema optimization:

```bash
php artisan schema:dump
php artisan schema:load --prune
```

## Troubleshooting

### Config/env changes not reflected

```bash
php artisan optimize:clear
php artisan config:cache
```

### Frontend changes not shown

```bash
npm run build
```

### Threat Intel kosong / lambat

- pastikan internet keluar ke endpoint NVD/CISA,
- cek konfigurasi `NVD_API_KEY`,
- clear cache aplikasi:

```bash
php artisan cache:clear
```

### Redis issues

- pastikan Redis service aktif,
- pastikan extension `phpredis` tersedia,
- cek `REDIS_HOST` dan `REDIS_PORT` di `.env`.

---

Internal use only. Jangan commit credential, token, atau data sensitif ke repository.
