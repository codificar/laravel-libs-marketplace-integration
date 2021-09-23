<?php

namespace Codificar\MarketplaceIntegration\Lib;

interface IMarketplace
{

    /**
     * Make authentication method
     * 
     * @param Array $credentials
     * @return Object $reponse 
     */
    public function auth($cretendials);

    /**
     * Get Orders in marketplace API
     * 
     * @param String $token
     * @return OrderDetils $object
     */
    public function getOrder($token);

    /**
     * Get order details in marketplace API
     * 
     * @param String $orderId, $token
     * 
     * @return OrderDetail
     */
    public function getOrderDetails($orderId, $token);

    /**
     * Get Merchant in marketplace API
     * 
     * @param String $merchantId, $token
     * 
     * @return Merchant $object
     */
    public function getMerchant($merchantId, $token);

    /**
     * Get Merchant details in marketplace API
     * 
     * @param String $merchantId, $token
     * 
     * @return Merchant $object
     */
    public function getMerchantDetails($merchantId, $token);

    /**
     * Dispatch order to delivery
     * 
     * @param String $orderId, $token
     * 
     * @return void
     */
    public function dispatchOrder($orderId, $token);

    /**
     * Finish order in marketplace API
     * 
     * @param String $orderId, $token
     * 
     * @return void
     */
    public function finishOrder($orderId, $token);

    /**
     * Pooling in marketplace API
     * 
     * @param String $token
     * 
     * @return OrderDetails $object
     */
    public function polling($token);

    /**
     * Webhook to access marketplace API
     * 
     * @param String $token
     * 
     * @return OrderDetails $object
     */
    public function webhook($token);
}
