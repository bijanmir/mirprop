<?php

namespace Database\Factories;

use App\Models\Payment;
use App\Models\Lease;
use App\Models\Contact;
use Illuminate\Database\Eloquent\Factories\Factory;

class PaymentFactory extends Factory
{
    protected $model = Payment::class;

    public function definition(): array
    {
        return [
            'organization_id' => fn() => Lease::factory()->create()->organization_id,
            'lease_id' => Lease::factory(),
            'contact_id' => Contact::factory(),
            'amount_cents' => 150000,
            'method' => 'ach',
            'processor_id' => 'pi_' . fake()->uuid(),
            'status' => 'succeeded',
            'posted_at' => now(),
        ];
    }
}
