<?php

namespace Codificar\MarketplaceIntegration\Repositories;

use Codificar\MarketplaceIntegration\Lib\MarketplaceFactory;
use Codificar\MarketplaceIntegration\Models\MarketConfig;

/**
 * Class ZeDeliveryRepository.
 */
class ZeDeliveryRepository extends MarketplaceRepository
{
    /**
     * @return array merchantIds
     */
    public static function getArrayMerchantIds()
    {
        $merchantIds = MarketConfig::where('market', '=', MarketplaceFactory::ZEDELIVERY)
                                ->groupby('merchant_id')
                                ->get()
                                ->pluck('merchant_id')
                                ->toArray();

        return $merchantIds;
    }

    /**
     * @return string merchantIds
     */
    public static function getMerchantIds()
    {
        $merchantIds = self::getArrayMerchantIds();
        $merchantIds = is_array($merchantIds) ? implode(',', $merchantIds) : null;

        return $merchantIds;
    }

    /**
     * Map Event Type.
     */
    public static function mapCode($eventType)
    {
        $mapEvents = [
            'CREATED' => self::PLACED,
            'DISPATCHED' => self::DISPATCHED,
            'CONFIRMED' => self::CONFIRMED,
            'CANCELLED' => self::CANCELLED
        ];

        return $mapEvents[$eventType];
    }

    /**
     * Map Full Code Event Type.
     */
    public static function mapFullCodeFromEvent($eventType)
    {
        $code = self::mapCode($eventType);

        return self::mapFullCode($code);
    }
}
