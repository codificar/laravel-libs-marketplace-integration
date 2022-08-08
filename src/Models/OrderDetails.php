<?php

namespace Codificar\MarketplaceIntegration\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

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
        'created_at_marketplace',
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
        return $this->hasOne(DeliveryAddress::class, 'order_id', 'order_id');
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
     * Get the ride that owns the reques_id.
     * @return Requests
     */
    public function ride()
    {
        return $this->hasOne('Requests', 'id', 'request_id');
    }

    /**
     * Get the request point that owns the point_id.
     * @return RequestPoint
     */
    public function point()
    {
        return $this->hasOne('RequestPoint', 'id', 'point_id');
    }

    /**
     * Get the address associated with the market_config.
     * @return string market_address
     */
    public function getMarketFormattedAddressAttribute()
    {
        if ($this->market && $this->market->marketplace_address) {
            $decoded = this->market->marketplace_address;

            return sprintf('%s - %s', $decoded->street, $decoded->district);
        }
    }

    /**
     * Get the factory string.
     * @return string marketplace factiro
     */
    public function getFactoryAttribute()
    {
        if ($this->aggregator) {
            return $this->aggregator;
        }

        return $this->marketplace;
    }

    /**
     * Get the shop name string.
     * @return string shop name
     */
    public function getShopNameAttribute()
    {
        if ($this->shop) {
            return $this->shop->name;
        }

        return null;
    }

    /**
     * Get the market name string.
     * @return string market name
     */
    public function getMarketNameAttribute()
    {
        if ($this->market && isset($this->market->name)) {
            return $this->market->name;
        }

        return null;
    }

    /**
     * Get the address associated with the order.
     * @return DeliveryAddress
     */
    public function deliveryAddress()
    {
        return DeliveryAddress::where('order_id', $this->order_id)->where('customer_id', $this->customer_id)->last();
    }

    /**
     * Get the delivery status for hubster marketplaces.
     * @return string delivery status "REQUESTED" "ALLOCATED" "PICKED_UP" "COMPLETED" "CANCELED" "ARRIVED_AT_PICKUP"
     */
    public function getDeliveryStatusAttribute()
    {
        if ($this->point_id && isset($this->point)) {
            return $this->point->delivery_status;
        }

        return 'REQUESTED';
    }

    /**
     * get estimatedDeliveryTime.
     * @return Carbon estimatedDeliveryTime
     */
    public function getEstimatedDeliveryTimeAttribute()
    {
        $carbon = Carbon::parse($this->created_at_marketplace);
        $estimateTime = 15;
        if ($this->ride) {
            $estimateTime = $this->ride->estimate_time;
        }

        return $carbon->addMinutes($estimateTime);
    }

    /**
     * get estimatedPickupTime.
     * @return Carbon estimatedPickupTime
     */
    public function getEstimatedPickupTimeAttribute()
    {
        $carbon = Carbon::parse($this->created_at_marketplace);
        $estimateTime = 5;

        return $carbon->addMinutes($estimateTime);
    }
}
