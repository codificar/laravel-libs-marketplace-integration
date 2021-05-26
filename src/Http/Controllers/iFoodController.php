<?php

namespace Codificar\MarketplaceIntegration\Http\Controllers\iFood;

use Codificar\MarketplaceIntegration\Events\OrderUpdate;
use App\Http\Controllers\Controller;
use Codificar\MarketplaceIntegration\MarketConfig;
use Codificar\MarketplaceIntegration\OrderDetails;
use IFoodApi;
use Illuminate\Http\Request;

class iFoodController extends Controller
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
            }
        }
        return $response;
    }

    public function getOrderDetails($id)
    {
        $clientId     = MarketConfig::select('client_id')->where('id', $id)->first();
        $res        = new IFoodApi($clientId);
        $response   = $res->getOrderDetails($id);
        if ($response) {
            \Log::debug('Details 0: '.print_r($response,1));
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
        $order = OrderDetails::where('orderId',$response->id)->first();
        event(new OrderUpdate($order));
    }

    public function getAcknowledgment($id, $data)
    {
        $res        = new IFoodApi($id);
        \Log::debug('Data: '. json_encode($data));
        $acknowledgment = $res->getAcknowledgment($data);
    }

    public function getOrdersDataBase()
    {
        $orders = OrderDetails::where('code', 'RTP')->orderBy('createdAt', 'DESC')
                            ->limit(10)->get();
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
}
