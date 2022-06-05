<?php

namespace Codificar\MarketplaceIntegration\Lib;

use Codificar\MarketplaceIntegration\Models\MarketConfig;
use Codificar\MarketplaceIntegration\Lib\IFoodApi;
use Codificar\MarketplaceIntegration\Models\DeliveryAddress;
use Codificar\MarketplaceIntegration\Models\OrderDetails;
use Codificar\MarketplaceIntegration\Models\Shops;
use Carbon\Carbon;

class IFoodLib
{

    private $api ;

    public function __construct(){
        #TODO ter settings proprias ao inves de usar a do projeto pai
        $clientId          = \Settings::findByKey('ifood_client_id');
        $clientSecret      = \Settings::findByKey('ifood_client_secret');

        \Log::debug("IFoodLib::auth -> client_id: ". print_r($clientId, 1));
        \Log::debug("IFoodLib::auth -> client_secret: ". print_r($clientSecret, 1));

        $this->api = new IFoodApi;

        $expiryToken  = \Settings::findByKey('ifood_expiry_token');
        if ($expiryToken == NULL || Carbon::parse($expiryToken) < Carbon::now()) {
            $this->api->auth($clientId, $clientSecret);
        }
    }
    
    public function newOrders()
    {
        $response   = $this->api->newOrders();

        if ($response) {
            foreach ($response as $key => $value) {
                $timestamp = strtotime($value->createdAt);
                $createdAt = date('Y-m-d H:i:s', $timestamp);
                
                #TODO miration para criar o marketplace em questao e agregador
                $order = OrderDetails::updateOrCreate([
                        'order_id'       => $value->orderId,
                    ],
                    [
                        'order_id'          => $value->orderId,
                        'code'              => $value->code,
                        'full_code'         => $value->fullCode,
                        'marketplace_order_id'      => $value->id,
                        'created_at_marketplace'    => $createdAt
                    ]
                );

                $acknowledgment = $this->api->acknowledgment($value);
                if ($acknowledgment) {
                    $this->orderDetails($value->orderId);
                }
            }
        }  
    }

    public function orderDetails($orderId)
    {

        $response   = $this->api->orderDetails($orderId);

        if ($response) {
            
            $marketConfig = MarketConfig::where('merchant_id', $response->merchant->id)->first();

            $timestamp = strtotime($response->createdAt);
            $createdAt = date('Y-m-d H:i:s', $timestamp);
            
            $timestamp = strtotime($response->preparationStartDateTime);
            $preparationStartDateTime = date('Y-m-d H:i:s', $timestamp);

            $order = OrderDetails::updateOrCreate([
                    'order_id'                      => $response->id
                ],[
                    'shop_id'                       => ($marketConfig ? $marketConfig->shop_id : null),
                    'order_id'                      => $response->id,
                    'client_name'                   => $response->customer->name,
                    'merchant_id'                   => $response->merchant->id,
                    'created_at_marketplace'        => $createdAt,
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
                    'card_brand'                     => $response->payments->methods[0]->method == 'CREDIT' ? $response->payments->methods[0]->card->brand : NULL,
                    'extra_info'                    => isset($response->extraInfo) ? $response->extraInfo : ''
                ]
            );
            
            if (isset($response->delivery)) 
            {

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

                if(!$address)
                    \Log::warning(__FUNCTION__.'::Error to save Delivery Address: getOrderDetails response => '.print_r($response));


                
            } else {
                \Log::warning(__FUNCTION__.'::Error to save Delivery Address: getOrderDetails without delivery data, see response => '.print_r($response));
            }
        }
    }

    public function dispatch($orderId){
        return $this->api->dispatch($orderId);
    }
}