<?php

namespace Database\Seeders;

use App\Models\Organization;
use App\Models\Property;
use App\Models\Unit;
use App\Models\MaintenanceTicket;
use App\Models\Contact;
use Illuminate\Database\Seeder;

class MaintenanceSeeder extends Seeder
{
    public function run(): void
    {
        $org = Organization::where('slug', 'demo-org')->first();
        if (!$org) return;

        $properties = Property::where('organization_id', $org->id)->with('units')->get();

        $ticketTemplates = [
            ['title' => 'Air conditioning not working', 'description' => 'AC unit in living room stopped working yesterday. Very hot.', 'priority' => 'high'],
            ['title' => 'Garbage disposal jammed', 'description' => 'Kitchen garbage disposal is making strange noises and not working.', 'priority' => 'medium'],
            ['title' => 'Bathroom faucet leaking', 'description' => 'Small but constant drip from bathroom sink faucet.', 'priority' => 'low'],
            ['title' => 'Front door lock sticking', 'description' => 'Having trouble with the front door lock, key gets stuck sometimes.', 'priority' => 'medium'],
            ['title' => 'Water heater issues', 'description' => 'Hot water runs out very quickly, seems like water heater problem.', 'priority' => 'high'],
            ['title' => 'Ceiling fan wobbling', 'description' => 'Bedroom ceiling fan wobbles when running at higher speeds.', 'priority' => 'low'],
            ['title' => 'Window screen torn', 'description' => 'Living room window screen has a large tear, bugs getting in.', 'priority' => 'medium'],
            ['title' => 'Smoke detector beeping', 'description' => 'Smoke detector in hallway beeping intermittently, may need battery.', 'priority' => 'high'],
            ['title' => 'Carpet stain removal', 'description' => 'Large stain on bedroom carpet needs professional cleaning.', 'priority' => 'low'],
            ['title' => 'Light fixture flickering', 'description' => 'Kitchen light fixture flickers randomly, electrical issue suspected.', 'priority' => 'medium'],
        ];

        $vendors = [];
        for ($i = 1; $i <= 5; $i++) {
            $vendors[] = Contact::create([
                'organization_id' => $org->id,
                'type' => 'vendor',
                'name' => fake()->company() . ' Services',
                'email' => fake()->companyEmail(),
                'phone' => fake()->phoneNumber(),
            ]);
        }

        foreach ($properties as $property) {
            $ticketCount = fake()->numberBetween(2, 6);
            
            for ($i = 0; $i < $ticketCount; $i++) {
                $template = fake()->randomElement($ticketTemplates);
                $unit = $property->units->random();
                $assignedVendor = fake()->boolean(60) ? fake()->randomElement($vendors) : null;

                MaintenanceTicket::create([
                    'organization_id' => $org->id,
                    'property_id' => $property->id,
                    'unit_id' => $unit->id,
                    'title' => $template['title'],
                    'description' => $template['description'],
                    'priority' => $template['priority'],
                    'status' => fake()->randomElement(['open', 'in_progress', 'completed', 'cancelled']),
                    'assigned_to_id' => $assignedVendor?->id,
                    'estimated_cost_cents' => fake()->numberBetween(5000, 50000),
                    'actual_cost_cents' => fake()->boolean(40) ? fake()->numberBetween(3000, 60000) : null,
                    'scheduled_at' => fake()->boolean(70) ? fake()->dateTimeBetween('now', '+2 weeks') : null,
                    'completed_at' => fake()->boolean(30) ? fake()->dateTimeBetween('-1 month', 'now') : null,
                ]);
            }
        }
    }
}