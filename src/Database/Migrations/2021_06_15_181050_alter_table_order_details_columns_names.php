<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTableOrderDetailsColumnsNames extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        # Table Order Details
        if (!Schema::hasColumn('order_detail', 'point_id'))
        {
            Schema::table('order_detail', function (Blueprint $table) {  
                $table->string('point_id')->after('request_id')->index();
            });
        }
        if (Schema::hasColumn('order_detail', 'orderId'))
        {
            Schema::table('order_detail', function (Blueprint $table) {
                $table->renameColumn('orderId', 'order_id');            
            });
        }
        if (Schema::hasColumn('order_detail', 'fullCode'))
        {
            Schema::table('order_detail', function (Blueprint $table) {
                $table->renameColumn('fullCode', 'full_code');            
            });
        }
        if (Schema::hasColumn('order_detail', 'ifoodId'))
        {
            Schema::table('order_detail', function (Blueprint $table) {
                $table->renameColumn('ifoodId', 'ifood_id');            
            });
        }
        if (Schema::hasColumn('order_detail', 'orderType'))
        {
            Schema::table('order_detail', function (Blueprint $table) {
                $table->renameColumn('orderType', 'order_type');            
            });
        }
        if (Schema::hasColumn('order_detail', 'displayId'))
        {
            Schema::table('order_detail', function (Blueprint $table) {
                $table->renameColumn('displayId', 'display_id');            
            });
        }
        if (Schema::hasColumn('order_detail', 'createdAt'))
        {
            Schema::table('order_detail', function (Blueprint $table) {
                $table->renameColumn('createdAt', 'created_at_ifood');            
            });
        }
        if (Schema::hasColumn('order_detail', 'preparationStartDateTime'))
        {
            Schema::table('order_detail', function (Blueprint $table) {
                $table->renameColumn('preparationStartDateTime', 'praparation_start_date_time');            
            });
        }
        if (Schema::hasColumn('order_detail', 'merchantId'))
        {
            Schema::table('order_detail', function (Blueprint $table) {
                $table->renameColumn('merchantId', 'merchant_id_ifood');            
            });
        }
        if (Schema::hasColumn('order_detail', 'customerId'))
        {
            Schema::table('order_detail', function (Blueprint $table) {
                $table->renameColumn('customerId', 'customer_id');            
            });
        }
        if (Schema::hasColumn('order_detail', 'subTotal'))
        {
            Schema::table('order_detail', function (Blueprint $table) {
                $table->renameColumn('subTotal', 'sub_total');            
            });
        }
        if (Schema::hasColumn('order_detail', 'deliveryFee'))
        {
            Schema::table('order_detail', function (Blueprint $table) {
                $table->renameColumn('deliveryFee', 'delivery_fee');            
            });
        }
        if (Schema::hasColumn('order_detail', 'orderAmount'))
        {
            Schema::table('order_detail', function (Blueprint $table) {
                $table->renameColumn('orderAmount', 'order_amount');            
            });
        }
        if (Schema::hasColumn('order_detail', 'paymentsId'))
        {
            Schema::table('order_detail', function (Blueprint $table) {
                $table->renameColumn('paymentsId', 'payments_id');            
            });
        }

        # Table Order Items
        if (Schema::hasColumn('order_items', 'orderId'))
        {
            Schema::table('order_items', function (Blueprint $table) {
                $table->renameColumn('orderId', 'order_id');            
            });
        }
        if (Schema::hasColumn('order_items', 'itemId'))
        {
            Schema::table('order_items', function (Blueprint $table) {
                $table->renameColumn('itemId', 'item_id');            
            });
        }
        if (Schema::hasColumn('order_items', 'externalCode'))
        {
            Schema::table('order_items', function (Blueprint $table) {
                $table->renameColumn('externalCode', 'external_code');            
            });
        }
        if (Schema::hasColumn('order_items', 'unitPrice'))
        {
            Schema::table('order_items', function (Blueprint $table) {
                $table->renameColumn('unitPrice', 'unit_price');            
            });
        }
        if (Schema::hasColumn('order_items', 'options_price'))
        {
            Schema::table('order_items', function (Blueprint $table) {
                $table->renameColumn('options_price', 'options_price');            
            });
        }
        if (Schema::hasColumn('order_items', 'opntions'))
        {
            Schema::table('order_items', function (Blueprint $table) {
                $table->renameColumn('opntions', 'options');            
            });
        }

        #Table delivery_address
        if (Schema::hasColumn('delivery_address', 'orderId'))
        {
            Schema::table('delivery_address', function (Blueprint $table) {
                $table->renameColumn('orderId', 'order_id');            
            });
        }
        if (Schema::hasColumn('delivery_address', 'customerId'))
        {
            Schema::table('delivery_address', function (Blueprint $table) {
                $table->renameColumn('customerId', 'customer_id');            
            });
        }
        if (Schema::hasColumn('delivery_address', 'streetName'))
        {
            Schema::table('delivery_address', function (Blueprint $table) {
                $table->renameColumn('streetName', 'street_name');            
            });
        }
        if (Schema::hasColumn('delivery_address', 'streetNumber'))
        {
            Schema::table('delivery_address', function (Blueprint $table) {
                $table->renameColumn('streetNumber', 'street_number');            
            });
        }
        if (Schema::hasColumn('delivery_address', 'formattedAddress'))
        {
            Schema::table('delivery_address', function (Blueprint $table) {
                $table->renameColumn('formattedAddress', 'formatted_address');            
            });
        }
        if (Schema::hasColumn('delivery_address', 'streetNumber'))
        {
            Schema::table('delivery_address', function (Blueprint $table) {
                $table->renameColumn('streetNumber', 'street_number');            
            });
        }
        if (Schema::hasColumn('delivery_address', 'postalCode'))
        {
            Schema::table('delivery_address', function (Blueprint $table) {
                $table->renameColumn('postalCode', 'postal_code');            
            });
        }
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
