FROM php:5.6-fpm AS runtime

# Fix debian APT repos
RUN rm /etc/apt/sources.list
RUN echo "deb http://archive.debian.org/debian-security stretch/updates main" > /etc/apt/sources.list.d/stretch.list
RUN echo "deb http://archive.debian.org/debian stretch main" >> /etc/apt/sources.list.d/stretch.list

# Dependencies
RUN apt-get update
RUN apt-get upgrade -y --allow-unauthenticated
# PHP 5.6 links OpenSSL 1.0.2. Stretch's libssl-dev is 1.1 and its opaque
# structs break ext/openssl, so the rebuild needs libssl1.0-dev.
RUN apt-get install -y --allow-unauthenticated\
  wget \
  libfreetype6-dev \
  libjpeg62-turbo-dev \
  libmcrypt-dev \
  libpng-dev \
  libpq-dev \
  curl libcurl3-dev \
  zip libzip-dev \
  zlib1g-dev libicu-dev g++ \
  dpkg-dev libxml2-dev libssl1.0-dev libedit-dev libmhash-dev bison

# php2shell (CVE-2026-109618 / CVE-2026-109619) ------------------------------
# PHP 5.6 is EOL and gets no upstream fix. Rebuild the bundled 5.6.40 source
# with the community backport of the official "avoid bailout" mitigation
# (see config/php/patches/README.md). The configure line mirrors the one used
# by the official php:5.6-fpm image so the ABI matches the extensions
# installed below.
COPY ./config/php/patches/php-5.6-community-backport.patch /tmp/php2shell.patch
RUN set -eux; \
    gnuArch="$(dpkg-architecture -qDEB_BUILD_GNU_TYPE)"; \
    multiarch="$(dpkg-architecture -qDEB_HOST_MULTIARCH)"; \
    docker-php-source extract; \
    cd /usr/src/php; \
    patch -p1 --batch --forward < /tmp/php2shell.patch; \
    grep -q 'php2shell' ext/session/session.c; \
    CFLAGS="-fstack-protector-strong -fpic -fpie -O2" \
    CPPFLAGS="-fstack-protector-strong -fpic -fpie -O2" \
    LDFLAGS="-Wl,-O1 -Wl,--hash-style=both -pie" \
    ./configure \
        --build="$gnuArch" \
        --with-config-file-path=/usr/local/etc/php \
        --with-config-file-scan-dir=/usr/local/etc/php/conf.d \
        --enable-option-checking=fatal \
        --with-mhash \
        --enable-ftp \
        --enable-mbstring \
        --enable-mysqlnd \
        --with-curl \
        --with-libedit \
        --with-openssl \
        --with-zlib \
        --with-libdir="lib/$multiarch" \
        --enable-fpm \
        --with-fpm-user=www-data \
        --with-fpm-group=www-data \
        --disable-cgi; \
    make -j"$(nproc)"; \
    make install; \
    docker-php-source delete; \
    rm -f /tmp/php2shell.patch; \
    grep -q zend_mm_safe_error_ex /usr/local/include/php/Zend/zend_alloc.h; \
    php -v

# PHP extensions

RUN pecl channel-update pecl.php.net
RUN pecl install redis-4.3.0
RUN docker-php-ext-enable redis
RUN pecl install timezonedb
RUN docker-php-ext-enable timezonedb
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
