<?php

namespace Codificar\MarketplaceIntegration\Http\Controllers;

use Codificar\MarketplaceIntegration\Http\Controllers\IFoodController;
use Codificar\MarketplaceIntegration\Models\MarketConfig;
use Carbon\Carbon;
use Codificar\MarketplaceIntegration\Models\Shops;
use Illuminate\Http\Request;

    class DeliveryFactory
    {

        /**
         * Authenticate API delivery
         *
         * @param integer $id
         * @return string $access_token
         */
        public function auth($id = null)
        {
            \Log::debug("Função: ".__FUNCTION__);
            $className = self::selectClass($id);
            \Log::debug("Classe: ".print_r($className, 1));
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
        public function getOrdersDataBase(Request $request, $params)
        {
            \Log::debug("Função: ".__FUNCTION__);
            \Log::debug("Params: ".$params);
            $className = self::selectClass($params);
            $method = __FUNCTION__;
            return $className::$method($request,$params);
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
        public function dspOrder(Request $request)
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
        public function getMerchantDetails($id, $params)
        {
            \Log::debug("Função: ".__FUNCTION__);
            $className = self::selectClass($id);
            $method = __FUNCTION__;
            return $className::$method($params);
        }

        public function selectClass($id = null)
        {
            return new IFoodController();      
        }
    }