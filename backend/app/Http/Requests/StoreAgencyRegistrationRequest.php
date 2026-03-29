<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class StoreAgencyRegistrationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'contact_person' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['nullable', 'string', 'max:20'],
            'logo' => ['nullable', 'image', 'max:2048'],
            'description' => ['nullable', 'string', 'max:1000'],
            'fleet_size' => ['nullable', 'integer', 'min:1', 'max:9999'],
            'coverage_area' => ['nullable', 'string', 'max:500'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'name.required' => 'A company name is required.',
            'contact_person.required' => 'A contact person name is required.',
            'email.required' => 'An email address is required for your account.',
            'email.unique' => 'This email is already registered.',
            'password.confirmed' => 'The passwords do not match.',
        ];
    }
}
