<?php

namespace Database\Seeders;
use Illuminate\Database\Seeder;

use Codificar\MarketplaceIntegration\Models\MarketConfig;
use Codificar\MarketplaceIntegration\Models\Shops;

class UpdateShopsAddressFromMarketConfigAddress extends Seeder {

    public function run() {
        foreach(MarketConfig::all() as $marketConfig){
            if($marketConfig->marketplace_address && $marketConfig->shop &&  !$marketConfig->shop->full_address){
                $marketConfig->shop->full_address   = $marketConfig->marketplace_address->street ;
                $marketConfig->shop->latitude       = $marketConfig->marketplace_address->latitude ;
                $marketConfig->shop->longitude      = $marketConfig->marketplace_address->longitude ;
                $marketConfig->shop->save();
            }
        }
    }
}
