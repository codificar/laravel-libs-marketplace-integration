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
  protected $access_token;
  protected $headers;
  protected $client;

  #status

  /**
   * Instantiate a new iFoodApi instance with common variables and configuration.
   */
  function __construct()
  {
    $this->baseUrl      = 'https://merchant-api.ifood.com.br/';
    $this->client       = new Client([
      'base_uri'  => $this->baseUrl
    ]);    
    //get the marketplace toe=ken
    $key = \Settings::getMarketPlaceToken('ifood_auth_token');

    \Log::debug('IFoodApi::__Construct__ -> ifood_auth_token:'.print_r($key,1));
    //initialize a common variable
    $this->access_token = $key;
    //initialize a common variable
    $this->headers    = [
      'Content-Type' => 'application/json',
      'Authorization' => 'Bearer '.$key
    ];
  }

  public function send($requestType, $route, $headers, $body = null, $retry = 0)
  {
    \Log::debug("requestType: ". print_r($requestType, 1));
    \Log::debug("route: ". print_r($route, 1));
    \Log::debug("headers: ". print_r($headers,1));
    \Log::debug("body: ". print_r($body,1));

    try {
      $response = $this->client->request($requestType, $route, ['headers'       => $headers, 'form_params'   => $body]);
      \Log::debug("Code: ". $response->getStatusCode());
    }
    catch(Exception $ex){
      // reautenticacao caso a chave tenha dado 401 e um novo retry
      if($ex->getCode() == 401 && $retry < 3){
        $clientId          = \Settings::findByKey('ifood_client_id');
        $clientSecret      = \Settings::findByKey('ifood_client_secret');
        $this->auth($clientId, $clientSecret);

        return $this->send($requestType, $route, $headers, $body, ++$retry);
      }  
    }
    
    return $response->getBody()->getContents();
  }

  /**
   * Authenticate and save updated keys
   */
  public function auth($clientId, $clientSecret)
  {
    \Log::debug('clientId:'.print_r($clientId,1));
    \Log::debug('clientSecret:'.print_r($clientSecret,1));
    try
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
     * Get Merchant Status in IFood
     * 
     * @param string $merchantId
     * 
     * @return string $status
     */
    public function getMerchantStatus($merchantId)
    {
        return $this->send('GET','merchant/v1.0/merchants/'.$merchantId.'/status', $this->headers);
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