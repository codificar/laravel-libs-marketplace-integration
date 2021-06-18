<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTableDeliveryAddress extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasColumn('delivery_address', 'coordinates'))
        {
            Schema::table('delivery_address', function (Blueprint $table) {
                $table->dropColumn('coordinates');            
            });
        }
        Schema::table('delivery_address', function (Blueprint $table) {  
            $table->float('latitude', 15, 8)->after('country');          
            $table->float('longitude', 15, 8)->after('latitude');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        
        Schema::table('delivery_address', function (Blueprint $table) {
            $table->dropColumn('latitude');
            $table->dropColumn('longitude');
        });
    }
}
