<?php

namespace Codificar\MarketplaceIntegration\Lib;

interface IMarketplace
{
    
    /**
    * Make authentication method
    * 
    * @param Object $credentials
    *
    * @return Object $reponse 
    */
    public function auth();
    
    /**
    * Get Orders in marketplace API
    * 
    * @return OrderDetils $object
    */
    public function getOrder();

    /**
     * Get Acknowledgment in marketplace API
     * 
     * @param Object $data
     * 
     * @return Object $response
     */
    public function getAcknowledgment($data);
    
    /**
    * Get order details in marketplace API
    * 
    * @param String $orderId
    * 
    * @return OrderDetail
    */
    public function getOrderDetails($orderId);
    
    /**
    * Get Merchant in marketplace API
    * 
    * @param String $merchantId
    * 
    * @return Merchant $object
    */
    public function getMerchant($merchantId);
    
    /**
    * Get Merchant details in marketplace API
    * 
    * @param String $merchantId
    * 
    * @return Merchant $object
    */
    public function getMerchantDetails($merchantId);
    
    /**
    * Dispatch order to delivery
    * 
    * @param String $orderId
    * 
    * @return void
    */
    public function dispatchOrder($orderId);
    
    /**
    * Finish order in marketplace API
    * 
    * @param String $orderId
    * 
    * @return void
    */
    public function finishOrder($orderId);
    
    /**
    * Pooling in marketplace API
    * 
    * @return OrderDetails $object
    */
    public function polling();
    
    /**
    * Webhook to access marketplace API
    * 
    * @return OrderDetails $object
    */
    public function webhook();

    /**
     * Check validity of token in marketplace API
     * 
     * @return Object $object
     */
    public function checkTokenValidity();
}
