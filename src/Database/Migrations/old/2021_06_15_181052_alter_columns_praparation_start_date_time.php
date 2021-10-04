<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterColumnsPraparationStartDateTime extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasColumn('order_detail', 'praparation_start_date_time'))
        {
            Schema::table('order_detail', function (Blueprint $table) {
                $table->renameColumn('praparation_start_date_time', 'preparation_start_date_time');            
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasColumn('order_detail', 'preparation_start_date_time'))
        {
            Schema::table('order_detail', function (Blueprint $table) {
                $table->renameColumn('preparation_start_date_time', 'praparation_start_date_time');            
            });
        }
    }
}
