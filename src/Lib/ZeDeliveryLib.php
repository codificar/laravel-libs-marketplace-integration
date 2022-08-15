<?php

namespace Codificar\MarketplaceIntegration\Lib;

use Codificar\MarketplaceIntegration\Models\DeliveryAddress;
use Codificar\MarketplaceIntegration\Models\MarketConfig;
use Codificar\MarketplaceIntegration\Models\OrderDetails;
use Codificar\MarketplaceIntegration\Repositories\ZeDeliveryRepository;
use Location\Coordinate;

class ZeDeliveryLib
{
    private $api;

    /**
     * Construct function.
     */
    public function __construct()
    {
        $this->api = new ZeDeliveryApi();
    }

    /**
     * Get new orders and save at order details database.
     */
    public function newOrders()
    {
        $this->api->setPollingMerchants(ZeDeliveryRepository::getMerchantIds());
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
                        'code'                      => ZeDeliveryRepository::mapCode($value->eventType),
                        'full_code'                 => ZeDeliveryRepository::mapFullCodeFromEvent($value->eventType),
                        'merchant_id'               => $value->merchantId,
                        'marketplace_order_id'      => $value->orderId,
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
            $marketConfig = MarketConfig::where('merchant_id', $response->merchant->id)->where('market', MarketplaceFactory::ZEDELIVERY)->first();

            $timestamp = strtotime($response->createdAt);
            $createdAt = date('Y-m-d H:i:s', $timestamp);

            $timestamp = strtotime($response->createdAt);
            $preparationStartDateTime = date('Y-m-d H:i:s', $timestamp);

            $order = OrderDetails::updateOrCreate(
                [
                    'order_id'                      => $response->displayId
                ],
                [
                    'shop_id'                       => ($marketConfig ? $marketConfig->shop_id : null),
                    'order_id'                      => $response->displayId,
                    'client_name'                   => $response->customer->name,
                    'merchant_id'                   => $response->merchant->id,
                    'created_at_marketplace'        => $createdAt,
                    'marketplace'                   => MarketplaceFactory::ZEDELIVERY,
                    'order_type'                    => $response->type,
                    'display_id'                    => $response->displayId,
                    'preparation_start_date_time'   => $preparationStartDateTime,
                    'customer_id'                   => $response->customer->documentNumber,
                    'sub_total'                     => $response->total->orderAmount->value,
                    'delivery_fee'                  => $response->total->otherFees->value,
                    'benefits'                      => $response->total->discount->value,
                    'order_amount'                  => $response->total->orderAmount->value,
                    'method_payment'                => $response->payments->methods[0]->method,
                    'prepaid'                       => $response->payments->methods[0]->type == 'PENDING' ? false : true,
                    'change_for'                    => $response->total->change->value,
                    'card_brand'                    => null,
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
                    'order_id'                      => $response->displayId
                ], [
                    'customer_id'                   => $response->customer->documentNumber,
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
        $clientId = \Settings::findByKey('zedelivery_client_id');
        $clientSecret = \Settings::findByKey('zedelivery_client_secret');
        $merchantIds = ZeDeliveryRepository::getMerchantIds();

        return $clientId && $clientSecret && $merchantIds && $clientId != '' && $merchantIds != '';
    }
}
