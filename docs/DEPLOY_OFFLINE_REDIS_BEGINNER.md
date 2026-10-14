# Tutorial Pemula: Deploy Risk Management System + Redis di Server Offline

Dokumen ini untuk skenario `air-gapped`: server tujuan tidak punya internet.  
Cara paling aman adalah pakai 2 mesin:
1. Mesin `ONLINE` untuk menyiapkan paket.
2. Server `OFFLINE` untuk instalasi dan menjalankan aplikasi.

Contoh di bawah memakai Ubuntu + Nginx + PHP-FPM + MariaDB + Redis.

## 1. Prasyarat
1. Pastikan versi PHP server minimal cocok dengan aplikasi ini (`>= 8.3` direkomendasikan).
2. Siapkan akses transfer file dari mesin online ke server offline (USB/LAN tertutup).
3. Siapkan domain/IP internal untuk akses aplikasi.
4. Pastikan database dan Redis akan berjalan di server yang sama atau jaringan lokal yang bisa diakses.

## 2. Persiapan di Mesin ONLINE (builder)
Jalankan dari root project.

```bash
composer install --no-dev --optimize-autoloader --classmap-authoritative
npm ci
npm run build
php artisan optimize:clear
```

Buat paket rilis aplikasi (sudah termasuk `vendor` dan asset build):

```bash
tar -czf rms-release.tgz \
  --exclude=.git \
  --exclude=node_modules \
  --exclude=tests \
  --exclude=.env \
  --exclude=storage/logs/* \
  --exclude=storage/framework/cache/* \
  --exclude=storage/framework/sessions/* \
  --exclude=storage/framework/views/* \
  .
```

## 3. Siapkan dump database di Mesin ONLINE
Jika kamu ingin bawa data existing:

```bash
mysqldump -u root -p --single-transaction --routines --triggers risk_app > risk_app.sql
```

Jika deploy fresh tanpa data lama, langkah dump ini boleh dilewati.

## 4. Siapkan paket OS offline (Mesin ONLINE)
Tujuan langkah ini: semua paket `.deb` sudah siap sebelum dibawa ke server offline.

```bash
mkdir -p offline-pkgs
cd offline-pkgs

sudo apt-get update
sudo apt-get install -y apt-rdepends

PKGS="redis-server redis-tools nginx mariadb-server \
php8.3-cli php8.3-fpm php8.3-mysql php8.3-mbstring php8.3-xml \
php8.3-curl php8.3-zip php8.3-bcmath php8.3-gd php8.3-intl php8.3-redis unzip"

apt-rdepends $PKGS 2>/dev/null \
  | grep -E '^[a-zA-Z0-9][a-zA-Z0-9.+-]*$' \
  | sort -u > pkg-list.txt

xargs -a pkg-list.txt -n1 apt download
```

File yang harus kamu bawa ke server offline:
1. `rms-release.tgz`
2. `risk_app.sql` (jika ada)
3. folder `offline-pkgs` berisi semua `.deb`

## 5. Instal paket di Server OFFLINE
Copy semua file ke server offline, lalu:

```bash
cd /path/ke/offline-pkgs
sudo dpkg -i ./*.deb || true
sudo dpkg -i ./*.deb
```

Aktifkan service:

```bash
sudo systemctl enable --now redis-server
sudo systemctl enable --now mariadb
sudo systemctl enable --now php8.3-fpm
sudo systemctl enable --now nginx
```

## 6. Deploy aplikasi di Server OFFLINE
Ekstrak paket aplikasi:

```bash
sudo mkdir -p /var/www/riskmanagementsystem
sudo tar -xzf /path/ke/rms-release.tgz -C /var/www/riskmanagementsystem
cd /var/www/riskmanagementsystem
```

Set permission:

```bash
sudo chown -R www-data:www-data /var/www/riskmanagementsystem
sudo chmod -R ug+rw storage bootstrap/cache
```

## 7. Konfigurasi `.env` untuk Redis
Buat `.env` dari contoh, lalu isi nilai sesuai server:

```bash
cp .env.example .env
```

Nilai minimum yang perlu dicek:

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=http://IP-ATAU-DOMAIN-INTERNAL

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=risk_app
DB_USERNAME=your_db_user
DB_PASSWORD=your_db_password

CACHE_DRIVER=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=sync

REDIS_CLIENT=phpredis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379
```

Catatan:
1. Untuk pemula, `QUEUE_CONNECTION=sync` dulu agar tidak perlu worker tambahan.
2. Kalau nanti mau antrean async, ubah ke `QUEUE_CONNECTION=redis` lalu jalankan worker.

## 8. Inisialisasi app di Server OFFLINE
Generate key, setup DB, dan cache Laravel:

```bash
php artisan key:generate
```

Jika pakai dump:

```bash
mysql -u root -p -e "CREATE DATABASE IF NOT EXISTS risk_app CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
mysql -u root -p risk_app < /path/ke/risk_app.sql
php artisan migrate --force
```

Jika fresh install tanpa dump:

```bash
php artisan migrate --force
```

Terakhir, aktifkan cache bootstrap:

```bash
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache
```

Jika aplikasi pakai file upload publik:

```bash
php artisan storage:link
```

## 9. Verifikasi cepat
1. Cek Redis:

```bash
redis-cli ping
```

Hasil harus `PONG`.

2. Cek Laravel bisa menulis cache ke Redis:

```bash
php artisan tinker --execute="cache()->put('offline_test','ok',60); dump(cache()->get('offline_test'));"
```

3. Cek health endpoint aplikasi:
1. `GET /health`
2. `GET /health/ready`

## 10. Troubleshooting yang paling sering
1. Error `Class "Redis" not found`
Penyebab: ekstensi `phpredis` belum terpasang/aktif.
Solusi: pasang paket `php8.3-redis` dari bundle offline, restart `php-fpm`.

2. Error `Connection refused` ke Redis
Penyebab: service Redis belum jalan atau host/port di `.env` salah.
Solusi: `systemctl status redis-server`, cek `REDIS_HOST` dan `REDIS_PORT`.

3. Setelah ubah `.env`, aplikasi tetap baca config lama
Solusi:

```bash
php artisan optimize:clear
php artisan config:cache
```

## 11. Fallback jika ekstensi `phpredis` sulit dipasang
Di mesin online, kamu bisa pakai client PHP murni:

```bash
composer require predis/predis:^2.2 --no-dev
```

Lalu di `.env` server offline:

```env
REDIS_CLIENT=predis
```

Dengan cara ini Redis tetap dipakai, tapi tanpa extension `phpredis`.
