<?php

namespace App\Http\Requests\Auth;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Foundation\Http\FormRequest;

class ChangePasswordRequest extends FormRequest
    {
    public function authorize(): bool
        {
        return true;
        }

    public function rules(): array
        {
        return [
            'current_password' => ['required',], // custom rule to check old password
            'new_password' => ['required', 'min:8'],
            'new_password_confirmation' => ['required', 'same:new_password'],
        ];
        }
    public function messages()
        {
        return [
            'current_password.required' => 'Please enter your current password.',
            'new_password.required' => 'Please enter a new password.',
            'new_password.min' => 'New password must be at least 6 characters.',
            'new_password_confirmation.required' => 'Please confirm your new password.',
            'new_password_confirmation.same' => 'New password & confirm password does not match.',
        ];
        }

    public function withValidator($validator)
        {
        $validator->after(function ($validator) {
            if (!Hash::check($this->current_password, Auth::user()->password)) {
                $validator->errors()->add('current_password', 'The current password is incorrect.');
                }
            });
        }
    }
