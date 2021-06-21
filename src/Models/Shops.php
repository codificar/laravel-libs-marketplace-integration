<?php

namespace Codificar\MarketplaceIntegration\Models;

use Illuminate\Database\Eloquent\Model;

class Shops extends Model
{
    protected $table = 'shops';
    protected $fillable = [
        'merchant_id',
        'name', 
        'institution_id',
        'status_reload'
    ];
    protected $dates = [ 'created_at', 'updated_at', 'deleted_at'];

    public function getConfig()
    {
        return $this->hasMany('Codificar\MarketplaceIntegration\Models\MarketConfig', 'shop_id');
    }
}
