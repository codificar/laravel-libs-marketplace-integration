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
//use App\Models\LibSettings;

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
                        'created_at_marketplace'        => $json['metadata']['payload']['orderedAt'],
                        'point_id'                      => null,
                        'request_id'                    => null,
                        'client_name'                   => $customer['name'] ,
                        'merchant_id'                   => $storeId,
                        'created_at_marketplace'        => $json['metadata']['payload']['orderedAt'],
                        'marketplace'                   => MarketplaceFactory::IFOOD,
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

                $calculatedDistance = 0 ;

                if($marketConfig) {
                    #TODO mudar calculo de distancia para lib PHP ao inves de consultar banco
                    $diffDistance = \DB::select( \DB::raw(
                        "SELECT ST_Distance_Sphere(ST_GeomFromText('POINT(".$marketConfig->longitude." ".$marketConfig->latitude.")'), ST_GeomFromText('POINT(".$response->delivery->deliveryAddress->coordinates->longitude." ".$response->delivery->deliveryAddress->coordinates->latitude.")')) AS diffDistance"
                    ));
                    \Log::debug("DISTANCE: ".print_r($diffDistance,1));
                    $calculatedDistance = $diffDistance[0]->diffDistance ;
                }

                $complement = property_exists($response->delivery->deliveryAddress,'complement') ? $response->delivery->deliveryAddress->complement : null;
                if(!$complement && property_exists($response->delivery->deliveryAddress,'reference')) 
                    $complement = $response->delivery->deliveryAddress->reference;
                elseif($complement && property_exists($response->delivery->deliveryAddress,'reference'))
                    $complement = $complement . ' - ' . $response->delivery->deliveryAddress->reference;

                $address = DeliveryAddress::updateOrCreate([
                    'order_id'                      => $response->id
                ],[
                    'customer_id'                   => $response->customer->id,
                    'stree_name'                    => $response->delivery->deliveryAddress->streetName,
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

                return $order;

            default: 
                break;
        }
        return $json;
    }
}