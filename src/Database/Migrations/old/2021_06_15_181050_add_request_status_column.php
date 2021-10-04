<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddRequestStatusColumn extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasColumn('order_detail', 'request_status'))
        {
            Schema::table('order_detail', function (Blueprint $table) {  
                $table->dropColumn('request_status');
            });
        }
        Schema::table('order_detail', function (Blueprint $table) {  
            $table->integer('request_status')->after('request_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('order_detail', function (Blueprint $table) {
            $table->dropColumn('request_status');
        });
    }
}
