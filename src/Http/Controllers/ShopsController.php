<?php

namespace App\Http\Controllers;

use App\Http\Requests\ShopsFormRequest;
use App\Http\Resources\ShopResource;
use App\MarketConfig;
use App\Shops;
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
