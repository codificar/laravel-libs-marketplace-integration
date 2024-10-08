<?php

namespace Codificar\MarketplaceIntegration\Imports;

use App\Models\RequestPoint;
use App\Services\EstimateService;
use Carbon\Carbon;
use Codificar\MarketplaceIntegration\Lib\MarketplaceFactory;
use Codificar\MarketplaceIntegration\Models\DeliveryAddress;
use Codificar\MarketplaceIntegration\Models\MarketConfig;
use Codificar\MarketplaceIntegration\Models\OrderDetails;
use Codificar\MarketplaceIntegration\Repositories\DispatchRepository;
use Codificar\MarketplaceIntegration\Repositories\MarketplaceRepository;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class ZeDeliveryImport implements ToCollection, WithChunkReading, ShouldQueue, WithHeadingRow
{
    /**
     * The number of seconds the job can run before timing out.
     *
     * @var int
     */
    public $timeout = 300;

    public function collection(Collection $rows)
    {
        $rows = $rows->groupBy('route_id');

        foreach ($rows as $row) {
            // after group lets get just one
            $providerKey = trim($row[0]['deliveryman_email']);

            // linha vazia
            if ($providerKey == '') {
                continue;
            }

            $provider = MarketplaceRepository::getProviderByKey($providerKey);

            if (! $provider) {
                \Log::warning(sprintf('Por favor cadastre um entregador com a chave %s', $providerKey));
                continue;
            }

            $ordersArray = [];
            foreach ($row as $groupedRow) {
                $storeId = intval(str_ireplace([',', '.'], '', $groupedRow['poc_id']));
                $orderId = intval(str_ireplace([',', '.'], '', $groupedRow['order_number']));
                $createdAt = $groupedRow['order_datetime'];
                $providerKey = $groupedRow['deliveryman_email'];
                $customerId = $orderId;
                $customerName = $orderId;

                // pular se nao for para pagar
                if (trim($groupedRow['recebe_pgto']) != 'PAGAR') {
                    continue;
                }

                $marketConfig = MarketConfig::where('merchant_id', $storeId)->where('market', MarketplaceFactory::ZEDELIVERY)->first();

                if (! $marketConfig) {
                    \Log::warning(sprintf('Loja com id: %s não encontrada na importação do Zé Delivery', $storeId));
                    continue;
                }

                $order = OrderDetails::updateOrCreate(
                    [
                        'order_id'                          => $orderId,
                        'marketplace'                       => MarketplaceFactory::ZEDELIVERY
                    ],
                    [
                        'shop_id'                       => $marketConfig->shop_id,
                        'order_id'                      => $orderId,
                        'marketplace_order_id'          => $orderId,
                        'code'                          => MarketplaceRepository::CONCLUDED,
                        'full_code'                     => MarketplaceRepository::mapFullCode(MarketplaceRepository::CONCLUDED),
                        'created_at_marketplace'        => Carbon::parse($createdAt),
                        'point_id'                      => null,
                        'request_id'                    => null,
                        'client_name'                   => $customerName,
                        'merchant_id'                   => $storeId,
                        'marketplace'                   => MarketplaceFactory::ZEDELIVERY,
                        'aggregator'                    => MarketplaceFactory::ZEDELIVERY,
                        'order_type'                    => MarketplaceRepository::DELIVERY,
                        'display_id'                    => $orderId,
                        'preparation_start_date_time'   => null,
                        'customer_id'                   => $customerId,
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

                $address = DeliveryAddress::parseAddress($marketConfig->shop->full_address);

                $address = DeliveryAddress::updateOrCreate([
                    'order_id'                      => $orderId
                ], [
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

                if (! $order->request_id) {
                    $ordersArray[] = $order;
                }
            }

            // create a ride from orders array
            if (count($ordersArray)) {
                self::createRide($ordersArray, $provider);
            }
        }
    }

    /**
     * Chunk size for import.
     */
    public function chunkSize(): int
    {
        return 100;
    }

    /**
     * Create Ride from orders.
     *
     * @return array
     */
    public static function createRide(array $shopOrderArray, \Provider $provider)
    {

        // ja criada a corrida
        if ($shopOrderArray[0]->request_id) {
            return;
        }

        $ride = new \Requests;
        $shop = $shopOrderArray[0]->shop;

        // locationId
        $locationId = null;

        //estimativa
        $ride->type_id = DispatchRepository::getProviderType($shop->institution_id);
        $estimate = EstimateService::estimateProcessPriceTable(count($shopOrderArray) * 4, count($shopOrderArray) * 2.5, count($shopOrderArray), $ride->type_id, null, $locationId, $shop->institution_id, null, false, null, null, null, null, null, null);
        $ride->estimate_provider_value = is_array($estimate) && array_key_exists('provider_value', $estimate) ? $estimate['provider_value'] : $estimate->estimate_provider_value;
        $ride->estimate_price = is_array($estimate) && array_key_exists('estimated_price', $estimate) ? $estimate['estimated_price'] : $estimate->estimated_price;
        $ride->estimate_distance = is_array($estimate) && array_key_exists('distance', $estimate) ? $estimate['distance'] : $estimate->distance;
        $ride->estimate_time = is_array($estimate) && array_key_exists('duration', $estimate) ? $estimate['duration'] : $estimate->duration;
        $ride->price_id = is_array($estimate) && array_key_exists('price_id', $estimate) ? $estimate['price_id'] : null;

        //demais dados da corrida
        $ride->user_id = $shop->institution->default_user_id;
        $ride->payment_mode = DispatchRepository::getPaymentMode($shop->institution_id);
        $ride->payment_id = null;
        $ride->time_zone = env('TIMEZONE', 'UTC');
        $ride->src_address = $shop->full_address;
        $ride->dest_address = $shop->full_address;
        $ride->created_at = $shopOrderArray[0]->created_at_marketplace;
        $ride->updated_at = $shopOrderArray[0]->created_at_marketplace;
        $ride->request_start_time = $shopOrderArray[0]->created_at_marketplace;
        $ride->provider_acceptance_time = $shopOrderArray[0]->created_at_marketplace;
        $ride->provider_started_time = $shopOrderArray[0]->created_at_marketplace;
        $ride->request_start_time = $shopOrderArray[0]->created_at_marketplace;
        $ride->provider_arrived_time = $shopOrderArray[0]->created_at_marketplace;
        $ride->request_finish_time = $shopOrderArray[0]->created_at_marketplace;
        $ride->latitude = $shop->latitude;
        $ride->longitude = $shop->longitude;
        $ride->D_latitude = $shop->latitude;
        $ride->D_longitude = $shop->longitude;
        $ride->req_create_user_time = $shopOrderArray[0]->created_at_marketplace;
        $ride->current_provider = $provider->id;
        $ride->confirmed_provider = $provider->id;
        $ride->location_id = $locationId;
        $ride->institution_id = $shop->institution_id;
        $ride->emergency_contact_user = 0;
        $ride->return_to_start = true;
        $ride->category_id = null;
        $ride->costcentre_id = null;
        $ride->is_automation = true;
        $ride->is_completed = true;
        $ride->is_cancelled = false;
        $ride->own_motoboy = true;

        //salva a corrida
        $ride->save();

        //salva o meta
        $requestMeta = new \RequestMeta;
        $requestMeta->request_id = $ride->id;
        $requestMeta->provider_id = $provider->id;
        $requestMeta->resend_id = $ride->resend_id;
        $requestMeta->number_resend = 1; //primeiro disparo
        $requestMeta->save();

        // associa o servico
        $requestService = new \RequestServices;
        $requestService->request_id = $ride->id;
        $requestService->type = $ride->type_id;
        $requestService->category_id = null;
        $requestService->save();

        // salvar pontos para estatisticas depois

        $letter = 1;
        //primeiro ponto / origem da loja
        $requestPoint = new RequestPoint();
        $requestPoint->title = chr(64 + $letter);
        $requestPoint->action_type = RequestPoint::action_collect_order;
        $requestPoint->action = trans('marketplace-integration::zedelivery.action_collect_order');
        $requestPoint->latitude = $shop->latitude;
        $requestPoint->longitude = $shop->longitude;
        $requestPoint->address = $shop->full_address;
        $requestPoint->request_id = $ride->id;
        $requestPoint->start_time = $shopOrderArray[0]->created_at_marketplace;
        $requestPoint->arrival_time = $shopOrderArray[0]->created_at_marketplace;
        $requestPoint->finish_time = $shopOrderArray[0]->created_at_marketplace;
        $requestPoint->save();
        $letter += 1;

        // pontos de entrega
        foreach ($shopOrderArray as $key => $order) {

            //novo ponto
            $requestPoint = new RequestPoint();
            $requestPoint->title = chr(64 + $letter);
            $requestPoint->action_type = RequestPoint::action_delivery_order;
            $requestPoint->action = trans('marketplace-integration::zedelivery.action_delivery_order', ['orderId' => $order->order_id]);
            $requestPoint->latitude = $shop->latitude;
            $requestPoint->longitude = $shop->longitude;
            $requestPoint->address = $shop->full_address;
            $requestPoint->request_id = $ride->id;
            $requestPoint->start_time = $order->created_at_marketplace;
            $requestPoint->arrival_time = $order->created_at_marketplace;
            $requestPoint->finish_time = $order->created_at_marketplace;
            $requestPoint->save();
            $letter += 1;

            $order->request_id = $ride->id;
            $order->point_id = $requestPoint->id;
            $order->save();
        }

        //retorno ponto / origem da loja
        $requestPoint = $requestPoint->replicate();
        $requestPoint->title = '@';
        $requestPoint->action_type = RequestPoint::action_return;
        $requestPoint->action = trans('marketplace-integration::zedelivery.return_to_start');
        $requestPoint->save();

        // set payments for user
        $ride->completeCharge();
    }
}
