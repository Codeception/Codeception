ARG flavor=bullseye

FROM php:8.1-cli-${flavor}

LABEL maintainer="Tobias Munk <tobias@diemeisterei.de>"

COPY --from=mlocati/php-extension-installer /usr/bin/install-php-extensions /usr/local/bin/

RUN set -eux; \
    install-php-extensions \
        bcmath \
        mysqli \
        pdo pdo_mysql pdo_pgsql \
        soap \
        sockets \
        zip \
        apcu-stable \
        memcached-stable \
        mongodb-stable \
        xdebug-stable \
        # and composer \
        @composer; \
    # Configure php \
    echo "date.timezone = UTC" >> /usr/local/etc/php/php.ini;

ENV COMPOSER_ALLOW_SUPERUSER '1'

WORKDIR /codecept

# Install codeception
RUN set -eux; \
    composer require --no-update \
        codeception/codeception \
        codeception/module-apc \
        codeception/module-asserts \
        codeception/module-cli \
        codeception/module-db \
        codeception/module-filesystem \
        codeception/module-ftp \
        codeception/module-memcache \
        codeception/module-mongodb \
        codeception/module-phpbrowser \
        codeception/module-redis \
        codeception/module-rest \
        codeception/module-sequence \
        codeception/module-soap \
        codeception/module-webdriver; \
    composer update --no-dev --prefer-dist --no-interaction --optimize-autoloader --apcu-autoloader; \
    ln -s /codecept/vendor/bin/codecept /usr/local/bin/codecept; \
    mkdir /project;

ENTRYPOINT ["codecept"]

# Prepare host-volume working directory
WORKDIR /project
