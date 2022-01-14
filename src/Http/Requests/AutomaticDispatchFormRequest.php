<?php

namespace Codificar\MarketplaceIntegration\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

use Codificar\MarketplaceIntegration\Repositories\DispatchRepository;

class AutomaticDispatchFormRequest extends FormRequest
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
            'automaticDispatch'       => 'required'  
        ];
    }

    /**
     * Prepare the data for validation.
     *
     * @return void
     */
    protected function prepareForValidation() {
        $automaticDispatch = null;
        if($this->route('institution_id'))
            $automaticDispatch = DispatchRepository::getAutomaticDispatch($this->route('institution_id'));

		$this->merge([
            'institution_id' => $this->route('institution_id'),
            'automaticDispatch' => $automaticDispatch
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
