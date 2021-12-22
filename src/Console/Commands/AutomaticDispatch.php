<?php

namespace Codificar\MarketplaceIntegration\Console\Commands;

use Codificar\MarketplaceIntegration\Http\Controllers\IFoodController;
use Codificar\MarketplaceIntegration\Models\MarketConfig;
use Codificar\MarketplaceIntegration\Models\Shops;
use Illuminate\Console\Command;
use Log;
use Carbon\Carbon;

class AutomaticDispatch extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'marketplace:dispatch';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Automatic dispatch rides for polling orders with status DSP';

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
       
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function polling()
    {
    
    }
}
