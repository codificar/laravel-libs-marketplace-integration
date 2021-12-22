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

        foreach ($orders as $key => $order) {
            if($order->mmerchant) {
                $diffDistance = \DB::select( \DB::raw(
                    "SELECT ST_Distance_Sphere(ST_GeomFromText('POINT(".$order->mmerchant->longitude." ".$order->mmerchant->latitude.")'), ST_GeomFromText('POINT(".$response->delivery->deliveryAddress->coordinates->longitude." ".$response->delivery->deliveryAddress->coordinates->latitude.")')) AS diffDistance"
                ));
                \Log::debug("DISTANCE: ".print_r($diffDistance,1));
                $calculatedDistance = $diffDistance[0]->diffDistance;
                $order->distance = $calculatedDistance;
            }
        }
        
        return new OrdersResource($orders);

    }

    #TODO update or create de ordens
    #TODO updateOrderRequest
    /**
     * Save orders details
     * @author Diogo C. Coutinho
     * 
     * @return OrderDatils $order
     */
    public function updateOrder($data)
    {
        return new OrdersResource(OrdersRepository::updateOrder($data));
    }
    

    #TODO comentar e criar a função de atualização com todos os status locais
    public function updateOrderRequestListener($order, $status)
    {
        # code...
    }
}
