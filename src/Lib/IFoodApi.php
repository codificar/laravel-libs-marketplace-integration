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
    $this->accessToken = $key;
    //initialize a common variable
    $this->headers    = [
      'Content-Type' => 'application/json',
      'Authorization' => 'Bearer '.$key
    ];
  }

  public function send($requestType, $route, $headers, $body = NULL)
  {
    \Log::debug("requestType: ". print_r($requestType, 1));
    \Log::debug("route: ". print_r($route, 1));
    \Log::debug("headers: ". print_r($headers,1));
    \Log::debug("body: ". print_r($body,1));
    $response = $this->client->request($requestType, $route, ['headers'       => $headers, 'form_params'   => $body]);
    \Log::debug("Code: ". $response->getStatusCode());
    return $response->getBody()->getContents();
  }

  /**
   * Authenticate and save updated keys
   */
  public function auth($credentials)
  {
    \Log::debug('clientId:'.print_r($credentials->clientId,1));
    \Log::debug('clientSecret:'.print_r($credentials->clientSecret,1));
    try
    {
      $headers    = ['Content-Type' => 'application/x-www-form-urlencoded'];
      $body       = [
          'grantType'     => 'client_credentials',
          'clientId'      => $credentials->clientId,
          'clientSecret'  => $credentials->clientSecret,
      ];
      $res = $this->send('POST', 'authentication/v1.0/oauth/token', $headers, $body);
      $res=json_decode($res);
      $this->accessToken = $res->access_token;
      $test = \Settings::updateOrCreateByKey('ifood_auth_token', $this->accessToken);
      \Log::debug("Ifood API updateOrCreateByKey: ifood_auth_token ". print_r($test,1));

      $test = \Settings::updateOrCreateByKey('ifood_expiry_token', Carbon::now()->addHours(6));
      \Log::debug("Ifood API updateOrCreateByKey: ifood_expiry_token ". print_r($test,1));

      return $res;
    }
    catch (\Exception $e)
    {
      //  \Log::error($e->getMessage());
      return $e;
    }
  }

  public function getOrder($token)
  {
    \Log::debug('TOKEN: '. $token);
    $headers = [
      'Content-Type' => 'application/json',
      'Authorization' => 'Bearer '.$token
    ];
    return $this->send('GET','order/v1.0/events:polling', $headers);
    
  }

  public function getAcknowledgment($token, $data)
  { 
    \Log::debug("getAcknowledgment: ".print_r($data, 1));
    $headers    = [
      'Content-Type' => 'application/json',
      'Authorization' => 'Bearer '.$token
    ];
    $object = array(
      (object)
        array(
          'id'                => $data->id,
          'code'              => $data->code,
          'full_code'         => $data->fullCode,
          'order_id'          => $data->orderId,
          'created_at_ifood'  => $data->createdAt
        )
      );
      $res = $this->client->request('POST','order/v1.0/events/acknowledgment', [
        'headers'   => $headers,
        'body'      => json_encode($object)
      ]);
      $response = json_decode($res->getBody()->getContents());
      
      return $response;
  }
  
  public function getOrderDetails($id, $token)
  {
    $headers = [
      'Content-Type' => 'application/json',
      'Authorization' => 'Bearer '.$token
    ];
    return $this->send('GET', 'order/v1.0/orders/'.$id, $headers);
  }

  public function confirmOrderApi($id, $token)
  {
    $headers = [
      'headers'   => [
        'Authorization' => 'Bearer '.$token
      ]
    ];
    try {
      return $this->send('POST', 'order/v1.0/orders/'.$id.'/confirm', $headers);
    }catch (\Exception $e){
      // \Log::debug($e->getMessage());
      return FALSE;
    }
  }

  public function cancelOrderApi($id)
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
    }catch (\Exception $e){
      // \Log::debug($e->getMessage());
      return FALSE;
    }
  }
  

  public function getMerchant($merchantId, $token)
  {
    # code...
  }

  /**
   * getMerchantDetails
   * Use a protected or private variable to store token instead of pass by params
   * 
   */
  public function getMerchantDetails($token, $id)
  {    
    \Log::debug("ID Merchant: ".$id);
    \Log::debug("Token Merchant - getMerchantDetails-> IFOOD API: ".$this->accessToken);
    $headers = [
      'accept' => 'application/json',
      'Authorization' => 'Bearer '.$this->accessToken
    ];
    try {
      $res = json_decode($this->send('GET', 'merchant/v1.0/merchants/'.$id, $headers));
      if (is_object($res)) {
        return $res;
      } else {
        return [
          'code'    => 401,
          'message' =>  "Infelizmente não temos acesso a sua loja com o ID $id. <br /> <a href='/page/ifood-market-permission' target='_blank'>Clique aqui</a>  para aprender como realizar essa permissão!"
        ];
      }
    } catch (ClientException $e) {
      \Log::debug("Erro: ".$e->getCode());
      \Log::debug("Erro Content: ".$e->getMessage());
      // \Log::debug('Message: '. $e->getResponse());
      return [
        'code'      => $e->getCode(),
        'message'   => "Infelizmente não temos acesso a sua loja com o ID $id. <br /> <a href='/page/ifood-market-permission' target='_blank'>Clique aqui</a>  para aprender como realizar essa permissão!"
      ];
    }
  }

  /**
   * Dispatch a order status to ifood
   * 
   * @param id
   * 
   */
  public function dispatchOrder($id, $token)
  {
    try {
      $headers = $this->headers;
      $headers['Content-Type'] = 'application/x-www-form-urlencoded';  
      // $headers = [
      //   'Content-Type' => 'application/x-www-form-urlencoded',
      //   'Authorization' => 'Bearer '.$this->accessToken
      // // ];
      //[
      //     'id'     => $id,
      //   ];
      return $this->send('POST','order/v1.0/orders/'.$id.'/dispatch', $headers, [ 'id' => $id ]);      

    }catch (\Exception $e){
      // \Log::debug($e->getMessage());
      return FALSE;
    }
  }

  /**
   * Dispatch a order status to ifood
   * 
   * @param id
   * 
   */
  public function finishOrder($id, $token)
  {
    try {
      $headers = $this->headers;
      $headers['Content-Type'] = 'application/x-www-form-urlencoded';  
      // $headers = [
      //   'Content-Type' => 'application/x-www-form-urlencoded',
      //   'Authorization' => 'Bearer '.$this->accessToken
      // // ];
      //[
      //     'id'     => $id,
      //   ];
      return $this->send('POST','order/v1.0/orders/'.$id.'/dispatch', $headers, [ 'id' => $id ]);      

    }catch (\Exception $e){
      // \Log::debug($e->getMessage());
      return FALSE;
    }
  }

  public function polling($token)
  {
    # code...
  }

  public function webhook($token)
  {
    # code...
  }
}