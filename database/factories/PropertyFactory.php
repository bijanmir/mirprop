<?php
namespace Database\Factories;
use App\Models\Organization;
use Illuminate\Database\Eloquent\Factories\Factory;
/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Property>
 */
class PropertyFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'organization_id' => Organization::factory(),
            'name' => fake()->randomElement([
                fake()->streetName() . ' Apartments',
                fake()->lastName() . ' Plaza',
                fake()->company() . ' Tower',
                'The ' . fake()->lastName() . ' Building',
            ]),
            'type' => fake()->randomElement(['residential', 'commercial', 'mixed']),
            'address_line1' => fake()->streetAddress(),
            'address_line2' => fake()->optional()->secondaryAddress(),
            'city' => fake()->city(),
            'state' => fake()->stateAbbr(),
            'zip' => fake()->postcode(),
            'country' => 'US',
            'meta' => [
                'year_built' => fake()->numberBetween(1960, 2023),
                'parking_spaces' => fake()->numberBetween(10, 100),
                'amenities' => fake()->randomElements([
                    'pool',
                    'gym',
                    'laundry',
                    'parking',
                    'elevator',
                    'security',
                    'concierge',
                    'playground'
                ], fake()->numberBetween(2, 5)),
            ],
        ];
    }

    /**

    Indicate that the property is residential.
    */
    public function residential(): static
    {
        return $this->state(fn(array $attributes) => [
            'type' => 'residential',
        ]);
    }

    /**

    Indicate that the property is commercial.
    */
    public function commercial(): static
    {
        return $this->state(fn(array $attributes) => [
            'type' => 'commercial',
        ]);
    }
}