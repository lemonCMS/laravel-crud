FROM php:7.3-fpm
RUN apt-get update && apt-get install supervisor git zip -y
RUN docker-php-ext-install pdo pdo_mysql
RUN pecl install -o -f redis \
  &&  rm -rf /tmp/pear \
  &&  docker-php-ext-enable redis

RUN curl --silent --show-error https://getcomposer.org/installer | php
RUN mv composer.phar /usr/sbin/composer

RUN yes | pecl install xdebug \
    && echo "zend_extension=$(find /usr/local/lib/php/extensions/ -name xdebug.so)" > /usr/local/etc/php/conf.d/xdebug.ini \
    && echo "xdebug.remote_enable=on" >> /usr/local/etc/php/conf.d/xdebug.ini \
    && echo "xdebug.remote_autostart=off" >> /usr/local/etc/php/conf.d/xdebug.ini

COPY ./conf /usr/local/etc/