<?php

namespace Codificar\MarketplaceIntegration\Models;

use Illuminate\Database\Eloquent\Model;

class Shops extends Model
{
    protected $table = 'mkt_shops';
    protected $fillable = [
        'id',
        'name', 
        'institution_id',
        'status_reload',
        'token',
    ];
    protected $dates = [ 'created_at', 'updated_at', 'deleted_at'];

    public function getConfig()
    {
        return $this->hasMany('Codificar\MarketplaceIntegration\Models\Merchant', 'shop_id');
    }
}
