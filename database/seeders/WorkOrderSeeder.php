<?php

namespace Database\Seeders;

use App\Models\Employee;
use App\Models\JobStatus;
use App\Models\Property;
use App\Models\RequestSource;
use App\Models\StaffRole;
use App\Models\Vendor;
use App\Models\VendorDailyStatusReport;
use App\Models\WorkOrder;
use App\Models\WorkOrderReference;
use App\Models\WorkOrderStaff;
use App\Models\WorkOrderVendor;
use App\Models\WorksiteStatus;
use Illuminate\Database\Seeder;

class WorkOrderSeeder extends Seeder
{
    public function run(): void
    {
        $northBergen = Property::query()->where('building_name', 'Avalon North Bergen')->first();
        $artisan = Property::query()->where('building_name', 'Artisan at Lawrenceville')->first();

        if (! $northBergen || ! $artisan) {
            return;
        }

        $occupied = WorksiteStatus::query()->where('name', 'Occupied')->firstOrFail();
        $inProgress = WorksiteStatus::query()->where('name', 'In Progress')->firstOrFail();
        $requestSource = RequestSource::query()->where('name', 'Request / PO / WTN / WO')->firstOrFail();
        $jobAwarded = JobStatus::query()->where('name', 'Awarded')->first();
        $jobInProgress = JobStatus::query()->where('name', 'In Progress')->first();

        $supervisorRole = StaffRole::query()->where('name', 'BC Supervisor / Runner')->firstOrFail();
        $pmRole = StaffRole::query()->where('name', 'BC Project Manager')->firstOrFail();
        $junior = Employee::query()->where('first_name', 'Junior')->first();
        $jarvis = Employee::query()->where('first_name', 'Jarvis')->first();
        $vendor = Vendor::query()->orderBy('id')->first();

        $order1 = WorkOrder::query()->updateOrCreate(
            [
                'property_id' => $northBergen->id,
                'unit_area' => '532',
                'job_description' => 'Purchase and Install: bathroom exhaust. NOTE: The same one we used in the other units',
            ],
            [
                'worksite_status_id' => $occupied->id,
                'job_status_id' => $jobAwarded?->id,
                'request_source_id' => $requestSource->id,
                'bc_work_order' => null,
                'bc_estimate' => null,
            ],
        );

        WorkOrderReference::query()->firstOrCreate(
            ['work_order_id' => $order1->id, 'reference_type' => 'PO', 'reference_number' => 'PO-88231'],
        );

        if ($junior) {
            WorkOrderStaff::query()->firstOrCreate([
                'work_order_id' => $order1->id,
                'employee_id' => $junior->id,
                'role_id' => $supervisorRole->id,
            ]);
        }

        if ($vendor) {
            WorkOrderVendor::query()->firstOrCreate([
                'work_order_id' => $order1->id,
                'vendor_id' => $vendor->id,
            ]);

            VendorDailyStatusReport::query()->firstOrCreate(
                [
                    'work_order_id' => $order1->id,
                    'vendor_id' => $vendor->id,
                    'report_date' => now()->toDateString(),
                ],
                [
                    'status' => 'Materials ordered',
                    'notes' => 'Lead time 3 business days.',
                ],
            );
        }

        $order2 = WorkOrder::query()->updateOrCreate(
            [
                'property_id' => $artisan->id,
                'unit_area' => '204',
                'job_description' => 'Replace kitchen faucet and check shut-off valves',
            ],
            [
                'worksite_status_id' => $inProgress->id,
                'job_status_id' => $jobInProgress?->id,
                'request_source_id' => $requestSource->id,
                'size' => '1/1',
                'bc_work_order' => 'BCWO-1201',
                'bc_estimate' => 'EST-4450',
                'extras' => 'Tenant move-in Friday',
            ],
        );

        WorkOrderReference::query()->firstOrCreate(
            ['work_order_id' => $order2->id, 'reference_type' => 'WTN', 'reference_number' => 'WTN-5521'],
        );

        if ($jarvis) {
            WorkOrderStaff::query()->firstOrCreate([
                'work_order_id' => $order2->id,
                'employee_id' => $jarvis->id,
                'role_id' => $pmRole->id,
            ]);
        }
    }
}
