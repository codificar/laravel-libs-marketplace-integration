<?php

namespace Codificar\MarketplaceIntegration\Rules;

use Codificar\MarketplaceIntegration\Lib\MarketplaceFactory;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Http\Request;

class CheckExistsInMarketplace implements Rule
{
    public $request;
    private $marketplaceApi;
    private $errorMessage;

    public function __construct(Request $request) {
        $this->request = $request;
    }

    public function passes($attribute, $value) {
        try {
            $this->marketplaceApi = MarketplaceFactory::createMarketplace($this->request->type)->getMerchantDetails($value);
            return true;
        } catch (\Throwable $th) {
            $this->errorMessage = $th->getMessage();
            return false;
        }
    }

    public function message()
    {
        return $this->errorMessage;
    }
}