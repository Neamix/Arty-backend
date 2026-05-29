# syntax=docker/dockerfile:1.7

FROM composer:2 AS vendor
WORKDIR /app
COPY composer.json composer.lock ./
COPY database/ database/
RUN composer install \
        --no-dev \
        --no-interaction \
        --no-progress \
        --no-scripts \
        --prefer-dist \
        --optimize-autoloader


FROM node:22-alpine AS assets
WORKDIR /app
COPY package.json package-lock.json ./
RUN npm ci --no-audit --no-fund
COPY resources/ resources/
COPY vite.config.js ./
COPY public/ public/
RUN npm run build

FROM php:8.4-fpm-alpine AS runtime

ARG APP_USER=www
ARG APP_UID=1000
ARG APP_GID=1000

RUN apk add --no-cache \
        nginx \
        supervisor \
        bash \
        tini \
        icu-dev \
        libzip-dev \
        oniguruma-dev \
        libpng-dev \
        libjpeg-turbo-dev \
        freetype-dev \
        postgresql-dev \
        sqlite-dev \
        $PHPIZE_DEPS \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
        pdo_mysql \
        pdo_pgsql \
        pdo_sqlite \
        bcmath \
        intl \
        zip \
        gd \
        opcache \
        pcntl \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && apk del $PHPIZE_DEPS \
    && addgroup -g ${APP_GID} ${APP_USER} \
    && adduser -D -u ${APP_UID} -G ${APP_USER} -s /bin/bash ${APP_USER}

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

COPY docker/php/php.ini /usr/local/etc/php/conf.d/zz-app.ini
COPY docker/php/opcache.ini /usr/local/etc/php/conf.d/zz-opcache.ini
COPY docker/php/www.conf /usr/local/etc/php-fpm.d/zz-www.conf
COPY docker/nginx/nginx.conf /etc/nginx/nginx.conf
COPY docker/supervisor/supervisord.conf /etc/supervisor/supervisord.conf
COPY docker/entrypoint.sh /usr/local/bin/entrypoint
RUN chmod +x /usr/local/bin/entrypoint

WORKDIR /var/www/html

COPY --chown=${APP_USER}:${APP_USER} . .
COPY --from=vendor   --chown=${APP_USER}:${APP_USER} /app/vendor          ./vendor
COPY --from=assets   --chown=${APP_USER}:${APP_USER} /app/public/build    ./public/build

RUN mkdir -p storage/framework/{cache,sessions,views} \
                storage/logs \
                bootstrap/cache \
    && chown -R ${APP_USER}:${APP_USER} storage bootstrap/cache \
    && chmod -R ug+rwX storage bootstrap/cache \
    && composer dump-autoload --optimize --no-dev --classmap-authoritative

EXPOSE 8080

ENV APP_ENV=production \
        APP_DEBUG=false \
        LOG_CHANNEL=stderr

ENTRYPOINT ["/sbin/tini","--","/usr/local/bin/entrypoint"]
CMD ["supervisord","-c","/etc/supervisor/supervisord.conf"]

HEALTHCHECK --interval=30s --timeout=5s --start-period=20s --retries=3 \
        CMD wget -qO- http://127.0.0.1:8080/up || exit 1
