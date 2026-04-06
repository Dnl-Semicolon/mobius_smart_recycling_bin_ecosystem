<?php

namespace App\Actions\Fortify;

use App\Concerns\PasswordValidationRules;
use App\Concerns\ProfileValidationRules;
use App\Enums\UserRole;
use App\Models\User;
use App\Rules\MalaysianMobilePhone;
use App\Rules\UniqueNormalizedEmail;
use App\Rules\UniqueNormalizedPhone;
use App\Support\EmailNormalizer;
use App\Support\PhoneNormalizer;
use Illuminate\Support\Facades\Validator;
use Laravel\Fortify\Contracts\CreatesNewUsers;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules, ProfileValidationRules;

    private const LEAD_CONFLICT_MESSAGE = 'Please contact admin to continue with this existing lead.';

    /**
     * Validate and create a newly registered user.
     *
     * @param  array<string, string>  $input
     */
    public function create(array $input): User
    {
        $normalizedInput = [
            ...$input,
            'email' => EmailNormalizer::normalize($input['email'] ?? null),
        ];

        Validator::make($normalizedInput, [
            'name' => $this->nameRules(),
            'email' => [
                'bail',
                ...$this->emailRules(),
                new UniqueNormalizedEmail(
                    'registration_requests',
                    column: 'contact_email',
                    scope: fn ($query) => $query->where('status', '!=', 'rejected'),
                    message: self::LEAD_CONFLICT_MESSAGE,
                ),
            ],
            'phone' => [
                'nullable',
                'bail',
                'string',
                'max:20',
                new MalaysianMobilePhone,
                new UniqueNormalizedPhone('users'),
                new UniqueNormalizedPhone(
                    'registration_requests',
                    column: 'contact_phone',
                    scope: fn ($query) => $query->where('status', '!=', 'rejected'),
                    message: self::LEAD_CONFLICT_MESSAGE,
                ),
            ],
            'password' => $this->passwordRules(),
        ])->validate();

        return User::create([
            'name' => $normalizedInput['name'],
            'email' => $normalizedInput['email'],
            'phone' => PhoneNormalizer::normalize($normalizedInput['phone'] ?? null),
            'password' => $normalizedInput['password'],
            'roles' => [UserRole::PublicUser->value],
        ]);
    }
}
