<?php

namespace Codificar\MarketplaceIntegration\Http\Controllers;

use Codificar\MarketplaceIntegration\Http\Requests\ShopsFormRequest;
use Codificar\MarketplaceIntegration\Http\Resources\ShopResource;
use Codificar\MarketplaceIntegration\Models\MarketConfig;
use Codificar\MarketplaceIntegration\Models\Shops;
use App\Http\Controllers\Controller;
use Codificar\MarketplaceIntegration\Http\Controllers\DeliveryFactory;
use Illuminate\Http\Request;
use Codificar\MarketplaceIntegration\Lib\IFoodApi;

use Carbon\Carbon;
use Codificar\MarketplaceIntegration\Lib\HubsterApi;
use Illuminate\Support\Facades\Log;

use Codificar\MarketplaceIntegration\Lib\MarketplaceFactory;

class ShopsController extends Controller
{

    /**
     * Function to store (save or update) shops model
     * @return ShopResource
     */
    public function store(ShopsFormRequest $request)
    {
        $user = \Auth::guard('web_corp')->user();
        $shop = Shops::create([
            'name'          => $request->name,
            'institution_id'=> $user->AdminInstitution->institution_id,
            'status_reload' => $request->status_reload ? $request->status_reload : 0,
        ]);
        
        return new ShopResource([$shop]);
    }

    /**
     * Function to get the shop list dropdown
     * @return array Shops
     */
    public function index()
    {
        $shops = Shops::where('institution_id', '=', \Auth::guard('web_corp')->user()->AdminInstitution->institution_id)->get();
        
        foreach ($shops as $key => $value)
        {
            
            if ($value->getConfig)
            {
                foreach ($value->getConfig as $key => $marketConfig)
                {
                    $factory = MarketplaceFactory::create($marketConfig->market);
                    $res = $factory->merchantDetails($marketConfig->merchant_id);

                    $marketConfig->status = isset($res->status) ? $res->status : "CLOSED";
                }
            }
        }
        return $shops;
    }

    public function status(Request $request)
    {
        $user = \Auth::guard('web_corp')->user();
        $shop = Shops::where('institution_id', $user->AdminInstitution->institution_id)
                    ->update([
            'institution_id'=> $user->AdminInstitution->institution_id,
            'status_reload' => $request->status_reload ? $request->status_reload : 0
        ]);
    }

}
