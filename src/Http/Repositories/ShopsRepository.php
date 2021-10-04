<?php

namespace Codificar\MarketplaceIntegration\Http\Repositories;

use Codificar\MarketplaceIntegration\Models\Shops;

/**
 * Class Shop repository to access Shops model
 * @author Diogo C. Coutinho
 */
class ShopsRepository
{
    /**
     * Update or create shop in database
     * @author Diogo C. Coutinho
     * 
     * @param ShopsFormRequest $request
     * @param Integer $institutionId
     * 
     * @return Shops $data
     */
    public static function updateOrCreateShop($shopData, $institutionId)
    {
        return Shops::updateOrCreate([
            'id'            => $shopData->id
        ],[
            'name'          => $shopData->name,
            'institution_id'=> $institutionId,
            'status_reload' => $shopData->status_reload ? $shopData->status_reload : 0,
        ]);
    }

    /**
     * Get specific shop in database
     * @author Diogo C. Coutinho
     * 
     * @param Integer $shopId
     * 
     * @return Shops $data
     */
    public static function getShop($shopId)
    {
        return Shops::find($shopId);
    }

    /**
     * Get all shops in database per institution
     * @author Diogo C. Coutinho
     * 
     * @param Integer $intitutionId
     * 
     * @return Shops $data
     */
    public static function getAllShops($institutionId)
    {
        return Shops::where('institution_id', $institutionId)->get();
    }

    /**
     * Update status reload in shop database
     * @author Diogo C. Coutinho
     * 
     * @param Integer $institutionId
     * 
     * @return Shop $data
     */
    public static function updateStatusReload($statusReload, $institutionId)
    {
        return Shops::where('institution_id', $institutionId)
                    ->update([
            'institution_id'=> $institutionId,
            'status_reload' => $statusReload ? $statusReload : 0
        ]);
    }
}
