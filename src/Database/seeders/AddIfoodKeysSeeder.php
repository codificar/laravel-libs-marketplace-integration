<?php

namespace Database\Seeders;
use Illuminate\Database\Seeder;


class AddIfoodKeysSeeder extends Seeder {

    public function run() {
         \Settings::updateOrCreate(array('key' => 'ifood_client_id', 'value' => '2a8bdb0b-bbe0-454d-8514-525eb4adf8de'));
         \Settings::updateOrCreate(array('key' => 'ifood_client_secret', 'value' => 'wz4k8m9egdt18z23gbkg66deew9kj3doha3xq5ph15qh0pv37tkvfwjv7xmfe374jaszts5z1g0f7kp617ftaiwuxwqzy7brl7v'));
    }
}
