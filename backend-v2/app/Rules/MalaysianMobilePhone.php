<?php

namespace App\Rules;

use App\Support\PhoneNormalizer;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class MalaysianMobilePhone implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ($value === null || $value === '') {
            return;
        }

        if (! is_string($value) || ! PhoneNormalizer::isValid($value)) {
            $fail('Please enter a valid Malaysian mobile number.');
        }
    }
}
