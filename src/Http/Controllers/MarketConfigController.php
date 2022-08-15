<?php

namespace Codificar\MarketplaceIntegration\Http\Controllers;

use App\Http\Controllers\Controller;
use Codificar\MarketplaceIntegration\Http\Requests\StoreMarketConfigFormRequest;
use Codificar\MarketplaceIntegration\Models\MarketConfig;
use Codificar\MarketplaceIntegration\Models\Shops;

class MarketConfigController extends Controller
{
    /**
     * Function to store (save or update) market config.
     * @return ?
     */
    public function store(StoreMarketConfigFormRequest $request)
    {
        \DB::beginTransaction();

        $shop = Shops::find($request->shop_id);

        $latitude = null;
        $longitude = null;
        $address = json_encode([]);

        if ($request->merchantDetails && $request->merchantDetails['data']) {
            $latitude = $request->merchantDetails['data']->address->latitude;
            $longitude = $request->merchantDetails['data']->address->longitude;
            $address = json_encode($request->merchantDetails['data']->address);
        }

        if (! $latitude) {
            $latitude = $shop->latitude;
        }
        if (! $longitude) {
            $longitude = $shop->longitude;
        }

        $marketConfig = MarketConfig::updateOrCreate([
            'shop_id'       => $request->shop_id,
            'market'        => $request->marketplace,
            'merchant_id'   => $request->merchant_id
        ], [
            'shop_id'       => $request->shop_id,
            'merchant_id'   => $request->merchant_id,
            'name'          => $request->merchant_name,
            'market'        => $request->marketplace,
            'latitude'      => $latitude,
            'longitude'     => $longitude,
            'address'       => $address,
            'polling'       => boolval($request->polling)
        ]);

        \DB::commit();

        return $marketConfig;
    }

    /**
     * Function to delete market config.
     * @return
     */
    public function delete($marketConfigId)
    {
        $response = ['success' => false];

        $destroy = MarketConfig::destroy($marketConfigId);

        if ($destroy) {
            $response['success'] = true;
        }

        return $response;
    }
}
