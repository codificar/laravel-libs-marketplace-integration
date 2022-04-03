<?php

namespace Codificar\MarketplaceIntegration\Lib;

use Exception;
use Carbon\Carbon;
use GuzzleHttp\Psr7;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Session;
use GuzzleHttp\Exception\ClientException;
use Codificar\MarketplaceIntegration\Models\Shops;
use Codificar\MarketplaceIntegration\Models\MarketConfig;

#TODO pass to folder lib/clients with Ifood class name
class IFoodApi
{
    protected $client;

    public function __construct()
    {
        $this->baseUrl      = 'https://merchant-api.ifood.com.br/';
    }

    public function auth(string|int $clientId, string|int $clientSecret): array|Exception
    {
        try {
            $body       = [
              'grantType'     => 'client_credentials',
              'clientId'      => $clientId,
              'clientSecret'  => $clientSecret,
            ];
            
            $response = $this->oAuthPost('authentication/v1.0/oauth/token', $body);     
            # TODO COLOCAR A DURATION DO TOKEN NA SESSION PARA EXPIRAR
            # TODO PERSONALIZAR OS ERRORS       
            Session::put('accessToken', $response['accessToken']);

            return $response;
        } catch (Exception $e) {
            return $e;
        }
    }

    public function findMerchant(string|int $id): array|Exception
    {
        try {
            $response = $this->get('merchant/v1.0/merchants/', ['id'=> $id]);  

            return $response;
        } catch (Exception $e) {
           return $e;
        }
    }

    # TODO Chamar em um cron que faz a call a cada 30s para receber os eventos
    # TODO Pegar o caso de success na DOC para criar o teste Mock 
    # TODO AO RECEBER MANDAR UM Acknowledgmen para não receber novamente
    public function getOrders(): array|Exception
    {
         try {            
            $response = $this->get('order/v1.0/events:polling');
            return $response;
        } catch (Exception $e) {
            return $e;
        }
        
    }
    

    private function get(string $route, array $body = [], $retry = 0)
    {
        $route = $this->baseUrl.$route;
        $accessToken = Session::get('accessToken');
        if(empty($accessToken)) return ['error' => 'Access token not found'];

        $header = ['accept' => 'application/json', 'Authorization' => 'Bearer '.$accessToken];

        try {
            $response =  Http::withHeaders($header)->get($route, $body);
            return $response->json();
        } catch (Exception $ex) {
            throw $ex;
        }
    }

    private function post(string $route, array $headers, array $body = [], $retry = 0)
    {
        $route = $this->baseUrl.$route;
        $accessToken = Session::get('accessToken');
        if(empty($accessToken)) return ['error' => 'Access token not found'];

        $header = ['accept' => 'application/json', 'Authorization' => 'Bearer '.$accessToken];

        try {
            $response =  Http::withHeaders($header)->post($route, $body);
             return $response->json();
        } catch (Exception $ex) {
            throw $ex;
        }
    }

    private function oAuthPost(string $route, array $body = [], $retry = 0): array
    {
        $route = $this->baseUrl.$route;

        try {
            $response =  Http::asForm()->withHeaders(['Content-Type' => 'application/x-www-form-urlencoded'])->post($route, $body);
            return $response->json();
        } catch (Exception $ex) {
            throw $ex;
        }
    }
}
