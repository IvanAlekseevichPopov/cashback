FROM php:7.3.12-fpm

RUN apt-get update && apt-get install -y \
    git \
    libicu-dev \
    locales \
    locales-all \
    zlib1g-dev \
    libzip-dev

RUN docker-php-ext-install \
    bcmath \
    intl \
    opcache \
    pdo \
    pdo_mysql \
    zip

#Imagine support
RUN apt-get install -y \
        jpegoptim \
        libfreetype6-dev \
        libjpeg-dev \
        libpng-dev \
        libwebp-dev \
        libmcrypt-dev \
        optipng \
    && pecl install mcrypt-1.0.2 \
    && docker-php-ext-enable mcrypt \
    && docker-php-ext-install -j$(nproc) iconv \
    && docker-php-ext-configure gd --with-freetype-dir=/usr/include/ --with-png-dir=/usr/include/ --with-webp-dir=/usr/include/ --with-jpeg-dir=/usr/include/ \
    && docker-php-ext-install -j$(nproc) gd

COPY --from=composer:1.9 /usr/bin/composer /usr/bin/
RUN composer global require hirak/prestissimo

RUN usermod -s /bin/bash www-data

ENV PROMPT_COMMAND="history -a"
ENV HISTTIMEFORMAT="%h %d %H:%M:%S "
ENV HISTFILESIZE=20000
ENV LC_ALL en_US.UTF-8
ENV LANG en_US.UTF-8
ENV LANGUAGE en_US.UTF-8

ENV COMPOSER_MEMORY_LIMIT=-1
ENV COMPOSER_ALLOW_SUPERUSER 1
