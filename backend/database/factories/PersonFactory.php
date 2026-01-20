<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Person>
 */
class PersonFactory extends Factory
{
    public function definition(): array
    {
        // Malaysian phone prefixes
        $prefixes = ['010', '011', '012', '013', '014', '016', '017', '018', '019'];

        return [
            'name' => fake()->name(),
            'birthday' => fake()->dateTimeBetween('-60 years', '-18 years'),
            'phone' => fake()->randomElement($prefixes).'-'.fake()->numerify('### ####'),
        ];
    }
}
