<?php

namespace Codificar\MarketplaceIntegration\Repositories;

use Codificar\MarketplaceIntegration\Models\MarketConfig;
use Codificar\MarketplaceIntegration\Models\Shops;
use Codificar\MarketplaceIntegration\Models\OrderDetails ;
use Codificar\MarketplaceIntegration\Models\AutomaticDispatch ;

use Codificar\MarketplaceIntegration\Lib\MarketplaceFactory;


use Carbon\Carbon;


/**
 * Class MarketplaceRepository
 * 
 */
class MarketplaceRepository
{

    /**
     * @author Raphael Cangucu
     *
     * update order by points
     * @return [] OrderDetails
     */
    public static function updateOrder($requestId, $pointId, $pointStartTime, $pointFinishTime, $isCancelled)
    {
        
        $order = OrderDetails::where('request_id', '=', $requestId)
                                ->where('point_id', '=', $pointId)
                                ->first();
        
        if ($order) 
        {
            $factory = MarketplaceFactory::create($order->marketplace);

            $request_status='';
            $code='';
            $full_code='';
            if (!$isCancelled) {
                #TODO set codes to a constants withou literals
                if ($pointStartTime != NULL && $order->code != 'DSP') {
                    $res = $factory->dispatch($order->order_id);
                    $request_status = 0;
                    $code = "DSP";
                    $full_code = "DISPATCHED";
                }
                if ($pointFinishTime) {
                    \Log::debug("IF point->finish_time". $pointFinishTime);
                    $request_status = 0;
                    $code = "CON";
                    $full_code = "CONCLUDED";
                }
            } 
            else {
                $request_status = 1;
                $code = "CAN";
                $full_code = "CANCELLED";
            }
            if (isset($request_status) && isset($code) && $code !='') {
                $order->request_status    = $request_status;
                $order->code              = $code;
                $order->full_code         = $full_code;
                $order->update();
            }
        }

        return $order;
    }
}