ARG PHP_VERSION

FROM php:$PHP_VERSION-fpm-alpine

# build time dependencies
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
    re2c \
    libxml2-dev

# Use a more local mirror as the CDN is sometimes terribly slow
RUN echo http://mirror1.hs-esslingen.de/pub/Mirrors/alpine/v3.9/main > /etc/apk/repositories; \
    echo http://mirror1.hs-esslingen.de/pub/Mirrors/alpine/v3.9/community >> /etc/apk/repositories

# persistent dependencies
# inspired by https://github.com/prooph/docker-files/blob/master/php/7.1-fpm
RUN set -ex \
       &&  apk add --no-cache \
        bash \
        mysql-client \
        gzip \
        icu \
        libzip-dev \
        freetype-dev \
        libjpeg-turbo-dev \
        libpng-dev \
        icu-dev

RUN set -ex \
    && apk add --no-cache --virtual .build-deps \
        $PHPIZE_DEPS \
    && docker-php-ext-configure bcmath --enable-bcmath \
    && docker-php-ext-configure gd \
        --with-gd \
        --with-freetype-dir=/usr/include \
        --with-png-dir=/usr/include \
        --with-jpeg-dir=/usr/include \
    && docker-php-ext-configure intl --enable-intl  \
    && docker-php-ext-configure pcntl --enable-pcntl \
    && docker-php-ext-configure pdo_mysql --with-pdo-mysql \
    && docker-php-ext-configure mbstring --enable-mbstring \
    && docker-php-ext-configure soap --enable-soap \
    && docker-php-ext-configure xml --enable-xml \
    && docker-php-ext-configure zip --enable-zip --with-libzip \
    && docker-php-ext-install \
        bcmath \
        gd \
        intl \
        pcntl \
        pdo_mysql \
        mbstring \
        soap \
        xml \
        zip \
    && pecl install -o -f redis \
    && rm -rf /tmp/pear \
    && docker-php-ext-enable redis \
    && apk del .build-deps
#    && pecl install xdebug-2.6.0 \
#    && docker-php-ext-enable xdebug \
