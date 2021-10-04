<?php

namespace Codificar\MarketplaceIntegration\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MerchantDetails extends Model
{
    use SoftDeletes;
    protected $table = 'mkt_merchant_details';
    protected $fillable = [
        'shop_id',
        'name',
        'merchant_id',
        'type',
        'latitude',
        'longitude',
        'address',
    ];
    protected $dates = [ 'created_at', 'updated_at', 'deleted_at'];

    public function getMarketplace(){
        return MarketplaceFactory::createMarketplace($this->type);
    }
}
