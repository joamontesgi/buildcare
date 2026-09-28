<?php

namespace Database\Seeders;

use App\Models\BillingClerk;
use App\Models\ManagementCompany;
use App\Models\Property;
use App\Models\State;
use Illuminate\Database\Seeder;

class PropertySeeder extends Seeder
{
    public function run(): void
    {
        $state = State::query()->where('code', 'NJ')->firstOrFail();
        $afton = ManagementCompany::query()->where('name', 'AFTON Management')->firstOrFail();
        $vivmark = ManagementCompany::query()->where('name', 'Vivmark Residential')->firstOrFail();
        $clerkAv = BillingClerk::query()->where('code', 'A-V')->firstOrFail();
        $clerkNo = BillingClerk::query()->where('code', 'N-O')->firstOrFail();

        $rows = [
            ['building_name' => 'Artisan at Lawrenceville', 'address' => '1000 Town Ct S, Lawrence Township, NJ 08648', 'management_id' => $afton->id, 'billing_clerk_id' => $clerkAv->id],
            ['building_name' => 'Stewards Crossing Apartments', 'address' => '1000 Stewards Crossing Way, Lawrence Township, NJ 08648', 'management_id' => $afton->id, 'billing_clerk_id' => $clerkAv->id],
            ['building_name' => 'Avalon at Edgewater I', 'address' => 'Old Building (River Mews)', 'management_id' => $vivmark->id, 'billing_clerk_id' => $clerkNo->id],
            ['building_name' => 'Avalon at Edgewater II', 'address' => 'New Building (Russell)', 'management_id' => $vivmark->id, 'billing_clerk_id' => $clerkNo->id],
            ['building_name' => 'Avalon at Florham Park', 'address' => '1 Florence Dr, Florham Park, NJ 07932', 'management_id' => $vivmark->id, 'billing_clerk_id' => $clerkNo->id],
            ['building_name' => 'Avalon North Bergen', 'address' => '5665 John F. Kennedy Blvd, North Bergen, NJ 07047', 'management_id' => $vivmark->id, 'billing_clerk_id' => $clerkNo->id],
        ];

        foreach ($rows as $row) {
            Property::query()->updateOrCreate(
                ['building_name' => $row['building_name'], 'address' => $row['address']],
                [...$row, 'state_id' => $state->id],
            );
        }
    }
}
