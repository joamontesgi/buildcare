<?php

namespace Database\Seeders;

use App\Models\WorksiteStatus;
use Illuminate\Database\Seeder;

class WorksiteStatusSeeder extends Seeder
{
    public function run(): void
    {
        foreach ([
            'Occupied',
            'Vacant',
            'In Progress',
            'Completed',
            'Waiting',
            'Cancelled',
        ] as $name) {
            WorksiteStatus::query()->firstOrCreate(['name' => $name]);
        }
    }
}
