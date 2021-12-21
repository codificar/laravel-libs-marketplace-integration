<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableMktOrdersDetails extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('mkt_orders_details', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('shop_id')->unsigned();
            $table->bigInteger('request_id')->nullable();
            $table->bigInteger('point_id')->nullable();
            $table->integer('request_status')->nullable();
            $table->string('tracking_code')->nullable();
            $table->string('merchant_id')->nullable(false);
            $table->string('order_id')->nullable(false);
            $table->string('client_name')->nullable();
            $table->string('code');
            $table->string('full_code');
            $table->string('order_type')->nullable();
            $table->string('display_id')->nullable();
            $table->datetime('preparation_start_date_time')->nullable();
            $table->string('customer_id')->nullable();
            $table->double('sub_total')->nullable();
            $table->double('delivery_fee')->nullable();
            $table->double('benefits')->nullable();
            $table->string('extra_info', "2000")->nullable();
            $table->double('order_amount')->nullable();
            $table->string('card_brand')->nullable();
            $table->string('method_payment');
            $table->string('change_for');
            $table->integer('prepaid');
            $table->softDeletes();
            $table->timestamps();
            $table->foreign('shop_id')->references('id')->on('mkt_shops')->onDelete('cascade');
            $table->index('shop_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('mkt_orders_details');
    }
}
