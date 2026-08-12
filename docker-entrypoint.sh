#!/bin/bash
set -e

# Export VIEW_COMPILED_PATH explicitly
export VIEW_COMPILED_PATH=/var/www/html/storage/framework/views

# Ensure all required directories exist
mkdir -p /var/www/html/storage/app/public
mkdir -p /var/www/html/storage/framework/cache/data
mkdir -p /var/www/html/storage/framework/sessions
mkdir -p /var/www/html/storage/framework/views
mkdir -p /var/www/html/storage/logs
mkdir -p /var/www/html/storage/keys
mkdir -p /var/www/html/bootstrap/cache
mkdir -p /var/www/html/database
touch /var/www/html/database/database.sqlite

# Full permissions so both CLI and Apache can write
chmod -R 777 /var/www/html/storage
chmod -R 777 /var/www/html/bootstrap/cache
chmod -R 777 /var/www/html/database
chown -R www-data:www-data /var/www/html/storage
chown -R www-data:www-data /var/www/html/bootstrap/cache
chown -R www-data:www-data /var/www/html/database

# Generate app key if not set
if [ -z "$APP_KEY" ]; then
    php artisan key:generate --force
fi

# Run package discovery
php artisan package:discover --ansi || true

# Run migrations
php artisan migrate --force

# Create storage link
php artisan storage:link 2>/dev/null || true

# Sync Admin account if env variables are specified
if [ -n "$ADMIN_EMAIL" ] && [ -n "$ADMIN_PASSWORD" ]; then
    php artisan pharmcare:admin "$ADMIN_EMAIL" "$ADMIN_PASSWORD" || true
    mkdir -p /var/www/html/storage/app_data/PharmCare
    touch /var/www/html/storage/app_data/PharmCare/.setup_complete
fi

# Clear any stale caches first
php artisan config:clear || true
php artisan view:clear || true

# Cache config & routes for production
php artisan config:cache || true
php artisan route:cache || true
php artisan view:cache || true

exec "$@"
