<?php

namespace Codificar\MarketplaceIntegration\Http\Repositories;

use Codificar\MarketplaceIntegration\Models\MerchantDetails;

class MerchantRepository
{
    /**
     * Update or create Mechant details on database
     * 
     * @author Diogo C. Coutinho
     * 
     * @param Object $merchantData
     * 
     * @return MerchantDetailsResource $merchant
     */
    public static function updateOrCreateMerchant($merchantData)
    {        
        return MerchantDetails::updateOrCreate([
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

    /**
     * Delete Merchant in database
     * @author Diogo C. Coutinho
     * 
     * @param Integer $id
     * 
     * @return MarchantDetails $object
     */
    public static function deleteMerchant($id)
    {
        return MerchantDetails::find($id)->delete();
    }
}
