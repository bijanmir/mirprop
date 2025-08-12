<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Organization;
use App\Models\Property;
use App\Models\Unit;
use App\Models\Contact;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class OrganizationDemoSeeder extends Seeder
{
    public function run(): void
    {
        $owner = User::firstOrCreate(
            ['email' => 'owner@example.com'],
            ['name' => 'Demo Owner', 'password' => Hash::make('password')]
        );

        $org = Organization::firstOrCreate(
            ['slug' => 'demo-org'],
            ['name' => 'Demo Org', 'settings' => ['currency' => 'USD']]
        );

        if (! $owner->organizations()->where('organizations.id', $org->id)->exists()) {
            $owner->organizations()->attach($org->id, ['role' => 'owner']);
        }

        if (! $owner->current_organization_id) {
            $owner->forceFill(['current_organization_id' => $org->id])->save();
        }

        $prop = Property::firstOrCreate([
            'organization_id' => $org->id,
            'name' => 'Maple Apartments',
            'type' => 'residential',
            'address_line1' => '123 Maple St',
            'city' => 'Austin',
            'state' => 'TX',
            'zip' => '78701',
            'country' => 'US',
        ]);

        $units = collect(['101','102','201','202'])->map(function ($label) use ($org, $prop) {
            return Unit::firstOrCreate([
                'organization_id' => $org->id,
                'property_id' => $prop->id,
                'label' => $label,
                'status' => 'available',
            ]);
        });

        foreach ($units as $i => $unit) {
            $tenant = Contact::create([
                'organization_id' => $org->id,
                'type' => 'tenant',
                'name' => 'Tenant '.($i+1),
                'email' => 'tenant'.($i+1).'@example.com',
            ]);

            $unit->update(['status' => 'occupied']);

            // Minimal lease if your models exist
            if (class_exists(\App\Models\Lease::class)) {
                $lease = \App\Models\Lease::create([
                    'organization_id' => $org->id,
                    'unit_id' => $unit->id,
                    'primary_contact_id' => $tenant->id,
                    'start_date' => now()->startOfMonth(),
                    'end_date' => now()->startOfMonth()->addYear(),
                    'rent_amount_cents' => 150000,
                    'deposit_amount_cents' => 50000,
                    'frequency' => 'monthly',
                    'status' => 'active',
                ]);

                if (class_exists(\App\Models\LeaseCharge::class)) {
                    \App\Models\LeaseCharge::create([
                        'lease_id' => $lease->id,
                        'type' => 'rent',
                        'amount_cents' => 150000,
                        'description' => 'Monthly Rent',
                        'due_date' => now()->startOfMonth(),
                        'balance_cents' => 150000,
                        'is_recurring' => true,
                        'day_of_month' => 1,
                    ]);
                }
            }
        }
    }
}
