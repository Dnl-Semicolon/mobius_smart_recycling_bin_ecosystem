<?php

namespace App\Http\Requests;

use App\Logging\WideEvent;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class UpdateAddressRequest extends FormRequest
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
        return [];
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
}
