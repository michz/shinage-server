ARG PHP_VERSION

#FROM php:$PHP_VERSION-fpm-alpine3.21
FROM dunglas/frankenphp:php8.4-bookworm

# add additional extensions here:
RUN install-php-extensions \
      pdo_mysql \
      gd \
      intl \
      mbstring \
      opcache \
      redis \
      zip \
    && cp $PHP_INI_DIR/php.ini-production $PHP_INI_DIR/php.ini

COPY installComposer.sh /installComposer.sh
RUN sh /installComposer.sh
