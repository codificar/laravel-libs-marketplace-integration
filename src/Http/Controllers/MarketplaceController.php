<?php

namespace Codificar\MarketplaceIntegration\Http\Controllers;

use Illuminate\Routing\Controller;
use Codificar\MarketplaceIntegration\Http\Repositories\MerchantRepository;
use Codificar\MarketplaceIntegration\Http\Requests\StoreMerchantFormRequest;
use Codificar\MarketplaceIntegration\Http\Resources\MerchantDetailsResource;

class MarketplaceController extends \BaseController
{
    
    #TODO comentar e criar a função de atualização com todos os status locais
    public function updateOrderRequestListener($order, $status)
    {
        # code...
    }

    public function getOrders($getOrdersFormRequest)
    {
        # code...
    }

    // funcao pegar ordens da base de dados

    // salvar / atualizar merchant

    public static function storeMerchant(StoreMerchantFormRequest $storeMerchantFormRequest)
    {
        // \Log::debug("StoreMerchantFormRequest: ".json_encode($storeMerchantFormRequest));
        // echo "StoreMerchantFormRequest: ".json_encode($storeMerchantFormRequest->all());
        $merchant = MerchantRepository::updateOrCreateMerchant($storeMerchantFormRequest);

        return new MerchantDetailsResource($merchant);
    }

    // deletar merchant

    // salvar  // atualizar shop

    // deletar shop
}