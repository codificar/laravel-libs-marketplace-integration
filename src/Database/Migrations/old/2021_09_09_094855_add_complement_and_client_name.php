<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddComplementAndClientName extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if(!Schema::hasColumn('delivery_address','complement'))
        {
            Schema::table('delivery_address', function (Blueprint $table) {
                $table->string('complement')->after('neighborhood');
            });
        }

        if(!Schema::hasColumn('order_detail','client_name'))
        {
            Schema::table('order_detail', function (Blueprint $table) {
                $table->string('client_name')->after('order_id');
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
