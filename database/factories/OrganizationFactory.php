<?php
namespace Database\Factories;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Organization>
 *
 * Define the model's default state.
 *
 * @return array<string, mixed>
 */
class OrganizationFactory extends Factory
{
    public function definition(): array
    {
        $name = fake()->company();
        return [
            'name' => $name,
            'slug' => Str::slug($name),
            'settings' => [
                'timezone' => fake()->timezone(),
                'currency' => 'USD',
                'late_fee_percentage' => fake()->randomElement([5, 10, 15]),
                'grace_period_days' => fake()->randomElement([3, 5, 7]),
            ],
            'billing_info' => [
                'address' => fake()->address(),
                'city' => fake()->city(),
                'state' => fake()->stateAbbr(),
                'zip' => fake()->postcode(),
                'tax_id' => fake()->ein(),
            ],
        ];
    }
}