<?php

namespace App\Http\Requests;

use App\Logging\WideEvent;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class UpdatePersonRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'birthday' => ['required', 'date'],
            'phone' => ['required', 'string', 'regex:/^01\\d-\\d{3,4} \\d{4}$/'],
            'line_1' => ['required', 'string', 'max:255'],
            'line_2' => ['nullable', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:255'],
            'state' => ['required', 'string', Rule::in($this->stateOptions())],
            'postal_code' => ['required', 'string', 'regex:/^\\d{5}$/'],
        ];
    }

    /**
     * Get custom error messages for validator failures.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Name is required.',
            'name.string' => 'Name must be a string.',
            'name.max' => 'Name must be at most :max characters.',
            'birthday.required' => 'Birthday is required.',
            'birthday.date' => 'Birthday must be a valid date.',
            'phone.required' => 'Phone is required.',
            'phone.string' => 'Phone must be a string.',
            'phone.regex' => 'Phone must match the Malaysian format 01X-XXX XXXX or 01X-XXXX XXXX.',
            'line_1.required' => 'Address line 1 is required.',
            'line_1.string' => 'Address line 1 must be a string.',
            'line_1.max' => 'Address line 1 must be at most :max characters.',
            'line_2.string' => 'Address line 2 must be a string.',
            'line_2.max' => 'Address line 2 must be at most :max characters.',
            'city.required' => 'City is required.',
            'city.string' => 'City must be a string.',
            'city.max' => 'City must be at most :max characters.',
            'state.required' => 'State is required.',
            'state.string' => 'State must be a string.',
            'state.in' => 'State must be one of the Malaysian states.',
            'postal_code.required' => 'Postal code is required.',
            'postal_code.string' => 'Postal code must be a string.',
            'postal_code.regex' => 'Postal code must be 5 digits.',
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $errors = $validator->errors();

            if ($errors->isEmpty()) {
                return;
            }

            app(WideEvent::class)->enrichMany([
                'business.validation.failed' => true,
                'business.validation.error_count' => $errors->count(),
                'business.validation.fields' => array_keys($errors->messages()),
                'business.validation.messages' => $errors->messages(),
                'business.validation.request' => static::class,
            ]);
        });
    }

    /**
     * Get the allowed Malaysian states for validation.
     *
     * @return array<int, string>
     */
    private function stateOptions(): array
    {
        return [
            'Johor',
            'Kedah',
            'Kelantan',
            'Melaka',
            'N. Sembilan',
            'Pahang',
            'Penang',
            'Perak',
            'Perlis',
            'Sabah',
            'Sarawak',
            'Selangor',
            'Terengganu',
            'KL',
            'Labuan',
            'Putrajaya',
        ];
    }
}
