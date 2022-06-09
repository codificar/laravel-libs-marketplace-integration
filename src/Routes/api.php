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
    Route::post('/market/delete', 'ShopsController@deleteMarketConfig');
    Route::put('/market/update', 'ShopsController@updateMarketConfig');
    Route::post('/shop/status', 'ShopsController@status');
    Route::get('/auth/ifood', 'DeliveryFactory@auth');
    // Route::get('/ifood/merchants', 'DeliveryFactory@getMerchants');
    Route::get('/ifood/events', 'DeliveryFactory@getOrders');
    Route::post('/orders/{id}', 'DeliveryFactory@getOrdersDataBase');
    Route::post('/order/cancel', 'DeliveryFactory@cancelOrder');
    Route::post('/order/readyToPickup', 'DeliveryFactory@dspOrder');
    Route::post('/order/{id}/confirm', 'DeliveryFactory@confirmOrder');
    Route::post('/order/update', 'DeliveryFactory@updateOrderRequest');
    Route::post('/rtc/order', 'DeliveryFactory@rtcOrder');
    // Route::post('/merchant/details', 'DeliveryFactory@getMerchantDetails');
});

Route::group(array('namespace' => 'Codificar\MarketplaceIntegration\Http\Controllers', 'prefix' => '/admin', 'middleware' => ['auth.admin']), function () {
    Route::post('/settings/credentials/save', array('as' => 'admin', 'uses' => 'ShopsController@iFoodCredentials'));
    Route::post('/settings/get/credentials', array('as' => 'admin', 'uses' => 'ShopsController@getIfoodCredentials'));
});


Route::group(['namespace' => 'Codificar\MarketplaceIntegration\Http\Controllers', 'prefix' => 'libs/marketplace-integration/'], function() {
    Route::get('/{market}/webhook', 'ShopsController@handleWebhook');
    Route::post('/{market}/webhook', 'ShopsController@handleWebhook');
});
