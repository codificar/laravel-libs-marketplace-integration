<?php

namespace Codificar\MarketplaceIntegration\Listeners;

use App\Events\RequestUpdate;
use Codificar\MarketplaceIntegration\Repositories\MarketplaceRepository;
use Illuminate\Contracts\Queue\ShouldQueue;

class MarketplaceRideListener implements ShouldQueue
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
     * @param object $request
     * @return void
     */
    public function handle(RequestUpdate $request)
    {
        $data = $request->broadcastWith();

        \Log::debug('MarketplaceRideListener > data ' . json_encode($data, 1));

        foreach ($data['points'] as $key => $point) {
            MarketplaceRepository::updateOrder($point->request_id, $point->id, $point->start_time, $point->finish_time, $data['is_cancelled']);
        }
    }

    /**
     * Determine whether the listener should be queued.
     *
     * @param \App\Events\RequestUpdate $request
     * @return bool
     */
    public function shouldQueue(RequestUpdate $request)
    {
        $data = $request->broadcastWith();
        if (! empty($data)) {
            \Log::debug(__FUNCTION__ . '::data inst empty');

            return true;
        }

        return false;
    }
}
