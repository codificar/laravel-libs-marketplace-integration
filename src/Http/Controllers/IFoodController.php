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
//use App\Models\LibSettings;

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

    public static function auth($id = null)
    {
        $clientId          =  \Settings::findByKey('ifood_client_id');
        $clientSecret      =  \Settings::findByKey('ifood_client_secret');
        \Log::debug("IFoodController::auth -> client_id: ". print_r($clientId, 1));
        \Log::debug("IFoodController::auth -> client_secret: ". print_r($clientSecret, 1));

        $api = new IFoodApi;
        $res = $api->auth($clientId, $clientSecret);
        // \Log::debug("auth: ". print_r($res->accessToken, 1));
        
        //  \Settings::updateOrCreateByKey('ifood_auth_token', $res->accessToken);
        //  \Settings::updateOrCreateByKey('ifood_expiry_token', Carbon::now()->addHours(6));
    
    }
    
    #TODO move query to repository
    public static function getOrdersDataBase(Request $request, $id = NULL)
    {
        \Log::warning("Request: ".print_r($request->all(),1));
        $startTime = $request
                        ['range'][0] != null ? $request
                                                ['range'][0] : \Carbon\Carbon::now()->subDays(1);

        $endTime = $request
                        ['range'][0] != null ? $request
                                            ['range'][0] : null;


        \Log::warning("startTime: ".print_r($startTime,1));

        $query = OrderDetails::query();

        if (isset($startTime->date)) {
            $query->where('order_detail.created_at', '>', $startTime->date);
        } else if (isset($startTime->date) && $endTime) {
            $query->whereBetween('order_detail.created_at', [$startTime->date, $endTime]);
        } else {
            $query->where('order_detail.created_at', '>', $startTime);
        }

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

    public static function confirmOrder(Request $request)
    {
        try {
            $market     = Shops::where('id', $request->id)->first();
            \Log::debug('s_id: '.$request->s_id);
            \Log::debug('id: '.$request->id);
            $res        = new IFoodApi;
            $response   = $res->confirmOrderApi($request->s_id,  \Settings::findByKey('ifood_auth_token'));
            
            // $order = '';
            // if ($response) {
                \Log::debug('Controller 1: '.print_r($response,1));
                $ifoodData = json_decode($res->getOrderDetails($request->s_id,  \Settings::findByKey('ifood_auth_token')));
                \Log::debug('entrou 1: '.print_r($ifoodData->createdAt,1));
                $timestamp = strtotime($ifoodData->createdAt);
                $createdAt = date('Y-m-d H:i:s', $timestamp);
                $timestamp = strtotime($ifoodData->preparationStartDateTime);
                $preparationStartDateTime = date('Y-m-d H:i:s', $timestamp);
                $order = OrderDetails::where('order_id', '=', $request->s_id)->first();
                \Log::debug("Order: ". print_r($order, 1));
                $order->merchant_id               = $ifoodData->merchant->id;
                $order->created_at_marketplace          = $createdAt;
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
            $order->address;
            return $order;
            
        }catch (\Exception $e){
            \Log::error("Erro: ".$e->getMessage());
            return $e;
        }
    }

    public static function cancelOrder(Request $request)
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
                $order->created_at_marketplace          = $createdAt;
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
            $order->address;
            return $order;
            
        }catch (\Exception $e){
            \Log::error("Erro: ".$e->getMessage());
            return $e;
        }
    }

    /**
     * Update a single order on our DB and to iFoodApi
     */
    public static function updateOrderRequest(Request $request)
    {
        \Log::debug('Request Update: '.print_r($request->all(), 1));
        
        $order = OrderDetails::where([
            'order_id'                       => $request->order_id
        ])->update([
                'request_id'                => $request->request_id,
                'point_id'                  => $request->point_id,
                'tracking_route'            => $request->tracking_route,
        ]);

        $order = OrderDetails::where([
            'order_id' => $request->order_id
        ])->first();
        \Log::debug('OrderDetails => '.print_r($order, 1));

        return $order;
    }

    public static function dspOrder(Request $request)
    {
        \Log::debug("readyToPickup: ".print_r($request->all(),1));
        \Log::debug('s_id: '.$request->s_id);
        \Log::debug('id: '.$request->id);
        try {
            $market     = Shops::where('id', $request->id)->first();
            $res = new IFoodApi;
            $response = $res->dispatch($request->s_id);
            \Log::debug('Controller 1: '.print_r($response,1));
            // if ($response) {
                $ifoodData = json_decode($res->getOrderDetails($request->s_id,  \Settings::findByKey('ifood_auth_token')));
                \Log::debug('entrou 1: '.print_r($ifoodData,1));
                $timestamp = strtotime($ifoodData->createdAt);
                $createdAt = date('Y-m-d H:i:s', $timestamp);
                $timestamp = strtotime($ifoodData->preparationStartDateTime);
                $preparationStartDateTime = date('Y-m-d H:i:s', $timestamp);
                $order = OrderDetails::where(['order_id'                   => $request->s_id])->first();
                $order->merchant_id               = $ifoodData->merchant->id;
                $order->created_at_marketplace          = $createdAt;
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
            $order->address;
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

    public static function getMerchantDetails($request)
    {
        
        $res = new IFoodApi;
        $response = $res->getMerchantDetails( \Settings::findByKey('ifood_auth_token'), $request->merchant_id);
        \Log::debug("MerchantDetails: ".print_r($response,1));
        
        return $response;
    }

    
}
