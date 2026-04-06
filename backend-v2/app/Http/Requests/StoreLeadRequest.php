<?php

namespace App\Http\Requests;

use App\Rules\MalaysianMobilePhone;
use App\Support\EmailNormalizer;
use Illuminate\Foundation\Http\FormRequest;

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
            'contact_email' => ['required', 'email', 'max:255'],
            'contact_phone' => ['required', 'string', 'max:20', new MalaysianMobilePhone],
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
