<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RatingRequest extends FormRequest
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
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'comment' => ['nullable', 'string', 'max:1000'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'rating.required' => '評価を選択してください',
            'rating.integer' => '評価は1から5の整数で選択してください',
            'rating.min' => '評価は1以上で選択してください',
            'rating.max' => '評価は5以下で選択してください',
            'comment.max' => 'コメントは1000文字以内で入力してください',
        ];
    }
}
