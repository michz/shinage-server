ARG PHP_VERSION

FROM dunglas/frankenphp:php8.4-bookworm as base

ENV DEBIAN_FRONTEND=noninteractive

ARG APP_BUILDDATE
ARG APP_REVISION

ENV APP_BUILDDATE=${APP_BUILDDATE}
ENV APP_REVISION=${APP_REVISION}

RUN apt-get update \
    && apt-get -y -o 'Dpkg::Options::=--force-confdef' -o 'Dpkg::Options::=--force-confold' --no-install-recommends dist-upgrade \
    && apt-get -y clean \
    && rm -rf /var/cache/apt \
    && install-php-extensions \
             pdo_mysql \
             gd \
             intl \
             mbstring \
             opcache \
             redis \
             zip \
    && rm -rf /var/lib/apt/lists/* \
    && cp $PHP_INI_DIR/php.ini-production $PHP_INI_DIR/php.ini

RUN mkdir -p -m 0777 /app/var/cache /app/var/log \
    && chown -R www-data:www-data /app/var \
    && chmod -R 0777 /app/var

COPY . /app
COPY etc/prod/php.ini /usr/local/etc/php/conf.d/99-custom.ini
COPY etc/prod/monolog.yaml /app/config/packages/monolog.yaml
COPY etc/prod/caddy.caddyfile /etc/frankenphp/Caddyfile

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app


FROM base AS prod
ENV APP_ENV=prod

RUN composer install --no-scripts --ignore-platform-reqs --optimize-autoloader --no-progress --no-dev \
    && rm /usr/bin/composer \
    && touch /app/.env

EXPOSE 80/tcp

VOLUME /app/data

USER www-data
