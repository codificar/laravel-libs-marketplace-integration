<?php

use Illuminate\Database\Seeder;

class AddInformationUrlKey extends Seeder {

    public function run() {
        \Information::updateOrCreate(array('title'=> 'Como Adicionar permissão para loja iFood', 'content'=> '', 'icon' => '', 'type' => 'corp', 'url_key' => 'ifood-market-permission'));
    }
}
