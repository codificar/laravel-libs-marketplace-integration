<?php

namespace Codificar\MarketplaceIntegration\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class OrderDetails extends Model
{
    use SoftDeletes;
    
    protected $table = 'order_detail';
    protected $fillable = [
        'request_id',
        'tracking_route',
        'request_status',
        'shop_id',
        'merchant_id',
        'order_id',
        'full_code',
        'code',
        'ifood_id',
        'order_type',
        'display_id',
        'createdAt',
        'preparation_start_date_time',
        'merchant_id',
        'customer_id',
        'subtotal',
        'delivery_fee',
        'benefits',
        'order_amount',
        'payments_id',
        'method_payment',
        'change_for',
        'prepaid',
        'card_brand'
    ];
    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    public function getAddress()
    {
        return $this->hasMany('Codificar\MarketplaceIntegration\Models\DeliveryAddress', 'order_id', 'order_id');
    }

    public function getItems()
    {
        return $this->hasMany('Codificar\MarketplaceIntegration\Models\OrderItems', 'order_id', 'order_id');
    }
}
