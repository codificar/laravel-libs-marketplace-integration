<?php 

namespace Codificar\MarketplaceIntegration\Http\Repositories;

use Codificar\MarketplaceIntegration\Http\Controllers\ShopsController;
use Codificar\MarketplaceIntegration\Models\MerchantDetails;
use Codificar\MarketplaceIntegration\Models\OrderDetails;

class OrdersRepository extends OrderDetails
{
    /**
     * Get orders in database
     * @author Diogo C. Coutinho
     * 
     * @param Integer|NULL $id
     * 
     * @return OrderDetails $data
     */
    public static function getOrders($id)
    {
        $query = OrderDetails::query();
        if (isset($id) && $id != null) {
            $query->where('shop_id', $id);
        }

        $query->with('merchant');

        $query->whereIn('code', OrderDetails::ORDER_STATUS);

        return $query   
                        // ->orderBy('distance', 'DESC')
                        ->orderBy('order_detail.display_id', 'ASC')
                        ->orderBy('order_detail.client_name', 'ASC')
                        ->orderBy('order_detail.request_id', 'ASC')//order by reuqest to show first the orders without points id, so orders without dispatched
                        ->paginate(200);
    }

    /**
     * Update Order Details
     * @author Diogo C. Coutinho
     * 
     * @param Object $data
     * 
     * @return OrderDetails
     */
    public static function updateOrder($data)
    {
        $marketConfig = MerchantDetails::where('merchant_id', $data->merchant->id)->first();

        $timestamp = strtotime($data->createdAt);
        $timestamp = strtotime($data->preparationStartDateTime);
        $preparationStartDateTime = date('Y-m-d H:i:s', $timestamp);
        \Log::debug('Name:: '.print_r($data->customer->name, 1));

        return OrderDetails::whereOrderId($data->id)->update([
            'shop_id'                       => ($marketConfig ? $marketConfig->shop_id : null),
            'order_id'                      => $data->id,
            'client_name'                   => $data->customer->name,
            'merchant_id'                   => $data->merchant->id,
            'order_type'                    => $data->orderType,
            'display_id'                    => $data->displayId,
            'preparation_start_date_time'   => $preparationStartDateTime,
            'customer_id'                   => $data->customer->id,
            'sub_total'                     => $data->total->subTotal,
            'delivery_fee'                  => $data->total->deliveryFee,
            'benefits'                      => $data->total->benefits,
            'order_amount'                  => $data->total->orderAmount,
            'method_payment'                => $data->payments->methods[0]->method,
            'prepaid'                       => $data->payments->methods[0]->prepaid,
            'change_for'                    => $data->payments->methods[0]->method == 'CASH' ? $data->payments->methods[0]->cash->changeFor : '',
            'card_brand'                     => $data->payments->methods[0]->method == 'CREDIT' ? $data->payments->methods[0]->card->brand : NULL,
            'extra_info'                    => isset($data->extraInfo) ? $data->extraInfo : ''

        ]);
    }

}