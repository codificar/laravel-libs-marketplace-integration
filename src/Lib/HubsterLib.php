<?php

namespace Codificar\MarketplaceIntegration\Lib;

use App\Models\RequestPoint;
use Carbon\Carbon;
use Codificar\MarketplaceIntegration\Models\DeliveryAddress;
use Codificar\MarketplaceIntegration\Models\MarketConfig;
use Codificar\MarketplaceIntegration\Models\OrderDetails;
use Codificar\MarketplaceIntegration\Repositories\MarketplaceRepository;
use Location\Coordinate;

class HubsterLib
{
    private $api;

    public function __construct()
    {
        //TODO ter settings proprias ao inves de usar a do projeto pai
        $clientId = \Settings::findByKey('hubster_client_id', 'c8f9a164-ac52-486f-bb85-74c3c7cc0518');
        $clientSecret = \Settings::findByKey('hubster_client_secret', 'CGX3I3RXL5IUDLP2ZHKA');

        $this->api = new HubsterApi;

        $expiryToken = \Settings::findByKey('hubster_expiry_token');
        if (($clientId && $clientSecret) && ($expiryToken == null || Carbon::parse($expiryToken) < Carbon::now())) {
            $this->api->auth($clientId, $clientSecret);
        }
    }

    public function newOrders()
    {
        $markets = MarketConfig::where('market', MarketplaceFactory::HUBSTER)->get();
        //var_dump($markets);
        $orderArray = [];
        foreach ($markets as $market) {
            $this->api->setStoreId($market->store_id);

            $response = $this->api->newOrders();

            if ($response && $response->orders) {
                foreach ($response->orders as $key => $order) {
                    $payload = json_decode(json_encode($order), true);
                    $orderDetail = $this->orderCreateFromPayload($market->store_id, $payload);
                    $orderArray[] = $orderDetail;
                }
            }
        }

        return $orderArray;
    }

    /**
     * Treat hubster webhooks
     * events order.create.
     * @param FormRequest $request
     */
    public function webhook($request)
    {
        $json = $request->json()->all();

        \Log::debug('jsons');
        \Log::debug($json);
        $storeId = $json['metadata']['storeId'];
        $payload = $json['metadata']['payload'];

        switch ($json['eventType']) {
            case 'orders.new_order' :

                $orderDetail = $this->orderCreateFromPayload($storeId, $payload);

                break;

            case 'delivery.request_quote' :

                $deliveryReferenceId = $payload['deliveryReferenceId'];
                $eventId = $json['eventId'];
                $arrPoints = $this->pointsFromPayload($payload);

                $this->deliveryQuote($storeId, $arrPoints, $eventId, $deliveryReferenceId);

                break;

            case 'delivery.accept' :

                $deliveryReferenceId = $payload['deliveryReferenceId'];
                $eventId = $json['eventId'];
                $arrPoints = $this->pointsFromPayload($payload);

                $this->deliveryAccept($storeId, $arrPoints, $eventId, $deliveryReferenceId);

                break;
            default:
                break;
        }

        return $json;
    }

    /**
     * Function to treat delivery.accept event.
     * It will create a ride and dispatch for the delivery boys.
     */
    private function deliveryAccept($storeId, $arrPoints, $eventId, $deliveryReferenceId)
    {
        $marketConfig = MarketConfig::where('merchant_id', $storeId)->where('market', MarketplaceFactory::HUBSTER)->first();
        if (is_array($arrPoints) && $marketConfig) {
        }
    }

    /**
     * Function to treat delivery.request_quote event.
     */
    private function deliveryQuote($storeId, $arrPoints, $eventId, $deliveryReferenceId)
    {
        $marketConfig = MarketConfig::where('merchant_id', $storeId)->where('market', MarketplaceFactory::HUBSTER)->first();

        if (is_array($arrPoints) && $marketConfig) {
            $locations = $this->getLocationsRequestPoints($arrPoints);
            $institutionId = $marketConfig->shop->institution_id;
            $providerType = DispatchRepository::getProviderType($institutionId);
            $estimate = EstimateService::estimatePriceTable($locations, $providerType, null, null, $institutionId, null, false, null, null);

            $notifyData = $this->getNotifyPayload($estimate, $marketConfig->shop->institution->getLedger()->getBalance());

            return $this->api->notifyDeliveryQuote($eventId, $deliveryReferenceId, $notifyData);
        }

        return null;
    }

