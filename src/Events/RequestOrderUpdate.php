<?php

namespace Codificar\MarketplaceIntegration\Events;

use Codificar\MarketplaceIntegration\Models\OrderDetails;
use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class RequestOrderUpdate implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $order;
    public $request;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(OrderDetails $order)
    {
        $this->order = $order;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn() {                       
        return new Channel('order');
    }

    public function breadcastWith()
    {
        return $this->order->toArray();
    }

    public function broadcastAs()
    {
        return 'update';
    }
}
