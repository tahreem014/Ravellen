<?php

namespace App\Http\Requests\Review;

use Illuminate\Foundation\Http\FormRequest;

class StoreReviewRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'order_item_id' => 'required|exists:order_items,id',
            'rating'        => 'required|integer|min:1|max:5',
            'comments'      => 'nullable|string|max:2000',
            'images'        => 'nullable|array|max:5',
            'images.*'      => 'image|mimes:jpg,jpeg,png,webp|max:1024',
        ];
    }
}
