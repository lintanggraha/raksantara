# Risk Management System (RMS)

![Laravel](https://img.shields.io/badge/Laravel-12.x-FF2D20?logo=laravel&logoColor=white)
![PHP](https://img.shields.io/badge/PHP-8.2%2B-777BB4?logo=php&logoColor=white)
![MySQL](https://img.shields.io/badge/Database-MySQL-4479A1?logo=mysql&logoColor=white)
![Vite](https://img.shields.io/badge/Frontend-Vite-646CFF?logo=vite&logoColor=white)

Aplikasi web untuk mengelola risk register, monitoring risiko, trend analysis, audit dashboard, compliance program, dan mitigasi OJK dalam satu platform.

## Table of Contents
- [Overview](#overview)
- [Main Modules](#main-modules)
- [Current Navigation](#current-navigation)
- [Tech Stack](#tech-stack)
- [System Requirements](#system-requirements)
- [Local Setup](#local-setup)
- [Bootstrap First Admin](#bootstrap-first-admin)
- [Run the App](#run-the-app)
- [Scheduler and Automation](#scheduler-and-automation)
- [Key Routes](#key-routes)
- [Import Features](#import-features)
- [Testing](#testing)
- [Deployment Notes](#deployment-notes)
- [Troubleshooting](#troubleshooting)

## Overview
RMS membantu tim operasional, risk, dan compliance menjalankan alur kerja harian:
- mencatat dan memelihara data risiko,
- memonitor status risiko dan treatment,
- mengekspor laporan manajerial,
- mengelola program compliance,
- memantau mitigasi OJK berbasis timeline.

## Main Modules
### 1) Risk
- Risk Monitoring dashboard
- Risk Register (CRUD)
- Risk Trend Analysis
- Risk Heatmap and Recapitulation
- Evidence attachment and comments

### 2) Audit
- Audit Monitoring dashboard multi-tab
- Import file audit XLS/XLSX
- Export audit report (PDF/XLSX/PPTX)

### 3) Compliance and OJK
- Compliance Programs:
  - Internal Audit
  - Risk Management
  - Governance and Compliance
- OJK Mitigation Monitoring:
  - import timeline Excel,
  - edit status per point,
  - timeline chart per target date.

### 4) Reporting
- Export report per scope (monitoring, board, risks, heatmap, recap, overall)
- Format: PDF, XLSX, PPTX
- Scheduled Reports route tersedia, tetapi saat ini menu navbar-nya di-hide sementara.

## Current Navigation
### Verified user
- `Risk` (Monitoring, Register, Trend)
- `Audit`
- `User Guide`

### Compliance division
- Semua menu verified user
- `Mitigasi OJK`
- `Compliance` dropdown (Internal Audit, Risk Management, Governance and Compliance)

### Superadmin
- `Dashboard`
- `User Management`
- `Status Approvals`
- `Alerts`
- `Audit Trail`

## Tech Stack
- Backend: Laravel 12
- Language: PHP 8.2+
- Database: MySQL / MariaDB
- Frontend: Bootstrap 5, Vue 3, Vite
- Reporting: Dompdf, Laravel Excel, PHPPresentation

## System Requirements
- PHP `>=8.2`
- Composer v2
- MySQL / MariaDB
- Node.js + npm
- (Optional) Redis for cache/session in production

## Local Setup
```bash
composer install
npm install
```

Copy environment file:
- Linux/macOS:
```bash
cp .env.example .env
```
- Windows PowerShell:
```powershell
Copy-Item .env.example .env
```

Generate app key and migrate database:
```bash
php artisan key:generate
php artisan migrate
```

Build frontend assets:
```bash
npm run build
```

## Bootstrap First Admin
Jika belum ada superadmin:
```bash
php artisan user:promote-superadmin <username> --create --name="Nama Admin" --email="admin@company.local"
```

## Run the App
```bash
php artisan serve
```

Untuk development frontend hot reload:
```bash
npm run dev
```

## Scheduler and Automation
Task scheduler yang digunakan:
- `risk:scan-alerts --days=3`
- `reports:dispatch-scheduled`

Contoh cron Linux:
```bash
* * * * * cd /path/to/project && php artisan schedule:run >> /dev/null 2>&1
```

## Key Routes
- Health check: `/health`, `/health/ready`
- Risk Monitoring: `/risk-monitoring`
- Risk Register: `/risks`
- Trend Analysis: `/trend-analysis`
- Audit Dashboard: `/audit`
- OJK Mitigation: `/ojk-mitigation` (compliance only)
- Compliance Programs: `/compliance/*` (compliance only)
- User Guide: `/user-guide`

## Import Features
### Risk Register import
- Endpoint: `POST /risks/import`
- Access: compliance division

### Audit import
- Endpoint: `POST /audit/import`
- Access: compliance division

### OJK Mitigation import
- Endpoint: `POST /ojk-mitigation/import`
- Access: compliance division
- Format: XLS/XLSX

## Testing
Run all tests:
```bash
php artisan test
```

Run specific suite:
```bash
php artisan test --filter="OjkMitigationMonitoringWorkflowTest"
```

## Deployment Notes
After deploy/update `.env`:
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

### Redis issues
- Pastikan Redis service aktif
- Pastikan extension `phpredis` tersedia
- Cek `REDIS_HOST` dan `REDIS_PORT` di `.env`

---
Internal use only. Jangan commit credential, token, atau data sensitif ke repository.
