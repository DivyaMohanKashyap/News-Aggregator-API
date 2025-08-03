FROM php:8.2-fpm

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git curl zip unzip libonig-dev libxml2-dev libzip-dev libpng-dev \
    libcurl4-openssl-dev libssl-dev mariadb-client

# Install PHP extensions
RUN docker-php-ext-install pdo pdo_mysql mbstring zip exif pcntl

# Install Xdebug
RUN pecl install xdebug \
    && docker-php-ext-enable xdebug

# Enable Xdebug for coverage only
RUN echo "xdebug.mode=coverage" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
    && echo "xdebug.start_with_request=yes" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini

# Install Composer globally from the official Composer image
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www

# Copy entire app
COPY . .

EXPOSE 9000

# Set permissions
RUN chown -R www-data:www-data /var/www && chmod -R 755 /var/www

CMD ["php-fpm"]
