FROM php:7.1.13-fpm

ENV COMPOSER_ALLOW_SUPERUSER 1
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer; \
    chmod +x /usr/local/bin/composer

RUN usermod -s /bin/bash www-data; \
    touch /var/www/.bash_history; \
    chmod 777 /var/www/.bash_history

RUN apt-get update && apt-get install -y \
    git \
    libicu-dev \
    locales \
    locales-all \
    zlib1g-dev

RUN docker-php-ext-install \
    bcmath \
    intl \
    opcache \
    pdo \
    pdo_mysql \
    zip

ENV PROMPT_COMMAND="history -a"
ENV HISTTIMEFORMAT="%h %d %H:%M:%S "
ENV HISTFILESIZE=20000
ENV LC_ALL en_US.UTF-8
ENV LANG en_US.UTF-8
ENV LANGUAGE en_US.UTF-8