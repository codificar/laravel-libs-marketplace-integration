<?php

namespace Codificar\MarketplaceIntegration\Http\Controllers;


use App\Http\Controllers\Controller;

use Illuminate\Http\Request;

use Carbon\Carbon;

use Illuminate\Support\Facades\Log;

use Illuminate\Http\Response;


class SettingsController extends Controller
{

    
    /**
     * Function to save ifood credentials from settings model
     * @return array
     */
    public function storeIFoodCredentials(Request $request)
    {
        
        $client_id          =  \Settings::updateOrCreate([
            'key'   =>  'ifood_client_id'],[
            'value' => $request->ifood_client_id
        ]);
        $client_secret      =  \Settings::updateOrCreate([
            'key'   =>  'ifood_client_secret'],[
            'value' => $request->ifood_client_secret
        ]);

        if ($client_id && $client_secret) {
            return response()
                        ->json(
                            [
                                'message'   => 'Salvo com sucesso!'
                            ]
                        )
                        ->setStatusCode(Response::HTTP_OK);
        } else {
            return response()
                        ->json(
                            [
                                'message'   => 'Erro ao salvar as credenciais!'
                            ]
                        )
                        ->setStatusCode(Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    /**
     * Function to return ifood credentials from settings model
     * @return array
     */
    public function getIFoodCredentials()
    {
        $client_id          =  \Settings::where('key', 'ifood_client_id')->first();
        $client_secret      =  \Settings::where('key', 'ifood_client_secret')->first();
        if ($client_secret && $client_id) {
            return response()
                        ->json(
                            [
                                'ifood_client_id'       => $client_id,
                                'ifood_client_secret'   => $client_secret
                            ]
                        )
                        ->setStatusCode(Response::HTTP_OK);

            return ;
        } else {
            return response()
                        ->json(
                            [
                                'message'   => 'Cadastre as credenciais iFood!'
                            ]
                        )
                        ->setStatusCode(Response::HTTP_NOT_FOUND);
        }

    }
}
