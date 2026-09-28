<?php

namespace Database\Seeders;

use App\Models\BillingClerk;
use App\Models\Employee;
use App\Models\ManagementCompany;
use App\Models\Property;
use App\Models\PropertyStaff;
use App\Models\RequestSource;
use App\Models\StaffRole;
use App\Models\State;
use App\Models\WorkOrder;
use App\Models\WorksiteStatus;
use Illuminate\Database\Seeder;

class BcManagementSeeder extends Seeder
{
    public function run(): void
    {
        foreach ([
            ['code' => 'A-V', 'name' => null, 'email' => null, 'phone' => null],
            ['code' => 'N-O', 'name' => null, 'email' => null, 'phone' => null],
        ] as $row) {
            BillingClerk::query()->updateOrCreate(['code' => $row['code']], $row);
        }

        State::query()->updateOrCreate(
            ['code' => 'NJ'],
            ['name' => 'New Jersey'],
        );

        foreach (['AFTON Management', 'Vivmark Residential'] as $name) {
            ManagementCompany::query()->firstOrCreate(['name' => $name]);
        }

        foreach ([
            'BC Supervisor / Runner',
            'BC Project Manager',
            'BC Director',
            'Technician',
            'Vendor',
        ] as $name) {
            StaffRole::query()->firstOrCreate(['name' => $name]);
        }

        foreach ([
            ['first_name' => 'Junior', 'last_name' => 'Garcia'],
            ['first_name' => 'Jarvis', 'last_name' => 'Woods'],
            ['first_name' => 'Juan', 'last_name' => 'Zapata'],
            ['first_name' => 'Roberto', 'last_name' => 'Cañas'],
        ] as $row) {
            Employee::query()->firstOrCreate(
                ['first_name' => $row['first_name'], 'last_name' => $row['last_name']],
                $row,
            );
        }

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

        RequestSource::query()->firstOrCreate(['name' => 'Request / PO / WTN / WO']);

        $state = State::query()->where('code', 'NJ')->firstOrFail();
        $afton = ManagementCompany::query()->where('name', 'AFTON Management')->firstOrFail();
        $vivmark = ManagementCompany::query()->where('name', 'Vivmark Residential')->firstOrFail();
        $clerkAv = BillingClerk::query()->where('code', 'A-V')->firstOrFail();
        $clerkNo = BillingClerk::query()->where('code', 'N-O')->firstOrFail();

        $propertyRows = [
            ['building_name' => 'Artisan at Lawrenceville', 'address' => '1000 Town Ct S, Lawrence Township, NJ 08648', 'management_id' => $afton->id, 'billing_clerk_id' => $clerkAv->id],
            ['building_name' => 'Stewards Crossing Apartments', 'address' => '1000 Stewards Crossing Way, Lawrence Township, NJ 08648', 'management_id' => $afton->id, 'billing_clerk_id' => $clerkAv->id],
            ['building_name' => 'Avalon at Edgewater I', 'address' => 'Old Building (River Mews)', 'management_id' => $vivmark->id, 'billing_clerk_id' => $clerkNo->id],
            ['building_name' => 'Avalon at Edgewater II', 'address' => 'New Building (Russell)', 'management_id' => $vivmark->id, 'billing_clerk_id' => $clerkNo->id],
            ['building_name' => 'Avalon at Florham Park', 'address' => '1 Florence Dr, Florham Park, NJ 07932', 'management_id' => $vivmark->id, 'billing_clerk_id' => $clerkNo->id],
            ['building_name' => 'Avalon North Bergen', 'address' => '5665 John F. Kennedy Blvd, North Bergen, NJ 07047', 'management_id' => $vivmark->id, 'billing_clerk_id' => $clerkNo->id],
        ];

        $properties = collect();
        foreach ($propertyRows as $row) {
            $properties->push(Property::query()->firstOrCreate(
                ['building_name' => $row['building_name'], 'address' => $row['address']],
                [...$row, 'state_id' => $state->id],
            ));
        }

        $supervisorRole = StaffRole::query()->where('name', 'BC Supervisor / Runner')->firstOrFail();
        $pmRole = StaffRole::query()->where('name', 'BC Project Manager')->firstOrFail();
        $employees = Employee::query()->orderBy('id')->get();

        $staffMap = [
            0 => [[0, $supervisorRole->id], [1, $pmRole->id]],
            1 => [[0, $supervisorRole->id], [1, $pmRole->id]],
            2 => [[2, $supervisorRole->id], [3, $pmRole->id]],
            3 => [[2, $supervisorRole->id], [3, $pmRole->id]],
            4 => [[0, $supervisorRole->id], [3, $pmRole->id]],
        ];

        foreach ($staffMap as $propIndex => $assignments) {
            $property = $properties[$propIndex];
            foreach ($assignments as [$empIndex, $roleId]) {
                PropertyStaff::query()->firstOrCreate([
                    'property_id' => $property->id,
                    'employee_id' => $employees[$empIndex]->id,
                    'role_id' => $roleId,
                ]);
            }
        }

        $northBergen = $properties->firstWhere('building_name', 'Avalon North Bergen');
        $occupied = WorksiteStatus::query()->where('name', 'Occupied')->firstOrFail();
        $requestSource = RequestSource::query()->first();

        if ($northBergen && $requestSource) {
            WorkOrder::query()->firstOrCreate(
                [
                    'property_id' => $northBergen->id,
                    'unit_area' => '532',
                    'job_description' => 'Purchase and Install: bathroom exhaust. NOTE: The same one we used in the other units',
                ],
                [
                    'worksite_status_id' => $occupied->id,
                    'request_source_id' => $requestSource->id,
                ],
            );
        }
    }
}
