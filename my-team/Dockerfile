FROM composer:2.8.12 AS vendor
WORKDIR /app
COPY composer.json composer.lock ./
RUN composer install --no-dev --prefer-dist --no-interaction --no-progress --optimize-autoloader --no-scripts
COPY . .
RUN composer dump-autoload --no-dev --classmap-authoritative --no-interaction

FROM node:22.14.0-alpine3.21 AS frontend
WORKDIR /app
COPY package.json package-lock.json ./
RUN npm ci
COPY . .
COPY --from=vendor /app/vendor ./vendor
RUN npm run build

FROM php:8.4.16-fpm-bookworm AS runtime
RUN apt-get update && apt-get install -y --no-install-recommends nginx libsqlite3-0 libsqlite3-dev libpq-dev \
 && docker-php-ext-install pdo_sqlite pdo_pgsql opcache \
 && rm -rf /var/lib/apt/lists/* \
 && groupadd --system app && useradd --system --gid app --home-dir /app app
WORKDIR /app
COPY --from=vendor --chown=app:app /app /app
COPY --from=frontend --chown=app:app /app/public/build /app/public/build
COPY docker/nginx/default.conf /etc/nginx/sites-enabled/default
COPY docker/php/production.ini /usr/local/etc/php/conf.d/zz-production.ini
COPY docker/php/www.conf /usr/local/etc/php-fpm.d/zz-ftmball.conf
COPY docker/entrypoint.sh /usr/local/bin/ftmball-entrypoint
RUN chmod 755 /usr/local/bin/ftmball-entrypoint && mkdir -p /run/nginx /app/storage/app/public /app/storage/framework/cache/data /app/storage/framework/sessions /app/storage/framework/views /app/bootstrap/cache /app/database \
 && chown -R app:app /app/storage /app/bootstrap/cache /app/database /run/nginx
ENV APP_ENV=production APP_DEBUG=false
EXPOSE 8080
HEALTHCHECK --interval=30s --timeout=5s --start-period=40s --retries=3 CMD php -r 'exit(@file_get_contents("http://127.0.0.1:8080/up")===false?1:0);'
ENTRYPOINT ["/usr/local/bin/ftmball-entrypoint"]
