<?php

namespace Codificar\MarketplaceIntegration\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MerchantDetails extends Model
{
    use SoftDeletes;
    protected $table = 'merchant_details';
    protected $fillable = [
        'shop_id',
        'name',
        'merchant_id',
        'type',
        'client_id',
        'client_secret',
        'latitude',
        'longitude',
        'address',
        'token',
        'expiry_token'
    ];
    protected $dates = [ 'created_at', 'updated_at', 'deleted_at'];


}
