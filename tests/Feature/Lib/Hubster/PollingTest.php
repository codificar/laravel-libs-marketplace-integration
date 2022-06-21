<?php

namespace Codificar\MarketplaceIntegration\Test\Feature\Lib\Hubster;


use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Session;
use Codificar\MarketplaceIntegration\Test\TestCase;
use Codificar\MarketplaceIntegration\Lib\HubsterLib;
use Codificar\MarketplaceIntegration\Lib\HubsterApi;


class PollingTest extends TestCase
{
    
    private $api ;

    public function setUp(): void
    {
        parent::setUp();

        $clientId          =  \Settings::findByKey('hubster_client_id');
        $clientSecret      =  \Settings::findByKey('hubster_client_secret');

        $this->api = new HubsterApi;

        $expiryToken  =  \Settings::findByKey('hubster_expiry_token');
        if (($clientId && $clientSecret) && ($expiryToken == NULL || Carbon::parse($expiryToken) < Carbon::now())) {
            $this->api->auth($clientId, $clientSecret);
        }
    }    

    /** @test */
    public function test_newOrders()
    {       
      // $lib = new HubsterLib();
      // $orderArray = $lib->newOrders();

    }


    public function test_apiNewOrders()
    {       
      $this->api->setStoreId('1234');
      $response = $this->api->newOrders();

    }

   
}
