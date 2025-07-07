# Dockerfile
FROM php:8.4-fpm

# 1. Install system dependencies
RUN apt-get update \
 && apt-get install -y \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
 && rm -rf /var/lib/apt/lists/*

# 2. Install PHP extensions
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd

# 3. Install MongoDB extension via PECL
RUN pecl install mongodb \
 && docker-php-ext-enable mongodb

# 4. Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# 5. Set working dir
WORKDIR /var/www

# 6. Copy the entire application
COPY . /var/www

# 7. Change to src directory and install PHP dependencies
WORKDIR /var/www/src
RUN composer install --no-dev --optimize-autoloader
