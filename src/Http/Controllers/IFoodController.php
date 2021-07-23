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
                \Log::debug('value: '.print_r( $value, 1));
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
        \Log::debug('MarketID: '. $market_id);
        \Log::debug('ID: '. $id);
        $marketConfig     = MarketConfig::where('shop_id',$id)->first();
        \Log::debug('marketConfig: '. $marketConfig);
        $res        = new IFoodApi($id);
        $response   = $res->getOrderDetails($market_id);
        if ($response) {
            \Log::debug('Details 0: '.print_r($response,1));
            $diffDistance = \DB::select( \DB::raw(
                "SELECT ST_Distance_Sphere(ST_GeomFromText('POINT(".$marketConfig->longitude." ".$marketConfig->latitude.")'), ST_GeomFromText('POINT(".$response->delivery->deliveryAddress->coordinates->longitude." ".$response->delivery->deliveryAddress->coordinates->latitude.")')) AS diffDistance"
            ));
            \Log::debug("DISTANCE: ".print_r($diffDistance[0]->diffDistance,1));
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
                ]
            );

            $order->getAddress;
            
        }
        $order = OrderDetails::where('order_id',$response->id)->first();
    }

    public function getAcknowledgment($id, $data)
    {

        \Log::debug('data: '. $data);
        \Log::debug('ID: '. $id);
        $res        = new IFoodApi($id);
        \Log::debug('Data: '. json_encode($data));
        $acknowledgment = $res->getAcknowledgment($data);
    }

    public function getOrdersDataBase($id = null)
    {
        \Log::debug('SHOP ID: '.$id);
        $query = OrderDetails::where('code', 'RTP')
                            ->join('delivery_address', 'order_detail.order_id', '=', 'delivery_address.order_id');
        if (isset($id) && $id != null) {
            $query = $query->where('shop_id', $id);
        }
        $orders =           $query->orderBy('distance', 'asc')
                            ->limit(10)
                            ->get();
        \Log::debug('OrdersDatabase: '. json_encode($orders));
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
            if ($response) {
                
                $timestamp = strtotime($response->createdAt);
                $createdAt = date('Y-m-d H:i:s', $timestamp);
                $timestamp = strtotime($response->preparationStartDateTime);
                $preparationStartDateTime = date('Y-m-d H:i:s', $timestamp);
                $order = OrderDetails::updateOrCreate([
                        'order_id'                   => $response->id
                    ],[
                        'request_id'                => $request->request_id,
                        'tracking_route'            => $request->tracking_route,
                        'merchant_id'               => $response->merchant->id,
                        'createdAt'                 => $createdAt,
                        'orderType'                 => $response->orderType,
                        'displayId'                 => $response->displayId,
                        'preparationStartDateTime'  => $preparationStartDateTime,
                        'merchantId'                => $response->merchant->id,
                        'customerId'                => $response->customer->id,
                        'subTotal'                  => number_format($response->total->subTotal, 2, ',', '.'),
                        'deliveryFee'               => $response->total->deliveryFee,
                        'benefits'                  => $response->total->benefits,
                        'orderAmount'               => $response->total->orderAmount,
                    ]
                );
            }       
            return $response;
        }catch (\Exception $e){
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
                'tracking_route'            => $request->tracking_route,
        ]);
        return $order;
    }

    public function rtcOrder(Request $request)
    {
        $res = new IFoodApi($request->s_id);
        $response = $res->rtcOrder($request->id);
        \Log::debug("readyToPickup: ".print_r($response,1));
    }

    public function dispatchOrder(Request $request)
    {
        $res = new IFoodApi($request->shop_id);
        $response = $res->dispatchOrder($request->order_id);
        \Log::debug("readyToPickup: ".print_r($response,1));
    }

    public function getMerchantDetails($id)
    {
        $shop = MarketConfig::where('shop_id', $id)->get();
        $res = new IFoodApi($id);
        $response = $res->getMerchantDetails($shop->merchant_id);
        \Log::debug("MerchantDetails: ".print_r($response,1));
        return $response;
    }
}
