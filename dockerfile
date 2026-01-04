FROM php:8-apache

RUN apt-get update && apt-get install -y \
    unzip \
    git \
    curl \
    && rm -rf /var/lib/apt/lists/*

RUN curl -sS https://getcomposer.org/installer | php -- \
    --install-dir=/usr/local/bin \
    --filename=composer

RUN docker-php-ext-install mysqli pdo_mysql

RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"

COPY src/ /var/www/html/
COPY assets/ /var/www/html/assets/
COPY styles/ /var/www/html/styles/
COPY tests/ /var/www/html/tests/
COPY composer.json /var/www/html/
COPY phpunit.xml /var/www/html/

RUN chown -R www-data:www-data /var/www/html
