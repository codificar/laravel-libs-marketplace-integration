<?php

namespace Codificar\MarketplaceIntegration\Lib;

use Codificar\MarketplaceIntegration\Models\MerchantDetails;
use Exception;

class MarketplaceFactory
{
    /**
     * Constants of marketplace type
     */
    const IFOOD     = "ifood";
    const UBEREATS  = "ubereats";
    const RAPPI     = 'rappi';

    /**
     * Instantiate object according to marketplace
     * 
     * @param MerchantDetails $merchant
     * 
     * @return mixed
     */
    public static function createMarketplace($type)
    {
        switch ($type) {
            case self::IFOOD:
                return new IFoodApi();
            case self::UBEREATS:
                return null;
                break;
            case self::RAPPI:
                return null;
                break;
            default:
                throw(new Exception("Marketplace not yet implemented with type $type",417));
        }
    }
}