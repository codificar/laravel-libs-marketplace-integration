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
            if ($value->getConfig) {
                foreach ($value->getConfig as $key => $v) {
                    $res = new DeliveryFactory();
                    \Log::debug('$v: '.print_r($v,1));
                    $response = $res->getMerchantDetails($value->id, (object)['merchant_id' => $v->merchant_id, 'id' => $v->shop_id]);
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
        ]);
        \Log::debug("shop: ".json_encode($shop->id,1));

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
        \Log::debug("storeMarketConfig".print_r($request->all(),1));
        \DB::beginTransaction();
        $marketConfig = MarketConfig::create([
            'shop_id'       => $request->id,
            'merchant_id'   => $request->merchant_id,
            'market'        => ($request->select == 1) ? 'ifood' : 'rappi',
        ]);
        $shop = Shops::find($request->id);
        $res = new DeliveryFactory();
        if ($shop->expiry_token == NULL || Carbon::now() > Carbon::parse($shop->expiry_token)) {
            \Log::debug("Entrou: ".Carbon::now());
            $res->auth($shop->id);
        }
        $response = $res->getMerchantDetails($request->id, $request);
        \Log::debug('response: '.print_r($response,1));
        if (is_object($response)) {
            
            $marketConfig = MarketConfig::where(['merchant_id'       => $request->merchant_id])
                                    ->update([
                                        'latitude'      => $response->address->latitude,
                                        'longitude'     => $response->address->longitude,
                                        'address'       => json_encode($response->address),
                                    ]);
            \DB::commit();
            return $marketConfig;
        } else if (is_array($response)) {
            \Log::debug('response: '.print_r($response,1));
            \DB::rollBack();
            return $response;
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
        $data = Shops::find($id);

        if (is_object($data))
        {
            $data->delete();
            return true;
        } else {
            return false;
        }
    }

    public function iFoodCredentials(Request $request)
    {
        \Log::debug('Credentials: '.print_r($request->all(), 1));
        $client_id          = \Settings::updateOrCreate([
            'key'   =>  'ifood_client_id'],[
            'value' => $request->ifood_client_id
        ]);
        $client_secret      = \Settings::updateOrCreate([
            'key'   =>  'ifood_client_secret'],[
            'value' => $request->ifood_client_secret
        ]);

        if ($client_id && $client_secret) {
            return [
                'code'      => 200,
                'message'   => 'Salvo com sucesso!'
            ];
        } else {
            return [
                'code'      => 401,
                'message'   => 'Erro ao salvar as credenciais!'
            ];
        }
    }

    public function getIfoodCredentials()
    {
        $client_id          = \Settings::where('key', 'ifood_client_id')->first();
        $client_secret      = \Settings::where('key', 'ifood_client_secret')->first();
        if ($client_secret && $client_id) {
            return [
                'ifood_client_id'       => $client_id,
                'ifood_client_secret'   => $client_secret
            ];
        } else {
            return [
                'code'      => 404,
                'message'   => 'Cadastre as cerdenciais iFood!'
            ];
        }
        
    }
}
