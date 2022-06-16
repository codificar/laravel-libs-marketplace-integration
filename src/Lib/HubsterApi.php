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
	protected $access_token;
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
		//$key = \Settings::getMarketPlaceToken('ifood_auth_token');
		$key = "BW_qzZS3JvzvJoGKeUe6n0GFiIhNozSRPlX3VpDXN30.X6JQyfska1CUpjkoH_hsY7zmUlcEaQIm9glDePp1e9I"; //TODO colocar para puxar do oauth

		//\Log::debug('IFoodApi::__Construct__ -> ifood_auth_token:'.print_r($key,1));
		//initialize a common variable
		$this->access_token = $key;
		//initialize a common variable
		$this->headers    = [
			'Content-Type' => 'application/json',
			'Authorization' => 'Bearer '.$key,
			'X-Application-Id' => "f0d58c67-646f-495f-b5ae-9bde99b37a2c", //TODO vem na request mas não vi mudar
			'X-Store-Id' => '1234', //TODO padrão para teste,
			//'X-Event-Id' => '', //setado na hora de enviar
		];
	}

	public function notifyRequest($data)
	{
		$requestData = [
			"createdAt" => date('Y-m-d\TH-i-s'),
			"cost" =>  [
				"baseCost" =>  4.99, //TODO configurar
				"extraCost" =>  0
			],
			"provider" =>  "codificar",
			"minPickupDuration" =>  5, //TODO configurar
			"maxPickupDuration" =>  10,
			"currencyCode" =>  "BRL"
		];

		$headers = $this->headers;
		$headers["X-Event-Id"] = $data->eventId;
		$ref_id = $request->metadata["payload"]["deliveryReferenceId"];
		$this->send('POST', "v1/delivery/$ref_id/quotes", $headers, $requestData);
	}

	function createOrder(Request $request) {
		Log::info("Creating order");
		$order = OrderDetails::create([
			"store_id" => $request->metadata["storeId"],
			"order_id" => $request->metadata["payload"]["deliveryReferenceId"],
			"order_type" => "DELIVERY",
			"preparation_start_date_time" => $request->eventTime,
			"order_amount" => $request->metadata["payload"]["orderSubTotal"],
			"method_payment" => $request->metadata["payload"]["customerPayments"][0]["paymentMethod"]
		]);

		$this->notifyRequest($request);
		return $order;
	}


	public function send($requestType, $route, $headers, $body = null, $retry = 0)
	{
		Log::debug("requestType: ". print_r($requestType, 1));
		Log::debug("route: ". print_r($route, 1));
		Log::debug("headers: ". print_r($headers,1));
		Log::debug("body: ". print_r($body,1));

		try {
			$response = $this->client->request($requestType, $route, ['headers'       => $headers, 'form_params'   => $body]);
			Log::debug("Code: ". $response->getStatusCode());
		}
		catch(\Exception $ex){
			// reautenticacao caso a chave tenha dado 401 e um novo retry
			//TODO implementar reautenticação em para o hubster
			//if($ex->getCode() == 401 && $retry < 3){
			//	$clientId          = \Settings::findByKey('ifood_client_id');
			//	$clientSecret      = \Settings::findByKey('ifood_client_secret');
			//	$this->auth($clientId, $clientSecret);

			//	return $this->send($requestType, $route, $headers, $body, ++$retry);
			//}
			Log::info('erro send', $ex->getMessage());
		}

		return json_decode($response->getBody()->getContents());
	}
}