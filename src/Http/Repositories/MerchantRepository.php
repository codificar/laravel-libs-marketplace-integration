<?php

namespace Codificar\MarketplaceIntegration\Http\Repositories;

use Codificar\MarketplaceIntegration\Models\MerchantDetails;

class MerchantRepository
{
    public static function updateOrCreateMerchant($merchantData)
    {
        // $data  = json_encode($merchantData->all());
        // dd($merchantData->merchantDetails->address);
        
        $res = MerchantDetails::updateOrCreate([
            'id'            => $merchantData->id
        ],[
            'merchant_id'   => $merchantData->merchant_id,
            'name'          => $merchantData->merchantDetails->name,
            'shop_id'       => $merchantData->shop_id,
            'latitude'      => $merchantData->merchantDetails->address->latitude,
            'longitude'     => $merchantData->merchantDetails->address->longitude,
            'address'       => json_encode($merchantData->merchantDetails->address),
            'type'          => $merchantData->type
        ]);
        

    }
}
