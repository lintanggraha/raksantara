# Deploying Risk Management on Railway

## Deployment decision

This application is a Laravel monolith with authenticated web workflows, MySQL data, queued jobs, scheduled reports, threat-intelligence refreshes, and evidence attachments. It must run on a native PHP application service rather than a community PHP serverless runtime. Railway's Laravel support uses Railpack to run PHP-FPM and Caddy, while the repository supplies separate worker and scheduler commands.

## Repository assets

| File | Purpose |
|---|---|
| `railway.json` | Railway build, pre-deploy, health check, and restart policy. |
| `railway/init-app.sh` | Runs migrations and creates Laravel production caches before the web service is released. |
| `railway/run-worker.sh` | Runs Laravel queue workers in an independent worker service. |
| `railway/run-cron.sh` | Runs the Laravel scheduler every minute in an independent cron service. |
| `/health` | Liveness endpoint that does not require a database. |
| `/health/ready` | Readiness endpoint that verifies database, cache, and writable storage. |

## Create the Railway project

Create a Railway project and deploy this GitHub repository as the **App** service. Railpack detects Laravel and provides PHP-FPM with Caddy. Use the repository `railway.json`; do not set a Vercel-style output directory or a PHP start command.

Create a **MySQL** service in the same Railway project. Then create a **Redis** service if queued jobs, cache, or sessions will use Redis. The application can use a database queue initially, but Redis is the recommended production session/cache/queue backend.

Create two additional services from the same GitHub repository: **Worker** and **Cron**. Do not generate public domains for these two services. Their Custom Start Commands are shown below.

| Railway service | Public domain | Deployment command |
|---|---:|---|
| App | Yes | Railpack default start command; `railway.json` runs pre-deploy bootstrap. |
| Worker | No | `chmod +x ./railway/run-worker.sh && sh ./railway/run-worker.sh` |
| Cron | No | `chmod +x ./railway/run-cron.sh && sh ./railway/run-cron.sh` |
| MySQL | No | Railway-managed database service. |
| Redis | No | Railway-managed cache and queue service, recommended for production. |

## Required App environment variables

Set the following values in Railway's **App** service. Copy the shared application variables to Worker and Cron. Never commit real values to the repository.

| Variable | Required value or source |
|---|---|
| `APP_ENV` | `production` |
| `APP_DEBUG` | `false` |
| `APP_KEY` | A stable key generated once with `php artisan key:generate --show`. Reuse the same value in App, Worker, and Cron. |
| `APP_URL` | The Railway public domain, beginning with `https://`. |
| `LOG_CHANNEL` | `stderr` |
| `LOG_STDERR_FORMATTER` | `Monolog\\Formatter\\JsonFormatter` |
| `DB_CONNECTION` | `mysql` |
| `DB_HOST` | Reference the Railway MySQL host variable, for example `${{MySQL.MYSQLHOST}}`. |
| `DB_PORT` | Reference `${{MySQL.MYSQLPORT}}`. |
| `DB_DATABASE` | Reference `${{MySQL.MYSQLDATABASE}}`. |
| `DB_USERNAME` | Reference `${{MySQL.MYSQLUSER}}`. |
| `DB_PASSWORD` | Reference `${{MySQL.MYSQLPASSWORD}}`. |
| `SESSION_DRIVER` | `redis` in production, or `database` as an initial fallback. |
| `CACHE_STORE` | `redis` in production. |
| `QUEUE_CONNECTION` | `redis` in production, or `database` if Redis is not yet enabled. |
| `FILESYSTEM_DISK` | `s3` for production evidence attachments. Do not use local storage for durable evidence. |

When Redis is enabled, reference its variables using `REDIS_HOST=${{Redis.REDISHOST}}`, `REDIS_PORT=${{Redis.REDISPORT}}`, and `REDIS_PASSWORD=${{Redis.REDISPASSWORD}}`. Configure the S3-compatible storage variables required by the selected object-storage provider before enabling `FILESYSTEM_DISK=s3`.

## Pre-deploy and health checks

The App service uses the pre-deploy command in `railway.json` to migrate the database and cache Laravel configuration, events, routes, and views. Railway then checks `/health/ready`. The readiness endpoint returns HTTP 200 only after database connectivity, cache read/write, and storage writeability checks pass. It returns HTTP 503 while the service is not ready.

## Go-live sequence

Deploy MySQL first, then configure the App service variables. Deploy the App service and confirm that `/health/ready` returns `status: ready`. Configure and deploy the Worker and Cron services using the same application and database variables. Finally, configure Redis and S3, move session/cache/queue to Redis, generate the public domain, and set `APP_URL` to that domain before the final redeploy.

## Operational controls

The Worker and Cron services must remain separate from the public App service. Their logs should be reviewed alongside the App log after every deploy. Backups are handled through the operational-resilience register in the application, while database backups and restore tests must be configured at the Railway/MySQL or chosen database-provider layer.
