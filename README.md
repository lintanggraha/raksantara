# Risk Management System

SSAS Project - Risk Management application.

## Security Defaults

- `ALLOW_PUBLIC_REGISTRATION=false`
- `ALLOW_BOOTSTRAP_SUPERADMIN_REGISTRATION=false`
- `SUPERADMIN_EMAILS=` (empty by default)

## Offline Deployment Notes

UI assets untuk layout utama/auth sudah menggunakan file lokal (`public/`) agar tetap berjalan di server tanpa koneksi internet.

## Bootstrap Superadmin (Recommended)

Promosikan user existing menjadi superadmin via CLI:

```bash
php artisan user:promote-superadmin <username>
```

Opsional set division default:

```bash
php artisan user:promote-superadmin <username> --division="CO - COMPLIANCE"
```

Jika user belum ada, bisa sekalian create + promote:

```bash
php artisan user:promote-superadmin <username> --create --name="Admin RMS" --email="admin@local"
```
