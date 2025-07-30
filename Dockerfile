FROM php:8.2-fpm

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git curl zip unzip libonig-dev libxml2-dev libzip-dev libpng-dev \
    libcurl4-openssl-dev libssl-dev mariadb-client

# Install PHP extensions
RUN docker-php-ext-install pdo_mysql mbstring zip exif pcntl

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www

# Set permissions
RUN chown -R www-data:www-data /var/www
RUN addgroup -g 82 www-data && adduser -u 82 -D -H -G www-data www-data
RUN mkdir -p /var/www/html/storage/framework/cache/data \
    && chmod -R 775 /var/www/html/storage \
    && chown -R www-data:www-data /var/www/html/storage


EXPOSE 9000
CMD ["php-fpm"]
