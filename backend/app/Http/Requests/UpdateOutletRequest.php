<?php

namespace App\Http\Requests;

use App\Enums\ContractStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateOutletRequest extends FormRequest
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
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'address' => ['sometimes', 'required', 'string', 'max:500'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'contact_name' => ['nullable', 'string', 'max:255'],
            'contact_phone' => ['nullable', 'string', 'max:50', 'regex:/^0\d[\d\s\-]{6,12}$/'],
            'contact_email' => ['nullable', 'email', 'max:255'],
            'operating_hours' => ['nullable', 'string'],
            'contract_status' => ['nullable', Rule::enum(ContractStatus::class)],
            'notes' => ['nullable', 'string'],
            'photo' => ['nullable', 'image', 'max:2048'],
            'remove_photo' => ['nullable', 'boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'The outlet name is required.',
            'address.required' => 'The outlet address is required.',
            'latitude.between' => 'Latitude must be between -90 and 90.',
            'longitude.between' => 'Longitude must be between -180 and 180.',
            'contact_email.email' => 'Please provide a valid email address.',
            'contact_phone.regex' => 'Please enter a valid Malaysian phone number (e.g. 012-345 6789).',
            'photo.image' => 'The file must be an image (JPEG, PNG, GIF, WebP).',
            'photo.max' => 'The photo must be smaller than 2MB.',
        ];
    }
}
