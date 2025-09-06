#!/bin/sh
set -e

# Make sure writable dirs exist + perms are correct (volumes can override image perms)
mkdir -p /var/www/storage/logs \
         /var/www/storage/framework/{cache,sessions,views} \
         /var/www/bootstrap/cache
chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache
chmod -R ug+rwX /var/www/storage /var/www/bootstrap/cache

# Create the public/storage symlink if missing
if [ ! -L /var/www/public/storage ]; then
  php artisan storage:link || true
fi

# hand off to the default command (php-fpm)
exec "$@"
