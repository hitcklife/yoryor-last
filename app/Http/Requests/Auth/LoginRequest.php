<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'email' => ['required_without:phone', 'email', 'nullable'],
            'phone' => ['required_without:email', 'nullable'],
            'password' => ['required']
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array
     */
    public function messages(): array
    {
        return [
            'email.required_without' => 'Either email or phone is required.',
            'phone.required_without' => 'Either email or phone is required.',
            'email.email' => 'Please enter a valid email address.',
            'password.required' => 'Password is required.',
        ];
    }

    /**
     * Get the credentials for authentication.
     *
     * @return array
     */
    public function credentials(): array
    {
        $credentials = [];

        if ($this->filled('email')) {
            $credentials['email'] = $this->input('email');
        } else {
            $credentials['phone'] = $this->input('phone');
        }

        $credentials['password'] = $this->input('password');

        return $credentials;
    }
}
