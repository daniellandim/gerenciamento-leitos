FROM php:8.4-fpm

RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpq-dev \
    libzip-dev \
    zip \
    unzip \
    && docker-php-ext-install pdo pdo_pgsql zip \
        && pecl install redis \
        && docker-php-ext-enable redis \
        && apt-get clean \
        && rm -rf /var/lib/apt/lists/*

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www

COPY . .

RUN composer install --no-interaction --no-scripts --prefer-dist

RUN chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache

EXPOSE 9000
CMD ["php-fpm"]
