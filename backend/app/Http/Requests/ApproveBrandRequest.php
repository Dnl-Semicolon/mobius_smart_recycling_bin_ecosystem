<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ApproveBrandRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        return [
            'points_multiplier' => ['required', 'numeric', 'min:1.00', 'max:5.00'],
            'rewards_budget' => ['required', 'integer', 'min:0', 'max:999999'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'points_multiplier.required' => 'A points multiplier is required to approve.',
            'rewards_budget.required' => 'A rewards budget is required to approve.',
        ];
    }
}
