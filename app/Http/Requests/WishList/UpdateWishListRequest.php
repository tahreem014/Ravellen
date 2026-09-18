<?php

namespace App\Http\Requests\WishList;

use Illuminate\Foundation\Http\FormRequest;

class UpdateWishListRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'user_id'    => 'sometimes|required|exists:users,id',
            'product_id' => 'sometimes|required|exists:products,id',
        ];
    }
}
