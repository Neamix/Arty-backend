#!/usr/bin/env bash
set -euo pipefail

cd /var/www/html


chown -R www:www storage bootstrap/cache || true

composer dump-autoload --optimize --no-dev --classmap-authoritative --no-scripts
composer dump-autoload --optimize --no-dev

php artisan optimize:clear --no-interaction

php artisan config:cache  --no-interaction
php artisan route:cache   --no-interaction
php artisan view:cache    --no-interaction || true
php artisan event:cache   --no-interaction || true
php artisan passport:keys --force --no-interaction || true
php artisan passport:client --personal --no-interaction || true
php artisan migrate --force --no-interaction
php artisan storage:link --no-interaction || true

chmod 600 /var/www/html/storage/oauth-private.key 2>/dev/null || true
chmod 600 /var/www/html/storage/oauth-public.key 2>/dev/null || true

exec "$@"

