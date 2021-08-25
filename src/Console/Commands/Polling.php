<?php

namespace Codificar\MarketplaceIntegration\Console\Commands;

use Codificar\MarketplaceIntegration\Http\Controllers\DeliveryFactory;
use Codificar\MarketplaceIntegration\Models\MarketConfig;
use Codificar\MarketplaceIntegration\Models\Shops;
use Illuminate\Console\Command;
use Log;
use Carbon\Carbon;

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
        \Log::notice(__FUNCTION__);
        $stores = Shops::get();
        foreach ($stores as $key => $value) {
            $polling = new DeliveryFactory();
            if ($value->expiry_token == NULL || Carbon::parse($value->expiry_token) < Carbon::now()) {
                $polling->auth($value->id);
            }
            $res = $polling->getOrders($value->id);                   
            
            if ($res) {
                foreach ($res as $i => $v) {
                    $acknowledgment = $polling->getAcknowledgment($value->id, $v);
                    if ($res) {
                        $polling->getOrderDetails($value->id, $v->orderId);
                    }
                    
                } 
            }
        }
    }
}
