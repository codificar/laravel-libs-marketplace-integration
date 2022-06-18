<?php

namespace Codificar\MarketplaceIntegration\Console\Commands;

use Codificar\MarketplaceIntegration\Lib\MarketplaceFactory;
use Illuminate\Console\Command;
use Log;
use Carbon\Carbon;
//use App\Models\LibSettings;

class Polling extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'marketplace:polling';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Get events polling';

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
        // just polling if has the proper configurations
        if( \Settings::findByKey('ifood_client_id') &&  \Settings::findByKey('ifood_client_secret')){
            $this->polling();
            sleep(30);
            $this->polling();
        }
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function polling()
    {   
        #TODO criar um foreach para cada marketplace que faz polling e disparar
        $factory = MarketplaceFactory::create();
        $factory->newOrders();                   
    }
}
