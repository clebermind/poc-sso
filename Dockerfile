# Dockerfile
FROM php:8.1-fpm

# Install necessary packages and PHP extensions
RUN apt-get update && apt-get install -y \
    vim \
    git \
    unzip \
    curl \
    && docker-php-ext-install pdo pdo_mysql

# Install Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Set the working directory
WORKDIR /var/www/html

# Copy files first to set permissions correctly
COPY ./src /var/www/html

# Change permissions for web server user as a POC we are giving 777 to avoid issues editing files, does not matter
RUN chown -R www-data:www-data /var/www/html && \
    chmod 777 -Rf /var/www

# Command to run PHP-FPM
CMD ["php-fpm"]
