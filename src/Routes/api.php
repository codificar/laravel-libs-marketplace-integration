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

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::resource('/shop', 'ShopsController');
Route::get('/auth/userCode', 'Libs\IFoodApi@userCode');
Route::get('/auth/ifood', 'iFood\iFoodController@auth');
Route::get('/ifood/merchants', 'iFood\iFoodController@getMerchants');
Route::get('/ifood/events', 'iFood\iFoodController@getOrders');
Route::get('/orders/{id}', 'iFood\iFoodController@getOrdersDataBase');
Route::post('/order/ifood/events', 'iFood\iFoodController@confirmOrder');
Route::post('/order/{id}/confirm', 'iFood\iFoodController@confirmOrder');
Route::post('/rtc/order', 'iFood\iFoodController@rtcOrder');
