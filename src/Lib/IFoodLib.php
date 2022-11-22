<?php

namespace Codificar\MarketplaceIntegration\Lib;

use Carbon\Carbon;
use Codificar\MarketplaceIntegration\Models\DeliveryAddress;
use Codificar\MarketplaceIntegration\Models\MarketConfig;
use Codificar\MarketplaceIntegration\Models\OrderDetails;
use Location\Coordinate;

class IFoodLib
{
    private $api;

    /**
     * Construct function.
     */
    public function __construct()
    {
        //TODO ter settings proprias ao inves de usar a do projeto pai
        $clientId = \Settings::findByKey('ifood_client_id');
        $clientSecret = \Settings::findByKey('ifood_client_secret');

        $this->api = new IFoodApi;

        $expiryToken = \Settings::findByKey('ifood_expiry_token');
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
                        'code'                      => $value->code,
                        'full_code'                 => $value->fullCode,
                        'merchant_id'               => $value->merchantId,
                        'marketplace_order_id'      => $value->id,
                        'created_at_marketplace'    => $createdAt,
                        'marketplace'               => MarketplaceFactory::IFOOD
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
                    'client_name'                   => $response->customer->name,
                    'merchant_id'                   => $response->merchant->id,
                    'created_at_marketplace'        => $createdAt,
                    'marketplace'                   => MarketplaceFactory::IFOOD,
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

                $calculatedDistance = ($marketConfig ? $marketConfig->calculateDistance(new Coordinate($response->delivery->deliveryAddress->coordinates->latitude, $response->delivery->deliveryAddress->coordinates->longitude)) : 0);

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

    /**
     * Retur if lib can polling.
     * @return bool
     */
    public function canPolling()
    {
        $clientId = \Settings::findByKey('ifood_client_id');
        $clientSecret = \Settings::findByKey('ifood_client_secret');

        return $clientId && $clientSecret && $clientId != '' && $clientSecret != '';
    }
}
