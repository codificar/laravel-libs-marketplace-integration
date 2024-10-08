const mix = require('laravel-mix');


/*
 |--------------------------------------------------------------------------
 | Mix Asset Management
 |--------------------------------------------------------------------------
 |
 | Mix provides a clean, fluent API for defining some Webpack build steps
 | for your Laravel application. By default, we are compiling the Sass
 | file for the application as well as bundling up all the JS files.
 |
 */

mix.js('src/resources/assets/js/marketplace-integration.js', 'public/js').vue();

mix.sass('src/resources/sass/app.scss', 'public/css');
 
mix.copy('src/resources/assets/img', 'public/img')
 .copy('public', '../../../public/vendor/codificar/marketplace-integration')
 .webpackConfig(require('./webpack.config'));

 mix.copyDirectory('public/js', '../../../public/vendor/codificar/marketplace-integration/js');
