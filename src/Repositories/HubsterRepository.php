<?php

namespace Codificar\MarketplaceIntegration\Repositories;

use Codificar\MarketplaceIntegration\Lib\MarketplaceFactory;
use Codificar\MarketplaceIntegration\Models\OrderDetails;

/**
 * Class HubsterRepository.
 */
class HubsterRepository extends MarketplaceRepository
{
    /**
     * @author Raphael Cangucu
     *
     * update order by points
     * @return OrderDetails
     */
    public static function cancelDeliveryOrder($deliveryReferenceId)
    {
        $order = OrderDetails::where('marketplace_order_id', '=', $deliveryReferenceId)
                                ->where('aggregator', '=', MarketplaceFactory::HUBSTER)
                                ->first();

        if ($order) {
            $order->code = self::CANCELLED;
            $order->full_code = self::mapFullCode(self::CANCELLED);
            $order->save();

            self::cancelOrder($order);
        } else {
            \Log::warning(sprintf('Pedido não encontrado para delivery com referencia: %s', $deliveryReferenceId));
        }

        return $order;
    }
}
