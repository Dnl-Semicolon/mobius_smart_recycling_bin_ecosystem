<?php

namespace App\Http\Requests;

use App\Enums\WasteType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SimulateDetectionRequest extends FormRequest
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
            'waste_type' => ['required', Rule::enum(WasteType::class)],
            'confidence' => ['nullable', 'integer', 'min:0', 'max:100'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'bin_id.required' => 'Please select a bin.',
            'bin_id.exists' => 'The selected bin does not exist.',
            'waste_type.required' => 'Please select a waste type.',
        ];
    }
}
