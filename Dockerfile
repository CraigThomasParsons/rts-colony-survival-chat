FROM php:8.1-fpm

RUN apt-get update && apt-get install -y     git zip unzip libzip-dev libpng-dev libonig-dev libxml2-dev     && docker-php-ext-install pdo_mysql zip

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html
CMD ["php-fpm"]
