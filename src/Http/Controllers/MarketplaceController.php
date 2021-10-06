<?php

namespace Codificar\MarketplaceIntegration\Http\Controllers;

use App\Http\Controllers\Controller;
use Codificar\MarketplaceIntegration\Http\Repositories\MerchantRepository;
use Codificar\MarketplaceIntegration\Http\Requests\StoreMerchantFormRequest;
use Codificar\MarketplaceIntegration\Http\Requests\UpdateMerchantFormRequest;
use Codificar\MarketplaceIntegration\Http\Resources\MerchantDetailsResource;

class MarketplaceController extends Controller
{
    /**
     * Update or create Mechant details on database
     * 
     * @author Diogo C. Coutinho
     * 
     * @param String $merchant_id
     * @param Integer $id
     * @param String $type
     * 
     * @return MerchantDetailsResource $merchant
     */
    public static function storeMerchant(StoreMerchantFormRequest $merchantData)
    {
        $merchant = MerchantRepository::updateOrCreateMerchant($merchantData->all());
        return new MerchantDetailsResource($merchant);
    }

    /**
     * Delete merchant details in database
     * @author Diogo C. Coutinho
     * 
     * @param Integer $merchantId
     * @return Boolen true|false
     */
    public function deleteMerchant($id)
    {
        $data = MerchantRepository::deleteMerchant($id);
        return new MerchantRepository($data);
    }

    /**
     * Update Merchant details in database
     * @author Diogo C. Coutinho
     * 
     * @param String $merchantId, $name, $type
     * @param Float $latitude, $longitude
     * @param Integer $shopId
     * @param Object $address
     * 
     * @return MerchantDetailsResource $data
     */
    public function updateMerchantDetails(UpdateMerchantFormRequest $merchantData)
    {
        $merchant = MerchantRepository::updateOrCreateMerchant($merchantData);
        return new MerchantDetailsResource($merchant);
    }

}