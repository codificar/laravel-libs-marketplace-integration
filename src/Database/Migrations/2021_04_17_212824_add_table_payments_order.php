<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddTablePaymentsOrder extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('payment_order', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('orderId');
            $table->double('prepaidValue');
            $table->double('pending');
            $table->double('value');
            $table->string('currency');
            $table->string('method');
            $table->string('type');
            $table->string('changeFor');
            $table->boolean('prepaid');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('payment_order');
    }
}
