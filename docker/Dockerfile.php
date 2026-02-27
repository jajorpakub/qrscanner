FROM php:8.2-fpm

RUN apt-get update && apt-get install -y \
    git \
    curl \
    zip \
    unzip \
    && rm -rf /var/lib/apt/lists/*

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Install PHP extensions
RUN docker-php-ext-install pdo pdo_mysql

WORKDIR /app

COPY backend/composer.json composer.json
COPY backend/composer.lock* composer.lock*

RUN composer install --no-dev --optimize-autoloader --ignore-platform-reqs 2>&1 || composer update --no-dev --optimize-autoloader --with-all-dependencies

COPY backend/ .

EXPOSE 9000

CMD ["php-fpm"]