    /**
     * Get RequestPoints from payload
     * event delivery.request_quote.
     * @return RequestPoints[]
     */
    private function pointsFromPayload($payload)
    {
        $requestPoints = [];

        // collect point
        if (isset($payload['pickupAddress'])) {
            $requestPoint = $this->getPointFromAddress($payload['pickupAddress']);
            $requestPoint->title = 'A';
            $requestPoint->action_type = RequestPoint::action_collect_order;
            $requestPoint->action = trans('marketplace-integration::zedelivery.action_collect_order');
            $requestPoints[] = $requestPoint;
        }

        // drop point
        if (isset($payload['dropoffAddress'])) {
            $requestPoint = $this->getPointFromAddress($payload['dropoffAddress']);
            $requestPoint->title = 'B';
            $requestPoint->action_type = RequestPoint::action_delivery_order;
            $requestPoint->action = trans('marketplace-integration::zedelivery.action_delivery_order', ['orderId' => $requestPoint->title]);
            $requestPoints[] = $requestPoint;
        }

        // // return
        // if (isset($payload['destinationAddress'])) {
        //     $requestPoint = $this->getPointFromAddress($payload['destinationAddress']);
        //     $requestPoint->title = '@';
        //     $requestPoint->action_type = RequestPoint::action_return;
        //     $requestPoint->action = trans('marketplace-integration::zedelivery.return_to_start');
        //     $requestPoints[] = $requestPoint;
        // }

        return $requestPoints;
    }

    /**
     * Get RequestPoint from payload Address.
     * @return RequestPoints
     */
    private function getPointFromAddress($address)
    {
        $requestPoint = new RequestPoint;

        $requestPoint->latitude = $address['location']['latitude'];
        $requestPoint->longitude = $address['location']['longitude'];
        $requestPoint->address = $address['fullAddress'];
        $requestPoint->start_time = Carbon::now();
        $requestPoint->arrival_time = Carbon::now();
        $requestPoint->finish_time = Carbon::now();

        return $requestPoint;
    }

    /**
     * get notify json object to send through api.
     */
    private function getNotifyPayload($estimate, $balance)
    {
        $jsonPayload = [
            'minPickupDuration' => $estimate['duration'] - 5,
            'maxPickupDuration' => $estimate['duration'] + 5,
            'deliveryDistance' => [
                'unit' => 'KILOMETERS',
                'value' => $estimate['estimated_distance']
            ],
            'currencyCode' => 'BRL',
            'cost' => [
                'baseCost'=> $estimate['estimated_price'],
                'extraCost'=> 0
            ],
            'provider'=> 'heyentregas',
            'fulfillmentPath' => [
                [
                    'name' => 'heyentregas',
                    'type'=> 'FULFILLMENT_PROCESSOR'
                ]
            ],
            'createdAt' => Carbon::now()->toAtomString(),
            'accountBalance' => 1068.32
        ];

        return $jsonPayload;
    }

    /**
     * Get location array from request points.
     * @return array
     */
    private function getLocationsRequestPoints($requestPoints)
    {
        $locations = [];

        foreach ($requestPoints as $point) {
            $location = $point->toArray();
            $location['geometry'] = [];
            $location['geometry']['location'] = [];
            $location['geometry']['location']['lat'] = $point->latitude;
            $location['geometry']['location']['lng'] = $point->longitude;
            $locations[] = $location;
        }

        return $locations;
    }

