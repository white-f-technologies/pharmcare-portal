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
    && docker-php-ext-install pdo pdo_sqlite mbstring exif pcntl bcmath gd \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Enable Apache mod_rewrite
RUN a2enmod rewrite

# Set Apache DocumentRoot to Laravel's public directory
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

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

# Create minimal .env so artisan package:discover works during build
RUN cp .env.example .env 2>/dev/null || printf "APP_NAME=PharmCare\nAPP_ENV=production\nAPP_KEY=base64:dGVtcG9yYXJ5a2V5Zm9yYnVpbGQxMjM0NTY3ODk=\nAPP_DEBUG=false\nDB_CONNECTION=sqlite\nDB_DATABASE=/var/www/html/database/database.sqlite\n" > .env

# Install PHP dependencies (skip scripts - they run at startup via entrypoint)
RUN composer install --no-dev --no-scripts --optimize-autoloader --no-interaction

# Set proper permissions
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 775 /var/www/html/storage \
    && chmod -R 775 /var/www/html/bootstrap/cache

# Copy entrypoint script
COPY docker-entrypoint.sh /usr/local/bin/
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

EXPOSE 80

ENTRYPOINT ["docker-entrypoint.sh"]
CMD ["apache2-foreground"]
