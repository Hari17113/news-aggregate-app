# Use the official PHP image with FPM (FastCGI Process Manager)
FROM php:8.2-fpm

# Install necessary system packages and PHP extensions
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    zip \
    git \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install gd pdo pdo_mysql \
    && apt-get clean

# Install Composer globally
RUN curl -sS https://getcomposer.org/installer | php \
    && mv composer.phar /usr/local/bin/composer

# Set the working directory inside the container
WORKDIR /var/www

# Expose port 9000 (PHP-FPM)
EXPOSE 9000
