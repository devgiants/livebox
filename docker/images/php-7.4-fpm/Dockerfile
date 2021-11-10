FROM php:7.4-fpm

MAINTAINER Nicolas BONNIOT <nicolas@devgiants.fr>

ARG UID

# Installation PHP + extensions
RUN apt-get update && apt-get install -y \
    zip \
    git

#RUN docker-php-ext-install zip

# Composer
RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');" \
    && php composer-setup.php --install-dir=/usr/local/bin --filename=composer \
    && php -r "unlink('composer-setup.php');"


RUN usermod -u ${UID} www-data

WORKDIR /var/www/html
USER www-data

