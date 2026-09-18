<?php

namespace App\Http\Requests\ReviewImage;

use Illuminate\Foundation\Http\FormRequest;

class StoreReviewImageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'review_id' => 'required|exists:reviews,id',
            'image_url' => 'required|image|mimes:jpg,jpeg,png,webp|max:2048',
        ];
    }
}
