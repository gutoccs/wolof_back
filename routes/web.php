<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProblemController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function(){
    return view('index');
});

Route::get('/privacy-policies', function(){
    return view('privacy_policies');
});

Route::get('/terms-and-conditions', function(){
    return view('terms_and_conditions');
});

Route::get('/{any}', function() {
    return view('problem');
})->where('any', '.*');
