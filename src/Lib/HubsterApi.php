<?php

namespace Codificar\MarketplaceIntegration\Lib;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7;
use GuzzleHttp\Exception\ClientException;
use Carbon\Carbon;


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

		\Log::debug('IFoodApi::__Construct__ -> ifood_auth_token:'.print_r($key,1));
		//initialize a common variable
		$this->access_token = $key;
		//initialize a common variable
		$this->headers    = [
			'Content-Type' => 'application/json',
			'Authorization' => 'Bearer '.$key
		];
	}
}