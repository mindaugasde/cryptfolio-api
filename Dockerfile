FROM php:7.3.14-apache

# dependencies and php modules
RUN apt-get update && apt-get install -y \
    libzip-dev \
    git \
    zip \
    unzip \
    && rm -r /var/lib/apt/lists/* \
    && docker-php-ext-configure pdo_mysql --with-pdo-mysql=mysqlnd \
    && docker-php-ext-install \
    pdo_mysql \
    zip \
    opcache

# composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/bin/ --filename=composer

# application folder as environment variable
ENV APP_HOME /var/www/html

# web_root as lumen public folder
RUN sed -i -e "s/html/html\/public/g" /etc/apache2/sites-enabled/000-default.conf

# enable apache module rewrite
RUN a2enmod rewrite

# composer run
COPY ./composer.* $APP_HOME/
WORKDIR $APP_HOME
RUN composer install --no-interaction --no-autoloader

# source code movement
COPY . $APP_HOME

# composer dump
RUN composer dump-autoload --no-interaction --optimize

# change ownership of our application
RUN chown -R www-data:www-data /var/www/html/storage/

# generate .env file
RUN cp .env.example .env

# generate app key
RUN php artisan key:generate

# generate jwt secret
RUN php artisan jwt:secret --force
