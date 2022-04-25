<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TransactionRequest extends FormRequest
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
        //Max value to transaction ???
        //Valid CPF??
        return [
            'value' => ['required', 'integer', 'min:1'],
            'payer' => ['required', 'string', 'min:11', 'max:14', 'different:payee'],
            'payee' => ['required', 'string', 'min:11', 'max:14'],
        ];
    }
}
