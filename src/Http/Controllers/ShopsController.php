<?php

namespace Codificar\MarketplaceIntegration\Http\Controllers;

use Codificar\MarketplaceIntegration\Http\Requests\ShopsFormRequest;
use Codificar\MarketplaceIntegration\Http\Resources\ShopResource;
use Codificar\MarketplaceIntegration\Models\MarketConfig;
use Codificar\MarketplaceIntegration\Models\Shops;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ShopsController extends Controller
{
    public function index(Request $request)
    {
        $shops = Shops::all();
        foreach ($shops as $key => $value) {
            $value->getConfig;
        }
        return $shops;
    }

    public function store(ShopsFormRequest $request)
    {
        \Log::info('Entrou: '.print_r($request->all(),1));
        $shop = Shops::create([
            'name'  => $request->name
        ]);

        if ($shop) {
            $marketConfig = MarketConfig::create([
                'shop_id'       => $shop->id,
                'market'        => ($request->select == 1) ? 'ifood' : 'rappi',
                'client_id'     => $request->client_id,
                'client_secret' => $request->client_secret
            ]);
        }
        
        return new ShopResource($request);
    }
}
