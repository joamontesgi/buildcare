<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        foreach (Role::catalog() as $row) {
            Role::query()->updateOrCreate(
                ['slug' => $row['slug']],
                [
                    'name' => $row['name'],
                    'description' => $row['description'] ?? null,
                    'can_manage_schedule' => (bool) ($row['can_manage_schedule'] ?? false),
                    'is_admin' => (bool) ($row['is_admin'] ?? false),
                ],
            );
        }
    }
}
