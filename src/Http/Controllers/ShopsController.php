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
        $shops = Shops::where('institution_id', '=', \Auth::guard('web_corp')->user()->AdminInstitution->institution_id)->get();
        \Log::debug("Shop: ".print_r($shops, 1));
        foreach ($shops as $key => $value) {
            $value->getConfig;
            foreach ($value->getConfig as $key => $v) {
                $address = new IFoodApi($v->id);
                $address = $address->getMerchantDetails($v->merchant_id);
                \Log::debug("address: ".print_r($address->address, 1));
                $v['address'] = $address->address;
            }
        }
        return $shops;
    }

    public function store(ShopsFormRequest $request)
    {
        $user = \Auth::guard('web_corp')->user();
        \Log::debug("User: ".print_r($user->institution, 1));
        $shop = Shops::create([
            'name'          => $request->name,
            'institution_id'=> $user->AdminInstitution->institution_id,
            'status_reload' => $request->status_reload ? $request->status_reload : 0
        ]);

        if ($shop) {
            $marketConfig = MarketConfig::create([
                'shop_id'       => $shop->id,
                'merchant_id'   => $request->merchant_id,
                'market'        => ($request->select == 1) ? 'ifood' : 'rappi',
                'client_id'     => $request->client_id,
                'client_secret' => $request->client_secret
                ]);
        }

        \Log::info('ShopID: '.print_r($shop->id,1));

        $res = new IFoodApi($marketConfig->id);
        $response = $res->getMerchantDetails($marketConfig->merchant_id);
        \Log::info('response: '.print_r($response,1));
        $marketConfig = MarketConfig::where(['shop_id'       => $shop->id])
                        ->update([
            'latitude'      =>$response->address->latitude,
            'longitude'      =>$response->address->longitude
        ]);

        $shops = Shops::where('institution_id', '=', \Auth::guard('web_corp')->user()->AdminInstitution->institution_id)->get();
        foreach ($shops as $key => $value) {
            $value->getConfig;
        }
        
        return new ShopResource($shops);
    }

    public function status(Request $request)
    {
        \Log::debug("User: ".print_r($request->all(), 1));
        $user = \Auth::guard('web_corp')->user();
        $shop = Shops::where('institution_id', $user->AdminInstitution->institution_id)
                    ->update([
            'institution_id'=> $user->AdminInstitution->institution_id,
            'status_reload' => $request->status_reload ? $request->status_reload : 0
        ]);
    }

    public function update(ShopsFormRequest $request)
    {
        \Log::debug('Update Shop: '.print_r($request->all(),1));
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
        \Log::debug('Add Market: '.print_r($request->all(),1));
        $marketConfig = MarketConfig::create([
            'shop_id'       => $request->id,
            'merchant_id'   => $request->merchant_id,
            'market'        => ($request->select == 1) ? 'ifood' : 'rappi',
            'client_id'     => $request->client_id,
            'client_secret' => $request->client_secret
        ]);
        \Log::debug('Market: '.print_r($marketConfig,1));
        return $marketConfig;
    }

    public function updateMarketConfig(Request $request)
    {
        \Log::debug('Update Market: '.print_r($request->all(),1));

        $marketConfig = MarketConfig::where('id', $request->id)->update([
            'market'        => ($request->select['id'] == 1) ? 'ifood' : 'rappi',
            'merchant_id'   => $request->merchant_id,
            'client_id'     => $request->client_id,
            'client_secret' => $request->client_secret
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
