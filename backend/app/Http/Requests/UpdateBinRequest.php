<?php

namespace App\Http\Requests;

use App\Enums\BinStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateBinRequest extends FormRequest
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
            'fill_level.between' => 'Fill level must be between 0 and 100.',
        ];
    }
}
