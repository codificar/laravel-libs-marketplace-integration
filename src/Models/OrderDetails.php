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
        'fullCode',
        'code',
        'ifoodId',
        'orderType',
        'displayId',
        'createdAt',
        'preparationStartDateTime',
        'merchantId',
        'customerId',
        'subtotal',
        'deliveryFee',
        'benefits',
        'orderAmount',
        'paymentsId'
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
