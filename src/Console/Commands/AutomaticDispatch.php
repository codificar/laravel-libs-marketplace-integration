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
        // if not enabled return false
        if(!\Settings::findByKey('automatic_dispatch_enabled')) {
            $this->info("Automatic dispatch is disabled");
            return ;
        }
        
        $shopOrders = $return = [];

        $orders = DispatchRepository::getOrders();

        $sentinelShopId = (count($orders) ? $orders[0]->shop_id : null ) ;

        $this->info(sprintf("Count orders: %s", count($orders)));
        
        foreach ($orders as $index => $order){

            $this->info(sprintf("shop_id: %s - order_id: %s", $order->shop_id, $order->id));

            // create empty array
            if(!array_key_exists($sentinelShopId, $shopOrders)) $shopOrders[$sentinelShopId] = [];

            $shopOrders[$sentinelShopId][] = $order; 

            // changes sentinel
            $newSentinelShopId = (isset($orders[$index+1]) ? $orders[$index+1]->shop_id : null); 
            //$this->info(sprintf("sentinelShopId: %s - newSentinelShopId: %s", $sentinelShopId, $newSentinelShopId));

            if($sentinelShopId != $newSentinelShopId) {
                $this->info(sprintf("Dispatching shop_id: %s - count orders: %s", $sentinelShopId, count($shopOrders[$sentinelShopId])));
                $return [] = $this->dispatch($shopOrders[$sentinelShopId]);
                $sentinelShopId = $newSentinelShopId;
            }

        }

        //$this->info(print_r($return,1));
    }

    /**
     * Dispatch when changed the sentinel
     *
     * @return void
     */
    public function dispatch(array $shopOrderArray){
        $return = [];
        // rule variables
        $timeLimit = DispatchRepository::getTimeLimit($shopOrderArray[0]->institution_id);

        foreach(array_chunk($shopOrderArray, DispatchRepository::SIZE_LIMIT) as $orderArray){
            // first rule - reach 3 orders
            if(count($orderArray) == DispatchRepository::SIZE_LIMIT){
                $this->info(sprintf("ShopId: %s - First Rule Count: %s", $orderArray[0]->shop_id, count($orderArray)));
                // create the order
                $return [] = DispatchRepository::createRide($orderArray);
                continue ;
            }

            $timePassed = Carbon::now()->diffInMinutes($orderArray[0]->updated_at);
            // second rule - reach time limit
            if($timePassed > $timeLimit){
                $this->info(sprintf("ShopId: %s - Second Rule Time: %s", $orderArray[0]->shop_id, $timePassed));
                // create the order
                $return [] = DispatchRepository::createRide($orderArray);
                continue;
            }
            
        }

        return $return ;

    }

}
