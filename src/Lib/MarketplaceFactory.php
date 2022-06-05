<?php 

namespace Codificar\MarketplaceIntegration\Lib;

use Codificar\MarketplaceIntegration\Lib\IFoodLib;

class MarketplaceFactory
{
    const IFOOD = 'ifood';

    public static function create($marketPlaceType = null)
    {         
            
       switch($marketPlaceType)
            {
                case self::IFOOD:
                    return(new IFoodLib());
                default:
                    #TODO trow exception no marketplace type valid
                    return(new IFoodLib());
            }
    }

}
