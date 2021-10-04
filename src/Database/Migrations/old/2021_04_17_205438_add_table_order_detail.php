<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddTableOrderDetail extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('order_detail', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('orderId')->unique();
            $table->string('code');
            $table->string('fullCode');
            $table->string('ifoodId');
            $table->string('orderType')->nullable();
            $table->string('displayId')->nullable();
            $table->dateTime('createdAt')->nullable();
            $table->dateTime('preparationStartDateTime')->nullable();
            $table->string('merchantId')->nullable();
            $table->string('customerId')->nullable();
            $table->double('subTotal')->nullable();
            $table->double('deliveryFee')->nullable();
            $table->double('benefits')->nullable();
            $table->double('orderAmount')->nullable();
            $table->string('paymentsId')->nullable();
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
        Schema::dropIfExists('order_detail');
    }
}
