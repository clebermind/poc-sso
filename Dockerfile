FROM php:8.1-fpm

RUN apt-get update && apt-get install -y \
    vim \
    git \
    unzip \
    curl \
    && docker-php-ext-install pdo pdo_mysql

RUN pecl install redis && docker-php-ext-enable redis

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

WORKDIR /var/www/html

COPY ./src /var/www/html

# Change permissions for web server user as a POC we are giving 777 to avoid issues editing files, does not matter
RUN chown -R www-data:www-data /var/www/html && \
    chmod 777 -Rf /var/www

CMD ["php-fpm"]
