<?php
namespace Codificar\MarketplaceIntegration;

use Codificar\MarketplaceIntegration\Console\Commands\Polling;
use Illuminate\Support\ServiceProvider;
use Illuminate\Console\Scheduling\Schedule;

class MarketplaceServiceProvider extends ServiceProvider {

    public function boot()
    {
        // Load routes (carrega as rotas)
        $this->loadRoutesFrom(__DIR__.'/Routes/api.php');
        $this->loadRoutesFrom(__DIR__.'/Routes/web.php');

        // // Load laravel views (Carregas as views do Laravel, blade)
        $this->loadViewsFrom(__DIR__.'/resources/views', 'marketplace-integration');

        // Load Migrations (Carrega todas as migrations)
        $this->loadMigrationsFrom(__DIR__.'/Database/Migrations');

        $this->commands([Polling::class]);

        $this->app->booted(function () {
            $schedule = app(Schedule::class);
            $schedule->command('events:polling')->everyMinute();
            sleep(30);
            $schedule->command('events:polling')->everyMinute();
        });

        // // Load trans files (Carrega tos arquivos de traducao) 
        $this->loadTranslationsFrom(__DIR__.'/resources/lang', 'marketplace-integration');

        // // Publish the VueJS files inside public folder of main project (Copia os arquivos do vue minificados dessa biblioteca para pasta public do projeto que instalar essa lib)
        $this->publishes([
            __DIR__.'/../public/js' => public_path('vendor/codificar/marketplace-integration'),
        ], 'public_vuejs_libs');

    }

    public function register()
    {

    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [
            'command.events.polling',
        ];
    }
}
?>
