<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterMerchantId extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasColumn('shops', 'merchant_id'))
        {
            Schema::table('shops', function (Blueprint $table) {  
                $table->dropColumn('merchant_id');
            });
        }
        Schema::table('market_config', function (Blueprint $table) {  
            $table->string('merchant_id')->after('id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('market_config', function (Blueprint $table) {
            $table->dropColumn('merchant_id');
        });
    }
}
