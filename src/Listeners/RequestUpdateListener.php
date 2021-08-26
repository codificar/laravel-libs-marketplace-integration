<?php

namespace Codificar\MarketplaceIntegration\Listeners;

use App\Events\RequestUpdate;
use Codificar\MarketplaceIntegration\Models\OrderDetails;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Codificar\MarketplaceIntegration\Http\Controllers\IFoodController;

class RequestUpdateListener implements ShouldQueue
{

    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the request.
     *
     * @param  object  $request
     * @return void
     */
    public function handle(RequestUpdate $request)
    {
        $data = $request->broadcastWith();
        \Log::debug("ID Data: ".json_encode($data,1));
        \Log::debug("ID Request: ".json_encode($request,1));
        \Log::debug("Data Points: ".json_encode($data['points'],1));
        $order = new IFoodController();
        foreach ($data['points'] as $key => $value) {
            \Log::debug('Value: '.print_r($value, 1));
            $res = $order->updateOrderRequestListener($value, $request->request->is_cancelled);
        }
    }

    /**
     * Determine whether the listener should be queued.
     *
     * @param  \App\Events\RequestUpdate  $request
     * @return bool
     */
    public function shouldQueue(RequestUpdate $request)
    {
        $order = OrderDetails::where('request_id', '=', $request->request->id)->get();
        // \Log::debug("Order: ".print_r($order, 1));
        if (!$order->isEmpty()) {
            // \Log::debug("TRUE");
            return TRUE;
        }
        return FALSE;
    }
}