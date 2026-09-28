<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Orden respetando FKs del esquema bc_management + auth.
     */
    public function run(): void
    {
        // Auth (user_roles, users)
        $this->call([
            RoleSeeder::class,
            UserSeeder::class,
        ]);

        // Catálogos base
        $this->call([
            StateSeeder::class,
            BillingClerkSeeder::class,
            ManagementCompanySeeder::class,
            StaffRoleSeeder::class,
            EmployeeSeeder::class,
            JobStatusSeeder::class,
            WorksiteStatusSeeder::class,
            RequestSourceSeeder::class,
            VendorSeeder::class,
        ]);

        // Propiedades y personal asignado
        $this->call([
            PropertySeeder::class,
            PropertyStaffSeeder::class,
        ]);

        // Operación (work_orders + tablas hijas)
        $this->call([
            WorkOrderSeeder::class,
        ]);
    }
}
