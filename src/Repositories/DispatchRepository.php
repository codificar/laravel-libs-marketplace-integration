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

        // 
        $query->whereIn('code', ['CFM', 'RDA']);
        
        ->join('delivery_address', 'order_detail.order_id', '=', 'delivery_address.order_id');

        return $query->all();
    }

}