<?php

namespace Codificar\MarketplaceIntegration\Lib;

use Carbon\Carbon;
use GuzzleHttp\Client;
use Illuminate\Http\Request;

class HubsterApi
{
    protected $clientId;
    protected $clientSecret;
    protected $baseUrl;
    protected $accessToken;
    protected $headers;
    protected $client;

    /**
     * Instantiate a new Hubster instance with common variables and configuration.
     */
    public function __construct()
    {
        $environment = \Settings::findOrCreateByKey('hubster_environment', 'sandbox');

        if ($environment == 'production') {
            $this->baseUrl = 'https://partners.tryhubster.com/';
        } else {
            $this->baseUrl = 'https://partners-staging.tryhubster.com/';
        }

        $this->client = new Client([
            'base_uri'  => $this->baseUrl
        ]);

        //TODO remove reset
        $clientSecret = \Settings::findOrCreateByKey('hubster_client_secret', 'CGX3I3RXL5IUDLP2ZHKA');

        //get the marketplace token
        $key = \Settings::findByKey('hubster_auth_token');

        $applicationId = \Settings::findByKey('hubster_application_id', 'c8f9a164-ac52-486f-bb85-74c3c7cc0518');

        $this->accessToken = $key;

        $this->headers = [
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer ' . $key,
            'X-Application-Id' => $applicationId, //TODO vem na request mas não vi mudar
            //'X-Event-Id' => '', //setado na hora de enviar
        ];
    }

    /**
     * Set Authorization header.
     */
    private function setAuthorization($token)
    {
        $this->headers['Authorization'] = 'Bearer ' . $token;
    }

    /**
     * Set X-Store-Id header.
     */
    public function setStoreId($storeId)
    {
        $this->headers['X-Store-Id'] = $storeId;
    }

    /**
     *  Set X-Application-Id header.
     */
    public function setApplicationId($applicationId)
    {
        $this->headers['X-Application-Id'] = $applicationId;
    }

    /**
     * Authenticate and save updated keys.
     */
    public function auth($clientId, $clientSecret)
    {

        //dd(func_get_args());
        \Log::debug('clientId:' . print_r($clientId, 1));
        \Log::debug('clientSecret:' . print_r($clientSecret, 1));

        try {
            $headers = ['Content-Type' => 'application/x-www-form-urlencoded'];
            $body = [
                'grant_type'     	=> 'client_credentials',
                'client_id'     	=> $clientId,
                'client_secret'  	=> $clientSecret,
                'scope'				=> 'ping'
            ];

            $options['headers'] = $headers;
            $options['form_params'] = $body;

            $response = $this->client->request('POST', 'v1/auth/token', $options);
            $response = json_decode($response->getBody()->getContents());

            $this->setAuthorization($response->access_token);

            $this->accessToken = $response->access_token;
            $test = \Settings::findOrCreateByKey('hubster_auth_token', $this->accessToken);
            \Log::debug('findOrCreateByKey: hubster_auth_token ' . print_r($test, 1));

            $test = \Settings::findOrCreateByKey('hubster_expiry_token', Carbon::now()->addHours(1));
            \Log::debug('findOrCreateByKey: hubster_expiry_token ' . print_r($test, 1));

            return $response;
        } catch (\Exception $ex) {
            \Log::error('error: ' . $ex->getMessage() . $ex->getTraceAsString());
        }
    }

    /**
     * get new orders.
     */
    public function newOrders()
    {
        //TODO set to minutes
        $body = [
            'limit' => '10',
            'minDateTime' => Carbon::now()->addDays(-10)->toIso8601String(),
            'maxDateTime' => Carbon::now()->toIso8601String()
        ];

        return $this->send('GET', 'manager/order/v1/orders', $this->headers, $body);
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

            return json_decode($response->getBody()->getContents());

            \Log::debug('Send > response: ' . print_r($response->getBody(), 1));
        } catch (\Exception $ex) {
            \Log::error('Send > Exception: ' . $ex->getMessage() . $ex->getTraceAsString());

            //reautenticacao caso a chave tenha dado 401 e um novo retry
            if (in_array($ex->getCode(), [401]) && $retry < 3) {
                $clientId = \Settings::findByKey('hubster_client_id', 'c8f9a164-ac52-486f-bb85-74c3c7cc0518');
                $clientSecret = \Settings::findByKey('hubster_client_secret', 'CGX3I3RXL5IUDLP2ZHKA');
                $this->auth($clientId, $clientSecret);

                return $this->send($requestType, $route, $headers, $body, ++$retry);
            }
        }

        return null;
    }

    /**
     * Get the merchant detail from the marketplace api, needs to return alway the array with code, data, and message.
     * @return array [code ; data ; message]
     */
    public function merchantDetails($merchantId)
    {
    }

    /**
     * Cancel a order status to ifood.
     *
     * @param id
     */
    public function cancelOrder($cancelData)
    {
        $headers = $this->headers;

        $object = [
            'cancellationReason'            => 'UNABLE_TO_DELIVER',
            'cancelingParty'              => null
        ];

        try {
            return $this->send('POST', sprintf('manager/order/v1/sources/%s/orders/%s/cancel', $cancelData['source'], $cancelData['orderId']), $headers, json_encode($object));
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
        // try {
        //     $headers = $this->headers;
        //     $headers['Content-Type'] = 'application/x-www-form-urlencoded';

        //     return $this->send('POST', 'order/v1.0/orders/' . $id . '/dispatch', $headers, ['id' => $id]);
        // } catch (Exception $ex) {
        //     \Log::error('error: ' . $ex->getMessage() . $ex->getTraceAsString());

        //     return false;
        // }
    }

    /**
     * fullfill a order status to hubster.
     *
     * @param id
     */
    public function fulfillOrder($data)
    {
        $headers = $this->headers;

        try {
            return $this->send('POST', sprintf('manager/order/v1/sources/%s/orders/%s/fulfill', $data['source'], $data['orderId']), $headers);
        } catch (Exception $ex) {
            \Log::error('error: ' . $ex->getMessage() . $ex->getTraceAsString());

            return false;
        }
    }

    /**
     * notify estimate to hubser.
     *
     * @param $notifyData
     */
    public function notifyDeliveryQuote($eventId, $deliveryReferenceId, $notifyData)
    {
        $headers = $this->headers;
        $headers['X-Event-Id'] = $eventId;

        return $this->send('POST', "v1/delivery/$deliveryReferenceId/quotes", $headers, $notifyData);
    }

    /**
     * notify delivery accept.
     *
     * @param $notifyData
     */
    public function notifyAcceptDelivery($eventId, $deliveryReferenceId, $notifyData)
    {
        $headers = $this->headers;
        $headers['X-Event-Id'] = $eventId;

        return $this->send('POST', "v1/delivery/$deliveryReferenceId/accept", $headers, $notifyData);
    }

    /**
     * notify delivery cancel.
     *
     * @param $notifyData
     */
    public function notifyCancelDelivery($eventId, $deliveryReferenceId, $notifyData)
    {
        $headers = $this->headers;
        $headers['X-Event-Id'] = $eventId;

        return $this->send('POST', "v1/delivery/$deliveryReferenceId/cancel", $headers, $notifyData);
    }

    /**
     * update delivery status.
     *
     * @param $deliveryReferenceId
     * @param $data
     */
    public function updateDeliveryStatus($deliveryReferenceId, $data)
    {
        $headers = $this->headers;

        return $this->send('POST', "v1/delivery/$deliveryReferenceId/status", $headers, $data);
    }
}
