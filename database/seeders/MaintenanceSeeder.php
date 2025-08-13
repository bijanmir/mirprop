<?php

namespace Database\Seeders;

use App\Models\Organization;
use App\Models\Property;
use App\Models\Unit;
use App\Models\MaintenanceTicket;
use App\Models\Contact;
use App\Models\Vendor;
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

        // Create vendor contacts and vendor records
        $vendors = [];
        for ($i = 1; $i <= 5; $i++) {
            $contact = Contact::create([
                'organization_id' => $org->id,
                'type' => 'vendor',
                'name' => fake()->company() . ' Services',
                'email' => fake()->companyEmail(),
                'phone' => fake()->phoneNumber(),
            ]);
            
            $vendor = Vendor::create([
                'organization_id' => $org->id,
                'contact_id' => $contact->id,
                'services' => [fake()->randomElement(['Plumbing', 'Electrical', 'HVAC', 'Painting', 'Cleaning'])],
                'is_active' => true,
            ]);
            
            $vendors[] = $vendor;
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
                    'status' => fake()->randomElement(['open', 'assigned', 'in_progress', 'completed', 'closed']),
                    'assigned_vendor_id' => $assignedVendor?->id,
                    'sla_due_at' => fake()->dateTimeBetween('now', '+1 week'),
                    'completed_at' => fake()->boolean(30) ? fake()->dateTimeBetween('-1 month', 'now') : null,
                ]);
            }
        }
    }
}