<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'string', 'max:255'],
            'email' => ['sometimes', 'string', 'email', 'max:255', 'unique:users,email,' . $this->user->id],
            'password' => ['sometimes', 'string', Password::defaults()],
            'dob' => ['sometimes', 'date', 'date_format:Y-m-d', 'before:today'],
            'address' => ['sometimes', 'string', 'max:1000'],
            'description' => ['sometimes', 'nullable', 'string', 'max:5000'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.max' => 'The name cannot exceed 255 characters.',
            'email.email' => 'Please provide a valid email address.',
            'email.unique' => 'This email is already registered.',
            'dob.date' => 'Please provide a valid date.',
            'dob.before' => 'The date of birth must be in the past.',
            'address.max' => 'The address cannot exceed 1000 characters.',
            'description.max' => 'The description cannot exceed 5000 characters.',
            'dob.date_format' => 'The date of birth must be in YYYY-MM-DD format.',
        ];
    }
}
