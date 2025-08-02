FROM php:8.2-fpm

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git curl zip unzip libonig-dev libxml2-dev libzip-dev libpng-dev \
    libcurl4-openssl-dev libssl-dev mariadb-client

# Install PHP extensions
RUN docker-php-ext-install pdo_mysql mbstring zip exif pcntl

# Set working directory
WORKDIR /var/www

# Install Composer (from Composer official image)
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Copy rest of the application
COPY . .

# ✅ Install Composer dependencies
RUN composer install --no-dev --prefer-dist --no-interaction

# Set permissions
RUN chown -R www-data:www-data /var/www && chmod -R 755 /var/www

EXPOSE 9000
CMD ["php-fpm"]
