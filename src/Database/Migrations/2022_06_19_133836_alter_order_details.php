<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterOrderDetails extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasColumn('order_detail', 'point_id'))
        {
            Schema::table('order_detail', function (Blueprint $table) {
                $table->integer('point_id')->nullable()->change();         
            });
        }

        if (Schema::hasColumn('order_detail', 'request_id'))
        {
            Schema::table('order_detail', function (Blueprint $table) {
                $table->integer('request_id')->nullable()->change();         
            });
        }

        if (Schema::hasColumn('order_detail', 'request_status'))
        {
            Schema::table('order_detail', function (Blueprint $table) {
                $table->integer('request_status')->nullable()->change();         
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
        //
    }
}
