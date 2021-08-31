<?php

namespace Codificar\MarketplaceIntegration\Lib;

use Codificar\MarketplaceIntegration\Models\MarketConfig;
use Codificar\MarketplaceIntegration\Models\Shops;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7;
use GuzzleHttp\Exception\ClientException;

class IFoodApi
{
  protected $clientId;
  protected $clientSecret;
  protected $baseUrl;
  protected $access_token;
  protected $client;

  #status
  
  function __construct()
  {
    $this->baseUrl      = 'https://merchant-api.ifood.com.br/';
    $this->client       = new Client([
      'base_uri'  => $this->baseUrl
    ]);    
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

  public function auth($clientId, $clientSecret)
  {
    \Log::debug('clientId:'.print_r($clientId,1));
    \Log::debug('clientSecret:'.print_r($clientSecret,1));
    try
    {
      $headers    = ['Content-Type' => 'application/x-www-form-urlencoded'];
      $body       = [
          'grantType'     => 'client_credentials',
          'clientId'      => $clientId,
          'clientSecret'  => $clientSecret,
      ];
      $res = $this->send('POST', 'authentication/v1.0/oauth/token', $headers, $body);
      // \Log::debug("Res: ". print_r($res,1));
      return $res;
    }
    catch (\Exception $e)
    {
      // \Log::debug($e->getMessage());
      return $e;
    }
  }

  public function getOrders($token)
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
        'Authorization' => 'Bearer '.$this->access_token
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

  public function dspOrder($id, $token)
  {
    try {
      $headers    = [
        'Content-Type' => 'application/x-www-form-urlencoded',
        'Authorization' => 'Bearer '.$token
      ];
      $body       = [
          'id'     => $id,
        ];
      return $this->send('POST','order/v1.0/orders/'.$id.'/dispatch', $headers, $body);      
    }catch (\Exception $e){
      // \Log::debug($e->getMessage());
      return FALSE;
    }
  }

  /**
   * getMerchantDetails
   */
  public function getMerchantDetails($token, $id)
  {    
    \Log::debug("ID Merchant: ".$id);
    // \Log::debug("Token Merchant: ".$token);
    $headers = [
      'accept' => 'application/json',
      'Authorization' => 'Bearer '.$token
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
}