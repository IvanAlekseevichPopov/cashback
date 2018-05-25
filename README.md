Cashback Service project
==========

This is project of web application for users, who wants get cashbacks from various online stores and manage it.

Fast start
==========

* [Install docker-compose](https://docs.docker.com/compose/install/)
* Clone repository
* copy env file `cp .env.dis .env` and tune it for you
* `docker-compose build`
* `docker-compose up -d`
* `docker-compose exec php composer install`
* set `127.0.0.1 cb.tk` to hosts
* Done ) Go to [local server](http://localhost)

Run tests
=========
* `docker-compose exec php bash`
* `APP_ENV=test bin/phpunit`

More docks
==========

* console commands
* testing
* docker-compose commands
* deploy
