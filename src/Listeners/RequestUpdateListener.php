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
        \Log::debug("Listener: ". print_r($request->all(), 1));
        $order = new IFoodController();
        foreach ($request->points as $key => $value) {
            $res = $order->updateOrderRequestListener($value, $request);

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
        // \Log::debug("ID Request: ".$request->request->id);
        $order = OrderDetails::where('request_id', '=', $request->request->id)->get();
        // \Log::debug("Order: ".print_r($order, 1));
        if (!$order->isEmpty()) {
            // \Log::debug("TRUE");
            return TRUE;
        }
        return FALSE;
    }
}