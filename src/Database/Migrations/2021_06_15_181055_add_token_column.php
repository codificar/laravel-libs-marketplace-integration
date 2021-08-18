<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddTokenColumn extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('market_config', function (Blueprint $table) {  
            $table->string('token', "2000")->after('merchant_id')->nullable();
            $table->dateTime('expiry_token')->after('token')->nullable();
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
            $table->dropColumn('token');
            $table->dropColumn('expiry_token');
        });
    }
}
