<?php

namespace Codificar\MarketplaceIntegration;

use Codificar\MarketplaceIntegration\Events\OrderUpdate;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Event;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        RequestUpdate::class => [
        ]
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        // parent::boot();
        Event::listen(function (PodcastProcessed $event) {
            //
        });
    }
}