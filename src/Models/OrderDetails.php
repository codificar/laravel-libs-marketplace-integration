<?php

namespace Codificar\MarketplaceIntegration\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class OrderDetails extends Model
{
    use SoftDeletes;
    
    protected $table = 'order_detail';
    protected $fillable = [
        'orderId',
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
}
