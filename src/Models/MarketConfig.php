<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class MarketConfig extends Model
{
    protected $table = 'market_config';
    protected $fillable = [
        'shop_id',
        'market',
        'client_id',
        'client_secret'
    ];
    protected $dates = [ 'created_at', 'updated_at', 'deleted_at'];


}
