#!/bin/bash

php artisan migrate:refresh
php artisan db:seed

php artisan config:cache
php artisan config:clear

#Start kafka consumers
php artisan kafka:authorize_transaction &
php artisan kafka:transaction_authorized &
php artisan kafka:transaction_not_authorized &
php artisan kafka:transaction_notification &

php-fpm