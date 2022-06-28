<?php

namespace Codificar\MarketplaceIntegration\Http\Rules;

use Illuminate\Contracts\Validation\ImplicitRule;

class MerchantDetailsRule implements ImplicitRule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct($merchantDetails)
    {
        $this->merchantDetails = $merchantDetails ;
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        return $this->merchantDetails['code'] === 200;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return $this->merchantDetails['message'] ;
    }
}
