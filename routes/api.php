<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\CodeValidationController;
use App\Http\Controllers\CommerceController;
use App\Http\Controllers\DeveloperController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\ForgotPasswordController;
use App\Http\Controllers\MerchantController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\ResetPasswordController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ValidateAccountController;


Route::prefix('auth')->group(function () {
    Route::post('login', [AuthController::class, 'login']);
    Route::post('login-cellphone-number', [AuthController::class, 'loginCellphoneNumber']);
    Route::post('forgot-password', ForgotPasswordController::class);
    Route::get('code-validation', CodeValidationController::class);
    Route::put('reset-password', [ResetPasswordController::class, 'reset']);
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

    Route::post('/update-profile-image', [UserController::class, 'updateProfileImage']);
    Route::delete('/remove-profile-image', [UserController::class, 'removeProfileImage']);
    Route::post('/start-validation', [ValidateAccountController::class, 'startValidation']);
    Route::put('/check-validation', [ValidateAccountController::class, 'checkValidation']);
    Route::get('/account-setting', [UserController::class, 'accountSetting']);

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
    Route::get('/{idPublicCommerce}/contact', [CommerceController::class, 'showContact'])->where('idPublicCommerce', '[A-Za-z0-9]+');
    Route::get('/account-setting', [CommerceController::class, 'accountSetting']);

    Route::group(['middleware' => ['checkIsEmployeeOrCommerceOwner']], function () {
        Route::put('/{idPublicCommerce}', [CommerceController::class, 'update'])->where('idPublicCommerce', '[A-Za-z0-9]+');
        Route::post('/update-profile-image', [CommerceController::class, 'updateProfileImage']);
        Route::delete('/remove-profile-image', [CommerceController::class, 'removeProfileImage']);
        Route::put('/{idPublicCommerce}/contact', [CommerceController::class, 'updateContact'])->where('idPublicCommerce', '[A-Za-z0-9]+');
    });

    Route::group(['middleware' => ['checkTypeOfUser:employee']], function () {
        Route::post('/', [CommerceController::class, 'store']);
        Route::put('/{idPublicCommerce}/flag-active', [CommerceController::class, 'flagActive'])->where('idPublicCommerce', '[A-Za-z0-9]+');
    });

});

Route::group([
    'prefix'    =>  'product',
    'middleware'    => [
        'auth:api'
    ]
], function() {

    Route::get('/', [ProductController::class, 'index']);
    Route::get('/{idProduct}', [ProductController::class, 'show'])->where('idProduct', '\d+');

    Route::group(['middleware' => ['checkIsEmployeeOrMerchant']], function () {
        Route::post('/', [ProductController::class, 'store']);
        Route::put('/{idProduct}', [ProductController::class, 'update'])->where('idProduct', '\d+');
        //Route::delete('{idProduct}', [ProductController::class, 'destroy'])->where('idProduct', '\d+');
        Route::post('/{idProduct}/update-image', [ProductController::class, 'updateImage'])->where('idProduct', '\d+');
    });

});

Route::group([
    'prefix'    =>  'purchase',
    'middleware'    => [
        'auth:api'
    ]
], function() {

    Route::get('/', [PurchaseController::class, 'index']);
    Route::get('/{idPurchase}', [PurchaseController::class, 'show'])->where('idPurchase', '\d+');
    Route::put('/{idPurchase}/cancel', [PurchaseController::class, 'cancelPurchase'])->where('idPurchase', '\d+');
    Route::put('/{idPurchase}/completed', [PurchaseController::class, 'changeToCompleted'])->where('idPurchase', '\d+');

    Route::group(['middleware' => ['checkTypeOfUser:client']], function () {
        Route::post('/', [PurchaseController::class, 'store']);
    });

    Route::group(['middleware' => ['checkTypeOfUser:employee']], function () {
        Route::put('/{idPurchase}/clean', [PurchaseController::class, 'cleanPurchase'])->where('idPurchase', '\d+');
    });

});

Route::get('/developer', DeveloperController::class);
