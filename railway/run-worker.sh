#!/usr/bin/env sh
set -eu

# Configure QUEUE_CONNECTION=database or redis in the Railway worker service.
exec php artisan queue:work --sleep=3 --tries=3 --max-time=3600 --no-interaction
