# private/

Everything in this folder except this README is gitignored. Use it for
organisation-specific data that must not be published. The app works without
any of these files and falls back to generic or fictional defaults.

| File | Effect |
|---|---|
| `incidents.php` | Overrides `config/incidents.php`, for example `['affected_parties' => [...]]` to suggest your real client names on incident forms. |
| `audit_dashboard.php` | Overrides top-level keys of `config/audit_dashboard.php`, typically `bank` with your real client list, regions and map coordinates. |
| `audit_dashboard_snapshot.php` | The audit dashboard snapshot shown before a spreadsheet is imported. Same structure as `resources/data/audit-dashboard-sample.php`. |

Real risk register seed data belongs in `database/seeders/data/` (also
gitignored) and is loaded by `RealWorldRiskRegisterSeeder` when present.
