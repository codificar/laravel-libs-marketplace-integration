<?php

namespace Codificar\MarketplaceIntegration;

use Codificar\MarketplaceIntegration\Events\OrderUpdate;
use Illuminate\Support\ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        OrderUpdate::class => [
        ]
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();
    }
}