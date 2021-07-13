<?php

namespace Codificar\MarketplaceIntegration\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class OrderItems extends Model
{
    use SoftDeletes;
    protected $table = 'order_items';
    protected $filable = [
        'order_id', 
        'index',
        'itemId',
        'name',
        'externalCode',
        'unit',
        'quantity',
        'unitPrice',
        'optionsPrice',
        'totalPrice',
        'price',
        'options'
    ];
    protected $dates = [
        'deleted_at',
        'created_at',
        'updated_at'
    ];
}
