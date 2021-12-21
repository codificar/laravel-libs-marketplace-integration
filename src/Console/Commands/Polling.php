<?php

namespace Codificar\MarketplaceIntegration\Console\Commands;

use Illuminate\Console\Command;
use Log;
use Carbon\Carbon;
use Codificar\MarketplaceIntegration\Http\Repositories\OrdersRepository;
use Codificar\MarketplaceIntegration\Lib\IFoodApi;
use Codificar\MarketplaceIntegration\Lib\MarketplaceFactory;

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
    
        $factory = MarketplaceFactory::createMarketplace(MarketplaceFactory::IFOOD);
                
        $expiryToken  = \Settings::findByKey('ifood_expiry_token');
        if ($expiryToken == NULL || Carbon::parse($expiryToken) < Carbon::now()) {
            $factory->auth();
        }

        $res = $factory->getOrder();                   
        \Log::info('Res: '.is_object($res));
        if ($res) {
            foreach (json_decode($res,1) as $i => $v) {
                $acknowledgment = $factory->getAcknowledgment($v);
                if ($res) {
                    $orderDetail = $factory->getOrderDetails($v['orderId']);
                    $saved = OrdersRepository::updateOrder(json_decode($orderDetail));
                }
                
            }
        } 
        
    }
}
