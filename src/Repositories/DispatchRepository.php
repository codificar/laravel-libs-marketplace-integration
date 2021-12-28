<?php

namespace Codificar\MarketplaceIntegration\Repositories;

use Codificar\MarketplaceIntegration\Models\MarketConfig;
use Codificar\MarketplaceIntegration\Models\Shops;
use Codificar\MarketplaceIntegration\Models\OrderDetails ;
use Codificar\MarketplaceIntegration\Models\AutomaticDispatch ;

use App\Http\Requests\api\v1\RequestCreateFormRequest;
use api\v1\RequestController;


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
        $query->whereNull('request_id');

        // needs to be associatade to a shop_id
        $query->whereNotNull('shop_id');

        // we need to order by shop_id 
        $query->orderBy('order_detail.shop_id', 'ASC');
        // the updated_at to get the time limit
        $query->orderBy('order_detail.updated_at', 'ASC');

        return $query->all();
    }

    /**
     * Dispatch the ride through the controller 
     *
     * @return array
     */
    public static function dispatch(array $shopOrderArray){
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
        $formRequest->points[]['geometry']['location']['lat'];
        $formRequest->points[]['geometry']['location']['lng'];
        $formRequest->points[]['address']                       = $shopOrderArray[0]->address;
        $formRequest->points[]['order_id'] ;

        // mount others points
        foreach($shopOrderArray as $order){
/*
             //add delivery points, point B,C, D and so on
        request.points.push({
            address: element.formatted_address,
            formatted_address: element.formatted_address,
            geometry:{
              location:{
                lat:element.latitude,
                lng:element.longitude
              }
            },
            title: this.state.alphabet[index+1].toLocaleUpperCase(),
            action: `Entregar pedido número ${element.display_id} para ${element.client_name}`,
            action_type:2,
            complement: `Cliente ${element.client_name}: ${element.complement}`,
            collect_value: element.prepaid ? null : element.order_amount,
            change: element.prepaid ? '' : element.change_for,
            form_of_receipt: element.method_payment,
            collect_pictures:0,
            collect_signature:0,
            address_instructions: `Entregar pedido número ${element.display_id} para ${element.client_name}`
          })
          request.institution_id = shop[0].institution_id
          if (!element.prepaid) {
            request.return_to_start = true;
          }

          */
            $formRequest->points[]['title'] ;
            $formRequest->points[]['action_type'] ;
            $formRequest->points[]['collect_value'] ;
            $formRequest->points[]['change'] ;
            $formRequest->points[]['form_of_receipt'] ;
            $formRequest->points[]['collect_pictures'] ;
            $formRequest->points[]['collect_signature'] ;
            $formRequest->points[]['geometry']['location']['lat'];
            $formRequest->points[]['geometry']['location']['lng'];
            $formRequest->points[]['address'] ;
            $formRequest->points[]['order_id'] = $order->display_id;
            
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
        ->get();

        // first dispatch for billing
        $paymentMode = array_reduce($paymentMethods, function($carry, $item){
            if($item->key == 'payment_billing') return \RequestCharging::PAYMENT_MODE_BILLING;
        });

        // then balance
        if(!$paymentMode){
            $paymentMode = array_reduce($paymentMethods, function($carry, $item){
                if($item->key == 'payment_balance') return \RequestCharging::PAYMENT_MODE_BALANCE;
            });
        }

        // or the first one active
        if(!$paymentMode && $paymentMethods){
            return \Settings::getPaymentMethodIndex($paymentMethods[0]->key);
        }

        if(!$paymentMode){
            throw(new Exception("There is no payment method defined as default to use on automatic dispatch"));
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

            $settings = \Settings::findByKey('payment_voucher');

            if ($settings)
                return $settings;
            else
                return 10;
        }

    }

}