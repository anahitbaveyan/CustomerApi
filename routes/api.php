<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\AccountController;
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


Route::apiResource('customers', CustomerController::class);



Route::prefix('accounts')->group(function () {
    
    Route::get('{id}', [AccountController::class, 'getBalance']);
    Route::post('{id}/deposit', [AccountController::class, 'deposit']);
    Route::post('{id}/withdraw', [AccountController::class, 'withdraw']);
    Route::post('transfer', [AccountController::class, 'transfer']);
    Route::get('{id}/rebuilt-balance', [AccountController::class, 'getRebuiltBalance']);
    Route::get('{id}/audit', [AccountController::class, 'getTransactionAudit']);
});


