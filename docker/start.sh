#!/bin/sh
set -e

cd /app

mkdir -p storage/logs storage/framework/sessions storage/framework/views storage/framework/cache
chown -R www-data:www-data storage bootstrap/cache
chmod -R 775 storage bootstrap/cache

php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan migrate --force

exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf
