<?php

use Illuminate\Support\Facades\Artisan;

/*
|--------------------------------------------------------------------------
| Console Routes
|--------------------------------------------------------------------------
|
| This file is where you may define all of your Closure based console
| commands. Each Closure is bound to a command instance allowing a
| simple approach to interacting with each command's IO methods.
|
*/

Artisan::command('thor:init', function () {
    Artisan::call('migrate:refresh', []);
    Artisan::call('db:seed');
    Artisan::call('config:clear');
    Artisan::call('kafka:authorize_transaction');
    Artisan::call('kafka:transaction_authorized');
    Artisan::call('kafka:transaction_not_authorized');
    Artisan::call('kafka:transaction_notification');
})->describe('Running commands');