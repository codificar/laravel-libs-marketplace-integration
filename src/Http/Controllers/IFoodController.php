<?php

namespace Codificar\MarketplaceIntegration\Http\Controllers;

use Codificar\MarketplaceIntegration\Events\OrderUpdate;
use App\Http\Controllers\Controller;
use Codificar\MarketplaceIntegration\Models\MarketConfig;
use Codificar\MarketplaceIntegration\Lib\IFoodApi;
use Codificar\MarketplaceIntegration\Models\DeliveryAddress;
use Codificar\MarketplaceIntegration\Models\OrderDetails;
use Codificar\MarketplaceIntegration\Models\Shops;
use Illuminate\Http\Request;
use Carbon\Carbon;

class IFoodController extends Controller
{
    public function auth($id = null)
    {
        $clientId          = \Settings::findByKey('ifood_client_id');
        $clientSecret      = \Settings::findByKey('ifood_client_secret');
        \Log::debug("client_id: ". print_r($clientId, 1));
        \Log::debug("client_secret: ". print_r($clientSecret, 1));

        $api = new IFoodApi;
        $res = json_decode($api->auth($clientId, $clientSecret));
        \Log::debug("auth: ". print_r($res->accessToken, 1));
        
        \Settings::updateOrCreateByKey('ifood_auth_token', $res->accessToken);
        \Settings::updateOrCreateByKey('ifood_expiry_token', Carbon::now()->addHours(6));
    
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

            $order = OrderDetails::updateOrCreate([
                    'order_id'                      => $response->id
                ],[
                    'shop_id'                       => ($marketConfig ? $marketConfig->shop_id : null),
                    'order_id'                      => $response->id,
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
            
            if (isset($response->delivery)) {

                $calculatedDistance = 0 ;

                if($marketConfig) {
                    $diffDistance = \DB::select( \DB::raw(
                        "SELECT ST_Distance_Sphere(ST_GeomFromText('POINT(".$marketConfig->longitude." ".$marketConfig->latitude.")'), ST_GeomFromText('POINT(".$response->delivery->deliveryAddress->coordinates->longitude." ".$response->delivery->deliveryAddress->coordinates->latitude.")')) AS diffDistance"
                    ));
                    \Log::debug("DISTANCE: ".print_r($diffDistance,1));
                    $calculatedDistance = $diffDistance[0]->diffDistance ;
                }

                $address = DeliveryAddress::updateOrCreate([
                    'order_id'                      => $response->id
                ],[
                    'customer_id'                   => $response->customer->id,
                    'stree_name'                    => $response->delivery->deliveryAddress->streetName,
                    'street_number'                 => $response->delivery->deliveryAddress->streetNumber,
                    'formatted_address'             => $response->delivery->deliveryAddress->formattedAddress,
                    'neighborhood'                  => $response->delivery->deliveryAddress->neighborhood,
                    'postal_code'                   => $response->delivery->deliveryAddress->postalCode,
                    'city'                          => $response->delivery->deliveryAddress->city,
                    'state'                         => $response->delivery->deliveryAddress->state,
                    'country'                       => $response->delivery->deliveryAddress->country,
                    'latitude'                      => $response->delivery->deliveryAddress->coordinates->latitude,
                    'longitude'                     => $response->delivery->deliveryAddress->coordinates->longitude,
                    'distance'                      => $calculatedDistance,
                ]);

                
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
        // $market = MarketConfig::where('merchant')
        $query = OrderDetails::whereIn('code', ['CFM', 'RDA'])
                            // ->where('code', '!=', 'CAN')
                            ->join('delivery_address', 'order_detail.order_id', '=', 'delivery_address.order_id');
        if (isset($id) && $id != null) {
            \Log::debug('SHOP ID: '.$id);
            $query = $query->where('shop_id', $id);
        }
        $orders =           $query->orderBy('distance', 'DESC')
                            ->orderBy('order_detail.created_at', 'DESC')
                            ->limit(10)
                            ->get();
        return $orders;
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

    public function updateOrderRequest(Request $request)
    {
        \Log::debug('Request Update: '.print_r($request->all(), 1));
        $order = OrderDetails::where([
            'order_id'                       => $request->order_id
        ])->update([
                'code'                      => 'DSP',
                'full_code'                 => 'DISPATCHED',
                'request_id'                => $request->request_id,
                'point_id'                  => $request->id,
                'tracking_route'            => $request->tracking_route,
        ]);

        $order = OrderDetails::where([
            'order_id'                       => $request->order_id
        ])->first();
        
        // despacha via ifood api
        $res        = new IFoodApi;
        $shop       = Shops::where('id',$order->shop_id)->first();
        $response   = $res->dspOrder($request->order_id, \Settings::findByKey('ifood_auth_token'));

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
                                ->where('display_id', '=', $point->action)
                                ->first();
        if ($order) {
            $request_status='';
            $code='';
            $full_code='';
            if (!$is_cancelled) {
                \Log::debug("IF ");
                if ($point->start_time != NULL) {
                    $request_status = 0;
                    $code = "DSP";
                    $full_code = "DISPATCHED";
                }
                if ($point->finish_time) {
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
                $order->update([
                    'request_status'    => $request_status,
                    'code'              => $code,
                    'full_code'         => $full_code
                ]);
            }
        }
    }
}
