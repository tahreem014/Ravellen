<?php

namespace App\Http\Requests\ReviewImage;

use Illuminate\Foundation\Http\FormRequest;

class UpdateReviewImageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'review_id' => 'sometimes|required|exists:reviews,id',
            'image_url' => 'sometimes|required|image|mimes:jpg,jpeg,png,webp|max:2048',
        ];
    }
}
