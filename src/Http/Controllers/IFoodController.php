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

class IFoodController extends Controller
{

    public function getOrders($id)
    {
        $res        = new IFoodApi($id);
        $response   = $res->getOrders();
        if ($response) {
            foreach ($response as $key => $value) {
                $timestamp = strtotime($value->createdAt);
                $createdAt = date('Y-m-d H:i:s', $timestamp);
                // \Log::debug('value: '.print_r( $value, 1));
                $order = OrderDetails::updateOrCreate([
                        'order_id'       => $value->orderId,
                    ],
                    [
                        'shop_id'           => $id,
                        'merchant_id'       => '',
                        'order_id'          => $value->orderId,
                        'code'              => $value->code,
                        'full_code'         => $value->fullCode,
                        'ifood_id'          => $value->id,
                        'created_at_ifood'  => $createdAt
                    ]
                );
                $this->getOrderDetails($id, $value->orderId);
            }
        }
        return $response;
    }

    public function getOrderDetails($id, $market_id)
    {
        // \Log::debug('MarketID: '. $market_id);
        // \Log::debug('ID: '. $id);
        $marketConfig     = MarketConfig::where('shop_id',$id)->first();
        // \Log::debug('marketConfig: '. $marketConfig);
        $res        = new IFoodApi($id);
        $response   = $res->getOrderDetails($market_id);
        if ($response) {
            // \Log::debug('Details 0: '.print_r($response,1));
            $diffDistance = \DB::select( \DB::raw(
                "SELECT ST_Distance_Sphere(ST_GeomFromText('POINT(".$marketConfig->longitude." ".$marketConfig->latitude.")'), ST_GeomFromText('POINT(".$response->delivery->deliveryAddress->coordinates->longitude." ".$response->delivery->deliveryAddress->coordinates->latitude.")')) AS diffDistance"
            ));
            // \Log::debug("DISTANCE: ".print_r($diffDistance[0]->diffDistance,1));
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
                'distance'                      => $diffDistance[0]->diffDistance
            ]);

            $timestamp = strtotime($response->createdAt);
            $createdAt = date('Y-m-d H:i:s', $timestamp);
            \Log::debug('Cash:: '.print_r($response->payments->methods[0], 1));
            $timestamp = strtotime($response->preparationStartDateTime);
            $preparationStartDateTime = date('Y-m-d H:i:s', $timestamp);
            $order = OrderDetails::updateOrCreate([
                    'order_id'                      => $response->id
                ],[
                    'shop_id'                       => $marketConfig->shop_id,
                    'order_id'                      => $response->id,
                    'merchant_id'                   => $response->merchant->id,
                    'created_at_ifood'              => $createdAt,
                    'order_type'                    => $response->orderType,
                    'display_id'                    => $response->displayId,
                    'preparation_start_date_time'   => $preparationStartDateTime,
                    'merchant_id'                   => $response->merchant->id,
                    'customer_id'                   => $response->customer->id,
                    'sub_total'                     => $response->total->subTotal,
                    'delivery_fee'                  => $response->total->deliveryFee,
                    'benefits'                      => $response->total->benefits,
                    'order_amount'                  => $response->total->orderAmount,
                    'method_payment'                => $response->payments->methods[0]->method,
                    'prepaid'                       => $response->payments->methods[0]->prepaid,
                    'change_for'                    => $response->payments->methods[0]->method == 'CASH' ? $response->payments->methods[0]->cash->changeFor : '',
                    'car_brand'                     => $response->payments->methods[0]->method == 'CARD' ? $response->payments->methods[0]->card->brand : NULL,
                ]
            );

            $order->getAddress;
            
        }
        $order = OrderDetails::where('order_id',$response->id)->first();
    }

    public function getAcknowledgment($id, $data)
    {
        // \Log::debug('data: '. print_r($data, 1));
        // \Log::debug('ID: '. $id);
        $res        = new IFoodApi($id);
        // \Log::debug('Data: '. json_encode($data));
        $acknowledgment = $res->getAcknowledgment($data);
    }

    public function getOrdersDataBase($id = null)
    {
        // \Log::debug('SHOP ID: '.$id);
        $query = OrderDetails::where('code', '!=', 'CON')
                            ->where('code', '!=', 'CAN')
                            ->join('delivery_address', 'order_detail.order_id', '=', 'delivery_address.order_id');
        if (isset($id) && $id != null) {
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
            \Log::debug('s_id: '.$request->s_id);
            \Log::debug('id: '.$request->id);
            $res        = new IFoodApi($request->id);
            $response   = $res->confirmOrderApi($request->s_id);
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
            }
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
            
            $res        = new IFoodApi($request->id);
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
        \Log::debug('Request Update: '.print_r($request->id, 1));
        $order = OrderDetails::where([
            'order_id'                       => $request->order_id
        ])->update([
                'request_id'                => $request->request_id,
                'point_id'                  => $request->id,
                'tracking_route'            => $request->tracking_route,
        ]);
        return $order;
    }

    public function rtpOrder(Request $request)
    {
        \Log::debug("readyToPickup: ".print_r($request->all(),1));
        \Log::debug('s_id: '.$request->s_id);
        \Log::debug('id: '.$request->id);
        try {
            $res = new IFoodApi($request->id);
            $response = $res->rtpOrder($request->s_id);
            \Log::debug('Controller 1: '.print_r($response,1));
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
            }
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
        $res = new IFoodApi($request->shop_id);
        $response = $res->dispatchOrder($request->order_id);
        // \Log::debug("readyToPickup: ".print_r($response,1));
    }

    public function getMerchantDetails($id)
    {
        // \Log::debug("id merchantDetails: ".$id);
        $shop = MarketConfig::where('shop_id', $id)->get();
        $res = new IFoodApi($id);
        // \Log::debug("Shop: ".print_r($shop,1));
        $response = $res->getMerchantDetails($shop->merchant_id);
        // \Log::debug("MerchantDetails: ".print_r($response,1));
        return $response;
    }

    public function updateOrderRequestListener($points, $request)
    {
        $request_status='';
        $code='';
        $full_code='';
        if (!$request->request->is_cancelled) {
            if ($points->start_time != NULL) {
                $request_status = 0;
                $code = "DSP";
                $full_code = "DISPATCHED";
            }
            if ($points->finish_time) {
                $request_status = 0;
                $code = "CON";
                $full_code = "CONCLUDED";
            }
        } else {
            $request_status = 1;
            $code = "CAN";
            $full_code = "CANCELLED";
        }
        
        $order = OrderDetails::where('request_id', '=', $points->request_id)
                                ->where('point_id', '=', $points->id)
                                ->update([
                                    'request_status'    => $request_status,
                                    'code'              => $code,
                                    'full_code'         => $full_code
                                ]);
        \Log::debug('Orders: '.print_r($order->id, 1));

    }
}
