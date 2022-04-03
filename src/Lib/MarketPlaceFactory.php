<?php 

namespace Codificar\MarketplaceIntegration\Lib;

use Codificar\MarketplaceIntegration\Lib\IFoodApi;
class MapsFactory
{
    const IFOOD = 'ifood';

    private $marketPlaceType;

    public function __construct($marketPlaceType)
    {
        $this->marketPlaceType = $marketPlaceType;
    }

    public function createMaps()
    {         
            
       switch($this->marketPlaceType)
            {
                case self::IFOOD:
                    return(new IFoodApi());
                default:
                    #TODO trow exception no marketplace type valid
                    return(new IFoodApi());
            }
    }

}
