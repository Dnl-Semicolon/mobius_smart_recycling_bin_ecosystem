<?php

namespace Database\Factories\Example;

use App\Models\Example\Person;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Example\Person>
 */
class PersonFactory extends Factory
{
    protected $model = Person::class;

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
