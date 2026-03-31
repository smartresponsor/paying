FROM php:8.4-cli

RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        git \
        chromium \
        chromium-driver \
        libicu-dev \
        libpq-dev \
        libsqlite3-dev \
        libzip-dev \
        unzip \
        zip \
    && docker-php-ext-install -j"$(nproc)" intl pdo_pgsql pdo_sqlite zip \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer

WORKDIR /app

COPY deploy/docker/app-entrypoint.sh /usr/local/bin/payment-app-entrypoint

RUN chmod +x /usr/local/bin/payment-app-entrypoint

ENTRYPOINT ["payment-app-entrypoint"]
CMD ["php", "-S", "0.0.0.0:8000", "-t", "public", "public/index.php"]
