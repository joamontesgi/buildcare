<?php

namespace Database\Seeders;

use App\Models\Employee;
use Illuminate\Database\Seeder;

class EmployeeSeeder extends Seeder
{
    public function run(): void
    {
        foreach ([
            ['first_name' => 'Junior', 'last_name' => 'Garcia', 'email' => 'jgarcia@buildcare.local', 'phone' => '201-555-0101'],
            ['first_name' => 'Jarvis', 'last_name' => 'Woods', 'email' => 'jwoods@buildcare.local', 'phone' => '201-555-0102'],
            ['first_name' => 'Juan', 'last_name' => 'Zapata', 'email' => 'jzapata@buildcare.local', 'phone' => '201-555-0103'],
            ['first_name' => 'Roberto', 'last_name' => 'Cañas', 'email' => 'rcanas@buildcare.local', 'phone' => '201-555-0104'],
        ] as $row) {
            Employee::query()->updateOrCreate(
                ['first_name' => $row['first_name'], 'last_name' => $row['last_name']],
                $row,
            );
        }
    }
}
