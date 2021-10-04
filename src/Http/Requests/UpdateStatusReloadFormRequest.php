<?php

namespace Codificar\MarketplaceIntegration\Http\Requests;

use Codificar\MarketplaceIntegration\Lib\MarketplaceFactory;
use ReflectionClass;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdateStatusReloadFormRequest extends FormRequest
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
            'status_reload'           => ['required','integer']
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
