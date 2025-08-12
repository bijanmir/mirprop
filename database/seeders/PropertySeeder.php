<?php

namespace Database\Seeders;

use App\Models\Organization;
use App\Models\Property;
use App\Models\Unit;
use App\Models\Contact;
use App\Models\Lease;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class PropertySeeder extends Seeder
{
    public function run(): void
    {
        $org = Organization::where('slug', 'demo-org')->first();
        if (!$org) return;

        $properties = [
            [
                'name' => 'Sunset Plaza',
                'type' => 'residential',
                'address_line1' => '456 Sunset Blvd',
                'city' => 'Austin',
                'state' => 'TX',
                'zip' => '78702',
                'country' => 'US',
                'units' => ['A1', 'A2', 'B1', 'B2', 'C1', 'C2']
            ],
            [
                'name' => 'Oak Ridge Complex',
                'type' => 'residential',
                'address_line1' => '789 Oak Ridge Dr',
                'city' => 'Austin',
                'state' => 'TX',
                'zip' => '78703',
                'country' => 'US',
                'units' => ['101', '102', '103', '201', '202', '203', '301', '302']
            ],
            [
                'name' => 'Downtown Office Building',
                'type' => 'commercial',
                'address_line1' => '100 Congress Ave',
                'city' => 'Austin',
                'state' => 'TX',
                'zip' => '78701',
                'country' => 'US',
                'units' => ['Suite 100', 'Suite 200', 'Suite 300', 'Suite 400']
            ]
        ];

        foreach ($properties as $propertyData) {
            $units = $propertyData['units'];
            unset($propertyData['units']);

            $property = Property::firstOrCreate(
                [
                    'organization_id' => $org->id,
                    'name' => $propertyData['name']
                ],
                array_merge($propertyData, ['organization_id' => $org->id])
            );

            foreach ($units as $index => $unitLabel) {
                $unit = Unit::firstOrCreate([
                    'organization_id' => $org->id,
                    'property_id' => $property->id,
                    'label' => $unitLabel,
                    'status' => fake()->randomElement(['available', 'occupied', 'maintenance']),
                    'bedrooms' => $property->type === 'residential' ? fake()->numberBetween(1, 3) : null,
                    'bathrooms' => $property->type === 'residential' ? fake()->numberBetween(1, 2) : null,
                    'square_feet' => fake()->numberBetween(600, 1500),
                ]);

                if ($unit->status === 'occupied') {
                    $tenant = Contact::create([
                        'organization_id' => $org->id,
                        'type' => 'tenant',
                        'name' => fake()->name(),
                        'email' => fake()->unique()->email(),
                        'phone' => fake()->phoneNumber(),
                    ]);

                    Lease::create([
                        'organization_id' => $org->id,
                        'unit_id' => $unit->id,
                        'primary_contact_id' => $tenant->id,
                        'start_date' => Carbon::now()->subMonths(fake()->numberBetween(1, 12)),
                        'end_date' => Carbon::now()->addMonths(fake()->numberBetween(6, 24)),
                        'rent_amount_cents' => fake()->numberBetween(100000, 300000),
                        'deposit_amount_cents' => fake()->numberBetween(100000, 200000),
                        'status' => 'active',
                    ]);
                }
            }
        }
    }
}