<?php

namespace App\Http\Requests\Address;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAddressRequest extends FormRequest
{
    /**
     * Keep these errors out of the shared sidebar error banner —
     * they're rendered inline below each field instead.
     */
    protected $errorBag = 'address';

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'       => 'sometimes|required|string|max:255',
            'address'    => 'sometimes|required|string|max:500',
            'country'    => 'sometimes|required|string|max:255',
            'city'       => 'sometimes|required|string|max:255',
            'phone'      => ['nullable', 'regex:/^\+[1-9]\d{7,14}$/'],
            'is_default' => 'nullable|boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'phone.regex' => 'Phone number must start with + and country code (e.g. +923001234567).',
        ];
    }
}
