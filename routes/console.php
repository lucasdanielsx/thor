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
})->describe('Running commands');