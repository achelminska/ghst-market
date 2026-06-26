#!/bin/sh
set -e

cd /var/www/html

# Ensure storage directories exist (in case Render Disk is mounted)
mkdir -p storage/app/public storage/logs storage/framework/{sessions,views,cache}
chown -R www-data:www-data storage bootstrap/cache
chmod -R 755 storage

# Create storage symlink
php artisan storage:link --force

# Run database migrations
php artisan migrate --force

# Cache configuration for production
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

# Start PHP-FPM in background
php-fpm -D

# Start nginx in foreground
exec nginx -g "daemon off;"
