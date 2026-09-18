<?php

namespace App\Http\Requests\Product;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'category_id'     => 'sometimes|required|exists:categories,id',
            'sub_category_id' => 'nullable|exists:sub_categories,id',
            'name'            => 'sometimes|required|string|max:255',
            'description'     => 'nullable|string',
        ];
    }
}
