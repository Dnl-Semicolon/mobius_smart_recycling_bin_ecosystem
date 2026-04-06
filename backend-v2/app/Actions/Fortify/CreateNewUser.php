<?php

namespace App\Actions\Fortify;

use App\Concerns\PasswordValidationRules;
use App\Concerns\ProfileValidationRules;
use App\Enums\UserRole;
use App\Models\User;
use App\Support\EmailNormalizer;
use App\Support\PhoneNormalizer;
use Illuminate\Support\Facades\Validator;
use Laravel\Fortify\Contracts\CreatesNewUsers;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules, ProfileValidationRules;

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
            ...$this->profileRules(),
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
