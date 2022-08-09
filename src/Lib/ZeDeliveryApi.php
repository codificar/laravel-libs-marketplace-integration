<?php

namespace Codificar\MarketplaceIntegration\Lib;

use Carbon\Carbon;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;

class ZeDeliveryApi
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
    public function __construct()
    {
        $environment = \Settings::updateOrCreateByKey('zedelivery_environment', 'sandbox');

        if ($environment == 'production') {
            $this->baseUrl = 'https://seller-public-api.ze.delivery';
        } else {
            $this->baseUrl = 'https://seller-public-api.release.ze.delivery';
        }

        $this->client = new Client([
            'base_uri'  => $this->baseUrl
        ]);

        //get the marketplace toe=ken
        $key = \Settings::findByKey('zedelivery_auth_token');

        //initialize a common variable
        $this->accessToken = $key;
        //initialize a common variable
        $this->headers = [
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer ' . $key
        ];
    }

    /**
     * Api Client send data and get json return.
     */
    public function send($requestType, $route, $headers, $body = null, $retry = 0)
    {
        $response = null;

        try {
            $options['headers'] = $headers;

            if (strtolower($requestType) == 'get') {
                $options['query'] = $body;
            } else {
                $options['form_params'] = $body;
            }

            $response = $this->client->request($requestType, $route, $options);
            var_dump($response);
            \Log::info('Code: ' . $response->getStatusCode());
        } catch (\Exception $ex) {

            //reautenticacao caso a chave tenha dado 401 e um novo retry
            if (in_array($ex->getCode(), [401]) && $retry < 3) {
                $clientId = \Settings::findByKey('zedelivery_client_id', '5c2sl86tvtn9hbk2a81pdhp9di');
                $clientSecret = \Settings::findByKey('zedelivery_client_secret', 'qite7frts0jf8936rks7nuc5lsi4hv8s0oqtfpovu24lsvflvbg');
                $this->auth($clientId, $clientSecret);

                return $this->send($requestType, $route, $headers, $body, ++$retry);
            }

            \Log::info('erro send: ' . $ex->getMessage());
        }

        return json_decode($response->getBody()->getContents());
    }

    /**
     * Authenticate and save updated keys.
     */
    public function auth($clientId, $clientSecret)
    {
        try {
            $headers = ['Content-Type' => 'application/x-www-form-urlencoded'];
            $body = [
                'client_id'      => $clientId,
                'client_secret'  => $clientSecret,
            ];

            $options['headers'] = $headers;
            $options['form_params'] = $body;

            $response = $this->client->request('POST', 'auth?grant_type=client_credentials&scope=orders/read', $options);

            $this->accessToken = $response->access_token;
            $test = \Settings::updateOrCreateByKey('zedelivery_auth_token', $this->accessToken);
            \Log::debug('Ifood API updateOrCreateByKey: zedelivery_auth_token ' . print_r($test, 1));

            $test = \Settings::updateOrCreateByKey('zedelivery_expiry_token', Carbon::now()->addHours(3));
            \Log::debug('Ifood API updateOrCreateByKey: zedelivery_expiry_token ' . print_r($test, 1));

            return $response;
        } catch (\Exception $ex) {
            \Log::error('error: ' . $ex->getMessage() . $ex->getTraceAsString());
        }
    }

    /**
     * Set Authorization header.
     */
    private function setAuthorization($token)
    {
        $this->headers['Authorization'] = 'Bearer ' . $token;
    }

    /**
     * Set x-polling-merchants header.
     * @param $merchantIds string id csv values
     */
    public function setPollingMerchants($merchantIds)
    {
        $this->headers['x-polling-merchants'] = $merchantIds;
    }

    /**
     * Get new orders from polling.
     *
     * @param data
     */
    public function newOrders()
    {
        $headers = [
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer ' . $this->accessToken
        ];

        return $this->send('GET', 'events:polling', $headers);
    }

    /**
     * acknowledgment events from polling.
     *
     * @param data
     */
    public function acknowledgment($data)
    {
        $headers = [
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer ' . $this->accessToken
        ];

        $object = [
            (object)
              [
                  'id'                => $data->id,
                  'code'              => $data->code,
                  'full_code'         => $data->fullCode,
                  'order_id'          => $data->orderId,
                  'created_at_marketplace'  => $data->createdAt
              ]
        ];

        $res = $this->client->request('POST', 'events/acknowledgment', [
            'headers'   => $headers,
            'body'      => json_encode($object)
        ]);

        $response = json_decode($res->getBody()->getContents());

        \Log::debug('getAcknowledgment > response: ' . print_r($response, 1));

        return $response;
    }

    /**
     * Ger the order detail json.
     *
     * @param id
     */
    public function orderDetails($id)
    {
        $headers = [
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer ' . $this->accessToken
        ];

        return $this->send('GET', 'orders/' . $id, $headers);
    }

    /**
     * Confirm a order status to ifood.
     *
     * @param id
     */
    public function confirmOrder($id)
    {
        $headers = [
            'headers'   => [
                'Authorization' => 'Bearer ' . $this->accessToken
            ]
        ];
        try {
            return $this->send('POST', 'orders/' . $id . '/confirm', $headers);
        } catch (Exception $ex) {
            \Log::error('error: ' . $ex->getMessage() . $ex->getTraceAsString());

            return false;
        }
    }

    /**
     * Cancel a order status to ifood.
     *
     * @param id
     */
    public function cancelOrder($id)
    {
        $headers = [
            'headers'   => [
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . $this->accessToken
            ]
        ];
        $object = [
            'reason'                        => 'PEDIDO FORA DA ÁREA DE ENTREGA',
            'cancellationCode'              => '506'
        ];
        try {
            return $this->send('POST', 'orders/' . $id . '/requestCancellation', $headers, json_encode($object));
        } catch (Exception $ex) {
            \Log::error('error: ' . $ex->getMessage() . $ex->getTraceAsString());

            return false;
        }
    }

    /**
     * Dispatch a order status to ifood.
     *
     * @param id
     */
    public function dispatchOrder($id)
    {
        try {
            $headers = $this->headers;
            $headers['Content-Type'] = 'application/x-www-form-urlencoded';

            return $this->send('POST', 'orders/' . $id . '/dispatch', $headers, ['id' => $id]);
        } catch (Exception $ex) {
            \Log::error('error: ' . $ex->getMessage() . $ex->getTraceAsString());

            return false;
        }
    }

    /**
     * Get the merchant detail from the marketplace api, needs to return alway the array with code, data, and message.
     * @return array [code ; data ; message]
     */
    public function merchantDetails($merchantId)
    {
        $headers = [
            'accept' => 'application/json',
            'Authorization' => 'Bearer ' . $this->accessToken
        ];

        try {
            $data = $this->send('GET', 'merchant/v1.0/merchants/' . $merchantId, $headers);
            if (is_object($data)) {
                return [
                    'code' => 200,
                    'data' => $data,
                    'message' => null
                ];
            } else {
                return [
                    'code'    => 401,
                    'data' => null,
                    'message' =>  "Infelizmente não temos acesso a sua loja com o ID $merchantId. <br /> <a href='/page/ifood-market-permission' target='_blank'>Clique aqui</a>  para aprender como realizar essa permissão!"
                ];
            }
        } catch (ClientException $e) {
            return [
                'code'      => $e->getCode(),
                'data'      => null,
                'message'   => "Infelizmente não temos acesso a sua loja com o ID $merchantId. <br /> <a href='/page/ifood-market-permission' target='_blank'>Clique aqui</a>  para aprender como realizar essa permissão!" . $e->getMessage()
            ];
        }
    }
}
