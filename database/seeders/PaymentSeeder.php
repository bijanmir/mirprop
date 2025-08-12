<?php

namespace Database\Seeders;

use App\Models\Organization;
use App\Models\Lease;
use App\Models\Payment;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class PaymentSeeder extends Seeder
{
    public function run(): void
    {
        $org = Organization::where('slug', 'demo-org')->first();
        if (!$org) return;

        $leases = Lease::where('organization_id', $org->id)
            ->where('status', 'active')
            ->with('primaryContact')
            ->get();

        foreach ($leases as $lease) {
            if (!$lease->primaryContact) continue;

            $paymentsToCreate = fake()->numberBetween(3, 12);
            
            for ($i = 0; $i < $paymentsToCreate; $i++) {
                $paymentDate = Carbon::now()->subMonths($paymentsToCreate - $i - 1)->addDays(fake()->numberBetween(1, 28));
                
                $status = fake()->randomElement([
                    'succeeded' => 85,
                    'pending' => 10,
                    'failed' => 5,
                ]);

                $method = fake()->randomElement([
                    'ach' => 60,
                    'card' => 25,
                    'check' => 10,
                    'cash' => 5,
                ]);

                $baseAmount = $lease->rent_amount_cents;
                $variance = fake()->numberBetween(-5000, 5000);
                $amount = max($baseAmount + $variance, 50000);

                Payment::create([
                    'organization_id' => $org->id,
                    'lease_id' => $lease->id,
                    'contact_id' => $lease->primary_contact_id,
                    'processor_id' => 'pi_' . fake()->uuid(),
                    'amount_cents' => $amount,
                    'method' => $method,
                    'status' => $status,
                    'posted_at' => $status === 'succeeded' ? $paymentDate : null,
                    'due_date' => $paymentDate->copy()->startOfMonth()->addDay(),
                    'late_fee_cents' => $paymentDate->gt($paymentDate->copy()->startOfMonth()->addDays(5)) ? fake()->numberBetween(2500, 7500) : 0,
                    'notes' => fake()->boolean(20) ? fake()->sentence() : null,
                    'created_at' => $paymentDate,
                    'updated_at' => $paymentDate,
                ]);
            }

            if (fake()->boolean(30)) {
                Payment::create([
                    'organization_id' => $org->id,
                    'lease_id' => $lease->id,
                    'contact_id' => $lease->primary_contact_id,
                    'processor_id' => 'pi_' . fake()->uuid(),
                    'amount_cents' => $lease->deposit_amount_cents ?? $lease->rent_amount_cents,
                    'method' => 'ach',
                    'status' => 'succeeded',
                    'posted_at' => $lease->start_date,
                    'payment_type' => 'deposit',
                    'notes' => 'Security deposit payment',
                    'created_at' => $lease->start_date,
                    'updated_at' => $lease->start_date,
                ]);
            }
        }
    }
}