# Cryptfolio API

Crypto API to manage user assets and see their values.

# Server setup instructions

Generate .env file	
> cp .env.example .env

Build and run Docker environment
> docker-compose up --build

Connect to the docker container to execute following commands
> docker-compose exec app bash

Generate database schema inside docker container
> php artisan migrate

Run test users seed to the database
Check users table for available users list, they share the same password which is `aB123Cd`
> php artisan db:seed

Check unit tests
> vendor/bin/phpunit

# Postman resources

```
Inside Postman directory you will found Postman collection of all endpoints to test API calls.
```
