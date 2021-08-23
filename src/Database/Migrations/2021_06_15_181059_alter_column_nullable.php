<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterColumnNullable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('market_config', function (Blueprint $table) {
            $table->string('client_id')->nullable(true)->change();
            $table->string('client_secret')->nullable(true)->change();
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
            $table->string('client_id')->nullable(false)->change();
            $table->string('client_secret')->nullable(false)->change();
        });
    }
}
