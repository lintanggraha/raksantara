# Local Testing Guide

## Requirements

Install PHP 8.2 or newer with SQLite, OpenSSL, XML, GD, ZIP, and PDO extensions. Install Composer and Node.js 22 or newer. The quickest local path uses SQLite, so no MySQL server or cloud account is required.

## First-time setup

Run the following commands from the repository root. The commands create a local-only database file and do not contact a cloud database.

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
mkdir -p database
: > database/database.sqlite
```

Set these values in `.env`:

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

Replace `/absolute/path/to/riskmanagement` with the full path on the local computer. On Windows, use a forward-slash path such as `C:/Users/you/riskmanagement/database/database.sqlite`.

## Create local data and start the application

```bash
php artisan migrate --seed
npm run build
php artisan serve
```

Open `http://127.0.0.1:8000`. The application uses the compiled Vite assets in `public/build`. For frontend changes during development, run this in a second terminal instead of `npm run build`:

```bash
npm run dev
```

## Test before changing code

Run the complete feature suite against a separate SQLite file:

```bash
: > database/testing.sqlite
APP_KEY="base64:UuDJHgHfdTsQI6LT+jSfQQUA4LoZqOyQv6h1h0elYio=" \
DB_CONNECTION=sqlite \
DB_DATABASE="$(pwd)/database/testing.sqlite" \
CACHE_STORE=array \
SESSION_DRIVER=array \
QUEUE_CONNECTION=sync \
MAIL_MAILER=array \
php artisan test
```

The project currently has 133 tests and 714 assertions in the validated suite. Do not use the test environment against a real MySQL database.

## Local background workflow testing

The production scheduler and queue are intentionally not required for basic local testing. To exercise them locally, use separate terminals:

```bash
php artisan schedule:work
php artisan queue:work
```

For a simple local setup, keep `QUEUE_CONNECTION=sync`. Scheduled reports, alert scans, and threat-intelligence refreshes can then be triggered manually through their respective application workflows or Artisan commands.

## Health checks

Use these URLs after starting the server:

| URL | Expected result |
|---|---|
| `http://127.0.0.1:8000/health` | JSON with `status: ok`. |
| `http://127.0.0.1:8000/health/ready` | JSON with `status: ready` when SQLite, cache, and local storage are available. |

## Common local issues

| Symptom | Fix |
|---|---|
| `No application encryption key has been specified` | Run `php artisan key:generate`. |
| `could not find driver` | Enable/install PHP SQLite extension, then restart PHP. |
| Missing CSS or JavaScript | Run `npm install` and then `npm run build` or `npm run dev`. |
| Database table not found | Run `php artisan migrate --seed`. |
| Permission error under `storage` or `bootstrap/cache` | Ensure the local user can write those directories. |
