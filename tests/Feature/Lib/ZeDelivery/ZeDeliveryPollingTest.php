<?php

namespace Codificar\MarketplaceIntegration\Test\Feature\Lib\Hubster;

use Codificar\MarketplaceIntegration\Lib\ZeDeliveryApi;
use Codificar\MarketplaceIntegration\Lib\ZeDeliveryLib;
use Codificar\MarketplaceIntegration\Test\TestCase;

class ZeDeliveryPollingTest extends TestCase
{
    private $api;

    private $lib;

    public function setUp(): void
    {
        parent::setUp();

        $clientId = \Settings::findByKey('zedelivery_client_id');
        $clientSecret = \Settings::findByKey('zedelivery_client_secret');

        $this->api = new ZeDeliveryApi;
        $this->lib = new ZeDeliveryLib;

        $expiryToken = \Settings::findByKey('zedelivery_expiry_token');
        if (($clientId && $clientSecret) && ($expiryToken == null || Carbon::parse($expiryToken) < Carbon::now())) {
            $this->api->auth($clientId, $clientSecret);
        }
    }

    /** @test */
    public function test_libNewOrdersZe()
    {
        $orderArray = $this->lib->newOrders();
        $this->assertIsArray($orderArray);
        $this->assertTrue(count($orderArray) > 0);
    }

    public function test_apiNewOrdersZe()
    {
        $this->api->setStoreId('1234');
        $response = $this->api->newOrders();

        $this->assertIsObject($response);
        $this->assertIsArray($response->orders);
    }
}
