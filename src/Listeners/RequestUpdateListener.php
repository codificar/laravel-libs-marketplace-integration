<?php

namespace Codificar\MarketplaceIntegration\Listeners;

use App\Events\RequestUpdate;
use Illuminate\Contracts\Queue\ShouldQueue;
use Codificar\MarketplaceIntegration\Repositories\MarketplaceRepository;


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
        
        foreach ($data['points'] as $key => $point) {
            MarketplaceRepository::updateOrder($point->request_id, $point->id, $point->start_time, $point->finish_time, $request->request->is_cancelled);
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
        $data = $request->broadcastWith();
        if (!empty($data)) {
            \Log::debug(__FUNCTION__.'::data inst empty');
            return true;
        }
        return false;
    }
}