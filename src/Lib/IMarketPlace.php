<?php 

namespace Codificar\MarketplaceIntegration\Lib;

use Exception;

interface IMarketPlace {
    /**
     * Auth on api and save token at session.
     *
     * @param string|int $clientId
     * @param string|int $clientSecret
     *        
     * @return array|Exception
    */
    public function auth($clientId, $clientSecret): array|Exception;

    /**
     * Find marketplace merchant.
     *
     * @param string|int $id
     * @param string|int $clientSecret
     *        
     * @return array|Exception
    */
    public function findMerchant($id): array|Exception;

    /**
     * Get marketplace order.
     *        
     * @return array|Exception
    */
    public function getOrders(): array|Exception;
}
