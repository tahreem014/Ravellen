<?php

namespace App\Http\Requests\Contact;

use Illuminate\Foundation\Http\FormRequest;

class SendContactMessageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'    => 'required|string|max:100',
            'email'   => 'required|email|max:255',
            'message' => 'required|string|min:10|max:3000',
        ];
    }

    public function attributes(): array
    {
        return ['name' => 'name', 'email' => 'email address', 'message' => 'message'];
    }
}
