<?php

namespace Codificar\MarketplaceIntegration\Models;

use Illuminate\Database\Eloquent\Model;

class MarketConfig extends Model
{
    protected $table = 'market_config';
    protected $fillable = [
        'shop_id',
        'name',
        'merchant_id',
        'market',
        'client_id',
        'client_secret',
        'latitude',
        'longitude',
        'address',
        'token',
        'expiry_token'
    ];
    protected $dates = [ 'created_at', 'updated_at', 'deleted_at'];

    /**
     * Get the store_id that is the same of merchant id string
     * @return string store id for hubster
     */
    public function getStoreIdAttribute(){
        return $this->merchant_id;
    }
}
