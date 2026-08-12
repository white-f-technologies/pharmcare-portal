FROM php:8.2-apache

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libsqlite3-dev \
    zip \
    unzip \
    sqlite3 \
    dos2unix \
    && docker-php-ext-install pdo pdo_sqlite mbstring exif pcntl bcmath gd \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Enable Apache mod_rewrite
RUN a2enmod rewrite

# Configure Apache to listen on Render's PORT (default 10000) instead of 80
RUN sed -i 's/Listen 80/Listen ${PORT}/' /etc/apache2/ports.conf
RUN sed -i 's/:80/:${PORT}/' /etc/apache2/sites-available/000-default.conf

# Set Apache DocumentRoot to Laravel's public directory
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# Environment variables for Render
ENV PORT=10000
ENV VIEW_COMPILED_PATH=/var/www/html/storage/framework/views

# Set working directory
WORKDIR /var/www/html

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copy application code
COPY . /var/www/html

# Create SQLite database and storage directories BEFORE composer install
RUN mkdir -p /var/www/html/storage/app/public \
    && mkdir -p /var/www/html/storage/framework/cache/data \
    && mkdir -p /var/www/html/storage/framework/sessions \
    && mkdir -p /var/www/html/storage/framework/views \
    && mkdir -p /var/www/html/storage/logs \
    && mkdir -p /var/www/html/storage/keys \
    && mkdir -p /var/www/html/database \
    && mkdir -p /var/www/html/bootstrap/cache \
    && touch /var/www/html/database/database.sqlite

# Set open permissions so both root CLI and www-data web server can read/write
RUN chmod -R 777 /var/www/html/storage /var/www/html/bootstrap/cache /var/www/html/database

# Create minimal .env so artisan works during build
RUN printf "APP_NAME=PharmCare\nAPP_ENV=production\nAPP_KEY=base64:dGVtcG9yYXJ5a2V5Zm9yYnVpbGQxMjM0NTY3ODk=\nAPP_DEBUG=false\nDB_CONNECTION=sqlite\nDB_DATABASE=/var/www/html/database/database.sqlite\nVIEW_COMPILED_PATH=/var/www/html/storage/framework/views\n" > .env

# Install PHP dependencies (skip scripts - they run at startup via entrypoint)
RUN composer install --no-dev --no-scripts --optimize-autoloader --no-interaction

# Copy entrypoint script and fix Windows CRLF line endings
COPY docker-entrypoint.sh /usr/local/bin/
RUN dos2unix /usr/local/bin/docker-entrypoint.sh && chmod +x /usr/local/bin/docker-entrypoint.sh

EXPOSE 10000

ENTRYPOINT ["docker-entrypoint.sh"]
CMD ["apache2-foreground"]
