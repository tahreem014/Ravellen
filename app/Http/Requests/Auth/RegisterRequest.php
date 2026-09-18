<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'         => 'required|string|max:255',
            'email'        => 'required|string|email:rfc,filter|max:255|unique:users',
            'password'     => 'required|string|min:8|confirmed',
            'phone_number' => 'required|regex:/^\+[1-9]\d{7,14}$/',
            'avatar'       => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ];
    }

    public function messages(): array
    {
        return [
            'phone_number.required' => 'Phone number is required.',
            'phone_number.regex'    => 'Phone number must start with + and country code (e.g. +923001234567).',
            'password.confirmed'    => 'Passwords do not match.',
            'email.unique'          => 'This email is already registered.',
        ];
    }
}
