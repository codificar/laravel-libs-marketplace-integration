<?php

namespace Codificar\MarketplaceIntegration\Lib;

class MarketplaceFactory
{
    const IFOOD = 'ifood';
    const HUBSTER = 'hubster';
    const ZEDELIVERY = 'zedelivery';
    const FOOD99 = '99food';
    const RAPPI = 'rappi';

    public static $allMarketplaces = [self::IFOOD, self::HUBSTER, self::ZEDELIVERY, self::FOOD99, self::RAPPI];
    public static $pollingMarketplaces = [self::IFOOD, self::ZEDELIVERY];

    public static function create($marketPlaceType = null)
    {
        switch ($marketPlaceType) {
            case self::IFOOD:
                return new IFoodLib();
            case self::HUBSTER:
                return new HubsterLib();
            case self::ZEDELIVERY:
                return new HubsterLib();
            default:
                //TODO trow exception no marketplace type valid
                return new IFoodLib();
        }
    }
}
