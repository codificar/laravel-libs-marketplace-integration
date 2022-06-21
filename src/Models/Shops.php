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
        'status_reload',
        'token',
        'expiry_token'
    ];
    protected $dates = [ 'created_at', 'updated_at', 'deleted_at'];

    public function getConfig()
    {
        return $this->hasMany('Codificar\MarketplaceIntegration\Models\MarketConfig', 'shop_id');
    }

}
