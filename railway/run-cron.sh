#!/usr/bin/env sh
set -eu

# Keep the scheduler independent from the web process so scheduled reports,
# alert scans, and threat-intelligence refreshes continue to run.
while true; do
  php artisan schedule:run --verbose --no-interaction
  sleep 60
done
