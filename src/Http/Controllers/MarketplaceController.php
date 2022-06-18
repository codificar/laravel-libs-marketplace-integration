<?php

namespace Codificar\MarketplaceIntegration\Http\Controllers;

use App\Http\Controllers\Controller;
use Codificar\MarketplaceIntegration\Models\MarketConfig;
use Codificar\MarketplaceIntegration\Lib\MarketplaceFactory;
use Codificar\MarketplaceIntegration\Models\DeliveryAddress;
use Codificar\MarketplaceIntegration\Models\OrderDetails;
use Codificar\MarketplaceIntegration\Models\Shops;
use Illuminate\Http\Request;


class MarketplaceController extends Controller
{
   
    /**
     * Function to handle webhook from multiple marketplaces
     */
    public function webhook(Request $request, $market)
    {
        $factory = MarketplaceFactory::create($market);
        return $factory->webhook($request);
    }
    
}
