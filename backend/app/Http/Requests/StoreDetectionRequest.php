<?php

namespace App\Http\Requests;

use App\Enums\WasteType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

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
            'image' => ['nullable', 'image', 'mimes:jpeg,png,jpg', 'max:5120'],
            'waste_type' => ['nullable', Rule::enum(WasteType::class)],
            'confidence' => ['nullable', 'integer', 'min:0', 'max:100'],
            'weight_g' => ['nullable', 'integer', 'min:0', 'max:50000'],
            'user_id' => ['nullable', 'integer', 'exists:users,id'],
            'detected_brand_id' => ['nullable', 'integer', 'exists:brands,id'],
            'detected_brand' => ['nullable', 'string', 'max:100'],
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
            'image.mimes' => 'The image must be a JPEG or PNG file.',
            'image.max' => 'The image must not exceed 5MB.',
            'waste_type.enum' => 'The waste type must be a valid type.',
            'confidence.min' => 'Confidence must be between 0 and 100.',
            'confidence.max' => 'Confidence must be between 0 and 100.',
            'user_id.exists' => 'The specified user does not exist.',
            'detected_brand_id.exists' => 'The specified brand does not exist.',
        ];
    }
}
