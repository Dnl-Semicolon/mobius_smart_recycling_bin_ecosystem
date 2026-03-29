<?php

namespace Database\Factories;

use App\Enums\ContractStatus;
use App\Models\Outlet;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Outlet>
 */
class OutletFactory extends Factory
{
    protected $model = Outlet::class;

    public function definition(): array
    {
        $phonePrefixes = ['010', '011', '012', '013', '014', '016', '017', '018', '019'];

        $lat = fake()->latitude(3.0, 3.3);
        $lng = fake()->longitude(101.4, 101.8);

        $openTime = fake()->randomElement(['08:00', '09:00', '10:00']);
        $closeTime = fake()->randomElement(['20:00', '21:00', '22:00', '23:00']);

        return [
            'name' => fake()->company().' '.fake()->randomElement(['Cafe', 'Coffee', 'Tea House', 'Food Court']),
            'address' => fake()->streetAddress().', '.fake()->randomElement(['50000 Kuala Lumpur', '47500 Subang Jaya', '46000 Petaling Jaya', '43000 Kajang']),
            'latitude' => $lat,
            'longitude' => $lng,
            'contact_name' => fake()->name(),
            'contact_phone' => fake()->randomElement($phonePrefixes).'-'.fake()->numerify('### ####'),
            'contact_email' => fake()->safeEmail(),
            'operating_hours' => self::buildStructuredHours($openTime, $closeTime),
            'contract_status' => fake()->randomElement(ContractStatus::cases()),
            'notes' => fake()->optional(0.3)->sentence(),
        ];
    }

    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'contract_status' => ContractStatus::Active,
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'contract_status' => ContractStatus::Inactive,
        ]);
    }

    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'contract_status' => ContractStatus::Pending,
        ]);
    }

    /**
     * Build structured operating hours JSON matching the admin form format.
     *
     * @param  array<string>  $closedDays  Day names to mark as closed (e.g. ['sunday'])
     */
    public static function buildStructuredHours(string $from = '09:00', string $to = '22:00', array $closedDays = []): string
    {
        $days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
        $hours = [];

        foreach ($days as $day) {
            $open = ! in_array($day, $closedDays);
            $hours[$day] = [
                'open' => $open,
                'from' => $open ? $from : '00:00',
                'to' => $open ? $to : '00:00',
            ];
        }

        return json_encode($hours);
    }
}
