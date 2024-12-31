ARG PHP_VERSION

FROM php:$PHP_VERSION-fpm AS base

ENV DEBIAN_FRONTEND=noninteractive

ARG APP_BUILDDATE
ARG APP_REVISION

ENV APP_BUILDDATE=${APP_BUILDDATE}
ENV APP_REVISION=${APP_REVISION}

RUN apt-get update \
    && apt-get -y -o 'Dpkg::Options::=--force-confdef' -o 'Dpkg::Options::=--force-confold' --no-install-recommends dist-upgrade \
    && apt-get -y -o 'Dpkg::Options::=--force-confdef' -o 'Dpkg::Options::=--force-confold' --no-install-recommends install \
        dumb-init \
        wget \
        ca-certificates \
        libmagickwand-dev \
        libzip-dev \
        libpng-dev \
        git \
        libxslt-dev \
    && rm -rf /var/cache/apt \
    && docker-php-ext-configure zip \
    && docker-php-ext-install zip \
    && docker-php-ext-configure gd \
    && docker-php-ext-install -j$(nproc) gd \
    && docker-php-ext-configure intl \
    && docker-php-ext-install intl \
    && docker-php-ext-install mysqli pdo pdo_mysql \
    && docker-php-ext-install opcache \
    && docker-php-ext-install xml \
    && docker-php-ext-install xsl \
    && pecl install -o -f redis \
    && rm -rf /tmp/pear \
    && docker-php-ext-enable redis \
    && apt-get -y clean \
    && rm -rf /var/lib/apt/lists/*

RUN mkdir /app \
    && mkdir -p -m 0777 /app/var/cache /app/var/log \
    && chown -R www-data:www-data /app/var \
    && chmod -R 0777 /app/var \
    && mkdir -m 0700 /run/php \
    && chown -R www-data:www-data /run/php

COPY . /app
COPY etc/prod/php-fpm.conf /usr/local/etc/php-fpm.conf
COPY etc/prod/php-fpm-pool.conf /usr/local/etc/php-fpm.d/www.conf
COPY etc/prod/php.ini /usr/local/etc/php/conf.d/99-custom.ini
COPY etc/prod/monolog.yaml /app/config/packages/monolog.yaml

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app



FROM base AS prod
ENV APP_ENV=prod

RUN composer install --no-scripts --ignore-platform-reqs --optimize-autoloader --no-progress --no-dev \
    && rm /usr/bin/composer \
    && touch /app/.env

EXPOSE 9000/tcp

VOLUME /app/data

USER www-data

ENTRYPOINT ["dumb-init", "/app/entrypoint.sh"]

CMD ["php-fpm"]
