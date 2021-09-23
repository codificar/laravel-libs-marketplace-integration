<?php

namespace Codificar\MarketplaceIntegration\Lib;

use Codificar\MarketplaceIntegration\Models\MerchantDetails;

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
     */
    public function createMarketplace(MerchantDetails $merchant)
    {
        switch ($merchant->type) {
            case self::IFOOD:
                return (new IFoodApi());
            case self::UBEREATS:
                return '(new UberEatsApi());';
                break;
            default:
                return (new IFoodApi());
        }
    }
}