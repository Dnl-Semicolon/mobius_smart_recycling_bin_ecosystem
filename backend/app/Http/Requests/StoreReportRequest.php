<?php

namespace App\Http\Requests;

use App\Enums\ReportType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreReportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'bin_id' => ['required', 'integer', 'exists:bins,id'],
            'type' => ['required', Rule::enum(ReportType::class)],
            'description' => ['required', 'string', 'max:1000'],
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
            'type.required' => 'A report type is required.',
            'type.Illuminate\Validation\Rules\Enum' => 'The report type must be a valid type.',
            'description.required' => 'A description is required.',
            'description.max' => 'Description must not exceed 1000 characters.',
        ];
    }
}
