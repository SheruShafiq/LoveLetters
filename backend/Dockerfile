FROM php:latest

# Install dependencies
RUN apt-get update && apt-get install -y \
    libpq-dev \
    libzip-dev \
    zip \
    unzip

# Install PHP extensions
RUN docker-php-ext-install pdo_pgsql

# Install Xdebug
RUN pecl install xdebug \
    && docker-php-ext-enable xdebug

# Copy Xdebug configuration
COPY xdebug.ini /usr/local/etc/php/conf.d/xdebug.ini

# Set the working directory
WORKDIR /var/www/html

# Copy the source code
COPY src/ .

# Expose port 80
EXPOSE 80

# Start PHP built-in web server
CMD ["php", "-S", "0.0.0.0:80"]
