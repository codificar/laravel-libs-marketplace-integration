<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterShops extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('shops', function (Blueprint $table) {
            if (!Schema::hasColumn('shops', 'full_address')) {
                $table->string('full_address')->nullable();
            }
            if (!Schema::hasColumn('shops', 'latitude')) {
                $table->float('latitude', 15, 8);
            }
            if (!Schema::hasColumn('shops', 'longitude')) {
                $table->float('longitude', 15, 8);
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
