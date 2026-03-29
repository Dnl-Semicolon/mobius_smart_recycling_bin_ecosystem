<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'username' => ['nullable', 'string', 'max:255', 'regex:/^[a-z0-9_]+$/', Rule::unique('users')->ignore($this->user()->id)],
            'email' => ['required', 'email', 'max:255', Rule::unique('users')->ignore($this->user()->id)],
            'phone' => ['nullable', 'string', 'max:20', 'regex:/^0\d[\d\s\-]{6,12}$/', Rule::unique('users')->ignore($this->user()->id)],
            'bio' => ['nullable', 'string', 'max:500'],
            'avatar' => ['nullable', 'image', 'max:2048'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'username.regex' => 'Username may only contain lowercase letters, numbers, and underscores.',
            'username.unique' => 'This username is already taken.',
            'email.unique' => 'This email address is already taken by another account.',
            'phone.regex' => 'Please enter a valid Malaysian phone number (e.g. 012-345 6789).',
            'phone.unique' => 'This phone number is already associated with another account.',
            'avatar.image' => 'The avatar must be an image file.',
            'avatar.max' => 'The avatar must be less than 2MB.',
        ];
    }
}
