<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Carbon\Carbon;

class RunAddIfoodOtherKeysSeeder extends Migration
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

        Schema::table('settings', function (Blueprint $table) {  
            $table->string('value', 2000)->change();
        });

        Schema::table('order_detail', function (Blueprint $table) {  
            $table->integer('shop_id')->nullable()->change();
        });
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
