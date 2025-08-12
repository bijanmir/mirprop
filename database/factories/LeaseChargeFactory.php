<?php

namespace Database\Factories;

use App\Models\LeaseCharge;
use App\Models\Lease;
use Illuminate\Database\Eloquent\Factories\Factory;

class LeaseChargeFactory extends Factory
{
    protected $model = LeaseCharge::class;

    public function definition(): array
    {
        return [
            'lease_id' => Lease::factory(),
            'type' => 'rent',
            'amount_cents' => 150000,
            'description' => 'Monthly Rent',
            'due_date' => now()->addMonth()->startOfMonth(),
            'balance_cents' => 150000,
            'is_recurring' => false,
            'day_of_month' => 1,
        ];
    }
}
