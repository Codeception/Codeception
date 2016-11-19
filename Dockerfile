FROM php:7.0-alpine

MAINTAINER Tobias Munk tobias@diemeisterei.de

# Install required system packages
RUN apk update --no-cache && \
    apk add --no-cache \
        git \
        zlib-dev \
        openssl-dev

# Install php extensions
RUN docker-php-ext-install \
        bcmath \
        zip

# Install pecl extensions
RUN apk add --no-cache  --virtual .ext-deps \
        autoconf \
        gcc \
        git \
        make \
        musl-dev \
        re2c && \
    pecl install mongodb xdebug && \
    docker-php-ext-enable mongodb && \
    docker-php-ext-enable xdebug && \
    apk del --no-cache --purge -r .ext-deps && \
    rm -rf /var/cache/apk/* /var/tmp/* /tmp/*

# Configure php
RUN echo "date.timezone = UTC" >> /usr/local/etc/php/php.ini

# Install composer
ENV COMPOSER_ALLOW_SUPERUSER=1
RUN curl -sS https://getcomposer.org/installer | php -- \
        --filename=composer \
        --install-dir=/usr/local/bin
RUN composer global require --optimize-autoloader \
        "hirak/prestissimo"

# Prepare application
WORKDIR /repo

# Install vendor
COPY ./composer.json /repo/composer.json
RUN composer install --prefer-dist --optimize-autoloader

# Add source-code
COPY . /repo

ENV PATH /repo:${PATH}
ENTRYPOINT ["codecept"]

# Prepare host-volume working directory
RUN mkdir /project
WORKDIR /project
