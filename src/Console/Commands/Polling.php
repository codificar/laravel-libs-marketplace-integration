<?php

namespace Codificar\MarketplaceIntegration\Console\Commands;

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
    
        // $factory = new IFoodController();
        
        
        // $expiryToken  = \Settings::findByKey('ifood_expiry_token');
        // if ($expiryToken == NULL || Carbon::parse($expiryToken) < Carbon::now()) {
        //     $factory->auth();
        // }

        // $res = $factory->getOrders();                   
        
        if ($res) {
            foreach ($res as $i => $v) {
                // $acknowledgment = $factory->getAcknowledgment($v);
                if ($res) {
                    // $factory->getOrderDetails($v->orderId);
                }
                
            }
        } 
        
    }
}
