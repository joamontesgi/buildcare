<?php

namespace Database\Seeders;

use App\Models\StaffRole;
use Illuminate\Database\Seeder;

/** Roles operativos (tabla `roles`, no `user_roles`). */
class StaffRoleSeeder extends Seeder
{
    public function run(): void
    {
        foreach ([
            'BC Supervisor / Runner',
            'BC Project Manager',
            'BC Director',
            'Technician',
            'Vendor',
        ] as $name) {
            StaffRole::query()->firstOrCreate(['name' => $name]);
        }
    }
}
