<?php

namespace App\Http\Requests\ProductImage;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProductImageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'product_id' => 'sometimes|required|exists:products,id',
            'image'      => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'video'      => 'nullable|mimes:mp4,mov,avi|max:20480',
        ];
    }
}
