FROM php:7.4-fpm AS crmeb_php
WORKDIR /var/www

RUN set -eux; \
    apt-get update; \
    apt-get install -y --no-install-recommends \
        libfreetype6-dev \
        libjpeg62-turbo-dev \
        libpng-dev \
        libzip-dev \
        unzip \
        git; \
    docker-php-ext-configure gd --with-freetype --with-jpeg; \
    docker-php-ext-install -j"$(nproc)" bcmath mysqli pdo_mysql gd pcntl sockets; \
    pecl install redis; \
    docker-php-ext-enable redis; \
    rm -rf /var/lib/apt/lists/*

RUN echo "open_basedir=/tmp:/var/www:/var" > /usr/local/etc/php/conf.d/open_basedir.ini
