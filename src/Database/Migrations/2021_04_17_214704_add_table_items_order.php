<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddTableItemsOrder extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('order_items', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('orderId');
            $table->integer('index');
            $table->string('itemId');
            $table->string('name');
            $table->string('externalCode');
            $table->string('unit');
            $table->integer('quantity');
            $table->double('unitPrice');
            $table->double('optionsPrice');
            $table->double('totalPrice');
            $table->double('price');
            $table->longText('opntions');
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
        Schema::dropIfExists('order_items');
    }
}
