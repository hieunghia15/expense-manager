<?php

declare(strict_types=1);

namespace App\Requests;

use App\Core\FormRequest;

final class LoginRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'email' => ['required', 'email'],
            'password' => ['required', 'passwordStrength'],
        ];
    }

    public function messages(): array
    {
        return [
            'email.required' => 'Email is required.',
            'email.email' => 'Invalid email format.',
            'password.required' => 'Password is required.',
            'password.passwordStrength' => 'Password must be at least 6 characters and include uppercase, lowercase, number, and special character.',
        ];
    }

    public function getEmail(): string
    {
        return trim((string) $this->input('email', ''));
    }

    public function getPassword(): string
    {
        return (string) $this->input('password', '');
    }
}
