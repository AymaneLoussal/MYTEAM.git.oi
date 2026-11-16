#!/bin/sh
set -eu
mkdir -p storage/app/public storage/framework/cache/data storage/framework/sessions storage/framework/views bootstrap/cache database /run/nginx
chown -R app:app storage bootstrap/cache database
storage_link='public/storage'
storage_target='../storage/app/public'
if [ -L "$storage_link" ]; then
 current_target="$(readlink "$storage_link" || true)"
 if [ "$current_target" != "$storage_target" ] && [ "$current_target" != '/app/storage/app/public' ]; then
  echo "Replacing incorrect public/storage symlink (uploaded files are unchanged)." >&2
  rm "$storage_link"
  ln -s "$storage_target" "$storage_link"
 fi
elif [ -e "$storage_link" ]; then
 echo "public/storage is a real file or directory; leaving it unchanged." >&2
else
 ln -s "$storage_target" "$storage_link"
fi
if [ "${DB_CONNECTION:-sqlite}" = sqlite ]; then
 db="${DB_DATABASE:-/app/database/docker.sqlite}"
 mkdir -p "$(dirname "$db")"
 [ -f "$db" ] || touch "$db"
 chown app:app "$db"
fi
if [ "${RUN_MIGRATIONS:-false}" = true ]; then
 su -s /bin/sh app -c 'php artisan migrate --force'
fi
php-fpm -D
exec nginx -g 'daemon off;'
