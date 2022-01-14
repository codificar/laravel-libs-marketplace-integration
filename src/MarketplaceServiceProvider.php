<?php
namespace Codificar\MarketplaceIntegration;

use Codificar\MarketplaceIntegration\Console\Commands\Polling;
use Codificar\MarketplaceIntegration\Console\Commands\AutomaticDispatch;
use Codificar\MarketplaceIntegration\Listeners\RequestUpdateListener;
use App\Events\RequestUpdate;
use Illuminate\Support\ServiceProvider;

class MarketplaceServiceProvider extends ServiceProvider
{

    public function boot()
    {

        \Illuminate\Support\Facades\Event::listen(
            RequestUpdate::class,
            RequestUpdateListener::class
        );

        // Load routes (carrega as rotas)
        $this->loadRoutesFrom(__DIR__.'/Routes/api.php');
        $this->loadRoutesFrom(__DIR__.'/Routes/web.php');

        // // Load laravel views (Carregas as views do Laravel, blade)
        $this->loadViewsFrom(__DIR__.'/resources/views', 'marketplace-integration');

        // Load Migrations (Carrega todas as migrations)
        $this->loadMigrationsFrom(__DIR__.'/Database/Migrations');

        // // Commands (Carrega os comandos do projeto)
        $this->commands(
            [
                Polling::class,
                AutomaticDispatch::class
            ]
        );

        // // Load trans files (Carrega tos arquivos de traducao) 
        $this->loadTranslationsFrom(__DIR__.'/resources/lang', 'marketplace-integration');

        // Load seeds
        $this->publishes([
            __DIR__.'/Database/Seeds' => database_path('seeds')
        ], 'public_vuejs_libs');


        // // Publish the VueJS files inside public folder of main project (Copia os arquivos do vue minificados dessa biblioteca para pasta public do projeto que instalar essa lib)
        $this->publishes([
            __DIR__.'/../public/' => public_path('vendor/codificar/marketplace-integration/'),
        ], 'public_vuejs_libs');

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
?>
