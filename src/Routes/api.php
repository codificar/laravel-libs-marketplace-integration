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
    Route::post('/market/store', 'ShopsController@storeMarketConfig');
    Route::put('/market/update', 'ShopsController@updateMarketConfig');
    Route::post('/shop/status', 'ShopsController@status');
    Route::get('/auth/ifood', 'IFoodController@auth');
    Route::get('/ifood/merchants', 'IFoodController@getMerchants');
    Route::get('/ifood/events', 'IFoodController@getOrders');
    Route::get('/orders/{id}', 'IFoodController@getOrdersDataBase');
    Route::post('/order/cancel', 'IFoodController@cancelOrder');
    Route::post('/order/readyToPickup', 'IFoodController@rtpOrder');
    Route::post('/order/{id}/confirm', 'IFoodController@confirmOrder');
    Route::post('/order/update', 'IFoodController@updateOrderRequest');
    Route::post('/rtc/order', 'IFoodController@rtcOrder');
    Route::post('/merchant/details', 'IFoodController@getMerchantDetails');
});

