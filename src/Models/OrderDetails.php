<?php

namespace Codificar\MarketplaceIntegration\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

use Codificar\MarketplaceIntegration\Models\MarketConfig ;
use Codificar\MarketplaceIntegration\Models\Shops ;

use Location\Coordinate;
use Location\Distance\Vincenty;

class OrderDetails extends Model
{
    
    use SoftDeletes;
    
    protected $table = 'order_detail';
    protected $fillable = [
        'request_id',
        'tracking_route',
        'request_status',
        'client_name',
        'shop_id',
        'merchant_id',
        'order_id',
        'full_code',
        'code',
        'marketplace_order_id',
        'marketplace',
        'aggregator',
        'order_type',
        'display_id',
        'preparation_start_date_time',
        'customer_id',
        'sub_total',
        'delivery_fee',
        'benefits',
        'order_amount',
        'payments_id',
        'method_payment',
        'change_for',
        'prepaid',
        'card_brand',
        'extra_info'
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = ['shop_name', 'market_name', 'factory'];


    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    /**
     * Get the address associated with the order.
     * @return DeliveryAddress
     */
    public function address()
    {
        return $this->hasOne('Codificar\MarketplaceIntegration\Models\DeliveryAddress', 'order_id', 'order_id');
    }
     
    /**
     * Get the market_config that owns the merchant_id.
     * @return MarketConfig
     */
    public function market()
    {
        return $this->belongsTo(MarketConfig::class, 'merchant_id', 'merchant_id');
    }


    /**
     * Get the market_config that owns the merchant_id.
     * @return Shops
     */
    public function shop()
    {
        return $this->hasOne(Shops::class, 'id', 'shop_id');
    }


    /**
     * Get the address associated with the market_config 
     * @return string market_address
     */
    public function getMarketFormattedAddressAttribute(){
        if($this->market_address){
            $decoded = json_decode($this->market_address);

            return sprintf('%s - %s', $decoded->street, $decoded->district);
        }
    }


    /**
     * Get the factory string
     * @return string marketplace factiro
     */
    public function getFactoryAttribute(){
        if($this->aggregator) return $this->aggregator;

        return $this->marketplace;
    }

    /**
     * Get the shop name string
     * @return string shop name
     */
    public function getShopNameAttribute(){
        if($this->shop) return $this->shop->name;

        return null;
    }

    /**
     * Get the market name string
     * @return string market name
     */
    public function getMarketNameAttribute(){
        if($this->market) return $this->market->name;

        return null;
    }

}
