<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([RoleSeeder::class]);

        $adminRole = Role::query()->where('slug', Role::SLUG_ADMIN)->first();

        User::updateOrCreate(
            ['email' => 'admin@buildcare.com'],
            [
                'name' => 'Admin BuildCare',
                'password' => Hash::make('password'),
                'role' => User::ROLE_ADMIN,
                'role_id' => $adminRole?->id,
            ],
        );

        $schedulerRole = Role::query()->where('slug', Role::SLUG_SCHEDULER_COORDINATOR)->first();

        User::updateOrCreate(
            ['email' => 'scheduler@buildcare.com'],
            [
                'name' => 'Scheduler Demo',
                'password' => Hash::make('password'),
                'role' => Role::SLUG_SCHEDULER_COORDINATOR,
                'role_id' => $schedulerRole?->id,
            ],
        );

        $this->call([
            BcManagementSeeder::class,
        ]);
    }
}
