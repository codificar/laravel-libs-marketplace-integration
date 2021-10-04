<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableMktMerchantDetails extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('mkt_merchant_details', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('merchant_id')->nullable(false);
            $table->string('name')->unique();
            $table->bigInteger('shop_id')->unsigned();
            $table->enum('type',['ifood', 'rappi','ubereats']);
            $table->longtext('address')->nullable(false);
            $table->float('latitude', 15, 8);          
            $table->float('longitude', 15, 8);
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
        Schema::dropIfExists('mkt_merchant_details');
    }
}
