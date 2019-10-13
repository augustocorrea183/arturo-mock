### Base ###
FROM php:7.3-apache

### Entorno ###
ARG DEPLOY_VERSION

### Dependencies ###
RUN apt-get update \
&& apt-get install -y curl unzip wget tar sudo nano pkg-config

### Mongodb ###
RUN pecl install mongodb
RUN docker-php-ext-enable mongodb

### Apache ###
RUN a2enmod rewrite

### Composer ###
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/bin/ --filename=composer

WORKDIR /var/www/html

COPY src/composer.json .
RUN composer install

COPY src/ .
COPY docker/virtual-host.conf /etc/apache2/sites-enabled/000-default.conf

### Permissions ###
RUN chown www-data:www-data -R  /var/www/html
COPY docker/run.sh /
COPY docker/apache2-foreground /usr/local/bin/

EXPOSE 80

CMD /run.sh
