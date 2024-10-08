<?php

namespace Codificar\MarketplaceIntegration;

use App\Events\RequestUpdate;
use Codificar\MarketplaceIntegration\Console\Commands\AutomaticDispatch;
use Codificar\MarketplaceIntegration\Console\Commands\Polling;
use Codificar\MarketplaceIntegration\Listeners\MarketplaceRideListener;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\ServiceProvider;

class MarketplaceServiceProvider extends ServiceProvider
{
    public function boot()
    {
        \Illuminate\Support\Facades\Event::listen(
            RequestUpdate::class,
            MarketplaceRideListener::class
        );

        // Load routes (carrega as rotas)
        $this->loadRoutesFrom(__DIR__ . '/Routes/api.php');
        $this->loadRoutesFrom(__DIR__ . '/Routes/web.php');

        // // Load laravel views (Carregas as views do Laravel, blade)
        $this->loadViewsFrom(__DIR__ . '/resources/views', 'marketplace-integration');

        // Load Migrations (Carrega todas as migrations)
        $this->loadMigrationsFrom(__DIR__ . '/Database/Migrations');

        // // Commands (Carrega os comandos do projeto)
        $this->commands(
            [
                Polling::class,
                AutomaticDispatch::class
            ]
        );

        // // Load trans files (Carrega tos arquivos de traducao)
        $this->loadTranslationsFrom(__DIR__ . '/resources/lang', 'marketplace-integration');

        // Load seeds
        $this->publishes([
            __DIR__ . '/Database/seeders' => database_path('seeders')
        ], 'public_vuejs_libs');

        // // Publish the VueJS files inside public folder of main project (Copia os arquivos do vue minificados dessa biblioteca para pasta public do projeto que instalar essa lib)
        $this->publishes([
            __DIR__ . '/../public/' => public_path('vendor/codificar/marketplace-integration/'),
        ], 'public_vuejs_libs');

        /**
         * Schedule package commands.
         */
        $this->app->booted(function () {
            $schedule = $this->app->make(Schedule::class);

            $schedule->command('marketplace:polling')->everyMinute()->withoutOverlapping();
            $schedule->command('marketplace:dispatch')->everyMinute()->withoutOverlapping();
        });
    }

    public function register()
    {
        //
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [
            'command.marketplace.polling',
            'command.marketplace.dispatch'
        ];
    }
}
