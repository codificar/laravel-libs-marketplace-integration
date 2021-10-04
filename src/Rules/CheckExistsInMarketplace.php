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
            $factory = MarketplaceFactory::createMarketplace($this->request->type);
            $this->marketplaceApi = $factory->getMerchantDetails($value);
            $this->request->merge(['merchantDetails' => $this->marketplaceApi]);
            return true;
        } catch (\Exception $e) {
            $this->errorMessage = $e->getMessage();
            return false;
        }
    }

    public function message()
    {
        return $this->errorMessage;
    }
}