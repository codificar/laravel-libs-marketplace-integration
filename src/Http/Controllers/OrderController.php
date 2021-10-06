<?php

namespace Codificar\MarketplaceIntegration\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class OrderController extends Controller
{
    /**
     * Get orders in database
     * @author Diogo C. Coutinho
     * 
     * @return OrdersDetails $data
     */
    public function getOrdersDatabase()
    {
        $query = OrderDetails::query();
        if (isset($id) && $id != null) {
            $query->where('shop_id', $id);
        }

        $query->where(function($queryCode){
                $queryCode->whereIn('code', ['CFM', 'RDA'])
                ->orWhere(function($queryInner) {
                        $queryInner->where('order_detail.code','DSP')
                        ->where('order_detail.request_id','>',1);
                });
        })
        ->join('delivery_address', 'order_detail.order_id', '=', 'delivery_address.order_id');

        $orders =   $query
                        ->orderBy('order_detail.request_id', 'ASC')//order by reuqest to show first the orders without points id, so orders without dispatched
                        ->orderBy('delivery_address.neighborhood', 'ASC')
                        ->orderBy('distance', 'DESC')
                        ->orderBy('order_detail.display_id', 'ASC')
                        ->orderBy('order_detail.client_name', 'ASC')
                        ->paginate(200);

        return new OrdersResource($orders);

    }
}
