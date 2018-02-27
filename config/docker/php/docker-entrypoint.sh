#!/bin/sh

/usr/sbin/usermod -u "$PARENT_USER_ID" www-data
mkdir -p /var/www/.composer
chmod 777 /var/www/.composer

#if [ "$APP_ENV" = 'prod' ]; then
#    composer install --prefer-dist --no-dev --no-progress --no-suggest --optimize-autoloader --classmap-authoritative --no-interaction
#else
#    composer install --prefer-dist --no-progress --no-suggest --no-interaction
#fi

php-fpm
