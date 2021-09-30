<?php

namespace Codificar\MarketplaceIntegration\Http\Controllers;

use App\Http\Controllers\Controller;
use Codificar\MarketplaceIntegration\Models\MarketConfig;
use Codificar\MarketplaceIntegration\Lib\IFoodApi;
use Codificar\MarketplaceIntegration\Models\DeliveryAddress;
use Codificar\MarketplaceIntegration\Models\OrderDetails;
use Codificar\MarketplaceIntegration\Models\Shops;
use Illuminate\Http\Request;
use Codificar\MarketplaceIntegration\Http\Resources\OrdersResource;

class IFoodController extends Controller
{
    // protected static $api;

    /**
     * Instantiate a new IFoodController instance. But the delivery factory call 
     * iFoodController functions with static methods so this namespace won't woker
     */
    public function __construct()
    {
        // $this->api = new IFoodApi;
        // \Log::error('Im here');

    }

    public function auth($id = null)
    {
        $clientId          = \Settings::findByKey('ifood_client_id');
        $clientSecret      = \Settings::findByKey('ifood_client_secret');
        \Log::debug("IFoodController::auth -> client_id: ". print_r($clientId, 1));
        \Log::debug("IFoodController::auth -> client_secret: ". print_r($clientSecret, 1));

        $api = new IFoodApi;
        $res = $api->auth($clientId, $clientSecret);
        // \Log::debug("auth: ". print_r($res->accessToken, 1));
        
        // \Settings::updateOrCreateByKey('ifood_auth_token', $res->accessToken);
        // \Settings::updateOrCreateByKey('ifood_expiry_token', Carbon::now()->addHours(6));
    
    }

    public function getOrders()
    {
        $res        = new IFoodApi;
        $response   = json_decode($res->getOrders(\Settings::findByKey('ifood_auth_token')));

        if ($response) {
            foreach ($response as $key => $value) {
                $timestamp = strtotime($value->createdAt);
                $createdAt = date('Y-m-d H:i:s', $timestamp);
                
                $order = OrderDetails::updateOrCreate([
                        'order_id'       => $value->orderId,
                    ],
                    [
                        'order_id'          => $value->orderId,
                        'code'              => $value->code,
                        'full_code'         => $value->fullCode,
                        'ifood_id'          => $value->id,
                        'created_at_ifood'  => $createdAt
                    ]
                );
            }
        }

        return $response;
    }

