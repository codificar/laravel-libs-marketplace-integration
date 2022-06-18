<?php

namespace Codificar\MarketplaceIntegration\Lib;

use Codificar\MarketplaceIntegration\Models\MarketConfig;
use Codificar\MarketplaceIntegration\Lib\HubsterApi;
use Codificar\MarketplaceIntegration\Lib\MarketplaceFactory;
use Codificar\MarketplaceIntegration\Models\DeliveryAddress;
use Codificar\MarketplaceIntegration\Models\OrderDetails;
use Codificar\MarketplaceIntegration\Models\Shops;
use Carbon\Carbon;
//use App\Models\LibSettings;

class HubsterLib
{

    private $api ;

    public function __construct(){
        #TODO ter settings proprias ao inves de usar a do projeto pai
        $clientId          =  \Settings::findByKey('hubster_client_id');
        $clientSecret      =  \Settings::findByKey('hubster_client_secret');

        $this->api = new HubsterApi;

        $expiryToken  =  \Settings::findByKey('hubster_expiry_token');
        if (($clientId && $clientSecret) && ($expiryToken == NULL || Carbon::parse($expiryToken) < Carbon::now())) {
            $this->api->auth($clientId, $clientSecret);
        }
    }
    
    public function newOrders()
    {
       
    }

    public function orderDetails($orderId)
    {

    }

    public function dispatch($orderId){
        return $this->api->dispatch($orderId);
    }

    public function webhook($request)
    {
        $json = $request->json()->all();
        \Log::debug($json);
        return $json;
    }
}