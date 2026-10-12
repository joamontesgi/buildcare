<?php

use App\Models\JobStatus;
use App\Support\WorkOrderJobStatus;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        foreach (WorkOrderJobStatus::all() as $name) {
            JobStatus::query()->firstOrCreate(['name' => $name]);
        }
    }

    public function down(): void
    {
        // Keep catalog rows; work orders may reference them.
    }
};
