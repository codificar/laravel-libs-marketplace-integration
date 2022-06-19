<?php

namespace Codificar\MarketplaceIntegration\Repositories;

use Codificar\MarketplaceIntegration\Models\MarketConfig;
use Codificar\MarketplaceIntegration\Models\Shops;
use Codificar\MarketplaceIntegration\Models\OrderDetails ;
use Codificar\MarketplaceIntegration\Models\AutomaticDispatch ;

use Codificar\MarketplaceIntegration\Lib\MarketplaceFactory;


use Carbon\Carbon;


/**
 * Class MarketplaceRepository
 * 
 */
class MarketplaceRepository
{

    // Constants from iFood

    // ORDER_STATUS 
    const PLACED = 'PLC' ; // Novo Pedido na plataforma
    const CONFIRMED = 'CFM' ; // Pedido foi confirmado e será preparado
    const READY_TO_PICKUP = 'RTP' ; // ndica que o pedido está pronto para ser retirado (Pra Retirar ou Na Mesa)
    const DISPATCHED = 'DSP' ; // Indica que o pedido saiu para entrega (Delivery)
    const CONCLUDED = 'CON' ; // Pedido foi concluído
    const CANCELLED = 'CAN' ; // Pedido foi Cancelado

    // DELIVERY
    const ASSIGN_DRIVER = 'ADR' ; // Um entregador foi alocado para realizar a entrega
    const GOING_TO_ORIGIN = 'GTO' ; //  Entregador está a caminho da origem para retirar o pedido
    const ARRIVED_AT_ORIGIN = 'AAO' ; // Entregador chegou na origem para retirar o pedido
    const COLLECTED = 'CLT' ; // Entregador coletou o pedido
    const ARRIVED_AT_DESTINATION = 'AAD' ; // Entregador chegou no endereço de destino

    // DELIVERY ON DEMAND
    const REQUEST_DRIVER_AVAILABILITY = 'RDA' ; // ndica se o pedido é elegível para requisitar o serviço de entrega sob demanda e o custo do serviço caso seja elegível
    const REQUEST_DRIVER = 'RDR' ; // Indica que foi feita uma requisição do serviço de entrega sob demanda
    const REQUEST_DRIVER_SUCCESS = 'RDS' ; // Requisição de entrega aprovada
    const REQUEST_DRIVER_FAILED = 'RDF' ; // Requisição de entrega negada Valores possíveis: SAFE_MODE_ON, OFF_WORKING_SHIFT_POST, CLOSED_REGION, SATURATED_REGION

    // OUTROS
    const PATCH_COMMITTED = 'PCO' ;
    const RECOMMENDED_PREPARATION_START = 'RPS' ; // Pedido começou a ser preparado
    const CONSUMER_CANCELLATION_DENIED = 'CCD' ;

    const DELIVERY = 'DELIVERY' ;

    /**
     * @author Raphael Cangucu
     *
     * update order by points
     * @return [] OrderDetails
     */
    public static function updateOrder($requestId, $pointId, $pointStartTime, $pointFinishTime, $isCancelled)
    {
        
        $order = OrderDetails::where('request_id', '=', $requestId)
                                ->where('point_id', '=', $pointId)
                                ->first();
        
        if ($order) 
        {
            $factory = MarketplaceFactory::create($order->factory);

            $request_status='';
            $code='';
            $full_code='';
            if (!$isCancelled) {
                #TODO remove full_code need
                if ($pointStartTime != NULL && $order->code != OrderDetails::DISPATCHED) {
                    $res = $factory->dispatch($order->order_id);
                    $request_status = 0;
                    $code = self::DISPATCHED;
                    $full_code = self::mapFullCode(self::DISPATCHED);
                }
                if ($pointFinishTime) {
                    \Log::debug("IF point->finish_time". $pointFinishTime);
                    $request_status = 0;
                    $code = self::CONCLUDED ;
                    $full_code = self::mapFullCode(self::CONCLUDED);
                }
            } 
            else {
                $request_status = 1;
                $code = self::CANCELLED;
                $full_code = self::mapFullCode(self::CANCELLED);
            }

            if (isset($request_status) && isset($code) && $code !='') {
                $order->request_status    = $request_status;
                $order->code              = $code;
                $order->full_code         = $full_code;
                $order->update();
            }
        }

        return $order;
    }

    /**
     * Get error message of a value. It's actually the constant's name
     * @param integer $value
     * 
     * @return string
     */
    public static function mapFullCode($value)
    {
        $class = new \ReflectionClass(__CLASS__);
        $constants = array_flip($class->getConstants());

        return $constants[$value];
    }
}