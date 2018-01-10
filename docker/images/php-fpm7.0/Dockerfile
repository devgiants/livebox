FROM ubuntu:16.10

MAINTAINER Nicolas BONNIOT <nicolas@devgiants.fr>

ARG UID

# Installation PHP + extensions
RUN apt-get update && apt-get install -y \
    curl \
    nano \
    php-fpm \
    php-mbstring \
    php-mcrypt \
    php-mysql \
    php-apcu \
    php-curl \
    php-intl \
    php-xdebug \
    php-xml \
    php-zip

# Configuration PHP (FPM, CLI) + extensions
COPY config/etc/php/7.0/fpm/php-fpm.conf /etc/php/7.0/fpm/php-fpm.conf
COPY config/etc/php/7.0/fpm/pool.d/www.conf /etc/php/7.0/fpm/pool.d/www.conf

COPY config/etc/php/7.0/mods-available/php-custom.ini /etc/php/7.0/mods-available/php-custom.ini
RUN ln -s /etc/php/7.0/mods-available/php-custom.ini /etc/php/7.0/fpm/conf.d/php-custom.ini
RUN ln -s /etc/php/7.0/mods-available/php-custom.ini /etc/php/7.0/cli/conf.d/php-custom.ini

# La config de xdebug est automatiquement ajoutée dans fpm et cli
COPY config/etc/php/7.0/mods-available/xdebug.ini /etc/php/7.0/mods-available/xdebug.ini

# Allow PHAR generation
#RUN echo "phar.readonly = Off" >> /etc/php/7.0/cli/php.ini

# Composer
RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');" \
    && php composer-setup.php --install-dir=/usr/local/bin --filename=composer \
    && php -r "unlink('composer-setup.php');"

# Box
#RUN curl -LSs https://box-project.github.io/box2/installer.php | php
#RUN mv box.phar /usr/local/bin/box
#RUN chmod u+x /usr/local/bin/box

RUN usermod -u ${UID} www-data

WORKDIR /var/www/html

EXPOSE 9000

RUN service php7.0-fpm start

CMD php-fpm7.0 -F

