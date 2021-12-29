<?php

namespace Codificar\MarketplaceIntegration\Repositories;

use Codificar\MarketplaceIntegration\Models\MarketConfig;
use Codificar\MarketplaceIntegration\Models\Shops;
use Codificar\MarketplaceIntegration\Models\OrderDetails ;
use Codificar\MarketplaceIntegration\Models\AutomaticDispatch ;

use App\Http\Requests\RequestCreateFormRequest;
use api\v1\RequestController;

use Carbon\Carbon;


/**
 * Class DispatchRepository
 * 
 */
class DispatchRepository
{
    /**
     * @author Raphael Cangucu
     *
     * Method that get all avalaible order to dispatch automatically
     * @return [] OrderDetails
     */
    public static function getOrders()
    {
        $query = OrderDetails::query();

        // set code to all orders available to delivery
        $query->whereIn('code', [OrderDetails::CONFIRMED, OrderDetails::REQUEST_DRIVER_AVAILABILITY])
            ->join('delivery_address', 'order_detail.order_id', '=', 'delivery_address.order_id')
            ->join('shops', 'order_detail.shop_id', '=', 'shops.id')
            ->join('market_config', 'order_detail.merchant_id', '=', 'market_config.merchant_id');

        // the request should not be created
        $query->whereNull('order_detail.request_id');

        // needs to be associatade to a shop_id
        $query->whereNotNull('order_detail.shop_id');

        // will get only for the last hour
        $query->where('order_detail.created_at', '>', Carbon::now()->addHours(-1));

        // we need to order by shop_id 
        $query->orderBy('order_detail.shop_id', 'ASC');
        // the updated_at to get the time limit
        $query->orderBy('order_detail.updated_at', 'ASC');

        $query->select(
            [
                'order_detail.*', 
                'market_config.longitude as market_longitude', 
                'market_config.latitude as market_latitude',
                'market_config.address as market_address',
                'delivery_address.longitude as delivery_longitude', 
                'delivery_address.latitude as delivery_latitude',
                'delivery_address.street_name as delivery_address'
            ]
        );

        return $query->get();
    }

    /**
     * Dispatch the ride through the controller 
     *
     * @return array
     */
    public static function createRide(array $shopOrderArray){
        $formRequest        = new RequestCreateFormRequest();
        $requestController  = new RequestController();
        
        $formRequest->institution_id        =   $shopOrderArray[0]->institution_id;
        $formRequest->token                 =   null;
        $formRequest->provider_type         =   self::getProviderType($shopOrderArray[0]->institution_id);
        $formRequest->payment_mode          =   self::getPaymentMode($shopOrderArray[0]->institution_id);;
        $formRequest->return_to_start       =   false ;
        $formRequest->points                =   [];

        $letter = 0;
        // first point it is the default shop location
        $formRequest->points[]['title']                         =  chr(64 + $letter);
        $formRequest->points[]['action']                        = 4;
        $formRequest->points[]['action_type']                   = 4;
        $formRequest->points[]['collect_value']                 = null ;
        $formRequest->points[]['change']                        = null ;
        $formRequest->points[]['form_of_receipt']               = null ;
        $formRequest->points[]['collect_pictures']              = false;
        $formRequest->points[]['collect_signature']             = false ;
        $formRequest->points[]['geometry']['location']['lat']   = $shopOrderArray[0]->market_latitude;
        $formRequest->points[]['geometry']['location']['lng']   = $shopOrderArray[0]->market_longitude;
        $formRequest->points[]['address']                       = $shopOrderArray[0]->market_address;
        $formRequest->points[]['order_id']                      = null;

        // mount others points
        foreach($shopOrderArray as $order){

            $formRequest->points[]['title']                         = chr(64 + (++$letter)) ;
            $formRequest->points[]['action']                        = 2;
            $formRequest->points[]['action_type']                   = 2;
            $formRequest->points[]['collect_value']                 = $order->prepaid ? null : $order->order_amount ;
            $formRequest->points[]['change']                        = $order->prepaid ? null : $order->change_for ;
            $formRequest->points[]['form_of_receipt']               = $order->method_payment ;
            $formRequest->points[]['collect_pictures']              = false;
            $formRequest->points[]['collect_signature']             = false;
            $formRequest->points[]['geometry']['location']['lat']   = $order->delivery_latitude;
            $formRequest->points[]['geometry']['location']['lng']   = $order->delivery_longitude;
            $formRequest->points[]['address']                       = $order->delivery_address;
            $formRequest->points[]['order_id']                      = $order->display_id;

            // if any order is not prepaid, should return
            if(!$order->prepaid) $formRequest->return_to_start  =   true ;
            
        }


        return $requestController->create($formRequest);
    }

    /**
     * Get the provider_mode configuration
     *
     * @return integer provider_mode
     */
    public static function getPaymentMode($institutionId){
        $paymentMethods = \PaymentMethods::whereInstitutionId($institutionId)->where('settings.value', '=', true)
        ->where('payment_methods.is_active', '=', true)
        ->join('settings', 'settings.id', '=', 'payment_methods.payment_settings_id')
        ->select(array('payment_methods.id', 'payment_methods.name', 'payment_methods.is_active', 'settings.key'))
        ->get()->toArray();

        $paymentMode = null ;
        
        if($paymentMethods){
            // first dispatch for billing
            $paymentMode = array_reduce($paymentMethods, function($carry, $item){
                if($item['key'] == 'payment_billing') return \RequestCharging::PAYMENT_MODE_BILLING;
            });

            // then balance
            if(!$paymentMode){
                $paymentMode = array_reduce($paymentMethods, function($carry, $item){
                    if($item['key'] == 'payment_balance') return \RequestCharging::PAYMENT_MODE_BALANCE;
                });
            }

            // or the first one active
            if(!$paymentMode && $paymentMethods){
                return \Settings::getPaymentMethodIndex($paymentMethods[0]->key);
            }
        }

        if(!$paymentMode){
            \Log::error("There is no payment method defined as default to use on automatic dispatch for institutionId: ". $institutionId);
            return \RequestCharging::PAYMENT_MODE_BALANCE;
        }

        return $paymentMode ;
        
    }

    /**
     * Get the provider_type configuration
     *
     * @return integer provider_type
     */
    public static function getProviderType($institutionId){

        $query = AutomaticDispatch::query();

        $query->where('institution_id', '=', $institutionId);
        
        $automaticDispatch = $query->first();

        if($automaticDispatch){
            return $automaticDispatch->provider_type_id ;
        }
        else {

            $query = \ProviderType::query();
            $query->where('is_default', '=', 1);    

            $providerType = $query->first();

            if($providerType)   return $providerType->id ;
            else {
                throw(new Exception("There is no provider type defined as default to use on automatic dispatch"));
            };
        }
    }

     /**
     * Get the max time limit in minutes to automatic dispatch the ride
     *
     * @return integer 
     */
    public static function getTimeLimit($institutionId){

        $query = AutomaticDispatch::query();

        $query->where('institution_id', '=', $institutionId);
        
        $automaticDispatch = $query->first();

        if($automaticDispatch){
            return $automaticDispatch->wait_time_limit ;
        }
        else {

            $settings = \Settings::findByKey('dispatch_wait_time_limit');

            if ($settings)
                return $settings;
            else
                return 10;
        }

    }

}