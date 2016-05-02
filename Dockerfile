FROM php:5.6-cli

RUN apt-get update && \
    apt-get -y install \
            git \
            zlib1g-dev \
            libssl-dev \
        --no-install-recommends && \
        apt-get clean && \
        rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*

# Install php extensions
RUN docker-php-ext-install \
    bcmath \
    zip

# Install pecl extensions
RUN pecl install mongodb && \
    docker-php-ext-enable mongodb

RUN echo "date.timezone = UTC" >> /usr/local/etc/php/php.ini

# Install composer
ENV COMPOSER_ALLOW_SUPERUSER=1
RUN curl -sS https://getcomposer.org/installer | php -- \
        --filename=composer \
        --install-dir=/usr/local/bin
RUN composer global require --optimize-autoloader \
        "hirak/prestissimo"

WORKDIR /var/www

# Install vendor
COPY ./composer.json /var/www/composer.json
RUN composer install --prefer-dist --optimize-autoloader

# Add source-code
COPY . /var/www

ENTRYPOINT ["./codecept"]