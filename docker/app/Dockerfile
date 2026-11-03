FROM php:8.5-fpm

ARG HOST_UID
ARG HOST_GID

RUN groupadd -g ${HOST_GID} app \
    && useradd -u ${HOST_UID} -g ${HOST_GID} -m app

WORKDIR /var/www/html

RUN apt-get update
RUN apt-get install -y \
    zip unzip curl \
    && docker-php-ext-install pdo pdo_mysql \
    && pecl install xdebug \
    && docker-php-ext-enable xdebug

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
COPY ./docker/app/xdebug.ini /usr/local/etc/php/conf.d/xdebug.ini
