<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AddressSaveRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'id'            => 'integer',
            'name'          => 'required | string',
            'addressDetail' => 'required | string',
            'city'          => 'required | string',
            'county'        => 'required | string',
            'isDefault'     => 'bool',
            'tel'           => 'regex:/^1[0-9]{10}$/',
            'province'      => 'required | string',
        ];
    }
}
