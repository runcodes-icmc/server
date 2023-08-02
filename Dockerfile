FROM php:5.6-fpm AS runtime

# Fix debian APT repos
RUN rm /etc/apt/sources.list
RUN echo "deb http://archive.debian.org/debian-security stretch/updates main" > /etc/apt/sources.list.d/stretch.list
RUN echo "deb http://archive.debian.org/debian stretch main" >> /etc/apt/sources.list.d/stretch.list

# Dependencies
RUN apt-get update
RUN apt-get upgrade -y
RUN apt-get install -y \
  wget \
  libfreetype6-dev \
  libjpeg62-turbo-dev \
  libmcrypt-dev \
  libpng-dev \
  libpq-dev \
  curl libcurl3-dev \
  zip libzip-dev \
  zlib1g-dev libicu-dev g++

# PHP extensions

RUN pecl channel-update pecl.php.net
RUN pecl install redis-4.3.0
RUN docker-php-ext-enable redis
RUN docker-php-ext-configure zip --with-libzip
RUN docker-php-ext-install mcrypt pdo pdo_pgsql gd curl intl zip

# Update www-data user to UID 1000
RUN usermod -u 1000 www-data

# Create install dir
RUN mkdir /var/www/runcodes/
RUN chown www-data:www-data /var/www/runcodes
WORKDIR /var/www/runcodes/

# Change user to www-data
USER www-data

# PHP-FPM configs
ADD ./config/php/php.ini /etc/php5/fpm/conf.d/
ADD ./config/php/php.ini /etc/php5/cli/conf.d/
ADD ./config/php/fpm-www.conf /etc/php5/fpm/pool.d/
ADD ./config/php/fpm-settings.conf /usr/local/etc/php-fpm.d/zzz-phpSettings.conf

FROM runtime AS build

# Install Composer
COPY --from=composer/composer:2.2-bin /composer /usr/bin/composer

# Load composer configs & packages
COPY --chown=www-data:www-data ./src/composer.json ./composer.json
COPY --chown=www-data:www-data ./src/composer.lock ./composer.lock
COPY --chown=www-data:www-data ./src/packages/ ./packages/

# Install deps
RUN composer install
RUN chown www-data:www-data ./vendor/

FROM build AS dist

# Load source & configs
COPY --chown=www-data:www-data ./src/app/ ./app/

# Go back to root
USER root