    /**
     * Save order from payload.
     * event order.create.
     */
    public function orderAcceptFromPayload($storeId, $payload)
    {
        $external = $payload['externalIdentifiers'];
        $customer = $payload['customer'];
        $delivery = $payload['deliveryInfo'];
        $total = $payload['orderTotal'];
        $totalV2 = $payload['orderTotalV2'];
        $orderId = $payload['pickupOrderId'];
        $displayId = $payload['ofoDisplayId'];
        $marketplace = $payload['ofoSlug'];

        if ($totalV2) {
            $prePaid = boolval($totalV2['customerPayment']['customerPrepayment']);
        } else {
            $prePaid = 0;
        }

        if ($payload['customerPayments']) {
            $payment = $payload['customerPayments'][0];
            $paymentMethod = $payment['paymentMethod'];
            $paymentChange = $payment['value'] - $total['total'];
        } else {
            $paymentMethod = 'CASH';
            $paymentChange = 0;
        }

        $customerId = null;
        $customerName = 'N/A';
        if ($customer) {
            $customerName = $customer['name'];
            $customerId = $customer['phone'];
        }

        if (isset($customer['personalIdentifiers']['taxIdentificationNumber'])) {
            $customerId = $customer['personalIdentifiers']['taxIdentificationNumber'];
        }

        $marketConfig = MarketConfig::where('merchant_id', $storeId)->where('market', MarketplaceFactory::HUBSTER)->first();

        $order = OrderDetails::updateOrCreate(
            [
                'order_id'                          => $orderId
            ],
            [
                'shop_id'                       => ($marketConfig ? $marketConfig->shop_id : null),
                'order_id'                      => $orderId,
                'marketplace_order_id'          => $orderId,
                'code'                          => MarketplaceRepository::CONFIRMED,
                'full_code'                     => MarketplaceRepository::mapFullCode(MarketplaceRepository::CONFIRMED),
                'created_at_marketplace'        => Carbon::parse($payload['orderedAt']),
                'point_id'                      => null,
                'request_id'                    => null,
                'client_name'                   => $customerName,
                'merchant_id'                   => $storeId,
                'marketplace'                   => $this->treatMarketplace($marketplace),
                'aggregator'                    => MarketplaceFactory::HUBSTER,
                'order_type'                    => MarketplaceRepository::DELIVERY,
                'display_id'                    => $displayId,
                'preparation_start_date_time'   => null,
                'customer_id'                   => $customerId,
                'sub_total'                     => $total['subtotal'],
                'delivery_fee'                  => $total['deliveryFee'],
                'benefits'                      => 0,
                'order_amount'                  => $total['total'],
                'method_payment'                => $paymentMethod,
                'prepaid'                       => $prePaid,
                'change_for'                    => $paymentChange,
                'card_brand'                    => null,
                'extra_info'                    => null
            ]
        );

        // somente salva o endereço se for de delivery, que é o que interessa para a plataforma
        if ($delivery && isset($delivery['destination'])) {
            $calculatedDistance = ($marketConfig ? $marketConfig->calculateDistance(new Coordinate($delivery['destination']['location']['latitude'], $delivery['destination']['location']['longitude'])) : 0);

            if ($delivery['destination']['addressLines']) {
                $address['street_name'] = $delivery['destination']['addressLines'][0];

                if (! isset($delivery['destination']['fullAddress'])) {
                    $delivery['destination']['fullAddress'] = $delivery['destination']['addressLines'][0];
                }
            }

            $address = DeliveryAddress::parseAddress($delivery['destination']['fullAddress']);

            $address = DeliveryAddress::updateOrCreate([
                'order_id'                      => $external['id']
            ], [
                'customer_id'                   => $customerId,
                'street_name'                   => $address['street_name'],
                'street_number'                 => $address['street_number'],
                'formatted_address'             => $delivery['destination']['fullAddress'],
                'neighborhood'                  => $address['neighborhood'],
                'complement'                    => $delivery['note'],
                'postal_code'                   => $delivery['destination']['postalCode'],
                'city'                          => $delivery['destination']['city'],
                'state'                         => $delivery['destination']['state'],
                'country'                       => $delivery['destination']['countryCode'],
                'latitude'                      => $delivery['destination']['location']['latitude'],
                'longitude'                     => $delivery['destination']['location']['longitude'],
                'distance'                      => $calculatedDistance,
            ]);
        }

        return $order;
    }

