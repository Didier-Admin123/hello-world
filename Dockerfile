FROM php:8.0.7-fpm-alpine3.13
RUN docker-php-ext-install pdo_mysql
