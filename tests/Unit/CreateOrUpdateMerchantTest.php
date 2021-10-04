<?php

namespace Tests\Unit;

use Codificar\MarketplaceIntegration\Http\Controllers\MarketplaceController;
use Codificar\MarketplaceIntegration\Http\Requests\StoreMerchantFormRequest;
use GuzzleHttp\Client;
use PHPUnit\Framework\TestCase;

class CreateOrUpdateMerchantTest extends TestCase
{
    public function testUpdateOrCreateMerchant()
    {
        $client       = new Client([
            'base_uri'  => 'http://192.168.0.2:8003'
        ]);
        $headers    = [
            'Content-Type' => 'application/json',
        ];
        // $data = new StoreMerchantFormRequest();
        // $data->id            = 18;
        // $data->merchant_id   =  "1ebeda78-3932-47b0-b34c-5376510a4725";
        // $data->type          = "ifood";
        $data = [
            "merchant_id"   => "1ebeda78-3932-47b0-b34c-5376510a4725",
            "type"          => "ifood"
        ];

        $res = $client->request('POST', '/store/merchant', [ 'form_params'   => $data]);
        // $res = MarketplaceController::storeMerchant($data);
        // fwrite(STDERR, print_r($data, TRUE));
        $this->assertTrue($res->getBody()->getContents());
    }
}