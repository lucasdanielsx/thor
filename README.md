<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400"></a></p>

<p align="center">
<a href="https://travis-ci.org/laravel/framework"><img src="https://travis-ci.org/laravel/framework.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

### THOR

## Requirements
- Docker ^20.10.14
- Docker compose ^1.26.0
- PHP ^8.1
- Composer ^2.3.5

## How to run

- sudo +x docker-entrypoint.sh
- docker-compose up --build

## About
By default the system creates 2 users:
- User 1 (document = 94271368040, type = CUSTOMER, wallet balance = 1000000)
- User 2 (document = 41297905000152, type = STORE, wallet balance = 1000000)

## Curl's

- To create a new transaction
`curl --location --request POST 'http://localhost/api/v1/transactions' \
--header 'Accept: application/json' \
--header 'Content-Type: application/json' \
--data-raw '{
    "value": 10000,
    "payer": "94271368040",
    "payee": "41297905000152"
}'`

- To find a transaction (you need to change the {{id}} parameter for the id returned at create a new transaction endpoint)
`curl --location --request GET 'http://localhost/api/v1/transactions/{{id}}' \
--header 'Accept: application/json' \
--data-raw ''`

- To find a wallet (you need to change the {{document}} parameter for the document of user)
curl --location --request GET 'http://localhost/api/v1/wallets/{{document}}' \
--header 'Accept: application/json' \
--data-raw ''