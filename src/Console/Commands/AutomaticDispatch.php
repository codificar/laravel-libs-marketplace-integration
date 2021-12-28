<?php

namespace Codificar\MarketplaceIntegration\Console\Commands;

use Codificar\MarketplaceIntegration\Repositories\DispatchRepository;
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
     * @return void
     */
    public function handle()
    {
        
        $shopOrders = [];
        $sentinelShopId = null ;

        $orders = DispatchRepository::getOrders();
        
        foreach ($orders as $order){
            
            if($order->shop_id != $sentinelShopId && ! is_null($sentinelShopId)) {

                $this->dispatch($shopOrders[$sentinelShopId]);

                $sentinelShopId = $order->shop_id; // changes sentinel

            }

            if(!array_key_exists($sentinelShopId)) $shopOrders[$sentinelShopId] = [];

            $shopOrders[$sentinelShopId][] = $order; 
        }
    }

    /**
     * Dispatch when changed the sentinel
     *
     * @return void
     */
    public function dispatch(array $shopOrderArray){

        // first rule - reach 3 or more orders
        if(count($shopOrderArray) > 2){
            // create the order
        }

        // second rule - reach time limit
        $timePassed = Carbon::now()->diffInMinutes($shopOrderArray[0]->updated_at);

        if($timePassed > DispatchRepository::getTimeLimit($shopOrderArray[0]->institution_id)){
            // create the order
        }

    }

}
