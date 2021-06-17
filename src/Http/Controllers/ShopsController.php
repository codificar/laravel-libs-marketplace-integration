<?php

namespace Codificar\MarketplaceIntegration\Http\Controllers;

use Codificar\MarketplaceIntegration\Http\Requests\ShopsFormRequest;
use Codificar\MarketplaceIntegration\Http\Resources\ShopResource;
use Codificar\MarketplaceIntegration\Models\MarketConfig;
use Codificar\MarketplaceIntegration\Models\Shops;
use App\Http\Controllers\Controller;
use Codificar\MarketplaceIntegration\Lib\IFoodApi;
use Illuminate\Http\Request;

class ShopsController extends Controller
{
    public function index()
    {
        // $user = \Auth::guard('web_corp')->user();
        // // $user->institution;
        // \Log::debug("User 1: ".print_r($user->AdminInstitution->institution_id, 1));
        $shops = Shops::where('institution_id', '=', \Auth::guard('web_corp')->user()->AdminInstitution->institution_id)->get();
        foreach ($shops as $key => $value) {
            $value->getConfig;
        }
        return $shops;
    }

    public function store(ShopsFormRequest $request)
    {
        $user = \Auth::guard('web_corp')->user();
        \Log::debug("User: ".print_r($user->institution, 1));
        $shop = Shops::create([
            'name'          => $request->name,
            'merchant_id'   => $request->merchant_id,
            'institution_id'=> $user->AdminInstitution->institution_id
        ]);

        if ($shop) {
            $marketConfig = MarketConfig::create([
                'shop_id'       => $shop->id,
                'market'        => ($request->select == 1) ? 'ifood' : 'rappi',
                'client_id'     => $request->client_id,
                'client_secret' => $request->client_secret
                ]);
        }

        \Log::info('ShopID: '.print_r($shop->id,1));

        $res = new IFoodApi($marketConfig->id);
        $response = $res->getMerchantDetails($shop->merchant_id);

        $marketConfig = MarketConfig::where(['shop_id'       => $shop->id])
                        ->update([
            'latitude'      =>$response->address->latitude,
            'longitude'      =>$response->address->longitude
        ]);
        
        return new ShopResource($request);
    }
}
