<?php

namespace Codificar\MarketplaceIntegration\Lib;

    interface DeliveryInterface
    {
        /**
         * Authenticate API delivery
         *
         * @param integer $id
         * @return $access_token
         */
        public function auth();

        public function getOrders();

        public function getOrderDetails($id);
        
    }