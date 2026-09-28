<?php

namespace Database\Seeders;

use App\Models\Employee;
use App\Models\Property;
use App\Models\PropertyStaff;
use App\Models\StaffRole;
use Illuminate\Database\Seeder;

class PropertyStaffSeeder extends Seeder
{
    public function run(): void
    {
        $supervisorRole = StaffRole::query()->where('name', 'BC Supervisor / Runner')->firstOrFail();
        $pmRole = StaffRole::query()->where('name', 'BC Project Manager')->firstOrFail();
        $employees = Employee::query()->orderBy('id')->get();

        $properties = Property::query()->orderBy('id')->get();

        $staffMap = [
            'Artisan at Lawrenceville' => [[0, $supervisorRole->id], [1, $pmRole->id]],
            'Stewards Crossing Apartments' => [[0, $supervisorRole->id], [1, $pmRole->id]],
            'Avalon at Edgewater I' => [[2, $supervisorRole->id], [3, $pmRole->id]],
            'Avalon at Edgewater II' => [[2, $supervisorRole->id], [3, $pmRole->id]],
            'Avalon at Florham Park' => [[0, $supervisorRole->id], [3, $pmRole->id]],
            'Avalon North Bergen' => [[2, $supervisorRole->id], [1, $pmRole->id]],
        ];

        foreach ($staffMap as $buildingName => $assignments) {
            $property = $properties->firstWhere('building_name', $buildingName);
            if (! $property) {
                continue;
            }

            foreach ($assignments as [$empIndex, $roleId]) {
                $employee = $employees[$empIndex] ?? null;
                if (! $employee) {
                    continue;
                }

                PropertyStaff::query()->firstOrCreate([
                    'property_id' => $property->id,
                    'employee_id' => $employee->id,
                    'role_id' => $roleId,
                ]);
            }
        }
    }
}
