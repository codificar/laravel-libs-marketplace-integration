<?php

namespace Codificar\MarketplaceIntegration\Lib;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7;
use GuzzleHttp\Exception\ClientException;
use Carbon\Carbon;
use Codificar\MarketplaceIntegration\Models\OrderDetails;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;


class HubsterApi {
	protected $clientId;
	protected $clientSecret;
	protected $baseUrl;
	protected $accessToken;
	protected $headers;
	protected $client;


	/**
	 * Instantiate a new Hubster instance with common variables and configuration.
	 */
	function __construct()
	{
		$this->baseUrl      = 'https://partners-staging.tryhubster.com/';
		$this->client       = new Client([
			'base_uri'  => $this->baseUrl
		]);

		//get the marketplace token
		$key =  \Settings::getMarketPlaceToken('hubster_auth_token');

		$applicationId =  \Settings::findByKey('hubster_application_id', "f0d58c67-646f-495f-b5ae-9bde99b37a2c");

		$this->accessToken = $key;

		$this->headers    = [
			'Content-Type' => 'application/json',
			'Authorization' => 'Bearer '.$key,
			'X-Application-Id' => $applicationId, //TODO vem na request mas não vi mudar
			//'X-Event-Id' => '', //setado na hora de enviar
		];
	}

	/**
	 * Set X-Store-Id header
	 */
	public function setStoreId($storeId) {
		$this->headers['X-Store-Id'] = $storeId;
	}

	/**
	 *  Set X-Application-Id header
	 */
	public function setApplicationId($applicationId) {
		$this->headers['X-Application-Id'] = $applicationId;
	}

	/**
	 * Authenticate and save updated keys
	 */
	public function auth($clientId, $clientSecret)
	{

		//dd(func_get_args());

		\Log::debug('clientId:'.print_r($clientId,1));
		\Log::debug('clientSecret:'.print_r($clientSecret,1));

		try
		{
			$headers    = ['Content-Type' => 'application/x-www-form-urlencoded'];
			$body       = [
				'grant_type'     	=> 'client_credentials',
				'client_id'     	=> $clientId,
				'client_secret'  	=> $clientSecret,
				'scope'				=> 'ping'
			];

			$response = $this->send('POST', 'v1/auth/token', $headers, $body);

			$this->accessToken = $response->access_token;
			$test =  \Settings::updateOrCreateByKey('hubster_auth_token', $this->accessToken);
			\Log::debug("updateOrCreateByKey: hubster_auth_token ". print_r($test,1));

			$test =  \Settings::updateOrCreateByKey('hubster_expiry_token', Carbon::now()->addHours(1));
			\Log::debug("updateOrCreateByKey: hubster_expiry_token ". print_r($test,1));

			return $response;
		}
		catch (\Exception $e)
		{
			\Log::debug($e->getMessage());
			return $e;
		}
	}

	/**
	 * get new orders
	 */
	public function newOrders()
	{
		$body = [ 
			'limit' => '10' ,
			'minDateTime' => Carbon::now()->addMinutes(-10),
			'maxDateTime' => Carbon::now()
		];

		return $this->send('GET','manager/order/v1/orders', $this->headers, $body);
	}

	/**
	 * Api Client send data and get json return
	 */
	public function send($requestType, $route, $headers, $body = null, $retry = 0)
	{
		
		$response = null;

		try {
			$options['headers'] =  $headers ;
			
			if(strtolower($requestType) == 'get') $options['query'] =  $body ;
			else $options['form_params'] =  $body ;

			$response = $this->client->request($requestType, $route, $options);
			
			\Log::info("Code: ". $response->getStatusCode());
		}
		catch(\Exception $ex){
			
			//reautenticacao caso a chave tenha dado 401 e um novo retry
			if(in_array($ex->getCode(), [401,403]) && $retry < 3){
				$clientId          =  \Settings::findByKey('hubster_client_id', 'f0d58c67-646f-495f-b5ae-9bde99b37a2c');
				$clientSecret      =  \Settings::findByKey('hubster_client_secret', 'WLRADY3XT2IMUHEE4ENA');
				$this->auth($clientId, $clientSecret);

				return $this->send($requestType, $route, $headers, $body, ++$retry);
			}

			Log::info('erro send: ' . $ex->getMessage());
		}

		return json_decode($response->getBody()->getContents());
	}

	// public function notifyRequest($data)
	// {
	// 	$requestData = [
	// 		"createdAt" => date('Y-m-d\TH:i:sP'),
	// 		"cost" =>  [
	// 			"baseCost" =>  4.99, //TODO configurar
	// 			"extraCost" =>  0
	// 		],
	// 		"provider" =>  "codificar",
	// 		"minPickupDuration" =>  5, //TODO configurar
	// 		"maxPickupDuration" =>  10,
	// 		"currencyCode" =>  "BRL"
	// 	];

	// 	$headers = $this->headers;
	// 	$headers["X-Event-Id"] = $data->eventId;
	// 	$ref_id = $data->metadata["payload"]["deliveryReferenceId"];
	// 	$this->send('POST', "v1/delivery/$ref_id/quotes", $headers, json_decode(json_encode($requestData), true));
	// }

	// function createOrder(Request $request) {
	// 	Log::info("Creating order");
	// 	$order = OrderDetails::where(["order_id" => $request->metadata["payload"]["deliveryReferenceId"]])->first();
	// 	if(!$order) {
	// 		$order = OrderDetails::create([
	// 			"store_id" => $request->metadata["storeId"],
	// 			"order_id" => $request->metadata["payload"]["deliveryReferenceId"],
	// 			"order_type" => "DELIVERY",
	// 			"preparation_start_date_time" => $request->eventTime,
	// 			"order_amount" => $request->metadata["payload"]["orderSubTotal"],
	// 			"method_payment" => $request->metadata["payload"]["customerPayments"][0]["paymentMethod"]
	// 		]);
	// 		$this->notifyRequest($request);
	// 	}

	// 	return $order;
	// }

}