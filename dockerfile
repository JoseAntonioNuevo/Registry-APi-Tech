# Use the official PHP image with FPM
FROM php:8.3-fpm

# Set environment variables
ENV COMPOSER_ALLOW_SUPERUSER=1

# Install required PHP extensions
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    git \
    unzip \
    dos2unix \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install gd pdo pdo_mysql \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Set the working directory
WORKDIR /var/www

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Copy the application code into the container
COPY . .

# Install dependencies using Composer
RUN composer install --no-interaction --optimize-autoloader --no-dev

# Ensure necessary permissions
RUN chmod -R 775 storage bootstrap/cache

# Expose port 9000 for PHP-FPM
EXPOSE 9000
