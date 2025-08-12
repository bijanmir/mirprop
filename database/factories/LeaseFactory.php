<?php

namespace Database\Factories;

use App\Models\Lease;
use App\Models\Organization;
use App\Models\Unit;
use App\Models\Contact;
use Illuminate\Database\Eloquent\Factories\Factory;

class LeaseFactory extends Factory
{
    protected $model = Lease::class;

    public function definition(): array
    {
        $start = now()->startOfMonth();
        return [
            'organization_id' => Organization::factory(),
            'unit_id' => Unit::factory(),
            'primary_contact_id' => Contact::factory(),
            'start_date' => $start,
            'end_date' => (clone $start)->addMonths(12),
            'rent_amount_cents' => 150000,
            'deposit_amount_cents' => 50000,
            'frequency' => 'monthly',
            'status' => 'active',
        ];
    }
}
