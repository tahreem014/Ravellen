<?php

namespace App\Http\Requests\Profile;

use Illuminate\Foundation\Http\FormRequest;

class DeleteProfileRequest extends FormRequest
{
    /**
     * Keep these errors out of the shared sidebar error banner —
     * they're rendered inline below the password field instead.
     */
    protected $errorBag = 'deleteAccount';

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'password' => 'required|string',
        ];
    }

    public function messages(): array
    {
        return [
            'password.required' => 'Please enter your password to confirm account deletion.',
        ];
    }
}
