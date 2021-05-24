<?php

namespace Codificar\MarketplaceIntegration\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DeliveryAddress extends Model
{
    use SoftDeletes;
    
    protected $table = 'delivery_address';
    protected $fillable = [
        'customerId',
        'streetName',
        'streetNumber',
        'formattedAddress',
        'neighborhood',
        'postalCode',
        'city',
        'state',
        'country',
        'coordinates'
    ];
    protected $dates = [
        'deleted_at',
        'created_at',
        'updated_at'
    ];
}
