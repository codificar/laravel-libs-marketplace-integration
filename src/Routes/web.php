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

Route::group(array('namespace' => 'Codificar\MarketplaceIntegration\Http\Controllers', 'prefix' => '/corp', 'middleware' => ['auth.corp_admin']), function () {
    Route::get('/marketplace/integration', array('as' => 'corp', 'uses' => 'SinglePageController@index'));
});

// Route::group([
//     'domain' => config('app.url'), // don't call `env` outside of configs
//     'namespace' => 'Codificar\MarketplaceIntegration\Http\Controllers',
// ], function () {
//     $this->loadRoutesFrom(__DIR__.'/web.php');
// });
