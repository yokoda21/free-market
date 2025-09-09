<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ExhibitionRequest extends FormRequest
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
            'name' => 'required|max:255',
            'description' => 'required|max:255',
            'image' => 'required|image|mimes:jpeg,png,jpg',
            'category_ids' => 'required|array|min:1',
            'category_ids.*' => 'exists:categories,id',
            'condition_id' => 'required|exists:conditions,id',
            'price' => 'required|integer|min:0',
            'brand' => 'nullable|string|max:255',
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
            'name.required' => '商品名を入力してください',
            'name.max' => '商品名は255文字以内で入力してください',
            'description.required' => '商品説明を入力してください',
            'description.max' => '商品説明は255文字以内で入力してください',
            'image.required' => '商品画像をアップロードしてください',
            'image.image' => '商品画像は画像ファイルを選択してください',
            'image.mimes' => '商品画像はjpeg、png、jpg形式で選択してください',
            'category_ids.required' => '商品のカテゴリーを選択してください',
            'category_ids.array' => '商品のカテゴリーを正しく選択してください',
            'category_ids.min' => '商品のカテゴリーを最低1つ選択してください',
            'category_ids.*.exists' => '選択されたカテゴリーが無効です',
            'condition_id.required' => '商品の状態を選択してください',
            'condition_id.exists' => '選択された商品の状態が無効です',
            'price.required' => '商品価格を入力してください',
            'price.integer' => '商品価格は整数で入力してください',
            'price.min' => '商品価格は0円以上で入力してください',
            'brand.max' => 'ブランド名は255文字以内で入力してください',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array
     */
    public function attributes()
    {
        return [
            'name' => '商品名',
            'description' => '商品説明',
            'image' => '商品画像',
            'category_ids' => 'カテゴリー',
            'condition_id' => '商品の状態',
            'price' => '商品価格',
            'brand' => 'ブランド',
        ];
    }
}
