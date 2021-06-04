<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\UserController;


Route::prefix('auth')->group(function () {
    Route::post('login', [AuthController::class, 'login']);
    //Route::post('forgot-password', 'ForgotPasswordController');
    //Route::post('reset-password', 'ResetPasswordController@reset');
    Route::get('refresh', [AuthController::class, 'refresh']);
    Route::group(['middleware' => 'auth:api'], function(){
        Route::get('user', [AuthController::class, 'user']);
        Route::post('logout', [AuthController::class, 'logout']);
        Route::get('me', [AuthController::class, 'me']);//->middleware('isLevelSevenOrMore');
        Route::get('payload', [AuthController::class, 'payload']);//->middleware('isLevelSevenOrMore');
    });
});

Route::group([
    'prefix'        =>  'user',
    'middleware'    => [
        'auth:api'
    ],
], function() {

    //Route::group(['middleware' => 'checkTypeOfUser:employee'], function () {
        Route::get('/', [UserController::class, 'index']);
        Route::get('/{idUser}', [UserController::class, 'show'])->where('idUser', '\d+');
    //});

    Route::group(['middleware' => 'checkMinimumLevel:10'], function () {
        Route::delete('/{idUser}', [UserController::class, 'destroy'])->where('idUser', '\d+');
    });

});

Route::group([
    'prefix'    =>  'client'
], function() {

    Route::group(['middleware' => ['auth:api', 'checkIsSelfClientOrEmployee']], function () {
        Route::get('/{idClient}', [ClientController::class, 'show'])->where('idClient', '\d+');
        Route::put('/{idClient}', [ClientController::class, 'update'])->where('idClient', '\d+');
    });

    //Route::group(['middleware' => ['auth:api', 'checkTypeOfUser:employee']], function () {
        Route::get('/', [ClientController::class, 'index']);
    //});

    Route::post('/', [ClientController::class, 'store']);

});
