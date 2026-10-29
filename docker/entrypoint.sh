#!/bin/sh
set -e

# /var/www/vendor is a named volume in production, and Docker only copies the
# image's vendor into it when the volume is first created. When this image was
# built from a different composer.lock, refresh the volume from /opt/vendor and,
# like composer install's post-autoload-dump scripts, drop the cached config and
# manifests built against the old packages. The stamp is written last, so a
# failed refresh is retried on the next start.
if ! cmp -s /opt/vendor/.composer-lock.sha1 /var/www/vendor/.composer-lock.sha1; then
  mkdir -p /var/www/vendor
  find /var/www/vendor -mindepth 1 -delete
  cp -a /opt/vendor/. /var/www/vendor/
  rm /var/www/vendor/.composer-lock.sha1
  rm -f /var/www/bootstrap/cache/config.php \
        /var/www/bootstrap/cache/services.php \
        /var/www/bootstrap/cache/packages.php
  php artisan package:discover --ansi
  cp /opt/vendor/.composer-lock.sha1 /var/www/vendor/.composer-lock.sha1
fi

# Bring the database up to date before the API starts serving, so new code
# never runs against an old schema. Only when starting the server, so one-off
# commands like `php artisan migrate:status` still see pending migrations.
# This only runs pending migrations: never a rollback, fresh or wipe. --force
# skips the confirmation Laravel asks for in production. Postgres runs each
# migration in a transaction, so one that fails changes nothing. The database
# may still be starting (after a reboot both containers start together), so
# keep trying for about a minute, then exit and let Docker restart us. This
# runs before the permissions fix below, which hands any log file it creates
# to www-data.
if [ "$1" = "php-fpm" ]; then
  attempt=1
  until php artisan migrate --force; do
    if [ "$attempt" -ge 30 ]; then
      echo "entrypoint.sh: migrations failed $attempt times, not starting $1" >&2
      exit 1
    fi
    echo "entrypoint.sh: migrations failed (attempt $attempt of 30), retrying in 2s" >&2
    attempt=$((attempt + 1))
    sleep 2
  done
fi

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
