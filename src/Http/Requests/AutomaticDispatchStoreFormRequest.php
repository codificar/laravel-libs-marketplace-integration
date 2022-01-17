<?php

namespace Codificar\MarketplaceIntegration\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

use Codificar\MarketplaceIntegration\Repositories\DispatchRepository;

class AutomaticDispatchStoreFormRequest extends FormRequest
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
            'institution_id'          => 'required|integer',
            'provider_type_id'        => 'required|integer',
            'wait_time_limit'         => 'required|integer',
            'max_delivery'            => 'required|integer',
        ];
    }

    /**
     * Prepare the data for validation.
     *
     * @return void
     */
    protected function prepareForValidation() {

		$this->merge([
            'institution_id' => $this->route('institution_id')
        ]);
	}


    /**
     * retorn a json if fail validation.
     */
    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json(['success' => false, 'errors' => $validator->errors()->all(), 'message' => trans('required')]));
    }
}
