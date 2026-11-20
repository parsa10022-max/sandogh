
FROM node:22-bookworm-slim AS frontend

WORKDIR /app
COPY package*.json ./
RUN npm ci
COPY . .
RUN npm run build

FROM php:8.3-apache

RUN apt-get update && apt-get install -y --no-install-recommends \
    libicu-dev libzip-dev libpng-dev libjpeg62-turbo-dev \
    libfreetype6-dev libonig-dev unzip \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j"$(nproc)" pdo_mysql bcmath gd intl zip mbstring opcache \
    && a2enmod rewrite headers \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

COPY composer.json composer.lock ./
RUN composer install --no-dev --no-interaction --prefer-dist \
    --optimize-autoloader --no-scripts

COPY . .
COPY --from=frontend /app/public/build ./public/build

RUN composer dump-autoload --no-dev --optimize --no-scripts \
    && php artisan package:discover --ansi \
    && chown -R www-data:www-data storage bootstrap/cache \
    && sed -ri "s|DocumentRoot /var/www/html|DocumentRoot /var/www/html/public|" /etc/apache2/sites-available/000-default.conf \
    && printf "<Directory /var/www/html/public>\nAllowOverride All\nRequire all granted\n</Directory>\n" >> /etc/apache2/apache2.conf \
    && sed -i "s/Listen 80/Listen 10000/" /etc/apache2/ports.conf \
    && sed -i "s/:80>/:10000>/" /etc/apache2/sites-available/000-default.conf

EXPOSE 10000

CMD ["apache2-foreground"]
