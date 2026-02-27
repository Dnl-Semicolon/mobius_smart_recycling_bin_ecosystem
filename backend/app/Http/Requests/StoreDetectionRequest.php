<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreDetectionRequest extends FormRequest
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
            'bin_id' => ['required', 'integer', 'exists:bins,id'],
            'image' => ['required', 'image', 'mimes:jpeg,png,jpg', 'max:5120'],
            'detected_at' => ['nullable', 'date'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'bin_id.required' => 'A bin ID is required.',
            'bin_id.exists' => 'The specified bin does not exist.',
            'image.required' => 'An image is required for detection.',
            'image.mimes' => 'The image must be a JPEG or PNG file.',
            'image.max' => 'The image must not exceed 5MB.',
        ];
    }
}
