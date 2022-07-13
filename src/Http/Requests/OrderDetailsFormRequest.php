<?php

namespace Codificar\MarketplaceIntegration\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Response;

use Codificar\MarketplaceIntegration\Models\OrderDetails;



class OrderDetailsFormRequest extends FormRequest
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
            'shop_id'           => 'required|numeric',
            'order_id'          => 'required|string|min:5',
            'order'             => 'required'
        ];
    }


    /**
     * Set new vars to use in validation
     */
    protected function prepareForValidation(){
        $this->order = OrderDetails::where('shop_id', $this->shop_id)->where('order_id', $this->order_id);
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
