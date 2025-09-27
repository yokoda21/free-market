<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PurchaseRequest extends FormRequest
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
             'payment_method' => [
                'required',
                'string',
                'in:convenience_store,card'// 有効な支払い方法のみ
            ],
            'postal_code' => [
                'required',
                'string',
                'regex:/^\d{3}-\d{4}$/'  // XXX-XXXX形式
            ],
            'address' => 'required',
            'building' => 'nullable',
        ];
    }    

    /**
     * Get custom error messages for validator errors.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'payment_method.required' => '支払い方法を選択してください',
            'postal_code.required' => '郵便番号を入力してください',
            'address.required' => '住所を入力してください',
            'payment_method.required' => '支払い方法を選択してください。',
            'payment_method.in' => '有効な支払い方法を選択してください。',
        ];
    }
}
