# syntax=docker/dockerfile:1

ARG flavor=trixie
# The images are published for every PHP version supported by Codeception, this is the default one
ARG PHP_VERSION=8.5

FROM php:${PHP_VERSION}-cli-${flavor}

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

ENV COMPOSER_ALLOW_SUPERUSER=1

WORKDIR /codecept

# Version of the Codeception sources in the build context, e.g. 5.3.7.
# Defaults to Codecept::VERSION from the sources, so a plain `docker build .` works.
ARG CODECEPTION_VERSION

# Install codeception from the build context (the workflow uses `git archive <tag>` as context)
# TODO: remove COMPOSER_NO_SECURITY_BLOCKING once module-ftp allows a phpseclib version without security advisories
RUN --mount=type=bind,target=/tmp/codeception \
    set -eux; \
    version="${CODECEPTION_VERSION:-$(sed -n "s/.*const VERSION = '\(.*\)';/\1/p" /tmp/codeception/src/Codeception/Codecept.php)}"; \
    test -n "${version}"; \
    echo '{}' > composer.json; \
    composer config repositories.codeception "{\"type\": \"path\", \"url\": \"/tmp/codeception\", \"options\": {\"symlink\": false, \"versions\": {\"codeception/codeception\": \"${version}\"}}}"; \
    composer require --no-update \
        "codeception/codeception:${version}" \
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
    COMPOSER_NO_SECURITY_BLOCKING=1 composer update --no-dev --prefer-dist --no-interaction --optimize-autoloader --apcu-autoloader; \
    ln -s /codecept/vendor/bin/codecept /usr/local/bin/codecept; \
    mkdir /project;

ENTRYPOINT ["codecept"]

# Prepare host-volume working directory
WORKDIR /project
