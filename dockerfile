FROM php:8-apache

RUN docker-php-ext-install mysqli pdo_mysql

RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"

COPY src/ /var/www/html/
COPY assets/ /var/www/html/assets/
COPY styles/ /var/www/html/styles/

RUN chown -R www-data:www-data /var/www/html
