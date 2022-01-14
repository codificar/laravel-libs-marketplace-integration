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
    Route::get('/marketplace-integration/credentials', array('as' => 'admin', 'uses' => 'SinglePageController@index'));
});

/**
 * Rota para permitir utilizar arquivos de traducao do laravel (dessa lib) no vue js
 */
Route::get('/marketplace-integration/lang.trans/{files}', function ($files) {
    \Debugbar::disable();
    $fileNames = explode(',', $files);
    $lang = config('app.locale');
    $files = array();
    foreach ($fileNames as $fileName) {
        array_push($files, __DIR__ . '/../resources/lang/' . $lang . '/' . $fileName . '.php');
    }
    $strings = [];
    foreach ($files as $file) {
        $name = basename($file, '.php');
        $strings[$name] = require $file;
    }

    header('Content-Type: text/javascript');
    return ('window.lang = ' . json_encode($strings) . ';');
    exit();
})->name('assets.lang');
