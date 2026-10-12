<?php

namespace Database\Seeders;

use App\Models\JobStatus;
use App\Support\WorkOrderJobStatus;
use Illuminate\Database\Seeder;

class JobStatusSeeder extends Seeder
{
    public function run(): void
    {
        foreach (WorkOrderJobStatus::all() as $name) {
            JobStatus::query()->firstOrCreate(['name' => $name]);
        }
    }
}
