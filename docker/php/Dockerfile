FROM php:8.4-fpm

RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        git \
        unzip \
        curl \
        libpq-dev \
        libicu-dev \
        libzip-dev \
    && docker-php-ext-install -j"$(nproc)" \
        pdo_pgsql \
        intl \
        zip \
        bcmath \
        pcntl \
        opcache \
    && curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer \
    && rm -rf /var/lib/apt/lists/*

WORKDIR /app

COPY docker/php/conf.d/app.ini ${PHP_INI_DIR}/conf.d/app.ini
COPY docker/php/entrypoint.sh /usr/local/bin/entrypoint.sh

RUN chmod +x /usr/local/bin/entrypoint.sh

COPY . /app

ENTRYPOINT ["entrypoint.sh"]
CMD ["php-fpm"]
