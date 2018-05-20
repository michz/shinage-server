ARG PHP_VERSION

FROM php:$PHP_VERSION-fpm-alpine

# persistent / runtime deps
ENV PHPIZE_DEPS \
    autoconf \
    cmake \
    file \
    g++ \
    gcc \
    libc-dev \
    pcre-dev \
    make \
    git \
    pkgconf \
    re2c

# inspired by https://github.com/prooph/docker-files/blob/master/php/7.1-fpm
RUN set -xe \
    && apk add --no-cache --virtual .persistent-deps \
        bash \
        icu-dev \
        libxml2-dev \
        libzip-dev \
        mysql-client \
        gzip

RUN set -xe \
    && apk add --no-cache --virtual .build-deps \
        $PHPIZE_DEPS \
    && docker-php-ext-configure bcmath --enable-bcmath \
    && docker-php-ext-configure intl --enable-intl \
    && docker-php-ext-configure pcntl --enable-pcntl \
    && docker-php-ext-configure pdo_mysql --with-pdo-mysql \
    && docker-php-ext-configure mbstring --enable-mbstring \
    && docker-php-ext-configure soap --enable-soap \
    && docker-php-ext-configure xml --enable-xml \
    && docker-php-ext-configure zip --enable-zip --with-libzip \
    && docker-php-ext-install \
        bcmath \
        intl \
        pcntl \
        pdo_mysql \
        mbstring \
        soap \
        xml \
        zip \
    && apk del .build-deps
#    && pecl install xdebug-2.6.0 \
#    && docker-php-ext-enable xdebug \
