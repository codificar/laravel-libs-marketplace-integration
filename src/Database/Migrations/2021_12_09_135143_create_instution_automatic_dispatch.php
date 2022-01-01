<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateInstutionAutomaticDispatch extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        try{
            \Settings::updateOrCreate(array('key' => 'automatic_dispatch_enabled', 'value' => false));
        }
        catch(Exception $ex){

        }

        try{
            \Settings::updateOrCreate(array('key' => 'dispatch_wait_time_limit', 'value' => 10));
        }
        catch(Exception $ex){

        }
        try{
            \Settings::updateOrCreate(array('key' => 'dispatch_max_delivery', 'value' => 3));
        
        }
        catch(Exception $ex){

        }


        Schema::create('automatic_dispatch', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('institution_id')->unsigned()->references('id')->on('institution')->onUpdate('CASCACE')->onDelete('CASCADE');
            $table->integer('provider_type_id')->unsigned()->nullable()->references('id')->on('provider_type')->onUpdate('CASCACE')->onDelete('CASCADE');
            $table->integer('wait_time_limit')->unsigned()->default(600); // in seconds 
            $table->integer('max_delivery')->unsigned()->default(3); // 3.3 entregas é o padrão da hey
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
        Schema::dropIfExists('automatic_dispatch');
    }
}
