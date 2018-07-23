#!/bin/sh

/usr/sbin/usermod -u "$PARENT_USER_ID" www-data
mkdir -p /var/www/.composer
chmod 777 /var/www/.composer

php-fpm
