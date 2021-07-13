<?php

namespace Codificar\MarketplaceIntegration\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PaymentOrder extends Model
{
    use SoftDeletes;
    protected $table = 'payment_order';
    protected $fillable = [
        'order_id',
        'prepaidValue',
        'pending',
        'value',
        'currency',
        'method',
        'type',
        'changeFor',
        'prepaid'
    ];
    protected $dates = [
        'deleted_at',
        'created_at',
        'updated_at'
    ];
}
