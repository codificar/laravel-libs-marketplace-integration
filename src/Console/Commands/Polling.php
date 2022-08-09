<?php

namespace Codificar\MarketplaceIntegration\Console\Commands;

use Codificar\MarketplaceIntegration\Lib\MarketplaceFactory;
use Illuminate\Console\Command;

class Polling extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'marketplace:polling {marketplaces?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Get events polling';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        // just polling if has the proper configurations
        $this->polling();
        sleep(30);
        $this->polling();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function polling()
    {
        $marketplaces = MarketplaceFactory::$pollingMarketplaces;
        if ($this->argument('marketplaces')) {
            $marketplaces = explode(',', $this->argument('marketplaces'));
        }

        foreach ($marketplaces as $market) {
            $factory = MarketplaceFactory::create($market);
            $factory->newOrders();
        }
    }
}
