<?php

namespace Codificar\MarketplaceIntegration\Repositories;

use App\Services\Ride\CancelRideService;
use Codificar\MarketplaceIntegration\Lib\MarketplaceFactory;
use Codificar\MarketplaceIntegration\Models\OrderDetails;

/**
 * Class MarketplaceRepository.
 */
class MarketplaceRepository
{
    // Constants from iFood

    // ORDER_STATUS
    const PLACED = 'PLC'; // Novo Pedido na plataforma
    const CONFIRMED = 'CFM'; // Pedido foi confirmado e será preparado
    const READY_TO_PICKUP = 'RTP'; // ndica que o pedido está pronto para ser retirado (Pra Retirar ou Na Mesa)
    const DISPATCHED = 'DSP'; // Indica que o pedido saiu para entrega (Delivery)
    const CONCLUDED = 'CON'; // Pedido foi concluído
    const CANCELLED = 'CAN'; // Pedido foi Cancelado

    // DELIVERY
    const ASSIGN_DRIVER = 'ADR'; // Um entregador foi alocado para realizar a entrega
    const GOING_TO_ORIGIN = 'GTO'; //  Entregador está a caminho da origem para retirar o pedido
    const ARRIVED_AT_ORIGIN = 'AAO'; // Entregador chegou na origem para retirar o pedido
    const COLLECTED = 'CLT'; // Entregador coletou o pedido
    const ARRIVED_AT_DESTINATION = 'AAD'; // Entregador chegou no endereço de destino

    // DELIVERY ON DEMAND
    const REQUEST_DRIVER_AVAILABILITY = 'RDA'; // ndica se o pedido é elegível para requisitar o serviço de entrega sob demanda e o custo do serviço caso seja elegível
    const REQUEST_DRIVER = 'RDR'; // Indica que foi feita uma requisição do serviço de entrega sob demanda
    const REQUEST_DRIVER_SUCCESS = 'RDS'; // Requisição de entrega aprovada
    const REQUEST_DRIVER_FAILED = 'RDF'; // Requisição de entrega negada Valores possíveis: SAFE_MODE_ON, OFF_WORKING_SHIFT_POST, CLOSED_REGION, SATURATED_REGION

    // OUTROS
    const PATCH_COMMITTED = 'PCO';
    const RECOMMENDED_PREPARATION_START = 'RPS'; // Pedido começou a ser preparado
    const CONSUMER_CANCELLATION_DENIED = 'CCD';

    const DELIVERY = 'DELIVERY';

    /**
     * @author Raphael Cangucu
     *
     * update order by points
     * @return OrderDetails
     */
    public static function updateOrder($requestId, $pointId, $pointStartTime, $pointFinishTime, $isCancelled)
    {
        $order = OrderDetails::where('request_id', '=', $requestId)
                                ->where('point_id', '=', $pointId)
                                ->first();

        if ($order) {
            $factory = MarketplaceFactory::create($order->factory);

            $status = null;
            $code = null;
            if (! $isCancelled) {
                $status = 0;
                if ($pointStartTime != null && $order->code != self::DISPATCHED) {
                    $factory->dispatchOrder($order);
                    $code = self::DISPATCHED;
                }

                if ($pointFinishTime) {
                    $factory->fulfillOrder($order);
                    $code = self::CONCLUDED;
                }
            } else {
                $status = 1;
                $code = self::CANCELLED;
                $factory->cancelOrder($order);
            }

            if (! is_null($status) && ! is_null($code)) {
                $order->request_status = $status;
                $order->code = $code;
                $order->full_code = self::mapFullCode($code);
                $order->save();

                // if its from hubster try to delivery status
                if ($order->aggregator == MarketplaceFactory::HUBSTER) {
                    $factory->updateDeliveryStatus($order);
                }
            } else {
                \Log::debug(sprintf('Pedido não atualizado para corrida: %s e ponto %s', $requestId, $pointId));
            }
        } else {
            \Log::warning(sprintf('Pedido não encontrado para corrida: %s e ponto %s', $requestId, $pointId));
        }

        return $order;
    }

    /**
     * @author Raphael Cangucu
     *
     * update order by points
     * @return OrderDetails
     */
    public static function cancelOrder($order)
    {
        if ($order->request_id && $order->ride) {
            $cancelRideService = new CancelRideService;
            $cancelRideService->registerRequestCancellation($order->ride, 'system', trans('marketplace-integration::ride.marketplace_cancel'), 0, 0, 0);
            $cancelRideService->cancel($order->ride);
        }

        return $order;
    }

    /**
     * Get orders from database.
     * @return Collection of orders
     */
    public static function getOrders($institutionId, $shopId = null, $marketId = null, $startTime = null, $endTime = null)
    {
        $query = OrderDetails::query();

        if (isset($startTime->date)) {
            $query->where('order_detail.created_at', '>', $startTime->date);
        } elseif (isset($startTime->date) && $endTime) {
            $query->whereBetween('order_detail.created_at', [$startTime->date, $endTime]);
        } else {
            $query->where('order_detail.created_at', '>', $startTime);
        }

        $query->join('shops', 'order_detail.shop_id', '=', 'shops.id');
        $query->where('shops.institution_id', $institutionId);

        if (isset($shopId) && $shopId != null) {
            $query->where('shop_id', $shopId);
        }

        if (isset($marketId) && $marketId != null) {
            $query->join('market_config', 'shops.id', '=', 'market_config.shop_id');
            $query->where('market_config.id', $marketId);
        }

        $query->where(function ($queryCode) {
            $queryCode->whereIn('code', ['CFM', 'RDA'])
                ->orWhere(function ($queryInner) {
                    $queryInner->where('order_detail.code', 'DSP')
                        ->where('order_detail.request_id', '>', 1);
                });
        })
        ->join('delivery_address', 'order_detail.order_id', '=', 'delivery_address.order_id');

        //dd(vsprintf(str_replace(['?'], ['\'%s\''], $query->toSql()), $query->getBindings()));

        return   $query
                        ->orderBy('order_detail.request_id', 'ASC')//order by reuqest to show first the orders without points id, so orders without dispatched
                        ->orderBy('delivery_address.neighborhood', 'ASC')
                        ->orderBy('distance', 'DESC')
                        ->orderBy('order_detail.display_id', 'ASC')
                        ->orderBy('order_detail.client_name', 'ASC')
                        ->paginate(200);
    }

    /**
     * Get error message of a value. It's actually the constant's name.
     * @param int $value
     *
     * @return string
     */
    public static function mapFullCode($value)
    {
        $class = new \ReflectionClass(__CLASS__);
        $constants = array_flip($class->getConstants());

        return $constants[$value];
    }

    /**
     * Get provider by key.
     * @param $key
     */
    public static function getProviderByKey($key)
    {
        return \Provider::where('email', $key)->orWhere('document', $key)->orWhere('pix', $key)->first();
    }
}
