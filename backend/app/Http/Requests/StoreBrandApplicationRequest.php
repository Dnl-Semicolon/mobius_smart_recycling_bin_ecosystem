<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class StoreBrandApplicationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        $rules = [
            'flow' => ['required', 'in:claim,new'],
            'contact_person' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['nullable', 'string', 'max:20'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ];

        if ($this->input('flow') === 'claim') {
            $rules['brand_id'] = ['required', 'exists:brands,id'];
        } else {
            $rules['brand_name'] = ['required', 'string', 'max:255'];
            $rules['description'] = ['nullable', 'string', 'max:1000'];
            $rules['website_url'] = ['nullable', 'url', 'max:255'];
            $rules['logo'] = ['nullable', 'image', 'max:2048'];
        }

        return $rules;
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'brand_id.required' => 'Please select a brand from the directory.',
            'brand_id.exists' => 'The selected brand is not in our directory.',
            'brand_name.required' => 'A brand name is required for new brand requests.',
            'email.unique' => 'This email is already registered.',
            'password.confirmed' => 'The passwords do not match.',
        ];
    }
}
