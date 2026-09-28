<?php

namespace Database\Seeders;

use App\Models\JobStatus;
use Illuminate\Database\Seeder;

class JobStatusSeeder extends Seeder
{
    public function run(): void
    {
        foreach ([
            'Awarded',
            'Scheduled',
            'In Progress',
            'Pending approval',
            'Completed',
            'Invoiced',
            'On hold',
        ] as $name) {
            JobStatus::query()->firstOrCreate(['name' => $name]);
        }
    }
}
