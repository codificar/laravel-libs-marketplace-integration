<?php

namespace Codificar\MarketplaceIntegration\Repositories;

use Codificar\MarketplaceIntegration\Models\MarketConfig;
use Codificar\MarketplaceIntegration\Models\Shops;
use Codificar\MarketplaceIntegration\Models\OrderDetails ;

/**
 * Class DispatchRepository
 * 
 */
class DispatchRepository
{
    /**
     * @author Raphael Cangucu
     *
     * Method that get all avalaible order to dispatch automatically
     * @return [] OrderDetails
     */
    public static function getOrders()
    {
        $query = OrderDetails::query();

        // set code to all orders available to delivery
        $query->whereIn('code', [OrderDetails::CONFIRMED, OrderDetails::REQUEST_DRIVER_AVAILABILITY])
            ->join('delivery_address', 'order_detail.order_id', '=', 'delivery_address.order_id');

        // we need to order by shop_id 
        $query->orderBy('order_detail.shop_id', 'ASC');

        return $query->all();
    }

}