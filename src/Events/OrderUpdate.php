<?php

namespace Codificar\MarketplaceIntegration\Events;

use Codificar\MarketplaceIntegration\Http\Controllers\IFoodController;
use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class OrderUpdate implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $request;
    public $order;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($request)
    {
        $this->request = $request;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn() {         
        $ifood = new IFoodController();
        $ifood->updateOrderRequest($this->request);
        return new Channel('order');
    }

    public function breadcastWith()
    {
        return $this->request->toArray();
    }

    public function broadcastAs()
    {
        return 'update';
    }
}
