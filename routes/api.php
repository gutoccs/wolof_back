<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\CommerceController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\MerchantController;
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

    Route::post('/update-profile-image', [UserController::class, 'updateProfileImage'])->where('idUser', '\d+');
    Route::delete('/remove-profile-image', [UserController::class, 'removeProfileImage'])->where('idUser', '\d+');

    Route::group(['middleware' => 'checkTypeOfUser:employee'], function () {
        Route::get('/', [UserController::class, 'index']);
        Route::get('/{idUser}', [UserController::class, 'show'])->where('idUser', '\d+');
        Route::put('/{idUser}/flag-login', [UserController::class, 'flagLogin'])->where('idUser', '\d+');
    });

    Route::group(['middleware' => 'checkMinimumLevel:10'], function () {
        Route::delete('/{idUser}', [UserController::class, 'destroy'])->where('idUser', '\d+');
    });


});

Route::group([
    'prefix'    =>  'client'
], function() {

    Route::group(['middleware' => ['auth:api', 'checkIsSelfClientOrEmployee']], function () {
        Route::get('/{idPublicClient}', [ClientController::class, 'show'])->where('idPublicClient', '[A-Za-z0-9]+');
        Route::put('/{idPublicClient}', [ClientController::class, 'update'])->where('idPublicClient', '[A-Za-z0-9]+');
    });

    Route::group(['middleware' => ['auth:api', 'checkTypeOfUser:employee']], function () {
        Route::get('/', [ClientController::class, 'index']);
        Route::delete('/{idPublicClient}', [ClientController::class, 'destroy'])->where('idPublicClient', '[A-Za-z0-9]+');
    });

    Route::post('/', [ClientController::class, 'store']);

});


Route::group([
    'prefix'    =>  'employee',
    'middleware'    => [
        'auth:api'
    ]
], function() {

    Route::group(['middleware' => ['checkTypeOfUser:employee']], function () {
        Route::get('/', [EmployeeController::class, 'index']);
    });

    Route::group(['middleware' => ['checkIsSelfEmployeeOrLevel10']], function () {
        Route::get('/{idPublicEmployee}', [EmployeeController::class, 'show'])->where('idPublicEmployee', '[A-Za-z0-9]+');
        Route::put('/{idPublicEmployee}', [EmployeeController::class, 'update'])->where('idPublicEmployee', '[A-Za-z0-9]+');
    });

    Route::group(['middleware' => ['checkMinimumLevel:10']], function () {
        Route::post('/', [EmployeeController::class, 'store']);
        Route::put('/{idPublicEmployee}/change-role', [EmployeeController::class, 'changeRole'])->where('idPublicEmployee', '[A-Za-z0-9]+');
        Route::delete('/{idPublicEmployee}', [EmployeeController::class, 'destroy'])->where('idPublicEmployee', '[A-Za-z0-9]+');
    });

});


Route::group([
    'prefix'    =>  'merchant',
    'middleware'    => [
        'auth:api'
    ]
], function() {

    Route::group(['middleware' => ['checkIsEmployeeOrMerchant']], function () {
        Route::get('/', [MerchantController::class, 'index']);
        Route::get('/{idPublicMerchant}', [MerchantController::class, 'show'])->where('idPublicMerchant', '[A-Za-z0-9]+');
    });

    Route::group(['middleware' => ['checkIsEmployeeOrCommerceOwner']], function () {
        Route::post('/', [MerchantController::class, 'store']);
        Route::put('/{idPublicMerchant}/change-role', [MerchantController::class, 'changeRole'])->where('idPublicMerchant', '[A-Za-z0-9]+');
        Route::delete('/{idPublicMerchant}', [MerchantController::class, 'destroy'])->where('idPublicMerchant', '[A-Za-z0-9]+');
    });

    Route::group(['middleware' => ['checkIsSelfMerchantOrCommerceOwnerOrEmployee']], function () {
        Route::put('/{idPublicMerchant}', [MerchantController::class, 'update'])->where('idPublicMerchant', '[A-Za-z0-9]+');
    });

});


Route::group([
    'prefix'    =>  'commerce',
    'middleware'    => [
        'auth:api'
    ]
], function() {

    Route::get('/', [CommerceController::class, 'index']);
    Route::get('/{idPublicCommerce}', [CommerceController::class, 'show'])->where('idPublicCommerce', '[A-Za-z0-9]+');

    Route::group(['middleware' => ['checkIsEmployeeOrCommerceOwner']], function () {
        Route::put('/{idPublicCommerce}', [CommerceController::class, 'update'])->where('idPublicCommerce', '[A-Za-z0-9]+');
    });

    Route::group(['middleware' => ['checkTypeOfUser:employee']], function () {
        Route::post('/', [CommerceController::class, 'store']);
        Route::put('/{idPublicCommerce}/flag-active', [CommerceController::class, 'flagActive'])->where('idPublicCommerce', '[A-Za-z0-9]+');
    });

});
