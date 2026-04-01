FROM php:8.2-fpm-alpine

RUN apk add --no-cache \
    icu-dev \
    libzip-dev \
    oniguruma-dev \
    unzip \
    git \
    bash \
    && docker-php-ext-install -j$(nproc) intl mysqli pdo_mysql

COPY --from=composer:2.7 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

CMD ["php-fpm"]
