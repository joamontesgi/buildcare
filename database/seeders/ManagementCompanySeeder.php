<?php

namespace Database\Seeders;

use App\Models\ManagementCompany;
use Illuminate\Database\Seeder;

class ManagementCompanySeeder extends Seeder
{
    public function run(): void
    {
        foreach (['AFTON Management', 'Vivmark Residential'] as $name) {
            ManagementCompany::query()->firstOrCreate(['name' => $name]);
        }
    }
}
