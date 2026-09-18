<?php

namespace App\Http\Requests\Review;

use Illuminate\Foundation\Http\FormRequest;

class UpdateReviewRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'user_id'            => 'sometimes|required|exists:users,id',
            'product_variant_id' => 'sometimes|required|exists:product_variants,id',
            'rating'             => 'sometimes|required|integer|min:1|max:5',
            'comments'           => 'nullable|string',
        ];
    }
}
