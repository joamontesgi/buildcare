<?php

namespace Database\Seeders;

use App\Models\Vendor;
use Illuminate\Database\Seeder;

class VendorSeeder extends Seeder
{
    public function run(): void
    {
        foreach ([
            ['name' => 'Reliable Mechanical LLC', 'email' => 'dispatch@reliablemech.local', 'phone' => '973-555-2001'],
            ['name' => 'North Jersey Electric', 'email' => 'jobs@njelectric.local', 'phone' => '201-555-2002'],
            ['name' => 'ProPlumb Services', 'email' => null, 'phone' => '551-555-2003'],
        ] as $row) {
            Vendor::query()->firstOrCreate(['name' => $row['name']], $row);
        }
    }
}
