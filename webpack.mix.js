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

 mix
 .js('src/resources/assets/js/app.js', 'public/js')
 .sass('src/resources/sass/app.scss', 'public/css')
 .vue()
 .copy('src/resources/assets/img', 'public/images')
 .copy('public', '../../../public/vendor/codificar/marketplace-integration')
 .webpackConfig(require('./webpack.config'));
