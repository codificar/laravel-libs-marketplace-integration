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

Route::group(array('namespace' => 'Codificar\MarketplaceIntegration\Http\Controllers', 'prefix' => '/libs/marketplace-integration/shops', 'middleware' => ['auth.corp_admin']), function () {
    Route::get('/', 'ShopsController@index');
});

Route::group(array('namespace' => 'Codificar\MarketplaceIntegration\Http\Controllers', 'prefix' => '/libs/marketplace-integration/order', 'middleware' => ['auth.corp_admin']), function () {
    Route::post('/cancel', 'OrderDetailsController@cancel');
    Route::post('/dispatch', 'OrderDetailsController@dispatchOrder');
    Route::post('/confirm', 'OrderDetailsController@confirm');
    Route::post('/set-ride', 'OrderDetailsController@setRide');
});

Route::group(array('namespace' => 'Codificar\MarketplaceIntegration\Http\Controllers', 'prefix' => '/libs/marketplace-integration/orders', 'middleware' => ['auth.corp_admin']), function () {
    Route::post('/{shop_id?}', 'OrderDetailsController@getOrders');
});

Route::group(array('namespace' => 'Codificar\MarketplaceIntegration\Http\Controllers', 'prefix' => '/libs/marketplace-integration/shop', 'middleware' => ['auth.corp_admin']), function () {

    Route::post('/store', 'ShopsController@store');
    Route::delete('/delete/{shop_id}', 'ShopsController@delete');

});

Route::group(array('namespace' => 'Codificar\MarketplaceIntegration\Http\Controllers', 'prefix' => '/libs/marketplace-integration/market_config', 'middleware' => ['auth.corp_admin']), function () {

    Route::post('/store', 'MarketConfigController@store');
    Route::delete('/delete/{market_config_id}', 'MarketConfigController@delete');

});


Route::group(array('namespace' => 'Codificar\MarketplaceIntegration\Http\Controllers', 'prefix' => '/admin', 'middleware' => ['auth.admin']), function () {
    Route::post('/settings/credentials/save', array('as' => 'admin', 'uses' => 'SettingsController@storeIFoodCredentials'));
    Route::post('/settings/get/credentials', array('as' => 'admin', 'uses' => 'SettingsController@getIFoodCredentials'));
});


Route::group(['namespace' => 'Codificar\MarketplaceIntegration\Http\Controllers', 'prefix' => 'libs/marketplace-integration/'], function() {
    Route::get('/{market}/webhook', 'MarketplaceController@webhook');
    Route::post('/{market}/webhook', 'MarketplaceController@webhook');
    
    Route::get('/{market}/merchant-details/{merchant_id}', 'MarketplaceController@merchantDetails');
});
