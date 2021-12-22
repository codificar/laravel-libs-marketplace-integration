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
            \Settings::updateOrCreate(array('key' => 'ifood_auth_token', 'value' => 'n/a'));
        }
        catch(Exception $ex){

        }
        try{
            \Settings::updateOrCreate(array('key' => 'ifood_expiry_token', 'value' => Carbon::now()->addHours(-6)));
        
        }
        catch(Exception $ex){

        }


        Schema::create('automatic_dispatch', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('institution_id')->unsigned()->references('id')->on('institution')->onUpdate('CASCACE')->onDelete('CASCADE');
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
