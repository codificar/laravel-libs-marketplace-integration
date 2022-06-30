<?php

namespace Codificar\MarketplaceIntegration\Http\Controllers;

use App\Http\Controllers\Controller;
use Codificar\MarketplaceIntegration\Models\MarketConfig;
use Codificar\MarketplaceIntegration\Lib\MarketplaceFactory;
use Codificar\MarketplaceIntegration\Models\DeliveryAddress;
use Codificar\MarketplaceIntegration\Models\OrderDetails;
use Codificar\MarketplaceIntegration\Models\Shops;
use Illuminate\Http\Request;

use Codificar\MarketplaceIntegration\Repositories\MarketplaceRepository;
use Codificar\MarketplaceIntegration\Http\Resources\OrdersResource;

use Codificar\MarketplaceIntegration\Http\Requests\StoreMarketConfigFormRequest;


class MarketConfigController extends Controller
{

    /**
     * Function to store (save or update) market config
     * @return ?
     */
    public function store(StoreMarketConfigFormRequest $request)
    {

        \DB::beginTransaction();

        $latitude   = null;
        $longitude  = null;
        $address    = null;

        if($request->merchantDetails && $request->merchantDetails['data']) {
            $latitude = $request->merchantDetails['data']->address->latitude ;
            $longitude = $request->merchantDetails['data']->address->longitude ;
            $address = json_encode($request->merchantDetails['data']) ;
        }

        $marketConfig = MarketConfig::updateOrCreate([
            'shop_id'       => $request->shop_id,
            'market'        => $request->marketplace ,
            'merchant_id'   => $request->merchant_id
        ],[
            'shop_id'       => $request->shop_id,
            'merchant_id'   => $request->merchant_id,
            'name'          => $request->merchant_name,
            'market'        => $request->marketplace ,
            'latitude'      => $latitude,
            'longitude'     => $longitude,
            'address'       => $address,
        ]);

        \DB::commit();

        return $marketConfig;
    }

    /**
     * Function to delete market config
     * @return 
     */
    public function delete($marketConfigId)
    {
        
        $response = ['success' => false];

        $destroy = MarketConfig::destroy($marketConfigId);

        if ($destroy)
        {
            $response['success'] = true;
        }

        return $response;
    }
 
}
