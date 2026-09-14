# syntax=docker/dockerfile:1

# ---------------------------------------------------------------------------
# Etapa 1: compilar los assets del frontend (Tailwind CSS vía Vite).
# ---------------------------------------------------------------------------
FROM node:20-alpine AS assets

WORKDIR /app

COPY package.json package-lock.json ./
RUN npm ci

COPY resources ./resources
COPY vite.config.js ./
COPY public ./public

RUN npm run build

# ---------------------------------------------------------------------------
# Etapa 2: la aplicación PHP, servida con Apache.
# ---------------------------------------------------------------------------
FROM php:8.3-apache AS app

# mbstring (Laravel, dompdf, PhpSpreadsheet), gd y zip (PhpSpreadsheet)
# no vienen habilitadas en la imagen base de PHP; pdo_pgsql tampoco,
# y es la que este proyecto necesita para conectar a PostgreSQL.
RUN apt-get update && apt-get install -y --no-install-recommends \
        libpq-dev \
        libzip-dev \
        libpng-dev \
        libjpeg62-turbo-dev \
        libfreetype6-dev \
        libonig-dev \
        unzip \
        git \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo pdo_pgsql zip gd mbstring \
    && a2enmod rewrite \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# El código primero, para que composer resuelva el autoload contra el
# proyecto real (algunos paquetes inspeccionan la app durante el install).
COPY . .
COPY --from=assets /app/public/build ./public/build

RUN composer install --no-dev --optimize-autoloader --no-interaction --prefer-dist \
    && chown -R www-data:www-data storage bootstrap/cache

COPY docker/000-default.conf /etc/apache2/sites-available/000-default.conf
COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

EXPOSE 80

ENTRYPOINT ["entrypoint.sh"]
CMD ["apache2-foreground"]
