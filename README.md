# Risk Management System (RMS)

![Laravel](https://img.shields.io/badge/Laravel-12.x-FF2D20?logo=laravel&logoColor=white)
![PHP](https://img.shields.io/badge/PHP-8.2%2B-777BB4?logo=php&logoColor=white)
![MySQL](https://img.shields.io/badge/Database-MySQL-4479A1?logo=mysql&logoColor=white)
![Vite](https://img.shields.io/badge/Frontend-Vite-646CFF?logo=vite&logoColor=white)

Platform internal untuk workflow end-to-end manajemen risiko perusahaan: risk register, monitoring dashboard, treatment operations, KRI, incident, BCM, vendor, corporate risk appetite, framework explorer, threat intelligence, audit/compliance, serta reporting manual dan terjadwal.

## Table of Contents

- [Overview](#overview)
- [Role and Access Model](#role-and-access-model)
- [Module Map](#module-map)
- [Navigation](#navigation)
- [Security and Account Rules](#security-and-account-rules)
- [Tech Stack](#tech-stack)
- [System Requirements](#system-requirements)
- [Local Setup](#local-setup)
- [Bootstrap First Superadmin](#bootstrap-first-superadmin)
- [Run the App](#run-the-app)
- [Scheduler and Automation](#scheduler-and-automation)
- [Important Routes](#important-routes)
- [Threat Intel Configuration](#threat-intel-configuration)
- [Testing](#testing)
- [Deployment Notes](#deployment-notes)
- [Troubleshooting](#troubleshooting)

## Overview

RMS dipakai untuk mengelola siklus risiko dari hulu ke hilir dalam satu aplikasi:

- menyusun dan memelihara risk register per risk owner,
- memantau portofolio risiko melalui dashboard monitoring dan trend analysis,
- mengelola treatment action, evidences, owner workload, dan playbook mitigasi,
- mencatat incident yang terkait langsung dengan risk,
- menjalankan RCSA, BIA, dan BCP pada risk yang sama,
- mengelola KRI dan log nilai threshold,
- memetakan vendor pihak ketiga dan corporate risk appetite,
- meninjau framework referensi keamanan dan regulasi,
- memantau threat intelligence berbasis NVD, CISA KEV, dan OWASP,
- menjalankan dashboard audit dan program kerja compliance,
- menghasilkan report manual maupun scheduled report via email.

## Role and Access Model

| Role / Kondisi | Akses Utama |
|---|---|
| Superadmin | Fokus ke governance aplikasi: dashboard admin, user management, parameter management, corporate risk appetite, status approvals, alerts, audit trail, threat intel, dan framework explorer. Superadmin bersifat read-only untuk operasi risk register dan incident. Export report dan scheduled report tidak tersedia untuk superadmin. |
| Verified user (non-compliance) | Akses ke seluruh modul operasional: Risk Monitoring, Risk Register, Trend Analysis, KRI, Mitigation Kanban, Owner Workload, Mitigation Playbooks, Incident Management, BCM, Corporate Risk Appetite, Frameworks, Vendors, Threat Intel, User Guide, serta Scheduled Reports via URL langsung. |
| Compliance division | Semua akses verified user ditambah Audit Dashboard, Compliance Programs, import risk register, dan cakupan pengelolaan risk lintas owner sesuai aturan akses compliance. |
| Pending verification | Hanya halaman pending approval sampai divisi diverifikasi superadmin. |

Catatan akses:

- User non-superadmin bekerja berdasarkan scope `risk owner` yang diizinkan untuk akunnya.
- Compliance memiliki cakupan lebih luas untuk monitoring dan pengelolaan data lintas owner.
- Superadmin dapat melihat seluruh data untuk fungsi pengawasan, tetapi tidak menjadi operator utama untuk risk register, incident, dan scheduled report.

## Module Map

### 1. Risk Monitoring Dashboard (`/risk-monitoring`)

- Dashboard monitoring dibagi ke 3 context tampilan: `exec_snapshot`, `ops_control`, dan `risk_intelligence`.
- Widget dashboard bisa di-show/hide, di-drag untuk reorder, dan preferensinya disimpan per user.
- Auto-refresh berjalan setiap 30 detik melalui endpoint summary dashboard.
- Ringkasan utama mencakup total/open/monitoring/closed risks, average inherent score, average residual score, variance, mitigation effectiveness, dan SLA status.
- Analytical widgets yang aktif mencakup benchmark scoring, KPI dashboard, department performance, root cause analysis, serta portfolio overview.
- Halaman monitoring juga menjadi salah satu sumber export report manual.

### 2. Risk Register Workspace (`/risks`)

- Satu halaman kerja dengan 3 workspace:
  - Risk Register
  - Risk Heat Map
  - Risk Recapitulation
- Terdapat summary cards level risiko dan coverage KRI.
- Tabel register mendukung quick search, owner filter, sorting, dan column preset:
  - `Overview`
  - `Full Table`
  - `Analysis`
  - `Treatment`
  - `Quantitative`
- Kolom utama yang tampil mencakup `Risk ID`, `Year`, `Process`, `Sasaran Target`, `Risk Identification`, owner, cause, control, inherent analysis, treatment summary, residual analysis, group radar, dan quantitative result.
- Aksi per baris meliputi view, edit, delete, dan quick jump ke KRI risk terkait.
- Export manual tersedia untuk register, heatmap, dan recap.
- Import risk register tersedia untuk compliance division dengan dukungan template download, `dry_run`, dan override tahun.
- Template import risk register menggunakan sheet kerja utama `BT`, `DI`, `CR`, `OM`, `CS`, `BS`, `HC`, dan `SUM`; sheet tersembunyi akan diabaikan.

### 3. Risk Form and Lifecycle

Form create/edit risk dibagi menjadi 6 section:

1. Risk
2. Inherent Analysis
3. Risk Treatment
4. Residual Analysis
5. Group Radar
6. Quantitative Result

Detail field yang aktif:

- Identitas dasar: `Risk ID`, `Year`, `Risk Category`, `Process`, `Risk Owner`, `Corporate Risk Appetite`, `Cause Category`, `Third-Party Vendor`, `Sasaran Target`, `Risk Identification`, `Cause`, dan `Existing Control`.
- Risk treatment mendukung dua jalur:
  - memakai `Mitigation Playbook`,
  - mengisi treatment manual.
- Setiap treatment menyimpan `option`, deskripsi aksi, `PIC`, deadline, effectiveness, next action, blocked reason, dan checklist.
- Quantitative result menyimpan inherent/residual impact amount, probability percent, dan potential loss.
- `Potential loss` dihitung otomatis dari impact x probability, tetapi tetap bisa di-override manual.
- Setelah create atau update, user bisa melanjutkan langsung ke BCM lewat aksi seperti `SAVE & CONTINUE TO BCM` atau `UPDATE & OPEN BCM`.
- Pada halaman edit, perubahan status tertentu seperti `Monitoring` atau `Closed` akan masuk ke approval queue superadmin.

### 4. Risk Detail and Governance (`/risks/{id}`)

Halaman detail risk memuat tab aktif berikut:

- Basic
- Inherent
- Treatment
- RCSA
- Residual
- BCM (BIA/BCP)
- Governance
- Key Risk Indicators

Kapabilitas utama:

- Shortcut langsung ke `RCSA Workspace`.
- Tab Basic menampilkan relasi penting seperti vendor dan corporate risk appetite.
- Tab Treatment menyimpan evidence/attachment per treatment.
- Tab Governance memusatkan risk evidence, diskusi, mention user `@username`, attachment komentar, edit/delete komentar milik sendiri, activity feed, histori status approval, dan histori alerts.
- Tab BCM menampilkan BIA terlebih dahulu; BCP dilanjutkan setelah BIA tersedia.

### 5. RCSA Workspace (`/risks/{risk}/rcsa`)

- Workspace assessment formal untuk satu risk.
- Field utama yang dikelola:
  - `Process Owner`
  - `Review Frequency`
  - `Next Review`
  - `Objective`
  - `Scope`
  - `Key Controls`
  - `Control Gaps`
  - `Likelihood Rationale`
  - `Impact Rationale`
  - `Notes`
  - `Sign-Off Name`
  - `Sign-Off Role`
  - `Signed-Off Date`
- Scope workspace mengikuti aturan akses risk owner yang sama dengan risk induknya.

### 6. Key Risk Indicators (`/kris`)

- Dashboard ringkasan `Healthy`, `Warning`, `Critical`, dan `Total`.
- Filter berdasarkan status dan risk.
- CRUD KRI tersedia untuk user yang memang boleh mengelola risk terkait.
- Threshold KRI mendukung tipe:
  - `lower_is_better`
  - `higher_is_better`
- Log nilai KRI akan menghitung status otomatis Green / Amber / Red.
- Import risk register dapat membuat atau memperbarui KRI otomatis bernama `Auto: Residual Risk Score`.

### 7. Treatment Operations

#### Mitigation Kanban (`/mitigation-kanban`)

- Kanban treatment dibagi ke status:
  - `Not Started`
  - `In Progress`
  - `Blocked`
  - `Completed`
- Drag-and-drop antar kolom akan mengubah status treatment secara langsung.
- Aturan operasional yang aktif:
  - item yang dipindah ke `Blocked` wajib punya `blocked_reason`,
  - item yang dipindah ke `Completed` wajib punya minimal satu evidence attachment.
- Kartu treatment menampilkan SLA state, aging, evidence count, PIC, deadline, next action, blocked reason, dan checklist.
- SLA state yang dipakai board adalah `Completed`, `No Deadline`, `Overdue`, `Due Soon`, dan `On Track`.

#### Owner Workload (`/owner-workload`)

- Dashboard khusus untuk memetakan beban kerja owner di visible scope user.
- Summary card yang aktif:
  - owners in scope,
  - open actions,
  - overdue,
  - blocked,
  - pending approvals,
  - red KRIs.
- Dashboard juga menampilkan `Highest Attention Owner` dan `Workload Score`.
- Antrian owner disusun berdasarkan skor perhatian dengan level:
  - `Stable`
  - `Watch`
  - `High Attention`
  - `Critical Focus`

#### Mitigation Playbooks (`/mitigation-playbooks`)

- Modul template aksi mitigasi yang bisa dipakai ulang untuk risk sejenis.
- Setiap playbook menyimpan:
  - nama template,
  - risk owner,
  - treatment option,
  - default action description,
  - default PIC,
  - default effectiveness,
  - target days,
  - next action offset days,
  - checklist,
  - status active/inactive.
- Modul ini juga terhubung dari parameter management superadmin.

### 8. Incident Management (`/incident-management`)

- Incident register terhubung langsung ke risk.
- Summary yang aktif menampilkan total incidents, active handling, closed, mitigation failed, dan affected risks.
- Visualisasi utama mencakup monthly incident trend dan mitigation verdict distribution.
- CRUD incident mengikuti scope risk yang terlihat oleh user.
- Superadmin bisa melihat incident, tetapi tetap read-only pada modul ini.

### 9. Risk Trend Analysis (`/trend-analysis`)

- Filter granularity: `monthly`, `quarterly`, `yearly`.
- Filter periode: 3 bulan, 6 bulan, 1 tahun, 2 tahun.
- Statistik utama:
  - Avg Inherent Risk Score
  - Avg Residual Risk Score
  - Risk Reduction %
  - Total Risks
- Panel aktif yang dapat di-drag untuk reorder:
  - Risk Score Trend (Inherent vs Residual)
  - Risk Score by Category
  - Risk Status Distribution Over Time
  - Risk Concentration by Owner
  - Heatmap High-Risk Trend

### 10. Business Continuity, Vendors, and Appetite

#### Business Continuity (`/bcm`)

- Dashboard BCM menampilkan:
  - `Total BIAs`
  - `Tier 1 (Critical)`
  - `Tested BCPs`
  - `Missing BCPs`
- Ada filter tier dan register `BIA & BCP`.
- Create BIA dilakukan dengan memilih risk/process yang sudah ada, lalu dilanjutkan dari BCM workspace pada risk detail.
- Field BIA yang aktif mencakup `RTO`, `RPO`, `MAO`, impact financial/operational/regulatory/reputational, criticality tier, dan justification notes.
- Field BCP yang aktif mencakup strategy summary, recovery procedures, primary workaround, testing frequency, last tested at, dan last test result.

#### Third-Party Vendors (`/vendors`)

- CRUD vendor pihak ketiga yang dapat dihubungkan ke risk.
- Informasi vendor meliputi nama, deskripsi, contact name, contact email, status, dan criticality level.
- Halaman detail vendor menampilkan risk yang terkait dan masih berada dalam visible scope user.

#### Corporate Risk Appetite (`/risk-appetites`)

- Semua verified user dapat melihat daftar dan detail corporate risk appetite.
- Create, edit, delete, dan aktivasi/deaktivasi dikelola oleh superadmin.
- Appetite dapat berbentuk threshold kualitatif maupun kuantitatif.
- Detail appetite menampilkan linked risks.
- Appetite yang masih terhubung ke risk tidak boleh dihapus; pola yang dipakai adalah deactivation.

### 11. Framework Explorer (`/frameworks/*`)

- Halaman referensi aktif yang tersedia:
  - ISO 27001
  - NIST CSF 2.0
  - SEOJK 29 SEOJK.03 2022
  - Panduan Resiliensi OJK
  - PBI 02/2024
- Modul ini berfungsi sebagai reference explorer untuk framework keamanan dan regulasi.
- Data framework disajikan dari data lokal/bundled sehingga tidak bergantung pada API eksternal saat page dibuka.

### 12. Threat Intelligence (`/threat-intelligence`)

- Sumber data aktif:
  - NVD CVE API
  - CISA Known Exploited Vulnerabilities Catalog
  - OWASP Top 10 mapping
- Filter dashboard:
  - `keyword`
  - `severity`
  - `owasp` (`A01` s.d. `A10`)
  - `days` (`7`, `30`, `90`)
  - `limit` (`10`, `12`, `20`, `50`)
- Tampilan utama mencakup summary cards, severity breakdown, publication trend, vendor exposure, OWASP breakdown, priority queue, dan source health.
- Threat intel dapat di-refresh otomatis melalui middleware saat user membuka aplikasi, dan juga melalui scheduler.

### 13. Audit and Compliance

#### Audit Dashboard (`/audit`)

- Hanya tersedia untuk compliance division.
- Mendukung filter tahun dan bank.
- Import audit menerima file `.xls`/`.xlsx`.
- Export audit mendukung `pdf`, `xls`, `xlsx`, dan `pptx`.
- Tab analisis yang aktif:
  - Executive Summary
  - Rekap Detail Temuan
  - MAPS Temuan
  - Progress Temuan
  - Trend Temuan
  - Detail Outstanding
  - Duedate Maturity
- Import dashboard audit memprioritaskan sheet `SUMMARY UPDATE`.

#### Compliance Programs (`/compliance/*`)

- Hanya tersedia untuk compliance division.
- Section program yang aktif:
  - Internal Audit
  - Risk Management
  - Governance & Compliance
- Data program kerja menampilkan `work_id`, Program / Key Initiative, Description & Rationales, Deliverables, How to Measure, Budget, Updated, dan Support Needed.

### 14. Superadmin Operations

#### User Management (`/users`)

- Menampilkan daftar pending, approved, dan all users.
- Superadmin dapat:
  - memverifikasi divisi user,
  - mengubah status superadmin,
  - reset password user.
- Reset password menghasilkan temporary password dan memaksa user mengganti password saat login berikutnya.
- Sistem menjaga agar last superadmin tidak bisa dicabut begitu saja.

#### Parameter Management (`/parameters`)

- Menjadi workspace master data superadmin.
- Blok parameter aktif:
  1. Risk Category
  2. Risk Owner
  3. Corporate Risk Appetite
  4. Cause Category
  5. Third-Party Vendor
  6. Probability Description
  7. Impact Category
  8. Impact Description
  9. Mitigation Playbook
  10. Group Radar Description

#### Risk Status Approvals (`/risk-status-approvals`)

- Menangani approve/reject permintaan perubahan status risk.
- Approval yang diterima akan memperbarui status risk terkait.

#### Alerts (`/risk-alerts`)

- Menampilkan daftar alert yang masih open maupun resolved.
- Superadmin dapat memfilter alert per status dan tipe, lalu menandai resolve.

#### Audit Trail (`/audit-logs`)

- Menyediakan jejak aktivitas sistem lengkap dengan event, user, waktu, dan ringkasan detail perubahan.

### 15. Reporting

#### Manual Export (`/reports/{scope}/{format}`)

- Scope export yang aktif:
  - `monitoring`
  - `board`
  - `risks`
  - `heatmap`
  - `recap`
  - `overall`
- Format export yang aktif:
  - `xls`
  - `xlsx`
  - `pdf`
  - `pptx`
- Filter export:
  - `year`
  - `risk_category_id`
  - `risk_cause_category_id`
  - `risk_owner`
- Export dibatasi oleh scope akses user dan tidak tersedia untuk superadmin.

#### Scheduled Reports (`/reports/scheduled`)

- Tersedia untuk verified user non-superadmin.
- Halaman ini aktif tetapi tidak muncul di navbar utama, sehingga diakses langsung melalui URL.
- Schedule mendukung:
  - scope `monitoring`, `board`, `risks`, `heatmap`, `recap`, `overall`
  - format `pdf`, `xlsx`, `pptx`
  - frequency `daily`, `weekly`, `monthly`
  - `send_time`, `timezone`, `day_of_week`, `day_of_month`
  - multi-recipient email
  - filter tahun, kategori risiko, cause category, dan risk owner
  - `run now`
  - pause/activate
  - histori run terbaru

## Navigation

### Navbar Verified User

- `Risk` dropdown:
  - Risk Monitoring
  - Risk Register
  - Risk Trend Analysis
  - Key Risk Indicators
  - Mitigation Kanban
  - Owner Workload
  - Mitigation Playbooks
  - Incident Management
  - Business Continuity (BCM)
  - Corporate Risk Appetite
- `Frameworks`
- `Third-Party Vendors`
- `Threat Intel`
- `User Guide`
- `Audit` (compliance only)
- `Compliance` dropdown (compliance only)

### Navbar Superadmin

- `Dashboard`
- `Frameworks`
- `App Management`
  - User Management
  - Parameter
  - Corporate Risk Appetite
- `Status Approvals`
- `Alerts`
- `Audit Trail`
- `Threat Intel`

Catatan:

- `Scheduled Reports` aktif, tetapi tidak ditampilkan di navbar utama.
- User yang belum diverifikasi hanya melihat halaman `Pending Approval`.

## Security and Account Rules

- Public registration default **off** melalui `ALLOW_PUBLIC_REGISTRATION=false`.
- User non-superadmin wajib menunggu verifikasi divisi dari superadmin.
- Rule password aktif pada registrasi, reset password, dan ganti password:
  - minimal 8 karakter,
  - wajib mengandung huruf,
  - wajib mengandung angka.
- Superadmin reset password user dengan `force_password_change`.
- Self-service `Change Password` tersedia di menu profile user.

## Tech Stack

- Backend: Laravel 12
- Language: PHP 8.2+
- Database: MySQL / MariaDB
- Frontend: Vite, Vue 3, Bootstrap 5, Tabler UI, jQuery DataTables, Chart.js
- Reporting: Dompdf, Laravel Excel, PHPPresentation
- HTTP / integration: Guzzle

## System Requirements

- PHP `>=8.2`
- Composer v2
- MySQL / MariaDB
- Node.js + npm
- Mail configuration untuk scheduled report
- (Opsional) Redis untuk cache/session production

## Local Setup

Install dependency backend dan frontend:

```bash
composer install
npm install
```

Copy `.env`:

- Linux/macOS:

```bash
cp .env.example .env
```

- Windows PowerShell:

```powershell
Copy-Item .env.example .env
```

Set variabel minimum di `.env`:

- koneksi database,
- `APP_URL`,
- konfigurasi mailer jika scheduled reports akan dipakai,
- `NVD_API_KEY` bila ingin refresh threat intel lebih stabil.

Generate app key dan migrasi:

```bash
php artisan key:generate
php artisan migrate
```

Build assets production:

```bash
npm run build
```

Catatan Windows / Laragon:

- Jika `php` tidak ada di PATH, jalankan perintah artisan memakai executable PHP milik Laragon.

## Bootstrap First Superadmin

Jika belum ada akun superadmin, gunakan command berikut:

```bash
php artisan user:promote-superadmin <username> --create --name="Nama Admin" --email="admin@company.local"
```

Opsi penting:

- `--division=` untuk menentukan division default.
- `--password=` untuk menentukan password manual.
- Jika `--password` tidak diisi saat `--create`, sistem akan generate temporary password dan memaksa user mengganti password saat login pertama.

Jika user sudah ada dan hanya perlu dipromosikan:

```bash
php artisan user:promote-superadmin <username>
```

## Run the App

Jalankan backend:

```bash
php artisan serve
```

Untuk development frontend dengan hot reload:

```bash
npm run dev
```

## Scheduler and Automation

Task scheduler aktif:

- `risk:scan-alerts --days=3` setiap hari pukul `07:00`
- `reports:dispatch-scheduled` setiap menit
- `threat-intel:refresh --force` setiap 15 menit

Contoh cron Linux:

```bash
* * * * * cd /path/to/project && php artisan schedule:run >> /dev/null 2>&1
```

Perilaku otomatis lain yang aktif:

- middleware threat intel akan melakukan refresh working set saat user login membuka aplikasi dan interval refresh sudah terlewati,
- dashboard monitoring melakukan refresh summary berkala tanpa reload halaman penuh.

## Important Routes

- Health: `/health`
- Readiness check: `/health/ready`
- Risk Monitoring: `/risk-monitoring`
- Risk Register: `/risks`
- RCSA Workspace: `/risks/{risk}/rcsa`
- Key Risk Indicators: `/kris`
- Mitigation Kanban: `/mitigation-kanban`
- Owner Workload: `/owner-workload`
- Mitigation Playbooks: `/mitigation-playbooks`
- Incident Management: `/incident-management`
- Trend Analysis: `/trend-analysis`
- Business Continuity: `/bcm`
- Vendors: `/vendors`
- Corporate Risk Appetite: `/risk-appetites`
- Framework Explorer: `/frameworks/*`
- Threat Intelligence: `/threat-intelligence`
- Audit Dashboard: `/audit` (compliance only)
- Compliance Programs: `/compliance/*` (compliance only)
- Scheduled Reports: `/reports/scheduled`
- User Guide: `/user-guide`

`/health/ready` memeriksa koneksi database, cache, dan write access storage.

## Threat Intel Configuration

Environment variable yang relevan:

- `NVD_API_KEY` (opsional, disarankan)
- `NVD_API_BASE_URL`
- `CISA_KEV_FEED_URL`
- `CISA_KEV_MIRROR_URL`
- `OWASP_TOP10_URL`
- `THREAT_INTEL_AUTO_REFRESH_ENABLED` (default `true`)
- `THREAT_INTEL_AUTO_REFRESH_INTERVAL_MINUTES` (default `30`)
- `THREAT_INTEL_AUTO_REFRESH_FORCE` (default `true`)

Command manual refresh:

```bash
php artisan threat-intel:refresh --force
```

## Testing

Penting:

- jangan jalankan test ke database utama dari `.env`,
- gunakan database testing terpisah,
- simpan konfigurasi itu di `.env.testing`.

Contoh awal konfigurasi testing tersedia di [`.env.testing.example`](./.env.testing.example).

Run all tests:

```bash
php artisan test
```

Jika memakai MySQL terpisah untuk test, buat database khusus terlebih dahulu:

```sql
CREATE DATABASE risk_app_testing CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

Jika belum punya MySQL testing aktif, gunakan fallback SQLite file-based test database.

Windows PowerShell (contoh Laragon):

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
php artisan migrate:fresh --force
php artisan test
```

Contoh suite yang relevan dengan fitur aktif:

- `TrendAnalysisWorkflowTest`
- `ScheduledReportWorkflowTest`
- `BcmVendorFlowRegressionTest`
- `CorporateRiskAppetiteWorkflowTest`
- `ThreatIntelligencePageTest`
- `RiskOpsWorkspaceRegressionTest`

Dependency audit:

```bash
composer audit --no-interaction
npm audit --audit-level=low
```

## Deployment Notes

Setelah deploy atau update `.env`:

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

Pastikan scheduler dan konfigurasi mail aktif bila scheduled report dipakai di server.

## Troubleshooting

### Config / env changes not reflected

```bash
php artisan optimize:clear
php artisan config:cache
```

### Frontend changes not shown

```bash
npm run build
```

### Threat Intel kosong atau lambat

- pastikan server bisa keluar ke endpoint NVD, CISA, dan OWASP,
- cek `NVD_API_KEY`,
- jalankan manual refresh:

```bash
php artisan threat-intel:refresh --force
```

### Scheduled report tidak terkirim

- pastikan `MAIL_MAILER`, host, port, username, dan password sudah benar,
- pastikan `reports:dispatch-scheduled` berjalan lewat scheduler,
- cek timezone pada jadwal report yang dibuat user.

### Issue database testing

- pastikan `.env.testing` mengarah ke database test, bukan database utama,
- jika memakai SQLite fallback, pastikan file `database/testing.sqlite` sudah dibuat sebelum test dijalankan.

---

Internal use only. Jangan commit credential, token, atau data sensitif ke repository.
