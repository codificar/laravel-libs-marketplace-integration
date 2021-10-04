<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableMktShops extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('mkt_shops', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('institution_id')->unsigned();
            $table->boolean('status_reload')->default(false);
            $table->string('name')->unique();
            $table->softDeletes();
            $table->timestamps();
        });
        Schema::table('mkt_shops', function($table) {
            $table->foreign('institution_id')->references('id')->on('institution')->onDelete('cascade');
            $table->index('institution_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('mkt_shops');
    }
}
