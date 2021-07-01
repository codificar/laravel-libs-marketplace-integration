<?php

namespace Codificar\MarketplaceIntegration\Console\Commands;

use Codificar\MarketplaceIntegration\Http\Controllers\IFoodController;
use Illuminate\Console\Command;
use App\Models\Requests;
use Codificar\MarketplaceIntegration\Models\OrderDetails;
use Log;

class CheckRequest extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'check:request';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check request Status';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $orders = OrderDetails::where(['code' => 'RTP', 'request_status' => 0])
                                ->whereNotNull('request_id')
                                ->leftJoin('request', 'request.id', '=', 'order_detail.request_id')
                                ->get();
        \Log::debug("CheckRequest: ". print_r($orders, 1));
        foreach ($orders as $key => $value) {
            switch ($value->is_completed) {
                case 1:
                    $value->update([
                        'request_status'    => $value->is_completed,
                        'code'              => 'CON',
    
                    ]);
                break;               
                default:
                    $value->update([
                        'request_status'    => $value->is_completed,
                        'code'              => 'RTP',

                    ]);
                break;
            }
                
            \Log::debug("Value 2: ". print_r($value, 1));
        }
    }
}
