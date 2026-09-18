<?php

namespace App\Http\Requests\AddToCart;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAddToCartRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'qty'        => 'required|integer|min:1|max:100',
            'comment'    => 'nullable|string|max:1000',
            'preference' => 'nullable|string|max:255',
        ];
    }
}
