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

    Route::post('plans', 'admin\PlanController@list');
   

    Route::group([
        'middleware' => ['auth:api'],
    ], function () {
        Route::post('get-auth', 'front\UserController@getAuth');
        Route::post('bank-accounts', 'admin\BankAccountController@list');

        Route::group([
            'middleware' => ['admin'],'prefix'=>'admin'
        ], function () {

            // plan admin
            Route::post('plans', 'admin\PlanController@listAdmin');
            Route::post('plan-store', 'admin\PlanController@store');
            Route::post('plan-update', 'admin\PlanController@update');
            Route::post('plan-delete/{id}', 'admin\PlanController@delete');
            Route::post('update-license', 'admin\PlanController@update_license');
             // bancos
             Route::post('bank-accounts', 'admin\BankAccountController@list');
             Route::post('bank-account/{id}', 'admin\BankAccountController@get');
             Route::post('bank-account-store', 'admin\BankAccountController@store');
             Route::post('bank-account-update', 'admin\BankAccountController@update');
             Route::post('bank-account-delete/{id}', 'admin\BankAccountController@delete');

              // Carteras
              Route::post('wallets', 'admin\WalletController@list');
              Route::post('wallet/{id}', 'admin\WalletController@get');
              Route::post('wallet-store', 'admin\WalletController@store');
              Route::post('wallet-update', 'admin\WalletController@update');
              Route::post('wallet-delete/{id}', 'admin\WalletController@delete');

              //  USER PLAN ADMIN
              Route::post('list-user-plan', 'front\UserPlanController@list');
              Route::post('get-plan-user-admin', 'front\UserPlanController@getPlanAdmin');
              Route::post('activate-plan', 'front\UserPlanController@activatePlan');
              Route::post('reject-plan', 'front\UserPlanController@rejectPlan');
              
              
        });

        Route::group([
            'middleware' => ['cliente'],
        ], function () {
            //  USER PLAN FRONT
            // Route::post('balance', 'front\BalancePlanController@get');

            Route::post('my-plan', 'front\UserPlanController@my_plan');
            
            Route::post('activate-plan/{id}', 'front\UserPlanController@activate_plan');
            Route::post('update-data-personal', 'front\UserController@update_data_personal');
            Route::post('update-data-contact', 'front\UserController@update_data_contact');
            Route::post('get-accounts-payment', 'front\UserPlanController@get_accounts_payment');
            Route::post('get-plan-user', 'front\UserPlanController@get');
            Route::post('insert-amount-plan', 'front\UserPlanController@insertAmount');
            Route::post('insert-accounts-payment', 'front\UserPlanController@insertAccountsPayment');
            Route::post('images-pay-plan', 'front\UserPlanController@getImages');

            Route::post('upload-file-userplan', 'front\UserPlanController@upload_file');
            Route::post('delete-file-userplan', 'front\UserPlanController@delete_file');
            
            Route::post('request-activation-userplan', 'front\UserPlanController@request_activation');
            Route::post('plan-under-review', 'front\UserPlanController@plan_under_review');
            
            Route::group([
                'middleware' => ['plan'],
            ], function () {
                
            });

           
            
        });
    });

  
    
});