    public function getOrderDetails($order_id)
    {
        \Log::debug('MarketID: '. $order_id);
        
        $res        = new IFoodApi;
        $response   = json_decode($res->getOrderDetails($order_id, \Settings::findByKey('ifood_auth_token')));

        if ($response) {
            
            \Log::debug('Details 0: '.print_r($response,1));

            $marketConfig = MarketConfig::where('merchant_id', $response->merchant->id)->first();

            $timestamp = strtotime($response->createdAt);
            $createdAt = date('Y-m-d H:i:s', $timestamp);
            \Log::debug('Cash:: '.print_r($response->payments->methods[0], 1));
            $timestamp = strtotime($response->preparationStartDateTime);
            $preparationStartDateTime = date('Y-m-d H:i:s', $timestamp);
            \Log::debug('Name:: '.print_r($response->customer->name, 1));

            $order = OrderDetails::updateOrCreate([
                    'order_id'                      => $response->id
                ],[
                    'shop_id'                       => ($marketConfig ? $marketConfig->shop_id : null),
                    'order_id'                      => $response->id,
                    'client_name'                   => $response->customer->name,
                    'merchant_id'                   => $response->merchant->id,
                    'created_at_ifood'              => $createdAt,
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
                    \Log::error(__FUNCTION__.'::Error to save Delivery Address: getOrderDetails response => '.print_r($response));


                
            } else {
                \Log::error(__FUNCTION__.'::Error to save Delivery Address: getOrderDetails without delivery data, see response => '.print_r($response));
            }
        }
    }

    public function getAcknowledgment($data)
    {
        $res        = new IFoodApi;
        $acknowledgment = $res->getAcknowledgment(\Settings::findByKey('ifood_auth_token'), $data);
    }

    public function getOrdersDataBase($id = NULL)
    {
        
        $query = OrderDetails::query();
        if (isset($id) && $id != null) {
            $query->where('shop_id', $id);
        }

        $query->where(function($queryCode){
                $queryCode->whereIn('code', ['CFM', 'RDA'])
                ->orWhere(function($queryInner) {
                        $queryInner->where('order_detail.code','DSP')
                        ->where('order_detail.request_id','>',1);
                });
        })
        ->join('delivery_address', 'order_detail.order_id', '=', 'delivery_address.order_id');

        $orders =   $query
                        ->orderBy('order_detail.request_id', 'ASC')//order by reuqest to show first the orders without points id, so orders without dispatched
                        ->orderBy('delivery_address.neighborhood', 'ASC')
                        ->orderBy('distance', 'DESC')
                        ->orderBy('order_detail.display_id', 'ASC')
                        ->orderBy('order_detail.client_name', 'ASC')
                        ->paginate(200);

        return new OrdersResource($orders);
    }

    public function confirmOrder(Request $request)
    {
        try {
            $market     = Shops::where('id', $request->id)->first();
            \Log::debug('s_id: '.$request->s_id);
            \Log::debug('id: '.$request->id);
            $res        = new IFoodApi;
            $response   = $res->confirmOrderApi($request->s_id, \Settings::findByKey('ifood_auth_token'));
            
            // $order = '';
            // if ($response) {
                \Log::debug('Controller 1: '.print_r($response,1));
                $ifoodData = json_decode($res->getOrderDetails($request->s_id, \Settings::findByKey('ifood_auth_token')));
                \Log::debug('entrou 1: '.print_r($ifoodData->createdAt,1));
                $timestamp = strtotime($ifoodData->createdAt);
                $createdAt = date('Y-m-d H:i:s', $timestamp);
                $timestamp = strtotime($ifoodData->preparationStartDateTime);
                $preparationStartDateTime = date('Y-m-d H:i:s', $timestamp);
                $order = OrderDetails::where('order_id', '=', $request->s_id)->first();
                \Log::debug("Order: ". print_r($order, 1));
                $order->merchant_id               = $ifoodData->merchant->id;
                $order->created_at_ifood          = $createdAt;
                $order->order_type                 = $ifoodData->orderType;
                $order->display_id                 = $ifoodData->displayId;
                $order->code                        = 'CFM';
                $order->full_code                   = 'CONFIRMED';
                $order->preparation_start_date_time  = $preparationStartDateTime;
                $order->merchant_id                = $ifoodData->merchant->id;
                $order->customer_id                = $ifoodData->customer->id;
                $order->sub_total                  = number_format($ifoodData->total->subTotal, 2, ',', '.');
                $order->delivery_fee               = $ifoodData->total->deliveryFee;
                $order->benefits                  = $ifoodData->total->benefits;
                $order->order_amount               = $ifoodData->total->orderAmount;
                $order->save();
            // }
            \Log::debug('Order: '.print_r($order, 1));
            $order->getAddress;
            return $order;
            
        }catch (\Exception $e){
            \Log::error("Erro: ".$e->getMessage());
            return $e;
        }
    }

    public function cancelOrder(Request $request)
    {
        \Log::debug('s_id: '.$request->s_id);
        \Log::debug('id: '.$request->id);
        try {
            
            $res        = new IFoodApi;
            $response   = $res->cancelOrderApi($request->s_id);
            \Log::debug('Controller 1: '.print_r($response,1));
            // $order = '';
            if ($response) {
                $ifoodData = $res->getOrderDetails($request->s_id);
                \Log::debug('entrou 1: '.print_r($ifoodData,1));
                $timestamp = strtotime($ifoodData->createdAt);
                $createdAt = date('Y-m-d H:i:s', $timestamp);
                $timestamp = strtotime($ifoodData->preparationStartDateTime);
                $preparationStartDateTime = date('Y-m-d H:i:s', $timestamp);
                $order = OrderDetails::where(['order_id'                   => $request->s_id])->first();
                $order->merchant_id               = $ifoodData->merchant->id;
                $order->created_at_ifood          = $createdAt;
                $order->order_type                 = $ifoodData->orderType;
                $order->display_id                 = $ifoodData->displayId;
                $order->code                        = 'CAN';
                $order->full_code                   = 'CANCELLED';
                $order->preparation_start_date_time  = $preparationStartDateTime;
                $order->merchant_id                = $ifoodData->merchant->id;
                $order->customer_id                = $ifoodData->customer->id;
                $order->sub_total                  = number_format($ifoodData->total->subTotal, 2, ',', '.');
                $order->delivery_fee               = $ifoodData->total->deliveryFee;
                $order->benefits                  = $ifoodData->total->benefits;
                $order->order_amount               = $ifoodData->total->orderAmount;
                $order->save();
            }
            \Log::debug('Order: '.print_r($order, 1));
            $order->getAddress;
            return $order;
            
        }catch (\Exception $e){
            \Log::error("Erro: ".$e->getMessage());
            return $e;
        }
    }

    /**
     * Update a single order on our DB and to iFoodApi
     */
    public function updateOrderRequest(Request $request)
    {
        \Log::debug('Request Update: '.print_r($request->all(), 1));
        
        $order = OrderDetails::where([
            'order_id'                       => $request->order_id
        ])->update([
                'code'                      => 'DSP',
                'full_code'                 => 'DISPATCHED',
                'request_id'                => $request->request_id,
                'point_id'                  => $request->point_id,
                'tracking_route'            => $request->tracking_route,
        ]);

        $order = OrderDetails::where([
            'order_id' => $request->order_id
        ])->first();
        \Log::debug('OrderDetails => '.print_r($order, 1));

        // $shop       = Shops::where('id',$order->shop_id)->first(); //Shop isn't used
        //I added teh token to iFoodApi construct
        $api = new IFoodApi;
        $response   = $api->dspOrder($request->order_id, "\Settings::findByKey('ifood_auth_token')");

        return $order;
    }

    public function dspOrder(Request $request)
    {
        \Log::debug("readyToPickup: ".print_r($request->all(),1));
        \Log::debug('s_id: '.$request->s_id);
        \Log::debug('id: '.$request->id);
        try {
            $market     = Shops::where('id', $request->id)->first();
            $res = new IFoodApi;
            $response = $res->dspOrder($request->s_id, \Settings::findByKey('ifood_auth_token'));
            \Log::debug('Controller 1: '.print_r($response,1));
            // if ($response) {
                $ifoodData = json_decode($res->getOrderDetails($request->s_id, \Settings::findByKey('ifood_auth_token')));
                \Log::debug('entrou 1: '.print_r($ifoodData,1));
                $timestamp = strtotime($ifoodData->createdAt);
                $createdAt = date('Y-m-d H:i:s', $timestamp);
                $timestamp = strtotime($ifoodData->preparationStartDateTime);
                $preparationStartDateTime = date('Y-m-d H:i:s', $timestamp);
                $order = OrderDetails::where(['order_id'                   => $request->s_id])->first();
                $order->merchant_id               = $ifoodData->merchant->id;
                $order->created_at_ifood          = $createdAt;
                $order->order_type                 = $ifoodData->orderType;
                $order->display_id                 = $ifoodData->displayId;
                $order->code                        = 'RTP';
                $order->full_code                   = 'READ_TO_PICKUP';
                $order->preparation_start_date_time  = $preparationStartDateTime;
                $order->merchant_id                = $ifoodData->merchant->id;
                $order->customer_id                = $ifoodData->customer->id;
                $order->sub_total                  = number_format($ifoodData->total->subTotal, 2, ',', '.');
                $order->delivery_fee               = $ifoodData->total->deliveryFee;
                $order->benefits                  = $ifoodData->total->benefits;
                $order->order_amount               = $ifoodData->total->orderAmount;
                $order->save();
            // }
            \Log::debug('Order: '.print_r($order, 1));
            $order->getAddress;
            return $order;
            
        }catch (\Exception $e){
            \Log::error("Erro: ".$e->getMessage());
            return $e;
        }
    }

    public function dispatchOrder(Request $request)
    {
        $res = new IFoodApi;
        $response = $res->dispatchOrder($request->order_id);
        // \Log::debug("readyToPickup: ".print_r($response,1));
    }

    public function getMerchantDetails($request)
    {
        
        $res = new IFoodApi;
        $response = $res->getMerchantDetails(\Settings::findByKey('ifood_auth_token'), $request->merchant_id);
        \Log::debug("MerchantDetails: ".print_r($response,1));
        
        return $response;
    }

    public function updateOrderRequestListener($point, $is_cancelled)
    {
        \Log::debug("is_cancelled TRUE: ".$is_cancelled);
        \Log::debug("point BLA: ".print_r($point, 1));
        $order = OrderDetails::where('request_id', '=', $point->request_id)
                                ->where('point_id', '=', $point->id)
                                ->first();
        $shop = Shops::find($order->shop_id);

        \Log::debug("ORDER GET BY POINT_ID BLA: ".print_r($order, 1));
        if ($order) 
        {
            $request_status='';
            $code='';
            $full_code='';
            if (!$is_cancelled) {
                \Log::debug("IF ");
                if ($point->start_time != NULL) {
                    \Log::debug("IF point->start_time".$point->start_time);
                    $ifood = new IFoodApi;
                    $res = $ifood->dspOrder($order->order_id,\Settings::findByKey('ifood_auth_token'));
                    $request_status = 0;
                    $code = "DSP";
                    $full_code = "DISPATCHED";
                }
                if ($point->finish_time) {
                    \Log::debug("IF point->finish_time". $point->finish_time);
                    $request_status = 0;
                    $code = "CON";
                    $full_code = "CONCLUDED";
                }
            } else {
                \Log::debug("ELSE");
                $request_status = 1;
                $code = "CAN";
                $full_code = "CANCELLED";
            }
            if ($request_status != '' && $code != '') {
                \Log::debug("IF UPDATE ORDER");
                $order->request_status    = $request_status;
                $order->code              = $code;
                $order->full_code         = $full_code;
                $order->update();
            }
        }
    }
}
