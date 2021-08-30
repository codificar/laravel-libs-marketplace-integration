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
        \Settings::updateOrCreate(array('key' => 'ifood_auth_token', 'value' => 'n/a'));
        \Settings::updateOrCreate(array('key' => 'ifood_expiry_token', 'value' => Carbon::now()->addHours(-6)));
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
