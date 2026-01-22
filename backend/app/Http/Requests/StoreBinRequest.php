<?php

namespace App\Http\Requests;

use App\Enums\BinStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreBinRequest extends FormRequest
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
            'serial_number' => ['required', 'string', 'max:50', 'unique:bins,serial_number'],
            'fill_level' => ['nullable', 'integer', 'between:0,100'],
            'status' => ['nullable', Rule::enum(BinStatus::class)],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'serial_number.required' => 'The serial number is required.',
            'serial_number.unique' => 'This serial number is already in use.',
            'fill_level.between' => 'Fill level must be between 0 and 100.',
        ];
    }
}
