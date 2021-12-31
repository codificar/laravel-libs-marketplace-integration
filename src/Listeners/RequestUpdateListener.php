<?php

namespace Codificar\MarketplaceIntegration\Listeners;

use App\Events\RequestUpdate;
use Codificar\MarketplaceIntegration\Models\OrderDetails;
use Illuminate\Contracts\Queue\ShouldQueue;
use Codificar\MarketplaceIntegration\Http\Controllers\MarketplaceController;

class RequestUpdateListener implements ShouldQueue
{
    /**
     * Handle the request.
     * @author Diogo C. Coutinho
     *
     * @param  object  $request
     * @return void
     */
    public function handle(RequestUpdate $request)
    {
        $data = $request->broadcastWith();
        $order = new MarketplaceController();
        foreach ($data['points'] as $key => $value) {
            $res = $order->updateOrderRequestListener($value, $request->request->is_cancelled);
        }
    }

    /**
     * Determine whether the listener should be queued.
     * @author Diogo C. Coutinho
     *
     * @param  \App\Events\RequestUpdate  $request
     * @return bool
     */
    public function shouldQueue(RequestUpdate $request)
    {
        $data = $request->broadcastWith();
        if (!empty($data)) {
            \Log::debug(__FUNCTION__.'::data inst empty');
            return true;
        }
        return false;
    }
}