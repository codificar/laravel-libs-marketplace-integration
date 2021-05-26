<?php

use Illuminate\Http\Request;

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


Route::group(array('namespace' => 'Codificar\MarketplaceIntegration\Http\Controllers', 'prefix' => '/corp/api/', 'middleware' => ['auth.corp_admin']), function () {
    Route::resource('/shop', 'ShopsController');
    Route::get('/auth/ifood', 'iFoodController@auth');
    Route::get('/ifood/merchants', 'iFoodController@getMerchants');
    Route::get('/ifood/events', 'iFoodController@getOrders');
    Route::get('/orders/{id}', 'iFoodController@getOrdersDataBase');
    Route::post('/order/ifood/events', 'iFoodController@confirmOrder');
    Route::post('/order/{id}/confirm', 'iFoodController@confirmOrder');
    Route::post('/rtc/order', 'iFoodController@rtcOrder');
});
