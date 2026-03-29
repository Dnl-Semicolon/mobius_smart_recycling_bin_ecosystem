<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
            'roles' => ['public_user'],
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    /** Create an admin user. */
    public function admin(): static
    {
        return $this->state(fn (array $attributes) => [
            'roles' => ['admin'],
        ]);
    }

    /** Create a collector user. */
    public function collector(): static
    {
        return $this->state(fn (array $attributes) => [
            'roles' => ['collector'],
        ]);
    }

    /** Create a public user. */
    public function publicUser(): static
    {
        return $this->state(fn (array $attributes) => [
            'roles' => ['public_user'],
        ]);
    }

    /** Create a store owner user. */
    public function storeOwner(): static
    {
        return $this->state(fn (array $attributes) => [
            'roles' => ['store_owner'],
        ]);
    }

    /** Create an agency admin user. */
    public function agencyAdmin(): static
    {
        return $this->state(fn (array $attributes) => [
            'roles' => ['agency_admin'],
        ]);
    }
}
