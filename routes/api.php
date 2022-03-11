<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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

Route::group([
    'middleware' => ['api', 'cors'],
], function () {
    Route::get('prueba', 'front\UserController@prueba');
    Route::get('view-mail', 'front\UserController@viewMail');
    Route::get('prueba-mail', 'front\UserController@pruebaMail');

    
    // AUTH
    Route::post('recover-password-request', 'front\UserController@recover_password_request');
    Route::post('login', 'front\UserController@login');
    Route::post('register', 'front\UserController@register');
    Route::get('confirmar-correo/{email}/{token}', 'front\UserController@confirm_mail');
    Route::post('resend-email-confirm/{email}', 'front\UserController@resend_email_confirm');
    Route::post('recover-password-verify', 'front\UserController@recover_password_verify');
    Route::post('recover-password', 'front\UserController@recover_password');

    Route::group([
        'middleware' => ['auth:api'],
    ], function () {
        Route::post('get-auth', 'front\UserController@getAuth');
    });

    
});
