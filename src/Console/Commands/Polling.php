<?php

namespace Codificar\MarketplaceIntegration\Console\Commands;

use Codificar\MarketplaceIntegration\Http\Controllers\IFoodController;
use Codificar\MarketplaceIntegration\Models\MarketConfig;
use Codificar\MarketplaceIntegration\Models\Shops;
use Illuminate\Console\Command;
use Log;

class Polling extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'events:polling';

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
        $stores = MarketConfig::get();
        foreach ($stores as $key => $value) {
            $shop = Shops::find($value->shop_id);
            \Log::debug('Shop'.print_r($shop,1)); 
            $polling = new IFoodController();
            $res = $polling->getOrders($value->shop_id);
            
            \Log::debug('Ta rodando'.print_r($res,1));
            if ($res) {
                foreach ($res as $i => $v) {
                    \Log::debug('v: '.print_r($v,1));
                    $acknowledgment = $polling->getAcknowledgment($value->shop_id, $v);
                
                    if ($acknowledgment) {
                        $polling->getOrderDetails($value->id, $value->orderId);
                    }
                    
                } 
            }
        }
    }
}
