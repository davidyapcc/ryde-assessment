<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class StoreUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', Password::defaults()],
            'dob' => ['required', 'date', 'date_format:Y-m-d', 'before:today'],
            'address' => ['required', 'string', 'max:1000'],
            'description' => ['nullable', 'string', 'max:5000'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'The name field is required.',
            'name.max' => 'The name cannot exceed 255 characters.',
            'email.required' => 'The email field is required.',
            'email.email' => 'Please provide a valid email address.',
            'email.unique' => 'This email is already registered.',
            'dob.date_format' => 'The date of birth must be in YYYY-MM-DD format.',
            'dob.before' => 'The date of birth must be in the past.',
            'dob.required' => 'The date of birth is required.',
            'password.required' => 'The password field is required.',
            'address.required' => 'The address field is required.',
            'address.max' => 'The address cannot exceed 1000 characters.',
            'description.max' => 'The description cannot exceed 5000 characters.',
        ];
    }
}
