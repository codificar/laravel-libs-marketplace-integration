<?php

namespace Codificar\MarketplaceIntegration\Http\Controllers;

use Codificar\MarketplaceIntegration\Http\Requests\ShopsFormRequest;
use Codificar\MarketplaceIntegration\Http\Resources\ShopResource;
use Codificar\MarketplaceIntegration\Models\MarketConfig;
use Codificar\MarketplaceIntegration\Models\Shops;
use App\Http\Controllers\Controller;
use Codificar\MarketplaceIntegration\Http\Controllers\DeliveryFactory;
use Illuminate\Http\Request;
use Carbon\Carbon;

class ShopsController extends Controller
{
    public function index()
    {
        $shops = Shops::where('institution_id', '=', \Auth::guard('web_corp')->user()->AdminInstitution->institution_id)->get();
        foreach ($shops as $key => $value) {
            $value->getConfig;
            if ($value->getConfig) {
                foreach ($value->getConfig as $key => $v) {
                    $res = new DeliveryFactory();
                    $response = $res->getMerchantDetails($v->shop_id);
                    \Log::debug("Status: ".print_r($response,1));
                    $v->status = isset($response->status) ? $response->status : "UNAVIABLE";
                }
            }
        }
        return $shops;
    }

    public function store(ShopsFormRequest $request)
    {        
        $user = \Auth::guard('web_corp')->user();
        $shop = Shops::create([
            'name'          => $request->name,
            'institution_id'=> $user->AdminInstitution->institution_id,
            'status_reload' => $request->status_reload ? $request->status_reload : 0,
            'client_id'     => $request->client_id,
            'client_secret' => $request->client_secret
        ]);

        $res = new DeliveryFactory();
        $token = $res->auth($shop->id);

        $shops = Shops::where('institution_id', '=', \Auth::guard('web_corp')->user()->AdminInstitution->institution_id)->get();
        \Log::debug("shops: ".json_encode($shops,1));
        return new ShopResource($shops);
    }

    public function status(Request $request)
    {
        $user = \Auth::guard('web_corp')->user();
        $shop = Shops::where('institution_id', $user->AdminInstitution->institution_id)
                    ->update([
            'institution_id'=> $user->AdminInstitution->institution_id,
            'status_reload' => $request->status_reload ? $request->status_reload : 0
        ]);
    }

    public function update(ShopsFormRequest $request)
    {
        $user = \Auth::guard('web_corp')->user();
        $shop = Shops::where('id', $request->id)->update([
            'name'          => $request->name,
            'institution_id'=> $user->AdminInstitution->institution_id,
        ]);
        $data = Shops::where('id', $request->id)->get();
        
        foreach ($data as $key => $value) {
            $value->getConfig;
        }

        return $data[0];
    }

    public function storeMarketConfig(Request $request)
    {
        \Log::debug("storeMarketConfig".print_r($request->id,1));
        
        $marketConfig = MarketConfig::create([
            'shop_id'       => $request->id,
            'merchant_id'   => $request->merchant_id,
            'client_id'     => $request->client_id,
            'client_secret' => $request->client_secret,
        ]);
        \Log::debug('marketConfig: '.print_r($marketConfig,1));
        $address = new IFoodApi;
        $address = $address->getMerchantDetails($request->merchant_id);
        \Log::debug('Address: '.print_r($address,1));
        if (!isset($address['code'])) {
            return $marketConfig;
        } else {
            return $address;
        }
        
    }

    public function updateMarketConfig(Request $request)
    {
        $address = new IFoodApi;
        $address = $address->getMerchantDetails($request->merchant_id);
        // \Log::debug("updateMarketConfig". json_encode($address->address));

        $marketConfig = MarketConfig::where('id', $request->id)->update([
            'market'        => ($request->select['id'] == 1) ? 'ifood' : 'rappi',
            'merchant_id'   => $request->merchant_id,
            'client_id'     => $request->client_id,
            'client_secret' => $request->client_secret,
            'address'       => json_encode($address->address)
        ]);

        return new ShopResource($request);
    }

    public function deleteMarketConfig($id)
    {
        $data = MarketConfig::where([
            ['id', $id],
        ])->first();

        if (is_object($data))
        {
            $data->delete();
            return true;
        } else {
            return false;
        }
    }

    public function delete($id)
    {
        $data = Shops::where([
            ['id', $id],
        ])->first();

        if (is_object($data))
        {
            $data->delete();
            return true;
        } else {
            return false;
        }
    }
}
