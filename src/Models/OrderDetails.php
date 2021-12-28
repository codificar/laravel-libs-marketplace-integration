<?php

namespace Codificar\MarketplaceIntegration\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class OrderDetails extends Model
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
   
    use SoftDeletes;
    
    protected $fillable = [
        'request_id',
        'tracking_route',
        'request_status',
        'client_name',
        'shop_id',
        'merchant_id',
        'order_id',
        'full_code',
        'code',
        'ifood_id',
        'order_type',
        'display_id',
        'createdAt',
        'preparation_start_date_time',
        'customer_id',
        'subtotal',
        'delivery_fee',
        'benefits',
        'order_amount',
        'payments_id',
        'method_payment',
        'change_for',
        'prepaid',
        'card_brand',
        'extra_info'
    ];

    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    public function getItems()
    {
        return $this->hasMany('Codificar\MarketplaceIntegration\Models\OrderItems', 'order_id', 'order_id');
    }

    public function merchant()
    {
        return $this->belongsTo('Codificar\MarketplaceIntegration\Models\MerchantDetails', 'merchant_id', 'merchant_id');
    }
}
