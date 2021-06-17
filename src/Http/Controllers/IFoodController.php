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
                $order = OrderDetails::updateOrCreate([
                        'orderId'       => $value->orderId,
                    ],
                    [
                        'orderId'       => $value->orderId,
                        'code'          => $value->code,
                        'fullCode'      => $value->fullCode,
                        'ifoodId'       => $value->id,
                        'createdAt'     => $createdAt
                    ]
                );
                $this->getOrderDetails($value->orderId);

            }
        }
        return $response;
    }

    public function getOrderDetails($id)
    {
        $marketConfig     = MarketConfig::first();
        \Log::debug('MarketID: '. $marketConfig->id);
        $res        = new IFoodApi($marketConfig->id);
        $response   = $res->getOrderDetails($id);
        if ($response) {
            \Log::debug('Details 0: '.print_r($response,1));
            $diffDistance = \DB::select( \DB::raw(
                "SELECT ST_Distance_Sphere(ST_GeomFromText('POINT(".$marketConfig->longitude." ".$marketConfig->latitude.")'), ST_GeomFromText('POINT(".$response->delivery->deliveryAddress->coordinates->longitude." ".$response->delivery->deliveryAddress->coordinates->latitude.")')) AS diffDistance"
            ));
            \Log::debug("DISTANCE: ".print_r($diffDistance[0]->diffDistance,1));
            $address = DeliveryAddress::updateOrCreate([
                'orderId'                       => $response->id
            ],[
                'customerId'                    => $response->customer->id,
                'streetName'                    => $response->delivery->deliveryAddress->streetName,
                'streetNumber'                  => $response->delivery->deliveryAddress->streetNumber,
                'formattedAddress'              => $response->delivery->deliveryAddress->formattedAddress,
                'neighborhood'                  => $response->delivery->deliveryAddress->neighborhood,
                'postalCode'                    => $response->delivery->deliveryAddress->postalCode,
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
                    'orderId'                   => $response->id
                ],[
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

            $order->getAddress;
            
        }
        $order = OrderDetails::where('orderId',$response->id)->first();
        event(new OrderUpdate($order));
    }

    public function getAcknowledgment($id, $data)
    {

        // Auth::guard('web_corp')->user();
        $res        = new IFoodApi($id);
        \Log::debug('Data: '. json_encode($data));
        $acknowledgment = $res->getAcknowledgment($data);
    }

    public function getOrdersDataBase()
    {
        $orders = OrderDetails::where('code', 'RTP')
                            ->join('delivery_address', 'order_detail.orderId', '=', 'delivery_address.orderId')
                            ->leftJoin('order_items', 'order_detail.orderId', '=', 'order_items.orderId')
                            ->orderBy('distance', 'asc')
                            ->limit(10)
                            ->get();

        return response()->json($orders);
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
                        'orderId'                   => $response->id
                    ],[
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

    public function rtcOrder(Request $request)
    {
        $res = new IFoodApi($request->s_id);
        $response = $res->rtcOrder($request->id);
        \Log::debug("readyToPickup: ".print_r($response,1));
    }

    public function getMerchantDetails($id)
    {
        $shop = Shops::find($id);
        $res = new IFoodApi($id);
        $response = $res->getMerchantDetails($shop->merchant_id);
        \Log::debug("MerchantDetails: ".print_r($response,1));
        return $response;
    }
}
