<?php

namespace Codificar\MarketplaceIntegration\Lib;

use Codificar\MarketplaceIntegration\Models\MarketConfig;
use Codificar\MarketplaceIntegration\Models\Shops;
use GuzzleHttp\Client;

class IFoodApi
{
  protected $clientId;
  protected $clientSecret;
  protected $baseUrl;
  protected $access_token;
  protected $client;

  #status
  

  function __construct($id)
  {
    \Log::debug("__construct". $id);
    $this->clientId     = MarketConfig::select('client_id')->where('id', $id)->first();
    $this->clientSecret     = MarketConfig::select('client_secret')->where('id', $id)->first();
    $this->baseUrl      = 'https://merchant-api.ifood.com.br/';
    $this->client       = new Client([
      'base_uri'  => $this->baseUrl
    ]);
    
    $this->access_token = $this->auth();
  }

  public function auth()
  {   
    \Log::debug("ClientId: ".$this->clientId);
    \Log::debug("ClientSecret: ".$this->clientSecret);
    try {
      $headers    = ['Content-Type' => 'application/x-www-form-urlencoded'];
      $body       = [
        'grantType'     => 'client_credentials',
        'clientId'      => $this->clientId['client_id'],
        'clientSecret'  => $this->clientSecret['client_secret'],
      ];
      $response   = $this->client->post('authentication/v1.0/oauth/token',[
        'form_params'   => $body,
        'headers'       => $headers
      ]);
      $res = json_decode($response->getBody()->getContents());
      $this->access_token = $res->accessToken;
      return $res->accessToken;
    }catch (\Exception $e){
      \Log::debug($e->getMessage());
      return $e;
    }
  }

  public function getOrders()
  {
    $res = $this->client->get('order/v1.0/events:polling', [
      'headers'   => [
        'Content-Type' => 'application/json',
        'Authorization' => 'Bearer '.$this->access_token
      ]
    ]);
    $response = json_decode($res->getBody()->getContents());
    
    return $response;
  }

  public function getAcknowledgment($data)
  { 
    $headers    = [
      'Content-Type' => 'application/json',
      'Authorization' => 'Bearer '.$this->access_token
    ];
    $object = array(
      (object)
        array(
          'id'        => $data->id,
          'code'      => $data->code,
          'fullCode'  => $data->fullCode,
          'orderId'   => $data->orderId,
          'createdAt' => $data->createdAt
        )
    );

    \Log::debug('acknowledgment: '.print_r($object,1));
    $res = $this->client->request('POST','order/v1.0/events/acknowledgment', [
      'headers'   => $headers,
      'body'      => json_encode($object)
    ]);
    $response = json_decode($res->getBody()->getContents());
    
    return $response;
  }
  
  public function getOrderDetails($id)
  {
    $res = $this->client->request('GET', 'order/v1.0/orders/'.$id, [
      'headers'   => [
        'Content-Type' => 'application/json',
        'Authorization' => 'Bearer '.$this->access_token
      ]
    ]);
    
    $response = json_decode($res->getBody()->getContents());
    return $response;
  }

  public function confirmOrderApi($id)
  {
    \Log::debug("Token: ".$this->access_token);
    \Log::debug("Entrou". $id);
    try {
      $res   = $this->client->post('order/v1.0/orders/'.$id.'/confirm', [
        'headers' => [
          'Authorization' => 'Bearer '.$this->access_token
        ]
      ]);
      \Log::debug('Details 1: '.print_r($res,1));
      $response = json_decode($res->getBody()->getContents());
      return $response;
    }catch (\Exception $e){
      \Log::debug($e->getMessage());
      return $e;
    }
  }

  public function rtcOrder($id)
  {
    $headers    = [
      'Content-Type' => 'application/x-www-form-urlencoded',
      'Authorization' => 'Bearer '.$this->access_token
    ];
    $body       = [
        'id'     => $id,
      ];
    $res   = $this->client->post('order/v1.0/orders/'.$id.'/readyToPickup',[
      'form_params'   => $body,
      'headers'       => $headers
    ]);
    $response = json_decode($res->getBody()->getContents());
    \Log::debug("readyToPickup: ".print_r($response,1));
    
  }

  public function getMerchantDetails($id)
  {    
    $res = $this->client->request('GET', 'merchant/v1.0/merchants/'.$id, [
      'headers'   => [
        'Content-Type' => 'application/json',
        'Authorization' => 'Bearer '.$this->access_token
      ]
    ]);
    $response = json_decode($res->getBody()->getContents());
    \Log::debug("MerchantDetails: ". print_r($response, 1));
    return $response;
  }
}