<?php

namespace Codificar\MarketplaceIntegration\Repositories;

use Codificar\MarketplaceIntegration\Models\MarketConfig;
use Codificar\MarketplaceIntegration\Models\Shops;
use Codificar\MarketplaceIntegration\Models\OrderDetails ;
use Codificar\MarketplaceIntegration\Models\AutomaticDispatch ;

use App\Http\Requests\RequestCreateFormRequest;
use api\v1\RequestController;
use App\Models\RequestPoint ;

use Carbon\Carbon;



/**
 * Class DispatchRepository
 * 
 */
class DispatchRepository
{
    const SIZE_LIMIT = 3;

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
            ->join('institution', 'institution.id', '=', 'shops.institution_id')
            ->join('automatic_dispatch', 'institution.id', '=', 'automatic_dispatch.institution_id')
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
                'institution.default_user_id',
                'market_config.longitude as market_longitude', 
                'market_config.latitude as market_latitude',
                'market_config.address as market_address',
                'delivery_address.longitude as delivery_longitude', 
                'delivery_address.latitude as delivery_latitude',
                \DB::raw('CONCAT(delivery_address.formatted_address, " - ", delivery_address.neighborhood) as delivery_address')
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
        $formRequest->user_id               =   $shopOrderArray[0]->default_user_id;
        $formRequest->token                 =   null;
        $formRequest->provider_type         =   self::getProviderType($shopOrderArray[0]->institution_id);
        $formRequest->payment_mode          =   self::getPaymentMode($shopOrderArray[0]->institution_id);;
        $formRequest->return_to_start       =   false ;
        $formRequest->is_automation         =   true ;
        $formRequest->is_admin              =   true ;
        $formRequest->points                =   [];
        $point                              =   [];

        $letter = 1;
        // first point it is the default shop location
        $point['title']                         =  chr(64 + $letter);
        $point['action']                        = trans('requests.take_package');
        $point['action_type']                   = RequestPoint::action_take_package;
        $point['collect_value']                 = null ;
        $point['change']                        = null ;
        $point['form_of_receipt']               = null ;
        $point['collect_pictures']              = false;
        $point['collect_signature']             = false ;
        $point['geometry']['location']['lat']   = $shopOrderArray[0]->market_latitude;
        $point['geometry']['location']['lng']   = $shopOrderArray[0]->market_longitude;
        $point['address']                       = $shopOrderArray[0]->market_formatted_address;
        $point['order_id']                      = null;
        $formRequest->points[] = $point ;

        // mount others points
        foreach($shopOrderArray as $order){
            $point                                  =   [];
            $point['title']                         = chr(64 + $letter) ;
            $point['action']                        = trans('requests.leave_package');
            $point['action_type']                   = RequestPoint::action_leave_package;
            $point['collect_value']                 = $order->prepaid ? null : $order->order_amount ;
            $point['change']                        = $order->prepaid ? null : $order->change_for ;
            $point['form_of_receipt']               = $order->method_payment ;
            $point['collect_pictures']              = false;
            $point['collect_signature']             = false;
            $point['geometry']['location']['lat']   = $order->delivery_latitude;
            $point['geometry']['location']['lng']   = $order->delivery_longitude;
            $point['address']                       = $order->delivery_address;
            $point['order_id']                      = $order->id;
            $formRequest->points[] = $point ;

            // if any order is not prepaid, should return
            if(!$order->prepaid) $formRequest->return_to_start  =   true ;

            $letter += 1;
            
        }
        //dd($formRequest->points);
        $formRequest->rules();
        return $requestController->create($formRequest);
    }

    /**
     * Get the provider_mode configuration
     *
     * @return integer provider_mode
     */
    public static function getPaymentMode($institutionId = null){

        $paymentMode = null ;

        if($institutionId) {
            $paymentMethods = \PaymentMethods::whereInstitutionId($institutionId)->where('settings.value', '=', true)
            ->where('payment_methods.is_active', '=', true)
            ->join('settings', 'settings.id', '=', 'payment_methods.payment_settings_id')
            ->select(array('payment_methods.id', 'payment_methods.name', 'payment_methods.is_active', 'settings.key'))
            ->get()->toArray();
            
            if($paymentMethods){
                // first dispatch for billing
                $paymentMode = array_reduce($paymentMethods, function($carry, $item){
                    if($item['key'] == 'payment_billing') $carry = \RequestCharging::PAYMENT_MODE_BILLING;
                    return $carry;
                });

                // then balance
                if(!$paymentMode){
                    $paymentMode = array_reduce($paymentMethods, function($carry, $item){
                        if($item['key'] == 'payment_balance') $carry = \RequestCharging::PAYMENT_MODE_BALANCE;
                        return $carry;
                    });
                }

                // or the first one active
                if(!$paymentMode && $paymentMethods){
                    return  \Settings::getPaymentMethodIndex($paymentMethods[0]['key']);
                }
            }
        }

        if(!$paymentMode){
            \Log::warning("There is no payment method defined as default to use on automatic dispatch for institutionId: ". $institutionId);
            return \RequestCharging::PAYMENT_MODE_BALANCE;
        }

        return $paymentMode ;
        
    }

    /**
     * Get the AutomaticDispatch model
     *
     * @return AutomaticDispatch automaticDispatch
     */
    public static function getAutomaticDispatch($institutionId){
        
        if(!$institutionId) return null;

        $query = AutomaticDispatch::query();

        $query->where('institution_id', '=', $institutionId);
        
        $automaticDispatch = $query->first();

        return $automaticDispatch ;
    }

    /**
     * Get the provider_type configuration
     *
     * @return integer provider_type
     */
    public static function getProviderType($institutionId = null){

        $automaticDispatch = self::getAutomaticDispatch($institutionId);

        if($automaticDispatch){
            return $automaticDispatch->provider_type_id ;
        }
        else {

            $query = \ProviderType::query();
            $query->where('is_default', '=', 1);    

            $providerType = $query->first();

            if($providerType)   return $providerType->id ;
            else {
                \Log::warning("There is no provider type defined as default to use on automatic dispatch. We will get the first");
                $providerType = \ProviderType::query()->first();
                return $providerType->id ;
            };
        }
    }

     /**
     * Get the max time limit in minutes to automatic dispatch the ride
     *
     * @return integer 
     */
    public static function getTimeLimit($institutionId = null){

        $automaticDispatch = self::getAutomaticDispatch($institutionId);

        if($automaticDispatch){
            return $automaticDispatch->wait_time_limit ;
        }
        else {

            $settings =  \Settings::findByKey('dispatch_wait_time_limit');

            if ($settings)
                return $settings;
            else
                return 10;
        }

    }


    /**
     * Update the point and request_id at an order
     * 
     * @return OrderDetail
     */
    public static function setPoint($orderId, $pointId, $requestId)
    {
      
        $order = OrderDetails::where([
            'id'                       => $orderId
        ])->update([
                'request_id'                => $requestId,
                'point_id'                  => $pointId,
                'tracking_route'            => $requestId,
        ]);

        return $order;
    }


    /**
     * Get Logged InstitutionId
     *
     * @return integer 
     */
    public static function getInstitutionIdFromGuard(){

        $admin = \Auth::guard('web')->user();

        if (!$admin || !$admin->AdminInstitution) {
            $admin = \Auth::guard('web_corp')->user();
        }
		
		if($admin) {
            $adminInstitution = \AdminInstitution::where('admin_id', '=', $admin->id)->first();
		    if($adminInstitution)
                return $adminInstitution->institution_id ;
        };

        return null;
    }

}