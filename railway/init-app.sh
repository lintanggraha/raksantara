#!/usr/bin/env sh
set -eu

# Railway executes this as the app service pre-deploy command. The service
# must receive APP_KEY and database variables before this script runs.
php artisan migrate --force
php artisan optimize:clear
php artisan config:cache
php artisan event:cache
php artisan route:cache
php artisan view:cache
