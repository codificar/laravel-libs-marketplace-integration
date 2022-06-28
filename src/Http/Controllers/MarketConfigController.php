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

        $latitude = $request->merchantDetails->address->latitude ;
        $longitude = $request->merchantDetails->address->longitude ;
        $address = json_encode($response->address) ;
    

        $address = MarketConfig::updateOrCreate([
            'id'            => $request->id
        ],[
            'shop_id'       => $request->shop_id,
            'merchant_id'   => $request->merchant_id,
            'name'          => $request->merchant_name,
            'market'        => $request->select ,
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
    public function delete(Request $request)
    {
        
        $response = ['success' => false];

        $data = MarketConfig::find($request->id);

        if (is_object($data))
        {
            $data->delete();
            $response['success'] = true;
        }

        return $response;
    }
 
}
