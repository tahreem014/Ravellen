<?php

namespace App\Http\Requests\Profile;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProfileRequest extends FormRequest
{
    /**
     * Keep these errors out of the shared sidebar error banner —
     * they're rendered inline below each field instead.
     */
    protected $errorBag = 'updateProfile';

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'         => 'sometimes|required|string|max:255',
            'email'        => [
                'sometimes',
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users')->ignore($this->user()->id),
            ],
            'phone_number' => ['nullable', 'regex:/^\+[1-9]\d{7,14}$/'],
            'avatar'       => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ];
    }

    public function messages(): array
    {
        return [
            'phone_number.regex' => 'Phone number must start with + and country code (e.g. +923001234567).',
        ];
    }
}
