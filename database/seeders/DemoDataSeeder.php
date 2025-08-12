<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        $orgId = \App\Models\Organization::where('slug', 'demo-org')->value('id');
        if (! $orgId) return;

        // Optional: a succeeded payment so the Payments page isn’t empty
        if (class_exists(\App\Models\Payment::class) && class_exists(\App\Models\Lease::class)) {
            $lease = \App\Models\Lease::where('organization_id', $orgId)->inRandomOrder()->first();
            $contactId = $lease?->primary_contact_id;

            if ($lease && $contactId) {
                \App\Models\Payment::firstOrCreate(
                    ['processor_id' => 'pi_demo_'.\Illuminate\Support\Str::uuid()],
                    [
                        'organization_id' => $orgId,
                        'lease_id' => $lease->id,
                        'contact_id' => $contactId,
                        'amount_cents' => 150000,
                        'method' => 'ach',
                        'status' => 'succeeded',
                        'posted_at' => now(),
                    ]
                );
            }
        }

        // A simple maintenance ticket so Tickets page has data
        if (class_exists(\App\Models\MaintenanceTicket::class) && class_exists(\App\Models\Property::class)) {
            $property = \App\Models\Property::where('organization_id', $orgId)->first();
            if ($property) {
                \App\Models\MaintenanceTicket::firstOrCreate(
                    ['title' => 'Leaking faucet in 101'],
                    [
                        'organization_id' => $orgId,
                        'property_id' => $property->id,
                        'unit_id' => $property->units()->first()?->id,
                        'priority' => 'medium',
                        'status' => 'open',
                        'description' => 'Kitchen faucet dripping.',
                    ]
                );
            }
        }
    }
}
