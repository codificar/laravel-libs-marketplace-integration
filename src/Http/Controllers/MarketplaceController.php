<?php

namespace Codificar\MarketplaceIntegration\Http\Controllers;

use App\Http\Controllers\Controller;
use Codificar\MarketplaceIntegration\Http\Requests\StoreMerchantFormRequest;
use Codificar\MarketplaceIntegration\Lib\MarketplaceFactory;
use Codificar\MarketplaceIntegration\Models\MerchantDetails;
use Codificar\MarketplaceIntegration\Models\OrderDetails;

class MarketplaceController extends Controller
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

    public function storeMerchant(StoreMerchantFormRequest $storeMerchantFormRequest){
        \Log::debug("StoreMerchantFormRequest: ".print_r($storeMerchantFormRequest->all(),1));
    }

    // deletar merchant

    // salvar  // atualizar shop

    // deletar shop
}