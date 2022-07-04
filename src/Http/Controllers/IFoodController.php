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


#TODO remove this controller
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
