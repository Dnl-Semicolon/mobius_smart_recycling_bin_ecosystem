<?php

namespace App\Http\Requests;

use App\Rules\MalaysianMobilePhone;
use App\Rules\UniqueNormalizedPhone;
use App\Support\EmailNormalizer;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreLeadRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'contact_name' => ['required', 'string', 'max:255'],
            'contact_email' => [
                'bail',
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email'),
                Rule::unique('registration_requests', 'contact_email')
                    ->where(fn ($query) => $query->where('status', '!=', 'rejected')),
            ],
            'contact_phone' => [
                'bail',
                'required',
                'string',
                'max:20',
                new MalaysianMobilePhone,
                new UniqueNormalizedPhone('users'),
                new UniqueNormalizedPhone(
                    'registration_requests',
                    column: 'contact_phone',
                    scope: fn ($query) => $query->where('status', '!=', 'rejected'),
                ),
            ],
            'company_name' => ['required', 'string', 'max:255'],
            'type' => ['required', 'in:beverage_company,recycling_company,government'],
            'selected_plan_id' => ['nullable', 'exists:plans,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'contact_name.required' => 'Please enter your name.',
            'contact_email.required' => 'Please enter your email address.',
            'contact_email.unique' => 'This email address has already been used.',
            'contact_phone.required' => 'Please enter your phone number.',
            'company_name.required' => 'Please enter your business name.',
            'type.required' => 'Please select your business type.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'contact_email' => EmailNormalizer::normalize($this->input('contact_email')),
        ]);
    }
}
