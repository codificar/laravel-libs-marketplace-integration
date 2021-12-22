<?php

namespace Codificar\MarketplaceIntegration\Lib;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use Carbon\Carbon;

class IFoodApi implements IMarketplace
{
    protected $clientId;
    protected $clientSecret;
    protected $baseUrl;
    protected $accessToken;
    protected $headers;
    protected $client;
        
    /**
     * Instantiate a new iFoodApi instance with common variables and configuration.
     */
    function __construct()
    {
        $this->baseUrl      = 'https://merchant-api.ifood.com.br/';
        $this->client       = new Client([
            'base_uri'  => $this->baseUrl
        ]);

        $expiryToken  = \Settings::findByKey('ifood_expiry_token');
        
        if ($expiryToken == NULL || Carbon::parse($expiryToken) < Carbon::now()) {
            $this->clientId          = \Settings::findByKey('ifood_client_id');
            $this->clientSecret      = \Settings::findByKey('ifood_client_secret');
            $this->accessToken = \Settings::updateOrCreateByKey('ifood_auth_token',$this->auth((object)array('clientId' => $this->clientId, 'clientSecret' => $this->clientSecret)));
            $expiryToken = \Settings::updateOrCreateByKey('ifood_expiry_token', Carbon::now()->addHours(1));
        } else {
            $this->accessToken = \Settings::getMarketPlaceToken('ifood_auth_token');
        }

        $this->headers    = [
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer '.$this->accessToken
        ];
    }
    
    /**
     * Send request to marketplace API
     * 
     * @param String $requestType, $route
     * @param Array $headers
     * @param mixed $body
     * 
     * @return mixed  
     */
    public function send($requestType, $route, $headers, $body = NULL, $retry = 0)
    {
        $response = $this->client->request($requestType, $route, ['headers'       => $headers, 'form_params'   => $body]);

        // reautenticacao caso a chave tenha dado 401 e um novo retry
        if($response->getStatusCode() == 401 && $retry < 3){
            $clientId          = \Settings::findByKey('ifood_client_id');
            $clientSecret      = \Settings::findByKey('ifood_client_secret');
            $this->auth($clientId, $clientSecret);
    
            return $this->send($requestType, $route, $headers, $body, ++$retry);
        }

        return $response->getBody()->getContents();
    }
    
    /**
     * Authenticate and save updated keys.
     * 
     * @param Object $credentials
     * 
     * @return Object $res
     */
    public function auth()
    {
        try{
            $headers    = ['Content-Type' => 'application/x-www-form-urlencoded'];
            $body       = [
                'grantType'     => 'client_credentials',
                'clientId'      => $this->clientId,
                'clientSecret'  => $this->clientSecret,
            ];
            $res = $this->send('POST', 'authentication/v1.0/oauth/token', $headers, $body);
            $res=json_decode($res);
            return $res->accessToken;
        } catch (\Exception $e) {
            \Log::error($e->getMessage());
            dd("API: ".$e->getMessage());
            return $e;
        }
    }

    /**
     * Get orders in marketplace API
     * 
     * @return Array
     */
    public function getOrder()
    {
        return $this->send('GET','order/v1.0/events:polling', $this->headers);
    }
    
    /**
     * Get Acknowledgment in marketplace API
     * 
     * @param Object $data
     * 
     * @return Object $response
     */
    public function getAcknowledgment($data)
    {
        $object = array(
            (object)
            array(
                'id'                => $data['id'],
                'code'              => $data['code'],
                'full_code'         => $data['fullCode'],
                'order_id'          => $data['orderId'],
                'created_at_ifood'  => $data['createdAt']
            )
        );
        $res = $this->client->request('POST','order/v1.0/events/acknowledgment', [
            'headers'   => $this->headers,
            'body'      => json_encode($object)
            ]
        );
        $response = json_decode($res->getBody()->getContents());
        return $response;
    }
    
    /**
     * Get order details in marketplace API
     * 
     * @param String $orderId
     * 
     * @return Object $object
     */
    public function getOrderDetails($orderId)
    {
        return $this->send('GET', 'order/v1.0/orders/'.$orderId, $this->headers);
    }

    /**
     * Get Merchant in marketplace API
     * 
     * @param String $merchantId
     * 
     * @return Object $object
     */
    public function getMerchant($merchantId)
    {
        # TODO Criar função para pegar o merchant
    }

    /**
     * Get merchant details in marketplace API
     * 
     * @param String $merchantId
     * 
     * @return mixed
     */
    public function getMerchantDetails($merchantId)
    {    
        try {
            $res = json_decode($this->send('GET', 'merchant/v1.0/merchants/'.$merchantId, $this->headers));
            if (is_object($res)) {
                return $res;
            } else {
                return [
                    'code'    => 401,
                    'message' =>  "Infelizmente não temos acesso a sua loja com o ID $merchantId. <br /> <a href='/page/ifood-market-permission' target='_blank'>Clique aqui</a>  para aprender como realizar essa permissão!"
                ];
            }
        } catch (ClientException $e) {
            \Log::debug("Erro Content: ".$e->getMessage());
            return [
                'code'      => $e->getCode(),
                'message'   => "Infelizmente não temos acesso a sua loja com o ID $merchantId. <br /> <a href='/page/ifood-market-permission' target='_blank'>Clique aqui</a>  para aprender como realizar essa permissão!"
            ];
        }
    }
                
    /**
    * Dispatch a order status to ifood
    * 
    * @param id
    * 
    * @return Object $object 
    */
    public function dispatchOrder($id)
    {
        try {
            $headers = $this->headers;
            $headers['Content-Type'] = 'application/x-www-form-urlencoded';  
            return $this->send('POST','order/v1.0/orders/'.$id.'/dispatch', $headers, [ 'id' => $id ]);      
        } catch (\Exception $e){
            \Log::error($e->getMessage());
            return false;
        }
    }

    /**
     * Dispatch a order status to ifood
     * 
     * @param String $orderId
     * 
     * @return Object $object
     */
    public function finishOrder($orderId)
    {
        try {
            $headers = $this->headers;
            $headers['Content-Type'] = 'application/x-www-form-urlencoded';  
            return $this->send('POST','order/v1.0/orders/'.$id.'/dispatch', $headers, [ 'id' => $id ]);      
        } catch (\Exception $e){
            \Log::debug($e->getMessage());
            return false;
        }
    }

    /**
     * Make a polling to get change events in orders
     * 
     */
    public function polling()
    {
        # TODO mover a funçao de polling para esta função
    }
    
    /**
     * Make a webhook to get new change events in orders
     * 
     */
    public function webhook()
    {
        # TODO criar a funçao de webhook para esta função
    }

    /**
     * Check token validity and renew token when necessary
     */
    public function checkTokenValidity()
    {
        # TODO criar a funçao para checar a validade do token em questão
    }
}