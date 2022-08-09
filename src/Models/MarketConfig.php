<?php

namespace Codificar\MarketplaceIntegration\Models;

use Illuminate\Database\Eloquent\Model;
use Location\Coordinate;

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
    protected $dates = ['created_at', 'updated_at', 'deleted_at'];

    /**
     * Get the market_config that owns the merchant_id.
     * @return Shops
     */
    public function shop()
    {
        return $this->hasOne(Shops::class, 'id', 'shop_id');
    }

    /**
     * Get the store_id that is the same of merchant id string.
     * @return string store id for hubster
     */
    public function getStoreIdAttribute()
    {
        return $this->merchant_id;
    }

    /**
     * Get the decoded address.
     * @return object Address
     */
    public function getMarketplaceAddressAttribute()
    {
        if ($this->address) {
            return json_decode($this->address);
        }

        return null;
    }

    /**
     * Get distance from the shop to the destination.
     * @return float distance
     */
    public function calculateDistance(Coordinate $destination)
    {
        $distance = 0;

        if ($this->shop) {
            $distance = $this->shop->calculateDistance($destination);
        }

        return $distance;
    }
}
