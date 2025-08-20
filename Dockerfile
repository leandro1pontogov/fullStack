FROM php:5.6-apache

RUN sed -i 's/deb.debian.org/archive.debian.org/g' /etc/apt/sources.list && \
  sed -i 's/security.debian.org/archive.debian.org/g' /etc/apt/sources.list && \
  sed -i '/stretch-updates/d' /etc/apt/sources.list && \
  apt-get update && \
  apt-get install -y --allow-unauthenticated libpq-dev libssl-dev unzip wget git autoconf build-essential && \
  docker-php-ext-install pdo pdo_pgsql pgsql

RUN git clone https://github.com/xdebug/xdebug.git /usr/src/xdebug && \
  cd /usr/src/xdebug && \
  git checkout XDEBUG_2_5_5 && \
  phpize && \
  ./configure && \
  make && make install

RUN echo "zend_extension=/usr/local/lib/php/extensions/no-debug-non-zts-20131226/xdebug.so" > /usr/local/etc/php/conf.d/xdebug.ini

COPY . /var/www/html/

EXPOSE 80