FROM php:8.4-fpm

# System dependencies
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libpq-dev \
    netcat-openbsd \
    && docker-php-ext-install pdo pdo_pgsql

# Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html
