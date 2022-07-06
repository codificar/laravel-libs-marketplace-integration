<?php

namespace Codificar\MarketplaceIntegration\Models;

use Illuminate\Database\Eloquent\Model;

use Location\Coordinate;
use Location\Distance\Vincenty;

class Shops extends Model
{
    protected $table = 'shops';
    protected $fillable = [
        'id',
        'name', 
        'institution_id',
        'latitude',
        'longitude',
        'full_address',
        'status_reload',
        'token',
        'expiry_token'
    ];
    
    protected $dates = [ 'created_at', 'updated_at', 'deleted_at'];

     /**
     * Get the market_configs associations with the shpw.
     * @return MarketConfig
     */
    public function getConfig()
    {
        return $this->hasMany('Codificar\MarketplaceIntegration\Models\MarketConfig', 'shop_id');
    }

    /**
     * Get distance from the shop to the destination
     * @return float distance
     */
    public function calculateDistance(Coordinate $destination){
        $distance =  0 ;

        $calculator = new Vincenty();
        $distance = $calculator->getDistance(new Coordinate($this->latitude, $this->longitude), $destination);

        return $distance ;
    }

}
