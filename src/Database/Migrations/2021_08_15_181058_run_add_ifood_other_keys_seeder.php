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

        DB::statement('ALTER TABLE `order_detail` CHANGE `shop_id` `shop_id` INT(11) NULL;');
        DB::statement('ALTER TABLE `order_detail` ADD INDEX(`shop_id`);');

        DB::statement('ALTER TABLE `order_detail` CHANGE `request_id` `request_id` INT(11) NULL;');
        DB::statement('ALTER TABLE `order_detail` ADD INDEX(`request_id`);');
        

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
