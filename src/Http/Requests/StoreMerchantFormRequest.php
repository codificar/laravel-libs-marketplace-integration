<?php

namespace Codificar\MarketplaceIntegration\Http\Requests;

use Codificar\MarketplaceIntegration\Lib\MarketplaceFactory;
use Codificar\MarketplaceIntegration\Rules\CheckExistsInMarketplace;
use ReflectionClass;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Request;

class StoreMerchantFormRequest extends FormRequest
{
    public $merchantTypes;
    public $reflection;
    
    public function __construct()
    {
        $this->reflection = new ReflectionClass(new MarketplaceFactory());
		$this->merchantTypes = implode(",",(array)$this->reflection->getConstants());
    }

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
        return [
            'merchant_id'           => ['required','string','min:5', new CheckExistsInMarketplace($this)],  // new ExistsInMarketplace ($this->type,  $this->merchant_id, & $this->merchantDetailsInMarketplace)
            'id'                    => 'integer|exists:market_config,id', // id do banco opcional, se vier tratar como inteiro
            'type'                  => "required|in:$this->merchantTypes" // obrigatorio (criar uma regra para ver se temos o type implementado)
        ];
    }


    /**
     * retorn a json if fail validation.
     */
    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json(['success' => false, 'errors' => $validator->errors()->all(), 'message' => trans('required')]));
    }
}
