<?php

namespace Codificar\MarketplaceIntegration\Http\Controllers;

use App\Http\Controllers\Controller;
use Codificar\MarketplaceIntegration\Http\Repositories\OrdersRepository;
use Codificar\MarketplaceIntegration\Http\Resources\OrdersResource;

class OrderController extends Controller
{
    /**
     * Get orders in database
     * @author Diogo C. Coutinho
     * 
     * @return OrdersDetails $data
     */
    public function getOrdersDatabase($id = NULL)
    {
        $orders = OrdersRepository::getOrders($id);

        return new OrdersResource($orders);

    }

    #TODO update or create de ordens
    #TODO updateOrderRequest
    

    #TODO comentar e criar a função de atualização com todos os status locais
    public function updateOrderRequestListener($order, $status)
    {
        # code...
    }
}
