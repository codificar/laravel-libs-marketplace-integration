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


class MarketplaceController extends Controller
{
   
    /**
     * Function to handle webhook from multiple marketplaces
     */
    public function webhook(Request $request, $market)
    {
        \Log::debug('Entrou no webhook controller');
        $factory = MarketplaceFactory::create($market);
        return $factory->webhook($request);
    }
    

    /**
     * Function to return orders from database
     * @return OrdersResource
     */
    public function getOrders(Request $request, $shopId = NULL)
    {
        
        $startTime = $request['range'][0] != null ? $request['range'][0] : \Carbon\Carbon::now()->subDays(1);

        $endTime = $request['range'][0] != null ? $request['range'][0] : null;

        $marketId = $request['marketId'] ;

        $orders =  MarketplaceRepository::getOrders($shopId, $marketId, $startTime, $endTime);

        return new OrdersResource($orders);
    }

    /**
     * Function to store (save or update) market config
     * @return ?
     */
    public function storeMarketConfig(StoreMarketConfigFormRequest $request)
    {

        \DB::beginTransaction();


        $factory = MarketplaceFactory::create($request->select);
        $response = $factory->merchantDetails($marketConfig->merchant_id);

        $latitude   = null;
        $longitude  = null;
        $address    = null;

        if($response) {
            $latitude = $response->address->latitude ;
            $longitude = $response->address->longitude ;
            $address = json_encode($response->address) ;
        }

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
    public function deleteMarketConfig(Request $request)
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
