FROM php:7.4-fpm

RUN apt-get update && apt-get install -y \
    libzip-dev \
    libonig-dev \
    libxml2-dev \
    libpng-dev \
    libjpeg62-turbo-dev \
    libfreetype6-dev \
    libwebp-dev \
    libmcrypt-dev \
    libpq-dev \
    && docker-php-ext-install pdo_mysql mbstring xml bcmath zip pcntl \
    && docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp \
    && docker-php-ext-install gd \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Composer 1: laravel/framework v6.18.1 no soporta el formato de
# vendor/composer/installed.json que genera Composer 2 (falla package:discover).
COPY --from=composer:1.10 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html
