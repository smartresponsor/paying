FROM php:8.4-cli-alpine

WORKDIR /app

RUN apk add --no-cache \
    bash \
    git \
    icu-dev \
    libpq-dev \
    oniguruma-dev \
    postgresql-dev \
    sqlite-dev \
    unzip \
    zip \
 && docker-php-ext-install intl opcache pdo pdo_pgsql pdo_sqlite

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

COPY composer.json composer.lock* ./
RUN composer install --no-dev --prefer-dist --no-interaction --no-progress || composer install --prefer-dist --no-interaction --no-progress

COPY . .

RUN mkdir -p var/cache var/log var/run

EXPOSE 8080

CMD ["php", "-S", "0.0.0.0:8080", "-t", "public", "public/index.php"]
