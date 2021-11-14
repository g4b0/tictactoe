FROM php:8.0-fpm-alpine

WORKDIR /var/www/html/

RUN php -r "readfile('http://getcomposer.org/installer');" | php -- --install-dir=/usr/bin/ --filename=composer

COPY . .

RUN export COMPOSER_ALLOW_SUPERUSER=1; composer install