    /**
     * Save order from payload.
     * event order.create.
     */
    public function orderCreateFromPayload($storeId, $payload)
    {
        $external = $payload['externalIdentifiers'];
        $customer = $payload['customer'];
        $delivery = $payload['deliveryInfo'];
        $total = $payload['orderTotal'];
        $totalV2 = $payload['orderTotalV2'];

        if ($totalV2) {
            $prePaid = boolval($totalV2['customerPayment']['customerPrepayment']);
        } else {
            $prePaid = 0;
        }

        if ($payload['customerPayments']) {
            $payment = $payload['customerPayments'][0];
            $paymentMethod = $payment['paymentMethod'];
            $paymentChange = $payment['value'] - $total['total'];
        } else {
            $paymentMethod = 'CASH';
            $paymentChange = 0;
        }

        $customerId = null;
        $customerName = 'N/A';
        if ($customer) {
            $customerName = $customer['name'];
            $customerId = $customer['phone'];
        }

        if (isset($customer['personalIdentifiers']['taxIdentificationNumber'])) {
            $customerId = $customer['personalIdentifiers']['taxIdentificationNumber'];
        }

        $marketConfig = MarketConfig::where('merchant_id', $storeId)->where('market', MarketplaceFactory::HUBSTER)->first();

        $order = OrderDetails::updateOrCreate(
            [
                'order_id'                          => $external['id']
            ],
            [
                'shop_id'                       => ($marketConfig ? $marketConfig->shop_id : null),
                'order_id'                      => $external['id'],
                'marketplace_order_id'          => $external['id'],
                'code'                          => MarketplaceRepository::CONFIRMED,
                'full_code'                     => MarketplaceRepository::mapFullCode(MarketplaceRepository::CONFIRMED),
                'created_at_marketplace'        => Carbon::parse($payload['orderedAt']),
                'point_id'                      => null,
                'request_id'                    => null,
                'client_name'                   => $customerName,
                'merchant_id'                   => $storeId,
                'marketplace'                   => $this->treatMarketplace($external['source']),
                'aggregator'                    => MarketplaceFactory::HUBSTER,
                'order_type'                    => MarketplaceRepository::DELIVERY,
                'display_id'                    => $external['friendlyId'],
                'preparation_start_date_time'   => null,
                'customer_id'                   => $customerId,
                'sub_total'                     => $total['subtotal'],
                'delivery_fee'                  => $total['deliveryFee'],
                'benefits'                      => 0,
                'order_amount'                  => $total['total'],
                'method_payment'                => $paymentMethod,
                'prepaid'                       => $prePaid,
                'change_for'                    => $paymentChange,
                'card_brand'                    => null,
                'extra_info'                    => null
            ]
        );

        // somente salva o endereço se for de delivery, que é o que interessa para a plataforma
        if ($delivery && isset($delivery['destination'])) {
            $calculatedDistance = ($marketConfig ? $marketConfig->calculateDistance(new Coordinate($delivery['destination']['location']['latitude'], $delivery['destination']['location']['longitude'])) : 0);

            if ($delivery['destination']['addressLines']) {
                $address['street_name'] = $delivery['destination']['addressLines'][0];

                if (! isset($delivery['destination']['fullAddress'])) {
                    $delivery['destination']['fullAddress'] = $delivery['destination']['addressLines'][0];
                }
            }

            $address = DeliveryAddress::parseAddress($delivery['destination']['fullAddress']);

            $address = DeliveryAddress::updateOrCreate([
                'order_id'                      => $external['id']
            ], [
                'customer_id'                   => $customerId,
                'street_name'                   => $address['street_name'],
                'street_number'                 => $address['street_number'],
                'formatted_address'             => $delivery['destination']['fullAddress'],
                'neighborhood'                  => $address['neighborhood'],
                'complement'                    => $delivery['note'],
                'postal_code'                   => $delivery['destination']['postalCode'],
                'city'                          => $delivery['destination']['city'],
                'state'                         => $delivery['destination']['state'],
                'country'                       => $delivery['destination']['countryCode'],
                'latitude'                      => $delivery['destination']['location']['latitude'],
                'longitude'                     => $delivery['destination']['location']['longitude'],
                'distance'                      => $calculatedDistance,
            ]);
        }

        return $order;
    }

    /**
     * order details.
     * @return object
     */
    public function orderDetails($orderId)
    {
        return $this->api->orderDetails($orderId);
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
        // return $this->api->dispatchOrder($order->order_id);
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
        $data = [
            'source' =>  $order->marketplace,
            'orderId' => $order->order_id
        ];

        $this->api->setStoreId($order->merchant_id);

        return $this->api->cancelOrder($data);
    }

    /**
     * fullfillOrder order to api.
     * @return object
     */
    public function fullfillOrder($order)
    {
        $data = [
            'source' =>  $order->marketplace,
            'orderId' => $order->order_id
        ];

        $this->api->setStoreId($order->merchant_id);

        return $this->api->fullfillOrder($data);
    }

    /**
     * treatMarketplace to treat whats is the marketplace.
     * @return string
     */
    private function treatMarketplace($source)
    {
        if (strstr($source, 'ifood')) {
            $source = 'ifood';
        }

        return $source;
    }
}
