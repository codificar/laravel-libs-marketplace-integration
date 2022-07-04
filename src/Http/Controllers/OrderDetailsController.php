<?php

namespace Codificar\MarketplaceIntegration\Http\Controllers;

use App\Http\Controllers\Controller;
use Codificar\MarketplaceIntegration\Models\MarketConfig;
use Codificar\MarketplaceIntegration\Lib\MarketplaceFactory;
use Codificar\MarketplaceIntegration\Models\DeliveryAddress;
use Codificar\MarketplaceIntegration\Models\OrderDetails;
use Codificar\MarketplaceIntegration\Models\Shops;
use Illuminate\Http\Request;

use Codificar\MarketplaceIntegration\Http\Requests\OrderDetailsFormRequest;

use Codificar\MarketplaceIntegration\Repositories\MarketplaceRepository;
use Codificar\MarketplaceIntegration\Http\Resources\OrdersResource;



class OrderDetailsController extends Controller
{
   

    /**
     * Function to return orders from database
     * @return OrdersResource
     */
    public function getOrders(Request $request, $shopId = NULL)
    {
        
        $startTime = isset($request['range']) && $request['range'][0] != null ? $request['range'][0] : \Carbon\Carbon::now()->subDays(1);

        $endTime = isset($request['range']) && $request['range'][0] != null ? $request['range'][0] : null;

        $marketId = $request['marketId'] ;

        $orders =  MarketplaceRepository::getOrders($shopId, $marketId, $startTime, $endTime);

        return new OrdersResource($orders);
    }

    /**
     * Update a single order after the request was created
     */
    public function setRide(Request $request)
    {
        
        $order = OrderDetails::where([
            'order_id'                       => $request->order_id
        ])->update([
                'request_id'                => $request->request_id,
                'point_id'                  => $request->point_id,
                'tracking_route'            => $request->tracking_route,
        ]);

        $order = OrderDetails::where([
            'order_id' => $request->order_id
        ])->first();

        return $order;
    }

    /**
     * Confirm Order
     */
    public function confirm(OrderDetailsFormRequest $request)
    {

        $order = $request->order;
        $factory = MarketplaceFactory::create($order->factory);
        
        $result = $factory->confirmOrder($order->order_id);

        if($result) {
            $order->code                        = MarketplaceRepository::CONFIRMED;
            $order->full_code                   = MarketplaceRepository::mapFullCode(MarketplaceRepository::CONFIRMED);
            $order->save();
        }
    
        return $order;
    
    }

    /**
     * Cancel Order
     */
    public function cancel(OrderDetailsFormRequest $request)
    {

        $order = $request->order;
        $factory = MarketplaceFactory::create($order->factory);
        
        $result = $factory->cancelOrder($order->order_id);

        if($result) {
            $order->code                        = MarketplaceRepository::CANCELLED;
            $order->full_code                   = MarketplaceRepository::mapFullCode(MarketplaceRepository::CANCELLED);
            $order->save();
        }
    
        return $order;
    
    }


    /**
     * Dispatch Order
     */
    public function dispatchOrder(OrderDetailsFormRequest $request)
    {

        $order = $request->order;
        $factory = MarketplaceFactory::create($order->factory);
        
        $result = $factory->dispatchOrder($order);

        if($result) {
            $order->code                        = MarketplaceRepository::DISPATCHED;
            $order->full_code                   = MarketplaceRepository::mapFullCode(MarketplaceRepository::DISPATCHED);
            $order->save();
        }
    
        return $order;
    
    }
 
}
