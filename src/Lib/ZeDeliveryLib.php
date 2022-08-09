<?php

namespace Codificar\MarketplaceIntegration\Lib;

use Carbon\Carbon;
use Codificar\MarketplaceIntegration\Models\DeliveryAddress;
use Codificar\MarketplaceIntegration\Models\MarketConfig;
use Codificar\MarketplaceIntegration\Models\OrderDetails;

class ZeDeliveryLib
{
    private $api;

    /**
     * Construct function.
     */
    public function __construct()
    {
        //TODO ter settings proprias ao inves de usar a do projeto pai
        $clientId = \Settings::findByKey('zedelivery_client_id');
        $clientSecret = \Settings::findByKey('zedelivery_client_secret');

        $this->api = new ZeDeliveryApi;

        $expiryToken = \Settings::findByKey('zedelivery_expiry_token');
        if ($expiryToken == null || Carbon::parse($expiryToken) < Carbon::now()) {
            $this->api->auth($clientId, $clientSecret);
        }
    }

    /**
     * Get new orders and save at order details database.
     */
    public function newOrders()
    {
        $response = $this->api->newOrders();

        \Log::debug('newOrders > response' . print_r($response, 1));

        if ($response) {
            foreach ($response as $key => $value) {
                $timestamp = strtotime($value->createdAt);
                $createdAt = date('Y-m-d H:i:s', $timestamp);

                $order = OrderDetails::updateOrCreate(
                    [
                        'order_id'       => $value->orderId,
                    ],
                    [
                        'order_id'                  => $value->orderId,
                        'code'                      => $value->code,
                        'full_code'                 => $value->fullCode,
                        'merchant_id'               => $value->merchantId,
                        'marketplace_order_id'      => $value->id,
                        'created_at_marketplace'    => $createdAt,
                        'marketplace'               => MarketplaceFactory::ZEDELIVERY
                    ]
                );

                \Log::debug('order' . $order);

                $acknowledgment = $this->api->acknowledgment($value);

                $this->orderDetails($value->orderId);
            }
        }
    }

    /**
     * Get the order detail from the marketplace api, needs to return Orde.
     * @return OrderDetail $order
     */
    public function orderDetails($orderId)
    {
        $response = $this->api->orderDetails($orderId);

        if ($response) {
            $marketConfig = MarketConfig::where('merchant_id', $response->merchant->id)->first();

            $timestamp = strtotime($response->createdAt);
            $createdAt = date('Y-m-d H:i:s', $timestamp);

            $timestamp = strtotime($response->preparationStartDateTime);
            $preparationStartDateTime = date('Y-m-d H:i:s', $timestamp);

            $order = OrderDetails::updateOrCreate(
                [
                    'order_id'                      => $response->id
                ],
                [
                    'shop_id'                       => ($marketConfig ? $marketConfig->shop_id : null),
                    'order_id'                      => $response->id,
                    'client_name'                   => $response->customer->name,
                    'merchant_id'                   => $response->merchant->id,
                    'created_at_marketplace'        => $createdAt,
                    'marketplace'                   => MarketplaceFactory::ZEDELIVERY,
                    'order_type'                    => $response->orderType,
                    'display_id'                    => $response->displayId,
                    'preparation_start_date_time'   => $preparationStartDateTime,
                    'customer_id'                   => $response->customer->id,
                    'sub_total'                     => $response->total->subTotal,
                    'delivery_fee'                  => $response->total->deliveryFee,
                    'benefits'                      => $response->total->benefits,
                    'order_amount'                  => $response->total->orderAmount,
                    'method_payment'                => $response->payments->methods[0]->method,
                    'prepaid'                       => $response->payments->methods[0]->prepaid,
                    'change_for'                    => $response->payments->methods[0]->method == 'CASH' ? $response->payments->methods[0]->cash->changeFor : '',
                    'card_brand'                     => $response->payments->methods[0]->method == 'CREDIT' ? $response->payments->methods[0]->card->brand : null,
                    'extra_info'                    => isset($response->extraInfo) ? $response->extraInfo : ''
                ]
            );

            if (isset($response->delivery)) {
                $calculatedDistance = 0;

                if ($marketConfig) {
                    //TODO mudar calculo de distancia para lib PHP ao inves de consultar banco
                    $diffDistance = \DB::select(\DB::raw(
                        "SELECT ST_Distance_Sphere(ST_GeomFromText('POINT(" . $marketConfig->longitude . ' ' . $marketConfig->latitude . ")'), ST_GeomFromText('POINT(" . $response->delivery->deliveryAddress->coordinates->longitude . ' ' . $response->delivery->deliveryAddress->coordinates->latitude . ")')) AS diffDistance"
                    ));
                    \Log::debug('DISTANCE: ' . print_r($diffDistance, 1));
                    $calculatedDistance = $diffDistance[0]->diffDistance;
                }

                $complement = property_exists($response->delivery->deliveryAddress, 'complement') ? $response->delivery->deliveryAddress->complement : '';
                if (! $complement && property_exists($response->delivery->deliveryAddress, 'reference')) {
                    $complement = $response->delivery->deliveryAddress->reference;
                } elseif ($complement && property_exists($response->delivery->deliveryAddress, 'reference')) {
                    $complement = $complement . ' - ' . $response->delivery->deliveryAddress->reference;
                }

                $address = DeliveryAddress::updateOrCreate([
                    'order_id'                      => $response->id
                ], [
                    'customer_id'                   => $response->customer->id,
                    'street_name'                    => $response->delivery->deliveryAddress->streetName,
                    'street_number'                 => $response->delivery->deliveryAddress->streetNumber,
                    'formatted_address'             => $response->delivery->deliveryAddress->formattedAddress,
                    'neighborhood'                  => $response->delivery->deliveryAddress->neighborhood,
                    'complement'                    => $complement,
                    'postal_code'                   => $response->delivery->deliveryAddress->postalCode,
                    'city'                          => $response->delivery->deliveryAddress->city,
                    'state'                         => $response->delivery->deliveryAddress->state,
                    'country'                       => $response->delivery->deliveryAddress->country,
                    'latitude'                      => $response->delivery->deliveryAddress->coordinates->latitude,
                    'longitude'                     => $response->delivery->deliveryAddress->coordinates->longitude,
                    'distance'                      => $calculatedDistance,
                ]);

                if (! $address) {
                    \Log::warning(__FUNCTION__ . '::Error to save Delivery Address: getOrderDetails response => ' . print_r($response));
                }
            } else {
                \Log::warning(__FUNCTION__ . '::Error to save Delivery Address: getOrderDetails without delivery data, see response => ' . print_r($response));
            }
        }
    }

    /**
     * Get the merchant detail from the marketplace api, needs to return alway.
     * @return array [code ; data ; message]
     */
    public function merchantDetails($merchantId)
    {
        return $this->api->merchantDetails($merchantId);
    }

    /**
     * Dispatch order to api.
     * @return object
     */
    public function dispatchOrder($order)
    {
        return $this->api->dispatchOrder($order->order_id);
    }

    /**
     * confirm order to api.
     * @return object
     */
    public function confirmOrder($order)
    {
        return $this->api->confirmOrder($order->order_id);
    }

    /**
     * Cancel order to api.
     * @return object
     */
    public function cancelOrder($order)
    {
        return $this->api->cancelOrder($order->order_id);
    }

    /**
     * fulfillOrder order to api.
     * @return object
     */
    public function fulfillOrder($order)
    {
        //return $this->api->fulfillOrder($cancelData);
    }
}
