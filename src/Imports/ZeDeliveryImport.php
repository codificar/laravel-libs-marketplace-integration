<?php

namespace Codificar\MarketplaceIntegration\Imports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Illuminate\Contracts\Queue\ShouldQueue;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

use Codificar\MarketplaceIntegration\Lib\MarketplaceFactory;

use Codificar\MarketplaceIntegration\Repositories\MarketplaceRepository;
use Codificar\MarketplaceIntegration\Repositories\DispatchRepository ;

use Codificar\MarketplaceIntegration\Models\MarketConfig;
use Codificar\MarketplaceIntegration\Models\Shops;
use Codificar\MarketplaceIntegration\Models\OrderDetails;


use App\Models\RequestPoint;


use App\Services\EstimateService;

class ZeDeliveryImport implements ToCollection, WithChunkReading, ShouldQueue, WithHeadingRow
{
    public function collection(Collection $rows)
    {
        
        $rows = $rows->groupBy('route_id');
    
        foreach ($rows as $row) 
        {
            // after group lets get just one
            $ordersArray = [];
            foreach($row as $groupedRow){
                $storeId        = $groupedRow['poc_id'];
                $orderId        = $groupedRow['order_number'];
                $createdAt      = $groupedRow['order_datetime'];
                $providerKey    = $groupedRow['deliveryman_email'];
                $customerId     = $orderId ;
                $customerName   = $orderId ;

                $marketConfig = MarketConfig::where('merchant_id', $storeId)->where('market', MarketplaceFactory::ZEDELIVERY)->first();

                $order = OrderDetails::updateOrCreate([
                    'order_id'                          => $orderId,
                    'marketplace'                       => MarketplaceFactory::ZEDELIVERY
                    ],[
                        'shop_id'                       => $marketConfig->shop_id ,
                        'order_id'                      => $orderId,
                        'marketplace_order_id'          => $orderId,
                        'code'                          => MarketplaceRepository::CONCLUDED,
                        'full_code'                     => MarketplaceRepository::mapFullCode(MarketplaceRepository::CONCLUDED),
                        'created_at_marketplace'        => Carbon::parse($createdAt),
                        'point_id'                      => null,
                        'request_id'                    => null,
                        'client_name'                   => $customerName ,
                        'merchant_id'                   => $storeId,
                        'marketplace'                   => MarketplaceFactory::ZEDELIVERY,
                        'aggregator'                    => MarketplaceFactory::ZEDELIVERY,
                        'order_type'                    => MarketplaceRepository::DELIVERY,
                        'display_id'                    => $orderId,
                        'preparation_start_date_time'   => null,
                        'customer_id'                   => $customerId ,
                        'sub_total'                     => 0,
                        'delivery_fee'                  => 0,
                        'benefits'                      => 0,
                        'order_amount'                  => 0,
                        'method_payment'                => '',
                        'prepaid'                       => 0,
                        'change_for'                    => 0,
                        'card_brand'                    => null,
                        'extra_info'                    => null
                    ]
                );

                // always will be at the same points
                $calculatedDistance = 0;

                $address = DeliveryAddress::parseAddress($marketConfig->shop->full_address) ;

                $address = DeliveryAddress::updateOrCreate([
                    'order_id'                      => $orderId
                ],[
                    'customer_id'                   => $customerId,
                    'street_name'                   => $address['street_name'],
                    'street_number'                 => $address['street_number'],
                    'formatted_address'             => $marketConfig->shop->full_address,
                    'neighborhood'                  => $address['neighborhood'],
                    'complement'                    => '',
                    'postal_code'                   => '',
                    'city'                          => '',
                    'state'                         => '',
                    'country'                       => 'BR',
                    'latitude'                      => $marketConfig->shop->latitude,
                    'longitude'                     => $marketConfig->shop->longitude,
                    'distance'                      => $calculatedDistance,
                ]);

                $ordersArray[] = $order ;
            }
            
            // create a ride from orders array
            self::createRide($ordersArray, MarketplaceRepository::getProviderByKey($providerKey));
           
        }
    }

    /**
     * Chunk size for import
     */
    public function chunkSize(): int
    {
        return 20000;
    }

