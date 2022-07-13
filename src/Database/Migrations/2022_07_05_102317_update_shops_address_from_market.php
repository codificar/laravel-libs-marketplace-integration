<?php

use Illuminate\Database\Migrations\Migration;

class UpdateShopsAddressFromMarket extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        \Artisan::call('db:seed', ['--class' => 'UpdateShopsAddressFromMarketConfigAddress', '--force' => null]);

        DB::statement("ALTER TABLE `market_config` CHANGE COLUMN `market` `market` ENUM('ifood', 'rappi', '99food', 'hubster', 'aiqfome', 'zedelivery') CHARACTER SET 'utf8' COLLATE 'utf8_unicode_ci' NOT NULL ; ");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
    }
}
