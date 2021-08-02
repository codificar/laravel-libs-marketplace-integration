<?php

namespace Codificar\MarketplaceIntegration\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DeliveryAddress extends Model
{
    use SoftDeletes;
    
    protected $table = 'delivery_address';
    protected $fillable = [
        'order_id',
        'customer_id',
        'street_name',
        'street_number',
        'formatted_address',
        'neighborhood',
        'postal_code',
        'city',
        'state',
        'country',
        'latitude',
        'longitude',
        'distance'
    ];
    protected $dates = [
        'deleted_at',
        'created_at',
        'updated_at'
    ];
}
