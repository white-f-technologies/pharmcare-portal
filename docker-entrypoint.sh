#!/bin/bash
set -e

# Ensure all required directories exist and are writable
mkdir -p /var/www/html/storage/app/public
mkdir -p /var/www/html/storage/framework/cache/data
mkdir -p /var/www/html/storage/framework/sessions
mkdir -p /var/www/html/storage/framework/views
mkdir -p /var/www/html/storage/logs
mkdir -p /var/www/html/storage/keys
mkdir -p /var/www/html/bootstrap/cache
mkdir -p /var/www/html/database
touch /var/www/html/database/database.sqlite

# Fix permissions
chown -R www-data:www-data /var/www/html/storage
chown -R www-data:www-data /var/www/html/bootstrap/cache
chown -R www-data:www-data /var/www/html/database
chmod -R 775 /var/www/html/storage
chmod -R 775 /var/www/html/bootstrap/cache

# Generate app key if not set
if [ -z "$APP_KEY" ]; then
    php artisan key:generate --force
fi

# Run composer scripts that were skipped during build
php artisan package:discover --ansi || true

# Cache config & routes for production
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Run migrations
php artisan migrate --force

# Create storage link
php artisan storage:link 2>/dev/null || true

exec "$@"
