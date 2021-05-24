<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Shops extends Model
{
    protected $table = 'shops';
    protected $fillable = ['name'];
    protected $dates = [ 'created_at', 'updated_at', 'deleted_at'];

    public function getConfig()
    {
        return $this->hasMany('App\MarketConfig', 'shop_id');
    }
}
