<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CodeOrganization extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasColumn('order_detail', 'ifood_id'))
        {
            Schema::table('order_detail', function (Blueprint $table) {
                $table->renameColumn('ifood_id', 'marketplace_order_id');            
            });
        }

        if (Schema::hasColumn('order_detail', 'created_at_ifood'))
        {
            Schema::table('order_detail', function (Blueprint $table) {
                $table->renameColumn('created_at_ifood', 'created_at_marketplace');            
            });
        }

        if (Schema::hasColumn('order_detail', 'merchant_id_ifood'))
        {
            Schema::table('order_detail', function (Blueprint $table) {
                $table->dropColumn('merchant_id_ifood');           
            });
        }

        Schema::table('order_detail', function (Blueprint $table) {
            $table->string('marketplace')->after('tracking_route')->nullable()->default('ifood');
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
