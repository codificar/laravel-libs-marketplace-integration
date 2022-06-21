<?php

namespace Codificar\MarketplaceIntegration\Lib;

use Codificar\MarketplaceIntegration\Models\MarketConfig;
use Codificar\MarketplaceIntegration\Lib\HubsterApi;
use Codificar\MarketplaceIntegration\Lib\MarketplaceFactory;
use Codificar\MarketplaceIntegration\Models\DeliveryAddress;
use Codificar\MarketplaceIntegration\Models\OrderDetails;
use Codificar\MarketplaceIntegration\Models\Shops;
use Codificar\MarketplaceIntegration\Repositories\MarketplaceRepository;

use Carbon\Carbon;

use Location\Coordinate;
use Location\Distance\Vincenty;

class HubsterLib
{

    private $api ;

    public function __construct(){
        #TODO ter settings proprias ao inves de usar a do projeto pai
        $clientId          =  \Settings::findByKey('hubster_client_id', 'f0d58c67-646f-495f-b5ae-9bde99b37a2c');
        $clientSecret      =  \Settings::findByKey('hubster_client_secret', 'WLRADY3XT2IMUHEE4ENA');

        $this->api = new HubsterApi;

        $expiryToken  =  \Settings::findByKey('hubster_expiry_token');
        if (($clientId && $clientSecret) && ($expiryToken == NULL || Carbon::parse($expiryToken) < Carbon::now())) {
            $this->api->auth($clientId, $clientSecret);
        }
    }
    
    public function newOrders()
    {
        $markets = MarketConfig::where('market', MarketplaceFactory::HUBSTER)->get();
        $orderArray = [] ;
        foreach ($markets as $market) {
            $this->api->setStoreId($market->store_id);

            $response   = $this->api->newOrders();

            if ($response) {
                foreach ($response as $key => $value) {
                }
            }
        }

        return $orderArray ;
    }

    public function orderDetails($orderId)
    {

    }

    public function dispatch($orderId){
        return $this->api->dispatch($orderId);
    }

    public function webhook($request)
    {
        $json = $request->json()->all();

        \Log::debug('jsons');
        \Log::debug($json);
        $storeId  = $json['metadata']['storeId'] ;
        $payload  = $json['metadata']['payload'] ; 

        switch ($json['eventType']) {
            case 'orders.new_order' :
                

                $order = $this->orderFromPayload($storeId, $payload);

                break ;

            case 'delivery.request_quote' :


                break ;
            default: 
                break;
        }

        return $json;
    }

    /**
     * Save order from payload.
     * event order.create
     */
    public function orderFromPayload($storeId , $payload) {
        $external = $payload['externalIdentifiers'] ;
        $customer = $payload['customer'] ;
        $delivery = $payload['deliveryInfo'] ;
        $total    = $payload['orderTotal'] ;
        $totalV2  = $payload['orderTotalV2'] ;

        if($totalV2) {
            $prePaid = $totalV2['customerPayment']['customerPrepayment'] ;
        }
        else {
            $prePaid = 0;
        }

        if($payload['customerPayments']) {
            $payment  = $payload['customerPayments'][0] ;
            $paymentMethod = $payment['paymentMethod'];
            $paymentChange = $payment['value'] - $total['total'] ;
        }
        else {
            $paymentMethod = 'CASH' ;
            $paymentChange = 0 ;
        }

        if(isset($customer['personalIdentifiers']['taxIdentificationNumber'] )){
            $customerId = $customer['personalIdentifiers']['taxIdentificationNumber'] ;
        }
        else {
            $customerId = $customer['phone'];
        }

        $marketConfig = MarketConfig::where('merchant_id', $storeId)->first();

        $order = OrderDetails::updateOrCreate([
            'order_id'                          => $external['id']
            ],[
                'shop_id'                       => ($marketConfig ? $marketConfig->shop_id : null),
                'order_id'                      => $external['id'],
                'marketplace_order_id'          => $external['id'],
                'code'                          => MarketplaceRepository::CONFIRMED,
                'full_code'                     => MarketplaceRepository::mapFullCode(MarketplaceRepository::CONFIRMED),
                'created_at_marketplace'        => Carbon::parse($payload['orderedAt']),
                'point_id'                      => null,
                'request_id'                    => null,
                'client_name'                   => $customer['name'] ,
                'merchant_id'                   => $storeId,
                'marketplace'                   => $external['source'],
                'aggregator'                    => MarketplaceFactory::HUBSTER,
                'order_type'                    => MarketplaceRepository::DELIVERY,
                'display_id'                    => $external['friendlyId'],
                'preparation_start_date_time'   => null,
                'customer_id'                   => $customerId ,
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

        $calculatedDistance = ($marketConfig ? $marketConfig->calculateDistance(new Coordinate($delivery['destination']['location']['latitude'], $delivery['destination']['location']['longitude'])) : 0);

        $address = self::parseAddress($delivery['destination']['fullAddress']) ;

        $address = DeliveryAddress::updateOrCreate([
            'order_id'                      => $external['id']
        ],[
            'customer_id'                   => $customer['personalIdentifiers']['taxIdentificationNumber'],
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

        return $order;
    }

  
    /**
     * Function to parse addres
     * @return array with street_name, neighborhood, zipcode, street_number
     */
    public static function parseAddress($srcAddress){
	
        preg_match(
            "/([A-Za-z_ ]*)(.*),([A-Za-z_ ]*),([A-Za-z_ ]*)([0-9]*)(-([0-9]{4})){0,1}/",
            $srcAddress,
            $matches
        );

        list($original, $name, $street, $city, $state, $zipcode) = $matches;
        list($number, $neighborhood) = explode(' ', $street);

        $return = [
			'street_name' 		=> $street ,
			'neighborhood' 		=> $neighborhood ,
			'zipcode' 			=> $zipcode ,
			'street_number' 	=> $number
		];

		return $return ;
	}
}