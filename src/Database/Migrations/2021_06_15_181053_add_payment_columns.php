<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddPaymentColumns extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('order_detail', function (Blueprint $table) {  
            $table->string('method_payment')->after('order_amount');
            $table->string('change_for')->after('method_payment');
            $table->integer('prepaid')->after('benefits');
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
            $table->dropColumn('method_payment');
            $table->dropColumn('change_for');
            $table->dropColumn('prepaid');
        });
    }
}
