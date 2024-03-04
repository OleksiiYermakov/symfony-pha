FROM php:8.2-fpm

RUN apt-get update && apt-get install -y --no-install-recommends \
    git \
    unzip \
    && php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');" \
    && php composer-setup.php --install-dir=/usr/local/bin --filename=composer \
    && php -r "unlink('composer-setup.php');" \
    && pecl install xdebug \
    && docker-php-ext-enable xdebug

RUN mv /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini /docker-php-ext-xdebug.ini

CMD ["php-fpm"]
