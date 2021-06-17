<?php

namespace Codificar\MarketplaceIntegration\Models;

use Illuminate\Database\Eloquent\Model;

class MarketConfig extends Model
{
    protected $table = 'market_config';
    protected $fillable = [
        'shop_id',
        'market',
        'client_id',
        'client_secret',
        'latitude',
        'longitude'
    ];
    protected $dates = [ 'created_at', 'updated_at', 'deleted_at'];


}
