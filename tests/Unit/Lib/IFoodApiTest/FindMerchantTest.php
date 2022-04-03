<?php

namespace Codificar\MarketplaceIntegration\Test\Unit\Lib\IFoodApiTest;

;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Session;
use Codificar\MarketplaceIntegration\Lib\IFoodApi;
use Codificar\MarketplaceIntegration\Test\TestCase;

class FindMerchantTest extends TestCase
{
    protected $clientId = "2a8bdb0b-bbe0-454d-8514-525eb4adf8de";
    protected $clientSecret = "wz4k8m9egdt18z23gbkg66deew9kj3doha3xq5ph15qh0pv37tkvfwjv7xmfe374jaszts5z1g0f7kp617ftaiwuxwqzy7brl7v";

    public function setUp(): void
    {
        parent::setUp();
    }    

    /** @test */
    public function it_should_be_find_merchant()
    {       
        $fakeUrl = "https://merchant-api.ifood.com.br/merchant/v1.0/merchants/";
        $expectedBody = [
            "id" => "9fbd626b-723b-4bb0-86d8-0a75b23285b6",
            "name" => "Teste - Codificar Sistemas (3282)",
            "corporateName" => "Teste - Codificar Sistemas (3282)"
        ];
        Http::fake([
            $fakeUrl => Http::response($expectedBody, 200)
        ]);  

        $api = new IFoodApi;
        $this->authApi($api);
        $merchantId = "1453175";

        $response = $api->findMerchant($merchantId);        
        $this->assertEquals($expectedBody, $response);
    }

    private function authApi(IFoodApi $api){
        $fakeUrl = "https://merchant-api.ifood.com.br/authentication/v1.0/oauth/token";
        $expectedBody = [
            "accessToken" => "randomTokenGeneratedFaker",
            "type" => "bearer",
            "expiresIn" => 10799
        ];
        Http::fake([
            $fakeUrl."*" => Http::response($expectedBody, 200)
        ]);  
        $api->auth($this->clientId, $this->clientSecret);
    }
}
