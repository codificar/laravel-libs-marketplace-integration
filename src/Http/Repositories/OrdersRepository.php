<?php 

namespace Codificar\MarketplaceIntegration\Http\Repositories;

use Codificar\MarketplaceIntegration\Models\OrderDetails;

class OrdersRepository
{
    /**
     * Get orders in database
     * @author Diogo C. Coutinho
     * 
     * @param Integer|NULL $id
     * 
     * @return OrderDetails $data
     */
    public static function getOrders($id)
    {
        $query = OrderDetails::query();
        if (isset($id) && $id != null) {
            $query->where('shop_id', $id);
        }

        $query->whereIn('code', OrderDetails::ORDER_STATUS);

        return $query->orderBy('distance', 'DESC')
                        ->orderBy('order_detail.display_id', 'ASC')
                        ->orderBy('order_detail.client_name', 'ASC')
                        ->orderBy('order_detail.request_id', 'ASC')//order by reuqest to show first the orders without points id, so orders without dispatched
                        ->paginate(200);
    }

}