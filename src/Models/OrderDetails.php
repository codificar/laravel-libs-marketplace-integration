<?php

namespace Codificar\MarketplaceIntegration\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class OrderDetails extends Model
{
    protected $table = 'mkt_orders_details';

    const ORDER_STATUS = ['CFM', 'RDA', 'DSP'];

    use SoftDeletes;
    
    protected $fillable = [
        'request_id',
        'tracking_route',
        'request_status',
        'client_name',
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
        'customer_id',
        'subtotal',
        'delivery_fee',
        'benefits',
        'order_amount',
        'payments_id',
        'method_payment',
        'change_for',
        'prepaid',
        'card_brand',
        'extra_info'
    ];
    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    public function getItems()
    {
        return $this->hasMany('Codificar\MarketplaceIntegration\Models\OrderItems', 'order_id', 'order_id');
    }

    public function merchant()
    {
        return $this->belongsTo('Codificar\MarketplaceIntegration\Models\MerchantDetails', 'merchant_id', 'merchant_id');
    }
}
