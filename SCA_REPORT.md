# Software Composition Analysis (SCA) Report
## Risk Management System

Generated: February 23, 2026  
Environment: Windows (PowerShell), Node/npm available, PHP 8.3.30, Composer via `composer.phar`  
Overall Status: Secure (no known vulnerability advisories)

---

## Scope

Scans executed:

```bash
npm.cmd audit --audit-level=low
C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe C:\laragon\bin\composer\composer.phar audit --no-interaction
npm.cmd outdated
C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe C:\laragon\bin\composer\composer.phar outdated --direct
```

---

## Results

| Category | Result | Detail |
|---|---|---|
| Frontend vulnerabilities | 0 | `npm audit` found 0 vulnerabilities |
| Backend vulnerabilities | 0 | `composer audit` found no security advisories |
| npm outdated packages | none | `npm outdated` returned no output |
| Composer direct outdated packages | 2 | See table below |

### Composer Direct Dependencies With Updates Available

| Package | Current | Latest | Type |
|---|---|---|---|
| `phpunit/phpunit` | 11.5.55 | 12.5.14 | Major |
| `spatie/laravel-ignition` | 2.10.0 | 2.11.0 | Minor/Patch |

---

## Risk Assessment

- Security risk: Low  
- Deployment blocker from dependency vulnerabilities: None  
- Recommended patch action: update `spatie/laravel-ignition` on next maintenance window  
- Recommended upgrade planning: evaluate `phpunit/phpunit` v12 in a dedicated compatibility branch

---

## Notes

- `composer.bat` requires `php` in PATH on this machine; audit was run using `php.exe composer.phar` directly.
- This report reflects the latest rerun scan status as of February 23, 2026.
