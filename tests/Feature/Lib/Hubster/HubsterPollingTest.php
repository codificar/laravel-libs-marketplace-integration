<?php

namespace Codificar\MarketplaceIntegration\Test\Feature\Lib\Hubster;

use Carbon\Carbon;
use Codificar\MarketplaceIntegration\Lib\HubsterApi;
use Codificar\MarketplaceIntegration\Lib\HubsterLib;
use Codificar\MarketplaceIntegration\Test\TestCase;

class HubsterPollingTest extends TestCase
{
    private $api;

    private $lib;

    public function setUp(): void
    {
        parent::setUp();

        $clientId = \Settings::findByKey('hubster_client_id');
        $clientSecret = \Settings::findByKey('hubster_client_secret');

        $this->api = new HubsterApi;
        $this->lib = new HubsterLib();

        $expiryToken = \Settings::findByKey('hubster_expiry_token');
        if (($clientId && $clientSecret) && ($expiryToken == null || Carbon::parse($expiryToken) < Carbon::now())) {
            $this->api->auth($clientId, $clientSecret);
        }
    }

    /** @test */
    public function test_libNewOrders()
    {
        $orderArray = $this->lib->newOrders();
        $this->assertIsArray($orderArray);
        $this->assertTrue(count($orderArray) > 0);
    }

    public function test_apiNewOrders()
    {
        $this->api->setStoreId('1234');
        $response = $this->api->newOrders();

        $this->assertIsObject($response);
        $this->assertIsArray($response->orders);
    }
}
