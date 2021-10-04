<?php

namespace Codificar\MarketplaceIntegration\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class IFoodCredentialsFormRequest extends FormRequest
{
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
            'ifood_client_id'          => 'required|string',
            'ifood_client_secret'          => 'required|string',
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
