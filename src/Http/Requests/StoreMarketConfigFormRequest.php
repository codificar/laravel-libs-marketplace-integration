<?php

namespace Codificar\MarketplaceIntegration\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule;

use Codificar\MarketplaceIntegration\Http\Rules\MerchantDetailsRule;

use Codificar\MarketplaceIntegration\Lib\MarketplaceFactory;



class StoreMarketConfigFormRequest extends FormRequest
{

    public $merchantDetails;

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $rules = [
            'merchant_name'         => 'required|string|min:4',
            'marketplace'           => ['required',Rule::in(MarketplaceFactory::$allMarketplaces)],
            'merchant_id'           => 'required|string|min:4'
        ];

        if($this->marketplace == MarketplaceFactory::IFOOD){
            $rules['merchant_config'] = [new MerchantDetailsRule($this->merchantDetails)] ;
        }

        return $rules;
    }


    /**
     * Set new vars to use in validation
     */
    protected function prepareForValidation(){
        $factory = MarketplaceFactory::create($this->marketplace);
        $this->merchantDetails = $factory->merchantDetails($this->merchant_id);
    }


    /**
     * retorn a json if fail validation.
     */
    protected function failedValidation(Validator $validator)
    {
        $this->statusCode  = Response::HTTP_UNPROCESSABLE_ENTITY ;
        throw new HttpResponseException(
            response()
                ->json(
                    [
                        'success' => false,
                        'errors' => $validator->errors()->all(),
                        'error_code' => $this->statusCode,
                    ]
                )
                ->setStatusCode($this->statusCode)
        );
    }
}
