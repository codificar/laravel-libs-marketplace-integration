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
        $clientId          =  \Settings::findByKey('hubster_client_id');
        $clientSecret      =  \Settings::findByKey('hubster_client_secret');

        $this->api = new HubsterApi;

        $expiryToken  =  \Settings::findByKey('hubster_expiry_token');
        if (($clientId && $clientSecret) && ($expiryToken == NULL || Carbon::parse($expiryToken) < Carbon::now())) {
            $this->api->auth($clientId, $clientSecret);
        }
    }
    
    public function newOrders()
    {
       
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
        switch ($json['eventType']) {
            case 'orders.new_order' :
                $storeId  = $json['metadata']['storeId'] ;
                $external = $json['metadata']['payload']['externalIdentifiers'] ;
                $customer = $json['metadata']['payload']['customer'] ;
                $delivery = $json['metadata']['payload']['deliveryInfo'] ;
                $total    = $json['metadata']['payload']['orderTotal'] ;
                $totalV2  = $json['metadata']['payload']['orderTotalV2'] ;
                $payment  = $json['metadata']['payload']['customerPayments'][0] ;

                $marketConfig = MarketConfig::where('merchant_id', $storeId)->first();

                $order = OrderDetails::updateOrCreate([
                    'order_id'                      => $external['id']
                    ],[
                        'shop_id'                       => ($marketConfig ? $marketConfig->shop_id : null),
                        'order_id'                      => $external['id'],
                        'marketplace_order_id'          => $external['id'],
                        'code'                          => MarketplaceRepository::CONFIRMED,
                        'full_code'                     => MarketplaceRepository::mapFullCode(MarketplaceRepository::CONFIRMED),
                        'created_at_marketplace'        => Carbon::parse($json['metadata']['payload']['orderedAt']),
                        'point_id'                      => null,
                        'request_id'                    => null,
                        'client_name'                   => $customer['name'] ,
                        'merchant_id'                   => $storeId,
                        'marketplace'                   => $external['source'],
                        'aggregator'                    => MarketplaceFactory::HUBSTER,
                        'order_type'                    => null,
                        'display_id'                    => $external['friendlyId'],
                        'preparation_start_date_time'   => null,
                        'customer_id'                   => $customer['personalIdentifiers']['taxIdentificationNumber'] ,
                        'sub_total'                     => $total['subtotal'],
                        'delivery_fee'                  => $total['deliveryFee'],
                        'benefits'                      => 0,
                        'order_amount'                  => $total['total'],
                        'method_payment'                => $payment['paymentMethod'],
                        'prepaid'                       => $totalV2['customerPayment']['customerPrepayment'],
                        'change_for'                    => $payment['paymentMethod'] == 'CASH' ? ($payment['value'] - $total['total']) : null,
                        'card_brand'                    => null,
                        'extra_info'                    => null
                    ]
                );

                $calculatedDistance = ($order->shop ? $order->shop->calculateDistance(new Coordinate($delivery['destination']['location']['latitude'], $delivery['destination']['location']['longitude'])) : 0);

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

            default: 
                break;
        }

        return $json;
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