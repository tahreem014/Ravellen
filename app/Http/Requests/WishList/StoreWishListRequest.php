<?php

namespace App\Http\Requests\WishList;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreWishListRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'user_id'    => 'required|exists:users,id',
            'product_id' => [
                'required',
                'exists:products,id',
                Rule::unique('wish_lists')->where('user_id', $this->input('user_id')),
            ],
        ];
    }
}
