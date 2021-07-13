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
        'customerId',
        'streetName',
        'streetNumber',
        'formattedAddress',
        'neighborhood',
        'postalCode',
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
