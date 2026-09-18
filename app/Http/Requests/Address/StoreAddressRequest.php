<?php

namespace App\Http\Requests\Address;

use Illuminate\Foundation\Http\FormRequest;

class StoreAddressRequest extends FormRequest
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
            'name'       => 'required|string|max:255',
            'address'    => 'required|string|max:500',
            'country'    => 'required|string|max:255',
            'city'       => 'required|string|max:255',
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
