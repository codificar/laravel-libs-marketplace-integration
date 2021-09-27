<?php

namespace Codificar\MarketplaceIntegration\Listeners;

use App\Events\RequestUpdate;
use Codificar\MarketplaceIntegration\Models\OrderDetails;
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
        $order = new IFoodController();
        foreach ($data['points'] as $key => $value) {
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
        $order = OrderDetails::where('request_id', '=', $request->request_id)->get();
        if (!$order->isEmpty()) {
            return TRUE;
        }
        return FALSE;
    }
}