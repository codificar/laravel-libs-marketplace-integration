<?php

namespace Codificar\MarketplaceIntegration\Test\Unit\Lib\IFoodApiTest;

;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Session;
use Codificar\MarketplaceIntegration\Lib\IFoodApi;
use Codificar\MarketplaceIntegration\Test\TestCase;

class GetOrdersTest extends TestCase
{
    protected $clientId = "2a8bdb0b-bbe0-454d-8514-525eb4adf8de";
    protected $clientSecret = "wz4k8m9egdt18z23gbkg66deew9kj3doha3xq5ph15qh0pv37tkvfwjv7xmfe374jaszts5z1g0f7kp617ftaiwuxwqzy7brl7v";

    public function setUp(): void
    {
        parent::setUp();
    }    

    /** @test */
    public function it_should_get_orders()
    {       
        $fakeUrl = "https://merchant-api.ifood.com.br/order/v1.0/events:polling";
        $expectedBody = [
            "createdAt" => "2019-09-19T13 =>40 =>11.822Z",
            "fullCode" => "PLACED",
            "metadata" => [
                "additionalProp1" => [],
            ],
            "code" => "PLC",
            "orderId" => "07110e1b-8191-4670-baed-407219481ffb",
            "id" => "cd40582b-0ef2-4d52-bc7c-507fdff12e21"
        ];
        Http::fake([
            $fakeUrl => Http::response($expectedBody, 200)
        ]);  

        $api = new IFoodApi;
        $this->authApi($api);
        $response = $api->getOrders();        
        $this->assertEquals($expectedBody, $response);
    }

    private function authApi(IFoodApi $api){
        // $fakeUrl = "https://merchant-api.ifood.com.br/authentication/v1.0/oauth/token";
        // $expectedBody = [
        //     "accessToken" => "randomTokenGeneratedFaker",
        //     "type" => "bearer",
        //     "expiresIn" => 10799
        // ];
        // Http::fake([
        //     $fakeUrl."*" => Http::response($expectedBody, 200)
        // ]);  
        $api->auth($this->clientId, $this->clientSecret);
    }
}
