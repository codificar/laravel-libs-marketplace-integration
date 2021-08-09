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
  

  function __construct($id)
  {
    \Log::debug("__construct". $id);
    $this->clientId     = MarketConfig::select('client_id')->where('shop_id', $id)->first();
    $this->clientSecret     = MarketConfig::select('client_secret')->where('shop_id', $id)->first();
    $this->baseUrl      = 'https://merchant-api.ifood.com.br/';
    $this->client       = new Client([
      'base_uri'  => $this->baseUrl
    ]);
    
    $this->access_token = $this->auth();
  }

  public function auth()
  {   
    // \Log::debug("ClientId: ".$this->clientId);
    // \Log::debug("ClientSecret: ".$this->clientSecret);
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
      // \Log::debug($e->getMessage());
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
          'id'                => $data->id,
          'code'              => $data->code,
          'full_code'         => $data->fullCode,
          'order_id'          => $data->orderId,
          'created_at_ifood'  => $data->createdAt
        )
    );

    // \Log::debug('acknowledgment: '.print_r($object,1));
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
    // \Log::debug("Token: ".$this->access_token);
    \Log::debug("Entrou". $id);
    try {
      $res   = $this->client->request('POST', 'order/v1.0/orders/'.$id.'/confirm', [
        'headers' => [
          'Authorization' => 'Bearer '.$this->access_token
        ]
      ]);
      \Log::debug('Details 1: '.$res->getStatusCode());
      if ($res->getStatusCode() == 202) {
        return TRUE;
      } else {
        return FALSE;
      }
    }catch (\Exception $e){
      \Log::debug($e->getMessage());
      return FALSE;
    }
  }

  public function cancelOrderApi($id)
  {
    \Log::debug("Entrou Cancel: ". $id);
    
    $object = array(
      'reason'                        => 'PEDIDO FORA DA ÁREA DE ENTREGA',
      'cancellationCode'              => '506'
    );
    
    try {
      $res   = $this->client->request('POST', 'order/v1.0/orders/'.$id.'/requestCancellation', [
        'headers'   => [
          'Content-Type' => 'application/json',
          'Authorization' => 'Bearer '.$this->access_token
        ],
        'body'      => json_encode($object)
      ]);
      \Log::debug('Details 1: '.$res->getStatusCode());
      if ($res->getStatusCode() == 202) {
        return TRUE;
      } else {
        return FALSE;
      }
    }catch (\Exception $e){
      \Log::debug($e->getMessage());
      return FALSE;
    }
  }

  public function rtpOrder($id)
  {
    try {
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
      \Log::debug('Details 1: '.$res->getStatusCode());
      if ($res->getStatusCode() == 202) {
        return TRUE;
      } else {
        return FALSE;
      }
    }catch (\Exception $e){
      \Log::debug($e->getMessage());
      return FALSE;
    }
  }

  public function getMerchantDetails($id)
  {    
    try {
      $res = $this->client->request('GET', 'merchant/v1.0/merchants/'.$id, [
        'headers'   => [
          'Content-Type' => 'application/json',
          'Authorization' => 'Bearer '.$this->access_token
        ]
      ]);
      // \Log::debug("StatusCode: ".$res->getStatusCode());
      $response = json_decode($res->getBody()->getContents());
      \Log::debug("MerchantDetails: ". print_r($response, 1));
      return $response;
    } catch (ClientException $e) {
      \Log::debug("Erro: ".$e->getCode());
      \Log::debug("Erro Content: ".$e->getMessage());
      // \Log::debug('Message: '. $e->getResponse());
      return [
        'code'      => $e->getCode(),
        'message'   => 'Sua loja não foi salva, verifique as informações e tente novamente.'
      ];
    }
  }
}