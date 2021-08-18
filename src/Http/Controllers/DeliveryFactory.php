<?php

namespace Codificar\MarketplaceIntegration\Http\Controllers;

use Codificar\MarketplaceIntegration\Http\Controllers\IFoodController;
use Codificar\MarketplaceIntegration\Models\MarketConfig;
use Carbon\Carbon;
use Illuminate\Http\Request;

    class DeliveryFactory
    {

        /**
         * Authenticate API delivery
         *
         * @param integer $id
         * @return string $access_token
         */
        public function auth($id)
        {
            \Log::debug("ID: ".$id);
            \Log::debug("Função: ".__FUNCTION__);
            $className = self::selectClass($id);
            // \Log::debug("Classe: ".$className);
            $method = __FUNCTION__;
            return $className::$method($id);
        }

        /**
         * Get Orders in delivery API
         *
         * @return object $orders
         */
        public function getOrders($params)
        {
            \Log::debug("Função: ".__FUNCTION__);
            $className = self::selectClass($params);
            $method = __FUNCTION__;
            return $className::$method($params);
        }

        /**
         * Confirm recive event from delivery API
         *
         * @return object $orders
         */
        public function getAcknowledgment($params, $res)
        {
            \Log::debug("Função: ".__FUNCTION__);
            $className = self::selectClass($params);
            $method = __FUNCTION__;
            return $className::$method($params, $res);
        }

        /**
         * Get Orders Details in delivery API
         *
         * @return object $orders
         */
        public function getOrderDetails($id, $params)
        {
            \Log::debug("Função: ".__FUNCTION__);
            \Log::debug("PArams: ".$params);
            $className = self::selectClass($id);
            $method = __FUNCTION__;
            return $className::$method($id, $params);
        }

        /**
         * Get Orders Details in DB
         *
         * @return object $orders
         */
        public function getOrdersDataBase($params)
        {
            \Log::debug("Função: ".__FUNCTION__);
            \Log::debug("Params: ".$params);
            $className = self::selectClass($params);
            $method = __FUNCTION__;
            $data = $className::$method($params);
            // \Log::debug("Data: ".print_r($data, 1));
            return $data;
        }

        /**
         * Cancel Order in delivery API
         *
         * @return object $orders
         */
        public function cancelOrder($params)
        {
            \Log::debug("Função: ".__FUNCTION__);
            $className = self::selectClass($params);
            $method = __FUNCTION__;
            return $className::$method($params);
        }

        /**
         * Dispatch Order in delivery API
         *
         * @return object $orders
         */
        public function rtpOrder(Request $request)
        {
            \Log::debug("Função: ".__FUNCTION__);
            \Log::debug("Request: ".print_r($request->all(), 1));
            $className = self::selectClass($request->id);
            $method = __FUNCTION__;
            return $className::$method($request);
        }

        /**
         * Confirm Order in delivery API
         *
         * @return object $orders
         */
        public function confirmOrder($params, Request $request)
        {
            \Log::debug("Função: ".__FUNCTION__);
            \Log::debug("Params: ".$params);
            \Log::debug("Params: ".print_r($request->all(),1));
            $className = self::selectClass($request->id);
            $method = __FUNCTION__;
            return $className::$method($request);
        }

        /**
         * Update Order by request DB
         *
         * @return object $orders
         */
        public function updateOrderRequest(Request $request)
        {
            \Log::debug("Função: ".__FUNCTION__);
            \Log::debug("Params: ".print_r($request->all(),1));
            $className = self::selectClass($request->shop_id);
            $method = __FUNCTION__;
            return $className::$method($request);
        }

        /**
         * Get Merchant Details in delivery API
         *
         * @return object $merchant
         */
        public function getMerchantDetails($params)
        {
            \Log::debug("Função: ".__FUNCTION__);
            $className = self::selectClass($params);
            $method = __FUNCTION__;
            return $className::$method($params);
        }

        public function selectClass($id)
        {
            $market = MarketConfig::where('shop_id', $id)->first();
            \Log::debug("Now: ".Carbon::now());
            \Log::debug("expiry_token: ".Carbon::parse($market->expiry_token));
            switch ($market['market']) {
                case 'ifood':
                    return new IFoodController();
                break;
                case 'rappi':
                    \Log::debug('rappi');
                break;
                default:
                    \Log::debug('default');
                break;
            }
            
        }
    }