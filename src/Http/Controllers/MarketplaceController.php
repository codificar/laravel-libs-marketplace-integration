<?php

namespace Codificar\MarketplaceIntegration\Http\Controllers;

use App\Http\Controllers\Controller;
use Codificar\MarketplaceIntegration\Http\Repositories\MerchantRepository;
use Codificar\MarketplaceIntegration\Http\Requests\StoreMerchantFormRequest;
use Codificar\MarketplaceIntegration\Http\Resources\MerchantDetailsResource;

class MarketplaceController extends Controller
{
    
    /**
     * Store Mechant details on database
     * 
     * @author Diogo C. Coutinho
     * 
     * @param String $merchant_id
     * @param Integer $id
     * @param String $type
     * 
     * @return MerchantDetailsResource $merchant
     */
    public static function storeMerchant(StoreMerchantFormRequest $storeMerchantFormRequest)
    {
        $merchant = MerchantRepository::updateOrCreateMerchant($storeMerchantFormRequest);
        return new MerchantDetailsResource($merchant);
    }

    #TODO comentar e criar a função de atualização com todos os status locais
    public function updateOrderRequestListener($order, $status)
    {
        # code...
    }

    public function getOrders($getOrdersFormRequest)
    {
        # code...
    }

    //TODO funcao pegar ordens da base de dados

    

    //TODO deletar merchant

    //TODO salvar  // atualizar shop

    //TODO deletar shop

    public function storeMarketConfig(Request $request)
    {
        // \Log::debug(__CLASS__.__FUNCTION__."request=>".print_r($request->all(),1));

        \DB::beginTransaction();

        $shop = Shops::find($request->id);
        $res = new DeliveryFactory();
        if ($shop->expiry_token == NULL || Carbon::now() > Carbon::parse($shop->expiry_token)) {
            \Log::debug(__CLASS__.__FUNCTION__."Entrou: ".Carbon::now());
            $res->auth($shop->id);
        }
        $response = $res->getMerchantDetails($request->id, $request);
        \Log::debug('response: getMerchantDetails=> '.print_r($response,1));
        if (is_object($response)) 
        {
            \Log::debug(__CLASS__.__FUNCTION__."marketConfig with address=>".print_r($request->merchant_name ,1));

            
            $marketConfig = MerchantDetails::create([
                'shop_id'       => $request->id,
                'merchant_id'   => $request->merchant_id,
                'name'          => $request->merchant_name,
                'market'        => ($request->select == 1) ? 'ifood' : 'rappi',
                'latitude'      => $response->address->latitude,
                'longitude'     => $response->address->longitude,
                'address'       => json_encode($response->address),
            ]);

            // \Log::debug(__CLASS__.__FUNCTION__."marketConfig with address=>".print_r($marketConfig ,1));

            \DB::commit();
            return $marketConfig;
        } else if (is_array($response)) {
            \Log::debug('response: '.print_r($response,1));
            
            \DB::rollBack();
            return $response;
        }
        
    }

    /**
     * 
     * Update market configuration
     * 
     * @return ShopResource
     */
    public function updateMarketConfig(Request $request)
    {
        $api = new IFoodApi;
        $api = json_encode($api->getMerchantDetails('',$request->merchant_id));

        \Log::debug("  request -> ". print_r($request->all(),1));

        if(property_exists($api,'address'))
        {
            \Log::info("save address from ifood too");
            MerchantDetails::where('id', $request->id)->update([
                'name'          => $request->merchant_name,
                'market'        => ($request->select['id'] == 1) ? 'ifood' : 'rappi',
                'merchant_id'   => $request->merchant_id,
                'address'       => json_encode($api->address) 
            ]);
        } else {
            MerchantDetails::where('id', $request->id)->update([
                'name'          => $request->merchant_name,
                'market'        => ($request->select['id'] == 1) ? 'ifood' : 'rappi',
                'merchant_id'   => $request->merchant_id
            ]);
        }


        return new ShopResource($request);
    }

    public function deleteMarketConfig(Request $request)
    {
        \Log::debug('deleteMarketConfig=>SHOPS: '.print_r($request->all(), 1));
        $response = ['success' => false];

        $data = MerchantDetails::find($request->id);

        if (is_object($data))
        {
            $data->delete();
            $response['success'] = true;
        }

        return new ShopResource($response);
    }
}