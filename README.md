Cashback Service project
==========

This is project of web application for users, who wants get cashbacks from various online stores and manage it.
[ ![Codeship Status for IvanAlekseevichPopov/cashback](https://app.codeship.com/projects/64b516c0-425c-0136-640e-6e467d7daecf/status?branch=master)](https://app.codeship.com/projects/291542)

Fast start
==========

* [Install docker-compose](https://docs.docker.com/compose/install/)
* Clone repository
* copy env file `cp .env.dis .env` and tune it for you
* `docker-compose build`
* `docker-compose up -d`
* `docker-compose exec php composer install`
* Done ) Go to [local server](http://localhost)

Run tests
=========
* `docker-compose exec --user=www-data php bash`
* `APP_ENV=test vendor/bin/phpunit`

More docks
==========

* console commands
* testing
* docker-compose commands
* deploy
