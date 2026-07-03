#!/bin/sh
set -e

cd /var/www/html

# Ensure ALL required directories exist before anything else
mkdir -p storage/app/public \
         storage/logs \
         storage/framework/sessions \
         storage/framework/views \
         storage/framework/cache \
         bootstrap/cache

chown -R www-data:www-data storage bootstrap/cache
chmod -R 755 storage bootstrap/cache

# Create storage symlink
php artisan storage:link --force

# Run database migrations
php artisan migrate --force

# Start PHP-FPM in background
php-fpm -D

# Start nginx in foreground
exec nginx -g "daemon off;"
