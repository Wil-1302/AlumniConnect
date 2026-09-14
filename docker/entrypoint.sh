#!/bin/sh
set -e

# Render entrega el puerto a escuchar en la variable de entorno PORT (no
# siempre 80). Apache no conoce esa variable de fábrica, así que la
# sustituimos en su configuración recién al arrancar el contenedor, que es
# el único momento en que ya se conoce su valor real.
: "${PORT:=80}"
sed -ri "s/Listen 80/Listen ${PORT}/" /etc/apache2/ports.conf
sed -ri "s/:80>/:${PORT}>/" /etc/apache2/sites-available/000-default.conf

# Las migraciones no pueden ejecutarse durante "docker build": en ese
# momento no existe conexión a la base de datos de Render. Se ejecutan
# aquí, en cada arranque del contenedor. migrate --force es idempotente
# (Laravel omite las migraciones ya aplicadas), así que es seguro repetirlo
# en cada despliegue. Ver docs/despliegue.md para el detalle y su
# limitación si en el futuro se escala a más de una instancia.
php artisan migrate --force

# El cacheo de config/rutas/vistas se hace aquí y no en la imagen, porque
# recién en el arranque están disponibles las variables de entorno reales
# (APP_KEY, DB_*, etc.) que Render inyecta en tiempo de ejecución.
php artisan config:cache
php artisan route:cache
php artisan view:cache

exec "$@"