    /**
     * Create Ride from orders
     *
     * @return array
     */
    public static function createRide(array $shopOrderArray, $provider){

        // ja criada a corrida
        if($shopOrderArray[0]->request_id)
            return ;        
        

        // locationId
        $locationId = null ;

        //estimativa
        $ride->type_id = DispatchRepository::getProviderType($shopOrderArray[0]->institution_id);
        $estimate = EstimateService::estimateProcessPriceTable(0, 0, count($shopOrderArray), $ride->type_id, null, $locationId, $shopOrderArray[0]->institution_id, null, false, null, null, null, null, null, null);
        $ride->estimate_provider_value = is_array($estimate) && array_key_exists('provider_value', $estimate) ? $estimate['provider_value'] : $estimate->estimate_provider_value;
        $ride->estimate_price = is_array($estimate) && array_key_exists('estimated_price', $estimate)  ? $estimate['estimated_price'] : $estimate->estimated_price;
        $ride->estimate_distance = is_array($estimate) && array_key_exists('distance', $estimate)  ? $estimate['distance'] : $estimate->distance;
        $ride->estimate_time = is_array($estimate) && array_key_exists('duration', $estimate)  ? $estimate['duration'] : $estimate->duration;
        $ride->price_id = is_array($estimate) && array_key_exists('price_id', $estimate) ? $estimate['price_id'] : null;

        //demais dados da corrida
        $ride->user_id = $shopOrderArray[0]->default_user_id;
        $ride->payment_mode = DispatchRepository::getPaymentMode($shopOrderArray[0]->institution_id);
        $ride->payment_id =  null;
        $ride->time_zone = null;
        $ride->src_address = $shopOrderArray[0]->shop->full_address;
        $ride->dest_address = $shopOrderArray[0]->shop->full_address;
        $ride->request_start_time = null;
        $ride->latitude = $shopOrderArray[0]->shop->latitude;
        $ride->longitude = $shopOrderArray[0]->shop->longitude;
        $ride->D_latitude = $shopOrderArray[0]->shop->latitude;
        $ride->D_longitude = $shopOrderArray[0]->shop->longitude;
        $ride->req_create_user_time = time();
        $ride->current_provider = $provider->id ; // carrega o prestador mais proximo do local de acordo com o tipo/categoria e que não esteja recebendo chamada
        $ride->location_id = $locationId;
        $ride->institution_id = $shopOrderArray[0]->shop->institution_id;
        $ride->emergency_contact_user = null;
        $ride->return_to_start = true;
        
        $ride->category_id = null;
		$ride->costcentre_id = null;
        $ride->is_automation = false;
        
        //salva a corrida
        $ride->save();


        // set payments for user
        $ride->completeCharge();

        // salvar pontos para estatisticas depois

        $letter = 1 ;
        //primeiro ponto / orige da loja
        $point['title']                         =  chr(64 + $letter);
        $point['action']                        = trans('requests.take_package');
        $point['action_type']                   = RequestPoint::action_take_package;
        $point['collect_value']                 = null ;
        $point['change']                        = null ;
        $point['form_of_receipt']               = null ;
        $point['collect_pictures']              = false;
        $point['collect_signature']             = false ;
        $point['latitude']                      = $shopOrderArray[0]->shop->latitude;
        $point['longitude']                     = $shopOrderArray[0]->shop->longitude;
        $point['address']                       = $shopOrderArray[0]->shop->full_address;
        $point['order_id']                      = null;

        //novo ponto
        $requestPoint = new RequestPoint();
        $requestPoint->store($point, $key - 1, $estimate);
        $letter += 1;


        foreach ($shopOrderArray as $key => $order) {

            //primeiro ponto / orige da loja
            $point['title']                         =  chr(64 + $letter);
            $point['action']                        = trans('requests.take_package');
            $point['action_type']                   = RequestPoint::action_take_package;
            $point['collect_value']                 = null ;
            $point['change']                        = null ;
            $point['form_of_receipt']               = null ;
            $point['collect_pictures']              = false;
            $point['collect_signature']             = false ;
            $point['latitude']                      = $shopOrderArray[0]->shop->latitude;
            $point['longitude']                     = $shopOrderArray[0]->shop->longitude;
            $point['address']                       = $shopOrderArray[0]->shop->full_address;
            $point['order_id']                      = null;

            //novo ponto
            $requestPoint = new RequestPoint();
            $requestPoint->store($point, $key - 1, $estimate);
            $letter += 1;

            $order->request_id  = $ride->id ;
            $order->point_id    = $requestPoint->id ;
            $order->save();

        }
    }

}