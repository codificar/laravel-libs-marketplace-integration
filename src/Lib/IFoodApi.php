<?php

namespace Codificar\MarketplaceIntegration\Lib;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7;
use GuzzleHttp\Exception\ClientException;
use Carbon\Carbon;


class IFoodApi
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

    //get the marketplace toe=ken
    $key =  \Settings::getMarketPlaceToken('ifood_auth_token');

    //initialize a common variable
    $this->accessToken = $key;
    //initialize a common variable
    $this->headers    = [
      'Content-Type' => 'application/json',
      'Authorization' => 'Bearer '.$key
    ];
  }

   /**
   * Api Client send data and get json return
   */
  public function send($requestType, $route, $headers, $body = null, $retry = 0)
  {
  
    \Log::debug("headers: ". print_r($headers, 1));
    \Log::debug("route: ". print_r($route, 1));


    try {
      $response = $this->client->request($requestType, $route, ['headers'       => $headers, 'form_params'   => $body]);      
    }
    catch(Exception $ex){

      \Log::error("error: ". $ex->getMessage().$ex->getTraceAsString());

      // reautenticacao caso a chave tenha dado 401 e um novo retry
      if($ex->getCode() == 401 && $retry < 3){
        $clientId          =  \Settings::findByKey('ifood_client_id');
        $clientSecret      =  \Settings::findByKey('ifood_client_secret');
        $this->auth($clientId, $clientSecret);

        return $this->send($requestType, $route, $headers, $body, ++$retry);
      }  
    }
    
    return json_decode($response->getBody()->getContents());
  }

  /**
   * Authenticate and save updated keys
   */
  public function auth($clientId, $clientSecret)
  {
    
    try
    {
      $headers    = ['Content-Type' => 'application/x-www-form-urlencoded'];
      $body       = [
          'grantType'     => 'client_credentials',
          'clientId'      => $clientId,
          'clientSecret'  => $clientSecret,
      ];
      $res = $this->send('POST', 'authentication/v1.0/oauth/token', $headers, $body);
      
      $this->accessToken = $res->accessToken;
      $test =  \Settings::updateOrCreateByKey('ifood_auth_token', $this->accessToken);
      \Log::debug("Ifood API updateOrCreateByKey: ifood_auth_token ". print_r($test,1));

      $test =  \Settings::updateOrCreateByKey('ifood_expiry_token', Carbon::now()->addHours(1));
      \Log::debug("Ifood API updateOrCreateByKey: ifood_expiry_token ". print_r($test,1));

      return $res;
    }
    catch (\Exception $ex)
    {
      \Log::error("error: ". $ex->getMessage().$ex->getTraceAsString());
    }
  }

  /**
   * Get new orders from polling
   * 
   * @param data
   * 
   */
  public function newOrders()
  {
   
    \Log::debug("newOrders > accessToken ". print_r($this->accessToken,1));
    $headers = [
      'Content-Type' => 'application/json',
      'Authorization' => 'Bearer '.$this->accessToken
    ];

    return $this->send('GET','order/v1.0/events:polling', $headers);
    
  }

  /**
   * acknowledgment events from polling
   * 
   * @param data
   * 
   */
  public function acknowledgment($data)
  { 


    $headers    = [
      'Content-Type' => 'application/json',
      'Authorization' => 'Bearer '.$this->accessToken
    ];

    $object = array(
      (object)
        array(
          'id'                => $data->id,
          'code'              => $data->code,
          'full_code'         => $data->fullCode,
          'order_id'          => $data->orderId,
          'created_at_marketplace'  => $data->createdAt
        )
      );

      $res = $this->client->request('POST','order/v1.0/events/acknowledgment', [
        'headers'   => $headers,
        'body'      => json_encode($object)
      ]);

      $response = json_decode($res->getBody()->getContents());

      \Log::debug("getAcknowledgment > response: ".print_r($response, 1));
      
      return $response;
  }
  
  public function orderDetails($id)
  {

    $headers = [
      'Content-Type' => 'application/json',
      'Authorization' => 'Bearer '.$this->accessToken
    ];

    return $this->send('GET', 'order/v1.0/orders/'.$id, $headers);
  }

  /**
   * Confirm a order status to ifood
   * 
   * @param id
   * 
   */
  public function confirmOrder($id)
  {
    $headers = [
      'headers'   => [
        'Authorization' => 'Bearer '.$this->accessToken
      ]
    ];
    try {
      return $this->send('POST', 'order/v1.0/orders/'.$id.'/confirm', $headers);
    }
    catch(Exception $ex){

      \Log::error("error: ". $ex->getMessage().$ex->getTraceAsString());

      return FALSE;
    }
  }

  /**
   * Cancel a order status to ifood
   * 
   * @param id
   * 
   */
  public function cancelOrder($id)
  {
    $headers = [
      'headers'   => [
        'Content-Type' => 'application/json',
        'Authorization' => 'Bearer '.$this->accessToken
      ]
    ];
    $object = array(
      'reason'                        => 'PEDIDO FORA DA ÁREA DE ENTREGA',
      'cancellationCode'              => '506'
    );    
    try {
      return $this->send('POST', 'order/v1.0/orders/'.$id.'/requestCancellation', $headers, json_encode($object));
    }
    catch(Exception $ex){

      \Log::error("error: ". $ex->getMessage().$ex->getTraceAsString());

      return FALSE;
    }
  }

  /**
   * Dispatch a order status to ifood
   * 
   * @param id
   * 
   */
  public function dispatchOrder($id)
  {
    try {
      $headers = $this->headers;
      $headers['Content-Type'] = 'application/x-www-form-urlencoded';  
     
      return $this->send('POST','order/v1.0/orders/'.$id.'/dispatch', $headers, [ 'id' => $id ]);      

    }
    catch(Exception $ex){

      \Log::error("error: ". $ex->getMessage().$ex->getTraceAsString());

      return FALSE;
    }
  }

  /** 
   * Get the merchant detail from the marketplace api, needs to return alway the array with code, data, and message
   * @return array [code ; data ; message] 
  */
  public function merchantDetails($merchantId)
  {    
    
    $headers = [
      'accept' => 'application/json',
      'Authorization' => 'Bearer '.$this->accessToken
    ];

    try {
      $data = $this->send('GET', 'merchant/v1.0/merchants/'.$merchantId, $headers);
      if (is_object($data)) {
        return [
          'code' => 200 ,
          'data' => $data ,
          'message' => null 
        ];
      } 
      else {
        return [
          'code'    => 401,
          'data' => null ,
          'message' =>  "Infelizmente não temos acesso a sua loja com o ID $merchantId. <br /> <a href='/page/ifood-market-permission' target='_blank'>Clique aqui</a>  para aprender como realizar essa permissão!"
        ];
      }
    } 
    catch (ClientException $e) {
      return [
        'code'      => $e->getCode(),
        'data'      => null ,
        'message'   => "Infelizmente não temos acesso a sua loja com o ID $merchantId. <br /> <a href='/page/ifood-market-permission' target='_blank'>Clique aqui</a>  para aprender como realizar essa permissão!".$e->getMessage()
      ];
    }

  }
}