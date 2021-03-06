<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\WalletController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::post('/v1/transactions', [TransactionController::class, 'create']);
Route::get('/v1/transactions/{id}', [TransactionController::class, 'findById']);

Route::get('/v1/wallets/{document}', [WalletController::class, 'findByUserDocument']);