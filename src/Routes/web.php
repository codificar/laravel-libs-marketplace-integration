<?php

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

require 'api.php';

Route::group(array('namespace' => 'Codificar\MarketplaceIntegration\Http\Controllers', 'prefix' => '/corp', 'middleware' => ['auth.corp_admin']), function () {
    Route::get('/marketplace/integration', array('as' => 'corp', 'uses' => 'SinglePageController@index'));
    Route::get('/marketplace/settings', array('as' => 'corp', 'uses' => 'SinglePageController@index'));
});

Route::group(array('namespace' => 'Codificar\MarketplaceIntegration\Http\Controllers', 'prefix' => '/admin', 'middleware' => ['auth.admin']), function () {
    Route::get('/settings/credentials', array('as' => 'admin', 'uses' => 'SinglePageController@index'));
});