#!/usr/bin/env bash
set -euo pipefail

cd /var/www/html

composer dump-autoload --optimize --no-dev

php artisan optimize:clear --no-interaction || true

php artisan config:cache  --no-interaction
php artisan route:cache   --no-interaction
php artisan view:cache    --no-interaction || true
php artisan event:cache   --no-interaction || true
php artisan migrate --force --no-interaction
php artisan storage:link --no-interaction || true

php artisan tinker --execute="
    if (!\Laravel\Passport\Client::where('grant_types', 'like', '%personal_access%')->where('revoked', false)->exists()) {
        Artisan::call('passport:client', ['--personal' => true, '--name' => 'Personal Access Client', '--provider' => 'users', '--no-interaction' => true]);
    }
"

chmod 600 /var/www/html/storage/oauth-private.key 2>/dev/null || true
chmod 600 /var/www/html/storage/oauth-public.key 2>/dev/null || true

# Everything above runs as root and may create files (laravel.log, caches,
# oauth keys). Hand ownership to the runtime user last so php-fpm can write.
chown -R www:www storage bootstrap/cache || true

exec "$@"